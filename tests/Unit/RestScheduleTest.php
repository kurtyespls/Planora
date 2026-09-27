<?php

namespace Tests\Unit;

use App\Models\Plan;
use App\Services\PlanoraService;
use PHPUnit\Framework\TestCase;

/**
 * Ang rest schedule ay dating apat na fixed na preset. Ngayon, ang traveller
 * mismo ang pumipili ng oras ("pagdating sa hotel"), kaya ang parsing at
 * validation ng 'HH:MM-HH:MM' windows ay dapat deterministic.
 */
class RestScheduleTest extends TestCase
{
    public function test_presets_and_custom_windows_are_accepted(): void
    {
        foreach (['Morning', 'Afternoon', 'Evening', 'Whole Day', 'Night Shift', '14:00-16:00', '22:00-06:00'] as $entry) {
            $this->assertTrue(
                PlanoraService::isValidRestEntry($entry),
                "{$entry} should be accepted as a rest entry"
            );
        }
    }

    public function test_malformed_entries_are_rejected(): void
    {
        foreach (['Siesta', '14:00', '25:00-26:00', '14:60-16:00', 'ab:cd-ef:gh', '14:00 - 16:00', '', '  '] as $entry) {
            $this->assertFalse(
                PlanoraService::isValidRestEntry($entry),
                "{$entry} should be rejected as a rest entry"
            );
        }
    }

    public function test_custom_window_is_parsed_into_minutes(): void
    {
        $window = PlanoraService::parseRestWindow('14:00-16:00');

        $this->assertSame('14:00', $window['start']);
        $this->assertSame('16:00', $window['end']);
        $this->assertFalse($window['overnight']);
        $this->assertSame(120, $window['minutes']);
    }

    public function test_overnight_window_is_detected(): void
    {
        $window = PlanoraService::parseRestWindow('22:00-06:00');

        $this->assertTrue($window['overnight']);
        $this->assertSame(480, $window['minutes']);
    }

    public function test_normalize_keeps_labels_aliases_and_ranges(): void
    {
        $windows = PlanoraService::normalizeRestSchedule([
            'Morning',
            'Night Shift', // legacy alias → Evening
            '14:00-16:00',
            '00:00-23:59', // explicit whole day → 'Whole Day' rule pa rin
            'hindi-wasto',  // binabalewala
        ]);

        $this->assertCount(4, $windows);
        $this->assertSame('Morning', $windows[0]['label']);
        $this->assertSame('08:00', $windows[0]['start']);
        $this->assertSame('Evening', $windows[1]['label']);
        $this->assertSame('19:00', $windows[1]['start']);
        $this->assertSame('14:00-16:00', $windows[2]['label']);
        $this->assertSame('Whole Day', $windows[3]['label']);
    }

    public function test_entries_are_labelled_for_display_in_twelve_hour_format(): void
    {
        $this->assertSame('Morning', PlanoraService::restEntryLabel('Morning'));
        $this->assertSame('Evening', PlanoraService::restEntryLabel('Night Shift'));
        $this->assertSame('2:00 PM–4:00 PM (2h)', PlanoraService::restEntryLabel('14:00-16:00'));
        $this->assertSame('10:00 PM–6:00 AM (8h)', PlanoraService::restEntryLabel('22:00-06:00'));
        $this->assertSame('2:30 PM–3:00 PM (30m)', PlanoraService::restEntryLabel('14:30-15:00'));

        // Ang literal na buong araw ay 'Whole Day' pa rin ang label.
        $this->assertSame('Whole Day', PlanoraService::restEntryLabel('00:00-23:59'));
    }

    public function test_preset_windows_have_a_twelve_hour_display_copy(): void
    {
        $display = PlanoraService::restWindowDisplay();

        $this->assertSame(['8:00 AM', '12:00 PM'], $display['Morning']);
        $this->assertSame(['1:00 PM', '5:00 PM'], $display['Afternoon']);
        $this->assertSame(['7:00 PM', '11:00 PM'], $display['Evening']);
        $this->assertSame(['12:00 AM', '11:59 PM'], $display['Whole Day']);

        // Ang storage (para sa data-start/data-end at validation) ay 'HH:MM' pa rin.
        $this->assertSame(['08:00', '12:00'], PlanoraService::REST_WINDOWS['Morning']);
    }

    public function test_twenty_four_hour_times_are_converted_but_am_pm_times_are_not(): void
    {
        $this->assertSame(
            'Visit at 3:30 PM, dinner at 7:00 PM.',
            PlanoraService::toTwelveHourClock('Visit at 15:30, dinner at 19:00.')
        );

        // Idempotent: ang oras na may AM/PM ay hindi ginalaw.
        $this->assertSame(
            'Rest 2:00 PM – 4:00 PM and 12:00 PM.',
            PlanoraService::toTwelveHourClock('Rest 2:00 PM – 4:00 PM and 12:00 PM.')
        );

        // Hatinggabi at tanghali.
        $this->assertSame('12:30 AM and 12:00 PM', PlanoraService::toTwelveHourClock('00:30 and 12:00'));

        // Walang laman na text ay hindi sumasabog.
        $this->assertNull(PlanoraService::toTwelveHourClock(null));
        $this->assertSame('', PlanoraService::toTwelveHourClock(''));
    }

    public function test_saved_plan_markdown_is_displayed_in_twelve_hour_format(): void
    {
        $plan = new Plan(['ai_recommendation' => '- **3:30 PM:** Dinner at 18:45 (~PHP 480).']);

        // Ang lumang naka-save na 24-hour time ay 12-hour sa display.
        $this->assertSame(
            '- **3:30 PM:** Dinner at 6:45 PM (~PHP 480).',
            $plan->ai_recommendation_display
        );

        // Ang raw na naka-save ay hindi binabago.
        $this->assertSame('- **3:30 PM:** Dinner at 18:45 (~PHP 480).', $plan->ai_recommendation);
    }

    public function test_rest_instruction_is_written_in_twelve_hour_times(): void
    {
        $instruction = PlanoraService::buildRestInstruction(['14:00-16:00'], null, 3);

        $this->assertStringContainsString('from 2:00 PM to 4:00 PM (2 hour(s))', $instruction);
        $this->assertStringNotContainsString('14:00', $instruction);

        $overnight = PlanoraService::buildRestInstruction(['22:00-06:00'], null, 3);
        $this->assertStringContainsString('from 10:00 PM to 6:00 AM', $overnight);

        // Ang Whole Day ay hiwalay na isinasaad (full rest day), hindi window.
        $this->assertStringContainsString(
            'Day 2 as a full rest day',
            PlanoraService::buildRestInstruction(['Whole Day'], 2, 4)
        );
    }
}

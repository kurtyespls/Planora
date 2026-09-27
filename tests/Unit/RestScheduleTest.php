<?php

namespace Tests\Unit;

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

    public function test_entries_are_labelled_for_display(): void
    {
        $this->assertSame('Morning', PlanoraService::restEntryLabel('Morning'));
        $this->assertSame('Evening', PlanoraService::restEntryLabel('Night Shift'));
        $this->assertSame('14:00–16:00 (2h)', PlanoraService::restEntryLabel('14:00-16:00'));
        $this->assertSame('22:00–06:00 (8h)', PlanoraService::restEntryLabel('22:00-06:00'));
        $this->assertSame('14:30–15:00 (30m)', PlanoraService::restEntryLabel('14:30-15:00'));
    }
}

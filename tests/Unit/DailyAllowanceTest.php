<?php

namespace Tests\Unit;

use App\Services\PlanoraService;
use PHPUnit\Framework\TestCase;

/**
 * Ang daily allowance ay ang isang numero na tatlong surface ay pinagkakapantay-
 * aan: ang server-side budget guard, ang browser's #budget-hint, at ang
 * eligibility filter ng place picker. Kaya naman ito ay inilagay sa sariling
 * test — kung magkakaiba ang arithmetic nila, pipili ang picker ng lugar na
 * ibabagsak ng budget guard.
 *
 * Walang DB dito: puro arithmetic, tulad ng RestScheduleTest.
 */
class DailyAllowanceTest extends TestCase
{
    public function test_allowance_is_the_budget_minus_lodging_spread_over_the_days(): void
    {
        // ₱2,000/night x 2 nights = ₱4,000 ng lodging sa ₱10,000 na budget
        // at 3 araw → ₱6,000 / 3 = ₱2,000 kada araw.
        $this->assertEqualsWithDelta(
            2000.0,
            PlanoraService::dailyAllowanceFor(2000.0, 10000.0, 3),
            0.001
        );
    }

    public function test_a_single_day_trip_is_not_charged_a_night(): void
    {
        // 1 araw → 0 gabi (NIGHTS_PER_DAY_OFFSET), kaya buo ang budget.
        $this->assertEqualsWithDelta(
            5000.0,
            PlanoraService::dailyAllowanceFor(2000.0, 5000.0, 1),
            0.001
        );
    }

    public function test_allowance_floors_at_zero_when_lodging_eats_the_budget(): void
    {
        $this->assertSame(0.0, PlanoraService::dailyAllowanceFor(5000.0, 3000.0, 3));
    }

    public function test_a_zero_or_negative_day_count_is_not_a_division_by_zero(): void
    {
        $this->assertSame(0.0, PlanoraService::dailyAllowanceFor(2000.0, 10000.0, 0));
        $this->assertSame(0.0, PlanoraService::dailyAllowanceFor(2000.0, 10000.0, -4));
    }

    public function test_distance_is_null_when_either_end_is_unknown(): void
    {
        $this->assertNull(PlanoraService::distanceKm(null, null, 16.04, 120.33));
        $this->assertNull(PlanoraService::distanceKm(16.04, 120.33, null, null));
    }

    public function test_distance_between_two_dagupan_points_is_a_plausible_few_kilometres(): void
    {
        $km = PlanoraService::distanceKm(16.0438, 120.3331, 16.0450, 120.3400);

        $this->assertNotNull($km);
        $this->assertGreaterThan(0.5, $km);
        $this->assertLessThan(2.0, $km);
    }

    public function test_distance_is_zero_for_the_same_point(): void
    {
        $this->assertEqualsWithDelta(
            0.0,
            PlanoraService::distanceKm(16.0438, 120.3331, 16.0438, 120.3331),
            0.001
        );
    }

    public function test_place_keys_ignore_case_punctuation_and_diacritics(): void
    {
        $this->assertSame(
            PlanoraService::placeKey('SM Center Dagupan'),
            PlanoraService::placeKey('sm-center  dagupan')
        );

        // Ang diacritic na anyo ng isang catalogue row ay iisang key lamang —
        // ito ang dahilan kaya hindi mapipili ng AI ang "Ma. P. del Pilar" at
        // ang "Ma P Del Pilar" bilang dalawang magkaibang lugar.
        $this->assertSame(
            PlanoraService::placeKey('Café Mango'),
            PlanoraService::placeKey('Cafe Mango')
        );
    }

    public function test_a_place_key_of_pure_punctuation_is_empty_and_never_matches(): void
    {
        $this->assertSame('', PlanoraService::placeKey('  ---  '));
    }
}
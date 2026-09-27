<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City malls. Safe to re-run.
 */
class MallSeeder extends Seeder
{
    public function run(): void
    {
        $malls = [
            [
                'name' => 'SM Center Dagupan',
                'location' => 'A.B. Fernandez Avenue, Downtown District, Dagupan City',
                'latitude' => 16.0440100,
                'longitude' => 120.3376800,
                'rating' => 8.5,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 200,
                'description' => 'The largest shopping mall in Dagupan, with retail stores, a supermarket, food court, cinema, and regular mall events. A common meet-up point for locals.',
            ],
            [
                'name' => 'Robinsons Dagupan',
                'location' => 'Magsaysay Road, Downtown District, Dagupan City',
                'latitude' => 16.0455000,
                'longitude' => 120.3355000,
                'rating' => 8.0,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 150,
                'description' => 'Mid-sized mall near the fish market with retail shops, grocery, dining options, and a cinema. Convenient for visitors staying near downtown.',
            ],
        ];

        foreach ($malls as $mall) {
            Location::updateOrCreate(['name' => $mall['name'], 'category' => $mall['category']], $mall);
        }

        $this->command?->info('Seeded ' . count($malls) . ' Dagupan malls.');
    }
}

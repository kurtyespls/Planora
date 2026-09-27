<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City and nearby shopping malls. Safe to re-run.
 *
 * Coordinates are verified against OpenStreetMap / Nominatim.
 */
class MallSeeder extends Seeder
{
    public function run(): void
    {
        $malls = [
            [
                'name' => 'SM Center Dagupan',
                'location' => 'M.H. del Pilar Street corner Herrero-Perez, Dagupan City',
                'latitude' => 16.0446424,
                'longitude' => 120.3431852,
                'rating' => 8.8,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 300,
                'description' => 'Major shopping mall in downtown Dagupan featuring SM Hypermarket, retail stores, Cyberzone, food court, and dining options.',
            ],
            [
                'name' => 'CSI City Mall Lucao',
                'location' => 'Jose de Venecia Avenue, Lucao District, Dagupan City',
                'latitude' => 16.0237956,
                'longitude' => 120.3230381,
                'rating' => 8.6,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 250,
                'description' => "Dagupan's premier lifestyle shopping and entertainment complex in Lucao with department store, supermarket, cinemas, and event spaces.",
            ],
            [
                'name' => 'Nepo Mall Dagupan',
                'location' => 'Arellano Street, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0512153,
                'longitude' => 120.3419955,
                'rating' => 8.4,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 200,
                'description' => 'Well-known shopping hub on Arellano Street with Robinsons Supermarket, retail shops, tech stalls, and local dining chains.',
            ],
            [
                'name' => 'CSI Market Square',
                'location' => 'A.B. Fernandez Avenue corner Jovellanos Street, Downtown, Dagupan City',
                'latitude' => 16.0435593,
                'longitude' => 120.3361628,
                'rating' => 8.2,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 150,
                'description' => 'Downtown commercial center next to Galvan Street with supermarket, retail outlets, and quick-bite food options.',
            ],
            [
                'name' => 'Magic Centerpoint',
                'location' => 'Jovellanos Street corner Zamora Street, Downtown, Dagupan City',
                'latitude' => 16.0427310,
                'longitude' => 120.3354585,
                'rating' => 8.1,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 150,
                'description' => 'Centrally located department store and supermarket in the heart of Dagupan, steps away from the city plaza and cathedral.',
            ],
            [
                'name' => 'Robinsons Place Pangasinan',
                'location' => 'Urdaneta-Dagupan Road, San Miguel, Calasiao (Bordering Dagupan)',
                'latitude' => 16.0222582,
                'longitude' => 120.3590306,
                'rating' => 8.9,
                'category' => Location::CATEGORY_MALL,
                'icon' => '🏬',
                'budgetPerDay' => 350,
                'description' => 'Full-scale regional shopping mall right on the Dagupan-Calasiao border with department store, supermarket, Movieworld cinemas, and extensive dining.',
            ],
        ];

        foreach ($malls as $mall) {
            Location::updateOrCreate(
                ['name' => $mall['name'], 'category' => $mall['category']],
                $mall
            );
        }

        $this->command?->info('Seeded ' . count($malls) . ' Dagupan malls.');
    }
}

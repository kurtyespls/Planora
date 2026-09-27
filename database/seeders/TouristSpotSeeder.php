<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City beaches and tourist spots. Safe to re-run — matched by name.
 *
 * Categories reuse the vocabulary of PlanoraService::categorizePlaces()
 * (restaurant / mall / beach / tourist) so seeded data and live Overpass
 * results can be merged without translating between two sets of names.
 *
 * Coordinates come from the Wikivoyage Dagupan listings, cross-checked against
 * OpenStreetMap; the city plaza is placed directly opposite the cathedral it
 * faces. budgetPerDay is a rough peso estimate for entrance fees, cottages
 * and local transport for one person.
 */
class TouristSpotSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            Location::CATEGORY_BEACH => [
                [
                    'name' => 'Tondaligan Blue Beach',
                    'location' => 'Tondaligan Beach Baywalk, Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0816860,
                    'longitude' => 120.3424465,
                    'rating' => 8.7,
                    'icon' => '🏖️',
                    'budgetPerDay' => 50,
                    'description' => "Dagupan's main public beach on the Lingayen Gulf, with a baywalk, food stalls and picnic sheds. Best at sunrise and in the late afternoon.",
                ],
                [
                    'name' => 'Bonuan Beach',
                    'location' => 'Bonuan, Dagupan City',
                    'latitude' => 16.0913100,
                    'longitude' => 120.3571800,
                    'rating' => 8.3,
                    'icon' => '🌊',
                    'budgetPerDay' => 50,
                    'description' => 'Quieter stretch of grey-sand shoreline north of Tondaligan, lined with resorts and seafood grills facing the gulf.',
                ],
                [
                    'name' => "Tondaligan People's Park",
                    'location' => 'Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0810000,
                    'longitude' => 120.3430000,
                    'rating' => 8.1,
                    'icon' => '🎡',
                    'budgetPerDay' => 30,
                    'description' => 'Open park and event grounds inside the Tondaligan complex, used for city celebrations and weekend family picnics by the sea.',
                ],
            ],
            Location::CATEGORY_TOURIST => [
                [
                    'name' => 'Metropolitan Cathedral Parish of St. John the Evangelist',
                    'location' => 'Burgos Street, Downtown District, Dagupan City',
                    'latitude' => 16.0422402,
                    'longitude' => 120.3344039,
                    'rating' => 8.8,
                    'icon' => '⛪',
                    'budgetPerDay' => 0,
                    'description' => 'Dagupan Cathedral, one of two seats of the Archdiocese of Lingayen-Dagupan. The modernist church replaced the Spanish-era building that was damaged in the 1990 earthquake. Feast day is 27 December.',
                ],
                [
                    'name' => 'Dagupan City Museum',
                    'location' => 'A.B. Fernandez Avenue, Downtown District, Dagupan City',
                    'latitude' => 16.0433300,
                    'longitude' => 120.3342000,
                    'rating' => 8.2,
                    'icon' => '🏛️',
                    'budgetPerDay' => 50,
                    'description' => "Small city museum along A.B. Fernandez Avenue covering Dagupan's history, from the old Kaboloan polity and the railway era to the bangus industry. A vintage railway car sits out front.",
                ],
                [
                    'name' => 'Magsaysay Fish Market and Landing Center',
                    'location' => 'Magsaysay Road, Downtown District, Dagupan City',
                    'latitude' => 16.0444400,
                    'longitude' => 120.3344200,
                    'rating' => 8.0,
                    'icon' => '🐠',
                    'budgetPerDay' => 0,
                    'description' => 'The working heart of the bangus trade — watch milkfish and other catch come off the boats at the landing centre, best very early in the morning.',
                ],
                [
                    'name' => 'Dagupan City Plaza',
                    'location' => 'Burgos Street corner A.B. Fernandez Avenue, Downtown District, Dagupan City',
                    'latitude' => 16.0422000,
                    'longitude' => 120.3346000,
                    'rating' => 8.0,
                    'icon' => '🏞️',
                    'budgetPerDay' => 0,
                    'description' => "The city's central plaza right in front of the cathedral. Busy with evening food stalls, and the venue for the nightly events of the Dagupan City Fiesta every December.",
                ],
                [
                    'name' => 'Japanese-Philippine Garden Park',
                    'location' => 'Tondaligan, Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0856000,
                    'longitude' => 120.3500700,
                    'rating' => 7.8,
                    'icon' => '🌳',
                    'budgetPerDay' => 0,
                    'description' => 'Memorial stele and small plaza along the Tondaligan shoreline, marking the friendship between Japan and the Philippines.',
                ],
            ],
        ];

        foreach ($groups as $category => $places) {
            foreach ($places as $place) {
                Location::updateOrCreate(
                    ['name' => $place['name'], 'category' => $category],
                    $place
                );
            }
        }

        $this->command?->info('Seeded ' . array_sum(array_map('count', $groups)) . ' Dagupan beaches and tourist spots.');
    }
}

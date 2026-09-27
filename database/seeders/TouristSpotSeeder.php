<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City beaches and tourist spots. Safe to re-run — matched by name.
 *
 * Categories reuse the vocabulary of PlanoraService::categorizePlaces()
 * (beach / tourist) so seeded data and live Overpass results merge cleanly.
 *
 * Coordinates are verified against OpenStreetMap / Nominatim.
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
                    'latitude' => 16.0910556,
                    'longitude' => 120.3571739,
                    'rating' => 8.8,
                    'icon' => '🏖️',
                    'budgetPerDay' => 100,
                    'description' => "Dagupan's premier public beach along the Lingayen Gulf, featuring the scenic Tondaligan Baywalk, open sheds, bike paths, and sunset dining stalls.",
                ],
                [
                    'name' => 'Tondaligan Beach Park',
                    'location' => 'Tondaligan Road, Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0816860,
                    'longitude' => 120.3424465,
                    'rating' => 8.6,
                    'icon' => '🌴',
                    'budgetPerDay' => 50,
                    'description' => 'The landscaped recreational park section of Tondaligan with pine-lined paths, children’s play areas, and picnic facilities by the beach.',
                ],
                [
                    'name' => 'Bonuan Beach',
                    'location' => 'Blue Beach Subdivision, Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0805201,
                    'longitude' => 120.3466718,
                    'rating' => 8.3,
                    'icon' => '🌊',
                    'budgetPerDay' => 50,
                    'description' => 'Historical grey-sand beach stretch in Bonuan, famous for fresh seafood shacks, morning seaside breezes, and local beach resorts.',
                ],
            ],
            Location::CATEGORY_TOURIST => [
                [
                    'name' => 'Metropolitan Cathedral Parish of St. John the Evangelist',
                    'location' => 'Burgos Street corner Jovellanos Street, Poblacion Oeste, Dagupan City',
                    'latitude' => 16.0422402,
                    'longitude' => 120.3344039,
                    'rating' => 8.9,
                    'icon' => '⛪',
                    'budgetPerDay' => 0,
                    'description' => 'Historic Dagupan Cathedral and seat of the Archdiocese of Lingayen-Dagupan. A majestic religious and cultural landmark facing the city plaza.',
                ],
                [
                    'name' => 'Dagupan City Museum',
                    'location' => 'A.B. Fernandez Avenue, Pantal Centro, Dagupan City',
                    'latitude' => 16.0433315,
                    'longitude' => 120.3341370,
                    'rating' => 8.3,
                    'icon' => '🏛️',
                    'budgetPerDay' => 50,
                    'description' => "City museum showcasing Dagupan's rich history, antique memorabilia, railway heritage with a vintage train car out front, and the world-renowned bangus industry.",
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
                    'name' => 'Filipino-Japanese Friendship Garden',
                    'location' => 'Bonuan Boquig, Dagupan City',
                    'latitude' => 16.0856475,
                    'longitude' => 120.3500062,
                    'rating' => 8.1,
                    'icon' => '⛩️',
                    'budgetPerDay' => 30,
                    'description' => 'Peace memorial park and serene garden along Bonuan honoring post-WWII reconciliation and bilateral friendship between the Philippines and Japan.',
                ],
                [
                    'name' => 'MacArthur Park Bonuan',
                    'location' => 'Bonuan Gueset, Dagupan City',
                    'latitude' => 16.0761615,
                    'longitude' => 120.3347796,
                    'rating' => 8.2,
                    'icon' => '🎖️',
                    'budgetPerDay' => 0,
                    'description' => 'Historic coastal park commemorating General Douglas MacArthur and Allied liberation forces landing along the Lingayen Gulf shores in January 1945.',
                ],
                [
                    'name' => 'Dawel River Cruise',
                    'location' => 'Dawel River, Brgy. Pantal / Lomboy, Dagupan City',
                    'latitude' => 16.0600591,
                    'longitude' => 120.3394103,
                    'rating' => 8.5,
                    'icon' => '🚤',
                    'budgetPerDay' => 250,
                    'description' => 'Scenic river cruise traversing the calm waters of the Dawel and Pantal rivers, passing through lush mangrove ecosystems and traditional milkfish fish pens.',
                ],
                [
                    'name' => 'Dagupan City Plaza & Heritage Park',
                    'location' => 'A.B. Fernandez Avenue corner Burgos Street, Downtown, Dagupan City',
                    'latitude' => 16.0431813,
                    'longitude' => 120.3341895,
                    'rating' => 8.0,
                    'icon' => '🏞️',
                    'budgetPerDay' => 0,
                    'description' => 'The vibrant civic and cultural center of the city, bustling with fountains, evening street-food kiosks, and city festival gatherings.',
                ],
                [
                    'name' => 'Saints Peter and Paul Parish Church Calasiao',
                    'location' => 'Poblacion West, Calasiao (Adjacent to Dagupan)',
                    'latitude' => 16.0102954,
                    'longitude' => 120.3569211,
                    'rating' => 9.0,
                    'icon' => '⛪',
                    'budgetPerDay' => 0,
                    'description' => 'Declared a National Cultural Treasure of the Philippines. A magnificent 16th-century Spanish colonial Baroque church renowned for its historic bell tower and nearby Calasiao Puto stalls.',
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

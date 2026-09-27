<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City restaurants. Safe to re-run — records are matched by name.
 *
 * Coordinates are verified against OpenStreetMap / Nominatim / official locations.
 * Ratings use the app's 0-10 scale and budgetPerDay is a realistic peso estimate
 * for one person eating a full meal there, so the planner can weigh food against activities.
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = [
            [
                'name' => "Matutina-Gerry's Seafood House",
                'location' => 'Jose de Venecia Avenue Extension, Sunrise Subdivision, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0552954,
                'longitude' => 120.3374765,
                'rating' => 9.0,
                'icon' => '🐟',
                'budgetPerDay' => 700,
                'description' => 'The premier seafood restaurant Dagupan is famous for: authentic boneless grilled bangus, sinigang na bangus belly, seafood platters, and local specialties.',
            ],
            [
                'name' => "Silverio's Seafood Restaurant",
                'location' => 'Arellano Street, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0560083,
                'longitude' => 120.3404878,
                'rating' => 8.7,
                'icon' => '🦐',
                'budgetPerDay' => 750,
                'description' => 'Long-running Dagupan seafood institution on Arellano Street in Pantal, celebrated for freshly prepared butter garlic prawns, steamed fish, and Pangasinan seafood feast.',
            ],
            [
                'name' => 'Ciudad Elmina Fishing Village',
                'location' => 'Bacayao Norte / Al Fernandez Road, Dagupan City',
                'latitude' => 16.0345605,
                'longitude' => 120.3441752,
                'rating' => 8.6,
                'icon' => '🍽️',
                'budgetPerDay' => 600,
                'description' => 'Scenic open-air dining pavilions built right over living fishponds. Famous for fresh grilled tilapia, authentic pigar-pigar, and relaxing rural ambiance away from traffic.',
            ],
            [
                'name' => 'Sílantro Fil-Mex Cantina',
                'location' => 'Perez Boulevard, Pogo Chico, Dagupan City',
                'latitude' => 16.0401211,
                'longitude' => 120.3378337,
                'rating' => 8.8,
                'icon' => '🌮',
                'budgetPerDay' => 500,
                'description' => 'The original birthplace of the famous homegrown Filipino-Mexican cantina on Perez Boulevard, celebrated for beef nachos, tacos, burritos, and signature dips.',
            ],
            [
                'name' => 'Kuya Max Restogrill',
                'location' => 'De Venecia Avenue, Lucao District, Dagupan City',
                'latitude' => 16.0192978,
                'longitude' => 120.3306673,
                'rating' => 8.5,
                'icon' => '🍲',
                'budgetPerDay' => 450,
                'description' => 'Popular local casual dining destination along De Venecia Avenue, serving generous servings of traditional Pangasinan and Ilocano comfort food and sizzling grills.',
            ],
            [
                'name' => 'Golden Mami House',
                'location' => 'Perez Boulevard, Pogo Chico, Dagupan City',
                'latitude' => 16.0387966,
                'longitude' => 120.3338639,
                'rating' => 8.2,
                'icon' => '🍜',
                'budgetPerDay' => 350,
                'description' => 'Well-loved classic Chinese noodle house in downtown Dagupan, known for steaming bowls of wonton beef mami, siopao, dumplings, and budget-friendly rice toppings.',
            ],
            [
                'name' => 'Panaderia Antonio Bakery & Cafe',
                'location' => 'Tapuac Road, Cuison Subdivision, Dagupan City',
                'latitude' => 16.0288178,
                'longitude' => 120.3283498,
                'rating' => 8.5,
                'icon' => '🥐',
                'budgetPerDay' => 300,
                'description' => "Dagupan's favorite artisan bakery and brunch spot on Tapuac Road, serving specialty coffees, freshly baked pastries, pasta, and all-day Filipino breakfast plates.",
            ],
            [
                'name' => 'Chef Distrito',
                'location' => 'Tapuac Road, Greenfields Subdivision, Dagupan City',
                'latitude' => 16.0337851,
                'longitude' => 120.3307563,
                'rating' => 8.4,
                'icon' => '🍕',
                'budgetPerDay' => 600,
                'description' => 'Cozy modern bistro on Tapuac Road near colleges, serving artisan brick-oven pizzas, pasta platters, and fusion Filipino-Western mains.',
            ],
            [
                'name' => 'Great Taste Pigar-Pigar',
                'location' => 'Don Marcelo Balolong Avenue, Bonuan Boquig, Dagupan City',
                'latitude' => 16.0741302,
                'longitude' => 120.3406937,
                'rating' => 8.6,
                'icon' => '🥩',
                'budgetPerDay' => 350,
                'description' => 'One of the most famous pigar-pigar spots in Dagupan, dishing out fresh carabeef stir-fried with heaps of onions and cabbage cooked over high heat.',
            ],
            [
                'name' => "Auntie Aneth Pigar-Pigar",
                'location' => 'Galvan Street, Downtown District, Dagupan City',
                'latitude' => 16.0423102,
                'longitude' => 120.3363307,
                'rating' => 8.7,
                'icon' => '🥩',
                'budgetPerDay' => 300,
                'description' => 'Legendary Galvan Street night-eatery at the heart of Dagupan’s pigar-pigar strip, serving sizzling tender beef and onions with calamansi-sili soy dip.',
            ],
            [
                'name' => "Mary's Kaleskesan",
                'location' => 'Zamora Street, Pogo Chico, Dagupan City',
                'latitude' => 16.0397776,
                'longitude' => 120.3364612,
                'rating' => 8.5,
                'icon' => '🍲',
                'budgetPerDay' => 200,
                'description' => 'Iconic Dagupan institution specializing in kaleskes (savory beef and intestine soup broth), seasoned with calamansi and eaten with puto.',
            ],
            [
                'name' => 'Cafe Feliz (Lenox Hotel)',
                'location' => 'Rizal Street, Pantal Centro, Dagupan City',
                'latitude' => 16.0419430,
                'longitude' => 120.3392779,
                'rating' => 8.3,
                'icon' => '☕',
                'budgetPerDay' => 550,
                'description' => 'Elegantly appointed in-house hotel restaurant inside Lenox Hotel, serving quality Chinese banquet favorites, Filipino staples, and morning buffet breakfast.',
            ],
            [
                'name' => 'Starbucks Lucao',
                'location' => 'Lucao Road corner Jose de Venecia Avenue, Lucao, Dagupan City',
                'latitude' => 16.0236336,
                'longitude' => 120.3257084,
                'rating' => 8.5,
                'icon' => '☕',
                'budgetPerDay' => 350,
                'description' => 'Spacious standalone Starbucks cafe in Lucao featuring a drive-thru, outdoor patio seating, espresso drinks, Frappuccinos, and bakery items.',
            ],
            [
                'name' => 'Yellow Cab Pizza Dagupan',
                'location' => 'A.B. Fernandez Avenue, Pantal Centro, Dagupan City',
                'latitude' => 16.0439178,
                'longitude' => 120.3346511,
                'rating' => 8.2,
                'icon' => '🍕',
                'budgetPerDay' => 500,
                'description' => 'New York-style pizza, Charlie Chan pasta, hot wings, and ice-cold drinks right along the busy A.B. Fernandez commercial corridor.',
            ],
            [
                'name' => 'Pizza Hut Arellano',
                'location' => 'Arellano Street, Dior Village, Herrero-Perez, Dagupan City',
                'latitude' => 16.0515350,
                'longitude' => 120.3416642,
                'rating' => 8.1,
                'icon' => '🍕',
                'budgetPerDay' => 450,
                'description' => 'Sit-down and delivery pizzeria on Arellano Street serving pan pizzas, stuffed crusts, and family pasta platters.',
            ],
            [
                'name' => 'Dagupeña Restaurant',
                'location' => 'Urdaneta-Dagupan Road, San Miguel, Calasiao (Dagupan Gateway)',
                'latitude' => 16.0216483,
                'longitude' => 120.3583080,
                'rating' => 9.1,
                'icon' => '🐟',
                'budgetPerDay' => 750,
                'description' => 'Established since 1928, this legendary culinary landmark near the Dagupan border is famous for its binusigan bangus, lechon de leche, and heritage Pangasinense cuisine.',
            ],
            [
                'name' => 'Jollibee Galvan',
                'location' => 'Galvan Street corner Perez Boulevard, Downtown, Dagupan City',
                'latitude' => 16.0401949,
                'longitude' => 120.3373612,
                'rating' => 8.3,
                'icon' => '🍗',
                'budgetPerDay' => 250,
                'description' => "Downtown flagship Jollibee branch serving iconic Chickenjoy, Jolly Spaghetti, and Yumburgers in the center of Dagupan's busiest shopping district.",
            ],
            [
                'name' => "McDonald's Lucao",
                'location' => 'Lucao Road, Lucao District, Dagupan City',
                'latitude' => 16.0242248,
                'longitude' => 120.3239179,
                'rating' => 8.2,
                'icon' => '🍔',
                'budgetPerDay' => 250,
                'description' => 'Modern 24-hour fast food restaurant with drive-thru located conveniently in Lucao beside CSI City Mall.',
            ],
        ];

        foreach ($restaurants as $restaurant) {
            Location::updateOrCreate(
                ['name' => $restaurant['name'], 'category' => Location::CATEGORY_RESTAURANT],
                $restaurant
            );
        }

        $this->command?->info('Seeded ' . count($restaurants) . ' Dagupan restaurants.');
    }
}

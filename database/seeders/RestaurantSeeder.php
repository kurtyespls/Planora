<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Dagupan City restaurants. Safe to re-run — records are matched by name.
 *
 * Coordinates come from the Wikivoyage Dagupan listings, cross-checked against
 * OpenStreetMap. Ratings use the app's 0-10 scale and budgetPerDay is a rough
 * peso estimate for one person eating a full meal there, so the planner can
 * weigh food against activities.
 */
class RestaurantSeeder extends Seeder
{
    public function run(): void
    {
        $restaurants = [
            [
                'name' => "Matutina-Gerry's Seafood House",
                'location' => 'De Venecia Road Extension, Brgy. Pantal, Dagupan City',
                'latitude' => 16.055327,
                'longitude' => 120.3326847,
                'rating' => 8.8,
                'icon' => '🐟',
                'budgetPerDay' => 700,
                'description' => 'The seafood house Dagupan is known for: grilled and sinigang na bangus, plus kaleskes and pigar-pigar. Open 8AM to 9:30PM.',
            ],
            [
                'name' => "Silverio's Seafood Restaurant",
                'location' => 'Arellano Street, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0555726,
                'longitude' => 120.3373001,
                'rating' => 8.6,
                'icon' => '🦐',
                'budgetPerDay' => 750,
                'description' => 'Long-running Arellano Street seafood place open from breakfast until midnight. Fresh bangus and prawns cooked to order.',
            ],
            [
                'name' => 'Ciudad Elmina Restaurant',
                'location' => 'De Venecia Road, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0342758,
                'longitude' => 120.3434491,
                'rating' => 8.4,
                'icon' => '🍽️',
                'budgetPerDay' => 600,
                'description' => 'Quirky but serene setting where you eat right next to tilapia fish ponds. Also a good place to try pigar-pigar away from the city noise.',
            ],
            [
                'name' => 'Sílantro Fil-Mex Cantina',
                'location' => '#229 Perez Boulevard, Downtown District, Dagupan City',
                'latitude' => 16.0402000,
                'longitude' => 120.3355773,
                'rating' => 8.3,
                'icon' => '🌮',
                'budgetPerDay' => 500,
                'description' => 'Filipino-Mexican cantina on Perez Boulevard serving tacos, burritos and rice meals. Open 10AM to 10PM.',
            ],
            [
                'name' => 'Kuya Max',
                'location' => 'Gaudencio Siapno Road, Lucao District, Dagupan City',
                'latitude' => 16.0196000,
                'longitude' => 120.3301000,
                'rating' => 8.5,
                'icon' => '🍲',
                'budgetPerDay' => 400,
                'description' => 'One of the original local restaurants in the city, offering Pangasinan and Ilocano dishes at very reasonable prices. Open 11AM to 10PM.',
            ],
            [
                'name' => 'Golden Mami House',
                'location' => '#20 Burgos Street, Downtown District, Dagupan City',
                'latitude' => 16.0394000,
                'longitude' => 120.3351000,
                'rating' => 8.1,
                'icon' => '🍜',
                'budgetPerDay' => 350,
                'description' => 'Chinese restaurant on Burgos Street, two blocks from the cathedral. Noodles, mami and rice toppings. Open 10:30AM to 8PM.',
            ],
            [
                'name' => "Pinkie's Bakeshop and Restaurant",
                'location' => 'Perez Boulevard, Downtown District, Dagupan City',
                'latitude' => 16.0427000,
                'longitude' => 120.3440000,
                'rating' => 8.0,
                'icon' => '🧁',
                'budgetPerDay' => 350,
                'description' => 'Bakeshop and restaurant in one — handy for breakfast, cakes and pasalubong. Open 8AM to 7PM.',
            ],
            [
                'name' => 'Chef Distrito',
                'location' => 'Tapuac Road, Tapuac District, Dagupan City',
                'latitude' => 16.0338000,
                'longitude' => 120.3308000,
                'rating' => 8.2,
                'icon' => '🍕',
                'budgetPerDay' => 650,
                'description' => 'Italian, American and Filipino plates on Tapuac Road near Lyceum-Northwestern University. Open 10AM to 8PM.',
            ],
            [
                'name' => 'Kusina Nen Laki Digno',
                'location' => 'Tabeng Road, Bonuan, Dagupan City',
                'latitude' => 16.0308000,
                'longitude' => 120.3574000,
                'rating' => 8.4,
                'icon' => '🍛',
                'budgetPerDay' => 400,
                'description' => 'Home-style Pangasinan cooking in a garden setting off Tabeng Road. Open 9AM to 6PM.',
            ],
            [
                'name' => 'Cafe Feliz',
                'location' => 'Herrero Road, Downtown District, Dagupan City (inside Lenox Hotel)',
                'latitude' => 16.0422000,
                'longitude' => 120.3392000,
                'rating' => 8.2,
                'icon' => '☕',
                'budgetPerDay' => 600,
                'description' => 'The restaurant inside Lenox Hotel, open 6:30AM to 11PM. Chinese and Filipino dishes plus a full breakfast service.',
            ],
            [
                'name' => 'Gao Dong Hai',
                'location' => 'Rivera Street, Downtown District, Dagupan City',
                'latitude' => 16.0422000,
                'longitude' => 120.3376000,
                'rating' => 8.0,
                'icon' => '🥢',
                'budgetPerDay' => 550,
                'description' => 'Chinese cuisine on Rivera Street in the downtown core. Open 8AM to 10PM.',
            ],
            [
                'name' => 'Big Boy',
                'location' => 'Galvan Street, Downtown District, Dagupan City',
                'latitude' => 16.0419000,
                'longitude' => 120.3363000,
                'rating' => 7.9,
                'icon' => '🍔',
                'budgetPerDay' => 300,
                'description' => 'Family diner on Galvan Street serving breakfast, burgers and American comfort food. The Galvan strip is where the pigar-pigar stalls sit beside CSI Square. Open 9AM to 10PM.',
            ],
            [
                'name' => "Angel's Pizza - Dagupan City",
                'location' => 'A.B. Fernandez Avenue, ASSADA Center, Downtown District, Dagupan City',
                'latitude' => 16.0455000,
                'longitude' => 120.3427000,
                'rating' => 8.1,
                'icon' => '🍕',
                'budgetPerDay' => 450,
                'description' => 'Branch of the local pizza chain inside ASSADA Center on A.B. Fernandez Avenue. Open 9AM to 7PM.',
            ],
            [
                'name' => "Olivia's Pizzeria",
                'location' => 'A.B. Fernandez East Avenue, Mayombo, Dagupan City',
                'latitude' => 16.0466100,
                'longitude' => 120.3514500,
                'rating' => 8.0,
                'icon' => '🍕',
                'budgetPerDay' => 500,
                'description' => 'Neighbourhood pizzeria on A.B. Fernandez East Avenue heading towards Mayombo.',
            ],
            [
                'name' => "Dad Joe's Restaurant",
                'location' => 'Arellano Street, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0570800,
                'longitude' => 120.3403500,
                'rating' => 8.2,
                'icon' => '🍗',
                'budgetPerDay' => 450,
                'description' => 'Filipino and American restaurant on Arellano Street, open 7AM to 9PM. A convenient stop before or after a De Venecia Road seafood run.',
            ],
            [
                'name' => "Jech's Restaurant - Dagupan",
                'location' => 'De Venecia Road, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0533000,
                'longitude' => 120.3359000,
                'rating' => 8.1,
                'icon' => '🍚',
                'budgetPerDay' => 400,
                'description' => 'Everyday Filipino meals on De Venecia Road. Open 9AM to 9PM.',
            ],
            [
                'name' => 'Ikura by Hagemu',
                'location' => 'De Venecia Road, Brgy. Pantal, Dagupan City',
                'latitude' => 16.0529000,
                'longitude' => 120.3362000,
                'rating' => 8.5,
                'icon' => '🍣',
                'budgetPerDay' => 700,
                'description' => 'Japanese restaurant on De Venecia Road — sushi, ramen and rice bowls. Open 11AM to 8PM.',
            ],
            [
                'name' => "Domino's Pizza - Dagupan",
                'location' => 'Arellano Street, Dagupan City',
                'latitude' => 16.0465000,
                'longitude' => 120.3432000,
                'rating' => 8.0,
                'icon' => '🍕',
                'budgetPerDay' => 500,
                'description' => 'International pizza chain branch on Arellano Street, handy for late delivery to downtown hotels.',
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

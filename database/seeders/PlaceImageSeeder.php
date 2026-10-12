<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

/**
 * Assign a per-place photo to every curated Dagupan location.
 *
 * Runs after the catalogue seeders and matches by name, so it is safe to
 * re-run — it only fills `image_url` on rows that already exist.
 *
 * Photos live in `public/images/places/`. Until a real per-place photo is
 * uploaded for a spot, it points at a thematically close Dagupan photo;
 * the Step 03 cards fall back to PlanoraService::PLACE_CATEGORY_IMAGES
 * when `image_url` is null, so no card ever renders a broken <img>.
 */
class PlaceImageSeeder extends Seeder
{
    public function run(): void
    {
        $images = [
            // Beaches
            'Tondaligan Blue Beach' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSf9CKnrs0hMgvOzkT1NKXejdj8DrvlCec98M7KCu3Yxw&s=10',
            'Tondaligan Beach Park' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSsJYVizh3wyZGOWr5qRAKQ6XCsBIs0EAQXtiK-0iEXCQ&s=10',
            'Bonuan Beach' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCXjg9-A-B7ezTa10S358-ZBJFpV2J6JIWVVH07zrBIg&s=10',

            // Tourist spots
            'Metropolitan Cathedral Parish of St. John the Evangelist' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSRigJV5Wc1WrKPALIKd9BBoSWI9FlMNCFouWl9KTWZlQ&s=10',
            'Dagupan City Museum' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQq9aHeSwtEIEtotS4D18QrlxS9iSWu5gQaA6HUUqk4uQ&s=10',
            'Magsaysay Fish Market and Landing Center' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS-1gT0lk2j1MNRacbT5r_PulN6FAGu1QneQCo2e5Rc-Q&s=10',
            'Filipino-Japanese Friendship Garden' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS0DATGjZf7ICRuqxXaob1IlPQOlJGOQVrcreYlFHp88g&s=10',
            'MacArthur Park Bonuan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTpNdC_HGbMN1iUqcYqShn8WSpsoRAGil8ubX59CQgptQ&s=10',
            'Dawel River Cruise' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQQ1nUP2nezavttRvGevAhbMR-ZBNEyd3r70ZueQsB4mQ&s=10',
            'Dagupan City Plaza & Heritage Park' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRuxYimmW0s2SC7DAu4sv_zjcBleb2v4UJmTg7ztdM_og&s=10',
            'Saints Peter and Paul Parish Church Calasiao' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT7lYVPPqf1_fLoko7lphQ4zMA9_kS6CDUV1qIukpX6-w&s=10',

            // Malls
            'SM Center Dagupan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSKtzBr5gtspoYZRX8MlshQ8aK446cA8bPBfK7CuC9Eug&s=10',
            'CSI City Mall Lucao' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTu3xJByrjs_PhVpbA-uBO3YQzRVRIKYowFFvXP6nfh1w&s=10',
            'Nepo Mall Dagupan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTRkZP-YGFxSTzIPOarCXX-3UHVgYxHtmZ6PPYwhvrD7Q&s=10',
            'CSI Market Square' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQUcYM0Q2xspRk0yxy9K6hQqXcPq3j5IJIBNkPhhmh2Ww&s=10',
            'Magic Centerpoint' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZHU7ONdls58q_oDOpqmfPU9ZUyCa7htnlT8bMCT_syw&s=10',
            'Robinsons Place Pangasinan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR2uURxObX_89bI9nXpuzHPXWMS7-I2gR6NVY86vhjcIA&s=10',

            // Restaurants — seafood houses share the seafood photo, pigar-pigar
            // spots share the grill photo, and so on until real photos land.
            "Matutina-Gerry's Seafood House" => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTAYiydxKf4RXt6L6mLpDIqBuEjYF0g4atBhutN8x90ng&s=10',
            "Silverio's Seafood Restaurant" => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQlQYlB2xzlfnCzhf6qTq2Ae4WfeYCFhvIucHihKt7iFQ&s=10',
            'Ciudad Elmina Fishing Village' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRctWTpvqR9arjN_QfVsWFF43Jt29k5r-AhsAnBU7AJ7Q&s=10',
            'Dagupeña Restaurant' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSA3JiMPafzbt8IhMtyfpXR2w-sfi18-amS58QO9VvGKQ&s',
            'Sílantro Fil-Mex Cantina' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGRBweIp9h-gg4HuwT1VYzCHg9NNN2QwcFsyAEOa3OKg&s=10g',
            'Kuya Max Restogrill' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTdcs3NKRelEt0rtlAMh5N0t9zpPZHSVKTHyCjEIvgP2A&s=10',
            'Great Taste Pigar-Pigar' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR8DFjJYuKomitOwAE9Peu0DQNhE7luoYZa9kdq7iuS5Q&s',

            'Golden Mami House' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSg5TuYXUXIR8wkMidE4sM41s-RqSf3SU5r2zRy2Q1Cnw&s=10',
            'Panaderia Antonio Bakery & Cafe' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT3RRiRwsLCipEjLjNP7ILTFtduCpybLf3lOm4OPnEAQA&s=10',

            'Chef Distrito' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQUg2yOvrmuj3zgNpm0x5w199ZUy1Nq3TBuWcxpo8bmUA&s=10',
            'Yellow Cab Pizza Dagupan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQkZMCUI9IJMDG2QpmZnpOrfoYwBdI50JdF1U7K7Hr07A&s=10',
            'Pizza Hut Arellano' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ2lfLVOFMK8F4s7JDJRsmWBGGPpIbcV1g_Hq1mkzRk9Q&s=10',
            'Starbucks Lucao' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQp_m2ugZOM6RAV3PCxUv0Xbk_oRvZxlfwKojHCTGMqfw&s=10',
            'Jollibee Galvan' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQdEKpk1Ar2P8LIpvmOy5gT9Yc2tnZFPChcw6ciRpgwgQ&s=10',
            "McDonald's Lucao" => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSyAuCxg3QoORpvsBuyRvjBUUH5CwSCB7lRuOCG_xbjvA&s=10',
        ];

        $updated = 0;

        foreach ($images as $name => $imageUrl) {
            $updated += Location::where('name', $name)->update(['image_url' => $imageUrl]);
        }

        $this->command?->info("Assigned photos to {$updated} places.");
    }
}

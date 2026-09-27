<?php

namespace Database\Seeders;

use App\Models\Hotel;
use App\Services\CacheService;
use Illuminate\Database\Seeder;

/**
 * Real Dagupan City hotels. Safe to re-run — records are matched by name.
 *
 * Coordinates come from OpenStreetMap (the Wikivoyage Dagupan listings plus
 * Nominatim lookups) so the planner's map pins land on the right buildings.
 *
 * Ratings follow the app's 0-10 scale: the UI renders "8.4 / 10" and halves
 * anything above 5 when drawing the stars. Prices are digits only — the admin
 * controller strips currency symbols and the views prepend the peso sign.
 *
 * Images point at the local /images/dagupan/ set so nothing 404s offline;
 * swap them for real property photos once you have them.
 */
class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $hotels = [
            [
                'name' => 'The Monarch Hotel',
                'address' => 'Urdaneta Junction-Dagupan Road, Calasiao, Pangasinan',
                'lat' => 16.0210000,
                'lon' => 120.3589500,
                'rating' => 9.0,
                'price' => '4500',
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT4tR52aLwXgUrcXN3HSI50v1oO4AJE3wBjsZNXnl8frw&s=10',
                'gallery' => 'https://www.kayak.com/rimg/himg/6e/f3/9c/expedia_group-4643277-93894119-865874.jpg?width=836&height=607&crop=true, https://www.hotelscombined.com/himg/94/ba/b0/expedia_group-4643277-5fa0d4-834308.jpg, https://images.getaroom-cdn.com/image/upload/s--NnZxch92--/c_limit,e_improve,fl_lossy.immutable_cache,h_940,q_auto:good,w_940/v1780450808/a4d13baeba670e6d3617736801580c7295192e82?_a=BACAEuEv&atc=e7cd1cfa, https://images.getaroom-cdn.com/image/upload/s--_FJkNzPO--/c_limit,e_improve,fl_lossy.immutable_cache,h_940,q_auto:good,w_940/v1721536468/c23dbb8a2e93b454a4a8d8079c2249fc4389054c?_a=BACAEuEv&atc=e7cd1cfa, https://dynamic-media-cdn.tripadvisor.com/media/photo-o/32/76/02/e2/caption.jpg?w=1100&h=1100&s=1, https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRsHnIDrgeYob_ofx2kzxXNnnSqcZeDPjqLU-xqqBxTx0ab9bHDFz5PBsA&s=10',
                'amenities' => 'Free WiFi, Air Conditioning, Swimming Pool, Gym, Restaurant, Bar, Ballroom, Free Parking',
                'description' => 'The upscale option in the Dagupan area, just across the boundary in Calasiao. Full-service facilities with a ballroom, pool and gym.',
            ],
            [
                'name' => 'Lenox Hotel',
                'address' => 'Rizal Street, Downtown District, Dagupan City',
                'lat' => 16.0419700,
                'lon' => 120.3393600,
                'rating' => 8.0,
                'price' => '1594',
                'image_url' => 'https://lh3.googleusercontent.com/grass-cs/ACvplmMdA5NS_oSPjNXlO_X25oIyCJk7JS60HKHm42Ah9o3nCPSKurzJvv5EdFgWZ1b71q16ZvH1qbJsr64fCbxTjGt66cw9-LFRee5cfI5CNmpstGAn-gNOp8QqOFyo-7-XK7SwNTpCLEVE3oFf=w324-h312-n-k-no',
                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWkOzmudaF9Vkwd8xsDqPxxQr7HNpdqDJfCBBSaSp4K6qoQ0JWSCVnr5SsFXUhsn2zgw8UnO5GoYPmtUjWjety6a8KlKFqSuz_X54heoTYv9PeERCrP6J1oqWsDKySIvFtPKD-scmQ=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWn3OwV4aK_kUc3nwssvrUCq_W5zt7N-H15LfUeH2zQ64ihGkgti_YuUb5afGE3LUMYE7dd1got2n5AwgZt15bUm6ClAxasV8tB_5wEje73UMa6EJ3ta09OvtM6jfBvtIRZYtC-i=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWlbh_2e8Pf8xAvUxnZvcUJsDktwxKeN5vCIVCCKxqVNcWdqiofBcXhY8WGVm468e93xHxUFi7PPgyRP72YgMXwLIwLH14EyKXKGAquyk1uuIZoC0K6sjL2ohrU5MvHEOFi43Vpp=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, Free Breakfast, Restaurant, Room Service, 24-Hour Front Desk',
                'description' => 'Long-standing downtown hotel along Rizal Street with its own restaurant, Cafe Feliz. Walking distance to the city plaza, the cathedral and CSI Market Square.',
            ],
            [
                'name' => 'Star Plaza Hotel',
                'address' => 'A.B. Fernandez Avenue, Downtown District, Dagupan City',
                'lat' => 16.0450000,
                'lon' => 120.3399000,
                'rating' => 7.8,
                'price' => '1850',
                'image_url' => 'https://lh3.googleusercontent.com/proxy/3g0JVutRpzkL1K8LN0jyB_u51fWtlaotMmKNf_9UbNl8NGopCAg4rWS8FBAZN9qsoJRaDQpvKK_KzbpL7PYBDFM7jWbrNUgsYj7e_8VIjhuq4XuK1Cov802EzLeQ64hn6vAhkeiBLGcDOUFODQmEx6cYuSDgtHg=s680-w680-h510-rw',
                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWm7J5xpXALFHefcwJk-yWUd-HdSNrgLyYiqSRd1ockZdCk0vE2cCiWAmeN7N6WYWCDaRLb2afADi4YbZvr-QtlVN6GpdoT6sz7HK24uS_b92M_5yvxatlfs9td3VBAthOZ0WHU=s680-w680-h510-rw, https://lh3.googleusercontent.com/proxy/pSaplsd3SmMOnTZcbnfybmk66sukC2M09KywrERnuVbBKQDwfN19iprtFOaVXSADvnzXJiwy6pxXuAt3uKG8HKFml8LDTDqmYOZVXIVT-d_hYpsiaJUxyRCvy9WKAsJFdX0oKLTt3i-kusROwsX6fZImbjztpA=s680-w680-h510-rw, https://lh3.googleusercontent.com/proxy/AbikrU8HaEt1ULuUNQmsELCUufubaVdcJJ-IcXSttafCb9UZXAYnuX62RG8SCCzA8_fFgoQTTQyXAe0zXjDWu2DbDc2f9cbMEbP17CDFtxLFDfKvEkD5b_JYSEXf7PZCrtCGA23QZcGyYlo_BWRK_GrPrXA4_RQ=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnmzkYlQ7WhgeddJRAsrUg2g1qTBpVgptN05VLkOGS8P1_0_LXY_4gBKozJgBHpagPH3bop3iAQRXZpPOYkUm9h22QmKPiZZ3TU9G51i8m4ehfCDa0iOmJ1eXfd_oK7BUMuZFHUHg=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, Restaurant, Function Hall, Parking, Room Service',
                'description' => 'One of the busiest mid-range hotels on A.B. Fernandez Avenue, in the middle of downtown. Its restaurant serves a wide range of local and Filipino dishes.',
            ],
            [
                'name' => 'Value Star Inn',
                'address' => 'Magsaysay Road corner A.B. Fernandez Avenue, Downtown District',
                'lat' => 16.0450000,
                'lon' => 120.3391000,
                'rating' => 7.8,
                'price' => '1150',
                'image_url' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWla6882BzHB4TSgYoAR8SBVMi_ZPT7kIawKGVzkhmfkp6sAbMJoIbptEHDJmBdfb8Gf4e-OsNUL6hG7bqLa6tHw1jsAMr6z6fj88_F6dDkFhapEbbserHCj5juMfc14E2wTrlqT=s680-w680-h510-rw',

                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnYss7yMpWvaqMYIVuQ_Qo8v9RxtdLRzHzTS0tG4Yh-rlMC7PTR3wlhXG0cDeItnMf2i_j21hUoOuunxnArbomiWuBsiln_vD--0Qo-N_9hKQE8ZPHaMWlFjsesY_Iyhuse70o=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWngKPOTH-2XAJOEnC9JRvuVNDXbO15M9n_Uo0mkd7pBCD9rfONAlJWyQvRrHxwe_CcksPX4Xxh6Hq0Ik-uWoW82SnaSz5UjX1WP4frSpwFgSYJg8RpbNCA2p97fjSmw5OUv8uXo1Q=s680-w680-h510-rw, https://lh3.googleusercontent.com/proxy/jxm58dgV7WeA1YZZTpP6tCUvEkN_1QoBLdHq2fLuqP-tpGBuX2FeQL2lrQQ7p8EPoIZ0DpY86s3LeVGZvilmN0xr3E77EBgYBuo99UIZ4knH1fx3LhmbTGmc-1x66GX8otfkcxh8rUfNzY2Ti6rhrq4qdH9n9A=s680-w680-h510-rw, https://lh3.googleusercontent.com/proxy/8neKehhQkVS5LlANbfBjB1K9pbVHOArSb83OM8yg_ys76nRB7PK5Mp_rrMxPrDB0eEfI1vu0W2YUJFnbfq1L0uE5kEMTBgpeq4fC03vRZXR2ZL3c_68SGeDBN0Jfwa-9TwgV3DEN04m-VUe5u2rhuibK1YV6Dw=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, 24-Hour Front Desk, Parking',
                'description' => 'Budget sister property of Star Plaza Hotel, tucked just behind it. Single and double rooms with private bathrooms, fan or air-conditioned.',
            ],
            [
                'name' => 'Hotel Monde',
                'address' => '#108 A.B. Fernandez West Avenue, Downtown District, Dagupan City',
                'lat' => 16.0451337,
                'lon' => 120.3414909,
                'rating' => 8.2,
                'price' => '1650',
                'image_url' => 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR1xFASKR9n_PkE1HpYJg2rYeJVZGu9Kytq91gCqmV7xg&s=10',

                'gallery' => 'https://pix10.agoda.net/hotelImages/862212/-1/3acaf2be758bf6cb727e823f74e4a067.jpg?va=1&ce=0&s=414x232, https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT-i5Ne2ReZ8PwUWaHpxEZjqMC1yF_ta-WiGGdP6ZB6lQ&s, https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRc8IYX94qvsL_bj6a5yBZzKh5YuuIgA2yb-Uj8o6h5i0a7utw3SanWUFc9&s=10',
                'amenities' => 'Free WiFi, Air Conditioning, Free Breakfast, Smart TV, Parking',
                'description' => 'Budget boutique hotel on A.B. Fernandez West Avenue, a short walk from the downtown commercial strip and SM Center Dagupan.',
            ],
            [
                'name' => 'Bedbox Hotel',
                'address' => 'Rizal corner Nueva Streets, Downtown District, Dagupan City',
                'lat' => 16.0437000,
                'lon' => 120.3391000,
                'rating' => 8.4,
                'price' => '1400',
                'image_url' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWlNWHJGXb2T2NVsidS1X39RBmLCDRpw_t_Fgd7DuCWcBRKkNgsD7oQbQ8Q6WqSTdTZJ5eaWmBccm5uvv1AstCk6CP8hvRnJZbpTYE9t9OGvXhd6hqkaCGUwN2Q0a5wQV0ZXonMM=s680-w680-h510-rw',

                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWmqWKoefVLyUIVT7smHo1nPSgnTOCYhPuCT8vfxyXli6MfY_HqAyBxpo_kKs154LiKQ752kG6wBl_gQHV3dUOsW46QVaUhQAHqfX06gMruvQ8MnHrcheEbJPiLcMWNjSSuImNrU=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWkxEuOI9VW8Yz8RmsCB246pi5P7GnvIyHNUu40LGsPLgSc1YTtiSFzgs3Llryk-JLQPbleSL8iLUilX5sUgWQla1V4Zdd_iX50G9j2qV6SrUk_PifXrmLEnmL2NrL_9D6cFoPITww=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWkNpVXaieNOQJlPwhbQNyBZqpJ1A561AfJ6opgj5BPZsQvo7ukvs_kCBsJci8DW1iyLh-27KlsuUtulybtyMwrukUogAHnaxysiBgCBPqF7yw3c5ojPwjeaMmu4hTvSGlvhOkyPdg=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, Shared Kitchen, Luggage Storage, Smart TV',
                'description' => 'Compact modern hotel at the Rizal and Nueva intersection, a few blocks from the downtown core. Popular with travellers on a short Dagupan stop.',
            ],
            [
                'name' => 'Hotel de Luc',
                'address' => 'Tapuac Road, Tapuac District, Dagupan City',
                'lat' => 16.0354000,
                'lon' => 120.3316000,
                'rating' => 7.6,
                'price' => '2200',
                'image_url' => 'https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEgekL8A4FUP73xt9uC2WQ00oMTKDhbHmCDAEPNvho7mhZuaZosNRS1NQCkbVxkTYMNiHjJxsBAvAssgt9ko4Ff1Qm1YSkFmSPCTnPHJoQI7oRKlwQxAPYCKJbvgOhmxdQIj9hzpKz7PQaxL/s1600/Hotel+Le+Duc+Dagupan+Facade+02.JPG',

                'gallery' => 'https://i.travelapi.com/lodging/24000000/23140000/23134200/23134117/4cff7019_z.jpg, 
                https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQh4Uk0O-jscUYF2YQcZBg5IiPwodkUno_G4T5-mmgUf1dmNfLuv-b8YRK4&s=10, https://blogger.googleusercontent.com/img/b/R29vZ2xl/AVvXsEhPYvTz9YoDKaBHSl_v4nF_QRg7JQflrkcCoSQDZ2nceKae5qYctEmZOaJoLQ4rSrzHs5sq9syhBlIaXk9wEIOxcql9u3CZSICUI2ARmK1e4Z15aQSqKYLKYWd154ccXRqgxyTN69d160qX/s1600/Hotel+Le+Duc+Dagupan+Room+05.JPG',
                'amenities' => 'Free WiFi, Air Conditioning, Swimming Pool, Restaurant, Parking, Room Service',
                'description' => 'Established hotel on Tapuac Road beside Lyceum-Northwestern University, on the quieter southern side of the city. Has its own pool and restaurant.',
            ],
            [
                'name' => 'YMCA Hotel',
                'address' => 'Perez Boulevard, Downtown District, Dagupan City',
                'lat' => 16.0378000,
                'lon' => 120.3326000,
                'rating' => 8.4,
                'price' => '950',
                'image_url' => 'https://lh3.googleusercontent.com/PJuA03Se4tEtMWzkwTdSphVtDRTm-4ITTxaa-a28sCwt0fUbDAgTNuhEn-fdm-K_=s680-w680-h510-rw',

                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnuGSF9d9i5p0_pCEHerc2BOIy8g_tDIjY-FSOmLcu9AatzPbOVayjoSx9eIA7z6Zq3TCHLzMVChoghHJmnb03zocl7Eg8I9S8Tt4NG8HZIx285tnuFIODqj8JmPlSSElku_TXk=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWk4hCshndx_CzfKLjPXnvb2_W7zDbjYihHsEHR_NVnekea-5W2yPUY1MqAxhucX-i1y5IIpPbVlc-G9h-MVIQGVv-YaBwdSMcuZ5xVb5dvd5Q29xKhaqZHLrX_dkyLkg2qc1bff=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnswGh5jCEz5jcEqJsvYuDvlen9_dxH48ErmykkJt7FIQY5GRuckQw5UQtaI6GwoC1aWitfIuV1ktKmfUOOpunB-p9UBKzGgxJQLc6KJLMOqAf7vvVcjaKlFFYBRfUsTWK4b4RO6Q=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, Function Hall, Parking, 24-Hour Front Desk',
                'description' => 'Simple, well-kept and very affordable rooms on Perez Boulevard, run by the YMCA. A good base if you mainly need a bed near downtown.',
            ],
            [
                'name' => 'Luxor Hotel',
                'address' => 'Arellano Street, Dior Village, Dagupan City',
                'lat' => 16.0494419,
                'lon' => 120.3412523,
                'rating' => 7.8,
                'price' => '1700',
                'image_url' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWnhA9QBSXa9JmqZSL0eB2xZr2cRPPexTSRtYGzzYQMILbdN9J1vHnkD0QISCgZvZIhJyV6JxaYTM12iVHq4xOno6CNRcz56SVUTrFcuTQY0tNqZ0IuF9obQNcfiF2KSv3zJN0U=s680-w680-h510-rw',
                
                'gallery' => 'https://lh3.googleusercontent.com/gps-cs-s/AHRPTWktsAeZv2mhtXSxtwrl0bzup09Tz5Sy9eqGMTtgFBhU42FULj_qMjY85S0Def8Z71fBKA--nCgfiLI3di3SSQ09qwxIUlLYUtJVjAskzu1zmej9cODDQEBG6yiNt8jUcOcy63RnVw=s680-w680-h510-rw, https://lh3.googleusercontent.com/gps-cs-s/AHRPTWktsAeZv2mhtXSxtwrl0bzup09Tz5Sy9eqGMTtgFBhU42FULj_qMjY85S0Def8Z71fBKA--nCgfiLI3di3SSQ09qwxIUlLYUtJVjAskzu1zmej9cODDQEBG6yiNt8jUcOcy63RnVw=s680-w680-h510-rw',
                'amenities' => 'Free WiFi, Air Conditioning, Restaurant, Parking, Room Service',
                'description' => 'Comfortable mid-range hotel on Arellano Street, a short ride from Nepo Mall and the seafood strip along De Venecia Road.',
            ],
            
        ];

        foreach ($hotels as $hotel) {
            Hotel::updateOrCreate(['name' => $hotel['name']], $hotel);
        }

        // Hotels are cached for 5 minutes, so drop the cache after seeding.
        CacheService::clearHotelsCache();

        $this->command?->info('Seeded ' . count($hotels) . ' Dagupan hotels.');
    }
}

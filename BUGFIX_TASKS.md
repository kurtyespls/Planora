# Planora — Bugfix Status

## Verified working (no change needed)
- [x] `User.php` — `casts()` closing brace is present and correct.
- [x] `AdminController::update()` — already validates via `$request->validate()`.
- [x] `AdminController::destroy()` / `destroyUser()` — already use `findOrFail`.
- [x] `routes/web.php` — no temporary `/fix-admin` route exists.
- [x] Auth / welcome / planora / admin views — CSS custom properties are defined in
      `public/css/planora-design.css` (`:root` block) and linked by every view.

## Fixed in this pass
- [x] **migration `add_user_id_to_plans_table`** — `down()` was empty; now drops the
      foreign key then the column, guarded with `Schema::hasColumn`.
- [x] **`GeocodeHotels.php`** — the Dagupan suffix was appended twice (once in the
      fallback, once in `geocode()`). The suffix is now applied in exactly one place
      and is configurable via `services.nominatim.suffix`.
- [x] **"View saved plan" was broken** — `PlanController::show()` rendered a
      non-existent `plans.show` view and the profile button only showed a
      "coming soon" toast. Added `resources/views/plans/show.blade.php`, wired
      `viewPlan()` to `/plans/{id}`, switched to route-model binding, and added a
      per-card Delete action for `PlanController::destroy()`.
- [x] **Check-in / check-out / tourist-spots were stubs** — route closures returned
      canned JSON and `[]`. Added the `visit_logs` table, `VisitLog` model,
      `VisitLogController`, a real `locations`-backed `/api/tourist-spots`, and fixed
      the client passing an un-awaited Promise as `spot_id`.
- [x] **Weather never reached the itinerary** — the client sent a hardcoded
      "Weather data not available" string. Conditions are now resolved server-side
      from the hotel coordinates via the cached weather service.
- [x] **AI generation silently always fell back** — `env('GROQ_API_KEY')` is now
      `config('services.groq.key')` (works under `config:cache`), and `plans.ai_provider`
      records whether a plan came from `groq` or the `local` generator.
- [x] **`activity_preferences` was dead code** — validated by the controller, never
      used by the service, never sent by any view. Removed; `PlanoraService::REST_OPTIONS`
      is now the single source of truth for validation.
- [x] **"Whole Day" rest option was unreachable** — the backend supported it but the
      UI only offered Morning / Afternoon / Night Shift. Added the fourth chip.
- [x] **`/generate-plan` was unauthenticated** — it persisted ownerless plans and
      spent external API calls. Now behind `auth` + throttle, with an
      `abort_unless(auth()->check())` guard in the service and a single atomic
      `DB::transaction` write.
- [x] **Hotel price & plan budget types normalized** — created migration
      `normalize_hotel_price_and_plan_budget` converting `hotels.price` to `decimal(10,2)`
      and `plans.budget` to `decimal(12,2)`. Replaced regex/string stripping hacks with
      direct decimal casting.
- [x] **Nights calculation standardized** — centralized overnight math in
      `PlanoraService::nightsFor($days)` (`days - 1`) across both backend budget checking
      and frontend estimates (1-day trip = 0 nights accommodation).
- [x] **Plan rename & regenerate capabilities** — added `PATCH /plans/{plan}` and
      `POST /plans/{plan}/regenerate` routes and controller methods with ownership
      enforcement. Added editable custom titles (`title` column and `display_title` accessor),
      inline renaming in both the plan view and profile view, and single-click regeneration.
- [x] **Hotel existence validation** — enforced that generated itineraries validate that
      the selected basecamp hotel exists in the database before calculating accommodation costs.

## GPS route guidance (Step 3 map + saved plans)
- [x] **Automatic GPS detection on Step 3** — `initMap()` (called by `confirmPlan()`
      the moment Step 3 becomes active) ends with `autoDetectAndRouteToHotel()`, so
      the browser asks for geolocation as soon as the traveller reaches the map.
- [x] **User pin + driving route** — the detected coordinates get an orange `U` pin
      (`makeIcon('U', 'pin-user')`) and Leaflet Routing Machine (OSRM) draws the
      driving route to the selected basecamp hotel, then `fitBounds()` frames the
      whole trip.
- [x] **Route guidance card above the map** — distance and estimated driving time from
      `routesfound` (o straight-line fallback) ang ipinapakita sa `route-guidance-details`.
- [x] **Tinanggal ang map shortcut buttons** — ang `Navigate (Google Maps) ↗` link
      (`btn-open-external-maps`) at ang `Hotel` button (`focusOnHotel()`) sa Step 3 card,
      kasama ang `extLink` / `navLink` href wiring sa `autoDetectAndRouteToHotel()` at
      `detectLocationAndRoute()`, at ang direkta na `Navigate ↗` link sa
      `plans/show.blade.php`. Nananatili pa rin ang automatic GPS detection, `U` pin,
      OSRM route drawing at distance/ETA text.
- [x] **Saved plans** — `plans/show.blade.php` has `📍 Route from my location` (OSRM route
      + `fitBounds()`); the direct `Navigate ↗` link was removed.
- [x] **Single source of truth for the user pin** — `upsertUserMarker()` /
      `removeUserMarker()` replaced the duplicated `userMarker` vs `window.userMarker`
      code paths, so `Show My Location` moves the existing pin instead of adding a
      second `U` marker, and the weather popup now loads on its first open.
- [x] **Stale map state reset** — `initMap()` clears `userMarker` and
      `currentRouteControl` after `map.remove()`, so re-entering Step 3 can no longer
      reuse a marker that belongs to a destroyed map.
- [x] **Routing failures are visible** — `routingerror` handlers plus a 15s watchdog
      fall back to a straight-line (haversine) distance/ETA instead of leaving the card
      at "Calculating…", and a denied GPS permission explains itself in the card.
- [x] **One haversine helper** — the copy nested inside `fetchNearbyAmenities()` was
      removed in favor of the module-level `haversineKm()`.
- [x] **Itinerary-driven map pins** — `fetchNearbyAmenities()` now only stores each POI
      as a descriptor sa `nearbyPlacesIndex` imbes na agad maglagay ng markers. Pagkatapos
      ma-render ang itinerary, ang `renderSuggestedPlaces()` ang naglalagay ng pins para
      lang sa mga lugar na binabanggit ng itinerary (normalized na paghahambing ng
      pangalan, may `PLACE_STOPWORDS` para hindi tumugma ang generic na "hotel"/"beach"/
      "Dagupan"), at `updateLegendVisibility()` ang nagtatago ng legend entry na walang
      pin. Kapag walang binanggit na POI, hotel + current location lang ang nasa mapa.
- [x] **Realtime throttled GPS tracking** — `watchPosition` keeps the traveller's location
      current on both Step 3 (`/planora`) and the saved-plan page (`/plans/{id}`):
      - **Step 3 (`planora.blade.php`)**: Auto-starts once the user reaches Step 3.
        Re-routes to the hotel only when moving >= 120 m or every 25 s (`needsReroute()`),
        updating the driving distance & ETA dynamically without hammering the OSRM server.
        Smart camera handling (`panTo()` only if marker leaves viewport; `fitBounds` on first lock).
        Toggle live tracking button in the route card allows turning it off or back on.
        Automatically pauses when switching steps, backgrounding tab (`visibilitychange`),
        or leaving the page (`beforeunload`).
      - **Saved-plan page (`plans/show.blade.php`)**: Initiated when the traveller clicks
        `Route from my location` (or via the `Start/Stop live tracking` button), keeping GPS
        strictly opt-in to avoid spontaneous browser prompts. Includes the same throttling,
        live status indicator (`● LIVE`), and lifecycle cleanups.
- [ ] Note: `navigator.geolocation` only works over `https://` or `localhost`.

## 12-hour (AM/PM) time display
- [x] **12-hour na ang lahat ng oras sa UI** — ang rest preset chips
      (`PlanoraService::restWindowDisplay()`), ang "Daily rest" live summary
      (`formatClockTime()` sa browser) at ang saved-plan badges (`restEntryLabel()`) ay
      `2:00 PM–4:00 PM (2h)` na imbes na `14:00–16:00`. Ang storage, ang native time
      inputs at ang `data-start`/`data-end` ay nanatiling 'HH:MM' — display lang ang binago.
- [x] **12-hour din ang hinihingi sa AI** — bagong `AI_TIME_FORMAT_RULE` sa prompt
      ("12-hour clock with AM/PM, NEVER 24-hour"), at 12-hour na ang dinagdag na slot
      guidance at ang `buildRestInstruction()` (public na para matest).
- [x] **Normalization ng natitirang 24-hour** — ang `toTwelveHourClock()` ay
      nagko-convert ng `HH:MM` na HINDI sinusundan ng AM/PM (negative lookahead), kaya
      hindi nagagalaw ang `3:30 PM`; idempotent sa output ng local generator, at 12-hour
      din ang mga lumang naka-save na plano (`Plan::ai_recommendation_display`).

## Real at tumpak na POI seeders (Malls, Restaurants, Tourist Spots)
- [x] **Na-verify ang bawat lokasyon laban sa OpenStreetMap / Nominatim at opisyal na talaan ng Dagupan City**:
  - **`MallSeeder.php`**: Pinalitan ang kathang-isip na "Robinsons Dagupan" ng mga totoong shopping centers ng Dagupan:
    - *SM Center Dagupan* (`16.0446424, 120.3431852`) — M.H. del Pilar corner Herrero-Perez
    - *CSI City Mall Lucao* (`16.0237956, 120.3230381`) — Jose de Venecia Avenue, Lucao
    - *Nepo Mall Dagupan* (`16.0512153, 120.3419955`) — Arellano Street, Brgy. Pantal
    - *CSI Market Square* (`16.0435593, 120.3361628`) — A.B. Fernandez Avenue corner Jovellanos Street
    - *Magic Centerpoint* (`16.0427310, 120.3354585`) — Jovellanos corner Zamora Street
    - *Robinsons Place Pangasinan* (`16.0222582, 120.3590306`) — Calasiao / Dagupan border
  - **`RestaurantSeeder.php`**: Lahat ng 18 restaurants ay totoong mga kainan sa Dagupan na may eksaktong GPS coordinates, kabilang ang:
    - *Matutina-Gerry's Seafood House* (`16.0552954, 120.3374765`) — De Venecia Extension
    - *Silverio's Seafood Restaurant* (`16.0560083, 120.3404878`) — Arellano Street, Pantal
    - *Ciudad Elmina Fishing Village* (`16.0345605, 120.3441752`) — Bacayao Norte
    - *Sílantro Fil-Mex Cantina* (`16.0401211, 120.3378337`) — Perez Boulevard
    - *Auntie Aneth Pigar-Pigar* (`16.0423102, 120.3363307`) — Galvan Street
    - *Great Taste Pigar-Pigar* (`16.0741302, 120.3406937`) — Bonuan Boquig
    - *Mary's Kaleskesan* (`16.0397776, 120.3364612`) — Zamora Street
    - *Kuya Max Restogrill* (`16.0192978, 120.3306673`) — Lucao District
    - *Panaderia Antonio Bakery & Cafe* (`16.0288178, 120.3283498`) — Tapuac Road
    - *Dagupeña Restaurant* (`16.0216483, 120.3583080`) — Calasiao / Dagupan Gateway
    - Kasama ang iba pang kilalang dining spots: *Chef Distrito*, *Golden Mami House*, *Cafe Feliz (Lenox Hotel)*, *Starbucks Lucao*, *Yellow Cab*, *Pizza Hut*, *Jollibee Galvan*, at *McDonald's Lucao*.
  - **`TouristSpotSeeder.php`**: Mga totoong pasyalan, heritage sites, at baybayin ng Dagupan:
    - *Tondaligan Blue Beach* (`16.0910556, 120.3571739`) — Tondaligan Baywalk
    - *Tondaligan Beach Park* (`16.0816860, 120.3424465`) — Bonuan Gueset
    - *Bonuan Beach* (`16.0805201, 120.3466718`) — Blue Beach
    - *Metropolitan Cathedral Parish of St. John the Evangelist* (`16.0422402, 120.3344039`) — Burgos Street
    - *Dagupan City Museum* (`16.0433315, 120.3341370`) — A.B. Fernandez Avenue
    - *Filipino-Japanese Friendship Garden* (`16.0856475, 120.3500062`) — Bonuan Boquig
    - *MacArthur Park Bonuan* (`16.0761615, 120.3347796`) — Bonuan Gueset
    - *Dawel River Cruise* (`16.0600591, 120.3394103`) — Dawel River, Pantal
    - *Dagupan City Plaza & Heritage Park* (`16.0431813, 120.3341895`) — City Plaza
    - *Magsaysay Fish Market and Landing Center* (`16.0444400, 120.3344200`) — Magsaysay Road
    - *Saints Peter and Paul Parish Church Calasiao* (`16.0102954, 120.3569211`) — National Cultural Treasure sa bungad ng Dagupan.

## Regression coverage added
- `tests/Unit/RestScheduleTest.php` — ang 12-hour display helpers (`restWindowDisplay()`,
      `toTwelveHourClock()` kasama ang AM/PM protection, `buildRestInstruction()`) at ang
      12-hour na `restEntryLabel()`.
- `tests/Feature/PlanTest.php` — guest rejection, ownership on view/delete/rename/regenerate,
  Whole Day validation, 404 for missing plans, provider persistence, hotel existence validation,
  and 0-night day trips.
- `tests/Feature/VisitLogTest.php` — tourist-spots lookup/filter, check-in with and
  without a curated location, duplicate check-in, check-out duration, guest rejection.
- `tests/Feature/RouteGuidanceTest.php` — Step 3 auto geolocation + OSRM route drawing,
  the route card (distance, ETA, at `assertDontSee` guards na wala na ang dating Google
  Maps navigation link at hotel focus button), router/location failure fallbacks, ang
  saved-plan `Route from my location` button, at ang itinerary-driven na POI pins
  (`renderSuggestedPlaces()` / `itineraryMentionsPlace()` + legend visibility).

Run with `php artisan test` (tests use in-memory SQLite via `phpunit.xml`).


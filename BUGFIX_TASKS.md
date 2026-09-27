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
- [x] **Route guidance card above the map** — distance and estimated driving time
      from `routesfound`, a `Navigate (Google Maps) ↗` link rebuilt with the detected
      origin + hotel destination, and a `Hotel` button (`focusOnHotel()`) to fly the
      view back to the basecamp.
- [x] **Saved plans** — `plans/show.blade.php` has `📍 Route from my location` and a
      direct `Navigate ↗` link; both reuse the same OSRM route drawing and `fitBounds()`.
- [x] **Single source of truth for the user pin** — `upsertUserMarker()` /
      `removeUserMarker()` replaced the duplicated `userMarker` vs `window.userMarker`
      code paths, so `Show My Location` moves the existing pin instead of adding a
      second `U` marker, and the weather popup now loads on its first open.
- [x] **Stale map state reset** — `initMap()` clears `userMarker` and
      `currentRouteControl` after `map.remove()`, so re-entering Step 3 can no longer
      reuse a marker that belongs to a destroyed map.
- [x] **Routing failures are visible** — `routingerror` handlers plus a 15s watchdog
      fall back to a straight-line (haversine) distance/ETA instead of leaving the card
      at "Calculating…", and a denied GPS permission explains itself in the card while
      the Google Maps link keeps working.
- [x] **One haversine helper** — the copy nested inside `fetchNearbyAmenities()` was
      removed in favor of the module-level `haversineKm()`.
- [ ] Note: `navigator.geolocation` only works over `https://` or `localhost`.

## Regression coverage added
- `tests/Feature/PlanTest.php` — guest rejection, ownership on view/delete/rename/regenerate,
  Whole Day validation, 404 for missing plans, provider persistence, hotel existence validation,
  and 0-night day trips.
- `tests/Feature/VisitLogTest.php` — tourist-spots lookup/filter, check-in with and
  without a curated location, duplicate check-in, check-out duration, guest rejection.
- `tests/Feature/RouteGuidanceTest.php` — Step 3 auto geolocation + OSRM route drawing,
  the route card (distance, ETA, Google Maps navigation, hotel focus), router/location
  failure fallbacks, and the saved-plan route button.

Run with `php artisan test` (tests use in-memory SQLite via `phpunit.xml`).


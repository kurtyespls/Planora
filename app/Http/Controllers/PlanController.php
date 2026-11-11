<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Location;
use App\Models\Plan;
use App\Services\PlanoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
    /**
     * List all saved plans for the current user.
     */
    public function index()
    {
        $userId = (int) Auth::id();
        $plans = Plan::where('user_id', $userId)->orderByDesc('created_at')->get();

        return view('plans.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Show a saved itinerary.
     *
     * The {plan} route parameter is resolved by implicit model binding, so a
     * missing plan 404s before this method runs.
     */
    public function show(Plan $plan)
    {
        $userId = (int) Auth::id();

        // OWNERSHIP CHECK — kung hindi ng user ang plan, i-block
        if ((int) $plan->user_id !== $userId) {
            Log::warning('Unauthorized plan access attempt', [
                'plan_id' => $plan->id,
                'plan_owner' => $plan->user_id,
                'attempted_by' => $userId,
            ]);
            return redirect('/planora')->with('error', 'Hindi mo ma-access ang plan na ito.');
        }

        try {
            // Used for the map pin. Hotels can be deleted after a plan is saved,
            // so a null result is expected and handled by the view.
            $hotel = Hotel::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($plan->hotel_name)])
                ->first();

            // ── Map data for the plans map ──
            // selected_places holds place name strings only (e.g. "SM Center Dagupan (Mall)"),
            // so we resolve each one against the curated locations table to get coordinates.
            $placeMarkers = [];
            foreach ($plan->selected_places ?? [] as $rawName) {
                $cleanName = preg_replace(
                    '/\\s*\\((Restaurant|Mall|Beach|Tourest Spot)\\)\\s*$/i',
                    '',
                    trim((string) $rawName)
                );
                if ($cleanName === '') {
                    continue;
                }
                $location = Location::where('name', $cleanName)->first();
                if ($location && $location->latitude !== null && $location->longitude !== null) {
                    $placeMarkers[] = [
                        'name' => $cleanName,
                        'lat' => (float) $location->latitude,
                        'lon' => (float) $location->longitude,
                        'category' => $location->category,
                    ];
                }
            }

            $hotelCoords = null;
            if ($hotel && $hotel->lat !== null && $hotel->lon !== null) {
                $hotelCoords = [
                    'lat' => (float) $hotel->lat,
                    'lon' => (float) $hotel->lon,
                    'name' => $hotel->name,
                ];
            }

            $hasMapData = $hotelCoords !== null || count($placeMarkers) > 0;

            return view('plans.show', [
                'plan' => $plan,
                'hotel' => $hotel,
                'hotelCoords' => $hotelCoords,
                'placeMarkers' => $placeMarkers,
                'hasMapData' => $hasMapData,
                'aiEnabled' => (bool) config('services.groq.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading plan', [
                'message' => $e->getMessage(),
                'plan_id' => $plan->id,
                'endpoint' => 'show',
            ]);
            return redirect('/my-plans')->with('error', 'May error sa pag-load ng plan.');
        }
    }

    public function destroy(Plan $plan)
    {
        $userId = (int) Auth::id();

        // OWNERSHIP CHECK — kung hindi ng user ang plan, i-block
        if ((int) $plan->user_id !== $userId) {
            Log::warning('Unauthorized plan delete attempt', [
                'plan_id' => $plan->id,
                'plan_owner' => $plan->user_id,
                'attempted_by' => $userId,
            ]);
            return redirect('/planora')->with('error', 'Hindi mo ma-delete ang plan na ito.');
        }

        $planId = $plan->id;

        try {
            $plan->delete();

            Log::info('Plan deleted successfully', [
                'plan_id' => $planId,
                'user_id' => $userId,
            ]);

            return redirect('/my-plans')->with('success', 'The plan has been deleted.');
        } catch (\Exception $e) {
            Log::error('Error deleting plan', [
                'message' => $e->getMessage(),
                'plan_id' => $planId,
                'user_id' => $userId,
            ]);
            return redirect('/my-plans')->with('error', 'May error sa pag-delete ng plan.');
        }
    }

    /**
     * Rename a saved plan. The `return` field records which surface sent the
     * request so the traveller lands back where they started.
     */
    public function rename(Request $request, Plan $plan)
    {
        $userId = (int) Auth::id();

        if ((int) $plan->user_id !== $userId) {
            Log::warning('Unauthorized plan rename attempt', [
                'plan_id' => $plan->id,
                'plan_owner' => $plan->user_id,
                'attempted_by' => $userId,
            ]);
            return redirect('/planora')->with('error', 'Hindi mo ma-rename ang plan na ito.');
        }

        $validated = $request->validate([
            'title'  => 'nullable|string|max:120',
            'return' => 'nullable|in:profile,plan',
        ]);

        $surface = $validated['return'] ?? 'plan';
        // An empty title is stored as null, which makes display_title fall back
        // to the hotel name.
        $title = filled($validated['title'] ?? null) ? trim($validated['title']) : null;

        try {
            $plan->update(['title' => $title]);

            Log::info('Plan renamed', [
                'plan_id' => $plan->id,
                'user_id' => $userId,
                'has_custom_title' => $title !== null,
            ]);

            return $this->planSurface($surface, $plan, $userId)
                ->with('success', $title === null ? 'Plan title cleared.' : 'Plan renamed.');
        } catch (\Exception $e) {
            Log::error('Error renaming plan', [
                'message' => $e->getMessage(),
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ]);
            return $this->planSurface($surface, $plan, $userId)
                ->with('error', 'May error sa pag-rename ng plan.');
        }
    }

    /**
     * Rebuild a saved itinerary from the inputs it was created with.
     */
    public function regenerate(PlanoraService $planoraService, Plan $plan)
    {
        $userId = (int) Auth::id();

        if ((int) $plan->user_id !== $userId) {
            Log::warning('Unauthorized plan regenerate attempt', [
                'plan_id' => $plan->id,
                'plan_owner' => $plan->user_id,
                'attempted_by' => $userId,
            ]);
            return redirect('/planora')->with('error', 'Hindi mo ma-regenerate ang plan na ito.');
        }

        try {
            $result = $planoraService->regeneratePlan($plan);

            if (isset($result['error'])) {
                return redirect('/plans/' . $plan->id)->with('error', $result['error']);
            }

            Log::info('Plan regenerated', [
                'plan_id' => $plan->id,
                'user_id' => $userId,
                'ai_provider' => $result['ai_provider'],
            ]);

            return redirect('/plans/' . $plan->id)->with('success', 'Your itinerary has been regenerated.');
        } catch (\Exception $e) {
            Log::error('Error regenerating plan', [
                'message' => $e->getMessage(),
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ]);
            return redirect('/plans/' . $plan->id)->with('error', 'May error sa pag-regenerate ng plan.');
        }
    }

    /**
     * List all public plans (anonymized, no user identity).
     */
    public function publicIndex()
    {
        $plans = Plan::with('user')
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->get();

        // Resolve hotel coordinates for each plan's map markers
        $plans->each(function ($plan) {
            $hotel = Hotel::query()
                ->whereRaw('LOWER(name) = ?', [strtolower($plan->hotel_name)])
                ->first();

            $plan->hotelCoords = null;
            if ($hotel && $hotel->lat !== null && $hotel->lon !== null) {
                $plan->hotelCoords = [
                    'lat' => (float) $hotel->lat,
                    'lon' => (float) $hotel->lon,
                    'name' => $hotel->name,
                ];
            }
        });

        return view('public-plans.index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Show a specific public plan.
     */
    public function publicShow(Plan $plan)
    {
        // Only public plans are viewable here
        if ((int) $plan->is_public !== 1) {
            abort(404, 'Plan not found.');
        }

        $plan->load('user');

        $hotel = Hotel::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($plan->hotel_name)])
            ->first();

        // ── Map data for the public itinerary map ──
        // selected_places holds place name strings only (e.g. "SM Center Dagupan (Mall)"),
        // so we resolve each one against the curated locations table to get coordinates.
        $placeMarkers = [];
        foreach ($plan->selected_places ?? [] as $rawName) {
            $cleanName = preg_replace(
                '/\s*\((Restaurant|Mall|Beach|Tourist Spot)\)\s*$/i',
                '',
                trim((string) $rawName)
            );
            if ($cleanName === '') {
                continue;
            }
            $location = Location::where('name', $cleanName)->first();
            if ($location && $location->latitude !== null && $location->longitude !== null) {
                $placeMarkers[] = [
                    'name' => $cleanName,
                    'lat' => (float) $location->latitude,
                    'lon' => (float) $location->longitude,
                    'category' => $location->category,
                ];
            }
        }

        $hotelCoords = null;
        if ($hotel && $hotel->lat !== null && $hotel->lon !== null) {
            $hotelCoords = [
                'lat' => (float) $hotel->lat,
                'lon' => (float) $hotel->lon,
                'name' => $hotel->name,
            ];
        }

        return view('public-plans.show', [
            'plan' => $plan,
            'hotel' => $hotel,
            'hotelCoords' => $hotelCoords,
            'placeMarkers' => $placeMarkers,
            'hasMapData' => $hotelCoords !== null || count($placeMarkers) > 0,
        ]);
    }

    /**
     * Toggle public share status for the current user's plan.
     */
    public function toggleShare(Request $request, Plan $plan)
    {
        $userId = (int) Auth::id();

        // OWNERSHIP CHECK
        if ((int) $plan->user_id !== $userId) {
            Log::warning('Unauthorized share toggle attempt', [
                'plan_id' => $plan->id,
                'plan_owner' => $plan->user_id,
                'attempted_by' => $userId,
            ]);
            return redirect('/my-plans')->with('error', 'Hindi mo ma-toggle ang share status ng plan na ito.');
        }

        $isValid = $request->validate([
            'is_public' => 'required|boolean',
        ]);

        try {
            $plan->update(['is_public' => $isValid['is_public']]);
            Log::info('Plan share toggled', [
                'plan_id' => $plan->id,
                'user_id' => $userId,
                'is_public' => $isValid['is_public'],
            ]);

            return redirect('/my-plans')->with('success', $isValid['is_public']
                ? 'Plan na binigay mo sa publiko! Madaling i-visualize ng iba ang idea mo.'
                : 'Plan na hindi na naka-share sa publiko.');
        } catch (\Exception $e) {
            Log::error('Error toggling plan share', [
                'message' => $e->getMessage(),
                'plan_id' => $plan->id,
                'user_id' => $userId,
            ]);
            return redirect('/my-plans')->with('error', 'May error sa pag-toggle ng share status.');
        }
    }

    /**
     * Send the traveller back to whichever surface started the request.
     */
    private function planSurface(string $surface, Plan $plan, int $userId)
    {
        return $surface === 'profile'
            ? redirect('/my-plans')
            : redirect('/plans/' . $plan->id);
    }
}

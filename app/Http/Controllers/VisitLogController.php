<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\VisitLog;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Check-in / check-out logging for points of interest.
 *
 * Replaces the route closures that returned canned JSON without ever writing to
 * the database.
 */
class VisitLogController extends Controller
{
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'spot_id'   => 'nullable|integer|exists:locations,id',
            'spot_name' => 'required|string|max:255',
        ]);

        $name = trim(strip_tags((string) $validated['spot_name']));
        $userId = (int) auth()->id();

        try {
            $existing = $this->openVisit($userId, $name);

            if ($existing) {
                return response()->json([
                    'error' => "You are already checked in at {$name}.",
                ], 422);
            }

            $visit = VisitLog::create([
                'user_id'       => $userId,
                'location_id'   => $this->resolveLocationId($validated['spot_id'] ?? null, $name),
                'location_name' => $name,
                'checked_in_at' => now(),
            ]);

            Log::info('Visit check-in recorded', [
                'user_id' => $userId,
                'visit_id' => $visit->id,
                'location_name' => $name,
                'location_id' => $visit->location_id,
                'endpoint' => 'checkIn',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in recorded',
                'visit_id' => $visit->id,
                'checked_in_at' => $visit->checked_in_at->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Visit check-in failed', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'location_name' => $name,
                'endpoint' => 'checkIn',
            ]);
            return response()->json(['error' => 'Check-in failed. Please try again.'], 500);
        }
    }

    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'spot_id'   => 'nullable|integer|exists:locations,id',
            'spot_name' => 'required|string|max:255',
        ]);

        $name = trim(strip_tags((string) $validated['spot_name']));
        $userId = (int) auth()->id();

        try {
            $visit = $this->openVisit($userId, $name);

            if (!$visit) {
                return response()->json([
                    'error' => "You have not checked in at {$name} yet.",
                ], 422);
            }

            $checkedOutAt = now();

            $visit->checked_out_at = $checkedOutAt;
            // Timestamp arithmetic instead of diffInMinutes() so the result does
            // not depend on the Carbon version's sign/absolute semantics.
            $visit->duration_minutes = max(0, intdiv(
                $checkedOutAt->getTimestamp() - $visit->checked_in_at->getTimestamp(),
                60
            ));
            $visit->save();

            // Award points if the checked-out location is in the user's AI-generated plan
            $this->awardPointsForAIVisit($userId, $name, $visit->location_name);

            return response()->json([
                'success' => true,
                'message' => 'Check-out recorded',
                'visit_id' => $visit->id,
                'duration_minutes' => $visit->duration_minutes,
            ]);
        } catch (\Exception $e) {
            Log::error('Visit check-out failed', [
                'message' => $e->getMessage(),
                'user_id' => $userId,
                'location_name' => $name,
                'endpoint' => 'checkOut',
            ]);
            return response()->json(['error' => 'Check-out failed. Please try again.'], 500);
        }
    }

    /**
     * The current user's still-open visits, so the map can show a "Check Out"
     * button instead of "Check In" for places they are already at.
     */
    public function active(Request $request)
    {
        $visits = VisitLog::query()
            ->where('user_id', (int) auth()->id())
            ->open()
            ->orderBy('checked_in_at')
            ->get(['location_name', 'checked_in_at'])
            ->map(fn (VisitLog $visit) => [
                'location_name' => $visit->location_name,
                'checked_in_at' => $visit->checked_in_at->toIso8601String(),
            ]);

        return response()->json(['visits' => $visits]);
    }

    /**
     * The still-open visit for this user at this place, if any.
     */
    private function openVisit(int $userId, string $name): ?VisitLog
    {
        return VisitLog::query()
            ->where('user_id', $userId)
            ->atPlace($name)
            ->open()
            ->latest('checked_in_at')
            ->first();
    }

    /**
     * Award points if the checked-out location is in the user's AI-generated plan.
     */
    private function awardPointsForAIVisit(int $userId, string $spotName, string $locationName): void
    {
        // Configuration: points awarded for checking out from AI-suggested locations
        $pointsAward = 10; // Can be made configurable later

        // Get the user's latest plan that has AI recommendations
        $plan = Plan::where('user_id', $userId)
            ->whereNotNull('ai_recommendation')
            ->latest('id')
            ->first();

        if (!$plan) {
            return; // No AI plan, no points awarded
        }

        // Check if the selected_places array exists and contains the location name
        $selectedPlaces = $plan->selected_places ?? [];
        $locationLower = strtolower(trim($locationName));

        foreach ($selectedPlaces as $selectedPlace) {
            if (strtolower(trim($selectedPlace)) === $locationLower) {
                // Location is in the AI plan's selected places - award points
                $user = User::find($userId);
                if ($user) {
                    $user->points = ($user->points ?? 0) + $pointsAward;
                    $user->save();
                }
                break; // Only award points once per check-out
            }
        }
    }

    /**
     * Prefer the posted location id, else match the curated name. Null is a
     * valid outcome: Overpass-only spots have no row in `locations`.
     */
    private function resolveLocationId(?int $locationId, string $name): ?int
    {
        if ($locationId) {
            return $locationId;
        }

        return Location::query()
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->value('id');
    }
};
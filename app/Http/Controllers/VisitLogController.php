<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\VisitLog;
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

            // Recalculate user level based on new points total
            $user = User::find($userId);
            if ($user) {
                $user->recalculateLevel();
            }

            Log::info('Visit check-out recorded', [
                'user_id' => $userId,
                'visit_id' => $visit->id,
                'location_name' => $name,
                'duration_minutes' => $visit->duration_minutes,
                'endpoint' => 'checkOut',
            ]);

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
/**\n     * Award points if the checked-out location is in the user's AI-generated plan.\n     */\n    private function awardPointsForAIVisit(int $userId, string $spotName, string $locationName): void\n    {\n        // Configuration: points awarded for checking out from AI-suggested locations\n        $pointsAward = 10; // Can be made configurable later\n\n        // Get the user's latest plan that has AI recommendations\n        $plan = Plan::where('user_id', $userId)\n            ->whereNotNull('ai_recommendation')\n            ->latest('id')\n            ->first();\n\n        if (!$plan) {\n            return; // No AI plan, no points awarded\n        }\n\n        // Check if the selected_places array exists and contains the location name\n        $selectedPlaces = $plan->selected_places ?? [];\n        $locationLower = strtolower(trim($locationName));\n\n        foreach ($selectedPlaces as $selectedPlace) {
            if (strtolower(trim($selectedPlace)) === $locationLower) {
                // Location is in the AI plan's selected places - award points\n                $user = User::find($userId);\n                if ($user) {\n                    $user->points = ($user->points ?? 0) + $pointsAward;\n                    $user->save();\n                }\n                break; // Only award points once per check-out\n            }
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
}

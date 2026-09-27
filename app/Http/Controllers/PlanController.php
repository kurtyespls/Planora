<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Plan;
use App\Services\PlanoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PlanController extends Controller
{
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

            return view('plans.show', [
                'plan' => $plan,
                'hotel' => $hotel,
                // Regeneration is only meaningful when a real AI model is wired
                // up, so the button is disabled rather than silently a no-op.
                'aiEnabled' => (bool) config('services.groq.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading plan', [
                'message' => $e->getMessage(),
                'plan_id' => $plan->id,
                'endpoint' => 'show',
            ]);
            return redirect('/profile/' . $userId)->with('error', 'May error sa pag-load ng plan.');
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

            return redirect('/profile/' . $userId . '?tab=plans')->with('success', 'The plan has been deleted.');
        } catch (\Exception $e) {
            Log::error('Error deleting plan', [
                'message' => $e->getMessage(),
                'plan_id' => $planId,
                'user_id' => $userId,
            ]);
            return redirect('/profile/' . $userId . '?tab=plans')->with('error', 'May error sa pag-delete ng plan.');
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
     * Send the traveller back to whichever surface started the request.
     */
    private function planSurface(string $surface, Plan $plan, int $userId)
    {
        return $surface === 'profile'
            ? redirect('/profile/' . $userId . '?tab=plans')
            : redirect('/plans/' . $plan->id);
    }
}

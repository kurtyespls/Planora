<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\Location;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function index() {
        $hotels = Hotel::paginate(50);
        return view('admin.hotels', compact('hotels'));
    }

    public function users() {
        $users = User::paginate(50);
        $adminCount = User::where('role', 'admin')->count();
        $userCount = User::where('role', 'user')->count();
        return view('admin.users', compact('users', 'adminCount', 'userCount'));
    }

    public function showUser($id) {
        $user = User::withCount('plans')->findOrFail($id);
        return view('admin.user-detail', compact('user'));
    }

    public function destroyUser($id) {
        // Prevent admin from deleting themselves
        if (auth()->id() == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        try {
            $user = User::findOrFail($id);
            $user->delete();
            Log::info('User deleted by admin', [
                'admin_id' => auth()->id(),
                'deleted_user_id' => $id,
                'endpoint' => 'destroyUser',
            ]);
            return back()->with('success', 'User deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete user', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'user_id' => $id,
                'endpoint' => 'destroyUser',
            ]);
            return back()->with('error', 'An error occurred while deleting the user.');
        }
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_url' => 'required|string|max:2048',
            'price' => 'required|numeric|min:0|max:99999999',
            'rating' => 'nullable|numeric|min:0|max:10',
            'description' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'amenities' => 'nullable|string',
            'gallery' => 'nullable|string',
        ]);

        try {
            Hotel::create($validated);
            CacheService::clearHotelsCache();
            Log::info('Hotel created by admin', [
                'admin_id' => auth()->id(),
                'hotel_name' => $validated['name'],
                'endpoint' => 'store',
            ]);
            return back()->with('success', 'Hotel added!');
        } catch (\Exception $e) {
            Log::error('Failed to create hotel', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'endpoint' => 'store',
            ]);
            return back()->with('error', 'An error occurred while adding the hotel.');
        }
    }

    public function update(Request $request, $id) {
        try {
            $hotel = Hotel::findOrFail($id);
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'image_url' => 'required|string|max:2048',
                'price' => 'required|numeric|min:0|max:99999999',
                'rating' => 'nullable|numeric|min:0|max:10',
                'description' => 'nullable|string',
                'lat' => 'nullable|numeric|between:-90,90',
                'lon' => 'nullable|numeric|between:-180,180',
                'address' => 'nullable|string|max:500',
                'amenities' => 'nullable|string',
                'gallery' => 'nullable|string',
            ]);

            $hotel->update($validated);
            CacheService::clearHotelsCache();
            Log::info('Hotel updated by admin', [
                'admin_id' => auth()->id(),
                'hotel_id' => $id,
                'hotel_name' => $validated['name'],
                'endpoint' => 'update',
            ]);
            return back()->with('success', 'Hotel updated!');
        } catch (\Exception $e) {
            Log::error('Failed to update hotel', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'hotel_id' => $id,
                'endpoint' => 'update',
            ]);
            return back()->with('error', 'An error occurred while updating the hotel.');
        }
    }

    public function destroy($id) {
        try {
            $hotel = Hotel::findOrFail($id);
            $hotel->delete();
            CacheService::clearHotelsCache();
            Log::info('Hotel deleted by admin', [
                'admin_id' => auth()->id(),
                'hotel_id' => $id,
                'endpoint' => 'destroy',
            ]);
            return back()->with('success', 'Hotel deleted!');
        } catch (\Exception $e) {
            Log::error('Failed to delete hotel', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'hotel_id' => $id,
                'endpoint' => 'destroy',
            ]);
            return back()->with('error', 'An error occurred while deleting the hotel.');
        }
    }

    /**
     * Shared validation rules for a Location (point of interest). Kept in one
     * place so store/update never drift apart.
     *
     * @return array<string, mixed>
     */
    private function placeRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category' => ['required', 'string', Rule::in(array_keys(\App\Services\PlanoraService::PLACE_CATEGORY_LABELS))],
            'location' => 'nullable|string|max:500',
            'image_url' => 'nullable|string|max:2048',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'budgetPerDay' => 'required|numeric|min:0|max:99999999',
            'rating' => 'nullable|numeric|min:0|max:10',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function places()
    {
        $places = Location::orderBy('category')->orderBy('name')->paginate(50);
        $categories = \App\Services\PlanoraService::PLACE_CATEGORY_LABELS;
        return view('admin.places', compact('places', 'categories'));
    }

    public function storePlace(Request $request)
    {
        $validated = $request->validate($this->placeRules());

        try {
            Location::create($validated);
            CacheService::clearPlacesCache();
            Log::info('Place created by admin', [
                'admin_id' => auth()->id(),
                'place_name' => $validated['name'],
                'endpoint' => 'storePlace',
            ]);
            return back()->with('success', 'Place added!');
        } catch (\Exception $e) {
            Log::error('Failed to create place', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'endpoint' => 'storePlace',
            ]);
            return back()->with('error', 'An error occurred while adding the place.');
        }
    }

    public function updatePlace(Request $request, $id)
    {
        try {
            $place = Location::findOrFail($id);
            $validated = $request->validate($this->placeRules());

            $place->update($validated);
            CacheService::clearPlacesCache();
            Log::info('Place updated by admin', [
                'admin_id' => auth()->id(),
                'place_id' => $id,
                'place_name' => $validated['name'],
                'endpoint' => 'updatePlace',
            ]);
            return back()->with('success', 'Place updated!');
        } catch (\Exception $e) {
            Log::error('Failed to update place', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'place_id' => $id,
                'endpoint' => 'updatePlace',
            ]);
            return back()->with('error', 'An error occurred while updating the place.');
        }
    }

    public function destroyPlace($id)
    {
        try {
            $place = Location::findOrFail($id);
            $place->delete();
            CacheService::clearPlacesCache();
            Log::info('Place deleted by admin', [
                'admin_id' => auth()->id(),
                'place_id' => $id,
                'endpoint' => 'destroyPlace',
            ]);
            return back()->with('success', 'Place deleted!');
        } catch (\Exception $e) {
            Log::error('Failed to delete place', [
                'message' => $e->getMessage(),
                'admin_id' => auth()->id(),
                'place_id' => $id,
                'endpoint' => 'destroyPlace',
            ]);
            return back()->with('error', 'An error occurred while deleting the place.');
        }
    }
}
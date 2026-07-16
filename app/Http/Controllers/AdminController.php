<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
            'price' => 'required|string|max:50',
            'rating' => 'nullable|numeric|min:0|max:10',
            'description' => 'nullable|string',
            'lat' => 'nullable|numeric|between:-90,90',
            'lon' => 'nullable|numeric|between:-180,180',
            'address' => 'nullable|string|max:500',
            'amenities' => 'nullable|string',
            'gallery' => 'nullable|string',
        ]);

        // Normalize price: strip currency symbols, commas and spaces so "₱2,800" becomes "2800"
        $validated['price'] = preg_replace('/[^0-9.]/', '', $validated['price']);

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
                'price' => 'required|string|max:50',
                'rating' => 'nullable|numeric|min:0|max:10',
                'description' => 'nullable|string',
                'lat' => 'nullable|numeric|between:-90,90',
                'lon' => 'nullable|numeric|between:-180,180',
                'address' => 'nullable|string|max:500',
                'amenities' => 'nullable|string',
                'gallery' => 'nullable|string',
            ]);

            // Normalize price: strip currency symbols, commas and spaces so "₱2,800" becomes "2800"
            $validated['price'] = preg_replace('/[^0-9.]/', '', $validated['price']);

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
}
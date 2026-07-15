<?php

namespace App\Http\Controllers;

use App\Models\Hotel;
use App\Models\User;
use App\Services\CacheService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index() {
        $hotels = Hotel::all();
        return view('admin.hotels', compact('hotels'));
    }

    public function users() {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    public function destroyUser($id) {
        $user = User::findOrFail($id);
        // Prevent admin from deleting themselves
        if (auth()->id() == $id) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return back()->with('success', 'User deleted successfully!');
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

        Hotel::create($validated);
        CacheService::clearHotelsCache();
        return back()->with('success', 'Hotel added!');
    }

    public function update(Request $request, $id) {
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
        $hotel->update($validated);
        CacheService::clearHotelsCache();
        return back()->with('success', 'Hotel updated!');
    }

    public function destroy($id) {
        $hotel = Hotel::findOrFail($id);
        $hotel->delete();
        CacheService::clearHotelsCache();
        return back()->with('success', 'Hotel deleted!');
    }
}

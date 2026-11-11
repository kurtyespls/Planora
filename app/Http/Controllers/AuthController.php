<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'user',
            ]);

            Auth::login($user);

            Log::info('User registered', [
                'user_id' => $user->id,
                'email' => $user->email,
                'endpoint' => 'register',
            ]);

            return redirect('/dashboard')->with('success', 'Account created! Welcome to Planora.');
        } catch (\Exception $e) {
            Log::error('Registration failed', [
                'message' => $e->getMessage(),
                'email' => $request->email,
                'endpoint' => 'register',
            ]);
            return back()->withErrors(['email' => 'Registration failed. Please try again.'])->withInput();
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            Log::info('User logged in', [
                'user_id' => $user->id,
                'email' => $user->email,
                'endpoint' => 'login',
            ]);

            if ($user->role === 'admin') {
                return redirect()->intended('/admin/hotels');
            }

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Dashboard - authenticated users only
     * Shows profile, plan history, level/xp, and public plans from other users
     */
    public function dashboard()
    {
        try {
            $user = auth()->user();

            // Get user's own plans (limit to 10 most recent)
            $userPlans = $user->plans()
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // Get public plans from other users with user relation
            $otherPublicPlans = Plan::with('user')
                ->where('is_public', true)
                ->where('user_id', '!=', $user->id)
                ->orderBy('created_at', 'desc')
                ->take(12)
                ->get();

            // Total counts for dashboard metrics
            $userPlansCount = $user->plans()->count();
            $userPublicPlansCount = $user->plans()->where('is_public', true)->count();
            $totalCommunityPlansCount = Plan::where('is_public', true)->count();

            // Calculate level progress (based on User model recalculateLevel logic)
            $points = $user->points ?? 0;
            if ($points >= 1000) {
                $level = 5;
                $levelProgress = $points - 1000;
                $nextLevelPoints = 1000;
            } elseif ($points >= 500) {
                $level = 4;
                $levelProgress = $points - 500;
                $nextLevelPoints = 500;
            } elseif ($points >= 250) {
                $level = 3;
                $levelProgress = $points - 250;
                $nextLevelPoints = 250;
            } elseif ($points >= 100) {
                $level = 2;
                $levelProgress = $points - 100;
                $nextLevelPoints = 100;
            } else {
                $level = 1;
                $levelProgress = $points;
                $nextLevelPoints = 100;
            }

            // Level roadmap details
            $levelDetails = [
                1 => ['level' => 1, 'name' => 'Beginner', 'color' => '#7C9187', 'bg' => '#F6EDE0', 'badge' => 'Starter Scout', 'difficulty' => 'Easy', 'min_points' => 0, 'description' => 'Embarking on the Dagupan travel adventure.'],
                2 => ['level' => 2, 'name' => 'Explorer', 'color' => '#D9622B', 'bg' => '#FFF3E0', 'badge' => 'Active Voyager', 'difficulty' => 'Moderate', 'min_points' => 100, 'description' => 'Discovering curated routes and local food spots.'],
                3 => ['level' => 3, 'name' => 'Adventure Seeker', 'color' => '#0B3D3A', 'bg' => '#E3F2FD', 'badge' => 'Seasoned Wanderer', 'difficulty' => 'Challenging', 'min_points' => 250, 'description' => 'Mastering multi-day itineraries and budgets.'],
                4 => ['level' => 4, 'name' => 'Travel Expert', 'color' => '#1B2624', 'bg' => '#F3E8FD', 'badge' => 'City Connoisseur', 'difficulty' => 'Expert', 'min_points' => 500, 'description' => 'Inspiring other travelers with public plans.'],
                5 => ['level' => 5, 'name' => 'Master Traveler', 'color' => '#B94E1F', 'bg' => '#EDE0CB', 'badge' => 'Legendary Voyager', 'difficulty' => 'Legendary', 'min_points' => 1000, 'description' => 'Ultimate Dagupan explorer with master status.'],
            ];

            $currentLevelData = $levelDetails[$level] ?? $levelDetails[1];
            $nextLevelData = $levelDetails[$level + 1] ?? $levelDetails[5];
            $progressPercent = $nextLevelPoints > 0 ? min(100, max(5, round(($levelProgress / $nextLevelPoints) * 100))) : 100;

            return view('dashboard.index', [
                'user' => $user,
                'userPlans' => $userPlans,
                'userPlansCount' => $userPlansCount,
                'userPublicPlansCount' => $userPublicPlansCount,
                'totalCommunityPlansCount' => $totalCommunityPlansCount,
                'otherPublicPlans' => $otherPublicPlans,
                'level' => $level,
                'points' => $points,
                'levelProgress' => $levelProgress,
                'nextLevelPoints' => $nextLevelPoints,
                'progressPercent' => $progressPercent,
                'currentLevelData' => $currentLevelData,
                'nextLevelData' => $nextLevelData,
                'levelDetails' => $levelDetails,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to load dashboard', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'dashboard',
            ]);

            return redirect('/planora')->with('error', 'Unable to load dashboard. Please try again.');
        }
    }

    public function logout(Request $request)
    {
        $userId = auth()->id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('User logged out', [
            'user_id' => $userId,
            'endpoint' => 'logout',
        ]);

        return redirect('/login');
    }

    // ── Forgot Password Methods ──
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __('A password reset link has been sent to your email.'))
            : back()->withErrors(['email' => __('We couldn\'t find an account with that email.')]);
    }

    public function showResetPassword($token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => request('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __('Your password has been reset! You can now log in.'))
            : back()->withErrors(['email' => __('Invalid or expired reset token.')]);
    }

    public function showProfile($id)
    {
        // Authorization: users can only view their own profile
        if ((int) $id !== (int) auth()->id()) {
            Log::warning('Unauthorized profile view attempt', [
                'requested_user_id' => $id,
                'auth_user_id' => auth()->id(),
                'endpoint' => 'showProfile',
            ]);
            return redirect('/dashboard')->with('error', 'You are not authorized to view this profile.');
        }

        try {
            $user = User::with('plans')->findOrFail($id);
            return view('profile.show', [
                'user' => $user,
                // Drives the Regenerate button: disabled when no AI model is
                // configured, so it never silently produces the same itinerary.
                'aiEnabled' => (bool) config('services.groq.key'),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load profile', [
                'message' => $e->getMessage(),
                'user_id' => $id,
                'endpoint' => 'showProfile',
            ]);
            return redirect('/dashboard')->with('error', 'Unable to load profile.');
        }
    }

    public function updateProfile(Request $request, $id)
    {
        // Authorization: users can only update their own profile
        if ((int) $id !== (int) auth()->id()) {
            Log::warning('Unauthorized profile update attempt', [
                'requested_user_id' => $id,
                'auth_user_id' => auth()->id(),
                'endpoint' => 'updateProfile',
            ]);
            return redirect('/dashboard')->with('error', 'You are not authorized to update this profile.');
        }

        try {
            $user = User::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $user->name = $request->name;
            $user->email = $request->email;

            if ($request->filled('password')) {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            Log::info('Profile updated', [
                'user_id' => $id,
                'endpoint' => 'updateProfile',
            ]);

            return back()->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            Log::error('Profile update failed', [
                'message' => $e->getMessage(),
                'user_id' => $id,
                'endpoint' => 'updateProfile',
            ]);
            return back()->withErrors(['error' => 'Failed to update profile. Please try again.']);
        }
    }
}
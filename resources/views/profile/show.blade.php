<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>Planora — User Profile</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .top-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255,253,249,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--line);
        }
        .top-nav-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
        }
        .top-nav .brand { font-size: 1.1rem; gap: 8px; }
        .top-nav .brand-mark { width: 32px; height: 32px; font-size: 0.9rem; }
        .top-nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--ink);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .top-nav-link:hover { background: var(--sand-deep); color: var(--deep-teal); }

        .profile-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            overflow: hidden;
        }

        .avatar-xl {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--deep-teal), var(--ember));
            color: var(--sand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 2.2rem;
            box-shadow: 0 8px 30px rgba(11,61,58,0.3);
            border: 4px solid var(--card);
            margin: 0 auto;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-admin { background: rgba(11,61,58,0.12); color: var(--deep-teal); border: 1px solid rgba(11,61,58,0.15); }
        .badge-user { background: rgba(124,145,135,0.15); color: var(--sage); border: 1px solid rgba(124,145,135,0.15); }

        .field {
            background: var(--card);
            border: 1.5px solid var(--line);
            color: var(--ink);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            border-radius: var(--radius-sm);
            width: 100%;
            padding: 12px 16px;
            font-size: 0.9rem;
        }
        .field:focus {
            border-color: var(--deep-teal);
            box-shadow: 0 0 0 3px rgba(11,61,58,0.08), 0 0 0 6px rgba(11,61,58,0.04);
            outline: none;
        }

        .btn-primary {
            background: var(--deep-teal);
            color: var(--sand);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-sm);
            font-weight: 700;
            padding: 14px 24px;
            border: none;
            cursor: pointer;
            width: 100%;
            font-size: 0.95rem;
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--deep-teal-ink);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -8px rgba(11,61,58,0.5);
        }
        .btn-primary::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            transform: translateX(-100%);
        }
        .btn-primary:hover::after { transform: translateX(100%); transition: transform 0.6s; }

        .plan-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-md);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .plan-card:hover {
            border-color: var(--ember);
            box-shadow: var(--shadow-lg);
            transform: translateY(-3px);
        }

        .plan-badge {
            background: var(--sand-deep);
            color: var(--deep-teal);
            padding: 0.3rem 0.85rem;
            border-radius: 0.5rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }

        @keyframes tabIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .tab-content { animation: tabIn 0.35s ease-out; }

        .tab-btn {
            padding: 10px 24px;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            cursor: pointer;
            border: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .tab-btn.active {
            background: var(--deep-teal);
            color: var(--sand);
            box-shadow: var(--shadow-md);
        }
        .tab-btn:not(.active) {
            background: var(--card);
            color: var(--ink);
            border: 1px solid var(--line);
        }
        .tab-btn:not(.active):hover {
            border-color: var(--deep-teal);
            color: var(--deep-teal);
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 6px;
        }

        .input-hint {
            font-size: 0.75rem;
            color: var(--sage);
            margin-top: 4px;
        }
    </style>
</head>
<body class="min-h-screen px-4 pb-8 md:px-8" x-data="{ activeTab: 'profile', showProfilePanel: false }">
    
    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand"><span class="brand-mark">⌁</span><span>planora</span></a>
            <div class="flex items-center gap-3">
                <a href="/planora" class="top-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    Back
                </a>
                <button @click="showProfilePanel = true" type="button" class="top-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a7 7 0 1 0-14 0"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </button>
            </div>
        </div>
    </nav>
    
    <!-- Profile Slide Panel -->
    <div x-show="showProfilePanel" x-cloak @click.away="showProfilePanel = false" 
         class="fixed inset-0 z-50 overflow-hidden">
        <div class="absolute inset-0 bg-black/30" @click="showProfilePanel = false"></div>
        <div class="absolute top-0 left-0 h-full w-80 max-w-[90%] bg-[var(--card)] border-r border-[var(--line)] shadow-2xl transform transition-transform duration-300"
             :class="{ '-translate-x-full': !showProfilePanel, 'translate-x-0': showProfilePanel }">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-display text-xl font-semibold text-[var(--ink)]">My Profile</h2>
                    <button @click="showProfilePanel = false" class="p-2 rounded-lg hover:bg-[var(--sand-deep)] transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <a href="/profile/{{ auth()->id() }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--sand-deep)] transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 10-8 0 4 4 0 008 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profile Settings</span>
                    </a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl text-[var(--ember-deep)] hover:bg-[var(--sand-deep)] transition">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Log out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="w-full max-w-lg mx-auto mt-6 animate-fade-in-up">
        <!-- Profile Card -->
        <div class="profile-card p-8">
            <!-- Avatar & Name -->
            <div class="text-center mb-8">
                <div class="avatar-xl mb-4">{{ substr($user->name, 0, 2) }}</div>
                <h1 class="font-display text-2xl font-bold text-[var(--ink)]">{{ $user->name }}</h1>
                <p class="text-[var(--ink-soft)] text-sm mt-1">{{ $user->email }}</p>
                <div class="mt-3">
                    <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex gap-2 mb-6 justify-center">
                <button @click="activeTab = 'profile'" :class="{ 'active': activeTab === 'profile' }" class="tab-btn">
                    <span class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 10-8 0 4 4 0 008 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profile Settings
                    </span>
                </button>
                <button @click="activeTab = 'plans'" :class="{ 'active': activeTab === 'plans' }" class="tab-btn">
                    <span class="flex items-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        My Plans
                    </span>
                </button>
            </div>
            
            <!-- Profile Tab -->
            <div x-show="activeTab === 'profile'" x-cloak class="tab-content">
                @if(auth()->id() === $user->id)
                    @if(session('success'))
                    <div class="mb-5 p-4 rounded-xl flex items-center gap-2" style="background:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;font-size:0.85rem;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01 5 7.01"/></svg>
                        {{ session('success') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div class="mb-5 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.85rem;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                            <span style="font-weight:600;">Please fix the following:</span>
                        </div>
                        @foreach ($errors->all() as $error)
                            <p style="padding-left:24px;margin-bottom:2px;">• {{ $error }}</p>
                        @endforeach
                    </div>
                    @endif
                    
                    <form action="/profile/{{ $user->id }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-5">
                            <div>
                                <label class="form-label" for="name">Full Name</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="field" required>
                            </div>
                            
                            <div>
                                <label class="form-label" for="email">Email Address</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="field" required>
                            </div>
                            
                            <div class="border-t border-[var(--line)] pt-5">
                                <h3 class="font-display text-lg font-semibold text-[var(--ink)] mb-4">Change Password</h3>
                                <p class="text-sm text-[var(--ink-soft)] mb-4">Leave blank to keep your current password.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="form-label" for="password">New Password</label>
                                        <input type="password" name="password" id="password" class="field" minlength="6" placeholder="Enter new password" autocomplete="new-password">
                                        <p class="input-hint">Minimum 6 characters</p>
                                    </div>
                                    <div>
                                        <label class="form-label" for="password_confirmation">Confirm New Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="field" minlength="6" placeholder="Confirm new password" autocomplete="new-password">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="btn-primary flex items-center justify-center gap-2">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21V7H7v14"/><path d="M7 3v4"/><path d="M12 12v6"/><path d="M15 12v6"/></svg>
                                Save Changes
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Read-only view for admin viewing another user -->
                    <div class="space-y-5">
                        <div>
                            <label class="form-label">Full Name</label>
                            <div class="p-3 rounded-xl text-sm" style="background:var(--sand-deep);color:var(--ink);">{{ $user->name }}</div>
                        </div>
                        
                        <div>
                            <label class="form-label">Email Address</label>
                            <div class="p-3 rounded-xl text-sm" style="background:var(--sand-deep);color:var(--ink);">{{ $user->email }}</div>
                        </div>
                        
                        <div>
                            <label class="form-label">Role</label>
                            <div class="p-3 rounded-xl text-sm" style="background:var(--sand-deep);color:var(--ink);display:flex;align-items:center;gap:8px;">
                                <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label">Member Since</label>
                            <div class="p-3 rounded-xl text-sm" style="background:var(--sand-deep);color:var(--ink);">{{ $user->created_at->format('F d, Y') }}</div>
                        </div>
                    </div>
                    
                    <div class="mt-6 p-4 rounded-xl text-sm text-center" style="background:rgba(124,145,135,0.1);color:var(--ink-soft);">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline;vertical-align:middle;margin-right:6px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                        Viewing <strong>{{ $user->name }}</strong>'s profile — read-only
                    </div>
                @endif
                
                <!-- Account Info -->
                <div class="mt-8 pt-6 border-t border-[var(--line)]">
                    <h3 class="font-display text-lg font-semibold text-[var(--ink)] mb-4">Account Information</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        <div class="p-4 rounded-xl" style="background:var(--sand-deep);">
                            <span class="block text-[var(--sage)] text-xs font-mono uppercase tracking-wider mb-1">Member since</span>
                            <span class="font-semibold text-[var(--ink)]">{{ $user->created_at->format('F d, Y') }}</span>
                        </div>
                        <div class="p-4 rounded-xl" style="background:var(--sand-deep);">
                            <span class="block text-[var(--sage)] text-xs font-mono uppercase tracking-wider mb-1">Last updated</span>
                            <span class="font-semibold text-[var(--ink)]">{{ $user->updated_at->format('F d, Y') }}</span>
                        </div>
                        <div class="p-4 rounded-xl" style="background:var(--sand-deep);">
                            <span class="block text-[var(--sage)] text-xs font-mono uppercase tracking-wider mb-1">Email status</span>
                            <span class="font-semibold {{ $user->email_verified_at ? 'text-[var(--deep-teal)]' : 'text-[var(--sage)]' }}">
                                {{ $user->email_verified_at ? '✓ Verified' : 'Unverified' }}
                            </span>
                        </div>
                        <div class="p-4 rounded-xl" style="background:var(--sand-deep);">
                            <span class="block text-[var(--sage)] text-xs font-mono uppercase tracking-wider mb-1">User ID</span>
                            <span class="font-mono text-[var(--deep-teal)] font-bold">#{{ $user->id }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Plans Tab -->
            <div x-show="activeTab === 'plans'" x-cloak class="tab-content">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="font-display text-xl font-semibold text-[var(--ink)]">My Saved Plans</h2>
                    <span class="plan-badge">{{ $user->plans->count() }} plan{{ $user->plans->count() !== 1 ? 's' : '' }}</span>
                </div>
                
                @if($user->plans->isEmpty())
                <div class="text-center py-10">
                    <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-[var(--sand-deep)] flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-[var(--sage)]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <p class="text-[var(--ink)] font-semibold mb-1">No saved plans yet</p>
                    <p class="text-sm text-[var(--sage)] mb-6">Create your first travel plan to see it here</p>
                    <a href="/planora" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold" style="width:auto;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                        Create a Plan
                    </a>
                </div>
                @else
                <div class="space-y-4">
                    @foreach($user->plans->sortByDesc('created_at') as $plan)
                    <div class="plan-card p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-display font-semibold text-lg text-[var(--ink)] mb-2">{{ $plan->hotel_name }}</h3>
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-[var(--ink-soft)]">
                                    <span class="flex items-center gap-1.5">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span class="font-mono font-semibold">{{ $plan->total_days }} day{{ $plan->total_days !== 1 ? 's' : '' }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1"/><line x1="12" y1="23"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z"/><path d="M12 2v1"/></svg>
                                        <span class="font-mono font-semibold">₱{{ number_format($plan->budget) }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                        {{ $plan->created_at->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                            <button onclick="viewPlan('{{ $plan->id }}')" class="btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 whitespace-nowrap" style="width:auto;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <script>
        function viewPlan(planId) {
            showToast('Plan details coming soon!', 'info', 3000);
        }
        
        function showToast(message, type = 'info', duration = 4000) {
            const toast = document.createElement('div');
            const colors = {
                success: 'bg-green-50 text-green-700 border border-green-200',
                error: 'bg-red-50 text-red-700 border border-red-200',
                warning: 'bg-amber-50 text-amber-700 border border-amber-200',
                info: 'bg-blue-50 text-blue-700 border border-blue-200'
            };
            toast.className = `fixed top-5 right-5 z-50 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-2 shadow-xl animate-fade-in-up ${colors[type] || colors.info}`;
            toast.innerHTML = `<span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
    </script>
</body>
</html>
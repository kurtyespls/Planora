<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings — Planora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        [x-cloak] { display: none !important; }

        body {
            background-color: var(--sand);
            background-image:
                radial-gradient(circle at 10% 10%, rgba(11, 61, 58, 0.05) 0%, transparent 45%),
                radial-gradient(circle at 90% 85%, rgba(217, 98, 43, 0.06) 0%, transparent 40%);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            min-height: 100vh;
        }

        .font-display { font-family: 'DM Serif Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Top Navigation */
        .top-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 253, 249, 0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--line);
        }
        .top-nav-inner {
            max-width: 1180px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--deep-teal);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }
        .brand-mark {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            box-shadow: 0 4px 12px rgba(11, 61, 58, 0.2);
        }
        .top-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9999px;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--ink-soft);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .top-nav-link:hover { color: var(--deep-teal); background: var(--sand-deep); }
        .top-nav-link.active {
            background: var(--deep-teal);
            color: var(--sand);
            box-shadow: 0 4px 12px rgba(11, 61, 58, 0.15);
        }

        /* Profile Card */
        .profile-container {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 24px;
            box-shadow: 0 4px 24px rgba(11, 61, 58, 0.06);
        }

        .avatar-lg {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ember), var(--ember-deep));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.8rem;
            box-shadow: 0 8px 24px rgba(217, 98, 43, 0.3);
            border: 3px solid var(--card);
            margin: 0 auto;
        }

        .tab-btn {
            padding: 10px 20px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            border: 1.5px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .tab-btn:hover {
            background: var(--sand-deep);
            color: var(--deep-teal);
        }
        .tab-btn.active {
            background: var(--deep-teal);
            color: var(--sand);
            border-color: var(--deep-teal);
            box-shadow: 0 4px 12px rgba(11, 61, 58, 0.18);
        }

        .field-input {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--line);
            background: var(--card);
            font-size: 0.9rem;
            color: var(--ink);
            outline: none;
            transition: all 0.2s ease;
        }
        .field-input:focus {
            border-color: var(--deep-teal);
            box-shadow: 0 0 0 3px rgba(11, 61, 58, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(11, 61, 58, 0.25);
            color: white;
        }

        .plan-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: var(--space-4);
            transition: var(--transition-base);
        }
        .plan-card::before {
            content: ''; position: absolute; inset: 0; border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(11,61,58,0.03) 0%, transparent 50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        .plan-card:hover { border-color: var(--ember); box-shadow: var(--shadow-lg); }
        .plan-card:hover::before { opacity: 1; }
        .plan-card:focus-visible { outline: 2px solid var(--deep-teal); outline-offset: -4px; }
        .plan-card:focus-visible::before { opacity: 1; }
    </style>
</head>
<body x-data="{ activeTab: '{{ request()->query('tab') === 'plans' ? 'plans' : 'profile' }}', mobileMenuOpen: false }" x-cloak class="flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand">
                <span class="brand-mark">⌁</span>
                <span class="font-display">planora</span>
            </a>

            <!-- Desktop Links -->
            <div class="hidden md:flex items-center gap-2">
                <a href="/planora" class="top-nav-link">Trip Planner</a>
                <a href="/dashboard" class="top-nav-link">Dashboard</a>
                <a href="/my-plans" class="top-nav-link">My Plans</a>
                <a href="/public-plans" class="top-nav-link">Public Plans</a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-3">
                <a href="/profile/{{ $user->id }}" class="top-nav-link active">
                    <span>{{ $user->name }}</span>
                </a>
                <form method="POST" action="/logout" class="m-0 hidden sm:inline-block">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-full text-[var(--ink-soft)] hover:text-red-700 hover:bg-red-50 transition">
                        Sign Out
                    </button>
                </form>
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden border-t border-[var(--line)] bg-[var(--card)] px-4 py-3 space-y-2">
            <a href="/planora" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)]">Trip Planner</a>
            <a href="/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)]">Dashboard</a>
            <a href="/my-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)]">My Plans</a>
            <a href="/public-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)]">Public Plans</a>
            <form method="POST" action="/logout" class="pt-2 border-t border-[var(--line)]">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-red-600">Sign Out</button>
            </form>
        </div>
    </nav>

    <!-- Main Container -->
    <main class="max-w-2xl w-full mx-auto px-4 py-8 flex-1">
        
        <div class="profile-container p-6 sm:p-10">

            <!-- Avatar & Greeting -->
            <div class="text-center mb-8">
                <div class="avatar-lg mb-4 animate-float-gentle ring-4 ring-[var(--sand-deep)] shadow-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h1 class="font-display text-2xl sm:text-3xl font-bold text-[var(--ink)]">{{ $user->name }}</h1>
                <p class="text-sm text-[var(--ink-soft)] mt-1">{{ $user->email }}</p>
                <div class="mt-2 flex items-center justify-center gap-2">
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-[var(--deep-teal)] text-[var(--sand)] animate-teal-glow">
                        Level {{ $user->level ?? 1 }}
                    </span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-semibold bg-[var(--sand-deep)] text-[var(--ink)]">
                        {{ $user->points ?? 0 }} XP
                    </span>
                </div>
            </div>

            <!-- Tab Buttons -->
            <div class="flex items-center justify-center gap-3 mb-8">
                <button type="button" @click="activeTab = 'profile'" :class="{ 'active': activeTab === 'profile' }" class="tab-btn btn-press flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Profile Settings
                </button>
                <button type="button" @click="activeTab = 'plans'" :class="{ 'active': activeTab === 'plans' }" class="tab-btn btn-press flex items-center gap-2">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                    My Plans ({{ $user->plans->count() }})
                </button>
            </div>

            <!-- Flash alerts -->
            @if(session('success'))
                <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PROFILE TAB CONTENT -->
            <div x-show="activeTab === 'profile'" x-cloak>
                @if(auth()->id() === $user->id)
                    <form action="/profile/{{ $user->id }}" method="POST" class="space-y-5">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider mb-1.5" for="name">
                                Full Name
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="field-input" required>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider mb-1.5" for="email">
                                Email Address
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="field-input" required>
                        </div>

                        <div class="border-t border-[var(--line)] pt-5">
                            <h3 class="font-display text-lg font-bold text-[var(--ink)] mb-1">Update Password</h3>
                            <p class="text-xs text-[var(--ink-soft)] mb-4">Leave fields blank if you do not want to change your password.</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider mb-1" for="password">
                                        New Password
                                    </label>
                                    <input type="password" name="password" id="password" class="field-input" minlength="6" placeholder="Enter new password (optional)" autocomplete="new-password">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider mb-1" for="password_confirmation">
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="field-input" minlength="6" placeholder="Confirm new password" autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="btn-primary">
                                Save Profile Changes
                            </button>
                        </div>
                    </form>
                @else
                    <div class="space-y-4 text-sm">
                        <div class="p-4 rounded-xl bg-[var(--sand)]">
                            <span class="text-xs font-bold uppercase text-[var(--ink-soft)] block mb-1">Name</span>
                            <span class="font-bold text-[var(--ink)]">{{ $user->name }}</span>
                        </div>
                        <div class="p-4 rounded-xl bg-[var(--sand)]">
                            <span class="text-xs font-bold uppercase text-[var(--ink-soft)] block mb-1">Email</span>
                            <span class="font-bold text-[var(--ink)]">{{ $user->email }}</span>
                        </div>
                    </div>
                @endif

                <!-- Account Information Summary -->
                <div class="mt-8 pt-6 border-t border-[var(--line)]">
                    <h3 class="font-display text-base font-bold text-[var(--ink)] mb-3">Account Details</h3>
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div class="p-3 rounded-xl bg-[var(--sand)] border border-[var(--line)]">
                            <span class="text-[var(--sage)] block mb-0.5">Member Since</span>
                            <span class="font-bold text-[var(--ink)]">{{ $user->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="p-3 rounded-xl bg-[var(--sand)] border border-[var(--line)]">
                            <span class="text-[var(--sage)] block mb-0.5">Role</span>
                            <span class="font-bold text-[var(--deep-teal)] capitalize">{{ $user->role ?? 'Traveler' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MY PLANS TAB CONTENT (restored) -->
            <div x-show="activeTab === 'plans'" x-cloak class="space-y-4">
                @if($user->plans->count() > 0)
                    @foreach($user->plans as $plan)
                        <div class="plan-card card-interactive flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <h3 class="font-display font-bold text-base text-[var(--ink)] truncate">
                                    {{ $plan->display_title }}
                                </h3>
                                <p class="text-xs text-[var(--ink-soft)] mt-0.5">
                                    PHP {{ number_format($plan->budget, 0) }} • {{ $plan->total_days }} {{ Str::plural('day', $plan->total_days) }}
                                </p>
                            </div>
                            <a href="/plans/{{ $plan->id }}" class="shrink-0 btn-press px-3.5 py-1.5 rounded-lg text-xs font-bold bg-[var(--deep-teal)] text-[var(--sand)] hover:bg-[var(--deep-teal-ink)] transition shadow-sm">
                                View Itinerary →
                            </a>
                        </div>
                    @endforeach
                    <div class="pt-2 text-center">
                        <a href="/my-plans" class="text-xs font-bold text-[var(--deep-teal)] hover:underline">
                            Open full My Plans page →
                        </a>
                    </div>
                @else
                    <div class="text-center py-8 text-sm text-[var(--ink-soft)]">
                        No plans created yet.
                        <div class="mt-3">
                            <a href="/planora" class="btn-primary py-2 px-4 text-xs inline-flex w-auto">
                                Plan a Trip
                            </a>
                        </div>
                    </div>
                @endif
            </div>

        </div>

    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[var(--line)] bg-[var(--card)] py-6 text-center text-xs text-[var(--ink-soft)]">
        <div class="max-w-[1180px] mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-display font-bold text-[var(--deep-teal)]">planora</span>
                <span>— Dagupan City Smart Travel Companion</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/planora" class="hover:text-[var(--deep-teal)]">Planner</a>
                <a href="/dashboard" class="hover:text-[var(--deep-teal)]">Dashboard</a>
                <a href="/my-plans" class="hover:text-[var(--deep-teal)]">My Plans</a>
            </div>
        </div>
    </footer>

</body>
</html>

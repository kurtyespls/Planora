<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planora — Traveler Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            color: var(--ink);
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
            background: transparent;
            border: 1px solid transparent;
        }
        .top-nav-link:hover {
            color: var(--deep-teal);
            background: var(--sand-deep);
        }
        .top-nav-link.active {
            background: var(--deep-teal);
            color: var(--sand);
            box-shadow: 0 4px 12px rgba(11, 61, 58, 0.15);
        }
        .top-nav-link-admin {
            background: rgba(11, 61, 58, 0.08);
            border: 1px solid rgba(11, 61, 58, 0.18);
            color: var(--deep-teal);
        }
        .top-nav-link-admin:hover {
            background: var(--deep-teal);
            color: var(--sand);
        }

        /* Buttons */
        .btn-primary {
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.9rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            border: none;
            box-shadow: 0 4px 14px rgba(11, 61, 58, 0.18);
            cursor: pointer;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(11, 61, 58, 0.28);
            color: white;
        }
        .btn-secondary {
            background: var(--card);
            border: 1.5px solid var(--line);
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background: var(--sand-deep);
            border-color: var(--sage);
            color: var(--deep-teal);
            transform: translateY(-1px);
        }

        /* Hero Passport Card */
        .passport-card {
            background: linear-gradient(135deg, #0B3D3A 0%, #0E2E2B 65%, #184844 100%);
            border-radius: 24px;
            color: var(--sand);
            position: relative;
            overflow: hidden;
            box-shadow: 0 16px 40px -10px rgba(11, 61, 58, 0.35);
        }
        .passport-card::before {
            content: "";
            position: absolute;
            top: -50%;
            right: -20%;
            width: 420px;
            height: 420px;
            background: radial-gradient(circle, rgba(217, 98, 43, 0.22) 0%, transparent 70%);
            pointer-events: none;
        }
        .passport-card::after {
            content: "DAGUPAN";
            position: absolute;
            bottom: -20px;
            right: 24px;
            font-size: 5rem;
            font-weight: 900;
            letter-spacing: 0.2em;
            color: rgba(255, 255, 255, 0.03);
            pointer-events: none;
            user-select: none;
            font-family: 'DM Serif Display', serif;
        }

        /* Stat Cards */
        .stat-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 20px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(11, 61, 58, 0.04);
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px -6px rgba(11, 61, 58, 0.1);
            border-color: rgba(217, 98, 43, 0.3);
        }

        /* Tabs Navigation */
        .tab-nav-btn {
            padding: 10px 22px;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.25s ease;
            border: 1.5px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
            cursor: pointer;
        }
        .tab-nav-btn:hover {
            background: var(--sand-deep);
            color: var(--deep-teal);
            border-color: var(--line);
        }
        .tab-nav-btn.active {
            background: var(--deep-teal);
            color: var(--sand);
            border-color: var(--deep-teal);
            box-shadow: 0 4px 14px rgba(11, 61, 58, 0.2);
        }

        /* Plan Cards */
        .plan-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            transition: var(--transition-base);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
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

        /* Badges */
        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge-public {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .badge-private {
            background: rgba(124, 145, 135, 0.12);
            color: var(--ink-soft);
            border: 1px solid rgba(124, 145, 135, 0.2);
        }
        .badge-ai {
            background: rgba(217, 98, 43, 0.12);
            color: var(--ember-deep);
            border: 1px solid rgba(217, 98, 43, 0.25);
        }
        .badge-local {
            background: rgba(11, 61, 58, 0.1);
            color: var(--deep-teal);
            border: 1px solid rgba(11, 61, 58, 0.2);
        }

        /* Level Roadmap Item */
        .roadmap-card {
            background: var(--card);
            border: 1.5px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            transition: all 0.25s ease;
        }
        .roadmap-card.current {
            border-color: var(--ember);
            background: linear-gradient(135deg, var(--card) 0%, rgba(217, 98, 43, 0.05) 100%);
            box-shadow: 0 6px 20px rgba(217, 98, 43, 0.12);
        }
        .roadmap-card.unlocked {
            border-color: var(--deep-teal);
        }

        /* Avatar */
        .user-avatar-circle {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--ember), var(--ember-deep));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            box-shadow: 0 4px 12px rgba(217, 98, 43, 0.25);
        }
    </style>
</head>
<body x-data="dashboard()" x-cloak class="flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand" title="Planora Home">
                <span class="brand-mark">⌁</span>
                <span class="font-display">planora</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-2">
                <a href="/planora" class="top-nav-link" title="Open Trip Planner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Trip Planner
                </a>
                <a href="/dashboard" class="top-nav-link active" title="Traveler Dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="/my-plans" class="top-nav-link" title="My Saved Itineraries">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                    My Plans
                </a>
                <a href="/public-plans" class="top-nav-link" title="Community Shared Plans">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Public Plans
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="/admin/hotels" class="top-nav-link top-nav-link-admin" title="Admin Control Center">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 12-8-8-4-12-8-8 4-12 8m0 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 22m-7-7h14"/></svg>
                        Admin Panel
                    </a>
                @endif
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-3">
                <a href="/profile/{{ $user->id }}" class="flex items-center gap-2.5 p-1.5 pl-3 rounded-full hover:bg-[var(--sand-deep)] transition-colors border border-transparent hover:border-[var(--line)]" title="View Profile">
                    <span class="text-sm font-semibold text-[var(--ink)] hidden sm:inline">{{ $user->name }}</span>
                    <div class="user-avatar-circle w-8 h-8 text-xs font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                </a>

                <form method="POST" action="/logout" class="m-0 hidden sm:inline-block">
                    @csrf
                    <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-full text-[var(--ink-soft)] hover:text-red-700 hover:bg-red-50 transition" title="Sign out">
                        Sign Out
                    </button>
                </form>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Dropdown -->
        <div x-show="mobileMenuOpen" @click.away="mobileMenuOpen = false" class="md:hidden border-t border-[var(--line)] bg-[var(--card)] px-4 py-3 space-y-2">
            <a href="/planora" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Trip Planner</a>
            <a href="/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-[var(--deep-teal)] bg-[var(--sand-deep)]">Dashboard</a>
            <a href="/my-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">My Plans</a>
            <a href="/public-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Public Plans</a>
            <a href="/profile/{{ $user->id }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Profile Settings</a>
            @if(auth()->user()->role === 'admin')
                <a href="/admin/hotels" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-[var(--deep-teal)] bg-emerald-50">Admin Panel</a>
            @endif
            <form method="POST" action="/logout" class="pt-2 border-t border-[var(--line)]">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">Sign Out</button>
            </form>
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="max-w-[1180px] w-full mx-auto px-4 sm:px-6 py-8 flex-1">

        <!-- Notification alerts if any -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        @endif

        <!-- HERO TRAVELER PASSPORT -->
        <div class="passport-card p-6 sm:p-8 mb-8 relative">
            <div class="orb-bg orb-1"></div>
            <div class="orb-bg orb-2"></div>
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
                
                <!-- Left: Profile and rank info -->
                <div class="flex items-start sm:items-center gap-5">
                    <div class="relative">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-[var(--ember)] to-[var(--ember-deep)] text-white flex items-center justify-center font-bold text-2xl sm:text-3xl shadow-xl ring-4 ring-white/10 animate-float-gentle">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="absolute -bottom-2 -right-2 px-2.5 py-0.5 rounded-full text-xs font-black bg-[var(--sand)] text-[var(--deep-teal)] border-2 border-[var(--deep-teal)] shadow animate-pulse-glow">
                            Lvl {{ $level }}
                        </span>
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-xs uppercase tracking-wider font-bold text-[var(--sand-deep)] opacity-80">
                                Official Traveler Passport
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/15 text-white backdrop-blur-sm">
                                {{ $currentLevelData['badge'] ?? 'Explorer' }}
                            </span>
                        </div>
                        <h1 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white mb-1">
                            Welcome back, {{ $user->name }}
                        </h1>
                        <p class="text-sm text-[var(--sand-deep)] opacity-90 max-w-xl">
                            Ready to craft your next journey in Dagupan City? Track itineraries, earn XP, and discover community recommendations.
                        </p>
                    </div>
                </div>

                <!-- Right: Action CTAs -->
                <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
                    <a href="/planora" class="btn-primary w-full sm:w-auto justify-center btn-press">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Plan New Trip
                    </a>
                </div>
            </div>

            <!-- XP Progression Bar Bar inside Passport -->
            <div class="mt-8 pt-6 border-t border-white/10 relative z-10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-[var(--sand-deep)] mb-2">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-white uppercase tracking-wider text-[11px]">Rank Progress</span>
                        <span class="px-2 py-0.5 rounded bg-white/10 font-mono text-white text-[11px]">
                            {{ $currentLevelData['name'] }} (Level {{ $level }})
                        </span>
                    </div>
                    <div class="font-mono">
                        <span class="font-bold text-white">{{ $points }} XP</span> / {{ $level >= 5 ? 'MAX LEVEL' : ($points + ($nextLevelPoints - $levelProgress)) . ' XP for ' . $nextLevelData['name'] }}
                    </div>
                </div>

                <div class="w-full bg-black/30 rounded-full h-3 overflow-hidden p-0.5 ring-1 ring-white/20">
                    <div class="h-full rounded-full xp-shimmer-bar transition-all duration-1000 ease-out shadow-sm"
                         style="width: {{ $level >= 5 ? 100 : $progressPercent }}%;"></div>
                </div>

                <div class="mt-2 flex items-center justify-between text-[11px] text-[var(--sand-deep)] opacity-80">
                    <span>{{ $level >= 5 ? 'Top tier unlocked!' : $nextLevelPoints - $levelProgress . ' XP needed to unlock Level ' . ($level + 1) . ' (' . $nextLevelData['name'] . ')' }}</span>
                    <span class="font-mono">{{ $level >= 5 ? '100%' : $progressPercent . '%' }} completed</span>
                </div>
            </div>
        </div>

        <!-- STATS HIGHLIGHTS ROW -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-8">
            <!-- Stat 1: Total Plans -->
            <div class="stat-card card-interactive animate-stagger-1">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider">Itineraries</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center transition-transform hover:scale-110">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </div>
                </div>
                <div class="font-display text-3xl font-bold text-[var(--deep-teal)]">{{ $userPlansCount }}</div>
                <div class="text-xs text-[var(--ink-soft)] mt-1 flex items-center justify-between">
                    <span>Crafted trips</span>
                    <a href="/my-plans" class="font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] flex items-center gap-0.5">
                        <span>View</span>
                        <span class="arrow-hover">→</span>
                    </a>
                </div>
            </div>

            
            <!-- Stat 2: Total XP Points -->
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider">Traveler XP</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                </div>
                <div class="font-display text-3xl font-bold text-[var(--deep-teal)]">{{ $points }}</div>
                <div class="text-xs text-[var(--ink-soft)] mt-1 flex items-center justify-between">
                    <span>Rank Points</span>
                    <span class="font-mono text-[var(--ember-deep)] font-semibold">Tier {{ $level }}</span>
                </div>
            </div>

            <!-- Stat 3: Public Shared Plans -->
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider">Community</span>
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                </div>
                <div class="font-display text-3xl font-bold text-[var(--deep-teal)]">{{ $userPublicPlansCount }}</div>
                <div class="text-xs text-[var(--ink-soft)] mt-1 flex items-center justify-between">
                    <span>Shared public</span>
                    <span class="text-emerald-600 font-semibold">Contributing</span>
                </div>
            </div>

            <!-- Stat 4: Membership Tier -->
            <div class="stat-card">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider">Account</span>
                    <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div class="font-display text-2xl font-bold text-[var(--deep-teal)] truncate">{{ ucfirst($user->role ?? 'Traveler') }}</div>
                <div class="text-xs text-[var(--ink-soft)] mt-1">
                    Joined {{ $user->created_at ? $user->created_at->format('M Y') : 'Recently' }}
                </div>
            </div>
        </div>

        <!-- DASHBOARD TABS -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-6 scrollbar-none">
            <button type="button" @click="activeTab = 'plans'" :class="{ 'active': activeTab === 'plans' }" class="tab-nav-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                <span>My Itineraries</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-[var(--sand-deep)] text-[var(--deep-teal)] font-mono font-bold">
                    {{ $userPlansCount }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'public'" :class="{ 'active': activeTab === 'public' }" class="tab-nav-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                <span>Community Inspiration</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-[var(--sand-deep)] text-[var(--deep-teal)] font-mono font-bold">
                    {{ $otherPublicPlans->count() }}
                </span>
            </button>

            <button type="button" @click="activeTab = 'roadmap'" :class="{ 'active': activeTab === 'roadmap' }" class="tab-nav-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Traveler Milestones</span>
                <span class="px-2 py-0.5 rounded-full text-xs bg-[var(--sand-deep)] text-[var(--deep-teal)] font-mono font-bold">
                    Lvl {{ $level }}
                </span>
            </button>
        </div>

        <!-- TAB 1: MY ITINERARIES -->
        <div x-show="activeTab === 'plans'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-[var(--ink)]">Recent Itineraries</h2>
                    <p class="text-sm text-[var(--ink-soft)]">Your custom planned journeys around Dagupan City</p>
                </div>
                @if($userPlansCount > 0)
                    <div class="flex items-center gap-3">
                        <a href="/my-plans" class="text-sm font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition">
                            View all ({{ $userPlansCount }}) →
                        </a>
                    </div>
                @endif
            </div>

            @if($userPlans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($userPlans as $plan)
                        <div class="card-interactive p-5 group">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <h3 class="font-display text-lg font-bold text-[var(--ink)] line-clamp-1 group-hover:text-[var(--deep-teal)] transition-colors" title="{{ $plan->display_title }}">
                                        {{ $plan->display_title }}
                                    </h3>
                                    <span class="badge-pill {{ $plan->is_public ? 'badge-public' : 'badge-private' }} shrink-0">
                                        @if($plan->is_public)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
                                            Public
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            Private
                                        @endif
                                    </span>
                                </div>

                                @if($plan->hotel_name)
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--ink-soft)] mb-4">
                                        <svg class="w-3.5 h-3.5 text-[var(--sage)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="truncate">{{ $plan->hotel_name }}</span>
                                    </div>
                                @endif

                                <!-- Travel Attributes Chips -->
                                <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-[var(--sand)] border border-[var(--line)] mb-4 transition-colors group-hover:border-[var(--sage)]">
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Est. Budget</div>
                                        <div class="font-mono font-bold text-sm text-[var(--deep-teal)]">
                                            PHP {{ number_format($plan->budget, 0) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Duration</div>
                                        <div class="font-mono font-bold text-sm text-[var(--ink)]">
                                            {{ $plan->total_days }} {{ Str::plural('Day', $plan->total_days) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-[var(--line)] flex items-center justify-between text-xs text-[var(--ink-soft)]">
                                <span class="font-mono text-[11px]">{{ $plan->created_at->diffForHumans() }}</span>
                                <a href="/plans/{{ $plan->id }}" class="font-bold text-[var(--deep-teal)] hover:text-[var(--ember)] transition flex items-center gap-1">
                                    <span>View Itinerary</span>
                                    <svg class="w-3.5 h-3.5 arrow-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <!-- Polished Empty State -->
                <div class="stat-card text-center py-12 px-6">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[var(--ember)] flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-[var(--ink)] mb-2">No travel plans created yet</h3>
                    <p class="text-sm text-[var(--ink-soft)] max-w-md mx-auto mb-6">
                        Planora helps you build the ideal Dagupan trip tailored to your budget, hotel location, and preferred pace.
                    </p>
                    <a href="/planora" class="btn-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        Create Your First Plan
                    </a>
                </div>
            @endif
        </div>

        <!-- TAB 2: COMMUNITY INSPIRATION -->
        <div x-show="activeTab === 'public'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="font-display text-2xl font-bold text-[var(--ink)]">Community Shared Plans</h2>
                    <p class="text-sm text-[var(--ink-soft)]">Explore public travel routes and budget breakdowns from fellow Dagupan travelers</p>
                </div>
                <a href="/public-plans" class="text-sm font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition">
                    Explore all public plans →
                </a>
            </div>

            @if($otherPublicPlans->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($otherPublicPlans as $plan)
                        <div class="card-interactive p-5 group">
                            <div>
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <h3 class="font-display text-lg font-bold text-[var(--ink)] line-clamp-1 group-hover:text-[var(--deep-teal)] transition-colors" title="{{ $plan->display_title }}">
                                        {{ $plan->display_title }}
                                    </h3>
                                    <span class="badge-pill badge-public shrink-0">Community</span>
                                </div>

                                <div class="flex items-center gap-2 mb-3">
                                    <div class="w-5 h-5 rounded-full bg-[var(--sand-deep)] text-[var(--deep-teal)] flex items-center justify-center font-bold text-[10px] ring-1 ring-[var(--line)]">
                                        {{ strtoupper(substr($plan->user ? $plan->user->name : 'T', 0, 1)) }}
                                    </div>
                                    <span class="text-xs text-[var(--ink-soft)] font-medium">
                                        {{ $plan->user ? $plan->user->name : 'Anonymous Traveler' }}
                                    </span>
                                </div>

                                @if($plan->hotel_name)
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--ink-soft)] mb-3">
                                        <svg class="w-3.5 h-3.5 text-[var(--sage)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span class="truncate">{{ $plan->hotel_name }}</span>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-2 p-3 rounded-xl bg-[var(--sand)] border border-[var(--line)] mb-4 transition-colors group-hover:border-[var(--sage)]">
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Budget</div>
                                        <div class="font-mono font-bold text-sm text-[var(--deep-teal)]">
                                            PHP {{ number_format($plan->budget, 0) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Duration</div>
                                        <div class="font-mono font-bold text-sm text-[var(--ink)]">
                                            {{ $plan->total_days }} {{ Str::plural('Day', $plan->total_days) }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-3 border-t border-[var(--line)] flex items-center justify-between text-xs text-[var(--ink-soft)]">
                                <span class="font-mono text-[11px]">{{ $plan->created_at->diffForHumans() }}</span>
                                <a href="/public-plans/{{ $plan->id }}" class="font-bold text-[var(--deep-teal)] hover:text-[var(--ember)] transition flex items-center gap-1">
                                    <span>View Plan</span>
                                    <svg class="w-3.5 h-3.5 arrow-hover" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="stat-card text-center py-12 px-6">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center mx-auto mb-4">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-[var(--ink)] mb-2">No other public plans shared yet</h3>
                    <p class="text-sm text-[var(--ink-soft)] max-w-md mx-auto mb-6">
                        Share your itineraries as public to inspire fellow travelers visiting Dagupan!
                    </p>
                    <button type="button" @click="activeTab = 'plans'" class="btn-primary">
                        Share from My Plans
                    </button>
                </div>
            @endif
        </div>

        <!-- TAB 3: TRAVELER MILESTONES & PROFILE -->
        <div x-show="activeTab === 'roadmap'" class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="font-display text-2xl font-bold text-[var(--ink)]">Traveler Progression Roadmap</h2>
                    <p class="text-sm text-[var(--ink-soft)]">Earn experience points by generating and sharing journeys across Dagupan</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="/profile/{{ $user->id }}" class="btn-secondary text-xs">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            <!-- Progression Ladder -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($levelDetails as $lvlNum => $detail)
                    <div class="roadmap-card {{ $level === $lvlNum ? 'current' : ($level > $lvlNum ? 'unlocked' : '') }} flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="w-8 h-8 rounded-xl font-black text-xs flex items-center justify-center {{ $level === $lvlNum ? 'bg-[var(--ember)] text-white shadow' : ($level > $lvlNum ? 'bg-[var(--deep-teal)] text-white' : 'bg-[var(--sand-deep)] text-[var(--ink-soft)]') }}">
                                    {{ $lvlNum }}
                                </span>
                                @if($level > $lvlNum)
                                    <span class="text-emerald-600 font-bold text-xs flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                @elseif($level === $lvlNum)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-[var(--ember)] text-white">
                                        Current
                                    </span>
                                @else
                                    <span class="text-[var(--sage)]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                    </span>
                                @endif
                            </div>

                            <div class="font-display font-bold text-base text-[var(--ink)] mb-0.5">
                                {{ $detail['name'] }}
                            </div>
                            <div class="text-[11px] font-bold text-[var(--ember-deep)] uppercase tracking-wider mb-2">
                                {{ $detail['badge'] }}
                            </div>
                            <p class="text-xs text-[var(--ink-soft)] leading-relaxed mb-3">
                                {{ $detail['description'] }}
                            </p>
                        </div>

                        <div class="pt-2 border-t border-[var(--line)] text-[11px] font-mono text-[var(--ink-soft)]">
                            {{ $detail['min_points'] }}+ XP required
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Profile Summary Card -->
            <div class="stat-card mt-6">
                <h3 class="font-display text-lg font-bold text-[var(--ink)] mb-4">Account Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div class="p-4 rounded-xl bg-[var(--sand)] border border-[var(--line)]">
                        <span class="text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">Full Name</span>
                        <span class="font-bold text-[var(--ink)]">{{ $user->name }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-[var(--sand)] border border-[var(--line)]">
                        <span class="text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">Email Address</span>
                        <span class="font-bold text-[var(--ink)]">{{ $user->email }}</span>
                    </div>
                    <div class="p-4 rounded-xl bg-[var(--sand)] border border-[var(--line)]">
                        <span class="text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">Traveler Role</span>
                        <span class="font-bold text-[var(--deep-teal)] capitalize">{{ $user->role ?? 'Traveler' }}</span>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <!-- Alpine Dashboard Store -->
    <script>
        function dashboard() {
            return {
                activeTab: 'plans',
                mobileMenuOpen: false
            }
        }
    </script>
</body>
</html>

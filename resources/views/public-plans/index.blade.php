<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Plans &mdash; Planora</title>
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
                radial-gradient(circle at 10% 8%, rgba(11, 61, 58, 0.05) 0%, transparent 42%),
                radial-gradient(circle at 92% 88%, rgba(217, 98, 43, 0.06) 0%, transparent 40%);
            color: var(--ink);
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
        }

        .font-display { font-family: 'DM Serif Display', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        .top-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 253, 249, 0.9);
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
        .top-nav-link:hover { background: var(--sand-deep); color: var(--deep-teal); }
        .top-nav-link.active {
            background: var(--deep-teal);
            color: var(--sand);
            box-shadow: 0 4px 12px rgba(11, 61, 58, 0.15);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(11, 61, 58, 0.18);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(11, 61, 58, 0.26);
            color: white;
        }

        .btn-secondary {
            background: var(--card);
            border: 1.5px solid var(--line);
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.825rem;
            transition: all 0.2s ease;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-secondary:hover {
            background: var(--sand-deep);
            color: var(--deep-teal);
            border-color: var(--deep-teal);
        }

        /* Stat Card */
        .stat-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 18px 20px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 10px rgba(11, 61, 58, 0.04);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(11, 61, 58, 0.08);
        }

        /* Plan Card */
        .plan-card {
            background: var(--card);
            border: 1.5px solid var(--line);
            border-radius: 20px;
            transition: all 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 3px 12px rgba(11, 61, 58, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .plan-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px -10px rgba(11, 61, 58, 0.14);
            border-color: rgba(217, 98, 43, 0.45);
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 9999px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge-public {
            background: rgba(34, 197, 94, 0.12);
            color: #15803d;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .badge-ai {
            background: rgba(217, 98, 43, 0.12);
            color: var(--ember-deep);
            border: 1px solid rgba(217, 98, 43, 0.25);
        }
        .badge-local {
            background: rgba(11, 61, 58, 0.08);
            color: var(--deep-teal);
            border: 1px solid rgba(11, 61, 58, 0.2);
        }

        .filter-chip {
            padding: 7px 14px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1.5px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .filter-chip:hover {
            border-color: var(--deep-teal);
            color: var(--deep-teal);
            background: var(--sand-deep);
        }
        .filter-chip.active {
            background: var(--deep-teal);
            color: var(--sand);
            border-color: var(--deep-teal);
            box-shadow: 0 3px 10px rgba(11, 61, 58, 0.2);
        }

        .place-chip {
            display: inline-flex;
            align-items: center;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.72rem;
            background: rgba(124, 145, 135, 0.12);
            color: var(--deep-teal);
            border: 1px solid rgba(124, 145, 135, 0.22);
            font-weight: 500;
            max-width: 160px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Toast styling */
        .toast-notification {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 50;
            background: var(--deep-teal);
            color: var(--sand);
            padding: 12px 20px;
            border-radius: 14px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body x-data="publicPlansApp()" x-cloak class="flex flex-col min-h-screen">

    <!-- Toast Notification -->
    <div x-show="toast.visible" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform translate-y-4"
         class="toast-notification">
        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
        <span x-text="toast.message"></span>
    </div>

    <!-- Top Navigation -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand">
                <span class="brand-mark">⌁</span>
                <span class="font-display">planora</span>
            </a>

            <!-- Desktop Links -->
            <div class="hidden md:flex items-center gap-2">
                <a href="/planora" class="top-nav-link" title="Open Trip Planner">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Trip Planner
                </a>
                @auth
                    <a href="/dashboard" class="top-nav-link" title="Traveler Dashboard">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                    <a href="/my-plans" class="top-nav-link" title="My Saved Plans">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                        My Plans
                    </a>
                @endauth
                <a href="/public-plans" class="top-nav-link active" title="Community Shared Plans">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Public Plans
                </a>
            </div>

            <!-- User Menu -->
            <div class="flex items-center gap-3">
                @auth
                    <a href="/profile/{{ auth()->id() }}" class="flex items-center gap-2 p-1.5 pl-3 rounded-full hover:bg-[var(--sand-deep)] transition" title="My Profile">
                        <span class="text-sm font-semibold text-[var(--ink)] hidden sm:inline">{{ auth()->user()->name }}</span>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[var(--ember)] to-[var(--ember-deep)] text-white font-bold text-xs flex items-center justify-center shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                    </a>
                    <form method="POST" action="/logout" class="m-0 hidden sm:inline-block">
                        @csrf
                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-full text-[var(--ink-soft)] hover:text-red-700 hover:bg-red-50 transition">
                            Sign Out
                        </button>
                    </form>
                @else
                    <a href="/login" class="top-nav-link">Sign In</a>
                    <a href="/register" class="btn-primary text-xs py-2 px-3.5 hidden sm:inline-flex">Join Planora</a>
                @endauth

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-[var(--ink)] hover:bg-[var(--sand-deep)] transition" aria-label="Toggle Menu">
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
            @auth
                <a href="/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Dashboard</a>
                <a href="/my-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">My Plans</a>
                <a href="/profile/{{ auth()->id() }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Profile</a>
            @endauth
            <a href="/public-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-[var(--deep-teal)] bg-[var(--sand-deep)]">Public Plans</a>
            @auth
                <form method="POST" action="/logout" class="pt-2 border-t border-[var(--line)]">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">Sign Out</button>
                </form>
            @else
                <a href="/login" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Sign In</a>
                <a href="/register" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-[var(--deep-teal)]">Create Account</a>
            @endauth
        </div>
    </nav>

    <!-- Main Content Container -->
    <main class="max-w-[1180px] w-full mx-auto px-4 sm:px-6 py-8 flex-1">

        <!-- Navigation Breadcrumbs -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2 text-xs font-semibold text-[var(--ink-soft)]">
                @auth
                    <a href="/dashboard" class="hover:text-[var(--deep-teal)] transition">Dashboard</a>
                    <span>/</span>
                @else
                    <a href="/planora" class="hover:text-[var(--deep-teal)] transition">Home</a>
                    <span>/</span>
                @endauth
                <span class="text-[var(--deep-teal)] font-bold">Public Plans</span>
            </div>
            <a href="/planora" class="inline-flex items-center gap-1.5 text-xs font-bold text-[var(--ember)] hover:text-[var(--ember-deep)] transition">
                <span>Plan your own trip</span>
                <span class="arrow-hover">&rarr;</span>
            </a>
        </div>

        <!-- Hero Header -->
        <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6 mb-8 pb-6 border-b border-[var(--line)]">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[var(--sand-deep)] text-[var(--deep-teal)] mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Community Travel Showcase
                </div>
                <h1 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-[var(--ink)] tracking-tight mb-3">
                    Inspiration from Public Plans
                </h1>
                <p class="text-[var(--ink-soft)] text-sm sm:text-base leading-relaxed">
                    Explore real Dagupan travel itineraries shared by fellow visitors and locals. Browse basecamps, budgets, and schedules to kickstart your next getaway.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a href="/planora" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Create My Itinerary
                </a>
            </div>
        </div>

        @if($plans->count() > 0)
            <!-- Metric Overview Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-[var(--ink-soft)] uppercase tracking-wider">Public Trips</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
                        </div>
                    </div>
                    <div class="font-display text-2xl font-bold text-[var(--deep-teal)]">{{ $plans->count() }}</div>
                    <div class="text-[11px] text-[var(--ink-soft)] mt-0.5">Shared itineraries</div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-[var(--ink-soft)] uppercase tracking-wider">Avg. Budget</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-[var(--ember)] flex items-center justify-center">
                            <span class="font-mono text-xs font-bold">₱</span>
                        </div>
                    </div>
                    <div class="font-display text-2xl font-bold text-[var(--ember-deep)]">
                        PHP {{ number_format($plans->avg('budget') ?: 0, 0) }}
                    </div>
                    <div class="text-[11px] text-[var(--ink-soft)] mt-0.5">Per planned tour</div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-[var(--ink-soft)] uppercase tracking-wider">AI Powered</span>
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-700 flex items-center justify-center">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        </div>
                    </div>
                    <div class="font-display text-2xl font-bold text-[var(--deep-teal)]">
                        {{ $plans->where('ai_provider', 'groq')->count() }}
                    </div>
                    <div class="text-[11px] text-[var(--ink-soft)] mt-0.5">Smart generated plans</div>
                </div>

                <div class="stat-card">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[11px] font-bold text-[var(--ink-soft)] uppercase tracking-wider">Multi-Day</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-700 flex items-center justify-center">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                    </div>
                    <div class="font-display text-2xl font-bold text-[var(--deep-teal)]">
                        {{ $plans->where('total_days', '>', 1)->count() }}
                    </div>
                    <div class="text-[11px] text-[var(--ink-soft)] mt-0.5">2+ day getaways</div>
                </div>
            </div>

            <!-- Interactive Search & Filtering Controls -->
            <div class="bg-[var(--card)] border border-[var(--line)] rounded-2xl p-4 sm:p-5 mb-8 shadow-sm">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    
                    <!-- Search Input -->
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[var(--sage)]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </div>
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Search by title, basecamp hotel, or places visited..."
                            class="w-full pl-10 pr-9 py-2.5 rounded-xl border border-[var(--line)] bg-[var(--sand)] text-sm text-[var(--ink)] placeholder-[var(--ink-soft)] focus:outline-none focus:border-[var(--deep-teal)] focus:ring-2 focus:ring-[var(--deep-teal)]/10 transition"
                        >
                        <button 
                            x-show="searchQuery.length > 0" 
                            @click="searchQuery = ''"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-[var(--ink-soft)] hover:text-[var(--ink)]"
                            title="Clear search"
                        >
                            &times;
                        </button>
                    </div>

                    <!-- Duration Filter Pills -->
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0">
                        <span class="text-xs font-bold text-[var(--ink-soft)] mr-1 hidden lg:inline">Duration:</span>
                        <button 
                            type="button" 
                            @click="durationFilter = 'all'" 
                            :class="durationFilter === 'all' ? 'active' : ''"
                            class="filter-chip"
                        >
                            All Days
                        </button>
                        <button 
                            type="button" 
                            @click="durationFilter = '1'" 
                            :class="durationFilter === '1' ? 'active' : ''"
                            class="filter-chip"
                        >
                            1 Day
                        </button>
                        <button 
                            type="button" 
                            @click="durationFilter = '2'" 
                            :class="durationFilter === '2' ? 'active' : ''"
                            class="filter-chip"
                        >
                            2 Days
                        </button>
                        <button 
                            type="button" 
                            @click="durationFilter = '3+'" 
                            :class="durationFilter === '3+' ? 'active' : ''"
                            class="filter-chip"
                        >
                            3+ Days
                        </button>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2 shrink-0">
                        <label for="sort-select" class="text-xs font-bold text-[var(--ink-soft)] whitespace-nowrap">Sort by:</label>
                        <select 
                            id="sort-select"
                            x-model="sortBy"
                            class="py-2 px-3 rounded-xl border border-[var(--line)] bg-[var(--sand)] text-xs font-semibold text-[var(--ink)] focus:outline-none focus:border-[var(--deep-teal)]"
                        >
                            <option value="newest">Newest First</option>
                            <option value="budget_low">Budget: Low to High</option>
                            <option value="budget_high">Budget: High to Low</option>
                            <option value="duration_desc">Longest Duration</option>
                        </select>
                    </div>
                </div>

                <!-- Active Filter Status & Reset -->
                <div x-show="hasActiveFilters" class="mt-3 pt-3 border-t border-[var(--line)] flex items-center justify-between text-xs text-[var(--ink-soft)]">
                    <span>
                        Showing <strong class="text-[var(--deep-teal)]" x-text="filteredPlansCount"></strong> matching itineraries
                    </span>
                    <button @click="resetFilters" class="text-xs font-semibold text-[var(--ember)] hover:underline flex items-center gap-1">
                        <span>Reset all filters</span>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>

            <!-- Plan Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($plans as $plan)
                    @php
                        $placesList = $plan->selected_places ?? [];
                        $cleanPlaces = array_map(function($p) {
                            return preg_replace('/\s*\((Restaurant|Mall|Beach|Tourist Spot)\)\s*$/i', '', trim((string)$p));
                        }, $placesList);
                        $authorName = $plan->user ? $plan->user->name : 'Community Traveler';
                        $authorInitial = strtoupper(substr($authorName, 0, 1));
                    @endphp

                    <div 
                        x-show="matchesPlan({{ $plan->id }}, @js($plan->display_title), @js($plan->hotel_name ?? ''), @js($cleanPlaces), {{ (int) $plan->total_days }}, {{ (float) $plan->budget }}, {{ $plan->created_at->timestamp }})"
                        class="p-6 group cursor-pointer"
                        onclick="if (!event.target.closest('button, a')) window.location.href='/public-plans/{{ $plan->id }}';"
                    >
                        <div>
                            <!-- Header: Author & Badges -->
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2 min-w-0">
                                    <div class="w-6 h-6 rounded-full bg-[var(--sand-deep)] border border-[var(--line)] text-[var(--deep-teal)] font-bold text-[10px] flex items-center justify-center shrink-0">
                                        {{ $authorInitial }}
                                    </div>
                                    <span class="text-xs text-[var(--ink-soft)] font-medium truncate" title="Shared by {{ $authorName }}">
                                        {{ $authorName }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0">
                                    <span class="badge-pill badge-public">
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                        Public
                                    </span>
                                    <span class="badge-pill {{ $plan->ai_provider === 'groq' ? 'badge-ai' : 'badge-local' }}">
                                        {{ $plan->ai_provider === 'groq' ? 'AI' : 'Curated' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Title -->
                            <h2 class="font-display font-bold text-xl text-[var(--ink)] line-clamp-2 mb-1 group-hover:text-[var(--deep-teal)] transition-colors" title="{{ $plan->display_title }}">
                                <a href="/public-plans/{{ $plan->id }}" class="hover:underline">
                                    {{ $plan->display_title }}
                                </a>
                            </h2>

                            <!-- Hotel Basecamp -->
                            @if($plan->hotel_name)
                                <div class="flex items-center gap-1.5 text-xs text-[var(--ink-soft)] mb-4">
                                    <svg class="w-3.5 h-3.5 text-[var(--sage)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="truncate font-medium">{{ $plan->hotel_name }}</span>
                                </div>
                            @else
                                <div class="mb-4"></div>
                            @endif

                            <!-- Meta Specifications Box -->
                            <div class="grid grid-cols-2 gap-3 p-3.5 rounded-xl bg-[var(--sand)] border border-[var(--line)] mb-4 group-hover:border-[var(--sage)] transition-colors">
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Est. Budget</div>
                                    <div class="font-mono font-bold text-sm text-[var(--deep-teal)] mt-0.5">
                                        PHP {{ number_format($plan->budget, 0) }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase font-bold text-[var(--ink-soft)] tracking-wider">Duration</div>
                                    <div class="font-mono font-bold text-sm text-[var(--ink)] mt-0.5">
                                        {{ $plan->total_days }} {{ Str::plural('Day', $plan->total_days) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Visited Places Preview -->
                            @if(count($cleanPlaces) > 0)
                                <div class="mb-4">
                                    <div class="text-[10px] uppercase font-bold text-[var(--sage)] tracking-wider mb-1.5 flex items-center justify-between">
                                        <span>Key Spots</span>
                                        <span class="font-mono text-[10px]">{{ count($cleanPlaces) }} places</span>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach(array_slice($cleanPlaces, 0, 3) as $place)
                                            <span class="place-chip" title="{{ $place }}">{{ $place }}</span>
                                        @endforeach
                                        @if(count($cleanPlaces) > 3)
                                            <span class="text-[11px] font-semibold text-[var(--ink-soft)] self-center">
                                                +{{ count($cleanPlaces) - 3 }} more
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-4 border-t border-[var(--line)] flex items-center justify-between mt-auto">
                            <span class="text-[11px] font-mono text-[var(--ink-soft)]" title="{{ $plan->created_at->format('M d, Y h:i A') }}">
                                {{ $plan->created_at->diffForHumans() }}
                            </span>
                            
                            <div class="flex items-center gap-2">
                                <!-- Share / Copy Link Button -->
                                <button 
                                    type="button" 
                                    @click.stop="copyPlanLink('{{ url('/public-plans/' . $plan->id) }}')"
                                    class="p-2 rounded-lg text-[var(--ink-soft)] hover:text-[var(--deep-teal)] hover:bg-[var(--sand-deep)] transition" 
                                    title="Copy public link"
                                    aria-label="Copy public link"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                                </button>

                                <!-- View Details CTA -->
                                <a 
                                    href="/public-plans/{{ $plan->id }}" 
                                    class="btn-primary text-xs py-2 px-3.5 inline-flex items-center gap-1.5"
                                >
                                    <span>View Itinerary</span>
                                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Zero Filter Match State (Shown when Alpine filters hide all) -->
            <div x-show="filteredPlansCount === 0" x-cloak class="stat-card text-center py-12 px-6 my-6">
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-[var(--ember)] flex items-center justify-center mx-auto mb-3">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <h3 class="font-display text-lg font-bold text-[var(--ink)] mb-1">No matching itineraries found</h3>
                <p class="text-xs text-[var(--ink-soft)] max-w-sm mx-auto mb-4">
                    Try clearing your search query or selecting "All Days" to view more community travel plans.
                </p>
                <button @click="resetFilters" class="btn-secondary text-xs">
                    Clear Search &amp; Filters
                </button>
            </div>

        @else
            <!-- Global Empty State: No public plans in DB yet -->
            <div class="stat-card text-center py-16 px-6 max-w-xl mx-auto my-8">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[var(--ember)] flex items-center justify-center mx-auto mb-4 shadow-sm">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                </div>
                <h2 class="font-display text-2xl font-bold text-[var(--ink)] mb-2">No public plans shared yet</h2>
                <p class="text-sm text-[var(--ink-soft)] max-w-md mx-auto mb-6 leading-relaxed">
                    Be the trailblazer! Build your custom Dagupan City travel itinerary and toggle it to "Public" so other travelers can discover your favorite spots.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <a href="/planora" class="btn-primary">
                        Plan a New Trip
                    </a>
                    @auth
                        <a href="/my-plans" class="btn-secondary">
                            View My Plans
                        </a>
                    @endauth
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-[var(--line)] bg-[var(--card)] py-6 text-xs text-[var(--ink-soft)]">
        <div class="max-w-[1180px] mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="brand-mark" style="width:22px;height:22px;font-size:0.75rem;border-radius:6px;">⌁</span>
                <span class="font-display font-bold text-[var(--deep-teal)] text-sm">planora</span>
                <span>&mdash; Dagupan City Smart Travel Companion</span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="/planora" class="hover:text-[var(--deep-teal)] transition">Trip Planner</a>
                <a href="/my-plans" class="hover:text-[var(--deep-teal)] transition">My Plans</a>
                <a href="/public-plans" class="text-[var(--deep-teal)] font-bold">Public Showcase</a>
            </div>
        </div>
    </footer>

    <!-- Alpine.js Client-Side Controller -->
    <script>
        function publicPlansApp() {
            return {
                mobileMenuOpen: false,
                searchQuery: '',
                durationFilter: 'all',
                sortBy: 'newest',
                totalPlans: {{ $plans->count() }},
                visiblePlanIds: new Set(),
                toast: {
                    visible: false,
                    message: '',
                    timeout: null
                },

                get hasActiveFilters() {
                    return this.searchQuery.trim() !== '' || this.durationFilter !== 'all' || this.sortBy !== 'newest';
                },

                get filteredPlansCount() {
                    return this.visiblePlanIds.size;
                },

                resetFilters() {
                    this.searchQuery = '';
                    this.durationFilter = 'all';
                    this.sortBy = 'newest';
                },

                showToast(msg) {
                    this.toast.message = msg;
                    this.toast.visible = true;
                    if (this.toast.timeout) clearTimeout(this.toast.timeout);
                    this.toast.timeout = setTimeout(() => {
                        this.toast.visible = false;
                    }, 2800);
                },

                copyPlanLink(url) {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        navigator.clipboard.writeText(url).then(() => {
                            this.showToast('Itinerary link copied to clipboard!');
                        }).catch(() => {
                            this.showToast('Could not copy link.');
                        });
                    } else {
                        const ta = document.createElement('textarea');
                        ta.value = url;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        document.body.removeChild(ta);
                        this.showToast('Itinerary link copied to clipboard!');
                    }
                },

                matchesPlan(id, title, hotel, places, days, budget, timestamp) {
                    let matchesSearch = true;
                    if (this.searchQuery.trim() !== '') {
                        const q = this.searchQuery.toLowerCase();
                        const titleMatch = (title || '').toLowerCase().includes(q);
                        const hotelMatch = (hotel || '').toLowerCase().includes(q);
                        const placesMatch = places.some(p => (p || '').toLowerCase().includes(q));
                        matchesSearch = titleMatch || hotelMatch || placesMatch;
                    }

                    let matchesDuration = true;
                    if (this.durationFilter === '1') {
                        matchesDuration = days === 1;
                    } else if (this.durationFilter === '2') {
                        matchesDuration = days === 2;
                    } else if (this.durationFilter === '3+') {
                        matchesDuration = days >= 3;
                    }

                    const isMatch = matchesSearch && matchesDuration;
                    if (isMatch) {
                        this.visiblePlanIds.add(id);
                    } else {
                        this.visiblePlanIds.delete(id);
                    }
                    return isMatch;
                }
            };
        }
    </script>
</body>
</html>

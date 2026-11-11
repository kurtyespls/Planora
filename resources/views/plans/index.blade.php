<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Plans — Planora</title>
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
        }
        .top-nav-link:hover { color: var(--deep-teal); background: var(--sand-deep); }
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
        }
        .btn-danger {
            background: transparent;
            border: 1.5px solid rgba(220, 38, 38, 0.25);
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.825rem;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-danger:hover {
            background: #fef2f2;
            border-color: #dc2626;
            color: #b91c1c;
        }

        /* Stat Card */
        .stat-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 20px;
            transition: all 0.25s ease;
            box-shadow: 0 2px 10px rgba(11, 61, 58, 0.04);
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 24px -6px rgba(11, 61, 58, 0.08);
        }

        /* Plan Cards */
        .plan-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            transition: var(--transition-base);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
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
    </style>
</head>
<body x-data="{ mobileMenuOpen: false, filter: 'all' }" x-cloak class="flex flex-col min-h-screen">

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand">
                <span class="brand-mark">⌁</span>
                <span class="font-display">planora</span>
            </a>

            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex items-center gap-2">
                <a href="/planora" class="top-nav-link" title="Open Trip Planner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Trip Planner
                </a>
                <a href="/dashboard" class="top-nav-link" title="Traveler Dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="/my-plans" class="top-nav-link active" title="My Saved Itineraries">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                    My Plans
                </a>
                <a href="/public-plans" class="top-nav-link" title="Community Shared Plans">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Public Plans
                </a>
                @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="/admin/hotels" class="top-nav-link top-nav-link-admin" title="Admin Control Center">
                        Admin Panel
                    </a>
                @endif
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
                @endauth

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
            <a href="/dashboard" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Dashboard</a>
            <a href="/my-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-[var(--deep-teal)] bg-[var(--sand-deep)]">My Plans</a>
            <a href="/public-plans" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Public Plans</a>
            @auth
                <a href="/profile/{{ auth()->id() }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium text-[var(--ink)] hover:bg-[var(--sand-deep)]">Profile Settings</a>
                <form method="POST" action="/logout" class="pt-2 border-t border-[var(--line)]">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 rounded-lg text-sm font-semibold text-red-600 hover:bg-red-50">Sign Out</button>
                </form>
            @endauth
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-[1180px] w-full mx-auto px-4 sm:px-6 py-8 flex-1">

        <!-- Flash messages -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-sm font-medium flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
                <button @click="$el.parentElement.remove()" class="text-red-500 hover:text-red-700">&times;</button>
            </div>
        @endif

        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold text-[var(--ink-soft)] mb-1">
                    <a href="/dashboard" class="hover:text-[var(--deep-teal)]">Dashboard</a>
                    <span>/</span>
                    <span class="text-[var(--deep-teal)] font-bold">My Plans</span>
                </div>
                <h1 class="font-display text-3xl sm:text-4xl font-bold text-[var(--ink)]">
                    My Saved Itineraries
                </h1>
                <p class="text-sm text-[var(--ink-soft)] mt-1">
                    Manage, review, or share your custom Dagupan travel itineraries.
                </p>
            </div>
            <a href="/planora" class="btn-primary shrink-0">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Plan New Journey
            </a>
        </div>

        @if($plans->count() > 0)
            <!-- Metric Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
                <div class="stat-card card-interactive animate-stagger-1 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">Total Trips</span>
                        <span class="font-display text-2xl font-bold text-[var(--deep-teal)]">{{ $plans->count() }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center transition-transform hover:scale-110">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>

                <div class="stat-card card-interactive animate-stagger-2 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">AI Generated</span>
                        <span class="font-display text-2xl font-bold text-[var(--ember-deep)]">{{ $plans->where('ai_provider', 'groq')->count() }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-[var(--ember)] flex items-center justify-center transition-transform hover:scale-110">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                </div>

                <div class="stat-card card-interactive animate-stagger-3 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-[var(--ink-soft)] uppercase tracking-wider block mb-1">Combined Budget</span>
                        <span class="font-display text-2xl font-bold text-[var(--deep-teal)]">PHP {{ number_format($plans->sum('budget') ?: 0, 0) }}</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center transition-transform hover:scale-110">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
            </div>

            <!-- List of Plans -->
            <div class="space-y-4">
                @foreach($plans as $plan)
                    <div class="plan-card card-interactive p-5 sm:p-6 group">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                            
                            <!-- Left Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-2">
                                    <h2 class="font-display text-xl sm:text-2xl font-bold text-[var(--ink)] tracking-tight group-hover:text-[var(--deep-teal)] transition-colors">
                                        {{ $plan->display_title }}
                                    </h2>
                                    <span class="badge-pill {{ $plan->is_public ? 'badge-public' : 'badge-private' }}">
                                        @if($plan->is_public)
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
                                            Public
                                        @else
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                            Private
                                        @endif
                                    </span>
                                    <span class="badge-pill badge-ai">
                                        {{ $plan->ai_provider === 'groq' ? 'AI Generated' : 'Curated Local' }}
                                    </span>
                                </div>

                                @if($plan->hotel_name)
                                    <div class="flex items-center gap-1.5 text-xs text-[var(--ink-soft)] mb-3">
                                        <svg class="w-3.5 h-3.5 text-[var(--sage)] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        <span>Basecamp: <strong>{{ $plan->hotel_name }}</strong></span>
                                    </div>
                                @endif

                                <div class="flex flex-wrap items-center gap-4 text-xs text-[var(--ink-soft)]">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold uppercase tracking-wider text-[10px] text-[var(--sage)]">Budget:</span>
                                        <span class="font-mono font-bold text-sm text-[var(--deep-teal)]">PHP {{ number_format($plan->budget, 0) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold uppercase tracking-wider text-[10px] text-[var(--sage)]">Duration:</span>
                                        <span class="font-mono font-bold text-sm text-[var(--ink)]">{{ $plan->total_days }} {{ Str::plural('Day', $plan->total_days) }}</span>
                                    </div>
                                    <div class="flex items-center gap-1 text-[var(--sage)]">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>{{ $plan->created_at->format('M d, Y') }} ({{ $plan->created_at->diffForHumans() }})</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Actions -->
                            <div class="flex flex-wrap items-center gap-2 pt-3 lg:pt-0 border-t lg:border-t-0 border-[var(--line)]">
                                <a href="/plans/{{ $plan->id }}" class="btn-primary btn-press text-xs py-2 px-4 whitespace-nowrap">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span>View Itinerary</span>
                                    <span class="arrow-hover ml-0.5">→</span>
                                </a>

                                <form action="/plans/{{ $plan->id }}/share" method="POST" class="inline m-0">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_public" value="{{ $plan->is_public ? 0 : 1 }}">
                                    <button type="submit" class="btn-secondary btn-press whitespace-nowrap {{ $plan->is_public ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : '' }}" title="{{ $plan->is_public ? 'Make this plan private' : 'Share publicly with other travelers' }}">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/></svg>
                                        {{ $plan->is_public ? 'Publicly Shared' : 'Share Publicly' }}
                                    </button>
                                </form>

                                <form action="/plans/{{ $plan->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this plan? This cannot be undone.')" class="inline m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-press whitespace-nowrap" title="Delete plan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
                            </div>
                        </div>
                    </div>
            </div>
        @else
            <!-- Empty state -->
            <div class="stat-card text-center py-16 px-6">
                <div class="w-16 h-16 rounded-2xl bg-amber-50 text-[var(--ember)] flex items-center justify-center mx-auto mb-4">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                </div>
                <h2 class="font-display text-2xl font-bold text-[var(--ink)] mb-2">No saved plans yet</h2>
                <p class="text-sm text-[var(--ink-soft)] max-w-md mx-auto mb-6">
                    Pick your hotel, budget, and favorite spots in Dagupan to create a smart travel itinerary.
                </p>
                <a href="/planora" class="btn-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Create Your First Plan
                </a>
            </div>
        @endif

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
                <a href="/public-plans" class="hover:text-[var(--deep-teal)]">Public Plans</a>
            </div>
        </div>
    </footer>

</body>
</html>

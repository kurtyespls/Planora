<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>My Plans — Planora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .top-nav {
            position: sticky; top: 0; z-index: 40;
            background: rgba(255,253,249,0.85);
            backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--line);
        }
        .top-nav-inner {
            max-width: 1120px; margin: 0 auto; display: flex; align-items: center;
            justify-content: space-between; padding: 14px 20px;
        }
        .top-nav-link {
            display: flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 999px;
            font-size: 0.8rem; font-weight: 600; color: var(--ink); text-decoration: none;
            background: transparent; border: none; cursor: pointer; transition: all 0.2s ease;
        }
        .top-nav-link.active { background: var(--deep-teal); color: var(--sand); }
        .top-nav-link:hover { background: var(--sand-deep); color: var(--deep-teal); }
        .font-display { font-family: 'DM Serif Display', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        body { background-color: var(--sand); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.6; margin: 0; }
        a { color: var(--deep-teal); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--ember); }
        .btn-primary {
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand); border: 0; padding: 12px 24px; border-radius: var(--radius-md);
            font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: var(--shadow-glow); }
        .btn-danger {
            background: var(--ember); color: var(--sand); border: 0;
            padding: 12px 24px; border-radius: var(--radius-md);
            font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-danger:hover { background: var(--ember-deep); transform: translateY(-2px); box-shadow: var(--shadow-glow); }
        .plan-card {
            background: var(--card); border: 1px solid var(--line);
            border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
            overflow: hidden; transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .plan-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: rgba(217, 98, 43, 0.4); }
        .plan-badge {
            display: inline-flex; align-items: center; padding: 0.35rem 0.85rem;
            border-radius: 9999px; font-size: 0.65rem; font-weight: 700;
            letter-spacing: 0.05em; text-transform: uppercase; color: #fff;
        }
        .plan-badge.ai { background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink)); }
        .plan-badge.offline { background: linear-gradient(135deg, var(--ember), var(--ember-deep)); }
        .stat-card {
            background: var(--card); border: 1px solid var(--line);
            border-radius: var(--radius-md); padding: var(--space-6);
            box-shadow: var(--shadow-sm); transition: all 0.2s ease;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: var(--shadow-md); }
        .empty-illustration { filter: drop-shadow(0 4px 12px rgba(0,0,0,0.08)); }
    </style>
</head>
<body>

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand">
                <span class="brand-mark">⌁</span><span>planora</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="/my-plans" class="top-nav-link active" title="My Saved Plans">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                    My Plans
                </a>
                <button type="button" class="top-nav-link" onclick="location.href='/profile/{{ auth()->id() }}'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a7 7 0 1 0-14 0"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </button>
                @auth
                <form method="POST" action="/logout" style="margin:0;">
                    @csrf
                    <button type="submit" class="top-nav-link">Log out</button>
                </form>
                @else
                <a href="/login" class="top-nav-link">Login</a>
                <a href="/register" class="top-nav-link">Sign Up</a>
                @endauth
            </div>
        </div>
    </nav>
    <!-- Page Header -->
    <header class="max-w-4xl mx-auto px-4 lg:px-6 mb-8">
        <div class="bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-sm p-6 lg:p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                <div>
                    <h1 class="font-display text-3xl font-bold text-[var(--ink)] tracking-tight">My Plans</h1>
                    <p class="text-[var(--ink-soft)] text-sm mt-1">Manage your saved travel itineraries for Dagupan City</p>
                </div>
                <a href="/planora" class="btn-primary px-6 py-3 text-sm font-semibold flex items-center gap-2 whitespace-nowrap">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                    Create New Plan
                </a>
            </div>
        </div>
    </header>

    <!-- Stats Overview -->
    @if($plans->count() > 0)
    <div class="max-w-4xl mx-auto px-4 lg:px-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-mono text-[var(--sage)] uppercase tracking-wider mb-1">Total Plans</p>
                        <p class="font-display text-2xl font-bold text-[var(--ink)]">{{ $plans->count() }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[var(--deep-teal)]/10 flex items-center justify-center text-[var(--deep-teal)]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-mono text-[var(--sage)] uppercase tracking-wider mb-1">AI Generated</p>
                        <p class="font-display text-2xl font-bold text-[var(--ink)]">{{ $plans->where('ai_provider', 'groq')->count() }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[var(--ember)]/10 flex items-center justify-center text-[var(--ember)]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-mono text-[var(--sage)] uppercase tracking-wider mb-1">Total Budget</p>
                        <p class="font-display text-2xl font-bold text-[var(--ink)]">₱{{ number_format($plans->sum('budget') ?: 0) }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-[var(--sage)]/10 flex items-center justify-center text-[var(--sage)]">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

        @if($plans->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto mb-5 rounded-full bg-[var(--sand-deep)] flex items-center justify-center">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-[var(--sage)]"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <p class="text-[var(--ink)] font-semibold mb-1">No saved plans yet</p>
            <p class="text-sm text-[var(--sage)] mb-6">Create your first travel itinerary for Dagupan City</p>
            <a href="/planora" class="btn-primary inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-semibold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                Create a Plan
            </a>
        </div>
        @else
        <div class="space-y-4">
            @foreach($plans as $plan)
            <div class="plan-card p-5">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-display font-semibold text-lg text-[var(--ink)]">{{ $plan->display_title }}</h3>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-[var(--ink-soft)] mt-1">
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1"/><line x1="12" y1="23"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z"/><path d="M12 2v1"/></svg>
                                <span class="font-mono font-semibold">₱{{ number_format($plan->budget) }}</span>
                            </span>
                            <span class="flex items-center gap-1.5">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                {{ $plan->created_at->format('M d, Y') }}
                            </span>
                            <span class="plan-badge">{{ $plan->ai_provider === 'groq' ? 'AI generated' : 'Offline generator' }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="/plans/{{ $plan->id }}" class="btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 whitespace-nowrap">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            View
                        </a>
                        @if($plan->is_public)
                            <form action="/plans/{{ $plan->id }}/share" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-secondary px-4 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap" style="background: rgba(34, 197, 94, 0.15); color: #16a34a; border: 1px solid rgba(34, 197, 94, 0.4);">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Sharing on
                                </button>
                            </form>
                        @else
                            <form action="/plans/{{ $plan->id }}/share" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn-outline px-4 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap" style="border: 1px solid var(--line); color: var(--ink-soft); background: transparent;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Share for ideas
                                </button>
                            </form>
                        @endif
                        <a href="/public-plans" class="btn-outline px-4 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap" style="border: 1px solid var(--line); color: var(--ink-soft); background: transparent;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                            Public Plans
                        </a>
                        <form action="/plans/{{ $plan->id }}" method="POST" onsubmit="return confirm('Delete this plan? This cannot be undone.')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger px-4 py-2.5 rounded-lg text-sm font-semibold whitespace-nowrap">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
</main>

</body>
</html>
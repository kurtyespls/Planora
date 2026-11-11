<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Plans — Planora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .top-nav { position: sticky; top: 0; z-index: 40; background: rgba(255,253,249,0.85); backdrop-filter: blur(20px); border-bottom: 1px solid var(--line); }
        .top-nav-inner { max-width: 1120px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; }
        .top-nav-link { display: flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; color: var(--ink); text-decoration: none; background: transparent; border: none; cursor: pointer; transition: all 0.2s ease; }
        .top-nav-link.active { background: var(--deep-teal); color: var(--sand); }
        .top-nav-link:hover { background: var(--sand-deep); color: var(--deep-teal); }
        .font-display { font-family: 'DM Serif Display', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        body { background-color: var(--sand); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.6; margin: 0; }
        a { color: var(--deep-teal); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--ember); }
        .page-header { max-width: 1120px; margin: 0 auto; padding: 32px 20px 24px; text-align: center; }
        .page-header h1 { font-family: 'DM Serif Display', serif; font-size: 2rem; color: var(--ink); margin-bottom: 8px; }
        .page-header p { color: var(--ink-soft); max-width: 600px; margin: 0 auto; }
        .card-grid { max-width: 1120px; margin: 0 auto; padding: 0 20px 48px; }
        .public-badge { display: inline-flex; align-items: center; gap: 6px; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; background: rgba(34, 197, 94, 0.15); color: #16a34a; }
        .empty-state { text-align: center; padding: 64px 20px; background: var(--card); border: 1px solid var(--line); border-radius: var(--radius-lg); }
        .empty-state svg { width: 64px; height: 64px; color: var(--ink-soft); margin-bottom: 16px; }
        .empty-state h3 { color: var(--ink); font-family: 'DM Serif Display', serif; font-size: 1.25rem; margin-bottom: 8px; }
        .empty-state p { color: var(--ink-soft); max-width: 400px; margin: 0 auto; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--deep-teal); text-decoration: none; font-weight: 600; margin-bottom: 16px; }
        .back-link:hover { color: var(--ember); }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="font-display font-semibold text-xl">Planora</a>
            <div class="flex items-center gap-3">
                <a href="/planora" class="top-nav-link">Plan</a>
                <a href="/my-plans" class="top-nav-link">My Plans</a>
                @auth
                    <a href="/profile/{{ auth()->id() }}" class="top-nav-link">Profile</a>
                @else
                    <a href="/login" class="top-nav-link">Sign in</a>
                @endauth
            </div>
                @foreach($plans as $plan)
                    <div class="public-card plan-card p-5">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-display font-semibold text-lg text-[var(--ink)] truncate">{{ $plan->display_title }}</h3>
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1.5 text-sm text-[var(--ink-soft)] mt-1">
                                    @if($plan->hotel_name)
                                        <span class="flex items-center gap-1.5">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                            <span>{{ $plan->hotel_name }}</span>
                                        </span>
                                    @endif
                                    @if($plan->budget)
                                        <span class="flex items-center gap-1.5">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z"/><path d="M12 2v1"/></svg>
                                            <span class="font-mono">PHP {{ number_format($plan->budget) }}</span>
                                        </span>
                                    @endif
                                    @if($plan->total_days)
                                        <span class="flex items-center gap-1.5">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                            <span>{{ $plan->total_days }} day{{ $plan->total_days > 1 ? 's' : '' }}</span>
                                        </span>
                                    @endif
                                    <span class="public-badge">{{ $plan->ai_provider === 'groq' ? 'AI generated' : 'Local' }}</span>
                                </div>
                            </div>
                            <a href="/public-plans/{{ $plan->id }}" class="btn-primary px-5 py-2.5 rounded-lg text-sm font-semibold flex items-center gap-2 whitespace-nowrap">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                View plan
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</body>
</html>

        </div>
    </nav>
    <div class="page-header">
        <a href="/planora" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back
        </a>
        <h1>Inspiration from Public Plans</h1>
        <p>See travel plans shared by Planora users for ideas. Your identity stays anonymous.</p>
    </div>
    <div class="card-grid">
        @if($plans->isEmpty())
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <h3>No public plans yet</h3>
                <p>Be the first to share your travel plan for others to get inspired!</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

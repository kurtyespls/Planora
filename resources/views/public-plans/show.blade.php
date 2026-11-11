<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Plan — Planora</title>
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
        .page-header { max-width: 1120px; margin: 0 auto; padding: 32px 20px 24px; }
        .page-header .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--deep-teal); text-decoration: none; font-weight: 600; margin-bottom: 16px; }
        .page-header .back-link:hover { color: var(--ember); }
        .card-grid { max-width: 1120px; margin: 0 auto; padding: 0 20px 48px; }
        .public-plan-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
            padding: var(--space-6);
            max-width: 800px;
            margin: 0 auto;
            position: relative;
            transition: var(--transition-base);
        }
        .public-plan-card::before {
            content: ''; position: absolute; inset: 0; border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(11,61,58,0.03) 0%, transparent 50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        .public-plan-card:hover { border-color: var(--ember); box-shadow: var(--shadow-lg); }
        .public-plan-card:hover::before { opacity: 1; }
        .public-plan-card:focus-visible { outline: 2px solid var(--deep-teal); outline-offset: -4px; }
        .public-plan-card:focus-visible::before { opacity: 1; }
        .public-creator-tag { display: inline-flex; align-items: center; gap: 6px; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; background: rgba(34, 197, 94, 0.15); color: #16a34a; }
        .section-title { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ink-soft); margin-bottom: 8px; }
        .budget-row { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: var(--sand); border-radius: var(--radius-md); }
        .budget-row svg { width: 16px; height: 16px; flex-shrink: 0; }
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
        </div>
    </nav>

    <div class="page-header">
        <a href="/public-plans" class="back-link">
                @if($plan->hotel_name)
                    <div class="budget-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span class="font-semibold">Hotel</span>
                        <span class="text-[var(--ink)]">{{ $plan->hotel_name }}</span>
                    </div>
                @endif
                @if($plan->budget)
                    <div class="budget-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z"/><path d="M12 2v1"/></svg>
                        <span class="font-semibold">Budget</span>
                        <span class="font-mono">PHP {{ number_format($plan->budget) }}</span>
                    </div>
                @endif
                @if($plan->total_days)
                    <div class="budget-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <span class="font-semibold">Duration</span>
                        <span class="text-[var(--ink)]">{{ $plan->total_days }} day{{ $plan->total_days > 1 ? 's' : '' }}</span>
                    </div>
                @endif
                <div class="budget-row">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span class="font-semibold">Shared via</span>
                    <span class="text-[var(--ink)]">{{ $plan->ai_provider === 'groq' ? 'AI generated' : 'Local generator' }}</span>
                </div>
                <div class="budget-row">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span class="font-semibold">Shared on</span>
                    <span class="text-[var(--ink-soft)]">{{ $plan->created_at->format('M d, Y') }}</span>
                </div>
            </div>

            <div class="mt-8 border-t border-[var(--line)] pt-6">
                <h2 class="section-title">Itinerary</h2>
                @if($plan->ai_recommendation)
                    <div class="markdown-body text-[var(--ink-soft)]" style="max-height: 400px; overflow-y: auto;">
                        {!! nl2br(e($plan->ai_recommendation)) !!}
                    </div>
                @else
                    <p class="text-[var(--ink-soft)] italic">No itinerary available for this plan.</p>
                @endif
            </div>
        </div>
    </div>
</body>
</html>

            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to public plans
        </a>
        <div class="public-plan-card">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="font-display text-2xl text-[var(--ink)]">{{ $plan->display_title }}</h1>
                <span class="public-creator-tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    Publicly shared
                </span>
            </div>
            <p class="text-[var(--ink-soft)] mt-2">Shared by a Planora user for inspiration. Your identity is anonymous.</p>

            <div class="mt-6 space-y-4">

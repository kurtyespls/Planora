<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $plan->display_title }} — Planora</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .top-nav { position: sticky; top: 0; z-index: 50; background: rgba(255,253,249,0.85); backdrop-filter: blur(20px); border-bottom: 1px solid var(--line); }
        .top-nav-inner { max-width: 1120px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; }
        .nav-logo { font-family: 'DM Serif Display', serif; font-size: 1.25rem; font-weight: 600; color: var(--ink); text-decoration: none; }
        
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .top-nav-link { display: flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9999px; font-size: 0.85rem; font-weight: 600; color: var(--ink); text-decoration: none; background: transparent; border: none; cursor: pointer; transition: all 0.2s ease; }
        .top-nav-link.active { background: var(--deep-teal); color: var(--sand); }
        .top-nav-link:hover { background: rgba(11, 61, 58, 0.08); color: var(--deep-teal); }
        .top-nav-link.signin-btn { background: var(--deep-teal); color: var(--sand); }
        .top-nav-link.signin-btn:hover { background: var(--ember); color: var(--sand); }

        /* Mobile Menu Toggle Button */
        .mobile-menu-btn { display: none; background: transparent; border: none; cursor: pointer; color: var(--ink); padding: 8px; }
        
        @media (max-width: 768px) {
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 253, 249, 0.98);
                backdrop-filter: blur(20px);
                flex-direction: column;
                align-items: stretch;
                padding: 16px 20px;
                border-bottom: 1px solid var(--line);
                box-shadow: 0 10px 20px rgba(0,0,0,0.05);
            }
            .nav-links.open { display: flex; }
            .top-nav-link { justify-content: center; padding: 12px; }
            .mobile-menu-btn { display: block; }
        }

        .font-display { font-family: 'DM Serif Display', serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        body { background-color: var(--sand); color: var(--ink); font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.6; margin: 0; }
        a { color: var(--deep-teal); text-decoration: none; transition: color 0.2s; }
        a:hover { color: var(--ember); }
        
        .main-container { max-width: 900px; margin: 0 auto; padding: 40px 20px 64px; }
        .back-link { display: inline-flex; align-items: center; gap: 8px; color: var(--deep-teal); font-weight: 600; font-size: 0.9rem; margin-bottom: 24px; transition: gap 0.2s ease; }
        .back-link:hover { gap: 12px; color: var(--ember); }
        
        .public-plan-card {
            background: var(--card, #fff);
            border: 1px solid var(--line);
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(11, 61, 58, 0.05);
            padding: 36px;
            position: relative;
            overflow: hidden;
        }
        .public-plan-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px;
            background: linear-gradient(90deg, var(--deep-teal), var(--ember));
        }
        
        .public-creator-tag { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase; background: rgba(34, 197, 94, 0.12); color: #16a34a; }
        .meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; margin-top: 24px; }
        .budget-row { display: flex; align-items: center; gap: 10px; padding: 12px 16px; background: rgba(11, 61, 58, 0.02); border: 1px solid var(--line); border-radius: 12px; }
        .budget-row svg { width: 18px; height: 18px; color: var(--deep-teal); flex-shrink: 0; }
        
        .section-title { font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-soft); margin-bottom: 12px; display: flex; align-items: center; gap: 8px; }
        .itinerary-box { background: rgba(255, 255, 255, 0.7); border: 1px solid var(--line); border-radius: 16px; padding: 24px; margin-top: 28px; }
    </style>
</head>
<body>
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="nav-logo">Planora</a>
            
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle navigation">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <div class="nav-links" id="navLinks">
                <a href="/planora" class="top-nav-link">Plan</a>
                <a href="/my-plans" class="top-nav-link">My Plans</a>
                @auth
                    <a href="/profile/{{ auth()->id() }}" class="top-nav-link">Profile</a>
                @else
                    <a href="/login" class="top-nav-link signin-btn">Sign in</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="main-container">
        <a href="/public-plans" class="back-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to public plans
        </a>

        <div class="public-plan-card">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="font-display text-3xl text-[var(--ink)] mb-1">{{ $plan->display_title }}</h1>
                    <p class="text-sm text-[var(--ink-soft)]">Shared by a Planora user for inspiration. Your identity remains anonymous.</p>
                </div>
                <span class="public-creator-tag">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>
                    Publicly Shared
                </span>
            </div>

            <!-- Metadata Grid -->
            <div class="meta-grid">
                @if($plan->hotel_name)
                    <div class="budget-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <div class="flex flex-col text-xs">
                            <span class="text-[var(--ink-soft)] font-medium">Hotel</span>
                            <span class="font-semibold text-[var(--ink)] text-sm truncate">{{ $plan->hotel_name }}</span>
                        </div>
                    </div>
                @endif
                @if($plan->budget)
                    <div class="budget-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M12 8a4 4 0 1 0 0 8 4 4 0 1 0 0-8z"/><path d="M12 2v1"/></svg>
                        <div class="flex flex-col text-xs">
                            <span class="text-[var(--ink-soft)] font-medium">Budget</span>
                            <span class="font-mono font-semibold text-[var(--ink)] text-sm">PHP {{ number_format($plan->budget) }}</span>
                        </div>
                    </div>
                @endif
                @if($plan->total_days)
                    <div class="budget-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        <div class="flex flex-col text-xs">
                            <span class="text-[var(--ink-soft)] font-medium">Duration</span>
                            <span class="font-semibold text-[var(--ink)] text-sm">{{ $plan->total_days }} day{{ $plan->total_days > 1 ? 's' : '' }}</span>
                        </div>
                    </div>
                @endif
                <div class="budget-row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <div class="flex flex-col text-xs">
                        <span class="text-[var(--ink-soft)] font-medium">Shared via</span>
                        <span class="font-semibold text-[var(--ink)] text-sm">{{ $plan->ai_provider === 'groq' ? 'AI generated' : 'Local generator' }}</span>
                    </div>
                </div>
            </div>

            <!-- Itinerary Section -->
            <div class="itinerary-box">
                <h2 class="section-title">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                    Itinerary Details
                </h2>
                @if($plan->ai_recommendation)
                    <div class="markdown-body text-[var(--ink)] text-sm leading-relaxed mt-4 space-y-3">
                        {!! nl2br(e($plan->ai_recommendation)) !!}
                    </div>
                @else
                    <p class="text-[var(--ink-soft)] italic text-sm mt-2">No itinerary available for this plan.</p>
                @endif
            </div>
        </div>
    </main>

    <script>
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navLinks = document.getElementById('navLinks');

        mobileMenuBtn.addEventListener('click', () => {
            navLinks.classList.toggle('open');
        });
    </script>
</body>
</html>
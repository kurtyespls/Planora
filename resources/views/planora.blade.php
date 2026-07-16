<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PLANORA — Your Dagupan Trip, Planned</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/planora-design.css">

    <style>
        :root {
            --deep-teal: #0B3D3A;
            --deep-teal-ink: #0E2E2B;
            --sand: #F6EDE0;
            --sand-deep: #EDE0CB;
            --card: #FFFDF9;
            --ember: #D9622B;
            --ember-deep: #B94E1F;
            --sage: #7C9187;
            --ink: #1B2624;
            --ink-soft: #5B6763;
            --line: #E2D8C4;
        }

        * { font-family: 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'Fraunces', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', ui-monospace, monospace; }

        body {
            background-color: var(--sand);
            background-image:
                radial-gradient(circle at 8% 12%, rgba(11,61,58,0.05) 0%, transparent 45%),
                radial-gradient(circle at 92% 85%, rgba(217,98,43,0.06) 0%, transparent 40%);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* ── Top Navigation Bar ── */
        .top-nav {
            position: sticky;
            top: 0;
            z-index: 40;
            background: rgba(255, 253, 249, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--line);
            margin-bottom: 1rem;
        }
        .top-nav-inner {
            max-width: 1120px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .top-nav-inner::-webkit-scrollbar { display: none; }
        .top-nav .brand {
            font-size: 1.1rem;
            gap: 8px;
        }
        .top-nav .brand-mark {
            width: 32px;
            height: 32px;
            font-size: 0.9rem;
        }
        .top-nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            color: var(--ink);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .top-nav-link:hover {
            background: var(--sand-deep);
            color: var(--deep-teal);
        }
        .top-nav-link.active {
            background: var(--deep-teal);
            color: var(--sand);
        }

        #map { height: 420px; width: 100%; border-radius: 0.9rem; display: none; z-index: 1; border: 1.5px solid var(--line); }
        .step { display: none; }
        .step.active { display: block; animation: stepFadeIn 0.45s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes stepFadeIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }

        @keyframes hotelCardIn { from { opacity: 0; transform: translateY(10px) scale(0.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        .hotel-card-enter { animation: hotelCardIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) both; }

        @keyframes pulseSoft { 0%, 100% { opacity: 1; } 50% { opacity: 0.55; } }
        .pulse-soft { animation: pulseSoft 1.6s ease-in-out infinite; }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        .skeleton {
            background: linear-gradient(90deg, var(--line) 25%, var(--sand-deep) 50%, var(--line) 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s ease-in-out infinite;
            border-radius: 0.5rem;
        }
        .skeleton-card { height: 180px; }
        .skeleton-text { height: 14px; margin-bottom: 8px; }
        .skeleton-text-sm { height: 10px; width: 60%; margin-bottom: 6px; }
        .skeleton-btn { height: 40px; width: 100%; }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.5s ease-out forwards; }

        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        .animate-scale-in { animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-slide-in-right { animation: slideInRight 0.4s ease-out forwards; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .animate-spin-slow { animation: spin 1.5s linear infinite; }

        @media (prefers-reduced-motion: reduce) {
            .step.active, .hotel-card-enter, .pulse-soft { animation: none !important; }
        }

        .ticket-frame { position: relative; background: var(--card); border: 1px solid var(--line); transition: box-shadow 0.3s ease; }
        .ticket-frame:hover { box-shadow: 0 15px 50px -15px rgba(11,61,58,0.3); }

        .stub-track { display: flex; align-items: center; gap: 0; }
        .stub-node {
            display: flex; align-items: center; justify-content: center;
            width: 34px; height: 34px; border-radius: 999px;
            border: 1.5px solid var(--line); background: var(--card);
            font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; font-weight: 700;
            color: var(--ink-soft); flex-shrink: 0;
            transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: default;
        }
        .stub-node.is-active { border-color: var(--ember); background: var(--ember); color: white; box-shadow: 0 0 0 4px rgba(217,98,43,0.15); transform: scale(1.1); }
        .stub-node.is-done { border-color: var(--deep-teal); background: var(--deep-teal); color: var(--sand); }
        .stub-line {
            flex: 1; height: 1.5px;
            background-image: linear-gradient(to right, var(--line) 60%, transparent 0%);
            background-size: 8px 1.5px; background-repeat: repeat-x; margin: 0 6px;
        }

        .markdown-body ul { list-style: none; padding-left: 0; margin-bottom: 0.25rem; }
        .markdown-body li { margin-bottom: 0.55rem; padding-left: 1.5rem; position: relative; line-height: 1.55; color: var(--ink-soft); }
        .markdown-body li::before { content: "→"; position: absolute; left: 0; color: var(--ember); font-weight: 700; }
        .markdown-body li strong { color: var(--ink); }
        .markdown-body p { margin-bottom: 0.75rem; line-height: 1.6; color: var(--ink-soft); }

        .itinerary-day { background: var(--card); border: 1px solid var(--line); border-radius: 0.9rem; margin-bottom: 0.75rem; overflow: hidden; transition: all 0.3s ease; }
        .itinerary-day:hover { border-color: var(--ember); box-shadow: 0 4px 12px rgba(11,61,58,0.08); }
        .itinerary-day summary {
            list-style: none; cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.9rem 1.1rem;
            background: var(--sand-deep); font-family: 'Fraunces', serif; font-weight: 600; color: var(--deep-teal);
            transition: background 0.2s ease;
        }
        .itinerary-day summary:hover { background: var(--line); }
        .itinerary-day summary::-webkit-details-marker { display: none; }
        .itinerary-day .chevron { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--ember); transition: transform 0.25s ease; flex-shrink: 0; }
        .itinerary-day[open] .chevron { transform: rotate(180deg); }
        .itinerary-day .day-body { padding: 1rem 1.1rem 1.1rem; animation: fadeInUp 0.3s ease-out; }
        .itinerary-day + .itinerary-day { margin-top: 0; }

        .budget-warning { background: #FDF1E6; border: 1px solid var(--ember); color: var(--ember-deep); border-radius: 0.85rem; padding: 0.85rem 1.1rem; font-size: 0.85rem; line-height: 1.5; margin-bottom: 1.25rem; }

        .leaflet-routing-container { display: none !important; }

        .map-pin {
            display: flex; align-items: center; justify-content: center; width: 38px; height: 38px;
            border-radius: 999px; border: 2.5px solid var(--card); box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35); font-size: 1.15rem; line-height: 1;
            transition: transform 0.2s ease;
        }
        .map-pin:hover { transform: scale(1.15); }
        .map-pin.pin-hotel { background: var(--deep-teal); }
        .map-pin.pin-user { background: var(--ember); }
        .map-pin.pin-restaurant { background: #C2410C; }
        .map-pin.pin-mall { background: #0E5F5A; }
        .map-pin.pin-tourist { background: #2E7D32; }
        .map-pin.pin-beach { background: #0891B2; }

        /* —— Enhanced Map Styles —— */
        .map-pin.pin-hotel-pulse {
            animation: hotelPulse 2s ease-in-out infinite;
        }
        @keyframes hotelPulse {
            0%, 100% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 0 rgba(217,98,43,0.7); }
            50% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 12px rgba(217,98,43,0); }
        }

        .leaflet-popup-content-wrapper {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 0.9rem;
            box-shadow: 0 10px 40px -15px rgba(11,61,58,0.3);
            font-family: 'Inter', system-ui, sans-serif;
        }
        .leaflet-popup-tip {
            background: var(--card);
            border: 1px solid var(--line);
        }
        .leaflet-popup-content {
            margin: 0.9rem 1rem;
            font-size: 0.9rem;
            line-height: 1.5;
            color: var(--ink);
        }
        .leaflet-popup-content b {
            color: var(--deep-teal);
            font-family: 'Fraunces', serif;
            font-weight: 600;
            font-size: 1rem;
        }
        .leaflet-popup-close-button {
            color: var(--ink-soft) !important;
            font-size: 1.5rem !important;
            padding: 0.4rem 0.6rem !important;
        }
        .leaflet-popup-close-button:hover {
            color: var(--ember) !important;
        }

        .leaflet-control-zoom {
            border: 1.5px solid var(--line) !important;
            border-radius: 0.75rem !important;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(11,61,58,0.1);
        }
        .leaflet-control-zoom a {
            background: var(--card) !important;
            color: var(--deep-teal) !important;
            border-bottom: 1px solid var(--line) !important;
            width: 36px !important;
            height: 36px !important;
            line-height: 36px !important;
            font-size: 1.1rem !important;
            transition: all 0.2s ease;
        }
        .leaflet-control-zoom a:hover {
            background: var(--sand-deep) !important;
            color: var(--ember) !important;
        }
        .leaflet-control-zoom a:last-child {
            border-bottom: none !important;
        }

        .leaflet-control-attribution {
            background: rgba(255,253,249,0.9) !important;
            color: var(--sage) !important;
            font-size: 0.65rem !important;
            padding: 2px 8px !important;
        }
        .leaflet-control-attribution a {
            color: var(--deep-teal) !important;
        }

        .distance-circle {
            stroke-dasharray: 8, 4;
            animation: circleDash 30s linear infinite;
        }
        @keyframes circleDash {
            to { stroke-dashoffset: -1000; }
        }

        .route-line {
            stroke-dasharray: 12, 6;
            animation: routeFlow 1.5s linear infinite;
        }
        @keyframes routeFlow {
            to { stroke-dashoffset: -18; }
        }

        #hotel-list::-webkit-scrollbar { width: 6px; }
        #hotel-list::-webkit-scrollbar-track { background: var(--sand-deep); border-radius: 8px; }
        #hotel-list::-webkit-scrollbar-thumb { background: var(--sage); border-radius: 8px; }
        #hotel-list::-webkit-scrollbar-thumb:hover { background: var(--deep-teal); }

        .focus-ring:focus-visible { outline: 2px solid var(--ember); outline-offset: 2px; }

        /* —— Smooth scrolling —— */
        html { scroll-behavior: smooth; }

        /* —— Selection styling —— */
        ::selection { background: var(--ember); color: white; }

        .btn-primary {
            background: var(--deep-teal); color: var(--sand);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative; overflow: hidden;
        }
        .btn-primary:hover:not(:disabled) {
            background: var(--deep-teal-ink); transform: translateY(-2px);
            box-shadow: 0 10px 25px -8px rgba(11,61,58,0.5);
        }
        .btn-primary:active:not(:disabled) { transform: translateY(0); }
        .btn-primary:disabled { background: #C9BFAE; opacity: 0.6; cursor: not-allowed; }
        .btn-primary::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%);
        }
        .btn-primary:hover:not(:disabled)::after { transform: translateX(100%); transition: transform 0.6s; }

        .input-field {
            background: var(--card); border: 1.5px solid var(--line);
            transition: all 0.2s ease;
        }
        .input-field:focus {
            border-color: var(--deep-teal);
            box-shadow: 0 0 0 3px rgba(11,61,58,0.1), 0 0 0 6px rgba(11,61,58,0.05);
            outline: none;
        }
        .input-field::placeholder { color: var(--sage); opacity: 0.7; }

        .rest-chip {
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer; user-select: none;
        }
        .rest-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,61,58,0.1); }
        .rest-chip:has(input:checked) {
            background: var(--deep-teal); border-color: var(--deep-teal); color: var(--sand);
            transform: scale(1.02);
        }
        .rest-chip:has(input:checked) .chip-text-sub { color: var(--sage); }

        .map-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; font-size: 0.72rem; color: var(--ink-soft); margin-top: 0.6rem; }
        .map-legend span { display: inline-flex; align-items: center; gap: 0.35rem; transition: transform 0.2s ease; }
        .map-legend span:hover { transform: translateY(-1px); }
        .legend-dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; box-shadow: 0 0 0 2px rgba(0,0,0,0.1); }

        /* —— Responsive utilities —— */
        @media (max-width: 640px) {
            .hotel-card-enter { animation: none; }
            .step.active { animation: none; }
            .ticket-frame { padding: 1rem; }
            #map { height: 320px; }
        }

        /* —— Touch device optimizations —— */
        @media (hover: none) {
            .hotel-card-enter:hover {
                transform: none;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
        }

        /* —— Print styles —— */
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }

        /* —— Toast notifications —— */
        #toast-container {
            position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.6rem; pointer-events: none;
        }
        .toast {
            pointer-events: auto; padding: 0.85rem 1.25rem; border-radius: 0.85rem;
            font-size: 0.85rem; font-weight: 500; line-height: 1.4;
            box-shadow: 0 8px 30px -8px rgba(0,0,0,0.25);
            transform: translateX(120%); opacity: 0;
            transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
            max-width: 380px; border: 1px solid transparent;
        }
        .toast.show { transform: translateX(0); opacity: 1; }
        .toast.toast-success { background: #E8F5E9; color: #1B5E20; border-color: #A5D6A7; }
        .toast.toast-error { background: #FFEBEE; color: #B71C1C; border-color: #EF9A9A; }
        .toast.toast-warning { background: #FFF8E1; color: #E65100; border-color: #FFE082; }
        .toast.toast-info { background: #E3F2FD; color: #0D47A1; border-color: #90CAF9; }

        /* —— Progress overlay —— */
        #progress-overlay {
            position: fixed; inset: 0; z-index: 9998;
            background: rgba(11,61,58,0.6); backdrop-filter: blur(4px);
            display: none; align-items: center; justify-content: center;
            opacity: 0; transition: opacity 0.3s ease;
        }
        #progress-overlay.active { display: flex; opacity: 1; }
        #progress-box {
            background: var(--card); border-radius: 1.25rem; padding: 2rem 2.5rem;
            max-width: 400px; width: 90%; text-align: center;
            box-shadow: 0 20px 60px -20px rgba(0,0,0,0.4);
            transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #progress-overlay.active #progress-box { transform: scale(1); }
        #progress-bar-track {
            width: 100%; height: 6px; background: var(--line); border-radius: 8px;
            overflow: hidden; margin: 1rem 0 0.5rem;
        }
        #progress-bar-fill {
            height: 100%; width: 0%; background: linear-gradient(90deg, var(--ember), var(--deep-teal));
            border-radius: 8px; transition: width 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #progress-label { font-size: 0.8rem; color: var(--ink-soft); font-family: 'JetBrains Mono', monospace; }
        #progress-step { font-size: 0.7rem; color: var(--sage); font-family: 'JetBrains Mono', monospace; margin-top: 0.25rem; }

        /* —— Image blur-in —— */
        .blur-in { filter: blur(20px); transition: filter 0.5s ease; }
        .blur-in.loaded { filter: blur(0); }

        /* —— Retry button —— */
        .retry-btn {
            background: transparent; border: 1.5px solid var(--ember); color: var(--ember-deep);
            padding: 0.5rem 1.25rem; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s ease;
        }
        .retry-btn:hover { background: var(--ember); color: white; }

        /* —— Performance optimizations —— */
        .hotel-card-enter { will-change: transform, opacity; }
        .btn-primary { will-change: transform; }
        .toast { will-change: transform, opacity; }

        /* —— Hotel card image consistency —— */
        .hotel-card-image {
            height: 240px;
            min-height: 240px;
        }
        @media (min-width: 640px) {
            .hotel-card-image {
                width: 320px;
                min-width: 320px;
                height: 100%;
                min-height: 280px;
            }
        }
    </style>
</head>
    <body class="text-[var(--ink)] min-h-screen px-4 pb-8 md:px-8" x-data="{ showProfilePanel: false }">

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand"><span class="brand-mark">⌁</span><span>planora</span></a>
            <div class="flex items-center gap-3">
                @auth
                <button @click="showProfilePanel = true" type="button" class="top-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a7 7 0 1 0-14 0"/><circle cx="12" cy="7" r="4"/></svg>
                    My Profile
                </button>
                @else
                <a href="/login" class="top-nav-link">Login</a>
                <a href="/register" class="top-nav-link">Sign Up</a>
                @endauth
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

    <div class="planner-shell max-w-4xl mx-auto">
        <header class="planner-hero mb-3 animate-fade-in-up">
            <div class="flex-1">
                <h1 class="font-display font-normal tracking-tight leading-none">Where to next?</h1>
                <p class="mt-3 text-[var(--ink-soft)] text-sm md:text-base">Build a personalized city escape around your time and budget.</p>
            </div>
        </header>

        <div class="mb-6 px-2 animate-fade-in-up" style="animation-delay: 0.1s;">
            <div class="stub-track">
                <div class="stub-node" id="stub-1" data-step="1">01</div>
                <div class="stub-line"></div>
                <div class="stub-node" id="stub-2" data-step="2">02</div>
                <div class="stub-line"></div>
                <div class="stub-node" id="stub-3" data-step="3">03</div>
            </div>
            <div class="flex justify-between mt-2 px-1 font-mono text-[0.62rem] uppercase tracking-wider text-[var(--sage)]">
                <span>Stay</span>
                <span>Details</span>
                <span>Itinerary</span>
            </div>
        </div>

        <div class="ticket-frame rounded-2xl p-6 md:p-10 animate-fade-in-up" style="animation-delay: 0.2s;">

            <div id="step-1" class="step active">
                <div class="mb-5">
                    <form id="hotel-search-form" class="flex items-center gap-2 max-w-md mx-auto relative">
                        <div class="relative flex-1">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[var(--sage)]">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                            </span>
                            <input type="text" id="hotel-search-input" placeholder="Search hotels by name..." class="input-field w-full pl-10 pr-10 p-2.5 rounded-xl text-sm focus-ring" autocomplete="off">
                            <button type="button" id="clear-search" class="absolute right-3 top-1/2 -translate-y-1/2 text-[var(--sage)] hover:text-[var(--ember)] transition hidden">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
                            </button>
                        </div>
                        <button type="submit" class="btn-primary px-5 py-2.5 rounded-xl text-sm font-bold focus-ring flex items-center gap-2">
                            <span>Search</span>
                        </button>
                    </form>
                    <div id="search-status" class="mt-3 text-left hidden"></div>
                </div>

                <div class="flex items-baseline justify-between mb-1 flex-wrap gap-y-1">
                    <h2 class="font-display text-2xl font-semibold text-[var(--ink)]">Choose your stay</h2>
                    <span class="font-mono text-[0.65rem] uppercase tracking-wider text-[var(--sage)]">Dagupan, Pangasinan</span>
                </div>
                <p id="hotel-status" class="text-sm text-[var(--ink-soft)] mb-5 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--ember)] pulse-soft"></span>
                    Fetching top-rated accommodations for you…
                </p>
                <div id="hotel-list" class="grid grid-cols-1 gap-5 items-stretch max-h-[480px] sm:max-h-[640px] overflow-y-auto pr-2 pb-2">
                </div>
            </div>

            <div id="step-2" class="step">
                <button onclick="goBackToStep1()" class="text-sm text-[var(--deep-teal)] font-semibold mb-5 hover:underline flex items-center gap-1 focus-ring rounded">
                    <span aria-hidden="true">←</span> Back to hotels
                </button>
                <h2 class="font-display text-2xl font-semibold text-[var(--ink)] mb-1">Trip details</h2>
                <p class="text-sm text-[var(--ink-soft)] mb-4">Selected stay: <span id="display-selected-hotel" class="font-semibold text-[var(--ember-deep)]"></span></p>

                <div class="flex flex-col sm:flex-row gap-5 mb-8 p-4 rounded-2xl border border-[var(--line)] bg-[var(--card)]">
                    <div class="relative w-full sm:w-52 h-52 sm:h-48 flex-shrink-0">
                        <img id="selected-hotel-image" src="" alt="" class="w-full h-full rounded-xl object-cover object-center border border-[var(--line)]" style="background: var(--line);">
                    </div>
                    <div class="flex flex-col justify-center min-w-0 w-full gap-3">
                        <div class="flex items-center justify-between gap-3 flex-wrap">
                            <div class="flex items-center text-sm">
                                <span id="selected-hotel-stars" class="text-[var(--ember)] text-base mr-1.5 tracking-tight"></span>
                                <span id="selected-hotel-rating" class="font-mono font-bold text-[var(--ink-soft)]"></span>
                            </div>
                            <button id="btn-view-details-step2" onclick="openHotelModal(selectedHotelIdx)"
                                    class="focus-ring bg-transparent border-[1.5px] border-[var(--deep-teal)] text-[var(--deep-teal)] py-2 px-4 rounded-lg text-sm font-bold hover:bg-[var(--sand-deep)] transition flex items-center gap-2 whitespace-nowrap">
                                View details & Amenities →
                            </button>
                        </div>
                        <div id="selected-hotel-description" class="font-mono font-bold text-[var(--deep-teal)] text-base"></div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block mb-2 font-medium text-sm text-[var(--ink)]">How many days?</label>
                        <input type="number" id="trip-days" class="input-field w-full p-3 rounded-xl font-mono focus-ring" min="1" max="30" value="1">
                    </div>
                    <div>
                        <label class="block mb-2 font-medium text-sm text-[var(--ink)]">Budget (PHP)</label>
                        <input type="number" id="trip-budget" class="input-field w-full p-3 rounded-xl font-mono focus-ring" min="1" placeholder="How much your Budget?">
                    </div>
                </div>
                <p id="budget-hint" class="text-xs text-[var(--sage)] mb-6 font-mono"></p>

                <label class="block mb-3 font-medium text-sm text-[var(--ink)]">Rest schedule <span class="text-[var(--sage)] font-normal">— select your preference</span></label>
                <div class="grid grid-cols-3 gap-3 mb-9">
                    <label class="rest-chip flex flex-col items-start gap-1 cursor-pointer p-4 rounded-xl border-[1.5px] border-[var(--line)] bg-[var(--card)] hover:border-[var(--deep-teal)] transition">
                        <input type="checkbox" class="rest-checkbox sr-only" value="Morning">
                        <span class="font-semibold text-sm">Morning</span>
                        <span class="chip-text-sub text-[0.65rem] text-[var(--ink-soft)] uppercase tracking-wide">08:00 — 12:00</span>
                    </label>
                    <label class="rest-chip flex flex-col items-start gap-1 cursor-pointer p-4 rounded-xl border-[1.5px] border-[var(--line)] bg-[var(--card)] hover:border-[var(--deep-teal)] transition">
                        <input type="checkbox" class="rest-checkbox sr-only" value="Afternoon">
                        <span class="font-semibold text-sm">Afternoon</span>
                        <span class="chip-text-sub text-[0.65rem] text-[var(--ink-soft)] uppercase tracking-wide">13:00 — 17:00</span>
                    </label>
                    <label class="rest-chip flex flex-col items-start gap-1 cursor-pointer p-4 rounded-xl border-[1.5px] border-[var(--line)] bg-[var(--card)] hover:border-[var(--deep-teal)] transition">
                        <input type="checkbox" class="rest-checkbox sr-only" value="Night Shift">
                        <span class="font-semibold text-sm">Night Shift</span>
                        <span class="chip-text-sub text-[0.65rem] text-[var(--ink-soft)] uppercase tracking-wide">19:00 — 23:00</span>
                    </label>
                </div>

                <button id="btn-confirm" onclick="confirmPlan()" class="btn-primary focus-ring w-full p-4 rounded-xl font-bold flex justify-center items-center gap-2">
                    Generate smart itinerary
                </button>
            </div>

            <div id="step-3" class="step">
                <div class="flex items-baseline justify-between mb-4 flex-wrap gap-y-1">
                    <h2 class="font-display text-2xl font-semibold text-[var(--ink)]">Your itinerary</h2>
                    <span class="font-mono text-[0.65rem] uppercase tracking-wider text-[var(--sage)]">Boarding: now</span>
                </div>
                <div id="map" class="mb-2 border border-[var(--line)]"></div>
                <div class="map-legend mb-6">
                    <span><span class="legend-dot" style="background:#0B3D3A"></span>Your hotel</span>
                    <span><span class="legend-dot" style="background:#C2410C"></span>Restaurants</span>
                    <span><span class="legend-dot" style="background:#0E5F5A"></span>Malls</span>
                    <span><span class="legend-dot" style="background:#2E7D32"></span>Tourist spots</span>
                    <span><span class="legend-dot" style="background:#0891B2"></span>Beaches</span>
                </div>

                <div id="budget-warning-box" class="budget-warning hidden"></div>
                <div id="ai-output"></div>

                <button onclick="location.reload()" class="mt-6 w-full bg-transparent text-[var(--deep-teal)] border-[1.5px] border-[var(--line)] p-4 rounded-xl font-bold hover:bg-[var(--sand-deep)] transition focus-ring">
                    Plan another trip
                </button>
            </div>
        </div>

        <footer class="text-center mt-6 font-mono text-[0.62rem] uppercase tracking-widest text-[var(--sage)]">
            Planora · Itinerary Systems Desk · Dagupan City
        </footer>
    </div>

    <!-- —— Toast Container —— -->
    <div id="toast-container"></div>

    <!-- —— Progress Overlay —— -->
    <div id="progress-overlay">
        <div id="progress-box">
            <div class="font-display text-xl font-semibold text-[var(--deep-teal)] mb-2">Planning your trip</div>
            <p id="progress-label" class="text-sm text-[var(--ink-soft)]">Gathering nearby places…</p>
            <div id="progress-bar-track">
                <div id="progress-bar-fill"></div>
            </div>
            <p id="progress-step" class="font-mono">Step 1 of 5</p>
        </div>
    </div>

    <div id="hotel-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/60 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-300">
        <div class="bg-[var(--card)] w-full max-w-lg max-h-[90vh] rounded-2xl overflow-hidden shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col" id="hotel-modal-content">
            <div class="relative h-56 sm:h-72 bg-[var(--line)] group flex-shrink-0">
                <img id="modal-slider-img" src="" class="w-full h-full object-cover object-center transition-opacity duration-300" alt="Hotel Image" style="background: var(--line);">
                <button onclick="prevImage()" class="absolute left-2 sm:left-3 top-1/2 -translate-y-1/2 bg-black/40 text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-[var(--ember)] active:bg-[var(--ember)] transition opacity-90">←</button>
                <button onclick="nextImage()" class="absolute right-2 sm:right-3 top-1/2 -translate-y-1/2 bg-black/40 text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-[var(--ember)] active:bg-[var(--ember)] transition opacity-90">→</button>
                <button onclick="closeModal()" class="absolute top-3 right-3 bg-black/50 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/80 transition">×</button>
            </div>
            <div class="p-5 sm:p-6 overflow-y-auto flex-1">
                <h3 id="modal-hotel-title" class="font-display font-semibold text-xl sm:text-2xl text-[var(--ink)] mb-2"></h3>
                <a id="modal-address-link" href="#" target="_blank" rel="noopener" class="inline-flex items-start gap-1.5 text-sm text-[var(--ink-soft)] mb-5 pb-5 border-b border-[var(--line)] w-full hover:text-[var(--deep-teal)] transition-colors">
                    <span class="font-mono text-xs font-bold text-[var(--deep-teal)]" aria-hidden="true">ADDRESS</span>
                    <span id="modal-address-text"></span>
                </a>
                <h4 class="font-medium text-sm text-[var(--ink)] mb-3 flex items-center gap-2">
                    <span class="font-mono text-xs font-bold text-[var(--deep-teal)]" aria-hidden="true">AMENITIES</span> Available Amenities
                </h4>
                <div id="modal-amenities-list" class="flex flex-wrap gap-2 text-xs"></div>
            </div>
            <div class="p-5 sm:p-6 pt-4 border-t border-[var(--line)] flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-4 flex-shrink-0">
                <div class="flex-1 min-w-0">
                    <p class="font-mono text-[0.62rem] uppercase tracking-wider text-[var(--sage)] mb-0.5">Rate</p>
                    <p id="modal-price" class="font-display font-semibold text-lg text-[var(--deep-teal)] truncate"></p>
                </div>
                <button onclick="selectFromModal()" class="btn-primary focus-ring w-full sm:w-auto px-6 py-3 rounded-xl font-bold whitespace-nowrap">
                    Select this hotel
                </button>
            </div>
        </div>
    </div>

    <script>
        const openWeatherKey = "{{ env('OPENWEATHER_API_KEY', '') }}";
        const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
        let map;
        let markerLayers;
        let currentRouteControl = null;
        let selectedLat = 16.0438;
        let selectedLon = 120.3331;
        let hotelName = '';
        let selectedHotelIdx = null;
        let nearbyPlacesData = [];
        let currentHotelPrice = 0;
        let allHotels = [];

        // Format a stored price (e.g. "2800" or "2,800") into "2,800" with thousands separator
        function formatPrice(price) {
            const num = parseFloat(String(price).replace(/[₱,\s]/g, ''));
            if (isNaN(num)) return '0';
            return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        let currentGallery = [];
        let currentImageIndex = 0;

        // —— Debounce utility ——
        function debounce(fn, wait) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        // —— Toast system ——
        function showToast(message, type = 'info', duration = 4000) {
            const container = document.getElementById('toast-container');
            const labels = { success: 'SUCCESS', error: 'ERROR', warning: 'WARNING', info: 'INFO' };
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `<span style="font-weight:700;margin-right:0.5rem;font-family:'JetBrains Mono',monospace;font-size:0.7rem;">${labels[type] || 'INFO'}</span>${message}`;
            container.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('show'));
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 400);
            }, duration);
        }

        // —— Progress overlay ——
        const PROGRESS_STEPS = [
            { label: 'Setting up map…', pct: 10 },
            { label: 'Checking weather…', pct: 30 },
            { label: 'Finding nearby places…', pct: 55 },
            { label: 'Generating your itinerary…', pct: 80 },
            { label: 'Finalizing plans…', pct: 100 },
        ];

        function showProgress(stepIndex) {
            const overlay = document.getElementById('progress-overlay');
            overlay.classList.add('active');
            if (stepIndex < PROGRESS_STEPS.length) {
                const step = PROGRESS_STEPS[stepIndex];
                document.getElementById('progress-label').textContent = step.label;
                document.getElementById('progress-bar-fill').style.width = step.pct + '%';
                document.getElementById('progress-step').textContent = `Step ${stepIndex + 1} of ${PROGRESS_STEPS.length}`;
            }
        }

        function hideProgress() {
            const overlay = document.getElementById('progress-overlay');
            overlay.classList.remove('active');
            document.getElementById('progress-bar-fill').style.width = '0%';
        }

        // —— Image blur-in helper ——
        function setupBlurIn(imgEl) {
            imgEl.classList.add('blur-in');
            if (imgEl.complete && imgEl.naturalWidth > 0) {
                imgEl.classList.add('loaded');
            } else {
                imgEl.addEventListener('load', () => imgEl.classList.add('loaded'), { once: true });
                imgEl.addEventListener('error', () => imgEl.classList.add('loaded'), { once: true });
            }
        }

        // —— Skeleton loading ——
        function showHotelSkeletons(count = 3) {
            const list = document.getElementById('hotel-list');
            list.innerHTML = '';
            for (let i = 0; i < count; i++) {
                const sk = document.createElement('div');
                sk.className = 'flex flex-col sm:flex-row bg-[var(--card)] border border-[var(--line)] rounded-2xl overflow-hidden';
                sk.innerHTML = `
                    <div class="h-64 sm:h-auto sm:w-1/2 skeleton skeleton-card"></div>
                    <div class="p-5 sm:p-6 flex-1 flex flex-col gap-3">
                        <div class="skeleton skeleton-text w-3/4"></div>
                        <div class="skeleton skeleton-text-sm"></div>
                        <div class="skeleton skeleton-text w-full"></div>
                        <div class="skeleton skeleton-text w-5/6"></div>
                        <div class="skeleton skeleton-btn mt-auto" style="height:42px;border-radius:12px;"></div>
                    </div>
                `;
                list.appendChild(sk);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchHotels();
            const debouncedCheck = debounce(checkBudget, 200);
            document.getElementById('trip-days').addEventListener('input', debouncedCheck);
            document.getElementById('trip-budget').addEventListener('input', debouncedCheck);
            if (sessionStorage.getItem('planora_selected_idx') !== null) {
                const idx = parseInt(sessionStorage.getItem('planora_selected_idx'));
                if (allHotels[idx]) selectHotel(idx);
            }
        });

        function preserveHotelSelection(idx) {
            sessionStorage.setItem('planora_selected_idx', idx);
        }

        function setActiveStub(stepNum) {
            for (let i = 1; i <= 3; i++) {
                const node = document.getElementById(`stub-${i}`);
                node.classList.remove('is-active', 'is-done');
                if (i < stepNum) {
                    node.classList.add('is-done');
                    node.innerHTML = '✓';
                } else if (i === stepNum) {
                    node.classList.add('is-active');
                    node.innerHTML = '0' + i;
                } else {
                    node.innerHTML = '0' + i;
                }
            }
        }

        async function fetchHotels() {
            const list = document.getElementById('hotel-list');
            const statusEl = document.getElementById('hotel-status');
            showHotelSkeletons(3);
            statusEl.innerHTML = '<span class="w-1.5 h-1.5 rounded-full bg-[var(--ember)] pulse-soft"></span> Fetching top-rated accommodations for you…';
            try {
                const res = await fetch(`/api/hotels`);
                if (!res.ok) throw new Error("Failed to fetch hotels");
                const hotels = await res.json();
                allHotels = hotels;
                statusEl.innerHTML = 'Select your preferred hotel to continue:';
                list.innerHTML = '';
                hotels.forEach((hotel, idx) => {
                    const starRating = (hotel.rating > 5) ? Math.round(hotel.rating / 2) : Math.round(hotel.rating);
                    const ratingText = Number(hotel.rating).toFixed(1) + ' / 10';
                    let stars = '★'.repeat(starRating) + '☆'.repeat(5 - starRating);
                    const card = document.createElement('div');
                    card.className = "hotel-card-enter flex flex-col sm:flex-row bg-[var(--card)] border border-[var(--line)] rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-[var(--ember)] transition duration-300 group";
                    card.style.animationDelay = `${idx * 60}ms`;
                    card.innerHTML = `
                        <div class="hotel-card-image overflow-hidden relative flex-shrink-0">
                            <img src="${hotel.image_url}" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500 blur-in" alt="${hotel.name}" loading="lazy" onload="this.classList.add('loaded')" onerror="this.classList.add('loaded')" style="background: var(--line);">
                        </div>
                        <div class="p-5 sm:p-6 flex-1 flex flex-col">
                            <h3 class="font-display font-semibold text-xl sm:text-2xl text-[var(--ink)] mb-1.5 leading-tight">${hotel.name}</h3>
                            <div class="flex items-center text-sm mb-3">
                                <span class="text-[var(--ember)] text-base mr-1.5 tracking-tight">${stars}</span>
                                <span class="font-mono font-bold text-[var(--ink-soft)] mr-1">${ratingText}</span>
                            </div>
                            <p class="text-sm text-[var(--ink-soft)] mb-3 leading-relaxed">${hotel.description}</p>
                            <div class="font-mono text-sm font-bold text-[var(--deep-teal)] mb-5">₱${formatPrice(hotel.price)} / night</div>
                            <div class="flex flex-col sm:flex-row gap-2.5 mt-auto">
                                <button onclick="openHotelModal(${idx})" class="focus-ring w-full sm:flex-1 bg-transparent border-[1.5px] border-[var(--line)] text-[var(--ink-soft)] py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center gap-1.5 hover:border-[var(--deep-teal)] hover:text-[var(--deep-teal)] transition-all">View details</button>
                                <button onclick="selectHotel(${idx})" class="btn-primary focus-ring w-full sm:flex-1 py-3 px-4 rounded-xl text-sm font-bold flex items-center justify-center gap-1.5 whitespace-nowrap">Select this hotel</button>
                            </div>
                        </div>
                    `;
                    list.appendChild(card);
                    card.querySelectorAll('img').forEach(setupBlurIn);
                });
            } catch (error) {
                console.error(error);
                statusEl.innerHTML = '';
                list.innerHTML = `
                    <div class="text-center p-8 bg-[var(--card)] border border-[var(--line)] rounded-2xl">
                        <p class="text-[var(--ember-deep)] font-semibold mb-3">Unable to load hotels</p>
                        <p class="text-sm text-[var(--ink-soft)] mb-4">${error.message || 'Connection issue'}</p>
                        <button onclick="fetchHotels()" class="retry-btn">Retry</button>
                    </div>
                `;
            }
        }

        function selectHotel(idx) {
            const hotel = allHotels[idx];
            if (!hotel) return;
            selectedHotelIdx = idx;
            hotelName = hotel.name;
            selectedLat = hotel.lat || 16.0438;
            selectedLon = hotel.lon || 120.3331;
            currentHotelPrice = parseFloat(String(hotel.price).replace(/[₱,]/g, ''));
            preserveHotelSelection(idx);
            document.getElementById('display-selected-hotel').innerText = hotel.name;
            const starRating = (hotel.rating > 5) ? Math.round(hotel.rating / 2) : Math.round(hotel.rating);
            const ratingText = Number(hotel.rating).toFixed(1) + ' / 10';
            const stars = '★'.repeat(starRating) + '☆'.repeat(5 - starRating);
            const imgEl = document.getElementById('selected-hotel-image');
            imgEl.src = hotel.image_url;
            imgEl.alt = hotel.name;
            setupBlurIn(imgEl);
            document.getElementById('selected-hotel-stars').innerText = stars;
            document.getElementById('selected-hotel-rating').innerText = ratingText;
            document.getElementById('selected-hotel-description').innerText = `Rate: ₱${formatPrice(hotel.price)} / night`;
            document.getElementById('step-1').classList.remove('active');
            document.getElementById('step-2').classList.add('active');
            setActiveStub(2);
            checkBudget();
        }

        function checkBudget() {
            const days = parseInt(document.getElementById('trip-days').value) || 1;
            const budget = parseFloat(document.getElementById('trip-budget').value) || 0;
            const btn = document.getElementById('btn-confirm');
            const hint = document.getElementById('budget-hint');
            const totalCost = currentHotelPrice * days;
            const remaining = budget - totalCost;
            if (budget < totalCost && budget > 0) {
                btn.disabled = true;
                hint.innerText = `Hotel alone costs ₱${totalCost.toLocaleString()} for ${days} day(s) — increase your budget.`;
                hint.className = 'text-xs text-[var(--ember-deep)] mb-6 font-mono';
            } else {
                btn.disabled = false;
                hint.className = 'text-xs text-[var(--sage)] mb-6 font-mono';
                if (budget > 0) {
                    const perDay = days > 0 ? Math.round(remaining / days) : 0;
                    hint.innerText = `≈ ₱${perDay.toLocaleString()}/day left for food & activities after the hotel.`;
                } else {
                    hint.innerText = '';
                }
            }
        }

        function goBackToStep1() {
            document.getElementById('step-2').classList.remove('active');
            document.getElementById('step-1').classList.add('active');
            setActiveStub(1);
        }

        async function confirmPlan() {
            const days = parseInt(document.getElementById('trip-days').value, 10);
            const budget = parseFloat(document.getElementById('trip-budget').value);
            let restDays = [];
            document.querySelectorAll('.rest-checkbox:checked').forEach(cb => restDays.push(cb.value));
            if (!budget || budget <= 0) {
                showToast('Please enter a valid budget amount.', 'warning');
                return;
            }
            toggleButtonState('btn-confirm', false);
            document.getElementById('step-2').classList.remove('active');
            document.getElementById('step-3').classList.add('active');
            setActiveStub(3);
            document.getElementById('map').style.display = 'block';
            nearbyPlacesData = [];
            initMap();
            showProgress(1);

            await fetchNearbyAmenities(selectedLat, selectedLon);
            showProgress(3);
            fetch('/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hotel: hotelName,
                    days: days,
                    budget: budget,
                    rest_days: restDays,
                    weather_desc: 'Weather data not available',
                    nearby_places: nearbyPlacesData.join('|')
                })
            })
            .then(async res => {
                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.error || `Server error (${res.status})`);
                }
                return res.json();
            })
            .then(data => {
                showProgress(4);
                renderItinerary(data.recommendation);
                const warningBox = document.getElementById('budget-warning-box');
                if (data.budget_warning) {
                    warningBox.innerText = data.budget_warning;
                    warningBox.classList.remove('hidden');
                } else {
                    warningBox.classList.add('hidden');
                }
                hideProgress();
                showToast('Your itinerary is ready!', 'success', 5000);
            })
            .catch(error => {
                hideProgress();
                if (!error.message.includes('budget') && !error.message.includes('Budget')) {
                    showToast(error.message || 'Something went wrong', 'error', 6000);
                } else {
                    showToast(error.message, 'warning', 6000);
                }
                document.getElementById('step-3').classList.remove('active');
                document.getElementById('step-2').classList.add('active');
                setActiveStub(2);
            })
            .finally(() => {
                toggleButtonState('btn-confirm', true);
            });
        }

        function renderItinerary(markdown) {
            const container = document.getElementById('ai-output');
            container.innerHTML = '';
            const sections = markdown.split(/\n(?=###\s)/).filter(s => s.trim() !== '');
            sections.forEach((section, idx) => {
                const headerMatch = section.match(/^###\s+(.*)/);
                const title = headerMatch ? headerMatch[1].trim() : 'Overview';
                const body = headerMatch ? section.replace(/^###\s+.*/, '') : section;
                const details = document.createElement('details');
                details.className = 'itinerary-day';
                if (idx === 0 || /summary/i.test(title)) details.open = true;
                const summary = document.createElement('summary');
                summary.innerHTML = `<span>${title}</span><span class="chevron">▾</span>`;
                const bodyDiv = document.createElement('div');
                bodyDiv.className = 'day-body markdown-body';
                bodyDiv.innerHTML = marked.parse(body);
                details.appendChild(summary);
                details.appendChild(bodyDiv);
                container.appendChild(details);
            });
        }

        function makeIcon(emoji, cls) {
            const labels = {
                'pin-hotel': 'H',
                'pin-user': 'U',
                'pin-restaurant': 'R',
                'pin-mall': 'M',
                'pin-tourist': 'T',
                'pin-beach': 'B'
            };
            const label = labels[cls] || '';
            return L.divIcon({
                className: '',
                html: `<div class="map-pin ${cls}"><span style="font-size:0.75rem;font-weight:700;color:white;font-family:'JetBrains Mono',monospace;">${label}</span></div>`,
                iconSize: [38, 38],
                iconAnchor: [19, 19],
                popupAnchor: [0, -19]
            });
        }

        // —— Weather helper: fetch live weather for a coordinate and render into a popup element ——
        function weatherIcon(main) {
            const m = (main || '').toLowerCase();
            if (m.includes('rain') || m.includes('drizzle')) return '🌧️';
            if (m.includes('thunder')) return '⛈️';
            if (m.includes('snow')) return '❄️';
            if (m.includes('cloud')) return '☁️';
            if (m.includes('clear')) return '☀️';
            if (m.includes('mist') || m.includes('fog') || m.includes('haze')) return '🌫️';
            return '🌡️';
        }

        function loadWeatherIntoPopup(lat, lon, elementId) {
            const el = document.getElementById(elementId);
            if (!el || el.dataset.loaded === '1') return;
            el.dataset.loaded = '1';
            el.innerHTML = '<span style="font-size:0.75rem;color:var(--sage);">Loading weather…</span>';
            fetch(`/api/weather?lat=${lat}&lon=${lon}`)
                .then(res => res.ok ? res.json() : null)
                .then(data => {
                    if (!data || data.error) {
                        el.innerHTML = '<span style="font-size:0.75rem;color:var(--ink-soft);">Weather unavailable</span>';
                        return;
                    }
                    const isRain = /rain|drizzle|thunder/i.test(data.main || '');
                    const rainTag = isRain
                        ? '<span style="display:inline-block;margin-left:0.35rem;background:#E3F2FD;color:#0D47A1;padding:0.1rem 0.4rem;border-radius:0.4rem;font-size:0.7rem;font-weight:600;">Maulan 🌧️</span>'
                        : '';
                    el.innerHTML = `
                        <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.5rem;padding-top:0.5rem;border-top:1px solid var(--line);">
                            <span style="font-size:1.4rem;line-height:1;">${weatherIcon(data.main)}</span>
                            <div style="line-height:1.3;">
                                <div style="font-weight:700;color:var(--deep-teal);font-size:0.95rem;">${Math.round(data.temp)}°C ${rainTag}</div>
                                <div style="font-size:0.72rem;color:var(--ink-soft);text-transform:capitalize;">${data.description || ''} · Feels ${Math.round(data.feels_like)}°C</div>
                                <div style="font-size:0.68rem;color:var(--sage);font-family:'JetBrains Mono',monospace;">Hum ${data.humidity}% · Wind ${Math.round(data.wind_speed)} m/s</div>
                            </div>
                        </div>`;
                })
                .catch(() => {
                    el.innerHTML = '<span style="font-size:0.75rem;color:var(--ink-soft);">Weather unavailable</span>';
                });
        }

        function initMap() {
            if (map) map.remove();
            map = L.map('map', {
                zoomControl: true,
                attributionControl: true
            }).setView([selectedLat, selectedLon], 14);
            markerLayers = L.layerGroup().addTo(map);

            // Custom styled tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                maxZoom: 18
            }).addTo(map);

            setTimeout(() => { map.invalidateSize(); }, 200);

            // Add distance circles (1km and 3km radius)
            L.circle([selectedLat, selectedLon], {
                radius: 1000,
                color: '#0B3D3A',
                fillColor: '#0B3D3A',
                fillOpacity: 0.05,
                weight: 1.5,
                dashArray: '8, 4',
                className: 'distance-circle'
            }).addTo(markerLayers).bindPopup('<b>1 km radius</b>');

            L.circle([selectedLat, selectedLon], {
                radius: 3000,
                color: '#D9622B',
                fillColor: '#D9622B',
                fillOpacity: 0.03,
                weight: 1,
                dashArray: '8, 4',
                className: 'distance-circle'
            }).addTo(markerLayers).bindPopup('<b>3 km radius</b>');

            // Hotel marker with pulse animation
            const hotelIcon = L.divIcon({
                className: '',
                html: `<div class="map-pin pin-hotel pin-hotel-pulse"><span style="font-size:0.75rem;font-weight:700;color:white;font-family:'JetBrains Mono',monospace;">H</span></div>`,
                iconSize: [38, 38],
                iconAnchor: [19, 19],
                popupAnchor: [0, -19]
            });

            L.marker([selectedLat, selectedLon], { icon: hotelIcon })
                .addTo(markerLayers)
                .bindPopup(`<b>${hotelName}</b><br><span style="color:var(--sage);font-size:0.85rem;">Your Basecamp</span>`)
                .openPopup();
        }

        window.drawRouteTo = function(targetLat, targetLon) {
            if (currentRouteControl) {
                map.removeControl(currentRouteControl);
            }

            // Smooth fly animation to target
            map.flyTo([targetLat, targetLon], 15, {
                duration: 1.2,
                easeLinearity: 0.3
            });

            currentRouteControl = L.Routing.control({
                waypoints: [
                    L.latLng(selectedLat, selectedLon),
                    L.latLng(targetLat, targetLon)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                show: false,
                createMarker: function() { return null; },
                lineOptions: {
                    styles: [
                        { color: '#D9622B', opacity: 0.8, weight: 5 },
                        { color: '#0B3D3A', opacity: 0.4, weight: 8 }
                    ],
                    addWaypoints: false
                }
            }).addTo(map);

            // Fit bounds to show both markers
            const bounds = L.latLngBounds([
                [selectedLat, selectedLon],
                [targetLat, targetLon]
            ]);
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });

            map.closePopup();
        };

        async function fetchNearbyAmenities(lat, lon) {
            try {
                const res = await fetch(`/api/nearby-places?lat=${lat}&lon=${lon}`);
                if (!res.ok) {
                    console.error('Nearby places endpoint failed:', res.status);
                    return;
                }
                const categorized = await res.json();
                if (!categorized) return;
                const iconMap = {
                    restaurant: { emoji: 'R', cls: 'pin-restaurant', label: 'Restaurant' },
                    mall: { emoji: 'M', cls: 'pin-mall', label: 'Mall' },
                    beach: { emoji: 'B', cls: 'pin-beach', label: 'Beach' },
                    tourist: { emoji: 'T', cls: 'pin-tourist', label: 'Tourist Spot' }
                };
                function haversineKm(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2)*Math.sin(dLat/2) +
                              Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*
                              Math.sin(dLon/2)*Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return R * c;
                }
                Object.keys(categorized).forEach(type => {
                    const places = categorized[type];
                    if (!places || !Array.isArray(places)) return;
                    places.forEach((place, pIdx) => {
                        const distKm = haversineKm(selectedLat, selectedLon, parseFloat(place.lat), parseFloat(place.lon));
                        const distText = distKm < 1
                            ? `${Math.round(distKm * 1000)}m away`
                            : `${distKm.toFixed(1)}km away`;
                        nearbyPlacesData.push(`${place.name} (${iconMap[type].label}, ${distText})`);
                        const weatherElId = `weather-popup-${type}-${pIdx}`;
                        let popupHTML = `
                            <div style="min-width: 180px;">
                                <b style="font-size: 1rem; margin-bottom: 0.4rem; display: block;">${place.name}</b>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${type === 'restaurant' ? '#C2410C' : type === 'mall' ? '#0E5F5A' : type === 'beach' ? '#0891B2' : '#2E7D32'}; box-shadow: 0 0 0 2px rgba(0,0,0,0.1);"></span>
                                    <span style="font-size: 0.8rem; color: var(--ink-soft);">${iconMap[type].label}</span>
                                    <span style="font-size: 0.75rem; color: var(--sage); margin-left: auto; font-family: 'JetBrains Mono', monospace;">${distText}</span>
                                </div>
                                <div id="${weatherElId}"></div>
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap;">
                                    <button onclick="drawRouteTo(${place.lat}, ${place.lon})" style="flex: 1; min-width: 100px; background: var(--deep-teal); color: white; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: 'Inter', sans-serif;" onmouseover="this.style.background='var(--ember)'" onmouseout="this.style.background='var(--deep-teal)'">Show Route</button>
                                    ${isAuthenticated ? `<button onclick="checkIn(${place.lat}, ${place.lon}, '${place.name.replace(/'/g, "\\'")}')" style="flex: 1; min-width: 100px; background: var(--ember); color: white; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: 'Inter', sans-serif;" onmouseover="this.style.background='var(--ember-deep)'" onmouseout="this.style.background='var(--ember)'">Check In</button>` : ''}
                                </div>
                            </div>
                        `;
                        const marker = L.marker([parseFloat(place.lat), parseFloat(place.lon)], { icon: makeIcon(iconMap[type].label[0], iconMap[type].cls) })
                            .addTo(markerLayers).bindPopup(popupHTML);
                        marker.on('popupopen', () => {
                            loadWeatherIntoPopup(parseFloat(place.lat), parseFloat(place.lon), weatherElId);
                        });
                    });
                });
            } catch (error) { console.error('Nearby places fetch error:', error); }
        }

        function toggleButtonState(buttonId, enable) {
            const btn = document.getElementById(buttonId);
            if (btn) { btn.disabled = !enable; }
        }

        let currentModalIdx = null;

        function openHotelModal(idx) {
            const hotel = allHotels[idx];
            if (!hotel) return;
            currentModalIdx = idx;
            const modal = document.getElementById('hotel-modal');
            const modalContent = document.getElementById('hotel-modal-content');
            document.getElementById('modal-hotel-title').innerText = hotel.name;
            const lat = hotel.lat || selectedLat;
            const lon = hotel.lon || selectedLon;
            document.getElementById('modal-address-text').innerText = hotel.address || 'Dagupan City, Pangasinan — view on map';
            document.getElementById('modal-address-link').href = `https://www.google.com/maps/search/?api=1&query=${lat},${lon}`;
            document.getElementById('modal-price').innerText = `₱${formatPrice(hotel.price)} / night`;
            currentGallery = hotel.gallery ? hotel.gallery.split(',').map(s => s.trim()) : [hotel.image_url];
            currentImageIndex = 0;
            updateSliderImage();
            const amenitiesContainer = document.getElementById('modal-amenities-list');
            amenitiesContainer.innerHTML = '';
            const amenitiesList = hotel.amenities ? hotel.amenities.split(',').map(s => s.trim()) : ['Standard Room', 'Air Conditioning', 'Free WiFi'];
            amenitiesList.forEach(item => {
                const chip = document.createElement('span');
                chip.className = "bg-[var(--sand-deep)] text-[var(--deep-teal)] px-3 py-1.5 rounded-full font-medium border border-[var(--line)]";
                chip.innerText = item;
                amenitiesContainer.appendChild(chip);
            });
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
            }, 10);
        }

        function selectFromModal() {
            if (currentModalIdx === null) return;
            const idx = currentModalIdx;
            closeModal();
            selectHotel(idx);
        }

        function closeModal() {
            const modal = document.getElementById('hotel-modal');
            const modalContent = document.getElementById('hotel-modal-content');
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            setTimeout(() => { modal.classList.add('hidden'); }, 300);
        }

        function updateSliderImage() {
            const imgEl = document.getElementById('modal-slider-img');
            imgEl.classList.add('opacity-50');
            setTimeout(() => {
                imgEl.src = currentGallery[currentImageIndex];
                imgEl.classList.remove('opacity-50');
            }, 150);
        }

        function nextImage() {
            if (currentGallery.length <= 1) return;
            currentImageIndex = (currentImageIndex + 1) % currentGallery.length;
            updateSliderImage();
        }

        function prevImage() {
            if (currentGallery.length <= 1) return;
            currentImageIndex = (currentImageIndex - 1 + currentGallery.length) % currentGallery.length;
            updateSliderImage();
        }

        document.getElementById('hotel-modal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        async function checkIn(lat, lon, name) {
            try {
                const res = await fetch('/api/visit-log/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ spot_id: getSpotIdByName(name) })
                });
                const data = await res.json();
                if (res.ok) {
                    showToast(`Checked in at ${name}`, 'success', 3000);
                } else {
                    showToast(data.error || 'Check-in failed', 'error');
                }
            } catch (err) {
                showToast('Check-in failed. Please try again.', 'error');
            }
        }

        async function checkOut(lat, lon, name) {
            try {
                const res = await fetch('/api/visit-log/checkout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ spot_id: getSpotIdByName(name) })
                });
                const data = await res.json();
                if (res.ok) {
                    const mins = data.duration_minutes;
                    showToast(`Checked out of ${name}. Stayed ${mins} min.`, 'success', 4000);
                } else {
                    showToast(data.error || 'Check-out failed', 'error');
                }
            } catch (err) {
                showToast('Check-out failed. Please try again.', 'error');
            }
        }

        async function getSpotIdByName(name) {
            const res = await fetch(`/api/tourist-spots?q=${encodeURIComponent(name)}`);
            const spots = await res.json();
            if (spots.length > 0) return spots[0].id;
            throw new Error('Spot not found');
        }

        // —— Hotel Search ——
        const hotelSearchInput = document.getElementById('hotel-search-input');
        const clearSearchBtn = document.getElementById('clear-search');
        const searchStatus = document.getElementById('search-status');
        const hotelList = document.getElementById('hotel-list');

        hotelSearchInput?.addEventListener('input', () => {
            const hasValue = hotelSearchInput.value.trim().length > 0;
            clearSearchBtn.classList.toggle('hidden', !hasValue);
        });

        clearSearchBtn?.addEventListener('click', () => {
            hotelSearchInput.value = '';
            clearSearchBtn.classList.add('hidden');
            searchStatus.classList.add('hidden');
            searchStatus.innerHTML = '';
            hotelList.innerHTML = '';
            fetchHotels();
            hotelSearchInput.focus();
        });

        const debouncedHotelSearch = debounce(async (query) => {
            if (!query.trim()) {
                searchStatus.classList.add('hidden');
                searchStatus.innerHTML = '';
                hotelList.innerHTML = '';
                fetchHotels();
                return;
            }
            searchStatus.classList.remove('hidden');
            searchStatus.innerHTML = `
                <div class="flex items-center gap-2 p-4 bg-[var(--card)] border border-[var(--line)] rounded-xl">
                    <div class="w-4 h-4 border-2 border-[var(--deep-teal)] border-t-transparent rounded-full animate-spin"></div>
                    <span class="text-sm text-[var(--ink-soft)]">Searching hotels...</span>
                </div>
            `;
            try {
                const res = await fetch(`/api/hotels`);
                if (!res.ok) throw new Error('Search failed');
                const hotels = await res.json();
                const lowerQuery = query.toLowerCase();
                const exactMatches = hotels.filter(h => h.name.toLowerCase() === lowerQuery);
                const partialMatches = hotels.filter(h => h.name.toLowerCase() !== lowerQuery && h.name.toLowerCase().includes(lowerQuery));
                const filtered = [...exactMatches, ...partialMatches];
                if (filtered.length === 0) {
                    searchStatus.innerHTML = `
                        <div class="p-4 bg-[var(--card)] border border-[var(--line)] rounded-xl text-center">
                            <p class="text-sm text-[var(--ink-soft)] mb-1">No hotels found matching "<span class="font-semibold text-[var(--ink)]">${query}</span>"</p>
                            <p class="text-xs text-[var(--sage)]">Try a different search term</p>
                        </div>
                    `;
                    hotelList.innerHTML = '';
                    return;
                }
                const resultLabel = exactMatches.length > 0
                    ? `Found exact match${exactMatches.length > 1 ? 'es' : ''}${partialMatches.length > 0 ? ` + ${partialMatches.length} suggestion${partialMatches.length > 1 ? 's' : ''}` : ''}`
                    : `Found ${filtered.length} result${filtered.length !== 1 ? 's' : ''}`;
                searchStatus.innerHTML = `<p class="text-xs text-[var(--sage)] px-1 font-mono">${resultLabel}</p>`;
                hotelList.innerHTML = '';
                filtered.forEach((hotel, idx) => {
                    const originalIdx = allHotels.findIndex(h => h.id === hotel.id);
                    const starRating = (hotel.rating > 5) ? Math.round(hotel.rating / 2) : Math.round(hotel.rating);
                    const ratingText = Number(hotel.rating).toFixed(1) + ' / 10';
                    const stars = '★'.repeat(starRating) + '☆'.repeat(5 - starRating);
                    const isExactMatch = hotel.name.toLowerCase() === lowerQuery;
                    const card = document.createElement('div');
                    card.className = `hotel-card-enter flex flex-col sm:flex-row bg-[var(--card)] border ${isExactMatch ? 'border-[var(--ember)]' : 'border-[var(--line)]'} rounded-2xl overflow-hidden shadow-sm hover:shadow-lg hover:border-[var(--ember)] transition duration-300 group cursor-pointer`;
                    card.style.animationDelay = `${idx * 60}ms`;
                    card.onclick = () => selectHotel(originalIdx);
                    card.innerHTML = `
                        <div class="hotel-card-image overflow-hidden relative flex-shrink-0">
                            <img src="${hotel.image_url}" class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500 blur-in" alt="${hotel.name}" loading="lazy" onload="this.classList.add('loaded')" onerror="this.classList.add('loaded')" style="background: var(--line);">
                            ${isExactMatch ? '<span class="absolute top-2 left-2 bg-[var(--deep-teal)] text-white text-xs px-3 py-1 rounded-full font-mono font-bold shadow-lg">EXACT MATCH</span>' : ''}
                        </div>
                        <div class="p-5 sm:p-6 flex-1 flex flex-col">
                            <h3 class="font-display font-semibold text-xl sm:text-2xl text-[var(--ink)] mb-1.5 leading-tight">${hotel.name}</h3>
                            <div class="flex items-center text-sm mb-3">
                                <span class="text-[var(--ember)] text-base mr-1.5 tracking-tight">${stars}</span>
                                <span class="font-mono font-bold text-[var(--ink-soft)] mr-1">${ratingText}</span>
                            </div>
                            <p class="text-sm text-[var(--ink-soft)] mb-3 leading-relaxed">${hotel.description || 'No description available'}</p>
                            <div class="font-mono text-sm font-bold text-[var(--deep-teal)] mb-5">₱${formatPrice(hotel.price)} / night</div>
                            <div class="flex flex-col sm:flex-row gap-2.5 mt-auto">
                                <button onclick="event.stopPropagation(); openHotelModal(${originalIdx})" class="focus-ring w-full sm:flex-1 bg-transparent border-[1.5px] border-[var(--line)] text-[var(--ink-soft)] py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center gap-1.5 hover:border-[var(--deep-teal)] hover:text-[var(--deep-teal)] transition-all">View details</button>
                                <button onclick="event.stopPropagation(); selectHotel(${originalIdx})" class="btn-primary focus-ring w-full sm:flex-1 py-3 px-4 rounded-xl text-sm font-bold flex items-center justify-center gap-1.5 whitespace-nowrap">Select this hotel</button>
                            </div>
                        </div>
                    `;
                    hotelList.appendChild(card);
                    card.querySelectorAll('img').forEach(setupBlurIn);
                });
            } catch (err) {
                searchStatus.innerHTML = `
                    <div class="p-4 bg-red-50 border border-red-200 rounded-xl text-center">
                        <p class="text-sm text-[var(--ember-deep)] font-semibold">Search error</p>
                        <p class="text-xs text-[var(--ink-soft)] mt-1">Please try again</p>
                    </div>
                `;
                hotelList.innerHTML = '';
            }
        }, 300);

        document.getElementById('hotel-search-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const query = hotelSearchInput.value.trim();
            if (query) debouncedHotelSearch(query);
            else fetchHotels();
        });

        hotelSearchInput?.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            if (query) {
                debouncedHotelSearch(query);
            } else {
                searchStatus.classList.add('hidden');
                searchStatus.innerHTML = '';
                hotelList.innerHTML = '';
                fetchHotels();
            }
        });

        function showUserLocation() {
            if (!navigator.geolocation) {
                showToast('Geolocation is not supported by your browser.', 'warning');
                return;
            }
            showToast('Getting your location...', 'info', 2000);
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLon = position.coords.longitude;
                    if (window.userMarker) {
                        markerLayers.removeLayer(window.userMarker);
                    }

                    // Enhanced user marker with pulse
                    const userIcon = L.divIcon({
                        className: '',
                        html: `<div class="map-pin pin-user" style="animation: hotelPulse 2s ease-in-out infinite; background: #D9622B;"><span style="font-size:0.75rem;font-weight:700;color:white;font-family:'JetBrains Mono',monospace;">U</span></div>`,
                        iconSize: [38, 38],
                        iconAnchor: [19, 19],
                        popupAnchor: [0, -19]
                    });

                    window.userMarker = L.marker([userLat, userLon], { icon: userIcon })
                        .addTo(markerLayers)
                        .bindPopup('<b>Your Location</b><br><span style="color:var(--sage);font-size:0.85rem;">You are here</span><div id="weather-user-location"></div>')
                        .openPopup();
                    window.userMarker.on('popupopen', () => {
                        loadWeatherIntoPopup(userLat, userLon, 'weather-user-location');
                    });

                    // Smooth fly animation
                    map.flyTo([userLat, userLon], 15, {
                        duration: 1.5,
                        easeLinearity: 0.3
                    });

                    showToast('Location found!', 'success', 2000);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    showToast('Unable to retrieve your location. Please enable location services.', 'error', 4000);
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }

        const mapObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.target.id === 'map' && mutation.target.style.display === 'block') {
                    const legend = document.querySelector('.map-legend');
                    if (legend && !document.getElementById('user-location-btn')) {
                        const btn = document.createElement('button');
                        btn.id = 'user-location-btn';
                        btn.className = 'text-xs text-[var(--deep-teal)] hover:underline font-medium ml-auto flex items-center gap-1.5';
                        btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v4m0 12v4M2 12h4m12 0h4"></path></svg> Show My Location';
                        btn.onclick = showUserLocation;
                        legend.appendChild(btn);
                    }
                }
            });
        });
        mapObserver.observe(document.getElementById('map'), { attributes: true, attributeFilter: ['style'] });
    </script>
</body>
</html>
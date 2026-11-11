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
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
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

        /* Ang Alpine ay buhat sa CDN na `defer`, kaya kumikislap ang anumang
           nasa ilalim ng x-show bago pa ito mag-initialize. Wala itong tinatakdang
           patakaran sa Alpine CDN, kaya kailangan natin itong ideklarar. */
        [x-cloak] { display: none !important; }

        /* Pareho sa public/css/planora-design.css. Dati ay 'Inter', 'Fraunces'
           at 'JetBrains Mono' lang na nakalist-a rito, pero wala silang-load sa
           Google Fonts link sa itaas — kaya palaging system-ui/Courier ang
           lumalabas kahit may `body` na 'Plus Jakarta Sans' sa design CSS. */
        * { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        .font-display { font-family: 'DM Serif Display', 'Fraunces', Georgia, serif; }
        .font-mono { font-family: 'JetBrains Mono', 'Courier New', ui-monospace, monospace; }

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
        /* Admin-only way back sa control center habang nasa live app. */
        .top-nav-link-admin {
            background: rgba(11,61,58,0.08);
            border: 1px solid rgba(11,61,58,0.18);
            color: var(--deep-teal);
            font-weight: 600;
        }
        .top-nav-link-admin:hover {
            background: var(--deep-teal);
            color: var(--sand);
            border-color: var(--deep-teal);
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

        .itinerary-day { background: var(--card); border: 1px solid var(--line); border-radius: var(--radius-lg); margin-bottom: var(--space-3); overflow: hidden; transition: var(--transition-base); position: relative; isolate; }
        .itinerary-day::before {
            content: ''; position: absolute; inset: 0; border-radius: var(--radius-lg);
            background: linear-gradient(135deg, rgba(11,61,58,0.03) 0%, transparent 50%);
            pointer-events: none;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        .itinerary-day:hover { border-color: var(--ember); box-shadow: var(--shadow-lg); }
        .itinerary-day:hover::before { opacity: 1; }
        .itinerary-day:focus-visible { outline: 2px solid var(--deep-teal); outline-offset: -4px; }
        .itinerary-day summary {
            list-style: none; cursor: pointer; user-select: none; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: var(--space-2) var(--space-3);
            background: var(--sand-deep); font-family: 'Fraunces', serif; font-weight: 600; color: var(--deep-teal);
            border-radius: calc(var(--radius-lg) - var(--space-1)); transition: background var(--transition-fast);
            font-size: var(--text-sm);
            backdrop-filter: blur(2px);
        }
        .itinerary-day summary:hover { background: var(--line); }
        .itinerary-day summary:focus-visible { outline: 2px solid var(--deep-teal); outline-offset: 2px; border-radius: calc(var(--radius-lg) - var(--space-1)); }
        .itinerary-day summary::-webkit-details-marker { display: none; }
        .itinerary-day .chevron { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--ember); transition: transform var(--transition-fast); flex-shrink: 0; }
        .itinerary-day[open] .chevron { transform: rotate(180deg); }
        .itinerary-day .day-body { padding: var(--space-3) var(--space-4); animation: fadeInUp 0.3s ease-out; }
        .itinerary-day + .itinerary-day { margin-top: 0; }
        .itinerary-day .markdown-body { color: var(--ink-soft); line-height: 1.6; }
        .itinerary-day .markdown-body h1, .itinerary-day .markdown-body h2, .itinerary-day .markdown-body h3 { color: var(--ink); font-weight: 600; margin-top: var(--space-3); margin-bottom: var(--space-1); }
        .itinerary-day .markdown-body h1 { font-size: var(--text-xl); }
        .itinerary-day .markdown-body h2 { font-size: var(--text-lg); }
        .itinerary-day .markdown-body h3 { font-size: var(--text-base); }
        .itinerary-day .markdown-body p { margin-bottom: var(--space-2); }
        .itinerary-day .markdown-body ul { list-style: none; padding-left: 0; margin-bottom: var(--space-1); }
        .itinerary-day .markdown-body li { margin-bottom: var(--text-sm); padding-left: var(--space-2); position: relative; line-height: 1.55; color: var(--ink-soft); }
        .itinerary-day .markdown-body li::before { content: '→'; position: absolute; left: 0; color: var(--ember); font-weight: 700; }
        .itinerary-day .markdown-body li strong { color: var(--ink); }

        .itinerary-badge {
            display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.65rem;
            border-radius: 9999px; font-family: 'JetBrains Mono', monospace; font-size: 0.68rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
        }
        .itinerary-badge-groq { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
        .itinerary-badge-local { background: var(--sand-deep); color: var(--ink-soft); border: 1px solid var(--line); }

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
        .map-pin.pin-user-pulse {
            animation: userPulse 2s ease-in-out infinite;
        }
        @keyframes hotelPulse {
            0%, 100% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 0 rgba(11,61,58,0.7); }
            50% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 12px rgba(11,61,58,0); }
        }
        @keyframes userPulse {
            0%, 100% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 0 rgba(217,98,43,0.8); }
            50% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 14px rgba(217,98,43,0); }
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
            display: flex; flex-direction: column; align-items: flex-start; gap: 0.15rem;
        }
        .rest-chip:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(11,61,58,0.1); border-color: var(--deep-teal); }
        .rest-chip.is-applied {
            background: var(--deep-teal); border-color: var(--deep-teal); color: var(--sand);
            transform: scale(1.02);
        }
        .rest-chip.is-applied .chip-text-sub { color: var(--sage); }

        /* —— Rest windows: ang traveller mismo ang pumipili ng oras ng rest —— */
        .rest-window { display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
        .rest-window .rest-remove:disabled { opacity: 0.35; cursor: not-allowed; }
        #btn-add-rest:disabled { opacity: 0.45; cursor: not-allowed; }

        .map-legend { display: flex; flex-wrap: wrap; gap: 0.9rem; font-size: 0.72rem; color: var(--ink-soft); margin-top: 0.6rem; }
        .map-legend span { display: inline-flex; align-items: center; gap: 0.35rem; transition: transform 0.2s ease; }
        .map-legend span:hover { transform: translateY(-1px); }
        .legend-dot { width: 10px; height: 10px; border-radius: 999px; display: inline-block; box-shadow: 0 0 0 2px rgba(0,0,0,0.1); }

        /* —— AI itinerary overflow safety + markdown typography (all widths) —— */
        /* AI markdown puwedeng maglaman ng mahabang URL, table o code block na
           sumisira sa mobile kung walang wrap at scroll. All text within #ai-output
           is scoped so global markdown-body styles don't leak. */
        #ai-output {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        #ai-output .markdown-body,
        #ai-output .day-body {
            overflow-wrap: break-word;
            text-align: justify;
            line-height: 1.7;
            color: var(--ink-soft);
            font-size: 0.875rem;
        }
        #ai-output .markdown-body h2,
        #ai-output .markdown-body h3,
        #ai-output .markdown-body h4,
        #ai-output .day-body .markdown-body h2,
        #ai-output .day-body .markdown-body h3,
        #ai-output .day-body .markdown-body h4 {
            font-family: 'Fraunces', serif;
            color: var(--deep-teal);
            line-height: 1.3;
            margin: 1.5rem 0 1rem;
        }
        #ai-output .markdown-body h2,
        #ai-output .day-body .markdown-body h2 { font-size: 1.5rem; }
        #ai-output .markdown-body h3,
        #ai-output .day-body .markdown-body h3 { font-size: 1.25rem; }
        #ai-output .markdown-body h4,
/* Trip Summary section - color red */
#ai-output .markdown-body h2.trip-summary,
#ai-output .day-body .markdown-body h2.trip-summary {
    color: #ff0000 !important;
    font-size: 1.25rem;
}
        #ai-output .day-body .markdown-body h4 { font-size: 1.1rem; }
        #ai-output .markdown-body blockquote,
        #ai-output .day-body .markdown-body blockquote {
            border-left: 3px solid var(--ember);
            background: rgba(217, 98, 43, 0.08);
            padding: 0.75rem 1rem;
            margin: 1rem 0;
            font-style: italic;
            color: var(--ember);
        }
        #ai-output .markdown-body code,
        #ai-output .day-body .markdown-body code {
            background: var(--sand-deep);
            color: var(--deep-teal);
            padding: 0.15rem 0.35rem;
            border-radius: var(--radius-sm);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
        }
        #ai-output .markdown-body pre,
        #ai-output .day-body .markdown-body pre {
            background: var(--sand-deep);
            color: var(--ink);
            padding: 1rem;
            overflow-x: auto;
            border-radius: var(--radius-md);
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            line-height: 1.45;
        }
        #ai-output .markdown-body hr,
        #ai-output .day-body .markdown-body hr {
            border: 0;
            border-top: 1px solid var(--line);
            margin: 2rem 0;
        }
        #ai-output .markdown-body a,
        #ai-output .day-body .markdown-body a {
            color: var(--deep-teal);
            text-decoration: underline;
            text-underline-offset: 3px;
            text-decoration-color: var(--ember);
        }
        #ai-output .markdown-body strong,
        #ai-output .day-body .markdown-body strong {
    color: var(--ink);
}

/* Day accordion card */
#ai-output .itinerary-day {
    background: var(--card);
    border: 1px solid var(--line);
    border-radius: 0.9rem;
    margin-bottom: 0.75rem;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}
#ai-output .itinerary-day:hover {
    border-color: var(--ember);
    box-shadow: 0 4px 12px rgba(11, 61, 58, 0.08);
    transform: translateY(-1px);
}
#ai-output .itinerary-day summary {
    list-style: none;
    cursor: pointer;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.9rem 1.1rem;
    background: var(--sand-deep);
    font-family: 'Fraunces', serif;
    font-weight: 600;
    color: var(--deep-teal);
    transition: background 0.2s ease;
}
#ai-output .itinerary-day summary:hover { background: var(--line); }
#ai-output .itinerary-day summary::-webkit-details-marker { display: none; }
#ai-output .itinerary-day .chevron {
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.75rem;
    color: var(--ember);
    transition: transform 0.25s ease;
    flex-shrink: 0;
}
#ai-output .itinerary-day[open] .chevron { transform: rotate(180deg); }
#ai-output .itinerary-day .day-body {
    padding: 1rem 1.1rem 1.1rem;
    animation: fadeInUp 0.3s ease-out forwards;
}
#ai-output .itinerary-day + .itinerary-day { margin-top: 0; }

/* Badges inside #ai-output */
#ai-output .itinerary-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    font-family: 'JetBrains Mono', monospace;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
#ai-output .itinerary-badge-groq { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }
#ai-output .itinerary-badge-local { background: var(--sand-deep); color: var(--ink-soft); border: 1px solid var(--line); }
        @media (max-width: 640px) {
            .hotel-card-enter { animation: none; }
            .step.active { animation: none; }
            .ticket-frame { padding: 1rem; }
            #map { height: 320px; }
            .stub-node { width: 30px; height: 30px; font-size: 0.62rem; }
            .stub-line { margin: 0 4px; }
            .planner-hero h1 { font-size: 2.35rem; }
            .planner-hero { padding: 16px 0 12px; }
            .toast { max-width: calc(100vw - 2rem); }
        }

        /* Short viewports (landscape phones): shrink the map so the form stays reachable */
        @media (max-height: 520px) {
            #map { height: 260px; }
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
            <a href="/planora" class="brand"><span class="brand-mark">⌁<img src="/images/planora-logo-sm.png" alt="" class="brand-logo" onerror="this.remove()"></span><span>planora</span></a>
            <div class="flex items-center gap-3">
                @auth
                @if(auth()->user()->role === 'admin')
                <a href="/admin/hotels" class="top-nav-link top-nav-link-admin" title="Back to the Planora admin panel">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Admin Panel
                </a>
                @endif
                <a href="/dashboard" class="top-nav-link {{ request()->is('dashboard') ? 'active' : '' }}" title="Traveler Dashboard">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Dashboard
                </a>
                <a href="/my-plans" class="top-nav-link {{ request()->is('my-plans') ? 'active' : '' }}" title="My Saved Plans">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                    My Plans
                </a>
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
                    @if(auth()->check() && auth()->user()->role === 'admin')
                    <a href="/admin/hotels" class="flex items-center gap-3 p-3 rounded-xl text-[var(--deep-teal)] font-semibold hover:bg-[var(--sand-deep)] transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Admin Panel</span>
                    </a>
                    @endif
                    <a href="/dashboard" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--sand-deep)] transition font-medium">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="/my-plans" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--sand-deep)] transition font-medium">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                        <span>My Plans</span>
                    </a>
                    <a href="/public-plans" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--sand-deep)] transition font-medium">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/></svg>
                        <span>Public Plans</span>
                    </a>
                    <a href="/profile/{{ auth()->id() }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-[var(--sand-deep)] transition">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 10-8 0 4 4 0 008 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Profile Settings</span>
                    </a>
                    <form method="POST" action="/logout">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 p-3 rounded-xl text-[var(--ember-deep)] hover:bg-[var(--sand-deep)] transition" onclick="sessionStorage.removeItem('planora_draft_v1');">
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
                <div class="stub-line"></div>
                <div class="stub-node" id="stub-4" data-step="4">04</div>
            </div>
            <div class="flex justify-between mt-2 px-1 font-mono text-[0.62rem] uppercase tracking-wider text-[var(--sage)]">
                <span>Stay</span>
                <span>Details</span>
                <span>Places</span>
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
                        <input type="number" id="trip-budget" class="input-field w-full p-3 rounded-xl font-mono focus-ring" min="1" max="1000000" placeholder="How much your Budget?">
                    </div>
                </div>
                <p id="budget-hint" class="text-xs text-[var(--sage)] mb-6 font-mono"></p>

                <div class="flex items-baseline justify-between gap-2 flex-wrap mb-1">
                    <label class="font-medium text-sm text-[var(--ink)]" id="rest-schedule-label">
                        Rest schedule <span class="text-[var(--sage)] font-normal">?</span>
                    </label>
                    <span class="font-mono text-[0.62rem] uppercase tracking-wider text-[var(--sage)]">Optional</span>
                </div>

                <!-- Quick fill: pinupuno lang ang oras sa ibaba at pwedeng i-edit agad -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3" role="group" aria-labelledby="rest-schedule-label">
                    @foreach(($restWindows ?? []) as $restLabel => $restWindow)
                    @php
                        // 12-hour ang nakikita ng traveller; ang data-start/data-end
                        // ay nananatiling 'HH:MM' para sa native time inputs.
                        $restDisplay = $restWindowDisplay[$restLabel] ?? $restWindow;
                    @endphp
                    <button type="button"
                            class="rest-chip p-3 rounded-xl border-[1.5px] border-[var(--line)] bg-[var(--card)] focus-ring"
                            data-rest-preset
                            data-start="{{ $restWindow[0] }}"
                            data-end="{{ $restWindow[1] }}"
                            aria-label="{{ $restLabel }} preset, {{ $restDisplay[0] }} to {{ $restDisplay[1] }}">
                        <span class="font-semibold text-sm">{{ $restLabel }}</span>
                        <span class="chip-text-sub text-[0.65rem] text-[var(--ink-soft)] uppercase tracking-wide">{{ $restDisplay[0] }} — {{ $restDisplay[1] }}</span>
                    </button>
                    @endforeach
                </div>

                <div id="rest-windows" class="space-y-3"></div>

                <div class="flex items-center justify-between gap-3 mb-9 flex-wrap">
                    <button type="button" id="btn-add-rest" class="text-sm font-semibold text-[var(--deep-teal)] border-[1.5px] border-[var(--line)] rounded-xl px-4 py-2 hover:border-[var(--deep-teal)] transition focus-ring">
                        + Add rest period
                    </button>
                    <p id="rest-summary" class="font-mono text-xs text-[var(--sage)]" aria-live="polite"></p>
                </div>

                <button id="btn-confirm" onclick="goToPlaces()" class="btn-primary focus-ring w-full p-4 rounded-xl font-bold flex justify-center items-center gap-2">
                    Pick my places
                </button>
            </div>

            <!-- ── Step 03: ang mga lugar na gusto ng traveller ── -->
            <div id="step-3" class="step">
                <button onclick="goBackToStep2()" class="text-sm text-[var(--deep-teal)] font-semibold mb-5 hover:underline flex items-center gap-1 focus-ring rounded">
                    <span aria-hidden="true">←</span> Back to details
                </button>

                <div class="flex items-baseline justify-between mb-1 flex-wrap gap-y-1">
                    <h2 class="font-display text-2xl font-semibold text-[var(--ink)]">Pick your places</h2>
                    <span class="font-mono text-[0.65rem] uppercase tracking-wider text-[var(--sage)]">Optional</span>
                </div>
                <p class="text-sm text-[var(--ink-soft)] mb-2">
                    Closest to <span id="places-origin" class="font-semibold text-[var(--ember-deep)]">your hotel</span> first, and every one of them costs less than your
                    <span id="places-allowance" class="font-mono font-bold text-[var(--deep-teal)]">—</span>
                    daily allowance.
                </p>
                <p id="places-sort-note" class="text-xs text-[var(--sage)] font-mono mb-5"></p>

                <div id="places-filters" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4" role="group" aria-label="Filter places by category"></div>

                <div id="places-status-row" class="mb-4 flex items-center gap-3 flex-wrap">
                    <p id="places-status" class="text-sm text-[var(--ink-soft)] flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-[var(--ember)] pulse-soft"></span>
                        Finding spots near your hotel that fit your budget…
                    </p>
                    <button type="button" id="places-retry" onclick="loadPlaceOptions(true)"
                            class="hidden text-xs font-semibold text-[var(--deep-teal)] border-[1.5px] border-[var(--line)] rounded-lg px-3 py-1.5 hover:border-[var(--deep-teal)] transition focus-ring">
                        Try again
                    </button>
                </div>

                <div id="places-list" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[520px] overflow-y-auto pr-2 pb-2"></div>

                <div id="places-empty" class="hidden p-6 rounded-2xl border border-dashed border-[var(--line)] bg-[var(--card)] text-center mb-5">
                    <p id="places-empty-message" class="text-sm text-[var(--ink-soft)] mb-1"></p>
                    <p class="text-xs text-[var(--sage)] font-mono">No problem — you can still generate an itinerary and the AI will plan around your hotel.</p>
                </div>

                <div class="flex items-center justify-between gap-3 mt-5 p-4 rounded-2xl border border-[var(--line)] bg-[var(--card)] flex-wrap">
                    <p id="places-summary" class="font-mono text-xs text-[var(--sage)]" aria-live="polite">Nothing picked yet.</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <button type="button" id="btn-let-ai-decide" onclick="clearPlaceSelection()"
                                class="text-sm font-semibold text-[var(--deep-teal)] border-[1.5px] border-[var(--line)] rounded-xl px-4 py-2.5 hover:border-[var(--deep-teal)] transition focus-ring">
                            Let AI decide
                        </button>
                        <button type="button" id="btn-generate" onclick="generateItinerary()"
                                class="btn-primary focus-ring px-5 py-3 rounded-xl font-bold">
                            Generate itinerary
                        </button>
                    </div>
                </div>
            </div>

            <div id="step-4" class="step">
                <div class="flex items-baseline justify-between mb-4 flex-wrap gap-y-1">
                    <h2 class="font-display text-2xl font-semibold text-[var(--ink)]">Your itinerary</h2>
                    <span class="font-mono text-[0.65rem] uppercase tracking-wider text-[var(--sage)]">Boarding: now</span>
                </div>

                <!-- GPS & Travel Route Banner -->
                <div id="route-guidance-card" class="hidden mb-3 p-3.5 rounded-xl border border-[var(--line)] bg-[var(--card)] shadow-sm">
                    <div class="flex items-start sm:items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[var(--ember)] flex items-center justify-center text-white font-mono font-bold text-xs shrink-0 shadow-sm">
                            GPS
                        </div>
                        <div>
                            <div class="text-xs font-bold text-[var(--ink)] flex items-center gap-1.5 flex-wrap">
                                <span id="route-guidance-title">Route from your current location</span>
                                <span id="route-badge-live" class="inline-block px-1.5 py-0.5 rounded text-[0.62rem] font-mono font-semibold bg-emerald-100 text-emerald-800">LIVE ROUTE</span>
                            </div>
                            <div id="route-guidance-details" class="text-xs text-[var(--ink-soft)] font-mono mt-0.5">
                                Calculating distance and driving time…
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Guided arrival / check-in / check-out card.
                     Lumalabas kapag nakarating (≤200 m) ang traveller sa aktibong
                     stop ng kanyang itinerary: Check In → live timer → Check Out.
                     Nasa labas ito ng map container, kaya may sariling delegated
                     click listener (tingnan ang arrivalCardEl.addEventListener). -->
                <div id="arrival-card" class="hidden mb-3 p-3.5 rounded-xl border border-[var(--line)] bg-[var(--card)] shadow-sm">
                    <div class="flex items-start sm:items-center gap-3">
                        <div id="arrival-icon" class="w-9 h-9 rounded-full bg-[var(--ember)] flex items-center justify-center text-white font-mono font-bold text-xs shrink-0 shadow-sm">
                            &#9679;
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-[var(--ink)] flex items-center gap-1.5 flex-wrap">
                                <span id="arrival-title">You've arrived</span>
                                <span id="arrival-progress" class="inline-block px-1.5 py-0.5 rounded text-[0.62rem] font-mono font-semibold bg-emerald-100 text-emerald-800"></span>
                            </div>
                            <div id="arrival-details" class="text-xs text-[var(--ink-soft)] mt-0.5"></div>
                            <div id="arrival-actions" class="mt-2 flex items-center gap-2 flex-wrap"></div>
                        </div>
                    </div>
                </div>

                <div id="map" class="mb-2 border border-[var(--line)]"></div>
                <div id="map-legend" class="map-legend mb-6">
                    <span data-legend="hotel"><span class="legend-dot" style="background:#0B3D3A"></span>Your hotel</span>
                    <span data-legend="user"><span class="legend-dot" style="background:#D9622B"></span>You (current location)</span>
                    <span id="tracking-status" style="display:none;">
                        <span class="legend-dot" style="background:#D9622B;box-shadow:0 0 0 4px rgba(217,98,43,0.25);"></span>Live tracking
                    </span>
                    <button type="button" id="btn-toggle-tracking" onclick="toggleUserTracking()" class="text-xs font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition">Start live tracking</button>
                    <span data-legend="restaurant"><span class="legend-dot" style="background:#C2410C"></span>Restaurants</span>
                    <span data-legend="mall"><span class="legend-dot" style="background:#0E5F5A"></span>Malls</span>
                    <span data-legend="tourist"><span class="legend-dot" style="background:#2E7D32"></span>Tourist spots</span>
                    <span data-legend="beach"><span class="legend-dot" style="background:#0891B2"></span>Beaches</span>
                </div>

                <div id="budget-warning-box" class="budget-warning hidden"></div>

                <div id="itinerary-header-card" mb-4 p-4 rounded-xl border border-[var(--line)] bg-[var(--sand-deep)] flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span id="itinerary-provider-badge" class="itinerary-badge itinerary-badge-local">Plan</span>
                        <span id="itinerary-meta-text" class="text-xs text-[var(--ink-soft)] font-mono"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="toggleAllItineraryDays()" id="btn-toggle-days" class="text-xs font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition">
                            Collapse all
                        </button>
                        <span class="text-[var(--line)]">·</span>
                        <button type="button" onclick="copyItinerary()" id="btn-copy-itinerary" class="text-xs font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition inline-flex items-center gap-1">
                            <span>Copy itinerary</span>
                        </button>
                    </div>
                </div>

                <div id="ai-output"></div>

                <button onclick="planAnotherTrip()" class="mt-6 w-full bg-transparent text-[var(--deep-teal)] border-[1.5px] border-[var(--line)] p-4 rounded-xl font-bold hover:bg-[var(--sand-deep)] transition focus-ring">
                    Plan another trip
                </button>
            </div>
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
                <div id="modal-address-link" class="inline-flex items-start gap-1.5 text-sm text-[var(--ink-soft)] mb-5 pb-5 border-b border-[var(--line)] w-full">
                    <span class="font-mono text-xs font-bold text-[var(--deep-teal)]" aria-hidden="true">ADDRESS</span>
                    <span id="modal-address-text"></span>
                </div>
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

    <div id="place-photo-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-black/70 backdrop-blur-sm p-4 opacity-0 transition-opacity duration-300" role="dialog" aria-modal="true" aria-labelledby="place-photo-title">
        <div class="bg-[var(--card)] w-full max-w-2xl max-h-[90vh] rounded-2xl overflow-hidden shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col" id="place-photo-modal-content">
            <div class="relative bg-black flex-shrink-0 flex items-center justify-center min-h-[240px] max-h-[70vh]">
                <img id="place-photo-img" src="" alt="" class="w-full max-h-[70vh] object-contain">
                <button type="button" onclick="closePlacePhoto()" class="absolute top-3 right-3 bg-black/50 text-white w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/80 transition" aria-label="Close photo viewer">×</button>
            </div>
            <div class="p-5 sm:p-6 overflow-y-auto flex-1">
                <h3 id="place-photo-title" class="font-display font-semibold text-xl sm:text-2xl text-[var(--ink)] mb-1"></h3>
                <p id="place-photo-meta" class="font-mono text-xs text-[var(--sage)] mb-2"></p>
                <p id="place-photo-desc" class="text-sm text-[var(--ink-soft)] leading-relaxed"></p>
                <!-- Lokasyon ng lugar sa mapa (tulad ng landing gallery). Naka-hide
                     kapag walang koordinado ang lugar. -->
                <div id="place-photo-map-wrap" class="mt-4 hidden">
                    <div id="place-photo-map" style="height:260px;border-radius:0.75rem;overflow:hidden;border:1px solid var(--line);"></div>
                    <p class="font-mono text-xs text-[var(--sage)] mt-2">Location · Dagupan City</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Hindi nakalagay ang server-side flags sa loob ng JS block, dahil kapag
         hinango ang Blade file nang walang compilation, ang mga Blade echo ay
         literal na teksto pa rin para sa JS parser at nasisira ang buong block
         (hal. "Property assignment expected"). Ang data attributes sa itaas
         ang may halaga — buong Blade escaping ay nananatili — at `dataset`
         sa ibaba ang nagbabasa. -->
    <div id="planner-config"
         data-authenticated="{{ auth()->check() ? 'true' : 'false' }}"
         data-nights-offset="{{ $nightsOffset }}"
         data-ai-enabled="{{ $aiEnabled ? 'true' : 'false' }}"
         data-min-daily-allowance="{{ $minDailyAllowance ?? 200 }}"
         data-max-trip-budget="{{ $maxTripBudget ?? 1000000 }}"
         hidden></div>

    <script>
        // The OpenWeather key used to be printed here, leaking it to every
        // visitor even though nothing read it — weather goes through /api/weather.
        const plannerConfig = document.getElementById('planner-config').dataset;
        const isAuthenticated = plannerConfig.authenticated === 'true';
        //
        // Mirrors PlanoraService::NIGHTS_PER_DAY_OFFSET so this page's estimate
        // can never disagree with the server-side budget guard.
        const NIGHTS_OFFSET = Number(plannerConfig.nightsOffset);
        //
        // Mirrors PlanoraService::MIN_DAILY_ALLOWANCE so Step 02 blocks a budget
        // the server would reject, instead of showing a green hint and then
        // failing on submit.
        const MIN_DAILY_ALLOWANCE = Number(plannerConfig.minDailyAllowance) || 200;
        // Mirrors PlanoraService::MAX_TRIP_BUDGET.
        const MAX_TRIP_BUDGET = Number(plannerConfig.maxTripBudget) || 1000000;
        const AI_ENABLED = plannerConfig.aiEnabled === 'true';
        let map;
        let markerLayers;
        let currentRouteControl = null;
        let selectedLat = 16.0438;
        let selectedLon = 120.3331;
        // Tunay ba ang selectedLat/Lon o mga fallback para sa mapa? Ang picker
        // ay kailangan ito para hindi mag-label ng "pinakamalapit" ang isang listahan
        // na ayon naman sa rating lang.
        let selectedHotelGeocoded = false;
        let hotelName = '';
        let selectedHotelIdx = null;
        let nearbyPlacesData = [];
        // Lahat ng POI na may coordinates, mula sa Overpass. Hindi ito agad
        // naka-pin sa mapa: ang itinerary (AI o offline generator) ang nagsasabi
        // kung alin lang sa kanila ang totoong sinuggest, at ang
        // `renderSuggestedPlaces()` lang ang naglalagay ng markers.
        let nearbyPlacesIndex = [];
        // Bukas na pag-check-in ng user (location_name → checked_in_at ISO). Ginagamit
        // ng POI popup para magpakita ng "Check Out" kapag naka-check-in na, at
        // ina-update pagkatapos ng bawat aksyon.
        let activeVisits = {};
        // Marker registry (location_name → Leaflet marker) para ma-refresh ang
        // popup button matapos ang check-in/check-out nang hindi nagre-render ulit
        // ng buong pin.
        let poiMarkers = {};
        let currentHotelPrice = 0;
        let allHotels = [];

        // —— Step 03: ang place picker ——
        // Stay → Details → Places → Itinerary. Ang bilang na ito ang
        // humahawak sa haba ng .stub-track, kaya hindi na magka-slide ang
        // 'for' loop sa setActiveStub() kapag may bagong step na idinagdag.
        const TOTAL_STEPS = 4;
        // Mga sumasalaminha ng PlanoraService::MAX_SELECTED_PLACES at
        // ::MAX_SELECTED_PLACES_PER_CATEGORY. Ang picker ay dapat tumayo na
        // mag-una sa server, hindi ito ang mahilig na pumili ng limitasyon.
        const MAX_SELECTED_PLACES = 12;
        const MAX_SELECTED_PER_CATEGORY = 4;
        // Parehong kategorya, icon at kulay na ginagamit ng map legend sa itaas
        // at ng fetchNearbyAmenities() — isang vocabulary para sa lahat ng mapa.
        const PLACE_CATEGORIES = [
            { key: 'restaurant', label: 'Restaurants', icon: '🍽️', dot: '#C2410C', pin: 'pin-restaurant', letter: 'R' },
            { key: 'mall', label: 'Malls', icon: '🏬', dot: '#0E5F5A', pin: 'pin-mall', letter: 'M' },
            { key: 'beach', label: 'Beaches', icon: '🏖️', dot: '#0891B2', pin: 'pin-beach', letter: 'B' },
            { key: 'tourist', label: 'Tourist spots', icon: '⛰️', dot: '#2E7D32', pin: 'pin-tourist', letter: 'T' },
        ];

        let activePlaceFilter = 'all';
        let placeOptions = { within_budget: {}, over_budget: {}, daily_allowance: 0 };
        let selectedPlaces = [];
        // Ang "na-load na" na bilang ng picker, bilang kahit anong kategorya ang
        // nakikita. Kasama ang hotel dahil nag-iiba ito ng ayos ng distansya.
        let loadedPlacesKey = null;

        // Ang pangalan ng POI ay panlabas na datos (Overpass) at direktang
        // naka-embed sa popup HTML ng Leaflet. Kailangan itong i-escape para
        // hindi makapasok ang `<` o `"` at mabawasan ang markup.
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[char]);
        }

        // Prices arrive as decimal values from the API, so this only formats.
        function formatPrice(price) {
            const num = Number(price);
            if (!isFinite(num)) return '0';
            return num.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        }

        // Every rating in the catalogue is on one 0-10 scale, so the 5-star
        // display is a plain division. This replaces the `rating > 5 ?
        // rating / 2 : rating` heuristic that guessed a scale per value.
        function starsFromRating(rating) {
            const filled = Math.max(0, Math.min(5, Math.round(Number(rating) / 2)));
            return '★'.repeat(filled) + '☆'.repeat(5 - filled);
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

        // REGRESSION NOTE: dati ay `sessionStorage.removeItem()` sa load — kaya
        // laging Step 01 pag refresh. Ngayon ay full draft ang nire-restore nang
        // may validation (tingnan ang restorePlannerDraft): ang hotel lang na
        // walang budget/days/rest ang nagdudulot ng "stuck sa Step 02", at ang
        // guarded restore ang pumipigil doon. Kasama na rin ngayon ang Step 04:
        // kapag may naka-save na itinerary sa draft, doon mismo ibinabalik ang
        // traveller (tingnan ang restoreItineraryStep) imbes na sa Step 03.

        // —— Planner draft (refresh-safe) ——
        // Dati: `sessionStorage.removeItem()` sa load — kaya laging Step 01 pag
        // refresh. Ngayon: sine-save ang buong draft at nire-restore nang may
        // validation, para hindi maulit ang "stuck sa Step 02 na walang laman".
        const PLANNER_DRAFT_KEY = 'planora_draft_v1';

        function currentStepNumber() {
            for (let i = 1; i <= TOTAL_STEPS; i++) {
                if (document.getElementById(`step-${i}`)?.classList.contains('active')) return i;
            }
            return 1;
        }

        function goToStep(stepNum) {
            for (let i = 1; i <= TOTAL_STEPS; i++) {
                document.getElementById(`step-${i}`)?.classList.toggle('active', i === stepNum);
            }
            setActiveStub(stepNum);
            savePlannerDraft();
        }

        function savePlannerDraft() {
            try {
                const daysEl = document.getElementById('trip-days');
                const budgetEl = document.getElementById('trip-budget');
                sessionStorage.setItem(PLANNER_DRAFT_KEY, JSON.stringify({
                    step: currentStepNumber(),
                    hotelIdx: selectedHotelIdx,
                    hotelName: hotelName || '',
                    days: daysEl ? daysEl.value : '',
                    budget: budgetEl ? budgetEl.value : '',
                    rest: collectRestWindows(),
                    picks: [...selectedPlaces],
                    filter: activePlaceFilter,
                    // —— Step 04 restore ——
                    // Dati ay DOM + `currentItineraryMarkdown` lang ang hawak ng
                    // itinerary, kaya pagkatapos ng refresh ay wala nang maibalik
                    // at laging Step 03 ang bagsak. Ngayon kasama na sa draft ang
                    // markdown at ang meta (provider, allowance, budget warning,
                    // tunay na napili) para buo ang Step 04 na walang bagong
                    // bayad na AI call. Maliliit lang ang mga ito (KBs), kaya
                    // kasya pa rin sa sessionStorage — at nasa try/catch pa rin
                    // ang quota.
                    itinerary: currentItineraryMarkdown,
                    itineraryMeta: lastItineraryMeta,
                    // —— Guided-trip restore ——
                    // Kasama ang naitayong stops at kung saan index natigil ang
                    // traveller, para pagkatapos ng refresh ay doon mismo
                    // magpatuloy ang arrival/check-in flow.
                    tripStops: tripStops,
                    activeStopIndex: activeStopIndex,
                    savedAt: Date.now(),
                }));
            } catch (error) {
                // Quota o private mode — hindi ito dapat makasira sa planner.
            }
        }

        function clearPlannerDraft() {
            try {
                sessionStorage.removeItem(PLANNER_DRAFT_KEY);
                sessionStorage.removeItem('planora_selected_idx');
            } catch (error) {
                // Balewalain — parehong dahilan sa itaas.
            }
        }

        // Explicit reset lang ang nagbubura ng draft — hindi ang refresh.
        function planAnotherTrip() {
            clearPlannerDraft();
            stopUserTracking();
            location.reload();
        }

        function readPlannerDraft() {
            try {
                const raw = sessionStorage.getItem(PLANNER_DRAFT_KEY);
                if (!raw) return null;
                const draft = JSON.parse(raw);
                return (draft && typeof draft === 'object') ? draft : null;
            } catch (error) {
                return null;
            }
        }

        function isValidClock(value) {
            return typeof value === 'string' && /^([01]\d|2[0-3]):[0-5]\d$/.test(value);
        }

        // Guarded restore: bawat bahagi ay vine-validate bago i-apply. Kapag may
        // hindi tugma (hal. wala na ang hotel sa listahan), bumabalik sa
        // pinakamataas na step na may valid na prereqs — hindi sa sirang step.
        async function restorePlannerDraft() {
            const draft = readPlannerDraft();
            if (!draft) return;
            const step = Number.isInteger(draft.step) && draft.step >= 1 && draft.step <= TOTAL_STEPS ? draft.step : 1;
            if (step === 1) return;
            let idx = Number.isInteger(draft.hotelIdx) && allHotels[draft.hotelIdx]?.name === draft.hotelName
                ? draft.hotelIdx
                : allHotels.findIndex(h => h && h.name === draft.hotelName);
            if (idx < 0) {
                if (allHotels.length > 0) {
                    // Loaded ang listahan pero wala na talaga ang hotel (tinanggal
                    // o binago ang pangalan) — sirang draft na ito.
                    clearPlannerDraft();
                } else {
                    // Hindi dapat mabura ang draft dahil lang bumagsak ang
                    // /api/hotels. Ipinapakita ang problema at binabura lang
                    // talaga kapag loaded na ang listahan.
                    showToast('We could not load the hotels just now — your draft is kept. Please refresh to try again.', 'warning', 6000);
                }
                return;
            }
            applyHotelSelection(idx, { silent: true });
            const daysEl = document.getElementById('trip-days');
            const budgetEl = document.getElementById('trip-budget');
            if (daysEl && draft.days !== undefined) daysEl.value = draft.days;
            if (budgetEl && draft.budget !== undefined) budgetEl.value = draft.budget;
            const container = document.getElementById('rest-windows');
            if (container) {
                container.innerHTML = '';
                const windows = Array.isArray(draft.rest) ? draft.rest.slice(0, MAX_REST_WINDOWS) : [];
                windows.forEach(value => {
                    const parts = String(value || '').split('-');
                    if (isValidClock(parts[0]) && isValidClock(parts[1])) addRestWindow(parts[0], parts[1]);
                });
                if (container.children.length === 0) addRestWindow();
                else syncRestControls();
            }
            checkBudget();
            if (readTripDays() === null || readTripBudget() === null) {
                goToStep(2);
                showToast('Welcome back — please check your trip details to continue.', 'info');
                return;
            }
            selectedPlaces = Array.isArray(draft.picks) ? draft.picks.filter(n => typeof n === 'string') : [];
            if (['all', 'restaurant', 'mall', 'tourist', 'beach'].includes(draft.filter)) {
                activePlaceFilter = draft.filter;
            }
            if (step === 2) {
                goToStep(2);
                return;
            }
            if (step >= 4 && typeof draft.itinerary === 'string' && draft.itinerary.trim() !== '') {
                // Buong Step 04: may naka-save na itinerary sa draft, kaya
                // hindi ito binabagsak sa Step 03. Walang auto-generate — ang
                // session copy ang binabasa, hindi ang AI.
                await restoreItineraryStep(draft);
                return;
            }
            goToStep(3);
            await loadPlaceOptions();
            renderPlaceCards();
            updatePlaceSummary();
            if (step >= 4) {
                // Walang auto-generate sa refresh: magastos ang AI call at hindi
                // inaasahan ng traveller. Buo ang picks — isang pindot na lang
                // sa "Generate itinerary" para tumuloy. Dito ka mapupunta kapag
                // na-refresh HABANG ongoing pa ang generation — wala pang
                // itinerary na nai-save sa draft.
                showToast('Welcome back — your picks are restored. Hit Generate itinerary when ready.', 'info');
            }
        }

        // Step 04 restore: ang draft ang may hawak ng itinerary (markdown +
        // meta), kaya hindi na kailangang ulitin ang bayad na AI call. Buo ang
        // mapa, ang header card, ang budget warning, at ang mga pin pagkatapos
        // ng refresh — kasing-laman ng noong ginenerate ito.
        async function restoreItineraryStep(draft) {
            const meta = (draft.itineraryMeta && typeof draft.itineraryMeta === 'object')
                ? draft.itineraryMeta
                : {};

            // Bago ang goToStep(4): ang goToStep ay nagse-save ng draft, at ang
            // save ay kumukuha ng `currentItineraryMarkdown`. Kung hindi ito
            // itinakda muna, walang laman ang maisasave at sa susunod na
            // refresh ay Step 03 na naman ang bagsak.
            currentItineraryMarkdown = draft.itinerary;
            lastItineraryMeta = meta;

            goToStep(4);
            document.getElementById('map').style.display = 'block';
            initMap();

            renderItinerary(draft.itinerary, meta);

            const warningBox = document.getElementById('budget-warning-box');
            if (warningBox) {
                if (meta.budget_warning) {
                    warningBox.innerText = meta.budget_warning;
                    warningBox.classList.remove('hidden');
                } else {
                    warningBox.classList.add('hidden');
                }
            }

            // Dalawang catalogue ang kailangan ng pins: ang POI index ay galing
            // sa Overpass (fetchNearbyAmenities), ang mga napili ay hinahanap sa
            // placeOptions (loadPlaceOptions → findPlace). Parehong nasa loob ng
            // try/catch ang bawat isa, at may `.catch()` din dito — kaya kahit
            // may bumagsak ay hindi mabigo ang restore; hotel + itinerary pa rin
            // ang makikita.
            await Promise.all([
                loadPlaceOptions().catch(() => {}),
                fetchNearbyAmenities(selectedLat, selectedLon).catch(() => {}),
            ]);
            renderSuggestedPlaces();
            const picks = (Array.isArray(meta.selected_places) && meta.selected_places.length)
                ? meta.selected_places
                : (Array.isArray(draft.picks) ? draft.picks : []);
            renderPickedPlaces(picks);

            // Ibalik ang guided-trip stops at ire-store ang naka-save na
            // activeStopIndex (kung saan natigil ang traveller bago mag-refresh),
            // at ikabit ang arrival-card listener.
            buildTripStops();
            if (Number.isInteger(draft.activeStopIndex) && draft.activeStopIndex >= 0
                && draft.activeStopIndex < tripStops.length) {
                activeStopIndex = draft.activeStopIndex;
            }
            bindArrivalCardListener();

            showToast('Welcome back — your itinerary was restored.', 'success');
        }

        // Hotel apply nang walang transition side-effect — ang restore flow ang
        // may kontrol ng step jump, hindi ang selectHotel.
        function applyHotelSelection(idx, options = {}) {
            const hotel = allHotels[idx];
            if (!hotel) return false;
            selectedHotelIdx = idx;
            hotelName = hotel.name;
            selectedLat = hotel.lat || 16.0438;
            selectedLon = hotel.lon || 120.3331;
            selectedHotelGeocoded = Number.isFinite(Number(hotel.lat)) && Number.isFinite(Number(hotel.lon));
            currentHotelPrice = Number(hotel.price);
            document.getElementById('display-selected-hotel').innerText = hotel.name;
            const imgEl = document.getElementById('selected-hotel-image');
            imgEl.src = hotel.image_url;
            imgEl.alt = hotel.name;
            setupBlurIn(imgEl);
            document.getElementById('selected-hotel-stars').innerText = starsFromRating(hotel.rating);
            document.getElementById('selected-hotel-rating').innerText = Number(hotel.rating).toFixed(1) + ' / 10';
            document.getElementById('selected-hotel-description').innerText = `Rate: ₱${formatPrice(hotel.price)} / night`;
            if (!options.silent) {
                goToStep(2);
                checkBudget();
            }
            return true;
        }

        function wireDraftTracking(debouncedDraft) {
            const container = document.getElementById('rest-windows');
            if (container && !container.dataset.draftWired) {
                container.dataset.draftWired = '1';
                container.addEventListener('input', debouncedDraft);
                container.addEventListener('click', debouncedDraft);
            }
            document.querySelectorAll('[data-rest-preset]').forEach(button => {
                if (button.dataset.draftWired) return;
                button.dataset.draftWired = '1';
                button.addEventListener('click', debouncedDraft);
            });
            document.getElementById('btn-add-rest')?.addEventListener('click', debouncedDraft);
        }

        document.addEventListener('DOMContentLoaded', async () => {
            const debouncedDraft = debounce(savePlannerDraft, 300);
            await fetchHotels();

            // No more silent degradation: if there is no AI key, say so instead of
            // letting the built-in planner pass itself off as an AI itinerary.
            if (!AI_ENABLED) {
                showToast('Offline mode — itineraries come from the built-in planner. Add a GROQ_API_KEY for AI itineraries.', 'info', 7000);
            }

            const debouncedCheck = debounce(() => { checkBudget(); savePlannerDraft(); }, 200);
            document.getElementById('trip-days').addEventListener('input', debouncedCheck);
            document.getElementById('trip-budget').addEventListener('input', debouncedCheck);
            initRestWindows();
            wireDraftTracking(debouncedDraft);
            await restorePlannerDraft();
        });

        function setActiveStub(stepNum) {
            for (let i = 1; i <= TOTAL_STEPS; i++) {
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
                    const stars = starsFromRating(hotel.rating);
                    const ratingText = Number(hotel.rating).toFixed(1) + ' / 10';
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
            if (!applyHotelSelection(idx)) return;
            goToStep(2);
            checkBudget();
        }

        function checkBudget() {
            // REGRESSION FIX: noong `budget > 0` ang kundisyon ng unang branch,
            // kaya ang BLANK na budget field ay napapasok sa 'else' — pindutan
            // bukas, walang "#budget-hint". Ito ang hitsura ng Step 02 pagkatapos
            // ng refresh, at doon nagsisimula ang pag-aakala ng traveller na
            // sira ang budget.
            //
            // Ang walanglamang input ay hindi katumbas ng "OK ang budget" — ito
            // ay "hindi pa sagot". Sapat ang isang malinaw na mensahe at
            // nakasarang pindutan.
            const daysEl = document.getElementById('trip-days');
            const budgetEl = document.getElementById('trip-budget');
            const btn = document.getElementById('btn-confirm');
            const hint = document.getElementById('budget-hint');

            const days = parseInt(daysEl.value, 10);
            const budget = parseFloat(budgetEl.value);

            const hasBudget = Number.isFinite(budget) && budget > 0;
            // Ang server ay tumatanggap ng 1..30 na integer lamang, kaya huwag
            // payagang magpadala ng 0, ng 31+, o ng NaN (na serialized bilang
            // JSON null at tinutuktok ng 'days' => 'required').
            const hasValidDays = Number.isInteger(days) && days >= 1 && days <= 30;

            // Lodging is charged per night, using the same offset the server
            // applies, so this hint can never promise a budget the server rejects.
            const safeDays = hasValidDays ? days : 1;
            const nights = Math.max(0, safeDays - NIGHTS_OFFSET);
            const totalCost = currentHotelPrice * nights;
            const remaining = budget - totalCost;

            if (!hasValidDays) {
                btn.disabled = true;
                hint.innerText = 'Enter how many days you are staying (1–30).';
                hint.className = 'text-xs text-[var(--ember-deep)] mb-6 font-mono';
            } else if (!hasBudget) {
                btn.disabled = true;
                hint.innerText = 'Enter your total budget to see your daily allowance.';
                hint.className = 'text-xs text-[var(--sage)] mb-6 font-mono';
            } else if (budget > MAX_TRIP_BUDGET) {
                // REGRESSION FIX: walang hangganan ang dating validation, kaya
                // napasok ang PHP 123,000,000. Sa ganung allowance, lahat ng 47
                // na lugar ay "swak" — kaya walang saysay ang picker.
                btn.disabled = true;
                hint.innerText = `That is above the ₱${MAX_TRIP_BUDGET.toLocaleString()} maximum we can plan for — please check the number.`;
                hint.className = 'text-xs text-[var(--ember-deep)] mb-6 font-mono';
            } else if (budget < totalCost) {
                btn.disabled = true;
                hint.innerText = `Lodging alone costs ₱${totalCost.toLocaleString()} for ${nights} night(s) — increase your budget or shorten your stay.`;
                hint.className = 'text-xs text-[var(--ember-deep)] mb-6 font-mono';
            } else if (remaining / safeDays < MIN_DAILY_ALLOWANCE) {
                // REGRESSION FIX: ang mirror ng MIN_DAILY_ALLOWANCE ng server. Ito
                // ang sagut sa "PHP 1 lang, gumagana pa" — noon, ang Step 02 ay
                // OK dahil bayad na sa hotel, at lang dumating ang problema sa
                // submit. Ang mensahe ay nagsasabi ng eksaktong kulang.
                const shortfall = totalCost + (MIN_DAILY_ALLOWANCE * safeDays) - budget;
                btn.disabled = true;
                hint.innerText = `That leaves about ₱${Math.round(remaining / safeDays).toLocaleString()}/day. `
                    + `Planora needs at least ₱${MIN_DAILY_ALLOWANCE.toLocaleString()}/day (one meal plus tricycle fares) — `
                    + `add ₱${Math.ceil(shortfall).toLocaleString()} to your budget, or pick a cheaper stay.`;
                hint.className = 'text-xs text-[var(--ember-deep)] mb-6 font-mono';
            } else {
                btn.disabled = false;
                hint.className = 'text-xs text-[var(--sage)] mb-6 font-mono';
                const perDay = Math.round(remaining / safeDays);
                const nightLabel = nights === 1 ? '1 night' : `${nights} nights`;
                hint.innerText = `≈ ₱${perDay.toLocaleString()}/day left for food & activities after ${nightLabel} of lodging.`;
            }

            // Nagbago na ang daily allowance, kaya hindi na tama ang naka-cache na
            // listahan ng Step 03. Ang susunod na pagpasok doon ay magre-fetch.
            // Naka-debounce na ito dahil naka-link sa input event.
            loadedPlacesKey = null;
        }

        function goBackToStep1() {
            // Wala nang mapa sa Step 1, kaya itigil na ang GPS watch.
            stopUserTracking();
            goToStep(1);
        }

        // —— Step 03: ang place picker ——
        // Ang allowance na ginagamit dito ay ang parehong arithmetic ng
        // checkBudget() sa itaas, na siya ring ginagamit ng server-side budget
        // guard. Kaya ang "swak sa budget mo" ng picker ay hindi mapanghahati
        // sa server pagkatapos.
        function currentDailyAllowance() {
            const days = readTripDays() ?? 1;
            const budget = readTripBudget() ?? 0;
            const nights = Math.max(0, days - NIGHTS_OFFSET);
            const remaining = budget - (currentHotelPrice * nights);

            return Math.max(0, remaining / days);
        }

        function allWithinBudgetPlaces() {
            const all = [];
            Object.values(placeOptions.within_budget || {}).forEach(list => (list || []).forEach(p => all.push(p)));

            // Ang bawat kategorya ay naiisa na ayon sa distansya mula sa server,
            // kaya ang "All" na pinagsama ay kailangang ayusin muli — kung hindi,
            // unang restaurant, saka mall, saka beach, at hindi ayon sa
            // "pinakamalapit".
            if (placeOptions.origin_known) {
                all.sort((a, b) => (a.distance_km ?? Infinity) - (b.distance_km ?? Infinity));
            }

            return all;
        }

        function findPlace(name) {
            return allWithinBudgetPlaces().find(place => place.name === name) || null;
        }

        // Ang mga lugar na nakikita sa listahan batay sa kasalukuyang filter.
        //
        // REGRESSION FIX: noong may tinatawag na helper dito pero WALANG
        // nade-definisyon — dahil inalis ko ito sa renderPlaceCards() at
        // inlineline ko roon, pero nakalimutang i-update ang call site na ito.
        // Ang ReferenceError ay nahahantul sa catch, na NAGPAPAKISA sa dating
        // na-load na 47 na lugar at nagpapakita ng "0" sa bawat chip.
        //
        // Ibinabalik ito bilang iisang function (hindi i-inline sa isang dako)
        // dahil dalawang consumer ang nangangailangan nito, at ang mismong
        // pagkakamali ang naganap dito. Ang pagtiyak: may eksaktong isang
        // definition at dalawang call site — dapat palaging magkatugma.
        function visiblePlaces() {
            const all = allWithinBudgetPlaces();
            return activePlaceFilter === 'all'
                ? all
                : all.filter(place => place.category === activePlaceFilter);
        }

        // Kabuuang bilang ng katalogo — may kasamang mga hindi umaangkop sa
        // budget. Ito ang nagpapansin kung walang naka-seed na lugar sa lahat, na
        // ibang problema sa "wala kang pwedeng puntahan".
        function catalogueCount() {
            const count = (group) => Object.values(group || {})
                .reduce((total, list) => total + (list || []).length, 0);

            return count(placeOptions.within_budget) + count(placeOptions.over_budget);
        }

        function renderPlaceFilters() {
            const container = document.getElementById('places-filters');
            const options = [{ key: 'all', label: 'All', icon: '✨' }].concat(PLACE_CATEGORIES);

            container.innerHTML = options.map(meta => {
                const active = activePlaceFilter === meta.key;
                const count = meta.key === 'all'
                    ? allWithinBudgetPlaces().length
                    : allWithinBudgetPlaces().filter(p => p.category === meta.key).length;

                return `
                    <button type="button" class="place-filter ${active ? 'is-active' : ''}"
                            data-place-filter="${meta.key}" aria-pressed="${active}" ${count === 0 ? 'disabled' : ''}>
                        <span class="place-filter-label">${meta.icon} ${escapeHtml(meta.label)}</span>
                        <span class="place-filter-count">${count}</span>
                    </button>
                `;
            }).join('');

            container.querySelectorAll('[data-place-filter]').forEach(button => {
                button.addEventListener('click', () => {
                    activePlaceFilter = button.dataset.placeFilter;
                    renderPlaceFilters();
                    renderPlaceCards();
                    savePlannerDraft();
                });
            });
        }

        function renderPlaceCards() {
            const list = document.getElementById('places-list');
            const places = visiblePlaces();
            const allowance = Number(placeOptions.daily_allowance) || 0;

            list.innerHTML = places.map(place => {
                const selected = selectedPlaces.includes(place.name);
                const meta = PLACE_CATEGORIES.find(c => c.key === place.category) || PLACE_CATEGORIES[3];
                const share = allowance > 0 ? Math.round((place.budget_per_day / allowance) * 100) : 0;
                const km = Number(place.distance_km);
                const distance = Number.isFinite(km)
                    ? `<span class="font-mono text-[0.65rem] text-[var(--sage)]">${km < 1 ? `${Math.round(km * 1000)}m` : `${km.toFixed(1)}km`}</span>`
                    : '';
                // Per-place photo mula sa /api/places (image_url), may category
                // fallback kapag null (tingnan ang PlanoraService). Ang onerror
                // ay nagtatago ng sirang <img> (pati zoom button) para emoji
                // icon ang matira.
                const photo = place.image_url
                    ? `<img class="place-card-photo" src="${escapeHtml(place.image_url)}" alt="${escapeHtml(place.name)}" loading="lazy" onerror="this.parentElement?.querySelector('[data-zoom-btn]')?.remove();this.remove()">`
                    : '';
                const zoomBtn = place.image_url
                    ? `<button type="button" class="place-zoom-btn" data-zoom-btn data-zoom-name="${escapeHtml(place.name)}" aria-label="View larger photo of ${escapeHtml(place.name)}" title="View larger photo">⤢</button>`
                    : '';

                return `
                    <article class="place-card ${selected ? 'is-selected' : ''}" role="button" tabindex="0"
                            data-place-name="${escapeHtml(place.name)}" data-place-category="${place.category}"
                            aria-pressed="${selected}">
                        <span class="place-card-media">
                            ${photo}
                            ${zoomBtn}
                            <span class="place-card-badge place-card-badge-float" style="border-color:${meta.dot}22;color:${meta.dot};background:rgba(255,255,255,.92);">
                                <span class="place-dot" style="background:${meta.dot};"></span>${escapeHtml(meta.label)}
                            </span>
                            <span class="place-card-check place-card-check-float" aria-hidden="true">${selected ? '✓' : ''}</span>
                        </span>
                        <span class="place-card-top">
                            <span class="place-card-icon" aria-hidden="true">${escapeHtml(place.icon)}</span>
                            <span class="place-card-title">${escapeHtml(place.name)}</span>
                        </span>
                        <span class="place-card-desc">${escapeHtml(place.description)}</span>
                        <span class="place-card-meta">
                            <span class="font-mono text-xs font-bold text-[var(--deep-teal)]">₱${formatPrice(place.budget_per_day)}/day</span>
                            <span class="font-mono text-[0.65rem] text-[var(--sage)]">${share}% of allowance</span>
                            <span class="text-[var(--ink-soft)] text-xs">★ ${Number(place.rating).toFixed(1)}</span>
                            ${distance}
                        </span>
                    </article>
                `;
            }).join('');

            list.querySelectorAll('[data-place-name]').forEach(card => {
                card.addEventListener('click', (event) => {
                    // Ang zoom button ay nasa loob ng card — huwag i-toggle ang
                    // selection kapag photo viewer ang pinindot.
                    if (event.target.closest('[data-zoom-btn]')) return;
                    togglePlace(card.dataset.placeName, card.dataset.placeCategory);
                });
                // <article role="button">: ibalik ang keyboard support na libre
                // sa dating <button>.
                card.addEventListener('keydown', (event) => {
                    if (event.target.closest('[data-zoom-btn]')) return;
                    if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        togglePlace(card.dataset.placeName, card.dataset.placeCategory);
                    }
                });
            });

            list.querySelectorAll('[data-zoom-btn]').forEach(btn => {
                btn.addEventListener('click', (event) => {
                    event.stopPropagation();
                    openPlacePhoto(btn.dataset.zoomName, btn);
                });
            });

            renderPlaceEmptyState();
        }

        // Dalawang ibang problema ang 'walang nakikita', at dati ito ang
        // pareho ang sinasabi. Hindi katumbas ng "wala sa budget mo" ang
        // "wala pang naka-seed na lugar sa katalogo" — at ang paghahabol ng
        // "db:seed" na utos sa isang bagay na pera lamang ang problema ay
        // aktuwal na mapananlamig.
        function renderPlaceEmptyState() {
            const emptyEl = document.getElementById('places-empty');
            const messageEl = document.getElementById('places-empty-message');

            if (!emptyEl || !messageEl) return;

            const visible = visiblePlaces();
            emptyEl.classList.toggle('hidden', visible.length !== 0);
            if (visible.length !== 0) return;

            const total = catalogueCount();

            if (total === 0) {
                messageEl.innerHTML = 'No places are loaded yet — our Dagupan catalogue is empty. '
                    + 'Run <code class="font-mono">php artisan db:seed</code> to populate it.';
            } else if (activePlaceFilter !== 'all') {
                const label = PLACE_CATEGORIES.find(c => c.key === activePlaceFilter)?.label || 'These';
                messageEl.innerHTML = `No ${escapeHtml(label.toLowerCase())} fit your daily allowance yet. `
                    + 'There are others in other categories — tap <strong>All</strong> to see them.';
            } else {
                const allowance = Number(placeOptions.daily_allowance) || 0;
                messageEl.innerHTML = `None of our ${total} places cost less than your `
                    + `₱${formatPrice(Math.round(allowance))}/day. `
                    + 'Raise your budget or pick a cheaper stay.';
            }
        }

        function togglePlace(name, category) {
            const idx = selectedPlaces.indexOf(name);

            if (idx > -1) {
                selectedPlaces.splice(idx, 1);
            } else {
                if (selectedPlaces.length >= MAX_SELECTED_PLACES) {
                    showToast(`You can pick up to ${MAX_SELECTED_PLACES} places. Remove one first.`, 'warning');
                    return;
                }

                const available = allWithinBudgetPlaces().filter(p => p.category === category).length;
                const picked = selectedPlaces.filter(n => findPlace(n)?.category === category).length;

                if (picked >= Math.min(MAX_SELECTED_PER_CATEGORY, available)) {
                    showToast(`Keep it to ${MAX_SELECTED_PER_CATEGORY} per category so every category gets a look in.`, 'warning');
                    return;
                }

                selectedPlaces.push(name);
            }

            renderPlaceCards();
            updatePlaceSummary();
            savePlannerDraft();
        }

        function clearPlaceSelection() {
            selectedPlaces = [];
            renderPlaceCards();
            updatePlaceSummary();
            savePlannerDraft();
            showToast('Cleared — the AI will choose for you.', 'info');
        }

        function updatePlaceSummary() {
            const summary = document.getElementById('places-summary');
            const clearBtn = document.getElementById('btn-let-ai-decide');

            if (selectedPlaces.length === 0) {
                summary.innerText = 'Nothing picked — this step is optional. The AI will choose for you.';
                summary.className = 'font-mono text-xs text-[var(--sage)]';
                clearBtn.disabled = true;
                return;
            }

            const total = selectedPlaces.reduce((sum, name) => sum + (findPlace(name)?.budget_per_day || 0), 0);
            const allowance = Number(placeOptions.daily_allowance) || 0;
            const share = allowance > 0 ? ` (${Math.round((total / allowance) * 100)}% of your allowance)` : '';

            summary.innerText = `${selectedPlaces.length} of ${MAX_SELECTED_PLACES} picked · ₱${formatPrice(total)}/day${share}`;
            summary.className = 'font-mono text-xs text-[var(--deep-teal)]';
            clearBtn.disabled = false;
        }

        function setPlacesStatus(state, html) {
            const statusEl = document.getElementById('places-status');
            const retryEl = document.getElementById('places-retry');

            if (statusEl) {
                statusEl.innerHTML = html;
                statusEl.classList.remove('hidden');
            }

            if (retryEl) {
                retryEl.classList.toggle('hidden', state !== 'error');
            }
        }

        async function loadPlaceOptions(force = false) {
            const allowance = currentDailyAllowance();
            const sortNoteEl = document.getElementById('places-sort-note');

            // REGRESSION FIX: ang cache key ay dapat kasama ang hotel. Dati
            // allowance lang ang tiningnan — kaya kapag bumalik ang traveller sa
            // Step 01 at pumili ng ibang hotel na magkparehong budget, naibigay
            // sa kanya ang listahan na ayon sa distansya mula sadating hotel.
            const cacheKey = `${allowance}|${hotelName}|${selectedHotelGeocoded ? selectedLat : 'nogeocode'}`;

            if (!force && loadedPlacesKey === cacheKey) {
                return;
            }

            loadedPlacesKey = cacheKey;
            setPlacesStatus('loading', '<span class="w-1.5 h-1.5 rounded-full bg-[var(--ember)] pulse-soft"></span> Finding spots near your hotel…');

            // REGRESSION FIX — ito ang totoong kalakal.
            //
            // Dati, kahit ANONG error sa function na ito ay nagtatakbo sa catch
            // na naglilinis ng `placeOptions` at nagre-render. Isang
            // ReferenceError sa isang kosmetikong label (ang sort note) ay
            // naging 47 na na-load na lugar na "0" sa bawat chip at "wala kang
            // mapipili".
            //
            // Ngayon, ang datos ay NILILINIS LANG kung talagang hindi dumating ang
            // payload. Kung dumating na ito at may ibang sira sa huli, ang
            // listahan ay nananatiling buo at ang error ay iniuulat lamang — ang
            // isang sirang label ay hindi dapat magmahalaga ng pindutan ng
            // "Generate".
            let fetched = false;

            try {
                // REGRESSION FIX: ang mga DOM write na ito ay dating labas ng try.
                // Kapag nawala ang isang element (hal. mula sa cached na HTML),
                // ang TypeError ay naging unhandled rejection — walang fetch, walang
                // card, at walang mensahe. Null-guard na sila, at nasa loob na ng
                // try.
                const allowanceEl = document.getElementById('places-allowance');
                const originEl = document.getElementById('places-origin');
                if (allowanceEl) {
                    allowanceEl.textContent = allowance > 0 ? `₱${formatPrice(Math.round(allowance))}` : '₱0';
                }
                if (originEl) {
                    originEl.textContent = hotelName || 'your hotel';
                }

                // REGRESSION FIX: kapag hindi pa geocode ang hotel, huwag ipadala
                // ang map fallback bilang pinagmulan — magiging totoo ang
                // `origin_known` at maaaring magmukhang "nearest first" ang isang
                // listahan na ayon naman sa rating.
                const origin = selectedHotelGeocoded
                    ? `&lat=${selectedLat}&lon=${selectedLon}`
                    : '';
                const res = await fetch(`/api/places?daily_allowance=${allowance}${origin}`);
                if (!res.ok) throw new Error(`Places request failed (${res.status})`);

                const data = await res.json();
                if (!data || typeof data !== 'object') {
                    throw new Error('The places service returned an unexpected response.');
                }

                placeOptions = data;
                placeOptions.within_budget = placeOptions.within_budget || {};
                placeOptions.over_budget = placeOptions.over_budget || {};
                fetched = true;

                // REGRESSION FIX: kung bumaba ang budget, maaaring wala nang
                // anumang beach (hal.) na umaangkop. Dati ay nananatiling aktibo
                // ang filter chip na iyon — disabled man, nakatago pa rin sa
                // 'is-active' — kaya walang lumalabas na card at walang
                // makakapili ng ibang kategorya ang traveller.
                if (activePlaceFilter !== 'all'
                    && allWithinBudgetPlaces().filter(p => p.category === activePlaceFilter).length === 0) {
                    activePlaceFilter = 'all';
                }

                // Kapag bina ang budget, maaaring wala na sa bagong bracket ang
                // dating pinili. Itinatapon ang mga naaari — ang label na wala
                // nang presyo ay laging mali para sa server.
                const selectable = new Set(allWithinBudgetPlaces().map(p => p.name));
                const dropped = selectedPlaces.filter(name => !selectable.has(name));
                selectedPlaces = selectedPlaces.filter(name => selectable.has(name));

                renderPlaceFilters();
                renderPlaceCards();
                updatePlaceSummary();

                const statusEl = document.getElementById('places-status');
                if (statusEl && allWithinBudgetPlaces().length > 0) {
                    statusEl.classList.add('hidden');
                }

                // Tapat lamang ang label kapag may alam na hotel coordinates —
                // kung wala, ayon sa rating ang pagkakasunod-sunod.
                const nearest = visiblePlaces()[0];
                const nearestKm = Number(nearest?.distance_km);
                if (sortNoteEl) {
                    sortNoteEl.textContent = placeOptions.origin_known
                        ? (Number.isFinite(nearestKm)
                            ? `Sorted nearest first · closest is ${nearestKm < 1 ? `${Math.round(nearestKm * 1000)}m` : `${nearestKm.toFixed(1)}km`} away`
                            : 'Sorted nearest first')
                        : 'This stay has no map location yet — sorted by rating instead.';
                }

                if (dropped.length > 0) {
                    showToast(`Budget changed — removed ${dropped.length} pick(s) that no longer fit.`, 'info', 5000);
                }
            } catch (error) {
                console.error('[Planora] Place picker failed:', error);

                // REGRESSION FIX: ito ang pinakaimportanteng linya. Dati, WALANG
                // pagtatangkang ikondisyonal ito — kaya kahit matagumpay ang fetch
                // at 47 nang card ang naka-render, isang ReferenceError sa
                // sort-note ay NAGPAPAWALANG lahat. Ngayon, ang listahan ay
                // nananatiling buo at ang error ay iniuulat lamang.
                if (!fetched) {
                    // Walang payload na dumating — ito lang ang sitwasyong tama
                    // na mag-clear. Hindi ito hadlang: optional ang picker.
                    placeOptions = { within_budget: {}, over_budget: {} };
                    renderPlaceFilters();
                    renderPlaceCards();
                    updatePlaceSummary();
                }

                if (sortNoteEl) sortNoteEl.textContent = '';

                setPlacesStatus(
                    'error',
                    `<span class="text-[var(--ember-deep)]">${escapeHtml(error.message || 'Could not load places.')} `
                    + 'You can still generate an itinerary below.</span>'
                );
            }
        }

        // Isang pagkaka-source ng pagpapatak: ang Step 02, ang picker, at ang
        // submit ay dapat gumamit ng parehong pagpapahalaga. Dati, ang
        // checkBudget()/currentDailyAllowance() ay may `|| 1` na fallback pero
        // walang ang submit — kaya kapag binura ang "days", pumapasok ang NaN,
        // nase-serialize ito bilang JSON null, at tinutuktok ng server ang
        // validation ng 'days' => 'required' (422) na walang malinaw na dahilan.
        function readTripDays() {
            const days = parseInt(document.getElementById('trip-days').value, 10);
            return (Number.isInteger(days) && days >= 1 && days <= 30) ? days : null;
        }

        function readTripBudget() {
            const budget = parseFloat(document.getElementById('trip-budget').value);
            return (Number.isFinite(budget) && budget > 0 && budget <= MAX_TRIP_BUDGET) ? budget : null;
        }

        async function goToPlaces() {
            if (readTripBudget() === null) {
                showToast('Please enter a valid budget amount.', 'warning');
                return;
            }

            if (readTripDays() === null) {
                showToast('Please enter how many days (1–30).', 'warning');
                return;
            }

            goToStep(3);

            await loadPlaceOptions();
        }

        function goBackToStep2() {
            goToStep(2);
        }

        // —— Rest schedule: ang traveller mismo ang pumipili ng oras ng rest ——
        // Ang server ay tumatanggap ng 'HH:MM-HH:MM'; ang end na mas maaga o
        // katumbas ng start ay itinuturing na overnight (hal. 22:00-06:00).
        const MAX_REST_WINDOWS = 4;

        function restClockToMinutes(value) {
            return parseInt(value.slice(0, 2), 10) * 60 + parseInt(value.slice(3, 5), 10);
        }

        // '14:00' → '2:00 PM' para 12-hour ang mga oras na nakikita ng traveller.
        // Ang inputs at ang API payload ay 'HH:MM' pa rin.
        function formatClockTime(value) {
            const hours = parseInt(value.slice(0, 2), 10);
            const minutes = value.slice(3, 5);
            const display = hours % 12 === 0 ? 12 : hours % 12;

            return `${display}:${minutes} ${hours >= 12 ? 'PM' : 'AM'}`;
        }

        function buildRestRow(start = '', end = '') {
            const row = document.createElement('div');
            row.className = 'rest-window';
            row.innerHTML = `
                <input type="time" class="rest-start input-field w-full sm:w-36 p-2.5 rounded-xl font-mono focus-ring" value="${start}" aria-label="Rest start time">
                <span class="text-[var(--sage)] font-mono text-sm" aria-hidden="true">→</span>
                <input type="time" class="rest-end input-field w-full sm:w-36 p-2.5 rounded-xl font-mono focus-ring" value="${end}" aria-label="Rest end time">
                <button type="button" class="rest-remove focus-ring px-3 py-2 rounded-xl border-[1.5px] border-[var(--line)] text-[var(--ember-deep)] hover:bg-[var(--sand-deep)] transition" aria-label="Remove this rest period">✕</button>
            `;

            row.querySelector('.rest-start').addEventListener('input', updateRestSummary);
            row.querySelector('.rest-end').addEventListener('input', updateRestSummary);
            row.querySelector('.rest-remove').addEventListener('click', () => {
                row.remove();
                syncRestControls();
            });

            return row;
        }

        function addRestWindow(start = '', end = '') {
            const container = document.getElementById('rest-windows');

            if (container.children.length >= MAX_REST_WINDOWS) {
                return null;
            }

            const row = buildRestRow(start, end);
            container.appendChild(row);
            syncRestControls();

            return row;
        }

        function syncRestControls() {
            const rows = document.getElementById('rest-windows').children;

            document.getElementById('btn-add-rest').disabled = rows.length >= MAX_REST_WINDOWS;

            // Hindi pwedeng alisin ang nag-iisang row.
            Array.from(rows).forEach(row => {
                row.querySelector('.rest-remove').disabled = rows.length <= 1;
            });

            updateRestSummary();
        }

        function applyRestPreset(button) {
            const container = document.getElementById('rest-windows');
            const emptyRow = Array.from(container.children).find(row =>
                !row.querySelector('.rest-start').value && !row.querySelector('.rest-end').value
            );

            const row = emptyRow || addRestWindow(button.dataset.start, button.dataset.end);

            if (!row) {
                showToast('Maximum of 4 rest periods only.', 'warning');
                return;
            }

            if (emptyRow) {
                emptyRow.querySelector('.rest-start').value = button.dataset.start;
                emptyRow.querySelector('.rest-end').value = button.dataset.end;
            }

            button.classList.add('is-applied');
            setTimeout(() => button.classList.remove('is-applied'), 700);
            syncRestControls();
        }

        function collectRestWindows() {
            const windows = [];

            document.querySelectorAll('#rest-windows .rest-window').forEach(row => {
                // Laging 'HH:MM' ang kailangan ng server, kaya pinuputol ang seconds.
                const start = (row.querySelector('.rest-start').value || '').slice(0, 5);
                const end = (row.querySelector('.rest-end').value || '').slice(0, 5);

                if (start && end) {
                    windows.push(`${start}-${end}`);
                }
            });

            return windows;
        }

        function updateRestSummary() {
            const summary = document.getElementById('rest-summary');
            const windows = collectRestWindows();

            if (!windows.length) {
                summary.innerText = 'No Schedule set.';
                summary.className = 'font-mono text-xs text-[var(--sage)]';
                return;
            }

            const parts = windows.map(value => {
                const [start, end] = value.split('-');
                const startAt = restClockToMinutes(start);
                const endAt = restClockToMinutes(end);
                let minutes = endAt - startAt;
                if (minutes <= 0) minutes += 1440;
                const hours = minutes / 60;
                const duration = hours >= 1 ? `${Number(hours.toFixed(1))}h` : `${minutes}m`;

                return `${formatClockTime(start)}–${formatClockTime(end)} (${duration}${endAt <= startAt ? ', overnight' : ''})`;
            });

            summary.innerText = `Daily rest: ${parts.join('  +  ')}`;
            summary.className = 'font-mono text-xs text-[var(--deep-teal)]';
        }

        function initRestWindows() {
            addRestWindow();

            document.getElementById('btn-add-rest').addEventListener('click', () => addRestWindow());
            document.querySelectorAll('[data-rest-preset]').forEach(button => {
                button.addEventListener('click', () => applyRestPreset(button));
            });
        }

        async function generateItinerary() {
            // Ang oras ng rest ay galing sa time pickers na pinuno ng traveller.
            const restDays = collectRestWindows();
            const days = readTripDays();
            const budget = readTripBudget();

            if (budget === null) {
                showToast('Please enter a valid budget amount.', 'warning');
                return;
            }

            // REGRESSION FIX: walang proteksyon ang 'days' dati. Kapag binura
            // ang field, parseInt() ay NaN, JSON.stringify() ay naging null, at
            // tinutuktok ng server ang 'days' => 'required' — isang 422 na walang
            // malinaw na mensahe sa screen, na tila sira ang app.
            if (days === null) {
                showToast('Please enter how many days (1–30).', 'warning');
                return;
            }

            toggleButtonState('btn-generate', false);
            goToStep(4);
            document.getElementById('map').style.display = 'block';
            nearbyPlacesData = [];
            nearbyPlacesIndex = [];
            activeVisits = {};
            poiMarkers = {};

            // Ang index 1 ("Checking weather…") ay hindi na ginagamit: na-resolve
            // na ng server ang kondisyon mula sa coordinates ng hotel, kaya walang
            // weather round-trip dito sa browser.
            showProgress(0);
            initMap();

            showProgress(2);
            await fetchNearbyAmenities(selectedLat, selectedLon);

            showProgress(3);
            fetch('/generate-plan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    hotel: hotelName,
                    days: days,
                    budget: budget,
                    rest_days: restDays,
                    // Live conditions are resolved server-side, so the client no
                    // longer sends a hardcoded "not available" placeholder.
                    nearby_places: nearbyPlacesData.join('|'),
                    // Ang mga pinili sa Step 03. Ang server ang nagsasabi kung
                    // alin ang totoo at tinatanggap mula sa katalogo.
                    selected_places: selectedPlaces
                })
            })
            .then(async res => {
                if (res.status === 401 || res.status === 419) {
                    window.location.href = '/login';
                    throw new Error('Your session expired. Please sign in again.');
                }
                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.error || `Server error (${res.status})`);
                }
                return res.json();
            })
            .then(data => {
                showProgress(4);
                renderItinerary(data.recommendation, data);
                // Sa itinerary lang nakasalalay ang POI pins sa mapa, pero ang
                // mga pinili ng traveller ay pinipin kahit hindi sila maabot ng
                // salita — kung hindi, magmumukhang walang epekto ang picker.
                renderSuggestedPlaces();
                renderPickedPlaces(data.selected_places || selectedPlaces);
                // Iayos ang guided-trip stops (hotel → sunod-sunod na napiling
                // lugar) at ikabit ang arrival-card listener para gumana ang
                // arrival → check-in → timer → check-out → auto-route flow.
                buildTripStops();
                bindArrivalCardListener();
                const warningBox = document.getElementById('budget-warning-box');
                if (data.budget_warning) {
                    warningBox.innerText = data.budget_warning;
                    warningBox.classList.remove('hidden');
                } else {
                    warningBox.classList.add('hidden');
                }
                hideProgress();
                // Ngayon may laman na ang Step 04 — itabi ang meta at i-save
                // ang draft muli. Ang naunang save (mula sa goToStep(4)) ay
                // wala pang itinerary; dito pa lang ito nagkakaroon, kaya dito
                // rin dapat ito i-persist — kung hindi, babagsak pa rin sa
                // Step 03 ang magre-refresh.
                lastItineraryMeta = {
                    ai_provider: data.ai_provider || 'local',
                    daily_allowance: data.daily_allowance,
                    budget_warning: data.budget_warning || '',
                    selected_places: data.selected_places || selectedPlaces,
                };
                savePlannerDraft();
                showToast('Your itinerary is ready!', 'success', 5000);
            })
            .catch(error => {
                hideProgress();
                if (!error.message.includes('budget') && !error.message.includes('Budget')) {
                    showToast(error.message || 'Something went wrong', 'error', 6000);
                } else {
                    showToast(error.message, 'warning', 6000);
                }
                stopUserTracking();
                // Papunta sa Step 03, hindi sa 02 — ang budget guard ay
                // tinatawag habang nasa unang await, kaya ang mga napiling
                // lugar ay pa rin ang context na dapat makita ang error.
                goToStep(3);
            })
            .finally(() => {
                // REGRESSION FIX: ang btn-generate ay dinidisable sa simula ng
                // pag-submit, pero walang tumutugon na nag-e-enable nang muli.
                // Kapag may error, ibinabalik namin ang traveller sa Step 03 —
                // kung hindi, hindi niya maipapindut ang "Generate itinerary"
                // ulit at kailangan ng full reload para makabawi.
                //
                // Hindi nito binubuksan ang pindutan kapang walang katotohanan:
                // ang 'budget < lodging' na hard guard ay nasa btn-confirm /
                // checkBudget(), hindi dito. Ang pindutan ay 'type="button"'
                // na walang default na aksyon, kaya walang mapipigil kahit
                // kapag naka-disable.
                toggleButtonState('btn-generate', true);
                checkBudget();
            });
        }

        let currentItineraryMarkdown = '';
        // Ang huling meta na ibinabalik ng /generate-plan (provider, daily
        // allowance, budget warning, tunay na napili). Kasama ito sa draft
        // para maibalik ang buong Step 04 — header card, warning box, at
        // pins — pagkatapos ng refresh nang walang bagong AI call.
        let lastItineraryMeta = {};

        function renderItinerary(markdown, meta = {}) {
            currentItineraryMarkdown = markdown || '';
            const container = document.getElementById('ai-output');
            container.innerHTML = '';

            const headerCard = document.getElementById('itinerary-header-card');
            const providerBadge = document.getElementById('itinerary-provider-badge');
            const metaText = document.getElementById('itinerary-meta-text');

            if (headerCard && providerBadge && metaText) {
                const isGroq = (meta.ai_provider || '').toLowerCase() === 'groq';
                providerBadge.className = 'itinerary-badge ' + (isGroq ? 'itinerary-badge-groq' : 'itinerary-badge-local');
                providerBadge.innerText = isGroq ? 'AI generated (Groq)' : 'Offline generator';

                const parts = [];
                if (hotelName) parts.push(hotelName);
                const daysInput = document.getElementById('trip-days');
                const days = daysInput ? parseInt(daysInput.value, 10) : null;
                if (days) parts.push(`${days} day${days > 1 ? 's' : ''}`);
                if (meta.daily_allowance) parts.push(`~PHP ${Number(meta.daily_allowance).toLocaleString()}/day allowance`);
                const pickCount = (meta.selected_places || selectedPlaces || []).length;
                if (pickCount) parts.push(`${pickCount} of your picks`);
                metaText.innerText = parts.join(' · ');
                headerCard.classList.remove('hidden');
            }

            const sections = (markdown || '').split(/\n(?=###\s)/).filter(s => s.trim() !== '');
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

        function toggleAllItineraryDays() {
            const days = document.querySelectorAll('#ai-output details.itinerary-day');
            if (days.length === 0) return;
            const anyOpen = Array.from(days).some(d => d.open);
            days.forEach(d => { d.open = !anyOpen; });
            const btn = document.getElementById('btn-toggle-days');
            if (btn) btn.innerText = anyOpen ? 'Expand all' : 'Collapse all';
        }

        function copyItinerary() {
            if (!currentItineraryMarkdown) {
                showToast('No itinerary to copy.', 'warning');
                return;
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(currentItineraryMarkdown)
                    .then(() => showToast('Itinerary copied to clipboard!', 'success', 3000))
                    .catch(() => fallbackCopy(currentItineraryMarkdown));
            } else {
                fallbackCopy(currentItineraryMarkdown);
            }
        }

        function fallbackCopy(text) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                showToast('Itinerary copied to clipboard!', 'success', 3000);
            } catch (e) {
                showToast('Could not copy itinerary.', 'error');
            }
            document.body.removeChild(ta);
        }

        function makeIcon(emoji, cls) {
            // Flaticon-style crisp vector SVGs with soft shadows
            const svgIcons = {
                'pin-user': `<svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:white;display:block;transform:rotate(-45deg);filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/></svg>`,
                // Bed / Hotel icon
                'pin-hotel': `<svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M7 13c1.66 0 3-1.34 3-3S8.66 7 7 7s-3 1.34-3 3 1.34 3 3 3zm12-6h-8v7H3V5H1v15h2v-3h18v3h2v-9c0-2.21-1.79-4-4-4z"/></svg>`,
                // Fork & Knife / Restaurant icon
                'pin-restaurant': `<svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z"/></svg>`,
                // Shopping Bag / Mall icon
                'pin-mall': `<svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M18 6h-2c0-2.21-1.79-4-4-4S8 3.79 8 6H6c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm-6-2c1.1 0 2 .9 2 2h-4c0-1.1.9-2 2-2zm6 16H6V8h2v2c0 .55.45 1 1 1s1-.45 1-1V8h4v2c0 .55.45 1 1 1s1-.45 1-1V8h2v12z"/></svg>`,
                // Camera / Tourist Attraction icon
                'pin-tourist': `<svg viewBox="0 0 24 24" style="width:17px;height:17px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M12 12c-1.65 0-3 1.35-3 3s1.35 3 3 3 3-1.35 3-3-1.35-3-3-3zm0-2c2.76 0 5 2.24 5 5s-2.24 5-5 5-5-2.24-5-5 2.24-5 5-5zm8-5h-3.17L15 3H9L7.17 5H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 14H4V7h4.05l1.83-2h4.24l1.83 2H20v12z"/></svg>`,
                // Sun & Parasol Umbrella / Beach icon
                'pin-beach': `<svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));"><path d="M13.5 6c0-1.93-1.57-3.5-3.5-3.5S6.5 4.07 6.5 6 8.07 9.5 10 9.5s3.5-1.57 3.5-3.5zm-5 0c0-.83.67-1.5 1.5-1.5s1.5.67 1.5 1.5S10.83 7.5 10 7.5 8.5 6.83 8.5 6zm5.64 6.86C13.57 12.33 12.82 12 12 12c-.82 0-1.57.33-2.14.86L2.36 20.36 3.78 21.78 11 14.56V22h2v-7.44l7.22 7.22 1.42-1.42-7.5-7.5z"/></svg>`
            };

            const extraClass = (cls === 'pin-user') ? ' pin-user-pulse' : (cls === 'pin-hotel' ? ' pin-hotel-pulse' : '');
            const svgContent = svgIcons[cls] || `<span style="font-size:0.75rem;font-weight:700;color:white;">•</span>`;

            return L.divIcon({
                className: '',
                html: `<div class="map-pin ${cls}${extraClass}">${svgContent}</div>`,
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

        let userMarker = null;
        let userLocation = null;

        // —— Realtime tracking state ——
        // Isang watchPosition lang ang bukas sa isang pagkakataon, at throttled
        // ang re-route para hindi bahain ang public OSRM server at ang battery.
        let userWatchId = null;
        let lastRoutedPoint = null;
        let lastRoutedAt = null;
        // Dalawang magkaibang tanong ang nasa likod ng tracking: ang
        // `trackingWanted` ay ang kagustuhan ng traveller, habang ang
        // `userWatchId` ay ang totoong estado ng watch. Walang flag na ito dati,
        // kaya ang visibilitychange handler na nagre-restart ay binubura ang
        // pagpipili na patayin na ng user ang tracking.
        let trackingWanted = false;
        const REROUTE_MIN_MOVE_KM = 0.12;   // ~120 m na galaw bago mag-recompute
        const REROUTE_MAX_AGE_MS = 25000;   // o 25s na pagitan — alinman ang mauna

        // —— Guided trip stops (arrival → check-in → timer → check-out) ——
        // Ang buong biyahe bilang ordered list: ang hotel ang unang stop, tapos
        // ang mga napiling lugar sa chronological order ng itinerary. Ang
        // `activeStopIndex` ang nagsasabi kung saan tayo ngayon.
        let tripStops = [];
        let activeStopIndex = 0;
        // Ilang metro ang itinuturing na "nakarating na" sa isang stop. Sapat na
        // malapit para sa GPS accuracy sa cellular, hindi masyadong malapit para
        // hindi mag-trigger habang nagmamaneho pa sa kalsada.
        const ARRIVAL_RADIUS_M = 200;
        // Name ng stop na pinakita ngayon sa arrival card — para maiwasang
        // mag-re-render tuwing GPS tick kapag pareho pa rin ang aktibong stop.
        let arrivalCardShownFor = null;

        // Isang pin lang ang dapat lumabas sa mapa: ang module-scope na `userMarker`
        // ang source of truth, at ang `window.userMarker` ay alias para sa mga
        // legacy caller (Show My Location button).
        function removeUserMarker() {
            if (userMarker) {
                if (markerLayers) markerLayers.removeLayer(userMarker);
                userMarker = null;
            }
            window.userMarker = null;
        }

        function upsertUserMarker(lat, lon, popupHTML, openPopup = false) {
            // Kapag na-recreate ang mapa (initMap), ang lumang marker ay hindi na
            // kasama sa bagong markerLayers kaya kailangang gumawa ng bagong pin.
            const isStale = !userMarker || !markerLayers || !markerLayers.hasLayer(userMarker);

            if (isStale) {
                removeUserMarker();
                userMarker = L.marker([lat, lon], { icon: makeIcon('U', 'pin-user') })
                    .addTo(markerLayers);
            } else {
                userMarker.setLatLng([lat, lon]);
            }

            if (popupHTML) userMarker.bindPopup(popupHTML);
            window.userMarker = userMarker;
            if (openPopup) userMarker.openPopup();

            return userMarker;
        }

        // Fallback ETA lang ito kapag hindi maabot ang OSRM router (provincial
        // highway average).
        const FALLBACK_SPEED_KMH = 45;

        function haversineKm(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon / 2) * Math.sin(dLon / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        function formatDriveTime(totalSeconds) {
            const hours = Math.floor(totalSeconds / 3600);
            const mins = Math.round((totalSeconds % 3600) / 60);
            return hours > 0 ? `${hours} hr ${mins} mins` : `${mins} mins`;
        }

        function initMap() {
            // Ang dating watch ay kabilang pa sa lumang mapa, kaya itinitigil muna
            // (nire-recreate ang mapa sa ibaba).
            stopUserTracking();

            if (map) map.remove();

            // Ang marker at route ng naunang mapa ay hindi na valid sa bagong mapa.
            userMarker = null;
            window.userMarker = null;
            currentRouteControl = null;

            map = L.map('map', {
                zoomControl: true,
                attributionControl: true
            }).setView([selectedLat, selectedLon], 14);
            markerLayers = L.layerGroup().addTo(map);

            // Isang delegated listener lang bawat recreated na mapa, kaya walang
            // pag-iipon ng listener habang naglilipat ng Step 3.
            map.getContainer().addEventListener('click', handlePoiActionClick);

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
            const hotelIcon = makeIcon('H', 'pin-hotel');

            L.marker([selectedLat, selectedLon], { icon: hotelIcon })
                .addTo(markerLayers)
                .bindPopup(`<b>${hotelName}</b><br><span style="color:var(--sage);font-size:0.85rem;">Your Basecamp</span>`)
                .openPopup();

            autoDetectAndRouteToHotel();
            startUserTracking();

            // Stay duration tracker: kunin ang mga bukas na check-in para lumabas
            // ang "Check Out" (kasama ang live timer) imbes na "Check In", at
            // simulan ang segundo-segundong pag-update ng tagal ng pananatili.
            loadActiveVisits();
            startStayTimer();
        }

        function autoDetectAndRouteToHotel() {
            if (!navigator.geolocation) return;

            const card = document.getElementById('route-guidance-card');
            const detailsEl = document.getElementById('route-guidance-details');
            const liveBadge = document.getElementById('route-badge-live');

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const uLat = position.coords.latitude;
                    const uLon = position.coords.longitude;
                    userLocation = { lat: uLat, lon: uLon };

                    upsertUserMarker(uLat, uLon, `<b>You are here</b><br><span style="font-size:0.78rem;color:var(--ink-soft);">Detected GPS Location</span>`);

                    // Na-route na ang puntong ito, kaya hindi na ito uulitin ng
                    // unang watch callback (ito ang throttle baseline).
                    lastRoutedPoint = { lat: uLat, lon: uLon };
                    lastRoutedAt = Date.now();

                    routeFromUserToHotel(uLat, uLon);
                },
                (err) => {
                    // Hindi nakakuha ng GPS (denied / unavailable): ipakita pa rin ang
                    // card na may paliwanag kaysa sa tahimik na walang feedback.
                    console.log('User location not granted or unavailable:', err.message);
                    if (card && detailsEl) {
                        card.classList.remove('hidden');
                        detailsEl.innerHTML = 'Location unavailable — allow location access to see the live route from your current position.';
                    }
                    if (liveBadge) liveBadge.classList.add('hidden');
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }

        // —— Realtime tracking ——
        // Ang dating one-shot getCurrentPosition() ay hindi sumusunod sa gumagalaw
        // na traveller. Ang watchPosition() ang nagbibigay ng tuloy-tuloy na
        // updates, pero hindi kada metro ang re-route para hindi mabaha ang OSRM.
        function needsReroute(lat, lon, lastPoint, lastAt, now) {
            if (!lastPoint || !lastAt) return true;
            if (haversineKm(lastPoint.lat, lastPoint.lon, lat, lon) >= REROUTE_MIN_MOVE_KM) return true;

            return (now - lastAt) >= REROUTE_MAX_AGE_MS;
        }

        // Sentinel na sinasabing "naka-subscribe pa" — hindi pa natin alam ang totoong
        // watch id.
        const WATCH_PENDING = -1;

        function startUserTracking() {
            trackingWanted = true;
            if (!navigator.geolocation || userWatchId !== null) return;

            // Itinatag muna ang sentinel bago mag-subscribe. May browser na
            // tumatawag ng error handler na synchronous, at kung sa ganoon ay
            // hindi pa napapasok ng stopUserTracking() ang watchId — naipupunta
            // ang watch na hindi na kayang i-clear.
            userWatchId = WATCH_PENDING;

            const watchId = navigator.geolocation.watchPosition(
                onUserPosition,
                onUserPositionError,
                { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
            );

            if (userWatchId === WATCH_PENDING) {
                userWatchId = watchId;
            } else {
                // Na-error na bago makapasok ang assignment: patay na ito,
                // huwag nang itong panatilihin bilang aktibong watch id.
                navigator.geolocation.clearWatch(watchId);
            }

            setTrackingState(true);
        }

        // Ang `keepIntent` ay para sa visibilitychange handler: ang pagpigil
        // dahil naka-background ang tab ay hindi dapat mag-clear ng kagustuhan
        // ng traveller, kaya i-rerestart pa rin ang watch pagbalik.
        function stopUserTracking(options = {}) {
            if (!options.keepIntent) trackingWanted = false;

            if (userWatchId !== null && navigator.geolocation) {
                navigator.geolocation.clearWatch(userWatchId);
            }

            userWatchId = null;
            lastRoutedPoint = null;
            lastRoutedAt = null;
            setTrackingState(false);
        }

        function toggleUserTracking() {
            // Dati ay `userWatchId === null` ang ginamit, kaya kapag na-error
            // nang magsubscribe ay inuulit ang pag-on.
            if (trackingWanted) {
                stopUserTracking();
                showToast('Live tracking stopped.', 'info', 3000);
            } else {
                startUserTracking();
                showToast('Live tracking on — the U pin follows you.', 'success', 3000);
            }
        }

        function setTrackingState(tracking) {
            const badge = document.getElementById('tracking-status');
            const btn = document.getElementById('btn-toggle-tracking');

            // Inline style ang kailangan: ang `.map-legend span` na
            // `display: inline-flex` ay mas mataas ang specificity sa `.hidden`.
            if (badge) badge.style.display = tracking ? '' : 'none';
            if (btn) btn.innerText = tracking ? 'Stop live tracking' : 'Start live tracking';
        }

        // —— Guided trip: arrival → check-in → timer → check-out → auto-route ——
        // Bumubuo ng ordered na listahan ng buong biyahe. Stop #1 ang hotel
        // (basecamp) kung saan nagsisimula; sunod ang mga napiling lugar sa
        // chronological order ng itinerary (kung paano binanggit). Ginagamit ang
        // parehong coordinate catalogue at text matcher ng mga pin, kaya tugma
        // ang stops sa nakapin sa mapa.
        function buildTripStops() {
            const stops = [];
            const seen = new Set();
            const hotelKey = normalizePlaceText(hotelName);

            // Stop #1: ang hotel/basecamp.
            if (hotelName) {
                stops.push({ name: hotelName, lat: selectedLat, lon: selectedLon, isHotel: true });
                seen.add(hotelKey);
            }

            // Canonical na napili mula sa meta kapag meron; kung wala, ang
            // aktuwal na selectedPlaces.
            const picks = (lastItineraryMeta && Array.isArray(lastItineraryMeta.selected_places) && lastItineraryMeta.selected_places.length)
                ? lastItineraryMeta.selected_places
                : selectedPlaces;

            const itineraryText = currentItineraryText();

            // Ihanay ang mga napili base sa posisyon ng unang banggit nila sa
            // itinerary (chronological). Ang hindi nabanggit ay mananatili sa
            // picker order nila at mapupunta sa dulo ng listahan.
            const ordered = picks
                .map((rawLabel, idx) => {
                    const name = String(rawLabel).replace(/\s*\((Restaurant|Mall|Beach|Tourist Spot)\)\s*$/i, '').trim();
                    const pos = itineraryText.indexOf(normalizePlaceText(name));
                    return { name, pos: pos === -1 ? Number.MAX_SAFE_INTEGER : pos, idx };
                })
                .sort((a, b) => (a.pos - b.pos) || (a.idx - b.idx));

            ordered.forEach(item => {
                if (!item.name) return;
                const key = normalizePlaceText(item.name);
                if (key.length < 3 || seen.has(key)) return;
                // Huwag idagdag ang hotel bilang stop (sariling 'H' pin na).
                if (hotelKey.length >= 3 && (key.includes(hotelKey) || hotelKey.includes(key))) return;
                const place = findPlace(item.name);
                if (!place || !Number.isFinite(Number(place.lat)) || !Number.isFinite(Number(place.lon))) return;
                seen.add(key);
                stops.push({ name: place.name, lat: Number(place.lat), lon: Number(place.lon), isHotel: false });
            });

            tripStops = stops;
            activeStopIndex = 0;
            arrivalCardShownFor = null;
            return stops;
        }

        // Ang arrival card ay nasa labas ng map container, kaya hindi ito
        // naririnig ng handler na nakatali sa map. Parehong handler ang ginamit
        // (data-poi-action) — isang beses lang itong ikinakabit (static markup).
        let arrivalCardListenerBound = false;
        function bindArrivalCardListener() {
            if (arrivalCardListenerBound) return;
            const arrivalCardEl = document.getElementById('arrival-card');
            if (!arrivalCardEl) return;
            arrivalCardEl.addEventListener('click', handlePoiActionClick);
            arrivalCardListenerBound = true;
        }

        // Tinitingnan kung nakarating na ba ang traveller sa aktibong stop. Kapag
        // oo, ipinapakita ang arrival card: Check In kung wala pang bukas na
        // visit doon, o live timer + Check Out kapag naka-check-in na.
        function checkArrival(uLat, uLon) {
            if (!isAuthenticated) return;
            const stop = tripStops[activeStopIndex];
            if (!stop) return;

            const distM = haversineKm(uLat, uLon, stop.lat, stop.lon) * 1000;
            if (distM > ARRIVAL_RADIUS_M) {
                // Umalis (o hindi pa nararating) — itago ang card kung ito ang
                // stop na kasalukuyang pinapakita, pero huwag galawin ang timer.
                if (arrivalCardShownFor === stop.name) hideArrivalCard();
                return;
            }

            showArrivalCard(stop, distM);
        }

        function showArrivalCard(stop, distM) {
            const card = document.getElementById('arrival-card');
            if (!card) return;

            const titleEl = document.getElementById('arrival-title');
            const progressEl = document.getElementById('arrival-progress');
            const detailsEl = document.getElementById('arrival-details');
            const actionsEl = document.getElementById('arrival-actions');

            if (titleEl) titleEl.textContent = `You've arrived at ${stop.name}`;
            if (progressEl) progressEl.textContent = `Stop ${activeStopIndex + 1} of ${tripStops.length}`;

            const since = activeVisits[stop.name];
            if (since) {
                // Naka-check-in na: live timer + Check Out.
                if (detailsEl) detailsEl.innerHTML = `Checked in — enjoying <strong>${escapeHtml(stop.name)}</strong>`;
                if (actionsEl) {
                    actionsEl.innerHTML =
                        `<span class="stay-duration" data-stay-since="${since}" style="font-family:'JetBrains Mono',monospace;font-size:0.8rem;font-weight:700;color:var(--deep-teal);">${formatStayDuration(since)}</span>`
                        + `<button type="button" data-poi-action="checkout" data-lat="${stop.lat}" data-lon="${stop.lon}" data-name="${escapeHtml(stop.name)}" class="focus-ring" style="background:var(--deep-teal);color:white;padding:0.45rem 0.85rem;border-radius:0.5rem;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;">Check Out</button>`;
                }
            } else {
                // Hindi pa naka-check-in: Check In (may distansya para sa konteksto).
                const distText = Number.isFinite(distM)
                    ? (distM < 1000 ? `${Math.round(distM)} m away` : `${(distM / 1000).toFixed(1)} km away`)
                    : '';
                if (detailsEl) {
                    detailsEl.innerHTML = `Dumating ka sa <strong>${escapeHtml(stop.name)}</strong>`
                        + (distText ? ` · <span style="font-family:'JetBrains Mono',monospace;">${distText}</span>` : '');
                }
                if (actionsEl) {
                    actionsEl.innerHTML =
                        `<button type="button" data-poi-action="checkin" data-lat="${stop.lat}" data-lon="${stop.lon}" data-name="${escapeHtml(stop.name)}" class="focus-ring" style="background:var(--ember);color:white;padding:0.45rem 0.85rem;border-radius:0.5rem;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;">Check In</button>`;
                }
            }

            card.classList.remove('hidden');
            arrivalCardShownFor = stop.name;
            startStayTimer(); // siguraduhing tumatakbo ang segundo-segundong update.
        }

        function hideArrivalCard() {
            const card = document.getElementById('arrival-card');
            if (card) card.classList.add('hidden');
            arrivalCardShownFor = null;
        }

        // I-re-render ang arrival card para sa kasalukuyang stop (hal. pagkatapos
        // mag-check in, lumipat mula Check In → live timer + Check Out).
        function refreshArrivalCard() {
            const stop = tripStops[activeStopIndex];
            if (!stop || arrivalCardShownFor !== stop.name) return;
            showArrivalCard(stop);
        }

        // Pagkatapos mag-check out sa aktibong stop: lumipat sa susunod at
        // awtomatikong i-route papunta roon. Sa huling stop, ibabalik sa hotel
        // (basecamp) at ire-reset ang pointer para gumana ulit ang arrival.
        function advanceToNextStop(justCheckedOutName) {
            hideArrivalCard();

            const current = tripStops[activeStopIndex];
            // Huwag galawin ang pointer kapag ang na-check out ay hindi ang
            // aktibong stop (hal. naka-check out sa ibang lugar mula sa popup).
            if (current && justCheckedOutName
                && normalizePlaceText(current.name) !== normalizePlaceText(justCheckedOutName)) {
                return;
            }

            if (activeStopIndex + 1 < tripStops.length) {
                activeStopIndex += 1;
                const next = tripStops[activeStopIndex];
                if (userLocation) routeFromUserToStop(userLocation.lat, userLocation.lon, next);
                showToast(`Tapos na sa <strong>${escapeHtml(justCheckedOutName)}</strong>. Auto-route papunta sa <strong>${escapeHtml(next.name)}</strong>.`, 'success', 5000);
            } else {
                // Tapos na ang buong itinerary — ibalik sa basecamp.
                activeStopIndex = 0;
                const home = tripStops[0];
                if (userLocation && home) routeFromUserToStop(userLocation.lat, userLocation.lon, home);
                showToast(`Tapos na ang buong itinerary! Auto-route pabalik sa <strong>${escapeHtml(hotelName)}</strong>.`, 'success', 6000);
            }
        }

        // Katulad ng routeFromUserToHotel pero papunta sa isang partikular na
        // stop (hindi lang sa hotel). Ginagamit ng guided flow.
        function routeFromUserToStop(fromLat, fromLon, stop, options = {}) {
            if (!map) return;
            const shouldFit = !options || options.fit !== false;

            if (currentRouteControl) {
                map.removeControl(currentRouteControl);
            }

            const card = document.getElementById('route-guidance-card');
            const detailsEl = document.getElementById('route-guidance-details');
            let routeResolved = false;

            const showFallbackEstimate = (reason) => {
                if (routeResolved) return;
                routeResolved = true;
                const km = haversineKm(fromLat, fromLon, stop.lat, stop.lon);
                const timeText = formatDriveTime((km / FALLBACK_SPEED_KMH) * 3600);
                if (card && detailsEl) {
                    card.classList.remove('hidden');
                    detailsEl.innerHTML = `<strong>${km.toFixed(1)} km</strong> straight-line (${reason}) · Estimated driving time <strong>~${timeText}</strong> to <em>${escapeHtml(stop.name)}</em>`;
                }
            };

            const fallbackTimer = setTimeout(() => showFallbackEstimate('road route unavailable'), 15000);

            currentRouteControl = L.Routing.control({
                waypoints: [
                    L.latLng(fromLat, fromLon),
                    L.latLng(stop.lat, stop.lon)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                show: false,
                createMarker: function() { return null; },
                lineOptions: {
                    styles: [
                        { color: '#0B3D3A', opacity: 0.9, weight: 6 },
                        { color: '#D9622B', opacity: 0.5, weight: 10 }
                    ],
                    addWaypoints: false
                }
            }).addTo(map);

            currentRouteControl.on('routesfound', function(e) {
                const routes = e.routes;
                if (routes && routes.length > 0) {
                    routeResolved = true;
                    clearTimeout(fallbackTimer);
                    const primary = routes[0];
                    const km = (primary.summary.totalDistance / 1000).toFixed(1);
                    const timeText = formatDriveTime(primary.summary.totalTime);
                    if (card && detailsEl) {
                        card.classList.remove('hidden');
                        detailsEl.innerHTML = `<strong>${km} km</strong> away · Estimated driving time <strong>~${timeText}</strong> to <em>${escapeHtml(stop.name)}</em>`;
                    }
                }
            });

            currentRouteControl.on('routingerror', function(err) {
                console.warn('Routing failed:', err && err.error);
                clearTimeout(fallbackTimer);
                showFallbackEstimate('road route unavailable');
            });

            if (shouldFit) {
                const bounds = L.latLngBounds([
                    [fromLat, fromLon],
                    [stop.lat, stop.lon]
                ]);
                map.fitBounds(bounds, { padding: [60, 60] });
            }
        }

        function onUserPosition(position) {
            if (!map || !markerLayers) return;

            const uLat = position.coords.latitude;
            const uLon = position.coords.longitude;
            userLocation = { lat: uLat, lon: uLon };

            upsertUserMarker(uLat, uLon, `<b>You are here</b><br><span style="font-size:0.78rem;color:var(--ink-soft);">Live GPS location</span>`);

            const now = Date.now();

            if (needsReroute(uLat, uLon, lastRoutedPoint, lastRoutedAt, now)) {
                lastRoutedPoint = { lat: uLat, lon: uLon };
                lastRoutedAt = now;
                // Guided flow: kung may naitayong stops, ang aktibong stop ang
                // target ng ruta (hotel kung stop #1 pa tayo). Kung wala pang
                // stops, manatili sa dating pag-route papunta sa hotel.
                const stop = tripStops[activeStopIndex];
                if (stop) {
                    routeFromUserToStop(uLat, uLon, stop, { fit: false });
                } else {
                    routeFromUserToHotel(uLat, uLon, { fit: false });
                }
            }

            // Arrival detection: kapag lumapit sa aktibong stop, lumabas ang
            // Check In / Check Out card (na may live timer).
            checkArrival(uLat, uLon);

            followUserOnMap(uLat, uLon);
        }

        function onUserPositionError(err) {
            console.log('Live tracking error:', err.message);

            // Kung wala pang nakuha kahit isang position, ipakita pa rin ang card
            // na may paliwanag imbes na tahimik na walang feedback.
            if (!lastRoutedPoint) {
                const card = document.getElementById('route-guidance-card');
                const detailsEl = document.getElementById('route-guidance-details');
                const liveBadge = document.getElementById('route-badge-live');

                if (card && detailsEl) {
                    card.classList.remove('hidden');
                    detailsEl.innerHTML = 'Location unavailable — allow location access to see the live route from your current position.';
                }
                if (liveBadge) liveBadge.classList.add('hidden');
            }

            stopUserTracking();
        }

        // Huwag i-fitBounds() kada update (lumalaban ito sa camera at nakakagulo);
        // i-pan lang kapag lumabas na ang pin sa nakikitang bahagi ng mapa.
        function followUserOnMap(lat, lon) {
            if (!map) return;
            if (!map.getBounds().contains([lat, lon])) {
                map.panTo([lat, lon], { animate: true, duration: 0.8 });
            }
        }

        function routeFromUserToHotel(fromLat, fromLon, options = {}) {
            // Ang re-route habang gumagalaw ay hindi na nagre-frame ng camera.
            const shouldFit = !options || options.fit !== false;

            if (currentRouteControl) {
                map.removeControl(currentRouteControl);
            }

            const card = document.getElementById('route-guidance-card');
            const detailsEl = document.getElementById('route-guidance-details');
            let routeResolved = false;

            // Kapag hindi umubra ang OSRM (network, rate limit, walang mahanap na
            // ruta), huwag iwanan ang card sa "Calculating..." — magpakita ng
            // straight-line na tantya para may magamit pa ring ETA ang traveller.
            const showFallbackEstimate = (reason) => {
                if (routeResolved) return;
                routeResolved = true;

                const km = haversineKm(fromLat, fromLon, selectedLat, selectedLon);
                const timeText = formatDriveTime((km / FALLBACK_SPEED_KMH) * 3600);

                if (card && detailsEl) {
                    card.classList.remove('hidden');
                    detailsEl.innerHTML = `<strong>${km.toFixed(1)} km</strong> straight-line (${reason}) · Estimated driving time <strong>~${timeText}</strong> to <em>${hotelName}</em>`;
                }
            };

            // Watchdog: kung hindi sumagot ang router sa loob ng 15s, fallback na.
            const fallbackTimer = setTimeout(() => showFallbackEstimate('road route unavailable'), 15000);

            currentRouteControl = L.Routing.control({
                waypoints: [
                    L.latLng(fromLat, fromLon),
                    L.latLng(selectedLat, selectedLon)
                ],
                routeWhileDragging: false,
                addWaypoints: false,
                show: false,
                createMarker: function() { return null; },
                lineOptions: {
                    styles: [
                        { color: '#0B3D3A', opacity: 0.9, weight: 6 },
                        { color: '#D9622B', opacity: 0.5, weight: 10 }
                    ],
                    addWaypoints: false
                }
            }).addTo(map);

            currentRouteControl.on('routesfound', function(e) {
                const routes = e.routes;
                if (routes && routes.length > 0) {
                    routeResolved = true;
                    clearTimeout(fallbackTimer);

                    const primary = routes[0];
                    const km = (primary.summary.totalDistance / 1000).toFixed(1);
                    const timeText = formatDriveTime(primary.summary.totalTime);

                    if (card && detailsEl) {
                        card.classList.remove('hidden');
                        detailsEl.innerHTML = `<strong>${km} km</strong> away · Estimated driving time <strong>~${timeText}</strong> to <em>${hotelName}</em>`;
                    }
                }
            });

            currentRouteControl.on('routingerror', function(err) {
                console.warn('Routing failed:', err && err.error);
                clearTimeout(fallbackTimer);
                showFallbackEstimate('road route unavailable');
            });

            if (shouldFit) {
                const bounds = L.latLngBounds([
                    [fromLat, fromLon],
                    [selectedLat, selectedLon]
                ]);
                map.fitBounds(bounds, { padding: [60, 60] });
            }
        }

        window.drawRouteTo = function(targetLat, targetLon) {
            if (currentRouteControl) {
                map.removeControl(currentRouteControl);
            }

            // Walang hiwalay na flyTo(): ang fitBounds() sa ibaba ay agad na
            // naka-o-override ng animation, kaya patay na code ito dati.
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

        // Delegated na click handler para sa mga button ng POI popup. Ang
        // `data-*` na paraan ang ginamit kaysa sa inline na `onclick` dahil
        // ang pangalan ay panlabas na datos — hindi ito ligtas na ilagay sa
        // loob ng JS string na attribute.
        function handlePoiActionClick(event) {
            const trigger = event.target.closest('[data-poi-action]');
            if (!trigger) return;

            const lat = parseFloat(trigger.dataset.lat);
            const lon = parseFloat(trigger.dataset.lon);
            if (!Number.isFinite(lat) || !Number.isFinite(lon)) return;

            if (trigger.dataset.poiAction === 'route') {
                drawRouteTo(lat, lon);
            } else if (trigger.dataset.poiAction === 'checkin') {
                checkIn(lat, lon, trigger.dataset.name || '');
            } else if (trigger.dataset.poiAction === 'checkout') {
                checkOut(lat, lon, trigger.dataset.name || '');
            }
        }

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
                // Ang haversineKm() ay module-level na helper na ngayon.
                Object.keys(categorized).forEach(type => {
                    const places = categorized[type];
                    if (!places || !Array.isArray(places)) return;
                    places.forEach((place, pIdx) => {
                        const placeLat = Number(place.lat);
                        const placeLon = Number(place.lon);
                        const distKm = haversineKm(selectedLat, selectedLon, placeLat, placeLon);
                        const distText = distKm < 1
                            ? `${Math.round(distKm * 1000)}m away`
                            : `${distKm.toFixed(1)}km away`;
                        nearbyPlacesData.push(`${place.name} (${iconMap[type].label}, ${distText})`);
                        const weatherElId = `weather-popup-${type}-${pIdx}`;
                        let popupHTML = `
                            <div style="min-width: 180px;">
                                <b style="font-size: 1rem; margin-bottom: 0.4rem; display: block;">${escapeHtml(place.name)}</b>
                                <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                                    <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${type === 'restaurant' ? '#C2410C' : type === 'mall' ? '#0E5F5A' : type === 'beach' ? '#0891B2' : '#2E7D32'}; box-shadow: 0 0 0 2px rgba(0,0,0,0.1);"></span>
                                    <span style="font-size: 0.8rem; color: var(--ink-soft);">${iconMap[type].label}</span>
                                    <span style="font-size: 0.75rem; color: var(--sage); margin-left: auto; font-family: 'JetBrains Mono', monospace;">${distText}</span>
                                </div>
                                <div id="${weatherElId}"></div>
                                <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                                    <button type="button" data-poi-action="route" data-lat="${placeLat}" data-lon="${placeLon}" style="flex: 1; min-width: 100px; background: var(--deep-teal); color: white; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: 'Plus Jakarta Sans', sans-serif;" onmouseover="this.style.background='var(--ember)'" onmouseout="this.style.background='var(--deep-teal)'">Show Route</button>
                                    ${isAuthenticated ? `<span data-visit-row data-lat="${placeLat}" data-lon="${placeLon}" data-name="${escapeHtml(place.name)}" style="display: inline-flex; align-items: center; gap: 0.4rem; flex: 1; min-width: 100px;"></span>` : ''}
                                </div>
                            </div>
                        `;
                        // Ang descriptor lang ang itinatabi dito. Marker lang ang
                        // idadagdag kapag binanggit na ito ng itinerary.
                        nearbyPlacesIndex.push({
                            name: place.name,
                            type: type,
                            iconLabel: iconMap[type].label[0],
                            iconClass: iconMap[type].cls,
                            lat: placeLat,
                            lon: placeLon,
                            popupHTML: popupHTML,
                            weatherElId: weatherElId
                        });
                    });
                });
            } catch (error) { console.error('Nearby places fetch error:', error); }
        }

        // —— Itinerary-driven na POI pins ——
        // Ang mga generic na salitang lumalabas sa halos lahat ng itinerary ay
        // hindi pwedeng maging batayan kung "sinuggest" ba ang isang lugar.
        const PLACE_STOPWORDS = new Set([
            'hotel', 'resort', 'restaurant', 'restaurants', 'mall', 'malls',
            'beach', 'beaches', 'tourist', 'spot', 'spots', 'city', 'dagupan',
            'pangasinan', 'barangay', 'inc', 'the', 'and', 'near', 'with'
        ]);

        // Case-, diacritic- at punctuation-insensitive na paghahambing, para
        // tumugma ang "SM Center Dagupan" sa itinerary text.
        function normalizePlaceText(value) {
            return String(value || '')
                .toLowerCase()
                .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
                .replace(/&/g, ' and ')
                .replace(/[^a-z0-9]+/g, ' ')
                .trim();
        }

        function itineraryMentionsPlace(name, normalizedItinerary) {
            const normalizedName = normalizePlaceText(name);
            if (normalizedName.length < 3) return false;

            // Kapag generic na salita lang ang buong pangalan (hal. "Beach
            // Resort"), hindi ito matutukoy — mas mabuting huwag mag-pin kaysa
            // magpakita ng lugar na hindi naman sinuggest.
            const tokens = normalizedName.split(' ')
                .filter(token => token.length >= 4 && !PLACE_STOPWORDS.has(token));

            if (tokens.length === 0) return false;

            // Buong pangalan ang nabanggit — ito ang pinaka-tiyak na tugma.
            if (normalizedItinerary.includes(normalizedName)) return true;

            // Kapag pinaikli o hinati-hati ng itinerary ang pangalan, sapat na
            // ang mga distinctive na salita (dalawa o higit pa, o isa kung isa
            // lang naman ito — hal. "Jollibee").
            const matched = tokens.filter(token => normalizedItinerary.includes(token)).length;

            return tokens.length === 1 ? matched === 1 : matched >= 2;
        }

        // Ang itinerary text (markdown + naka-render na DOM) ang source of truth,
        // kaya dito lang naglalagay ng POI markers imbes na sa
        // fetchNearbyAmenities() — kung ano lang ang sinuggest, iyon lang ang
        // lalabas sa mapa.
        //
        // Isang function para sa parehong tawag: noong hiwalay ang ginamit ng
        // renderSuggestedPlaces() (markdown + DOM) at ng renderPickedPlaces()
        // (markdown lang), isang lugar na nabanggit sa DOM pero hindi sa
        // markdown ay napipin TWICE.
        function currentItineraryText() {
            const outputEl = document.getElementById('ai-output');
            return normalizePlaceText(`${currentItineraryMarkdown} ${outputEl ? outputEl.textContent : ''}`);
        }

        // Mga kategoryang may pin sa mapa. Ibinabahagi ng dalawang renderer
        // dahil updateLegendVisibility() ay nagta-toggle ng buong legend sa
        // isang pagkakataon — kung magkaibang set ang bawat isa, ang huli na
        // nakatawag ang may hawak ng katotohanan, at nawawala ang legend ng
        // isang kategoryang may pin.
        let plottedMapTypes = new Set();
        // Shared dedupe registry: lahat ng na-plot na lugar (Overpass + curated)
        // ay iniimbak dito ng renderSuggestedPlaces(), at ginagamit ng
        // renderPickedPlaces() para hindi mag-doble ang pin.
        let plottedPlaceKeys = new Set();

        function renderSuggestedPlaces() {
            if (!markerLayers) return 0;

            // Bago ang anumang maagang return: kung hindi, may hinuling set ang
            // mga pinili sa susunod na renderPickedPlaces() at lilitaw ang
            // legend ng kategoryang walang pin.
            plottedMapTypes = new Set();
            plottedPlaceKeys = new Set();

            const itineraryText = currentItineraryText();
            if (itineraryText === '') return 0;

            // Ang basecamp ay may sariling 'H' pin na, kaya hindi na ito dapat
            // maging POI pin.
            const hotelKey = normalizePlaceText(hotelName);

            // Mga pinili ng traveller — ipapakita ng renderPickedPlaces() nang may
            // "Your pick" label, kaya laktawan dito para hindi mawalan niyon at
            // hindi doble.
            const pickedKeys = new Set(
                (Array.isArray(selectedPlaces) ? selectedPlaces : []).map(n => normalizePlaceText(n))
            );

            nearbyPlacesIndex.forEach(place => {
                const key = normalizePlaceText(place.name);
                if (plottedPlaceKeys.has(key)) return;
                if (hotelKey.length >= 3 && (key.includes(hotelKey) || hotelKey.includes(key))) return;
                if (!itineraryMentionsPlace(place.name, itineraryText)) return;

                plottedPlaceKeys.add(key);
                plottedMapTypes.add(place.type);

                const marker = L.marker([place.lat, place.lon], { icon: makeIcon(place.iconLabel, place.iconClass) })
                    .addTo(markerLayers).bindPopup(place.popupHTML);
                marker.on('popupopen', () => {
                    loadWeatherIntoPopup(place.lat, place.lon, place.weatherElId);
                    refreshVisitRowIn(marker);
                });
            });

            // Ang mga curated lugar (mula sa `locations` catalogue) na isinuggest
            // ng AI ay wala sa nearbyPlacesIndex (Overpass), kaya hindi sila
            // napipin ng loop sa itaas. Hanapin sila sa catalogue at i-pin din —
            // buong functionality tulad ng Overpass pin (weather, check-in,
            // route) — para hindi mukhang "wala sa mapa" ang mga sinuggest na
            // restaurant/mall/beach/tourist spot.
            allWithinBudgetPlaces().forEach(place => {
                const key = normalizePlaceText(place.name);
                if (plottedPlaceKeys.has(key)) return;
                if (pickedKeys.has(key)) return;
                if (hotelKey.length >= 3 && (key.includes(hotelKey) || hotelKey.includes(key))) return;
                if (!itineraryMentionsPlace(place.name, itineraryText)) return;

                const placeLat = Number(place.lat);
                const placeLon = Number(place.lon);
                if (!Number.isFinite(placeLat) || !Number.isFinite(placeLon)) return;

                plottedPlaceKeys.add(key);
                plottedMapTypes.add(place.category);

                const meta = PLACE_CATEGORIES.find(c => c.key === place.category) || PLACE_CATEGORIES[3];
                const weatherElId = `weather-popup-curated-${key.replace(/[^a-z0-9]+/g, '-')}`;
                const curatedPopup = `
                    <div style="min-width: 180px;">
                        <b style="font-size: 1rem; margin-bottom: 0.4rem; display: block;">${escapeHtml(place.name)}</b>
                        <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;">
                            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${meta.dot}; box-shadow: 0 0 0 2px rgba(0,0,0,0.1);"></span>
                            <span style="font-size: 0.8rem; color: var(--ink-soft);">${meta.label}</span>
                            <span style="font-size: 0.75rem; color: var(--sage); margin-left: auto; font-family: 'JetBrains Mono', monospace;">₱${formatPrice(place.budget_per_day)}/day</span>
                        </div>
                        <div id="${weatherElId}"></div>
                        <div style="display: flex; gap: 0.4rem; flex-wrap: wrap; align-items: center;">
                            <button type="button" data-poi-action="route" data-lat="${placeLat}" data-lon="${placeLon}" style="flex: 1; min-width: 100px; background: var(--deep-teal); color: white; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: 'Plus Jakarta Sans', sans-serif;" onmouseover="this.style.background='var(--ember)'" onmouseout="this.style.background='var(--deep-teal)'">Show Route</button>
                            ${isAuthenticated ? `<span data-visit-row data-lat="${placeLat}" data-lon="${placeLon}" data-name="${escapeHtml(place.name)}" style="display: inline-flex; align-items: center; gap: 0.4rem; flex: 1; min-width: 100px;"></span>` : ''}
                        </div>
                    </div>
                `;

                const marker = L.marker([placeLat, placeLon], { icon: makeIcon(meta.letter, meta.pin) })
                    .addTo(markerLayers).bindPopup(curatedPopup);
                marker.on('popupopen', () => {
                    loadWeatherIntoPopup(placeLat, placeLon, weatherElId);
                    refreshVisitRowIn(marker);
                });
            });

            updateLegendVisibility(plottedMapTypes);

            if (plottedPlaceKeys.size === 0 && nearbyPlacesIndex.length === 0) {
                showToast('Your itinerary did not name any nearby spot, so only the hotel and your location are pinned.', 'info', 5000);
            }

            return plottedPlaceKeys.size;
        }

        // Ang mga pinili ng traveller ay galing sa `locations` katalogo, hindi
        // sa Overpass — kaya wala sila sa nearbyPlacesIndex at hindi mapipin ng
        // renderSuggestedPlaces(). Sila mismo ang source of truth ng kanilang
        // presyo at coordinates, kaya safe nating ipin directly rito.
        function renderPickedPlaces(labels) {
            if (!markerLayers || !Array.isArray(labels) || labels.length === 0) return 0;

            // Parehong teksto ang ginagamit ng dalawang renderer. Kung magkaibang
            // pinagmulan noon (markdown + DOM dito, markdown lang doon), isang
            // lugar na nabanggit lamang sa DOM ay napipin nang dalawa.
            const itineraryText = currentItineraryText();
            // Ang renderSuggestedPlaces() ay nag-plot na ng lahat ng nabanggit na
            // Overpass at curated lugar at iniimbak sa plottedPlaceKeys. Gamitin
            // ito bilang dedupe dito — kung hindi, doble ang pin para sa isang
            // curated lugar na nabanggit ng itinerary at pinili rin ng traveller.
            const alreadyPlotted = plottedPlaceKeys;
            const hotelKey = normalizePlaceText(hotelName);
            let plotted = 0;

            labels.forEach(label => {
                // Ang server ay nagpapadala ng "Name (Category)"; ang katalogo
                // naman ay may bare name lang.
                const name = String(label).replace(/\s*\((Restaurant|Mall|Beach|Tourist Spot)\)\s*$/i, '').trim();
                const place = findPlace(name);
                if (!place) return;

                const key = normalizePlaceText(place.name);
                if (alreadyPlotted.has(key)) return;
                if (hotelKey.length >= 3 && (key.includes(hotelKey) || hotelKey.includes(key))) return;

                alreadyPlotted.add(key);
                plottedMapTypes.add(place.category);

                const meta = PLACE_CATEGORIES.find(c => c.key === place.category) || PLACE_CATEGORIES[3];
                L.marker([place.lat, place.lon], { icon: makeIcon(meta.letter, meta.pin) })
                    .addTo(markerLayers)
                    .bindPopup(`
                        <div style="min-width: 180px;">
                            <b style="font-size: 1rem; margin-bottom: 0.4rem; display: block;">${escapeHtml(place.name)}</b>
                            <div style="display: flex; align-items: center; gap: 0.4rem;">
                                <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background: ${meta.dot};"></span>
                                <span style="font-size: 0.8rem; color: var(--ink-soft);">${escapeHtml(meta.label)}</span>
                                <span style="font-size: 0.75rem; margin-left: auto; font-family: 'JetBrains Mono', monospace;">₱${formatPrice(place.budget_per_day)}/day</span>
                            </div>
                            <div style="font-size: 0.72rem; color: var(--sage); margin-top: 0.5rem; font-family: 'JetBrains Mono', monospace;">Your pick</div>
                        </div>
                    `);

                plotted++;
            });

            // REGRESSION FIX: kailan itong tawag. Noon, ang legend ay ina-update
            // ng renderSuggestedPlaces() bago pa man magkaroon ng pin ang mga
            // pinili — kaya kung beach ang napili pero walang beach na binanggit
            // ng itinerary, may beach na pin sa mapa nang walang legend.
            updateLegendVisibility(plottedMapTypes);

            return plotted;
        }

        // Hindi maintindihan ng traveller ang legend entry na walang pin sa mapa.
        function updateLegendVisibility(plottedTypes) {
            ['restaurant', 'mall', 'tourist', 'beach'].forEach(type => {
                const entry = document.querySelector(`[data-legend="${type}"]`);
                if (!entry) return;
                // Inline style ang kailangan: ang `.map-legend span` na
                // `display: inline-flex` ay mas mataas ang specificity sa
                // Tailwind na `.hidden`.
                entry.style.display = plottedTypes.has(type) ? '' : 'none';
            });
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
            document.getElementById('modal-address-text').innerText = hotel.address || 'Dagupan City, Pangasinan — view on map';
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

        // Step 03 photo viewer: buong image (object-contain, hindi crop) na may
        // caption. Hiwalay sa hotel-modal para walang ID/listener conflict.
        let placePhotoReturnFocus = null;
        // Leaflet instance ng magnify modal. Sinisira tuwing isasara o bago
        // buksan ang bagong lugar para walang nai-iwan na marker/tile layer.
        let placePhotoMap = null;

        function destroyPlacePhotoMap() {
            if (placePhotoMap) { placePhotoMap.remove(); placePhotoMap = null; }
        }

        function openPlacePhoto(name, trigger) {
            const place = findPlace(name);
            if (!place || !place.image_url) return;
            placePhotoReturnFocus = trigger || null;
            const modal = document.getElementById('place-photo-modal');
            const modalContent = document.getElementById('place-photo-modal-content');
            if (!modal || !modalContent) return;
            const img = document.getElementById('place-photo-img');
            img.src = place.image_url;
            img.alt = place.name;
            document.getElementById('place-photo-title').innerText = place.name;
            const meta = PLACE_CATEGORIES.find(c => c.key === place.category);
            const rating = Number(place.rating);
            document.getElementById('place-photo-meta').innerText =
                `${meta ? meta.label : place.category} · ★ ${Number.isFinite(rating) ? rating.toFixed(1) : '—'} · ₱${formatPrice(place.budget_per_day)}/day`;
            document.getElementById('place-photo-desc').innerText = place.description || '';

            // Lokasyon sa mapa (tulad ng landing gallery): marker ng lugar gamit
            // ang parehong lat/lon ng Step 04. Kapag walang koordinado, naka-hide
            // ang mapa at foto + detalye lang ang nakikita.
            const mapWrap = document.getElementById('place-photo-map-wrap');
            destroyPlacePhotoMap();
            const lat = Number(place.lat);
            const lon = Number(place.lon);
            const hasCoords = Number.isFinite(lat) && Number.isFinite(lon) && typeof L !== 'undefined';
            if (mapWrap) mapWrap.classList.toggle('hidden', !hasCoords);
            if (hasCoords && mapWrap) {
                placePhotoMap = L.map('place-photo-map').setView([lat, lon], 16);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                    maxZoom: 18
                }).addTo(placePhotoMap);
                // Pangalan lang sa popup (textContent, hindi HTML) — hindi ito
                // puwedeng maging injection kahit anong pangalan ang nasa DB.
                const popupTitle = document.createElement('b');
                popupTitle.textContent = place.name;
                L.marker([lat, lon]).addTo(placePhotoMap).bindPopup(popupTitle).openPopup();
            }

            document.body.style.overflow = 'hidden';
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                modalContent.classList.remove('scale-95');
                modal.querySelector('button[aria-label="Close photo viewer"]')?.focus();
                // Ang container ay naka-hidden hanggang buksan ang modal, kaya
                // kailangan ng invalidateSize() para gumana ang tile layer.
                if (placePhotoMap) placePhotoMap.invalidateSize();
            }, 10);
        }

        function closePlacePhoto() {
            const modal = document.getElementById('place-photo-modal');
            const modalContent = document.getElementById('place-photo-modal-content');
            if (!modal || !modalContent) return;
            destroyPlacePhotoMap();
            modal.classList.add('opacity-0');
            modalContent.classList.add('scale-95');
            document.body.style.overflow = '';
            setTimeout(() => {
                modal.classList.add('hidden');
                placePhotoReturnFocus?.focus();
                placePhotoReturnFocus = null;
            }, 300);
        }

        document.getElementById('place-photo-modal').addEventListener('click', function(e) {
            if (e.target === this) closePlacePhoto();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key !== 'Escape') return;
            const photoModal = document.getElementById('place-photo-modal');
            if (photoModal && !photoModal.classList.contains('hidden')) {
                closePlacePhoto();
                return;
            }
            const hotelModal = document.getElementById('hotel-modal');
            if (hotelModal && !hotelModal.classList.contains('hidden')) closeModal();
        });

        async function checkIn(lat, lon, name) {
            try {
                const res = await fetch('/api/visit-log/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ spot_id: await getSpotIdByName(name), spot_name: name })
                });
                const data = await res.json();
                if (res.ok) {
                    activeVisits[name] = new Date().toISOString();
                    refreshVisitRows();
                    // Kapag ang na-check in ay ang aktibong stop ng guided trip,
                    // i-re-render ang arrival card para lumabas ang live timer +
                    // Check Out (imbes na Check In).
                    refreshArrivalCard();
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
                    body: JSON.stringify({ spot_id: await getSpotIdByName(name), spot_name: name })
                });
                const data = await res.json();
                if (res.ok) {
                    const mins = data.duration_minutes;
                    delete activeVisits[name];
                    refreshVisitRows();
                    showToast(`Checked out of ${name}. Stayed ${mins} min.`, 'success', 4000);
                    // Guided flow: pagkatapos mag-check out sa aktibong stop,
                    // awtomatikong lumipat sa susunod at i-route papunta roon.
                    advanceToNextStop(name);
                } else {
                    showToast(data.error || 'Check-out failed', 'error');
                }
            } catch (err) {
                showToast('Check-out failed. Please try again.', 'error');
            }
        }

        async function getSpotIdByName(name) {
            try {
                const res = await fetch(`/api/tourist-spots?q=${encodeURIComponent(name)}`);
                if (!res.ok) return null;
                const spots = await res.json();
                // Null is a valid answer for Overpass-sourced places: the visit
                // is still recorded, just without a curated location_id.
                return spots.length > 0 ? spots[0].id : null;
            } catch (err) {
                return null;
            }
        }

        // —— Stay duration tracker ——
        // Ang bukas na pag-check-in ay mula sa /api/visit-log/active. Ginagamit ng
        // POI popup para magpakita ng "Check Out" (kasama ang live na tagal ng
        // pananatili) imbes na "Check In" kapag naka-check-in na ang user.

        async function loadActiveVisits() {
            if (!isAuthenticated) return;
            try {
                const res = await fetch('/api/visit-log/active', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const data = await res.json();
                activeVisits = {};
                (data.visits || []).forEach(v => {
                    if (v.location_name && v.checked_in_at) activeVisits[v.location_name] = v.checked_in_at;
                });
                refreshVisitRows();
            } catch (err) {
                // Non-fatal: gumagana pa rin ang check-in, kulang lang ang tracker.
            }
        }

        function formatStayDuration(iso) {
            const start = new Date(iso).getTime();
            if (!isFinite(start)) return '';
            const totalSec = Math.max(0, Math.floor((Date.now() - start) / 1000));
            const h = Math.floor(totalSec / 3600);
            const m = Math.floor((totalSec % 3600) / 60);
            const s = totalSec % 60;
            const pad = n => String(n).padStart(2, '0');
            if (h > 0) return `${h}h ${pad(m)}m ${pad(s)}s`;
            if (m > 0) return `${m}m ${pad(s)}s`;
            return `${s}s`;
        }

        // Inner content ng isang visit row: Check In O Check Out na button (kasama
        // ang live timer kapag naka-check-in). Ang wrapper na <span data-visit-row>
        // ay nasa popup template; dito lang ang papalitan tuwing refresh.
        function visitRowHtml(name, lat, lon) {
            if (!isAuthenticated) return '';
            const coords = `data-lat="${lat}" data-lon="${lon}" data-name="${escapeHtml(name)}"`;
            const base = 'flex: 1; min-width: 100px; color: white; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; transition: all 0.2s; font-family: \'Plus Jakarta Sans\', sans-serif;';
            const since = activeVisits[name];
            if (since) {
                return `<button type="button" data-poi-action="checkout" ${coords} style="${base} background: var(--deep-teal);" onmouseover="this.style.background='#09403c'" onmouseout="this.style.background='var(--deep-teal)'">Check Out</button>`
                    + `<span class="stay-duration" data-stay-since="${since}" style="font-size: 0.68rem; color: var(--sage); font-family: 'JetBrains Mono', monospace; text-align: center;">${formatStayDuration(since)}</span>`;
            }
            return `<button type="button" data-poi-action="checkin" ${coords} style="${base} background: var(--ember);" onmouseover="this.style.background='var(--ember-deep)'" onmouseout="this.style.background='var(--ember)'">Check In</button>`;
        }

        // I-refresh ang lahat ng nakabukas na visit row sa DOM (hal. pagkatapos ng
        // check-in/out) nang hindi nire-render ang buong marker.
        function refreshVisitRows() {
            document.querySelectorAll('[data-visit-row]').forEach(row => {
                const name = row.dataset.name || '';
                const lat = row.dataset.lat;
                const lon = row.dataset.lon;
                row.innerHTML = visitRowHtml(name, lat, lon);
            });
        }

        // I-refresh ang visit row sa loob ng isang marker popup kapag binuksan, para
        // laging tama ang button kahit naunang naka-build ang popup.
        function refreshVisitRowIn(marker) {
            const popup = marker.getPopup && marker.getPopup();
            const el = popup && popup.getElement ? popup.getElement() : null;
            const row = el && el.querySelector('[data-visit-row]');
            if (!row) return;
            const name = row.dataset.name || '';
            const lat = row.dataset.lat;
            const lon = row.dataset.lon;
            row.innerHTML = visitRowHtml(name, lat, lon);
        }

        let stayTimerStarted = false;
        function startStayTimer() {
            if (stayTimerStarted) return;
            stayTimerStarted = true;
            setInterval(() => {
                document.querySelectorAll('[data-stay-since]').forEach(el => {
                    const since = el.getAttribute('data-stay-since');
                    if (since) el.textContent = formatStayDuration(since);
                });
            }, 1000);
        }

        // —— Hotel Search ——
        const hotelSearchInput = document.getElementById('hotel-search-input');
        const clearSearchBtn = document.getElementById('clear-search');
        const searchStatus = document.getElementById('search-status');
        const hotelList = document.getElementById('hotel-list');

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
                    const stars = starsFromRating(hotel.rating);
                    const ratingText = Number(hotel.rating).toFixed(1) + ' / 10';
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

        // Iisang listener lang para sa input: itinatago ang clear button at
        // hinahanap ang hotel. Dalawang listener dito dati, kaya hindi
        // palaging magkatugma ang estado ng clear button at ng listahan.
        hotelSearchInput?.addEventListener('input', (e) => {
            const query = e.target.value.trim();
            clearSearchBtn?.classList.toggle('hidden', query.length === 0);

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
                    // Iisang pin lang: kapag may na-detect nang location ang auto-route,
                    // i-move lang ito imbes na gumawa ng pangalawang "U" pin.
                    upsertUserMarker(userLat, userLon, '<b>Your Location</b><br><span style="color:var(--sage);font-size:0.85rem;">You are here</span><div id="weather-user-location"></div>');

                    // Naka-attach ang weather loader bago i-open ang popup para
                    // agad mag-load ang weather (hindi na kailangang mag-close/open).
                    userMarker.off('popupopen');
                    userMarker.on('popupopen', () => {
                        loadWeatherIntoPopup(userLat, userLon, 'weather-user-location');
                    });
                    userMarker.openPopup();

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

        // Sa mobile, humihinto ang GPS updates kapag naka-background ang tab o
        // naka-lock ang screen — kaya itinitigil natin ang watch at binubuksan
        // ulit kapag bumalik ang traveller sa Step 4 (ang step ng mapa).
        document.addEventListener('visibilitychange', () => {
            const itineraryStep = document.getElementById('step-4');
            const onItineraryStep = !!itineraryStep && itineraryStep.classList.contains('active');

            if (document.hidden) {
                // Pansamantala lang ito — panatilihin ang kagustuhan para
                // mag-rerestart ang watch pagbalik ng traveller.
                stopUserTracking({ keepIntent: true });
            } else if (onItineraryStep && trackingWanted) {
                startUserTracking();
            }
        });

        // Pag-alis sa page (o reload) ay awtomatikong pinuputol ang watch.
        window.addEventListener('beforeunload', stopUserTracking);
    </script>
</body>
</html>
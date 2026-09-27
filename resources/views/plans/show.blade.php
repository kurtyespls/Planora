<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Planora — {{ $plan->hotel_name }} itinerary</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @if($hotel && $hotel->lat !== null && $hotel->lon !== null)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3/dist/purify.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .top-nav .brand { font-size: 1.1rem; gap: 8px; }
        .top-nav .brand-mark { width: 32px; height: 32px; font-size: 0.9rem; }
        .top-nav-link {
            display: flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 999px;
            font-size: 0.8rem; font-weight: 600; color: var(--ink); text-decoration: none;
            background: transparent; border: none; cursor: pointer; transition: all 0.2s ease;
        }
        .top-nav-link:hover { background: var(--sand-deep); color: var(--deep-teal); }

        .plan-card {
            background: var(--card); border: 1px solid var(--line);
            border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); overflow: hidden;
        }

        .plan-meta-row {
            display: flex; flex-wrap: wrap; align-items: center;
            gap: 0.5rem 1.25rem; font-size: 0.8rem; color: var(--ink-soft);
        }

        .markdown-body ul { list-style: none; padding-left: 0; margin-bottom: 0.25rem; }
        .markdown-body li { margin-bottom: 0.55rem; padding-left: 1.5rem; position: relative; line-height: 1.55; color: var(--ink-soft); }
        .markdown-body li::before { content: "→"; position: absolute; left: 0; color: var(--ember); font-weight: 700; }
        .markdown-body li strong { color: var(--ink); }
        .markdown-body p { margin-bottom: 0.75rem; line-height: 1.6; color: var(--ink-soft); }

        .itinerary-day { background: var(--card); border: 1px solid var(--line); border-radius: 0.9rem; margin-bottom: 0.75rem; overflow: hidden; transition: all 0.3s ease; }
        .itinerary-day:hover { border-color: var(--ember); box-shadow: 0 4px 12px rgba(11,61,58,0.08); }
        .itinerary-day summary {
            list-style: none; cursor: pointer; user-select: none; display: flex; align-items: center;
            justify-content: space-between; gap: 0.75rem; padding: 0.9rem 1.1rem;
            background: var(--sand-deep); font-family: 'DM Serif Display', serif; font-weight: 600;
            color: var(--deep-teal); transition: background 0.2s ease;
        }
        .itinerary-day summary:hover { background: var(--line); }
        .itinerary-day summary::-webkit-details-marker { display: none; }
        .itinerary-day .chevron { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--ember); transition: transform 0.25s ease; flex-shrink: 0; }
        .itinerary-day[open] .chevron { transform: rotate(180deg); }
        .itinerary-day .day-body { padding: 1rem 1.1rem 1.1rem; }

        #plan-map { height: 320px; border: 1px solid var(--line); border-radius: 0.9rem; }

        .map-pin {
            display: flex; align-items: center; justify-content: center; width: 38px; height: 38px;
            border-radius: 999px; border: 2.5px solid var(--card); box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35); font-size: 1.15rem; line-height: 1;
            transition: transform 0.2s ease;
        }
        #plan-map .map-pin.pin-hotel { background: var(--deep-teal); }
        .map-pin.pin-hotel-pulse {
            animation: hotelPulse 2s ease-in-out infinite;
        }
        @keyframes hotelPulse {
            0%, 100% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 0 rgba(11,61,58,0.7); }
            50% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 12px rgba(11,61,58,0); }
        }
        .map-pin.pin-user { background: var(--ember); }
        .map-pin.pin-user-pulse {
            animation: userPulse 2s ease-in-out infinite;
        }
        @keyframes userPulse {
            0%, 100% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 0 rgba(217,98,43,0.8); }
            50% { box-shadow: 0 3px 10px -2px rgba(0,0,0,0.35), 0 0 0 14px rgba(217,98,43,0); }
        }
    </style>
</head>
<body class="min-h-screen px-4 pb-8 md:px-8">

    <!-- Top Navigation Bar -->
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand"><span class="brand-mark">⌁<img src="/images/planora-logo-sm.png" alt="" class="brand-logo" onerror="this.remove()"></span><span>planora</span></a>
            <div class="flex items-center gap-3">
                <a href="/profile/{{ auth()->id() }}?tab=plans" class="top-nav-link">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    My Plans
                </a>
                <form method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="top-nav-link">Log out</button>
                </form>
            </div>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto mt-8">
        @if(session('success'))
        <div class="mb-5 p-4 rounded-xl flex items-center gap-2" style="background:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;font-size:0.85rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01 5 7.01"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-5 p-4 rounded-xl flex items-center gap-2" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.85rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-5 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.85rem;">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
        @endif

        <div class="plan-card p-6 sm:p-8 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                <div class="min-w-0 flex-1" x-data="{ renaming: false, draft: @js($plan->title ?? '') }">
                    <p class="font-mono text-[0.65rem] uppercase tracking-widest text-[var(--sage)] mb-1">Saved itinerary</p>
                    <div class="flex items-center gap-2" x-show="!renaming">
                        <h1 class="font-display text-2xl sm:text-3xl font-semibold text-[var(--ink)] leading-tight">{{ $plan->display_title }}</h1>
                        <button type="button" @click="renaming = true" class="text-xs font-semibold text-[var(--sage)] hover:text-[var(--deep-teal)] underline whitespace-nowrap">Rename</button>
                    </div>
                    @if($plan->title)
                    <p class="text-sm text-[var(--ink-soft)] mt-1">Basecamp: {{ $plan->hotel_name }}</p>
                    @endif
                    <form x-show="renaming" x-cloak action="/plans/{{ $plan->id }}" method="POST" class="flex flex-wrap items-center gap-2 mt-2">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="return" value="plan">
                        <input type="text" name="title" x-model="draft" maxlength="120" placeholder="{{ $plan->hotel_name }}" class="field py-2 text-sm" style="max-width:22rem;">
                        <button type="submit" class="btn-primary px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap" style="width:auto;">Save</button>
                        <button type="button" @click="renaming = false" class="btn-ghost px-3 py-2 rounded-lg text-xs font-semibold whitespace-nowrap">Cancel</button>
                    </form>
                </div>
                <span class="badge {{ $plan->ai_provider === 'groq' ? 'badge-admin' : 'badge-user' }}">
                    {{ $plan->ai_provider === 'groq' ? 'AI generated' : 'Offline generator' }}
                </span>
            </div>

            <div class="plan-meta-row mb-5">
                <span class="flex items-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span class="font-mono font-semibold">{{ $plan->total_days }} day{{ $plan->total_days !== 1 ? 's' : '' }}</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="font-mono text-xs font-bold text-[var(--deep-teal)]" aria-hidden="true">PHP</span>
                    <span class="font-mono font-semibold">{{ number_format($plan->budget) }}</span>
                </span>
                <span class="flex items-center gap-1.5">Saved {{ $plan->created_at->format('M d, Y') }}</span>
            </div>

            @if(!empty($plan->rest_schedule_labels))
            <div class="mb-5">
                <p class="font-mono text-[0.62rem] uppercase tracking-wider text-[var(--sage)] mb-2">Rest schedule (daily)</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($plan->rest_schedule_labels as $restLabel)
                    <span class="badge badge-user">{{ $restLabel }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                <a href="/planora" class="btn-primary px-5 py-3 rounded-xl text-sm font-semibold text-center" style="width:auto;">Plan another trip</a>
                <form action="/plans/{{ $plan->id }}/regenerate" method="POST">
                    @csrf
                    <button type="submit" class="btn-secondary px-5 py-3 rounded-xl text-sm font-semibold" @disabled(!$aiEnabled) title="{{ $aiEnabled ? 'Build a fresh version of this itinerary' : 'Add a GROQ_API_KEY to enable AI regeneration' }}">Regenerate itinerary</button>
                </form>
                <form action="/plans/{{ $plan->id }}" method="POST" onsubmit="return confirm('Delete this plan? This cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger px-5 py-3 rounded-xl text-sm font-semibold">Delete this plan</button>
                </form>
            </div>
        </div>

        @if($hotel && $hotel->lat !== null && $hotel->lon !== null)
        <div class="plan-card p-4 sm:p-5 mb-6">
            <div class="flex items-center justify-between gap-3 mb-3 flex-wrap">
                <h2 class="font-display text-lg font-semibold text-[var(--ink)]">Basecamp</h2>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="detectLocationAndRoute()" id="btn-detect-route-show" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-[var(--line)] hover:bg-[var(--sand-deep)] text-[var(--deep-teal)] transition flex items-center gap-1.5">
                        <span>📍 Route from my location</span>
                    </button>
                    <a id="show-google-maps-link" href="https://www.google.com/maps/dir/?api=1&destination={{ (float) $hotel->lat }},{{ (float) $hotel->lon }}" target="_blank" rel="noopener noreferrer" class="btn-primary text-xs font-semibold px-3 py-1.5 rounded-lg" style="width:auto;">
                        Navigate ↗
                    </a>
                </div>
            </div>

            <div id="show-route-info" class="hidden mb-3 p-3 rounded-lg border border-[var(--line)] bg-[var(--sand-deep)] text-xs text-[var(--ink)] font-mono">
                Calculating route…
            </div>

            <div id="plan-map"
                 data-lat="{{ (float) $hotel->lat }}"
                 data-lon="{{ (float) $hotel->lon }}"
                 data-name="{{ $hotel->name }}"></div>
            <p class="mt-3 text-sm text-[var(--ink-soft)]">{{ $hotel->address ?: 'Dagupan City, Pangasinan' }}</p>
        </div>
        @endif

        <div class="plan-card p-6 sm:p-8">
            <div class="flex items-center justify-between gap-4 mb-4 flex-wrap">
                <h2 class="font-display text-xl font-semibold text-[var(--ink)]">Your itinerary</h2>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleAllItineraryDays()" id="btn-toggle-days" class="text-xs font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition">
                        Collapse all
                    </button>
                    <span class="text-[var(--line)]">·</span>
                    <button type="button" onclick="copyItinerary()" id="btn-copy-itinerary" class="text-xs font-semibold text-[var(--deep-teal)] hover:text-[var(--ember)] transition inline-flex items-center gap-1">
                        <span id="copy-label">Copy itinerary</span>
                    </button>
                </div>
            </div>
            <div id="ai-output"></div>
            <p id="plan-empty" class="hidden text-sm text-[var(--ink-soft)]">
                This plan has no saved itinerary text. Generate a new plan to get a day-by-day schedule.
            </p>
        </div>
    </main>

    <footer class="text-center mt-8 font-mono text-[0.62rem] uppercase tracking-widest text-[var(--sage)]">
        Planora · Itinerary Systems Desk · Dagupan City
    </footer>

    <script type="application/json" id="plan-markdown">@json($plan->ai_recommendation ?? '')</script>

    <script>
        const planMarkdown = JSON.parse(document.getElementById('plan-markdown').textContent || '""');

        // DOMPurify is progressive enhancement: if the CDN is unreachable the
        // page still renders, matching the planner page's behaviour.
        function sanitize(html) {
            return window.DOMPurify ? DOMPurify.sanitize(html) : html;
        }

        function renderItinerary(markdown) {
            const container = document.getElementById('ai-output');

            if (!markdown || !markdown.trim()) {
                document.getElementById('plan-empty').classList.remove('hidden');
                return;
            }

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
                bodyDiv.innerHTML = sanitize(marked.parse(body));
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
            if (!planMarkdown) return;
            const label = document.getElementById('copy-label');
            const done = () => {
                if (label) {
                    const prev = label.innerText;
                    label.innerText = 'Copied!';
                    setTimeout(() => { label.innerText = prev; }, 2000);
                }
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(planMarkdown).then(done).catch(() => fallbackCopy(planMarkdown, done));
            } else {
                fallbackCopy(planMarkdown, done);
            }
        }

        function fallbackCopy(text, callback) {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                if (callback) callback();
            } catch (e) {}
            document.body.removeChild(ta);
        }

        renderItinerary(planMarkdown);

        const mapEl = document.getElementById('plan-map');
        let showMapInstance = null;
        let showRouteControl = null;
        let showUserMarker = null;

        if (mapEl && window.L) {
            const lat = parseFloat(mapEl.dataset.lat);
            const lon = parseFloat(mapEl.dataset.lon);
            const hotelName = mapEl.dataset.name;

            if (!isNaN(lat) && !isNaN(lon)) {
                showMapInstance = L.map('plan-map', { scrollWheelZoom: false }).setView([lat, lon], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(showMapInstance);
                const hotelIcon = L.divIcon({
                    className: '',
                    html: `<div class="map-pin pin-hotel pin-hotel-pulse" title="Your Basecamp">
                        <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:white;display:block;filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
                            <path d="M7 13c1.66 0 3-1.34 3-3S8.66 7 7 7s-3 1.34-3 3 1.34 3 3 3zm12-6h-8v7H3V5H1v15h2v-3h18v3h2v-9c0-2.21-1.79-4-4-4z"/>
                        </svg>
                    </div>`,
                    iconSize: [38, 38],
                    iconAnchor: [19, 19],
                    popupAnchor: [0, -19]
                });
                L.marker([lat, lon], { icon: hotelIcon })
                    .addTo(showMapInstance)
                    .bindPopup(`<b>${hotelName}</b><br><span style="font-size:0.8rem;">Your basecamp</span>`)
                    .openPopup();
            }
        }

        // Fallback lang ito kapag hindi maabot ang OSRM router (network / rate limit).
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

        function describeFallbackRoute(uLat, uLon, hLat, hLon, hotelName) {
            const km = haversineKm(uLat, uLon, hLat, hLon);
            const mins = Math.round((km / FALLBACK_SPEED_KMH) * 60);
            const duration = mins >= 60 ? `${Math.floor(mins / 60)} hr ${mins % 60} mins` : `${mins} mins`;
            return `Road route unavailable · straight-line <strong>${km.toFixed(1)} km</strong> · est. <strong>~${duration}</strong> to ${hotelName}`;
        }

        function detectLocationAndRoute() {
            if (!navigator.geolocation || !showMapInstance || !mapEl) {
                alert('Geolocation is not supported by your browser.');
                return;
            }

            const hLat = parseFloat(mapEl.dataset.lat);
            const hLon = parseFloat(mapEl.dataset.lon);
            const hotelName = mapEl.dataset.name;
            const infoEl = document.getElementById('show-route-info');
            const navLink = document.getElementById('show-google-maps-link');

            if (infoEl) {
                infoEl.classList.remove('hidden');
                infoEl.innerText = 'Detecting your GPS location…';
            }

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const uLat = pos.coords.latitude;
                    const uLon = pos.coords.longitude;

                    if (navLink) {
                        navLink.href = `https://www.google.com/maps/dir/?api=1&origin=${uLat},${uLon}&destination=${hLat},${hLon}`;
                    }

                    const userIcon = L.divIcon({
                        className: '',
                        html: `<div class="map-pin pin-user pin-user-pulse" title="Your GPS Location">
                            <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:white;display:block;transform:rotate(-45deg);filter:drop-shadow(0 1px 2px rgba(0,0,0,0.25));">
                                <path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71z"/>
                            </svg>
                        </div>`,
                        iconSize: [38, 38],
                        iconAnchor: [19, 19],
                        popupAnchor: [0, -19]
                    });

                    if (showUserMarker) {
                        showUserMarker.setLatLng([uLat, uLon]);
                    } else {
                        showUserMarker = L.marker([uLat, uLon], { icon: userIcon })
                            .addTo(showMapInstance)
                            .bindPopup('<b>You are here</b><br>Detected GPS Location');
                    }

                    if (showRouteControl) {
                        showMapInstance.removeControl(showRouteControl);
                    }

                    if (window.L && L.Routing) {
                        // Watchdog: kung walang sagot ang router sa loob ng 15s,
                        // straight-line na tantya na ang ipakita.
                        const fallbackTimer = setTimeout(() => {
                            if (infoEl) {
                                infoEl.innerHTML = describeFallbackRoute(uLat, uLon, hLat, hLon, hotelName);
                            }
                        }, 15000);

                        showRouteControl = L.Routing.control({
                            waypoints: [L.latLng(uLat, uLon), L.latLng(hLat, hLon)],
                            routeWhileDragging: false,
                            addWaypoints: false,
                            show: false,
                            createMarker: () => null,
                            lineOptions: {
                                styles: [
                                    { color: '#0B3D3A', opacity: 0.9, weight: 6 },
                                    { color: '#D9622B', opacity: 0.5, weight: 10 }
                                ],
                                addWaypoints: false
                            }
                        }).addTo(showMapInstance);

                        showRouteControl.on('routesfound', (e) => {
                            const r = e.routes && e.routes[0];
                            if (r && infoEl) {
                                clearTimeout(fallbackTimer);
                                const km = (r.summary.totalDistance / 1000).toFixed(1);
                                const totalSec = r.summary.totalTime;
                                const hrs = Math.floor(totalSec / 3600);
                                const mins = Math.round((totalSec % 3600) / 60);
                                const duration = hrs > 0 ? `${hrs} hr ${mins} mins` : `${mins} mins`;
                                infoEl.innerHTML = `Route found: <strong>${km} km</strong> · Approx driving time <strong>~${duration}</strong> to ${hotelName}`;
                            }
                        });

                        showRouteControl.on('routingerror', (err) => {
                            console.warn('Routing failed:', err && err.error);
                            clearTimeout(fallbackTimer);
                            if (infoEl) {
                                infoEl.innerHTML = describeFallbackRoute(uLat, uLon, hLat, hLon, hotelName);
                            }
                        });
                    }

                    const bounds = L.latLngBounds([[uLat, uLon], [hLat, hLon]]);
                    showMapInstance.fitBounds(bounds, { padding: [50, 50] });
                },
                (err) => {
                    if (infoEl) {
                        infoEl.innerHTML = `Could not get your location (${err.message}). You can still tap <strong>Navigate ↗</strong> to open Google Maps.`;
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 }
            );
        }
    </script>
</body>
</html>


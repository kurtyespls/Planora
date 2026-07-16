<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Planora Admin — Hotel Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        /* Enhanced Admin Panel Design */
        :root {
            --sidebar-width: 260px;
        }
        
        body {
            background-color: var(--sand);
            background-image: 
                radial-gradient(circle at 8% 12%, rgba(11,61,58,0.04) 0%, transparent 45%),
                radial-gradient(circle at 95% 90%, rgba(217,98,43,0.03) 0%, transparent 40%);
        }
        
        /* Sidebar Enhancements */
        .sidebar {
            background: linear-gradient(180deg, var(--deep-teal) 0%, var(--deep-teal-ink) 100%);
            box-shadow: 2px 0 16px rgba(11,61,58,0.08);
        }
        
        .sidebar-link {
            position: relative;
            color: rgba(246, 237, 224, 0.65);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: var(--radius-sm);
            padding: 0.625rem 0.875rem;
        }
        
        .sidebar-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            background: var(--sand);
            border-radius: 0 2px 2px 0;
            transition: height 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .sidebar-link:hover,
        .sidebar-link.active {
            color: var(--sand);
            background: rgba(246, 237, 224, 0.1);
            transform: translateX(4px);
        }
        
        .sidebar-link:hover::before,
        .sidebar-link.active::before {
            height: 60%;
        }
        
        /* Table Enhancements */
        .hotel-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .hotel-table thead {
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .hotel-table thead th {
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--sage);
            font-weight: 600;
            background: var(--sand-deep);
            border-bottom: 1px solid var(--line);
            padding: 1rem 1.25rem;
            text-align: left;
            white-space: nowrap;
        }
        
        .hotel-table tbody tr {
            border-bottom: 1px solid rgba(226, 216, 196, 0.5);
            transition: all 0.2s ease;
            position: relative;
        }
        
        .hotel-table tbody tr:hover {
            background: rgba(11,61,58,0.025);
            transform: translateX(4px);
        }
        
        .hotel-table td {
            padding: 1.125rem 1.25rem;
            vertical-align: middle;
        }
        
        /* Responsive Table */
        @media (max-width: 768px) {
            .hotel-table thead th,
            .hotel-table td {
                padding: 0.75rem 0.5rem;
                font-size: 0.875rem;
            }
            
            .hotel-table thead th {
                font-size: 0.65rem;
                letter-spacing: 0.05em;
            }
        }
        
        /* Buttons - Enhanced */
        .btn-primary {
            background: var(--deep-teal);
            color: var(--sand);
            border: 0;
            box-shadow: 0 4px 12px rgba(11,61,58,0.16);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
            border-radius: var(--radius-sm);
        }
        
        .btn-primary:hover:not(:disabled) {
            background: var(--deep-teal-ink);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(11,61,58,0.24);
        }
        
        .btn-primary:active:not(:disabled) {
            transform: translateY(0);
        }
        
        .btn-primary:disabled {
            background: var(--sage);
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .btn-secondary {
            background: var(--card);
            color: var(--deep-teal);
            border: 1.5px solid var(--line);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: var(--radius-sm);
        }
        
        .btn-secondary:hover {
            border-color: var(--deep-teal);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(11,61,58,0.08);
        }
        
        .btn-success {
            background: var(--deep-teal);
            color: var(--sand);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: var(--radius-sm);
        }
        
        .btn-success:hover {
            background: var(--deep-teal-ink);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(11,61,58,0.16);
        }
        
        .btn-danger {
            background: transparent;
            color: var(--ember-deep);
            border: 1.5px solid var(--ember-deep);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: var(--radius-sm);
        }
        
        .btn-danger:hover {
            background: var(--ember-deep);
            color: var(--sand);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(217,98,43,0.16);
        }
        
        .btn-ghost {
            background: transparent;
            color: var(--ink);
            transition: all 0.2s ease;
            border-radius: var(--radius-sm);
        }
        
        .btn-ghost:hover {
            background: var(--sand-deep);
            transform: scale(1.05);
        }
        
        /* Form Fields - Enhanced */
        .field {
            background: var(--card);
            border: 1.5px solid var(--line);
            color: var(--ink);
            border-radius: var(--radius-sm);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            padding: 0.625rem 0.875rem;
        }
        
        .field:focus {
            border-color: var(--deep-teal);
            box-shadow: 0 0 0 3px rgba(11,61,58,0.06), 0 0 0 6px rgba(11,61,58,0.03);
            outline: none;
            transform: translateY(-1px);
        }
        
        .field::placeholder {
            color: var(--sage);
            opacity: 0.6;
        }
        
        /* Badges - Enhanced */
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.4rem 0.875rem;
            border-radius: var(--radius-sm);
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
        }
        
        .badge-success {
            background: rgba(11,61,58,0.08);
            color: var(--deep-teal);
        }
        
        .badge-warning {
            background: rgba(217,98,43,0.08);
            color: var(--ember-deep);
        }
        
        .badge-info {
            background: rgba(124,145,135,0.12);
            color: var(--sage);
        }
        
        /* Modal - Enhanced */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(11,61,58,0.5);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 10001;
            align-items: center;
            justify-content: center;
            padding: 16px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .modal.active {
            display: flex;
            opacity: 1;
        }
        
        .modal-box {
            background: var(--card);
            border-radius: var(--radius-lg);
            box-shadow: 0 24px 70px rgba(11,61,58,0.15);
            max-width: 640px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.95) translateY(10px);
            transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .modal.active .modal-box {
            transform: scale(1) translateY(0);
        }
        
        /* Image Styles - Enhanced */
        .hotel-thumbnail {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-sm);
            object-fit: cover;
            border: 1.5px solid var(--line);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(11,61,58,0.04);
        }
        
        .hotel-thumbnail:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 16px rgba(11,61,58,0.12);
        }
        
        .hotel-thumbnail-placeholder {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-sm);
            background: var(--sand-deep);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--sage);
            border: 1.5px dashed var(--line);
        }
        
        /* Dropdown - Enhanced */
        .dropdown {
            position: relative;
            display: inline-block;
            z-index: 1;
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background: var(--card);
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            box-shadow: 0 12px 32px rgba(11,61,58,0.1);
            min-width: 180px;
            z-index: 9999;
            opacity: 0;
            transform: translateY(-8px);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.65rem 1rem;
            font-size: 0.875rem;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none;
            position: relative;
            z-index: 10000;
            pointer-events: auto;
        }
        
        .dropdown-item:first-child {
            border-radius: var(--radius-sm) var(--radius-sm) 0 0;
        }
        
        .dropdown-item:last-child {
            border-radius: 0 0 var(--radius-sm) var(--radius-sm);
        }
        
        .dropdown-item:hover {
            background: var(--sand-deep);
            transform: translateX(4px);
        }
        
        .dropdown-item.text-ember-deep:hover {
            background: rgba(217,98,43,0.08);
            color: var(--ember-deep);
        }
        
        .dropdown-divider {
            height: 1px;
            background: var(--line);
            margin: 0.4rem 0;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideIn {
            from { 
                opacity: 0; 
                transform: translateX(-20px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
        
        .animate-slide-in {
            animation: slideIn 0.4s ease-out forwards;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 40;
            }
            
            .sidebar.open {
                transform: translateX(0);
            }
            
            main {
                margin-left: 0 !important;
            }
            
            .mobile-menu-btn {
                display: flex !important;
            }
        }
        
        @media (max-width: 768px) {
            .hotel-table tbody tr:hover {
                transform: none;
            }
            
            /* Stack table cells on mobile */
            .hotel-table tbody tr {
                display: flex;
                flex-wrap: wrap;
                border-bottom: 1px solid var(--line);
                padding: 1rem 0;
            }
            
            .hotel-table tbody td {
                display: block;
                width: 100%;
                padding: 0.5rem 0;
                border-bottom: none;
            }
            
            .hotel-table thead {
                display: none;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--sand-deep);
            border-radius: 8px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--sage);
            border-radius: 8px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--deep-teal);
        }
        
        /* Loading Spinner */
        .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(246, 237, 224, 0.3);
            border-top-color: var(--sand);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex admin-shell">
        <!-- Sidebar -->
        <aside class="sidebar w-[var(--sidebar-width)] min-h-screen flex flex-col" id="sidebar">
            <div class="p-5 pb-4">
                <div class="flex items-center gap-2.5 mb-1.5">
                    <span class="brand-mark" style="background:rgba(255,255,255,0.12);box-shadow:none;color:white;display:inline-flex;width:32px;height:32px;font-size:0.875rem;">⌁</span>
                    <h2 class="text-lg font-bold text-[var(--sand)] font-display">Planora</h2>
                </div>
                <p class="font-mono text-[0.6rem] tracking-wider text-[rgba(246,237,224,0.4)] uppercase ml-0.5">Admin Panel</p>
            </div>
            
            <nav class="space-y-1 flex-1 px-3">
                <a href="/admin/hotels" class="sidebar-link active flex items-center gap-3 px-3 py-2.5 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-sm font-medium">Hotels</span>
                </a>
                <a href="/admin/users" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">Users</span>
                </a>
            </nav>
            
            <div class="p-3 mt-auto">
                <div class="pt-3 border-t border-[rgba(246,237,224,0.08)]">
                    <form action="/logout" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-link w-full flex items-center gap-2.5 px-2.5 py-2 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            <span class="text-sm font-medium">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Mobile Overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 p-4 lg:p-8 min-h-screen">
            <!-- Mobile Header -->
            <div class="lg:hidden flex items-center justify-between mb-8">
                <button onclick="toggleSidebar()" class="mobile-menu-btn hidden p-2.5 rounded-lg hover:bg-[var(--sand-deep)]">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center gap-2.5">
                    <span class="brand-mark" style="background:var(--deep-teal);color:white;display:inline-flex;width:32px;height:32px;font-size:0.875rem;">⌁</span>
                    <span class="text-lg font-bold text-[var(--deep-teal)] font-display">Planora</span>
                </div>
            </div>

            <!-- Header -->
            <header class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div>
                    <h1 class="font-display text-3xl lg:text-4xl font-bold text-[var(--deep-teal)] mb-1">Hotel Management</h1>
                    <p class="text-sm text-[var(--ink-soft)] flex items-center gap-2">
                        <span class="inline-block w-1.5 h-1.5 bg-[var(--ember)] rounded-full animate-pulse-soft"></span>
                        Manage your hotel listings and inventory
                    </p>
                </div>
                <button onclick="openModal()" class="btn-primary px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 w-full lg:w-auto justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Hotel
                </button>
            </header>
            
            <!-- Hotels Table -->
            <!-- Toolbar -->
            <div class="p-4 lg:p-5 border-b border-[var(--line)]">
                    <div class="flex flex-col lg:flex-row gap-3 items-start lg:items-center justify-between">
                        <!-- Search -->
                        <div class="relative w-full lg:w-80">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--sage)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <input 
                                type="text" 
                                id="searchInput"
                                placeholder="Search hotels..." 
                                class="field w-full pl-9 pr-3 py-2 rounded-lg text-sm"
                            >
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex flex-wrap gap-2 w-full lg:w-auto">
                            <button onclick="refreshTable()" class="btn-secondary px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Refresh
                            </button>
                            <button onclick="exportHotels()" class="btn-secondary px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export
                            </button>
                        </div>
                    </div>
                    
                    <!-- Results count -->
                    <div class="mt-4 flex items-center justify-between text-sm">
                        <p class="text-[var(--ink-soft)]">
                            Showing <span class="font-semibold text-[var(--ink)]" id="resultsCount">{{ $hotels->count() }}</span> hotels
                        </p>
                        <button onclick="clearFilters()" class="text-[var(--ember-deep)] hover:text-[var(--deep-teal)] font-semibold text-sm transition hidden" id="clearFilters">
                            Clear Filters
                        </button>
                    </div>
                </div>
                
                <!-- Table -->
                <div>
                    <table class="hotel-table">
                        <thead>
                            <tr>
                                <th class="w-20">Image</th>
                                <th class="min-w-[240px]">Hotel Name</th>
                                <th class="w-28">Price</th>
                                <th class="w-24">Rating</th>
                                <th class="w-32">Updated</th>
                                <th class="w-24 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="hotelsTableBody">
                            @if($hotels->count() > 0)
                                @foreach($hotels as $hotel)
                                <tr class="animate-slide-in" style="animation-delay: {{ $loop->index * 0.03 }}s">
                                    <td>
                                        @if($hotel->image_url)
                                        <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}" class="hotel-thumbnail" onerror="this.style.display='none'">
                                        @else
                                        <div class="hotel-thumbnail-placeholder">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="flex flex-col gap-0.5">
                                            <span class="font-semibold text-[var(--ink)] text-sm">{{ $hotel->name }}</span>
                                            @if($hotel->description)
                                            <span class="text-xs text-[var(--sage)] line-clamp-1">{{ Str::limit($hotel->description, 50) }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-mono text-sm font-semibold text-[var(--ink)]">{{ $hotel->price !== null && $hotel->price !== '' ? '₱' . number_format((float) str_replace([',', '₱', ' '], '', $hotel->price), 2) : 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="font-mono text-sm font-semibold text-[var(--ink)]">{{ $hotel->rating !== null ? number_format((float) $hotel->rating, 1) : 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-sm text-[var(--sage)]">{{ $hotel->updated_at?->format('M d, Y') ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-2">
                                            <div class="dropdown">
                                                <button onclick="toggleDropdown(event, 'dropdown-{{ $hotel->id }}')" class="btn-ghost p-2 rounded-lg hover:bg-[var(--sand-deep)]">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                                    </svg>
                                                </button>
                                                <div id="dropdown-{{ $hotel->id }}" class="dropdown-menu">
        <div class="dropdown-item" onclick="viewHotel({{ $hotel->id }})">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                        </svg>
                                                        <span>View</span>
                                                    </div>
        <div class="dropdown-item" onclick="editHotel({{ $hotel->id }})">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                        </svg>
                                                        <span>Edit</span>
                                                    </div>
                                                    <div class="dropdown-divider"></div>
        <div class="dropdown-item text-ember-deep" onclick="confirmDelete({{ $hotel->id }})">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                        <span>Delete</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5">
                                        <div class="text-center py-16 px-4">
                                            <div class="mx-auto w-20 h-20 mb-4 rounded-full bg-[var(--sand-deep)] flex items-center justify-center">
                                                <svg class="w-10 h-10 text-[var(--sage)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                            </div>
                                            <p class="font-display text-xl font-semibold text-[var(--ink)] mb-2">No hotels found</p>
                                            <p class="text-sm text-[var(--sage)] mb-6 max-w-sm mx-auto">Get started by adding your first hotel listing to the system.</p>
                                            <button onclick="openModal()" class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm inline-flex items-center gap-2">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Add First Hotel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
        </main>
    </div>
    
    <!-- Add/Edit Hotel Modal -->
    <div id="hotelModal" class="modal">
        <div class="modal-box">
            <div class="p-6 border-b border-[var(--line)]">
                <div class="flex items-center justify-between">
                    <h3 id="modalTitle" class="font-display text-2xl font-semibold text-[var(--deep-teal)]">Add New Hotel</h3>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-[var(--sage)] hover:text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form id="hotelForm" action="/admin/hotels" method="POST" class="p-6">
                @csrf
                <input type="hidden" id="hotelId" name="hotel_id" value="">
                <input type="hidden" id="formMethod" name="_method" value="POST">
                
                <div class="space-y-4">
                    <!-- Hotel Name -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                            Hotel Name <span class="text-[var(--ember)]">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="hotelName"
                            name="name" 
                            required 
                            class="field w-full p-2.5 rounded-lg text-sm" 
                            placeholder="Enter hotel name"
                        >
                    </div>
                    
                    <!-- Image URL -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                            Main Image URL <span class="text-[var(--ember)]">*</span>
                        </label>
                        <input 
                            type="url" 
                            id="hotelImageUrl"
                            name="image_url" 
                            required 
                            class="field w-full p-2.5 rounded-lg text-sm" 
                            placeholder="https://example.com/image.jpg"
                        >
                        <p id="imageUrlError" class="text-xs text-[var(--ember)] mt-1 hidden">Please enter a valid URL</p>
                    </div>
                    
                    <!-- Price and Rating -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Price</label>
                            <input 
                                type="text" 
                                id="hotelPrice"
                                name="price" 
                                class="field w-full p-2.5 rounded-lg text-sm font-mono" 
                                placeholder="₱2,500"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Rating (0-10, decimals allowed e.g. 4.5)</label>
                            <input 
                                type="number" 
                                id="hotelRating"
                                step="0.1" 
                                name="rating" 
                                class="field w-full p-2.5 rounded-lg text-sm font-mono" 
                                min="0" 
                                max="10" 
                                placeholder="4.5"
                            >
                        </div>
                    </div>
                    
                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Description</label>
                        <textarea 
                            id="hotelDescription"
                            name="description" 
                            rows="3" 
                            class="field w-full p-2.5 rounded-lg text-sm resize-none" 
                            placeholder="Brief description of the hotel..."
                        ></textarea>
                    </div>
                    
                    <!-- Coordinates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Latitude</label>
                            <input 
                                type="text" 
                                id="hotelLat"
                                step="any"
                                name="lat" 
                                class="field w-full p-2.5 rounded-lg text-sm font-mono" 
                                placeholder="16.0438"
                            >
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Longitude</label>
                            <input 
                                type="text" 
                                id="hotelLon"
                                step="any"
                                name="lon" 
                                class="field w-full p-2.5 rounded-lg text-sm font-mono" 
                                placeholder="120.3331"
                            >
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Address</label>
                        <input 
                            type="text" 
                            id="hotelAddress"
                            name="address" 
                            class="field w-full p-2.5 rounded-lg text-sm" 
                            placeholder="123 Rizal St, Dagupan City"
                        >
                    </div>
                    
                    <!-- Amenities -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Amenities</label>
                        <input 
                            type="text" 
                            id="hotelAmenities"
                            name="amenities" 
                            class="field w-full p-2.5 rounded-lg text-sm" 
                            placeholder="Swimming Pool, Free WiFi, Gym, Restaurant"
                        >
                    </div>
                    
                    <!-- Gallery -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Gallery Images</label>
                        <input 
                            type="text" 
                            id="hotelGallery"
                            name="gallery" 
                            class="field w-full p-2.5 rounded-lg text-sm" 
                            placeholder="Comma-separated URLs: url1.jpg, url2.jpg, url3.jpg"
                        >
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3 mt-6">
                    <button type="submit" id="submitBtn" class="btn-primary flex-1 py-2.5 rounded-lg font-semibold text-sm">
                        Add Hotel
                    </button>
                    <button type="button" onclick="closeModal()" class="btn-secondary flex-1 py-2.5 rounded-lg font-semibold text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-box confirm-dialog">
            <div class="p-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-50">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="font-display text-xl font-semibold text-[var(--ink)] text-center mb-2">Delete Hotel</h3>
                <p class="text-sm text-[var(--ink-soft)] text-center mb-6">Are you sure you want to delete this hotel? This action cannot be undone.</p>
                <div class="flex gap-3">
                    <button onclick="closeDeleteModal()" class="btn-secondary flex-1 py-2.5 rounded-lg font-semibold text-sm">Cancel</button>
                    <button onclick="deleteHotel()" class="btn-danger flex-1 py-2.5 rounded-lg font-semibold text-sm">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Hotel Modal -->
    <div id="viewModal" class="modal">
        <div class="modal-box">
            <div class="p-6 border-b border-[var(--line)]">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-semibold text-[var(--deep-teal)]">Hotel Details</h3>
                    <button onclick="closeViewModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-[var(--sage)] hover:text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div id="viewModalContent" class="p-6">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    @foreach($hotels as $hotel)
    <form id="edit-hotel-{{ $hotel->id }}" action="/admin/hotels/{{ $hotel->id }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="name" value="{{ $hotel->name }}">
        <input type="hidden" name="image_url" value="{{ $hotel->image_url }}">
        <input type="hidden" name="rating" value="{{ $hotel->rating }}">
        <input type="hidden" name="price" value="{{ $hotel->price }}">
        <input type="hidden" name="lat" value="{{ $hotel->lat }}">
        <input type="hidden" name="lon" value="{{ $hotel->lon }}">
        <input type="hidden" name="address" value="{{ $hotel->address }}">
        <input type="hidden" name="amenities" value="{{ $hotel->amenities }}">
        <input type="hidden" name="description" value="{{ $hotel->description }}">
        <input type="hidden" name="gallery" value="{{ $hotel->gallery }}">
    </form>
    
    <form id="del-{{ $hotel->id }}" action="/admin/hotels/{{ $hotel->id }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endforeach

    <script>
        // Hotel data for modals
        const hotelsData = @json($hotels);
        let currentHotelId = null;

        // Modal functions
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Hotel';
            document.getElementById('hotelForm').action = '/admin/hotels';
            document.getElementById('hotelForm').reset();
            document.getElementById('hotelId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('submitBtn').textContent = 'Add Hotel';
            document.getElementById('hotelModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('hotelModal').classList.remove('active');
            document.body.style.overflow = '';
            document.getElementById('imageUrlError').classList.add('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function editHotel(id) {
            const hotel = hotelsData.find(h => h.id === id);
            if (!hotel) return;

            document.getElementById('modalTitle').textContent = 'Edit Hotel';
            document.getElementById('hotelForm').action = '/admin/hotels/' + id;
            document.getElementById('hotelId').value = id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('submitBtn').textContent = 'Update Hotel';
            
            document.getElementById('hotelName').value = hotel.name || '';
            document.getElementById('hotelImageUrl').value = hotel.image_url || '';
            document.getElementById('hotelPrice').value = hotel.price || '';
            document.getElementById('hotelRating').value = hotel.rating || '';
            document.getElementById('hotelDescription').value = hotel.description || '';
            document.getElementById('hotelLat').value = hotel.lat || '';
            document.getElementById('hotelLon').value = hotel.lon || '';
            document.getElementById('hotelAddress').value = hotel.address || '';
            document.getElementById('hotelAmenities').value = hotel.amenities || '';
            document.getElementById('hotelGallery').value = hotel.gallery || '';
            
            document.getElementById('hotelModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function viewHotel(id) {
            const hotel = hotelsData.find(h => h.id === id);
            if (!hotel) return;

            const content = `
                <div class="space-y-4">
                    ${hotel.image_url ? `
                    <div class="mb-4">
                        <img src="${hotel.image_url}" alt="${hotel.name}" class="w-full h-48 object-cover rounded-lg" onerror="this.style.display='none'">
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Hotel Name</p>
                        <p class="text-base font-semibold text-[var(--ink)]">${hotel.name}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Price</p>
                            <p class="text-sm font-mono font-semibold text-[var(--ink)]">${hotel.price ? '₱' + parseFloat(String(hotel.price).replace(/[₱,\s]/g, '')).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Rating</p>
                            <p class="text-sm font-mono font-semibold text-[var(--ink)]">${hotel.rating ? Number(hotel.rating).toFixed(1) : '0.0'} / 10</p>
                        </div>
                    </div>
                    ${hotel.description ? `
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Description</p>
                        <p class="text-sm text-[var(--ink-soft)]">${hotel.description}</p>
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Address</p>
                        <p class="text-sm text-[var(--ink-soft)]">${hotel.address || 'N/A'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Latitude</p>
                            <p class="text-sm font-mono text-[var(--ink-soft)]">${hotel.lat || 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Longitude</p>
                            <p class="text-sm font-mono text-[var(--ink-soft)]">${hotel.lon || 'N/A'}</p>
                        </div>
                    </div>
                    ${hotel.amenities ? `
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Amenities</p>
                        <p class="text-sm text-[var(--ink-soft)]">${hotel.amenities}</p>
                    </div>
                    ` : ''}
                    ${hotel.gallery ? `
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-2">Gallery</p>
                        <div class="grid grid-cols-3 gap-2">
                            ${hotel.gallery.split(',').filter(img => img.trim()).map(img => `
                                <img src="${img.trim()}" alt="Gallery" class="w-full h-24 object-cover rounded-lg" onerror="this.style.display='none'">
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>
            `;
            
            document.getElementById('viewModalContent').innerHTML = content;
            document.getElementById('viewModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function confirmDelete(id) {
            currentHotelId = id;
            document.getElementById('deleteModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.style.overflow = '';
            currentHotelId = null;
        }

        // Dropdown toggle
        function toggleDropdown(event, dropdownId) {
            event.stopPropagation();
            closeAllDropdowns();
            document.getElementById(dropdownId).classList.add('show');
        }

        function closeAllDropdowns() {
            document.querySelectorAll('.dropdown-menu').forEach(menu => {
                menu.classList.remove('show');
            });
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            const isDropdown = e.target.closest('.dropdown');
            const isDropdownMenu = e.target.closest('.dropdown-menu');
            if (!isDropdown && !isDropdownMenu) {
                closeAllDropdowns();
            }
        });

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }

        // Search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#hotelsTableBody tr');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            document.getElementById('resultsCount').textContent = visibleCount;
            document.getElementById('clearFilters').classList.toggle('hidden', !searchTerm);
        });

        function clearFilters() {
            searchInput.value = '';
            const rows = document.querySelectorAll('#hotelsTableBody tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            document.getElementById('resultsCount').textContent = @json($hotels->count());
            document.getElementById('clearFilters').classList.add('hidden');
        }

        // Refresh table
        function refreshTable() {
            location.reload();
        }

        // Export functionality (placeholder)
        function exportHotels() {
            showToast('Export feature coming soon', 'info');
        }

        // URL validation for image field
        const imageUrlInput = document.getElementById('hotelImageUrl');
        imageUrlInput.addEventListener('blur', function() {
            const error = document.getElementById('imageUrlError');
            if (this.value && !isValidUrl(this.value)) {
                error.classList.remove('hidden');
                this.classList.add('error');
            } else {
                error.classList.add('hidden');
                this.classList.remove('error');
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }

        // Form submission
        document.getElementById('hotelForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>';
        });

        // Delete hotel
        function deleteHotel() {
            if (currentHotelId) {
                const formId = 'del-' + currentHotelId;
                const form = document.getElementById(formId);
                if (form) {
                    form.submit();
                }
                currentHotelId = null;
            }
        }

        // Close modals on escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
                closeViewModal();
            }
        });

        // Close modals on backdrop click
        [document.getElementById('hotelModal'), document.getElementById('deleteModal'), document.getElementById('viewModal')].forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Toast notification system
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            
            const icons = {
                success: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
                error: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
                warning: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
                info: '<svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
            };
            
            toast.innerHTML = `${icons[type] || icons.info}<span>${message}</span>`;
            container.appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 10);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Add keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl/Cmd + K to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                }
                // N to add new (when not in input)
                if (e.key === 'n' && !e.target.matches('input, textarea')) {
                    e.preventDefault();
                    openModal();
                }
            });
        });
    </script>
</body>
</html>
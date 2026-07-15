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
        body { 
            background-color: var(--sand);
            background-image: radial-gradient(circle at 8% 12%, rgba(11,61,58,0.05) 0%, transparent 45%);
        }
        
        .sidebar { 
            background: linear-gradient(180deg, var(--deep-teal), var(--deep-teal-ink));
            box-shadow: 4px 0 20px rgba(11,61,58,0.1);
        }
        .sidebar-link { 
            color: rgba(246, 237, 224, 0.6); 
            transition: all 0.2s ease; 
            border-radius: var(--radius-sm);
        }
        .sidebar-link:hover, .sidebar-link.active { 
            color: var(--sand); 
            background: rgba(246, 237, 224, 0.1); 
        }
        
        .stat-card { 
            border-left: 0; 
            border-top: 4px solid var(--deep-teal);
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .table-card {
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.3s ease;
        }
        .table-card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .field { 
            background: var(--card); 
            border: 1.5px solid var(--line); 
            color: var(--ink); 
            transition: border-color 0.2s ease, box-shadow 0.2s ease; 
            border-radius: var(--radius-sm);
        }
        .field:focus { 
            border-color: var(--deep-teal); 
            box-shadow: 0 0 0 3px rgba(11,61,58,0.08); 
            outline: none; 
        }
        
        .btn-primary { 
            background: var(--deep-teal); 
            color: var(--sand); 
            transition: all 0.25s ease;
            border-radius: var(--radius-sm);
            font-weight: 600;
        }
        .btn-primary:hover { 
            background: var(--deep-teal-ink); 
            transform: translateY(-1px); 
            box-shadow: var(--shadow-md);
        }
        .btn-success { 
            background: var(--deep-teal); 
            color: var(--sand); 
            transition: all 0.2s ease;
            border-radius: var(--radius-sm);
        }
        .btn-success:hover { 
            background: var(--deep-teal-ink); 
            transform: translateY(-1px);
        }
        .btn-danger { 
            background: transparent; 
            color: var(--ember-deep); 
            border: 1.5px solid var(--ember-deep); 
            transition: all 0.2s ease;
            border-radius: var(--radius-sm);
        }
        .btn-danger:hover { 
            background: var(--ember-deep); 
            color: var(--sand); 
            transform: translateY(-1px);
        }
        .btn-secondary { 
            background: var(--sand-deep); 
            color: var(--ink); 
            transition: all 0.2s ease;
            border-radius: var(--radius-sm);
        }
        .btn-secondary:hover { 
            background: var(--line); 
        }
        
        table { border-collapse: collapse; }
        thead th { 
            font-family: 'JetBrains Mono', monospace; 
            font-size: 0.68rem; 
            text-transform: uppercase; 
            letter-spacing: 0.08em; 
            color: var(--sage); 
            font-weight: 700; 
            background: var(--sand-deep); 
            border-bottom: 1px solid var(--line); 
            padding: 0.9rem 0.75rem;
        }
        tbody tr { 
            border-top: 1px dashed var(--line); 
            transition: background 0.15s ease; 
        }
        tbody tr:hover { 
            background: rgba(11,61,58,0.03); 
        }
        td { 
            padding: 0.75rem; 
            vertical-align: middle;
        }
        
        .modal { 
            display: none; 
            position: fixed; 
            inset: 0; 
            background: rgba(11,61,58,0.5);
            backdrop-filter: blur(4px);
            z-index: 50; 
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .modal.active { display: flex; }
        .modal-box {
            background: var(--card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .badge { 
            display: inline-flex; 
            align-items: center; 
            padding: 0.25rem 0.5rem; 
            border-radius: 0.5rem; 
            font-size: 0.75rem; 
            font-weight: 600; 
        }
        .badge-success { background: rgba(11,61,58,0.1); color: var(--deep-teal); }
        .badge-warning { background: rgba(217,98,43,0.1); color: var(--ember-deep); }
        
        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-track { background: var(--sand-deep); }
        ::-webkit-scrollbar-thumb { background: var(--sage); border-radius: 8px; }
        
        .admin-title {
            font-family: 'DM Serif Display', serif;
            color: var(--deep-teal);
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex admin-shell">
        <!-- Sidebar -->
        <aside class="sidebar w-64 min-h-screen p-6 fixed flex flex-col">
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-1">
                    <span class="brand-mark" style="background:rgba(255,255,255,0.12);box-shadow:none;color:white;display:inline-flex;">⌁</span>
                    <h2 class="text-xl font-bold text-[var(--sand)] font-display">Planora</h2>
                </div>
                <p class="font-mono text-[0.6rem] tracking-wider text-[rgba(246,237,224,0.4)] uppercase mt-1 ml-1">Admin Panel</p>
            </div>
            
            <nav class="space-y-1 flex-1">
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
            
            <div class="pt-4 border-t border-[rgba(246,237,224,0.08)]">
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="sidebar-link w-full flex items-center gap-3 px-3 py-2.5 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span class="text-sm font-medium">Logout</span>
                    </button>
                </form>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="ml-64 flex-1 p-8" style="min-height:100vh;">
            <header class="mb-8 flex items-center justify-between admin-header">
                <div>
                    <h1 class="admin-title text-3xl font-semibold">Hotel Management</h1>
                    <p class="text-sm text-[var(--ink-soft)] mt-1">Manage your hotel listings and inventory</p>
                </div>
                <button onclick="openModal()" class="btn-primary px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Add New Hotel
                </button>
            </header>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Total Hotels</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $hotels->count() }}</p>
                </div>
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl" style="border-top-color:var(--ember);">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Premium</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $hotels->where('rating', '>=', 4)->count() }}</p>
                </div>
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl" style="border-top-color:var(--ember);">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Avg. Rating</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $hotels->avg('rating') ? number_format($hotels->avg('rating'), 1) : '0.0' }}</p>
                </div>
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Last Updated</p>
                    <p class="text-sm font-bold text-[var(--ink)] mt-2">{{ $hotels->last()?->updated_at?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
            </div>
            
            <!-- Hotels Table -->
            <div class="bg-[var(--card)] border border-[var(--line)] rounded-xl table-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th class="min-w-[200px]">Hotel Name</th>
                                <th class="w-48">Image</th>
                                <th class="w-16">Rating</th>
                                <th class="w-24">Price</th>
                                <th class="w-32">Coordinates</th>
                                <th class="min-w-[150px]">Address</th>
                                <th class="min-w-[120px]">Amenities</th>
                                <th class="w-20">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hotels as $hotel)
                            <tr>
                                <td class="font-mono text-sm text-[var(--sage)]">{{ $loop->iteration }}</td>
                                <td>
                                    <input type="text" name="name" value="{{ $hotel->name }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1.5 rounded text-sm w-full" required>
                                </td>
                                <td>
                                    @if($hotel->image_url)
                                    <img src="{{ $hotel->image_url }}" alt="{{ $hotel->name }}" class="image-preview" onerror="this.style.display='none'">
                                    @endif
                                    <input type="text" name="image_url" value="{{ $hotel->image_url }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1 rounded text-xs w-full mt-1" placeholder="Image URL">
                                </td>
                                <td>
                                    <input type="number" step="0.1" name="rating" value="{{ $hotel->rating }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1.5 rounded text-sm font-mono w-16 text-center" min="0" max="10">
                                </td>
                                <td>
                                    <input type="text" name="price" value="{{ $hotel->price }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1.5 rounded text-sm font-mono w-20 text-center" placeholder="₱0">
                                </td>
                                <td>
                                    <div class="flex flex-col gap-1">
                                        <input type="text" name="lat" value="{{ $hotel->lat }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1 rounded text-xs font-mono w-full" placeholder="Lat">
                                        <input type="text" name="lon" value="{{ $hotel->lon }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1 rounded text-xs font-mono w-full" placeholder="Lon">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" name="address" value="{{ $hotel->address }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1.5 rounded text-xs w-full" placeholder="Address">
                                </td>
                                <td>
                                    <input type="text" name="amenities" value="{{ $hotel->amenities }}" form="edit-hotel-{{ $hotel->id }}" class="field p-1.5 rounded text-xs w-full" placeholder="Pool, WiFi">
                                </td>
                                <td>
                                    <div class="flex gap-1.5">
                                        <button type="submit" form="edit-hotel-{{ $hotel->id }}" class="btn-success px-2.5 py-1.5 rounded text-xs font-semibold">Save</button>
                                        <button type="button" onclick="confirmDelete({{ $hotel->id }})" class="btn-danger px-2.5 py-1.5 rounded text-xs font-semibold">Delete</button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-16 text-[var(--ink-soft)]">
                                    <div style="width:64px;height:64px;margin:0 auto 16px;border-radius:50%;background:var(--sand-deep);display:flex;align-items:center;justify-content:center;">
                                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-[var(--ink)]">No hotels found</p>
                                    <p class="text-sm text-[var(--sage)] mt-1">Get started by adding your first hotel</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Add Hotel Modal -->
    <div id="addModal" class="modal">
        <div class="modal-box">
            <div class="p-6 border-b border-[var(--line)]">
                <div class="flex items-center justify-between">
                    <h3 class="font-display text-2xl font-semibold text-[var(--deep-teal)]">Add New Hotel</h3>
                    <button onclick="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-[var(--sage)] hover:text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <form action="/admin/hotels" method="POST" class="p-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Hotel Name *</label>
                        <input type="text" name="name" required class="field w-full p-2.5 rounded-lg" placeholder="Enter hotel name">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Main Image URL *</label>
                        <input type="text" name="image_url" required class="field w-full p-2.5 rounded-lg" placeholder="https://example.com/image.jpg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Price</label>
                        <input type="text" name="price" class="field w-full p-2.5 rounded-lg font-mono" placeholder="₱2,500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Rating (0-10)</label>
                        <input type="number" step="0.1" name="rating" class="field w-full p-2.5 rounded-lg font-mono" min="0" max="10" placeholder="4.5">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Description</label>
                        <textarea name="description" rows="3" class="field w-full p-2.5 rounded-lg" placeholder="Brief description of the hotel..."></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Latitude</label>
                        <input type="text" name="lat" step="any" class="field w-full p-2.5 rounded-lg font-mono" placeholder="16.0438">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Longitude</label>
                        <input type="text" name="lon" step="any" class="field w-full p-2.5 rounded-lg font-mono" placeholder="120.3331">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Address</label>
                        <input type="text" name="address" class="field w-full p-2.5 rounded-lg" placeholder="123 Rizal St, Dagupan City">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Amenities</label>
                        <input type="text" name="amenities" class="field w-full p-2.5 rounded-lg" placeholder="Swimming Pool, Free WiFi, Gym, Restaurant">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-[var(--ink)] mb-1.5">Gallery Images</label>
                        <input type="text" name="gallery" class="field w-full p-2.5 rounded-lg" placeholder="Comma-separated URLs: url1.jpg, url2.jpg, url3.jpg">
                    </div>
                </div>
                <div class="flex gap-3 mt-6">
                    <button type="submit" class="btn-primary flex-1 py-2.5 rounded-lg font-semibold">Add Hotel</button>
                    <button type="button" onclick="closeModal()" class="btn-secondary flex-1 py-2.5 rounded-lg font-semibold">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    @foreach($hotels as $hotel)
    <form id="edit-hotel-{{ $hotel->id }}" action="/admin/hotels/{{ $hotel->id }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="description" value="{{ $hotel->description }}">
        <input type="hidden" name="gallery" value="{{ $hotel->gallery }}">
    </form>
    
    <form id="del-{{ $hotel->id }}" action="/admin/hotels/{{ $hotel->id }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
    @endforeach
    
    <script>
        function openModal() {
            document.getElementById('addModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('addModal').classList.remove('active');
            document.body.style.overflow = '';
        }
        
        function confirmDelete(id) {
            if (confirm('Are you sure you want to delete this hotel? This action cannot be undone.')) {
                document.getElementById('del-' + id).submit();
            }
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });
        
        // Close modal on backdrop click
        document.getElementById('addModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
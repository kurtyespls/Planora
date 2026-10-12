@extends('layouts.admin')

@section('title', 'Places & Spots')
@section('page_title', 'Places & Spots')
@section('page_subtitle', 'Curate the points of interest shown in the gallery and trip planner')

@push('styles')
<style>
    body {
        background-color: var(--sand);
        background-image:
            radial-gradient(circle at 8% 12%, rgba(11,61,58,0.04) 0%, transparent 45%),
            radial-gradient(circle at 95% 90%, rgba(217,98,43,0.03) 0%, transparent 40%);
    }

    /* Place Card Grid */
    .place-grid { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
    @media (min-width: 768px) { .place-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1280px) { .place-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }

    .place-card {
        position: relative; display: flex; flex-direction: column;
        background: var(--card); border: 1px solid var(--line);
        border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm);
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .place-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-lg); border-color: rgba(217, 98, 43, 0.4); }

    .place-card-cover { position: relative; height: 11rem; background: var(--sand-deep); overflow: hidden; flex-shrink: 0; }
    .place-card-cover img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .place-card-cover-empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: var(--sage); background: var(--sand-deep); }

    .place-card-badge {
        position: absolute; top: 0.75rem; left: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;
        padding: 0.25rem 0.5rem; border-radius: 999px; font-family: 'JetBrains Mono', monospace;
        font-size: 0.7rem; font-weight: 600; color: #fff; background: rgba(11, 61, 58, 0.85); backdrop-filter: blur(4px);
    }
    .place-card-category {
        position: absolute; bottom: 0.75rem; left: 0.75rem; display: inline-flex; align-items: center;
        padding: 0.25rem 0.625rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700;
        letter-spacing: 0.02em; text-transform: uppercase; color: #fff; background: var(--ember);
    }

    .place-card-body { padding: 1.125rem; }
    .place-card-title { font-family: 'DM Serif Display', serif; font-size: 1.15rem; font-weight: 400; color: var(--ink); line-height: 1.25; }
    .place-card-stars { color: var(--ember); letter-spacing: 0.05em; }
    .place-card-price { font-family: 'JetBrains Mono', monospace; font-size: 0.95rem; font-weight: 700; color: var(--deep-teal); }
    .place-card-actions { margin-top: auto; padding-top: 0.75rem; display: flex; gap: 0.5rem; }
    .place-chip { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.7rem; font-weight: 600; background: rgba(11, 61, 58, 0.08); color: var(--deep-teal); }

    @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }
    .animate-fade-in { animation: fadeIn 0.4s ease-out forwards; }
    .animate-slide-in { animation: slideIn 0.4s ease-out forwards; }

    ::-webkit-scrollbar { height: 8px; width: 8px; }
    ::-webkit-scrollbar-track { background: var(--sand-deep); border-radius: 8px; }
    ::-webkit-scrollbar-thumb { background: var(--sage); border-radius: 8px; }
    ::-webkit-scrollbar-thumb:hover { background: var(--deep-teal); }

    .spinner { width: 16px; height: 16px; border: 2px solid rgba(246, 237, 224, 0.3); border-top-color: var(--sand); border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block; }
    @keyframes spin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .place-card-cover { height: 9.5rem; }
        .place-card-body { padding: 1rem; }
    }
</style>
@endpush

@section('content')
<!-- Header -->
<header class="mb-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div>
        <h1 class="font-display text-3xl lg:text-4xl font-bold text-[var(--deep-teal)] mb-1">Places &amp; Spots</h1>
        <p class="text-sm text-[var(--ink-soft)] flex items-center gap-2">
            <span class="inline-block w-1.5 h-1.5 bg-[var(--ember)] rounded-full animate-pulse-soft"></span>
            Points of interest that appear in the gallery and trip planner
        </p>
    </div>
    <button onclick="openModal()" class="btn-primary px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 w-full lg:w-auto justify-center">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        Add New Place
    </button>
</header>

<div class="bg-[var(--card)] border border-[var(--line)] rounded-xl shadow-sm overflow-hidden">

    <!-- Toolbar -->
    <div class="p-4 lg:p-5 border-b border-[var(--line)]">
        <div class="flex flex-col lg:flex-row gap-3 items-start lg:items-center justify-between">
            <!-- Search -->
            <div class="relative w-full lg:w-80">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[var(--sage)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Search places..." class="field w-full pl-9 pr-3 py-2 rounded-lg text-sm">
            </div>

            <!-- Category Filter -->
            <div class="w-full lg:w-56">
                <select id="categoryFilter" class="field w-full px-3 py-2 rounded-lg text-sm">
                    <option value="">All categories</option>
                    @foreach($categories as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Results count -->
        <div class="mt-4 flex items-center justify-between text-sm">
            <p class="text-[var(--ink-soft)]">
                Showing <span id="resultsCount" class="font-semibold text-[var(--ink)]">{{ $places->count() }}</span>
                of <span class="font-semibold text-[var(--ink)]">{{ $places->total() }}</span> places
            </p>
            <button onclick="clearFilters()" id="clearFilters" class="hidden text-xs font-semibold text-[var(--ember)] hover:underline">Clear filters</button>
        </div>
    </div>


    <!-- Places Grid -->
    <div id="placesGrid" class="place-grid p-4 lg:p-5">
        @if($places->count() > 0)
            @foreach($places as $place)
                @php
                    $catLabel = $categories[$place->category] ?? ucfirst((string) $place->category);
                    $searchBlob = mb_strtolower(implode(' ', array_filter([
                        $place->name,
                        $place->location,
                        $place->description,
                        $catLabel,
                        $place->category,
                    ])), 'UTF-8');
                @endphp
                <article class="place-card animate-slide-in" data-place-card data-search="{{ $searchBlob }}" data-category="{{ $place->category }}" style="animation-delay: {{ $loop->index * 0.03 }}s">
                    <!-- Cover -->
                    <div class="place-card-cover">
                        @if($place->image_url)
                            <img src="{{ $place->image_url }}" alt="{{ $place->name }}" loading="lazy" onerror="this.style.display='none'">
                        @else
                            <div class="place-card-cover-empty">
                                <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        <span class="place-card-badge">
                            <svg class="w-3.5 h-3.5" style="color: var(--ember);" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 15.27L16.18 19l-1.64-7.03L20 7.24l-7.19-.61L10 0 7.19 6.63 0 7.24l5.46 4.73L3.82 19z"></path>
                            </svg>
                            {{ $place->rating !== null ? number_format((float) $place->rating, 1) : 'N/A' }}
                        </span>
                        <span class="place-card-category">{{ $catLabel }}</span>
                    </div>

                    <!-- Body -->
                    <div class="place-card-body">
                        <h3 class="place-card-title mb-1">{{ $place->name }}</h3>

                        <div class="flex items-center gap-1.5 mb-2.5">
                            @php $starCount = max(0, min(5, (int) round(((float) ($place->rating ?? 0)) / 2))); @endphp
                            <span class="place-card-stars" aria-hidden="true">{{ str_repeat('★', $starCount) ?: '☆' }}</span>
                            <span class="font-mono text-xs font-semibold text-[var(--ink-soft)]">
                                {{ $place->rating !== null ? number_format((float) $place->rating, 1) . ' / 10' : 'Unrated' }}
                            </span>
                        </div>

                        <p class="place-card-price mb-3">
                            {{ $place->budgetPerDay !== null ? '₱' . number_format((float) $place->budgetPerDay, 2) . ' / day' : 'Budget unavailable' }}
                        </p>

                        @if($place->description)
                            <p class="text-sm text-[var(--ink-soft)] leading-relaxed line-clamp-2 mb-3">{{ $place->description }}</p>
                        @endif

                        <div class="text-xs text-[var(--sage)] space-y-1 mb-1">
                            @if($place->location)
                                <p class="flex items-start gap-1.5 line-clamp-2">
                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>{{ $place->location }}</span>
                                </p>
                            @endif
                            @if($place->latitude !== null && $place->longitude !== null)
                                <p class="font-mono">{{ number_format((float) $place->latitude, 4) }}, {{ number_format((float) $place->longitude, 4) }}</p>
                            @endif
                            <p class="font-mono">Updated {{ $place->updated_at?->format('M d, Y') ?? 'N/A' }}</p>
                        </div>

                        <!-- Actions -->
                        <div class="place-card-actions">
                            <button type="button" onclick="viewPlace({{ $place->id }})" class="btn-secondary flex-1 px-3 py-2 rounded-lg text-xs font-semibold inline-flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </button>
                            <button type="button" onclick="editPlace({{ $place->id }})" class="btn-primary flex-1 px-3 py-2 rounded-lg text-xs font-semibold inline-flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit
                            </button>
                            <button type="button" onclick="confirmDelete({{ $place->id }})" class="btn-danger px-2.5 py-2 rounded-lg inline-flex items-center justify-center" aria-label="Delete {{ $place->name }}" title="Delete place">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </article>
            @endforeach
        @else
            <div class="bg-[var(--card)] border border-[var(--line)] rounded-xl">
                <div class="text-center py-16 px-4">
                    <div class="mx-auto w-20 h-20 mb-4 rounded-full bg-[var(--sand-deep)] flex items-center justify-center">
                        <svg class="w-10 h-10 text-[var(--sage)] opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.999 1.999 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <p class="font-display text-xl font-semibold text-[var(--ink)] mb-2">No places found</p>
                    <p class="text-sm text-[var(--sage)] mb-6 max-w-sm mx-auto">Get started by adding your first point of interest to the catalogue.</p>
                    <button onclick="openModal()" class="btn-primary px-6 py-2.5 rounded-lg font-semibold text-sm inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add First Place
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if($places->hasPages())
        <div class="p-4 border-t border-[var(--line)] bg-[var(--sand)]">
            {{ $places->links() }}
        </div>
    @endif
</div>
@endsection


@section('modals')
<!-- Add/Edit Place Modal -->
<div id="placeModal" class="modal">
    <div class="modal-box">
        <div class="p-6 border-b border-[var(--line)]">
            <div class="flex items-center justify-between">
                <h3 id="modalTitle" class="font-display text-2xl font-semibold text-[var(--deep-teal)]">Add New Place</h3>
                <button onclick="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-[var(--sage)] hover:text-[var(--ink)] hover:bg-[var(--sand-deep)] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <form id="placeForm" action="/admin/places" method="POST" class="p-6">
            @csrf
            <input type="hidden" id="placeId" name="place_id" value="">
            <input type="hidden" id="formMethod" name="_method" value="POST">

            <div class="space-y-4">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                        Place Name <span class="text-[var(--ember)]">*</span>
                    </label>
                    <input type="text" id="placeName" name="name" required class="field w-full p-2.5 rounded-lg text-sm" placeholder="e.g. Bonuan Blue Beach">
                </div>

                <!-- Category -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                        Category <span class="text-[var(--ember)]">*</span>
                    </label>
                    <select id="placeCategory" name="category" required class="field w-full p-2.5 rounded-lg text-sm">
                        <option value="" disabled selected>Select a category</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Image URL -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Image URL</label>
                    <input type="url" id="placeImageUrl" name="image_url" class="field w-full p-2.5 rounded-lg text-sm" placeholder="https://example.com/image.jpg">
                    <p id="imageUrlError" class="text-xs text-[var(--ember)] mt-1 hidden">Please enter a valid URL</p>
                </div>

                <!-- Location / Address -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Location / Address</label>
                    <input type="text" id="placeLocation" name="location" class="field w-full p-2.5 rounded-lg text-sm" placeholder="e.g. Bonuan Binloc, Dagupan City">
                </div>


                <!-- Coordinates -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                            Latitude <span class="text-[var(--ember)]">*</span>
                        </label>
                        <input type="text" id="placeLat" step="any" name="latitude" required class="field w-full p-2.5 rounded-lg text-sm font-mono" placeholder="16.0439">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                            Longitude <span class="text-[var(--ember)]">*</span>
                        </label>
                        <input type="text" id="placeLon" step="any" name="longitude" required class="field w-full p-2.5 rounded-lg text-sm font-mono" placeholder="120.3331">
                    </div>
                </div>

                <!-- Budget & Rating -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">
                            Budget per day (₱) <span class="text-[var(--ember)]">*</span>
                        </label>
                        <input type="text" id="placeBudget" name="budgetPerDay" required class="field w-full p-2.5 rounded-lg text-sm font-mono" placeholder="500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Rating (0-10)</label>
                        <input type="number" id="placeRating" step="0.1" name="rating" class="field w-full p-2.5 rounded-lg text-sm font-mono" min="0" max="10" placeholder="4.5">
                    </div>
                </div>

                <!-- Icon (optional) -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Icon (optional)</label>
                    <input type="text" id="placeIcon" name="icon" class="field w-full p-2.5 rounded-lg text-sm" placeholder="Short label or emoji used as a fallback marker">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-semibold text-[var(--ink)] mb-1.5">Description</label>
                    <textarea id="placeDescription" name="description" rows="3" class="field w-full p-2.5 rounded-lg text-sm resize-none" placeholder="Brief description of the place..."></textarea>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 mt-6">
                <button type="submit" id="submitBtn" class="btn-primary flex-1 py-2.5 rounded-lg font-semibold text-sm">
                    Add Place
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
            <h3 class="font-display text-xl font-semibold text-[var(--ink)] text-center mb-2">Delete Place</h3>
            <p class="text-sm text-[var(--ink-soft)] text-center mb-6">Are you sure you want to delete this place? This action cannot be undone.</p>
            <div class="flex gap-3">
                <button onclick="closeDeleteModal()" class="btn-secondary flex-1 py-2.5 rounded-lg font-semibold text-sm">Cancel</button>
                <button onclick="deletePlace()" class="btn-danger flex-1 py-2.5 rounded-lg font-semibold text-sm">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- View Place Modal -->
<div id="viewModal" class="modal">
    <div class="modal-box">
        <div class="p-6 border-b border-[var(--line)]">
            <div class="flex items-center justify-between">
                <h3 class="font-display text-2xl font-semibold text-[var(--deep-teal)]">Place Details</h3>
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

@foreach($places as $place)
<form id="del-{{ $place->id }}" action="/admin/places/{{ $place->id }}" method="POST" class="hidden">
    @csrf
    @method('DELETE')
</form>
@endforeach
@endsection


@push('scripts')
<script>
// Place data for modals
        const placesData = @json($places->items());
        const categoryLabels = @json($categories);
        let currentPlaceId = null;

        function categoryLabel(key) {
            return categoryLabels[key] || (key ? key.charAt(0).toUpperCase() + key.slice(1) : 'Place');
        }

        function formatBudget(value) {
            if (value === null || value === undefined || value === '') return 'N/A';
            return '₱' + Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // Modal functions
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Place';
            document.getElementById('placeForm').action = '/admin/places';
            document.getElementById('placeForm').reset();
            document.getElementById('placeId').value = '';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('submitBtn').textContent = 'Add Place';
            document.getElementById('placeCategory').value = '';
            document.getElementById('imageUrlError').classList.add('hidden');
            document.getElementById('placeModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('placeModal').classList.remove('active');
            document.body.style.overflow = '';
            document.getElementById('imageUrlError').classList.add('hidden');
        }

        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function editPlace(id) {
            const place = placesData.find(p => p.id === id);
            if (!place) return;

            document.getElementById('modalTitle').textContent = 'Edit Place';
            document.getElementById('placeForm').action = '/admin/places/' + id;
            document.getElementById('placeId').value = id;
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('submitBtn').textContent = 'Update Place';

            document.getElementById('placeName').value = place.name || '';
            document.getElementById('placeCategory').value = place.category || '';
            document.getElementById('placeImageUrl').value = place.image_url || '';
            document.getElementById('placeLocation').value = place.location || '';
            document.getElementById('placeLat').value = place.latitude || '';
            document.getElementById('placeLon').value = place.longitude || '';
            document.getElementById('placeBudget').value = place.budgetPerDay || '';
            document.getElementById('placeRating').value = place.rating || '';
            document.getElementById('placeIcon').value = place.icon || '';
            document.getElementById('placeDescription').value = place.description || '';

            document.getElementById('placeModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }


        function viewPlace(id) {
            const place = placesData.find(p => p.id === id);
            if (!place) return;

            const content = `
                <div class="space-y-4">
                    ${place.image_url ? `
                    <div class="mb-4">
                        <img src="${place.image_url}" alt="${place.name}" class="w-full h-48 object-cover rounded-lg" onerror="this.style.display='none'">
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Place Name</p>
                        <p class="text-base font-semibold text-[var(--ink)]">${place.name}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Category</p>
                            <span class="place-chip">${categoryLabel(place.category)}</span>
                        </div>
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Rating</p>
                            <p class="text-sm font-mono font-semibold text-[var(--ink)]">${place.rating ? Number(place.rating).toFixed(1) : '0.0'} / 10</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Budget per day</p>
                        <p class="text-sm font-mono font-semibold text-[var(--ink)]">${formatBudget(place.budgetPerDay)}</p>
                    </div>
                    ${place.description ? `
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Description</p>
                        <p class="text-sm text-[var(--ink-soft)]">${place.description}</p>
                    </div>
                    ` : ''}
                    <div>
                        <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Location</p>
                        <p class="text-sm text-[var(--ink-soft)]">${place.location || 'N/A'}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Latitude</p>
                            <p class="text-sm font-mono text-[var(--ink-soft)]">${place.latitude ?? 'N/A'}</p>
                        </div>
                        <div>
                            <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] font-semibold mb-1">Longitude</p>
                            <p class="text-sm font-mono text-[var(--ink-soft)]">${place.longitude ?? 'N/A'}</p>
                        </div>
                    </div>
                </div>
            `;

            document.getElementById('viewModalContent').innerHTML = content;
            document.getElementById('viewModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }


        function confirmDelete(id) {
            currentPlaceId = id;
            document.getElementById('deleteModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
            document.body.style.overflow = '';
            currentPlaceId = null;
        }

        function deletePlace() {
            if (currentPlaceId) {
                const form = document.getElementById('del-' + currentPlaceId);
                if (form) form.submit();
                currentPlaceId = null;
            }
        }

        // Search + category filter
        const searchInput = document.getElementById('searchInput');
        const categoryFilter = document.getElementById('categoryFilter');
        const placeCards = Array.from(document.querySelectorAll('#placesGrid [data-place-card]'));

        function applyFilters() {
            const term = searchInput.value.trim().toLowerCase();
            const cat = categoryFilter.value;
            let visible = 0;

            placeCards.forEach(card => {
                const haystack = (card.dataset.search || card.textContent).toLowerCase();
                const matchesTerm = haystack.includes(term);
                const matchesCat = !cat || card.dataset.category === cat;
                const show = matchesTerm && matchesCat;
                card.style.display = show ? '' : 'none';
                if (show) visible++;
            });

            document.getElementById('resultsCount').textContent = visible;
            document.getElementById('clearFilters').classList.toggle('hidden', !term && !cat);
        }

        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);

        function clearFilters() {
            searchInput.value = '';
            categoryFilter.value = '';
            placeCards.forEach(card => { card.style.display = ''; });
            document.getElementById('resultsCount').textContent = placeCards.length;
            document.getElementById('clearFilters').classList.add('hidden');
        }

        // URL validation for image field
        const imageUrlInput = document.getElementById('placeImageUrl');
        imageUrlInput.addEventListener('blur', function () {
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
            try { new URL(string); return true; } catch (_) { return false; }
        }

        // Form submission loading state
        document.getElementById('placeForm').addEventListener('submit', function () {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner"></span>';
        });

        // Close modals on escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeModal();
                closeDeleteModal();
                closeViewModal();
            }
        });

        // Close modals on backdrop click
        [document.getElementById('placeModal'), document.getElementById('deleteModal'), document.getElementById('viewModal')].forEach(modal => {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Keyboard shortcut: N to add new (when not typing)
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'n' && !e.target.matches('input, textarea, select')) {
                    e.preventDefault();
                    openModal();
                }
            });
        });
</script>
@endpush


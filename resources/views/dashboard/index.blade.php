<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planora — Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .tab-button {
            @apply px-4 py-2 rounded-lg font-semibold text-sm transition-all duration-200 flex items-center gap-2;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
        }
        .tab-button.active {
            @apply bg-[var(--deep-teal)] text-[var(--sand)] border-[var(--deep-teal)];
            box-shadow: 0 4px 12px rgba(11,61,58,0.15);
        }
        .tab-button:hover:not(.active) {
            background: var(--sand-deep);
            color: var(--deep-teal);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        .level-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--sand);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .xp-bar-bg {
            @apply h-2 rounded-full bg-[#E5D5C4] overflow-hidden;
        }
        .xp-bar-fill {
            @apply h-full rounded-full bg-gradient-to-r from-[#D9622B] to-[#0B3D3A];
        }
        .plan-card {
            @apply bg-[var(--card)] border border-[var(--line)] rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300;
        }
        .plan-card:hover {
            transform: translateY(-2px);
            border-color: rgba(217,98,43,0.4);
        }
        .badge-level {
            @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-bold;
        }
    </style>
</head>
<body class="bg-[#F6EDE0]">
    <nav class="top-nav">
        <div class="top-nav-inner">
            <a href="/planora" class="brand flex items-center gap-2 text-[#0B3D3A] font-bold text-lg">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2L22 8V16C22 20.42 18.42 24 14 26C9.58 24 6 20.42 6 16V8L14 2Z" fill="white" stroke="#0B3D3A" stroke-width="1.5"/>
                    <path d="M14 8V16M14 16L17 13M14 16L11 13" stroke="#0B3D3A" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="14" cy="8" r="2" fill="#D9622B"/>
                </svg>
                <span>Planora</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="/my-plans" class="text-sm text-[#5B6763] hover:text-[#0B3D3A] font-medium">My Plans</a>
                <a href="/public-plans" class="text-sm text-[#5B6763] hover:text-[#0B3D3A] font-medium">Public Plans</a>

        <main class="max-w-6xl mx-auto px-4 py-8">
            <div class="mb-8">
                <h1 class="font-display text-3xl font-bold text-[#1B2624] mb-2">
                    <span x-text="welcomeMessage"></span>
                </h1>
                <p class="text-[#5B6763] text-lg">
                    Welcome back! Here's an overview of your Planora account.
                </p>
            </div>

            <div class="flex flex-wrap gap-4 mb-6 border-b border-[#E2D8C4]">
                <button @click="activeTab = 'profile'" :class="['tab-button', { active: activeTab === 'profile' }]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile
                </button>
                <button @click="activeTab = 'plans'" :class="['tab-button', { active: activeTab === 'plans' }]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    My Plans ({{ $userPlans->count() }})
                </button>
                <button @click="activeTab = 'level'" :class="['tab-button', { active: activeTab === 'level' }]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Level &amp; XP
                </button>
                <button @click="activeTab = 'public'" :class="['tab-button', { active: activeTab === 'public' }]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                    Public Plans ({{ $otherPublicPlans->count() }})
                </button>
            </div>

            <div class="space-y-6">
                <!-- Profile Tab -->
                <div x-show="activeTab === 'profile'" :class="['tab-content', { active: activeTab === 'profile' }]">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-[var(--card)] rounded-xl p-6 border border-[var(--line)]">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#0B3D3A] to-[#D9622B] flex items-center justify-center text-white text-3xl font-bold shadow-lg">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h2 class="font-display text-xl font-bold text-[#1B2624]">{{ $user->name }}</h2>
                                    <p class="text-sm text-[#5B6763]">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-3 rounded-lg bg-[#EDE0CB]">
                                    <span class="text-sm text-[#5B6763]">Member since</span>
                                    <span class="text-sm font-semibold text-[#1B2624]">{{ $user->created_at->format('F d, Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg bg-[#EDE0CB]">
                                    <span class="text-sm text-[#5B6763]">Last updated</span>
                                    <span class="text-sm font-semibold text-[#1B2624]">{{ $user->updated_at->format('F d, Y') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-lg bg-[#EDE0CB]">
                                    <span class="text-sm text-[#5B6763]">Account type</span>
                                    <span class="text-sm font-semibold">
                                        <span class="inline-flex items-center gap-1">
                                            <svg class="w-4 h-4 text-[#0B3D3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            User
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-[#EDE0CB] rounded-xl p-4 text-center">
                                <p class="text-2xl font-bold text-[#0B3D3A]">{{ $userPlans->count() }}</p>
                                <p class="text-xs text-[#5B6763] uppercase tracking-wider">Your Plans</p>
                            </div>
                            <div class="bg-[#EDE0CB] rounded-xl p-4 text-center">
                                <p class="text-2xl font-bold text-[#0B3D3A]">{{ $otherPublicPlans->count() }}</p>
                                <p class="text-xs text-[#5B6763] uppercase tracking-wider">Public Plans</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <button @click="dropdownOpen = !dropdownOpen" class="flex items-center gap-2 hover:bg-[#EDE0CB] rounded-lg p-1 transition">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-[#0B3D3A] to-[#D9622B] flex items-center justify-center text-white font-bold text-sm">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <svg class="w-4 h-4 text-[#5B6763]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="dropdownOpen" @click="dropdownOpen = false" class="absolute right-0 mt-2 w-48 rounded-lg shadow-lg bg-white border border-[#E2D8C4] z-50">
                        <a href="/profile/{{ $user->id }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-[#EDE0CB]">
                            <svg class="w-4 h-4 text-[#0B3D3A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Your Profile
                        </a>
                        <form action="/logout" method="POST">
                            @csrf
                            <button class="w-full text-left px-4 py-2 text-sm text-[#5B6763] hover:bg-[#EDE0CB]">Logout</button>
                        </form>
                    </div>
                </div>

                <!-- Plans Tab -->
                <div x-show="activeTab === 'plans'" :class="['tab-content', { active: activeTab === 'plans' }]">
                    @if($userPlans->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($userPlans as $plan)
                                <div class="plan-card">
                                    <div class="p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <h3 class="font-semibold text-[#1B2624] text-lg">{{ $plan->title ?: $plan->hotel_name }}</h3>
                                            <span class="badge-level bg-[#0B3D3A] text-white">Plan</span>
                                        </div>
                                        <p class="text-sm text-[#5B6763] mb-2">
                                            {{ number_format($plan->budget, 2) }} PHP • {{ $plan->total_days }} days
                                        </p>
                                        <div class="flex items-center justify-between mt-4">
                                            <span class="text-xs text-[#5B6763] font-mono">
                                                {{ $plan->created_at->diffForHumans() }}
                                            </span>
                                            <div class="flex gap-2">
                                                <a href="/plans/{{ $plan->id }}" class="text-xs font-semibold text-[#0B3D3A] hover:underline">View →</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-[var(--card)] rounded-xl border border-[var(--line)]">

                <!-- Level & XP Tab -->
                <div x-show="activeTab === 'level'" :class="['tab-content', { active: activeTab === 'level' }]">
                    <div class="max-w-2xl mx-auto">
                        <div class="text-center mb-8">
                            <div class="relative inline-block mb-4">
                                <div class="level-circle" :style="`background: linear-gradient(135deg, ${currentLevelData.color}, ${currentLevelData.bg});`">
                                    <span class="text-2xl">{{ $level }}</span>
                                </div>
                                <div class="absolute inset-0 rounded-full border-4 {{ $level >= 5 ? 'border-[#EDE0CB]' : 'border-[#0B3D3A] opacity-30' }}"></div>
                            </div>
                            <h2 class="font-display text-2xl font-bold text-[#1B2624]">{{ $currentLevelData['name'] }}</h2>
                            <p class="text-sm text-[#5B6763]">{{ $currentLevelData['difficulty'] }} level</p>
                        </div>

                        <div class="bg-[var(--card)] rounded-xl p-6 border border-[var(--line)]">
                            <div class="flex items-baseline justify-between mb-2">
                                <span class="text-sm font-semibold text-[#1B2624]">Current XP</span>
                                <span class="text-lg font-bold text-[#0B3D3A]">{{ number_format($points) }}</span>
                            </div>
                            <p class="text-xs text-[#5B6763] mb-4">
                                {{ $levelProgress }} / {{ number_format($nextLevelPoints) }} XP to {{ $nextLevelData['name'] }}
                            </p>
                            
                            <div class="xp-bar-bg">
                                <div 
                                    class="xp-bar-fill" 
                                    :style="`width: ${($levelProgress / $nextLevelPoints) * 100}%`"
                                    id="xpBar">
                                </div>
                            </div>
                            
                            <div class="flex justify-between mt-2">
                                <span class="text-xs text-[#5B6763]">Next Level: {{ $nextLevelData['name'] }}</span>
                                <span class="text-xs font-semibold text-[#0B3D3A]">
                                    {{ floor(($nextLevelPoints - $levelProgress) / 10) }} XP more required
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-5 gap-2 mt-6">
                            @foreach([1, 2, 3, 4, 5] as $lvl)
                                <div class="text-center">
                                    <div class="w-10 h-10 rounded-full mx-auto mb-1 flex items-center justify-center font-bold text-sm {{ $lvl <= $level ? 'bg-[#0B3D3A] text-white' : 'bg-[#EDE0CB] text-[#5B6763]' }}">
                                        {{ $lvl }}
                                    </div>
                                    <p class="text-xs {{ $lvl <= $level ? 'text-[#0B3D3A]' : 'text-[#5B6763]' }}">
                                        {{ $lvl === 1 ? 'Beginner' : ($lvl === 5 ? 'Master' : '') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                            <svg class="w-16 h-16 text-[#5B6763] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-[#5B6763]">You haven't created any plans yet.</p>
                            <a href="/planora" class="inline-block mt-4 text-[#0B3D3A] font-semibold hover:underline">Start planning →</a>
                        </div>
                    @endif
                </div>

            </div>

                <!-- Public Plans Tab -->
                <div x-show="activeTab === 'public'" :class="['tab-content', { active: activeTab === 'public' }]">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="font-display text-2xl font-bold text-[#1B2624]">Other Travelers' Plans</h2>
                        <a href="/public-plans" class="text-sm font-semibold text-[#0B3D3A] hover:underline">View all →</a>
                    </div>
                    
                    @if($otherPublicPlans->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($otherPublicPlans as $plan)
                                <div class="plan-card">
                                    <div class="p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <h3 class="font-semibold text-[#1B2624] text-lg">{{ $plan->title ?: $plan->hotel_name }}</h3>
                                            @if($plan->is_public)
                                                <span class="badge-level bg-[#E3F2FD] text-[#0B3D3A] text-xs font-bold">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                                                    Public
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-[#5B6763] mb-2">
                                            {{ number_format($plan->budget, 2) }} PHP • {{ $plan->total_days }} days
                                        </p>
                                        <div class="flex items-center justify-between mt-4">
                                            <span class="text-xs text-[#5B6763] font-mono">
                                                {{ $plan->created_at->diffForHumans() }}
                                            </span>
                                            <a href="/plans/{{ $plan->id }}" class="text-xs font-semibold text-[#0B3D3A] hover:underline">View plan →</a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-[var(--card)] rounded-xl border border-[var(--line)]">
                            <svg class="w-16 h-16 text-[#5B6763] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2zM8 10h8M8 14h5"/></svg>
                            <p class="text-[#5B6763]">No public plans available yet.</p>
                            <p class="text-sm text-[#5B6763] mt-2">Share your plans with <span class="text-[#0B3D3A] font-semibold">Public</span> to help others get inspired!</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('dashboard', () => ({
                    activeTab: 'profile',
                    welcomeMessage: '{{ $user->name }} — Your Travel Dashboard',
                    currentLevelData: {!! json_encode($currentLevelData) !!},
                    nextLevelData: {!! json_encode($nextLevelData) !!},
                }));
            });
        </script>
    </body>
</html>

        </div>
    </nav>


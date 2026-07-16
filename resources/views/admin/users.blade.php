<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Planora Admin — User Management</title>
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
        
        .admin-title {
            font-family: 'DM Serif Display', serif;
            color: var(--deep-teal);
        }
        
        .badge { 
            display: inline-flex; 
            align-items: center; 
            padding: 0.35rem 0.85rem; 
            border-radius: 0.5rem; 
            font-size: 0.75rem; 
            font-weight: 600; 
        }
        .badge-admin { 
            background: rgba(11,61,58,0.12); 
            color: var(--deep-teal);
            border: 1px solid rgba(11,61,58,0.15);
        }
        .badge-user { 
            background: rgba(124,145,135,0.15); 
            color: var(--sage);
            border: 1px solid rgba(124,145,135,0.15);
        }
        
        ::-webkit-scrollbar { height: 8px; width: 8px; }
        ::-webkit-scrollbar-track { background: var(--sand-deep); }
        ::-webkit-scrollbar-thumb { background: var(--sage); border-radius: 8px; }
        
        .user-avatar { 
            width: 40px; 
            height: 40px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, var(--deep-teal), var(--ember)); 
            color: var(--sand); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: 700; 
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .table-card {
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.3s ease;
        }
        .table-card:hover {
            box-shadow: var(--shadow-md);
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
                <a href="/admin/hotels" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <span class="text-sm font-medium">Hotels</span>
                </a>
                <a href="/admin/users" class="sidebar-link active flex items-center gap-3 px-3 py-2.5 rounded-lg">
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
        <div id="sidebarOverlay" class="sidebar-overlay fixed inset-0 bg-black/50 z-30 hidden lg:hidden" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main class="flex-1 p-4 lg:p-8 min-h-screen">
            <header class="mb-8">
                <div>
                    <h1 class="admin-title text-3xl font-semibold">User Management</h1>
                    <p class="text-sm text-[var(--ink-soft)] mt-1">View and manage registered users</p>
                </div>
            </header>
            
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
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Total Users</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $users->total() }}</p>
                </div>
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl" style="border-top-color:var(--ember);">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Admins</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $adminCount ?? $users->where('role', 'admin')->count() }}</p>
                </div>
                <div class="bg-[var(--card)] border border-[var(--line)] stat-card p-5 rounded-xl" style="border-top-color:var(--ember);">
                    <p class="text-xs font-mono uppercase tracking-wider text-[var(--sage)] mb-1">Regular Users</p>
                    <p class="text-3xl font-bold text-[var(--ink)] font-display">{{ $userCount ?? $users->where('role', 'user')->count() }}</p>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="bg-[var(--card)] border border-[var(--line)] rounded-xl table-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr>
                                <th class="w-12">#</th>
                                <th class="min-w-[200px]">User</th>
                                <th class="min-w-[250px]">Email</th>
                                <th class="w-32">Role</th>
                                <th class="w-40">Joined</th>
                                <th class="w-32">Status</th>
                                <th class="w-32">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td class="font-mono text-sm text-[var(--sage)]">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="user-avatar">{{ substr($user->name, 0, 2) }}</div>
                                        <div>
                                            <a href="/profile/{{ $user->id }}" class="font-medium text-sm text-[var(--deep-teal)] hover:underline">{{ $user->name }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-sm text-[var(--ink-soft)]">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'badge-admin' : 'badge-user' }}">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="text-sm text-[var(--ink-soft)]">{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    @if($user->email_verified_at)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium" style="color:#059669;">
                                        <span style="width:6px;height:6px;border-radius:50%;background:#059669;display:inline-block;"></span>
                                        Verified
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium" style="color:var(--sage);">
                                        <span style="width:6px;height:6px;border-radius:50%;background:var(--sage);display:inline-block;"></span>
                                        Unverified
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-1.5">
                                        <a href="/profile/{{ $user->id }}" class="btn-success px-3 py-1.5 rounded text-xs font-semibold">View</a>
                                        @if(auth()->id() !== $user->id)
                                        <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger px-3 py-1.5 rounded text-xs font-semibold">Delete</button>
                                        </form>
                                        @else
                                        <span class="text-xs italic self-center px-2" style="color:var(--sage);">You</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-16 text-[var(--ink-soft)]">
                                    <div style="width:64px;height:64px;margin:0 auto 16px;border-radius:50%;background:var(--sand-deep);display:flex;align-items:center;justify-content:center;">
                                        <svg class="w-8 h-8 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="font-semibold text-[var(--ink)]">No users found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
            overlay.classList.toggle('open');
        }
    </script>
</body>
</html>

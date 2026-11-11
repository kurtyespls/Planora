<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Planora Admin — @yield('title', 'Dashboard')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="stylesheet" href="/css/planora-design.css">
    <link rel="stylesheet" href="/css/planora-admin.css">
    @stack('styles')
</head>
<body>
    @include('admin.partials.sidebar')

    <div class="admin-main-wrap">
        <header class="admin-topbar px-4 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-[var(--deep-teal)] hover:bg-[var(--sand-deep)] transition" aria-label="Toggle Navigation">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div>
                    <h1 class="font-display text-xl lg:text-2xl font-bold text-[var(--deep-teal)] leading-none">
                        @yield('page_title', 'Admin Dashboard')
                    </h1>
                    <p class="text-xs text-[var(--ink-soft)] mt-0.5">
                        @yield('page_subtitle', 'Planora Dagupan City')
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="/planora" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[var(--deep-teal)] bg-[var(--card)] border border-[var(--line)] rounded-lg hover:border-[var(--deep-teal)] transition">
                    <span>Visit App</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                <a href="/my-plans" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-[var(--deep-teal)] bg-[var(--card)] border border-[var(--line)] rounded-lg hover:border-[var(--deep-teal)] transition">
                    <span>My Plans</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M17 6H9c-4 0-4 4 0 8h8c4-4 4-8 0-8z"/></svg>
                </a>
                @yield('topbar_actions')
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-8">
            @if(session('success'))
            <div id="toast-success-{{ session('id') ?: 'unknown' }}" class="mb-6 p-4 rounded-xl flex items-center gap-3 text-sm shadow-sm hidden" style="background:#ECFDF5;border:1px solid #A7F3D0;color:#065F46;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div id="toast-error-{{ session('id') ?: 'unknown' }}" class="mb-6 p-4 rounded-xl flex items-center gap-3 text-sm shadow-sm hidden" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('modals')

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }
    </script>

    <!-- Toast Notification System -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

    <script>
        // Show toast from session messages on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Show success toast
            const sessionId = "{{ session('id') ?: 'unknown' }}";
            const successEl = document.getElementById('toast-success-' + sessionId);
            if (successEl) {
                successEl.classList.remove('hidden');
                showToast(successEl.querySelector('span').textContent, 'success');
                setTimeout(() => {
                    successEl.classList.add('hidden');
                }, 5000);
            }
            
            // Show error toast
            const errorEl = document.getElementById('toast-error-' + sessionId);
            if (errorEl) {
                errorEl.classList.remove('hidden');
                showToast(errorEl.querySelector('span').textContent, 'error');
                setTimeout(() => {
                    errorEl.classList.add('hidden');
                }, 5000);
            }
        });

        // Global toast function
        window.showToast = function(message, type = 'info', duration = 4000) {
            const colors = {
                success: 'bg-green-50 text-green-700 border border-green-200',
                error: 'bg-red-50 text-red-700 border border-red-200',
                warning: 'bg-amber-50 text-amber-700 border border-amber-200',
                info: 'bg-blue-50 text-blue-700 border border-blue-200'
            };
            
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-2 shadow-xl animate-fade-in-up ${colors[type] || colors.info}`;
            toast.innerHTML = `<span>${message}</span>`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transition = 'opacity 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, duration);
        };
    </script>
    
    @stack('scripts')
</body>
</html>

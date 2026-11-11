$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$css4 = @'
        /* ── Stat Overview ── */
        .stat-overview {
            @apply grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8;
        }
        .stat-item {
            @apply bg-[var(--card)] border border-[var(--line)] rounded-xl px-4 py-4 transition-all duration-300 hover:shadow-md;
        }
        .stat-item .label {
            @apply text-xs font-semibold text-[var(--ink-soft)] uppercase tracking-wider;
        }
        .stat-item .value {
            @apply mt-1 text-2xl font-bold text-[var(--deep-teal)];
        }
        .stat-item .change {
            @apply mt-1 text-xs font-medium text-emerald-600;
        }
        /* ── User Avatar ── */
        .user-avatar {
            @apply w-10 h-10 rounded-full bg-gradient-to-br from-[var(--deep-teal)] to-[var(--ember)] flex items-center justify-center text-white font-bold text-sm shadow-md;
        }
        /* ── Empty State ── */
        .empty-state {
            @apply text-center py-12 bg-[var(--card)] rounded-xl border border-[var(--line)];
        }
        .empty-state svg { width: 64px; height: 64px; color: var(--sage); }
        .empty-state h3 { @apply font-display text-xl font-bold text-[var(--ink)]; }
        .empty-state p { @apply mt-2 text-sm text-[var(--ink-soft)]; }
        /* ── Responsive fixes ── */
        @media (max-width: 767px) {
            .stat-overview { grid-template-columns: 1fr; }
            .tab-button { @apply px-4 py-2.5 text-sm; }
            .level-circle { width: 80px; height: 80px; font-size: 1.6rem; }
            .plan-card { @apply mb-3; }
        }
        /* ── Scrollbar styling ── */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: var(--sand); }
        ::-webkit-scrollbar-thumb { background: var(--sage); border-radius: var(--radius-full); }
        ::-webkit-scrollbar-thumb:hover { background: var(--ink-soft); }
        /* ── Focus states ── */
        .focus-visible-ring:focus-visible {
            outline: 2px solid var(--ember);
            outline-offset: 2px;
            border-radius: inherit;
        }
    </style>
</head>
<body class="bg-[#F6EDE0] min-h-screen">
    <nav class="top-nav border-b border-[var(--line)]">
        <div class="top-nav-inner max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/planora" class="brand flex items-center gap-2 text-[var(--deep-teal)] font-bold text-lg">
                <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14 2L22 8V16C22 20.42 18.42 24 14 26C9.58 24 6 20.42 6 16V8L14 2Z" fill="white" stroke="var(--deep-teal)" stroke-width="1.5"/>
                    <path d="M14 8V16M14 16L17 13M14 16L11 13" stroke="var(--deep-teal)" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="14" cy="8" r="2" fill="var(--ember)"/>
                </svg>
                <span>Planora</span>
            </a>
            <div class="flex items-center gap-3">
                <a href="/my-plans" class="text-sm font-medium text-[var(--ink-soft)] hover:text-[var(--deep-teal)] transition-colors">My Plans</a>
                <a href="/public-plans" class="text-sm font-medium text-[var(--ink-soft)] hover:text-[var(--deep-teal)] transition-colors">Public Plans</a>
                <div class="flex items-center gap-2 relative">
                    <span class="text-sm font-medium text-[var(--ink)]" x-text="welcomeMessage.split('—')[0].trim()"></span>
                    <div class="user-avatar"></div>
                </div>
            </div>
        </div>
    </nav>
'@
$css4 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "CSS4 + Nav written: $($css4.Length) chars"
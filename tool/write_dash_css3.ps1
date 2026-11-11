$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$css3 = @'
        /* ── Plan Card Enhancements ── */
        .plan-card {
            @apply bg-[var(--card)] border border-[var(--line)] rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group;
        }
        .plan-card:hover {
            transform: translateY(-3px);
            border-color: rgba(217, 98, 43, 0.3);
            box-shadow: var(--shadow-lg);
        }
        .plan-card::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            opacity: 0;
            transition: opacity var(--transition-base);
            background: linear-gradient(135deg, rgba(13, 90, 85, 0.05), rgba(217, 98, 43, 0.05));
            pointer-events: none;
        }
        .plan-card:hover::before {
            opacity: 1;
        }
        .plan-card-content {
            position: relative;
            z-index: 1;
        }
        .plan-card .view-btn {
            @apply opacity-0 group-hover:opacity-100 transition-opacity duration-300;
            text-decoration: none;
            font-weight: 600;
            color: var(--deep-teal);
        }
        /* ── Badge ── */
        .badge-level {
            @apply inline-flex items-center px-3 py-1 rounded-full text-xs font-bold;
        }
        .badge-public {
            background: linear-gradient(135deg, #E3F2FD, #BBD9F0);
            color: var(--deep-teal);
        }
        .badge-public svg { width: 12px; height: 12px; }
'@
$css3 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "CSS3 written: $($css3.Length) chars"
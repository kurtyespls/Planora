$f = "C:\Users\Kurt\Desktop\planorabeta\Planora\\resources\\views\\dashboard\\index.blade.php"
$css2 = @'
        /* ── Level Circle ── */
        .level-circle {
            position: relative;
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            color: var(--deep-teal);
            box-shadow: 0 8px 24px rgba(11, 61, 58, 0.15);
        }
        .level-circle-ring {
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 3px solid var(--line);
            box-sizing: border-box;
        }
        .level-circle-inner {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }
        /* ── Enhanced XP Bar ── */
        .xp-bar-wrap {
            @apply relative overflow-hidden rounded-full h-3;
            background: var(--sand-deep);
        }
        .xp-bar-fill {
            @apply h-full rounded-full bg-gradient-to-r from-[#D9622B] to-[#0B3D3A] transition-all duration-700 ease-out;
            box-shadow: 0 0 12px rgba(217, 98, 43, 0.3);
        }
        .xp-label {
            @apply flex items-center justify-center gap-2 font-semibold text-sm;
        }
'@
$css2 | Out-File -FilePath $f -Encoding UTF8 -Append
Write-Host "CSS2 part1 written: $($css2.Length) chars"
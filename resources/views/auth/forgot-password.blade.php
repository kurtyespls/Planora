<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — Planora</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        .input-group {
            position: relative;
            margin-bottom: 20px;
        }
        .input-group input {
            width: 100%;
            padding: 16px 44px 16px 16px;
            border: 1.5px solid var(--line);
            border-radius: var(--radius-sm);
            background: var(--card);
            font-size: 0.95rem;
            color: var(--ink);
            transition: all 0.25s ease;
            outline: none;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .input-group input:focus {
            border-color: var(--deep-teal);
            box-shadow: 0 0 0 4px rgba(11,61,58,0.08), 0 0 0 8px rgba(11,61,58,0.04);
        }
        .input-group input.error {
            border-color: var(--ember);
            box-shadow: 0 0 0 4px rgba(217,98,43,0.08);
        }
        .input-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .btn-gradient {
            background: linear-gradient(135deg, var(--deep-teal), var(--deep-teal-ink));
            color: var(--sand);
            border: 0;
            padding: 16px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: 1rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(11,61,58,0.35);
        }
        .btn-gradient:active { transform: translateY(0); }
        .btn-gradient::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.08), transparent);
            transform: translateX(-100%);
        }
        .btn-gradient:hover::after { transform: translateX(100%); transition: transform 0.6s; }

        .auth-art-quote {
            font-family: 'DM Serif Display', serif;
            font-size: clamp(1.8rem, 3.5vw, 3rem);
            line-height: 1.15;
            color: white;
            margin-bottom: 20px;
        }
        .auth-art-quote cite {
            display: block;
            font-size: 0.9rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-style: normal;
            font-weight: 400;
            color: rgba(255,255,255,0.6);
            margin-top: 12px;
        }

        @keyframes inputFocusGlow {
            0% { box-shadow: 0 0 0 0 rgba(11,61,58,0.1); }
            100% { box-shadow: 0 0 0 8px rgba(11,61,58,0.04); }
        }

        @media (max-width: 767px) {
            .auth-quote-mobile {
                display: block;
                text-align: center;
                padding: 24px 20px;
                background: linear-gradient(135deg, var(--deep-teal-ink), var(--deep-teal));
                border-radius: var(--radius-md);
                margin-bottom: 24px;
                color: white;
            }
            .auth-quote-mobile h2 {
                font-family: 'DM Serif Display', serif;
                font-size: 1.6rem;
                line-height: 1.2;
                margin-bottom: 8px;
            }
            .auth-quote-mobile p {
                font-size: 0.85rem;
                opacity: 0.8;
            }
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <!-- ── Left: Image Side ── -->
        <aside class="auth-art" style="order:1;">
            <img src="/images/dagupan/beach-scene.jpg" alt="Dagupan beach" onerror="this.parentElement.style.background='linear-gradient(145deg, var(--deep-teal-ink), var(--deep-teal))'">
            <div class="auth-art-copy">
                <!-- Travel Quote -->
                <div class="auth-art-quote">
                    "Every journey begins with a single step."
                    <cite>— Planora Travel Guide</cite>
                </div>
                <!-- Weather Widget -->
                <div class="weather-widget">
                    <span style="font-size:1.8rem;line-height:1;">☀️</span>
                    <div>
                        <span style="font-weight:700;font-size:1.1rem;">29°C</span>
                        <span style="display:block;font-size:0.75rem;opacity:0.8;">Dagupan, Pangasinan</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Right: Forgot Password Card ── -->
        <section class="auth-panel" style="order:2;">
            <div class="auth-card">
                <!-- Mobile Quote (hidden on desktop) -->
                <div class="auth-quote-mobile" style="display:none;">
                    <h2>Reset your password</h2>
                    <p>We'll help you get back to planning your trip.</p>
                </div>

                <a href="/planora" class="brand" style="margin-bottom:8px;"><span class="brand-mark">⌁</span><span>planora</span></a>
                <h1>Forgot password?</h1>
                <p class="auth-subtitle">Enter your email to receive a reset link.</p>

                @if (session('status'))
                <div class="mb-6 p-4 rounded-xl" style="background:#E8F5E9;border:1px solid #A5D6A7;color:#1B5E20;font-size:0.85rem;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.85rem;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                        <span style="font-weight:600;">Please fix the following:</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p style="padding-left:24px;margin-bottom:2px;">• {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
                    @csrf

                    <div class="input-group">
                        <label for="email">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus placeholder="you@example.com" autocomplete="email">
                    </div>

                    <button type="submit" class="btn-gradient">
                        Send reset link →
                    </button>
                </form>

                <p class="text-sm text-[var(--ink-soft)] mt-6 text-center">
                    <a href="/login" class="font-bold text-[var(--deep-teal)]" style="transition:color 0.2s;" onmouseover="this.style.color='var(--ember)'" onmouseout="this.style.color='var(--deep-teal)'">Back to login</a>
                </p>
            </div>
        </section>
    </main>

    <script>
        // ── Smooth validation feedback ──
        document.querySelectorAll('.input-group input').forEach(input => {
            input.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
            input.addEventListener('input', function() {
                this.classList.remove('error');
            });
        });

        // ── Show mobile quote on small screens ──
        if (window.innerWidth < 768) {
            document.querySelector('.auth-quote-mobile').style.display = 'block';
        }

        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
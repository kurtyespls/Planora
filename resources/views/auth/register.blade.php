<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Planora</title>
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
            padding: 14px 44px 14px 16px;
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
        .input-group input.valid {
            border-color: #059669;
            box-shadow: 0 0 0 4px rgba(5,150,105,0.08);
        }
        .input-group label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }
        .input-group .toggle-pw {
            position: absolute;
            right: 14px;
            bottom: 14px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--sage);
            padding: 0;
            font-size: 1.1rem;
            line-height: 1;
            transition: color 0.2s;
        }
        .input-group .toggle-pw:hover { color: var(--deep-teal); }

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

        /* ── Password strength bar ── */
        .pw-strength {
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pw-strength-bar {
            flex: 1;
            height: 4px;
            background: var(--line);
            border-radius: 4px;
            overflow: hidden;
        }
        .pw-strength-fill {
            height: 100%;
            width: 0%;
            border-radius: 4px;
            transition: width 0.4s ease, background 0.4s ease;
        }
        .pw-strength-label {
            font-size: 0.72rem;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            min-width: 56px;
            text-align: right;
        }

        /* ── Password match indicator ── */
        .pw-match {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            margin-top: 6px;
            transition: all 0.3s ease;
        }
        .pw-match-icon {
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.55rem;
            transition: all 0.3s ease;
        }

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
            <img src="/images/dagupan/beach-aerial.jpg" alt="Dagupan aerial view" onerror="this.parentElement.style.background='linear-gradient(145deg, var(--deep-teal-ink), var(--deep-teal))'">
            <div class="auth-art-copy">
                <div class="auth-art-quote">
                    "Every journey begins<br>with a single step — or click."
                    <cite>— Planora Travel Guide</cite>
                </div>
                <div class="weather-widget">
                    <span style="font-size:1.8rem;line-height:1;"></span>
                    <div>
                        <span style="font-weight:700;font-size:1.1rem;">Discover Dagupan</span>
                        <span style="display:block;font-size:0.75rem;opacity:0.8;">Your adventure starts here</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- ── Right: Register Card ── -->
        <section class="auth-panel" style="order:2;">
            <div class="auth-card">
                <!-- Mobile Quote (hidden on desktop) -->
                <div class="auth-quote-mobile" style="display:none;">
                    <h2>Let's go places</h2>
                    <p>Create your account and make travel feel effortless.</p>
                </div>

                <a href="/planora" class="brand" style="margin-bottom:8px;"><span class="brand-mark">⌁</span><span>planora</span></a>
                <h1>Let's go places.</h1>
                <p class="auth-subtitle">Create your account and make travel feel effortless.</p>

                @if ($errors->any())
                <div class="mb-6 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;font-size:0.85rem;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <span style="font-weight:600;">Please fix the following:</span>
                    </div>
                    @foreach ($errors->all() as $error)
                        <p style="padding-left:24px;margin-bottom:2px;">• {{ $error }}</p>
                    @endforeach
                </div>
                @endif

                <form method="POST" action="/register" novalidate>
                    @csrf

                    <div class="input-group">
                        <label for="name">Your name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus placeholder="Full name" autocomplete="name">
                    </div>

                    <div class="input-group">
                        <label for="email">Email address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="you@example.com" autocomplete="email">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="input-group" style="margin-bottom:0;">
                            <label for="password">Password</label>
                            <div style="position:relative;">
                                <input type="password" name="password" id="password" required minlength="6" placeholder="Create password" autocomplete="new-password">
                                <button type="button" class="toggle-pw" onclick="togglePassword('password', this)" aria-label="Toggle password visibility">👁️</button>
                            </div>
                            <!-- Password Strength -->
                            <div class="pw-strength" id="pw-strength">
                                <div class="pw-strength-bar">
                                    <div class="pw-strength-fill" id="pw-strength-fill"></div>
                                </div>
                                <span class="pw-strength-label" id="pw-strength-label">Weak</span>
                            </div>
                        </div>

                        <div class="input-group" style="margin-bottom:0;">
                            <label for="password_confirmation">Confirm</label>
                            <div style="position:relative;">
                                <input type="password" name="password_confirmation" id="password_confirmation" required minlength="6" placeholder="Confirm password" autocomplete="new-password">
                                <button type="button" class="toggle-pw" onclick="togglePassword('password_confirmation', this)" aria-label="Toggle password visibility">👁️</button>
                            </div>
                            <!-- Match Indicator -->
                            <div class="pw-match" id="pw-match" style="opacity:0;">
                                <span class="pw-match-icon" id="pw-match-icon" style="background:var(--line);color:white;">✓</span>
                                <span id="pw-match-text" style="color:var(--ink-soft);">Passwords match</span>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-gradient" style="margin-top:24px;">
                        Create my account →
                    </button>
                </form>

                <p class="text-sm text-[var(--ink-soft)] mt-6 text-center">Already exploring? <a href="/login" class="font-bold text-[var(--deep-teal)]" style="transition:color 0.2s;" onmouseover="this.style.color='var(--ember)'" onmouseout="this.style.color='var(--deep-teal)'">Sign in</a></p>
            </div>
        </section>
    </main>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.textContent = '🙈';
            } else {
                input.type = 'password';
                btn.textContent = '👁️';
            }
        }

        // ── Password strength ──
        const pwInput = document.getElementById('password');
        const pwConfirm = document.getElementById('password_confirmation');
        const strengthFill = document.getElementById('pw-strength-fill');
        const strengthLabel = document.getElementById('pw-strength-label');
        const matchEl = document.getElementById('pw-match');
        const matchIcon = document.getElementById('pw-match-icon');
        const matchText = document.getElementById('pw-match-text');

        function evaluateStrength(pw) {
            let score = 0;
            if (pw.length >= 6) score += 1;
            if (pw.length >= 10) score += 1;
            if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score += 1;
            if (/\d/.test(pw)) score += 1;
            if (/[^a-zA-Z0-9]/.test(pw)) score += 1;
            return score;
        }

        pwInput.addEventListener('input', function() {
            const score = evaluateStrength(this.value);
            const pct = (score / 5) * 100;
            strengthFill.style.width = pct + '%';

            if (score <= 1) {
                strengthFill.style.background = '#EF4444';
                strengthLabel.textContent = 'Weak';
                strengthLabel.style.color = '#EF4444';
            } else if (score <= 2) {
                strengthFill.style.background = '#F59E0B';
                strengthLabel.textContent = 'Fair';
                strengthLabel.style.color = '#F59E0B';
            } else if (score <= 3) {
                strengthFill.style.background = '#10B981';
                strengthLabel.textContent = 'Good';
                strengthLabel.style.color = '#10B981';
            } else {
                strengthFill.style.background = '#059669';
                strengthLabel.textContent = 'Strong';
                strengthLabel.style.color = '#059669';
            }

            checkMatch();
        });

        pwConfirm.addEventListener('input', checkMatch);

        function checkMatch() {
            const pw = pwInput.value;
            const confirm = pwConfirm.value;

            if (!confirm) {
                matchEl.style.opacity = '0';
                return;
            }

            matchEl.style.opacity = '1';

            if (pw === confirm && pw.length > 0) {
                matchIcon.style.background = '#059669';
                matchIcon.textContent = '✓';
                matchText.textContent = 'Passwords match';
                matchText.style.color = '#059669';
                pwConfirm.classList.remove('error');
                pwConfirm.classList.add('valid');
            } else if (pw !== confirm) {
                matchIcon.style.background = '#EF4444';
                matchIcon.textContent = '✗';
                matchText.textContent = 'Passwords do not match';
                matchText.style.color = '#EF4444';
                pwConfirm.classList.remove('valid');
                pwConfirm.classList.add('error');
            } else {
                matchIcon.style.background = 'var(--line)';
                matchIcon.textContent = '⋯';
                matchText.textContent = 'Enter confirmation';
                matchText.style.color = 'var(--ink-soft)';
                pwConfirm.classList.remove('error', 'valid');
            }
        }

        // ── Validation feedback ──
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
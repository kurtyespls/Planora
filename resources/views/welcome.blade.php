<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planora — Travel beautifully</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/planora-design.css">
    <style>
        /* ── Mobile-First Responsive Overrides ── */
        
        /* ── Navigation ── */
        .nav-container { padding-left: 16px !important; padding-right: 16px !important; }
        .nav-btn { padding: 8px 16px !important; font-size: 0.75rem !important; }
        .nav-btn-plan { padding: 8px 18px !important; font-size: 0.75rem !important; }
        
        /* ── Floating Cards ── */
        @media (max-width: 1024px) {
            .art-card.one { top: 14% !important; right: 3% !important; padding: 8px 14px !important; }
            .art-card.one .art-card-icon { font-size: 1.2rem !important; }
            .art-card.one .art-card-text { font-size: 0.75rem !important; }
            .art-card.one span:last-child { font-size: 0.6rem !important; }
            
            .art-card.two { bottom: 30% !important; right: 3% !important; padding: 8px 14px !important; }
            .art-card.two .art-card-text { font-size: 0.75rem !important; }
            .art-card.two span:last-child { font-size: 0.6rem !important; }
            
            .art-card[style*="top:40%"] { 
                top: 35% !important; 
                left: 3% !important; 
                padding: 6px 14px !important; 
            }
            .art-card[style*="top:40%"] .art-card-text { font-size: 0.7rem !important; }
            .art-card[style*="top:40%"] span:last-child { font-size: 0.55rem !important; }
        }
        
        @media (max-width: 640px) {
            .art-card.one { 
                top: 12% !important; 
                right: 2% !important; 
                padding: 6px 12px !important; 
                border-radius: 12px !important;
            }
            .art-card.one .art-card-icon { font-size: 1rem !important; }
            .art-card.one .art-card-text { font-size: 0.7rem !important; }
            .art-card.one span:last-child { display: none !important; }
            
            .art-card[style*="top:40%"] { 
                display: none !important; 
            }
            
            .art-card.two { 
                bottom: 22% !important; 
                right: 2% !important; 
                padding: 6px 12px !important; 
                border-radius: 12px !important;
            }
            .art-card.two .art-card-text { font-size: 0.7rem !important; }
            .art-card.two span:last-child { display: none !important; }
        }
        
        @media (max-width: 380px) {
            .art-card.one { top: 10% !important; }
            .art-card.two { display: none !important; }
        }
        
        /* ── Hero Content ── */
        @media (max-width: 640px) {
            .hero-content { padding: 20px 16px !important; }
            .hero-content .landing-title { 
                font-size: clamp(2.2rem, 10vw, 3.2rem) !important; 
                margin: 8px auto 14px !important;
            }
            .hero-content .landing-copy { 
                font-size: 0.95rem !important; 
                line-height: 1.7 !important;
            }
            .hero-content .flex-wrap.gap-4 { 
                gap: 10px !important; 
                flex-direction: column !important; 
                align-items: center !important;
            }
            .hero-content .flex-wrap.gap-4 a { 
                width: 100% !important; 
                max-width: 280px !important; 
                justify-content: center !important;
                padding: 14px 24px !important;
                font-size: 0.9rem !important;
            }
            .hero-content .flex-wrap.gap-6 { 
                gap: 12px !important; 
                flex-wrap: wrap !important; 
                justify-content: center !important;
            }
            .hero-content .flex-wrap.gap-6 span { 
                font-size: 0.75rem !important; 
            }
            .hero-content .flex-wrap.gap-6 .w-1\\.h-1 { 
                display: none !important; 
            }
        }
        
        /* ── Stats Bar ── */
        @media (max-width: 640px) {
            .stats-bar { 
                margin-top: -20px !important; 
                margin-left: 12px !important; 
                margin-right: 12px !important; 
                border-radius: 16px !important;
            }
            .stat-number { font-size: 1.5rem !important; }
            .stat-label { font-size: 0.65rem !important; }
            .stat-item { padding: 14px 8px !important; }
        }
        
        @media (max-width: 380px) {
            .stats-bar { grid-template-columns: repeat(2, 1fr) !important; }
            .stat-divider:nth-child(4) { display: none !important; }
            .stat-divider:nth-child(6) { display: none !important; }
        }
        
        /* ── Features Strip ── */
        @media (max-width: 640px) {
            .feature-strip { 
                padding: 40px 12px !important; 
                gap: 14px !important; 
            }
            .feature-tile { 
                padding: 20px !important; 
                border-radius: 18px !important; 
            }
            .feature-icon { 
                width: 40px !important; 
                height: 40px !important; 
                border-radius: 12px !important;
                margin-bottom: 14px !important;
            }
            .feature-icon svg { width: 20px !important; height: 20px !important; }
            .feature-tile h3 { font-size: 1rem !important; }
            .feature-tile p { font-size: 0.82rem !important; }
        }
        
        /* ── How It Works ── */
        @media (max-width: 640px) {
            .how-section { padding: 40px 12px !important; }
            .how-section .text-center.mb-16 { margin-bottom: 32px !important; }
            .how-section h2 { font-size: 1.8rem !important; }
            .how-section .text-lg { font-size: 0.9rem !important; }
            .how-step { 
                padding: 20px !important; 
                gap: 16px !important; 
                border-radius: 18px !important;
            }
            .step-number { 
                width: 42px !important; 
                height: 42px !important; 
                min-width: 42px !important;
                font-size: 1rem !important;
                border-radius: 12px !important;
            }
            .how-step h3 { font-size: 1rem !important; }
            .how-step p { font-size: 0.8rem !important; }
            .grid.md\\:grid-cols-3 { gap: 14px !important; }
        }
        
        /* ── Gallery ── */
        @media (max-width: 640px) {
            .dagupan-gallery { padding: 40px 12px 60px !important; }
            .dagupan-gallery h2 { font-size: 1.8rem !important; }
            .dagupan-gallery .text-lg { font-size: 0.9rem !important; }
            .dagupan-gallery .grid { 
                grid-template-columns: repeat(3, 1fr) !important; 
                gap: 8px !important; 
            }
            .dagupan-gallery .group { border-radius: 12px !important; }
            .dagupan-gallery .group:nth-child(2) { 
                grid-column: span 2 !important; 
                grid-row: span 1 !important; 
            }
        }
        
        @media (max-width: 480px) {
            .dagupan-gallery .grid { 
                grid-template-columns: repeat(2, 1fr) !important; 
            }
        }
        
        /* ── Testimonials ── */
        @media (max-width: 640px) {
            .max-w-6xl.mx-auto.px-6.pb-20.reveal { 
                padding-left: 12px !important; 
                padding-right: 12px !important; 
                padding-bottom: 40px !important; 
            }
            .max-w-6xl.mx-auto.px-6.pb-20.reveal h2 { 
                font-size: 1.8rem !important; 
            }
            .max-w-6xl.mx-auto.px-6.pb-20.reveal .text-lg { 
                font-size: 0.9rem !important; 
            }
            .grid.md\\:grid-cols-3.gap-6 { 
                gap: 14px !important; 
            }
            .bg-white.border.rounded-2xl.p-6 { 
                padding: 16px !important; 
            }
            .bg-white.border.rounded-2xl.p-6 p { 
                font-size: 0.82rem !important; 
            }
        }
        
        /* ── FAQ ── */
        @media (max-width: 640px) {
            .max-w-3xl.mx-auto.px-6.pb-20.reveal { 
                padding-left: 12px !important; 
                padding-right: 12px !important; 
                padding-bottom: 40px !important; 
            }
            .max-w-3xl.mx-auto.px-6.pb-20.reveal h2 { 
                font-size: 1.8rem !important; 
            }
            .max-w-3xl.mx-auto.px-6.pb-20.reveal .text-lg { 
                font-size: 0.9rem !important; 
            }
            details summary { 
                padding: 14px 16px !important; 
                font-size: 0.9rem !important; 
            }
            details div { 
                padding: 0 16px 14px !important; 
                font-size: 0.85rem !important; 
            }
        }
        
        /* ── CTA Banner ── */
        @media (max-width: 640px) {
            .cta-banner { 
                margin-bottom: 40px !important; 
                padding: 0 12px !important; 
            }
            .cta-banner-inner { 
                padding: 40px 20px !important; 
                border-radius: 20px !important; 
            }
            .cta-banner-inner h2 { 
                font-size: 1.8rem !important; 
            }
            .cta-banner-inner p { 
                font-size: 0.9rem !important; 
                margin-bottom: 24px !important; 
            }
            .cta-banner-inner a { 
                padding: 14px 28px !important; 
                font-size: 0.9rem !important; 
                width: 100% !important;
                max-width: 280px !important;
                justify-content: center !important;
            }
        }
        
        /* ── Footer ── */
        @media (max-width: 640px) {
            .site-footer { padding: 40px 0 0 !important; }
            .footer-grid { 
                grid-template-columns: 1fr !important; 
                gap: 24px !important; 
                padding-bottom: 24px !important; 
            }
            .footer-brand { grid-column: 1 !important; }
            .footer-desc { font-size: 0.8rem !important; margin-bottom: 16px !important; }
            .footer-links h4 { margin-bottom: 12px !important; font-size: 0.75rem !important; }
            .footer-links a { font-size: 0.82rem !important; padding: 4px 0 !important; }
            .footer-bottom { font-size: 0.72rem !important; padding: 16px 0 !important; }
            .social-link { width: 36px !important; height: 36px !important; }
        }
        
        /* ── Navigation Mobile ── */
        @media (max-width: 640px) {
            nav[style*="height:72px"] { height: 60px !important; }
            nav[style*="height:72px"] .brand { font-size: 1rem !important; gap: 6px !important; }
            nav[style*="height:72px"] .brand-mark { 
                width: 30px !important; 
                height: 30px !important; 
                font-size: 0.8rem !important;
                border-radius: 10px !important;
            }
            nav[style*="height:72px"] .gap-3 { gap: 6px !important; }
            nav[style*="height:72px"] .px-5 { 
                padding-left: 12px !important; 
                padding-right: 12px !important; 
                font-size: 0.7rem !important; 
            }
            nav[style*="height:72px"] .px-6 { 
                padding-left: 14px !important; 
                padding-right: 14px !important; 
                font-size: 0.7rem !important; 
            }
        }
        
        @media (max-width: 380px) {
            nav[style*="height:72px"] .px-5 { display: none !important; }
            nav[style*="height:72px"] .px-6 { font-size: 0.65rem !important; padding: 6px 12px !important; }
        }
        
        /* ── Hero image fallback ── */
        @media (max-width: 640px) {
            .landing-hero { min-height: 100dvh !important; }
        }
        
        /* ── General spacing reduction ── */
        @media (max-width: 640px) {
            .pb-20 { padding-bottom: 40px !important; }
            .mb-14 { margin-bottom: 24px !important; }
            .mb-16 { margin-bottom: 28px !important; }
        }
    </style>
</head>
<body>
    <!-- ── Navigation ── -->
    <nav class="travel-nav" style="position:fixed;top:0;left:0;right:0;z-index:50;background:rgba(11,61,58,0.08);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,255,255,0.1);height:72px;">
        <div class="nav-container" style="max-width:1180px;margin:auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between;height:100%;">
            <a href="/" class="brand" style="color:white;"><span class="brand-mark" style="background:rgba(255,255,255,0.15);color:white;box-shadow:none;">⌁</span><span style="font-weight:800;">planora</span></a>
            <div class="flex items-center gap-2 sm:gap-3">
                <a href="/login" class="nav-btn px-4 sm:px-5 py-2 sm:py-2.5 text-xs sm:text-sm font-bold text-white/80 hover:text-white transition" style="text-decoration:none;">Sign in</a>
                <a href="/register" class="nav-btn-plan px-5 sm:px-6 py-2 sm:py-2.5 rounded-full text-xs sm:text-sm font-bold" style="background:var(--ember);color:white;text-decoration:none;box-shadow:0 8px 24px rgba(217,98,43,0.3);transition:all 0.25s ease;" onmouseover="this.style.background='var(--ember-deep)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 12px 32px rgba(217,98,43,0.4)';" onmouseout="this.style.background='var(--ember)';this.style.transform='translateY(0)';this.style.boxShadow='0 8px 24px rgba(217,98,43,0.3)';">Plan a trip</a>
            </div>
        </div>
    </nav>

    <main>
        <!-- ── Hero ── -->
        <section class="landing-hero">
            <div class="hero-art" aria-label="Welcome to Dagupan">
                <div class="hero-art-bg">
                    <img src="/images/dagupan/welcome-sign.jpg" alt="Welcome to Dagupan sign" class="w-full h-full object-cover object-center" onerror="this.style.display='none'">
                    <div class="hero-art-fallback" style="position:absolute;inset:0;background:linear-gradient(135deg,#0E2E2B,#0B3D3A);"></div>
                </div>
                <div class="hero-art-overlay"></div>
                
                <!-- Floating Weather Card -->
                <div class="art-card one" style="top:18%;right:5%;">
                    <span class="art-card-icon">☀️</span>
                    <div>
                        <span class="art-card-text" style="font-size:0.9rem;font-weight:700;">29°C · Dagupan</span>
                        <span style="display:block;font-size:0.7rem;color:var(--ink-soft);margin-top:2px;">Sunny · Perfect for exploring</span>
                    </div>
                </div>
                
                <!-- Floating AI Badge -->
                <div class="art-card" style="top:40%;left:4%;padding:10px 18px;animation-delay:0.45s;opacity:0;">
                    <span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:10px;background:linear-gradient(135deg,var(--deep-teal),var(--ember));color:white;font-size:0.85rem;font-weight:700;">AI</span>
                    <div>
                        <span class="art-card-text" style="font-size:0.8rem;">Smart itineraries</span>
                        <span style="display:block;font-size:0.65rem;color:var(--ink-soft);margin-top:1px;">Powered by AI</span>
                    </div>
                </div>
                

                <div class="hero-scroll" aria-hidden="true">
                    <span class="scroll-mouse"><span class="scroll-dot"></span></span>
                </div>
            </div>
            
            <div class="hero-content">
                <div class="eyebrow" style="color:var(--ember);font-size:0.75rem;letter-spacing:0.2em;">Your local travel companion</div>
                <h1 class="landing-title reveal" style="font-size:clamp(2.8rem, 8vw, 6.5rem);">Less planning.<br><em style="color:var(--ember);font-style:normal;">More going.</em></h1>
                <p class="landing-copy reveal-2" style="font-size:1.1rem;max-width:600px;">Discover your way in Dagupan. Planora turns your budget, schedule, and favorite kind of adventure into one effortless itinerary.</p>
                <div class="flex flex-wrap gap-3 sm:gap-4 mt-8 sm:mt-10 justify-center reveal-2">
                    <a href="/register" class="button-primary px-7 sm:px-8 py-3.5 sm:py-4 rounded-full font-bold text-sm sm:text-base" style="background:var(--ember);box-shadow:0 12px 32px rgba(217,98,43,0.35);">Start exploring →</a>
                </div>
                <div class="flex items-center justify-center gap-4 sm:gap-6 mt-8 sm:mt-10 text-xs sm:text-sm text-white/60 reveal-2">
                    <span class="flex items-center gap-1.5 sm:gap-2"><span style="width:16px;height:16px;border-radius:50%;background:rgba(217,98,43,0.3);display:flex;align-items:center;justify-content:center;font-size:0.55rem;">✓</span> Budget-aware</span>
                    <span class="w-0.5 h-0.5 rounded-full bg-white/30"></span>
                    <span class="flex items-center gap-1.5 sm:gap-2"><span style="width:16px;height:16px;border-radius:50%;background:rgba(217,98,43,0.3);display:flex;align-items:center;justify-content:center;font-size:0.55rem;">✓</span> AI planned</span>
                    <span class="w-0.5 h-0.5 rounded-full bg-white/30"></span>
                    <span class="flex items-center gap-1.5 sm:gap-2"><span style="width:16px;height:16px;border-radius:50%;background:rgba(217,98,43,0.3);display:flex;align-items:center;justify-content:center;font-size:0.55rem;">✓</span> Local routes</span>
                </div>
            </div>
        </section>


        <!-- ── Features ── -->
        <section class="feature-strip" style="padding-top:40px;">
            <article class="feature-tile reveal" style="border-top:4px solid var(--deep-teal);">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <h3 class="font-bold text-lg text-[var(--deep-teal)] mb-2">Stay somewhere special</h3>
                <p class="text-sm leading-6 text-[var(--ink-soft)]">Compare local hotels and find the right base for your city escape.</p>
            </article>
            <article class="feature-tile reveal" style="border-top:4px solid var(--ember);">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <h3 class="font-bold text-lg text-[var(--deep-teal)] mb-2">Make every peso count</h3>
                <p class="text-sm leading-6 text-[var(--ink-soft)]">See a practical breakdown designed around your real travel budget.</p>
            </article>
            <article class="feature-tile reveal" style="border-top:4px solid var(--deep-teal);">
                <div class="feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                </div>
                <h3 class="font-bold text-lg text-[var(--deep-teal)] mb-2">Follow the easy route</h3>
                <p class="text-sm leading-6 text-[var(--ink-soft)]">Navigate food, beaches, and landmarks with interactive local maps.</p>
            </article>
        </section>

        <!-- ── How It Works ── -->
        <section class="how-section">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-10 md:mb-16 reveal">
                    <div class="eyebrow text-center justify-center" style="color:var(--ember-deep);">Simple steps</div>
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-display text-[var(--deep-teal)] mt-3 mb-4">How it works</h2>
                    <p class="text-[var(--ink-soft)] max-w-xl mx-auto text-base sm:text-lg">Three clicks and you're on your way to exploring Dagupan like a local.</p>
                </div>
                <div class="grid md:grid-cols-3 gap-6 md:gap-12">
                    <div class="how-step reveal">
                        <div class="step-number">01</div>
                        <div class="step-content">
                            <h3 class="font-bold text-lg text-[var(--deep-teal)]">Tell us your style</h3>
                            <p class="text-sm leading-6 text-[var(--ink-soft)]">Pick your budget, duration, and what excites you — food, history, or beaches.</p>
                        </div>
                    </div>
                    <div class="how-step reveal">
                        <div class="step-number">02</div>
                        <div class="step-content">
                            <h3 class="font-bold text-lg text-[var(--deep-teal)]">Get your itinerary</h3>
                            <p class="text-sm leading-6 text-[var(--ink-soft)]">Our AI builds a day-by-day plan with hotels, routes, and price estimates.</p>
                        </div>
                    </div>
                    <div class="how-step reveal">
                        <div class="step-number">03</div>
                        <div class="step-content">
                            <h3 class="font-bold text-lg text-[var(--deep-teal)]">Explore with confidence</h3>
                            <p class="text-sm leading-6 text-[var(--ink-soft)]">View your trip on an interactive map, adjust anytime, and share with friends.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Gallery ── -->
        <section class="dagupan-gallery max-w-6xl mx-auto px-4 sm:px-6 pb-16 sm:pb-20">
            <div class="text-center mb-10 md:mb-14 reveal">
                <div class="eyebrow text-center justify-center" style="color:var(--ember-deep);">Discover Dagupan</div>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-display text-[var(--deep-teal)] mt-3 mb-3">A city full of stories</h2>
                <p class="text-[var(--ink-soft)] max-w-xl mx-auto text-base sm:text-lg">From sunny beaches to colorful festivals — every corner of Dagupan has something to share.</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/beach-scene.jpg" alt="I ❤️ Dagupan sign" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>I ❤️ Dagupan</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 md:col-span-2 md:row-span-2 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/i-love-dagupan.jpg" alt="Dagupan beach" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Tondaligan Beach</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/festival-dancers.jpg" alt="Bangus festival dancers" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Bangus Festival</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/jeepneys.jpg" alt="Colorful jeepneys" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Colorful Jeepneys</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/church-building.jpg" alt="Dagupan church" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>St. John's Cathedral</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/market-square.jpg" alt="Market Square" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Market Square</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/sm-center.jpg" alt="SM Center Dagupan" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>SM Center Dagupan</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/street-scene.jpg" alt="Dagupan street" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Downtown Streets</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/festival-parade.jpg" alt="Festival parade" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Pistay Dayat</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/church-aerial.jpg" alt="Church aerial view" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Aerial View</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/train.webp" alt="Historic train" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Historic Train</span></div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl aspect-[4/3] bg-[var(--line)] shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-1 reveal" style="border-radius:var(--radius-sm);">
                    <img src="/images/dagupan/beach-aerial.jpg" alt="Beach aerial view" class="w-full h-full object-cover object-center group-hover:scale-110 transition duration-700" loading="lazy" onerror="this.parentElement.style.background='linear-gradient(135deg, var(--sand), var(--line))';this.style.display='none'">
                    <div class="gallery-label"><span>Beach Aerial</span></div>
                </div>
            </div>
        </section>



        <!-- ── CTA Banner ── -->
        <section class="cta-banner reveal">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
                <div class="cta-banner-inner">
                    <h2 class="text-3xl sm:text-4xl md:text-5xl font-display text-white mt-2 mb-3 sm:mb-4">Ready to explore Dagupan?</h2>
                    <p class="text-white/80 max-w-lg mx-auto mb-6 sm:mb-8 text-base sm:text-lg">Your perfect itinerary is just a few clicks away. Start planning your trip today.</p>
                    <a href="/register" class="inline-flex items-center gap-2 bg-white text-[var(--deep-teal)] font-bold px-7 sm:px-8 py-3.5 sm:py-4 rounded-full hover:shadow-lg hover:-translate-y-1 transition-all duration-200 mx-auto" style="box-shadow:0 12px 32px rgba(0,0,0,0.15);">
                        Plan your trip
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>
        </section>

    </main>

    <!-- ── Scroll reveal + Counter animations ── -->
    <script>
        (function() {
            'use strict';

            // ── Scroll reveal ──
            const revealEls = document.querySelectorAll('.reveal, .reveal-2');
            if (revealEls.length && 'IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
                revealEls.forEach(el => observer.observe(el));
            } else {
                revealEls.forEach(el => el.classList.add('is-visible'));
            }

            // ── Counter animation ──
            const statNumbers = document.querySelectorAll('.stat-number');
            if (statNumbers.length && 'IntersectionObserver' in window) {
                const counterObserver = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const el = entry.target;
                            const target = parseInt(el.dataset.count || el.textContent, 10);
                            if (isNaN(target)) return;
                            animateCounter(el, target);
                            counterObserver.unobserve(el);
                        }
                    });
                }, { threshold: 0.3 });
                statNumbers.forEach(el => counterObserver.observe(el));
            }

            function animateCounter(el, target) {
                let current = 0;
                const increment = Math.max(1, Math.floor(target / 40));
                const duration = 1500;
                const stepTime = Math.floor(duration / (target / increment));
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    el.textContent = current + (target === 98 ? '%' : '+');
                }, stepTime);
            }

            // ── FAQ accordion chevron rotation ──
            document.querySelectorAll('details').forEach(details => {
                details.addEventListener('toggle', function() {
                    const chevron = this.querySelector('.chevron');
                    if (chevron) {
                        chevron.style.transform = this.open ? 'rotate(180deg)' : 'rotate(0deg)';
                    }
                });
            });
        })();
    </script>
</body>
</html>
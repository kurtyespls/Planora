/**
 * planora-animations.js
 * Global UI/UX enhancement layer for Planora
 * Handles: scroll-reveal, counter roll-up, page transitions,
 *          nav scroll state, parallax tilt, form loading states
 */
(function () {
    'use strict';

    /* ── 1. IntersectionObserver scroll-reveal ── */
    function initReveal() {
        const els = document.querySelectorAll('.reveal, .reveal-fade, .reveal-scale, .reveal-left, .reveal-right');
        if (!els.length) return;

        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    io.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        els.forEach(el => io.observe(el));
    }

    /* ── 2. Counter roll-up ── */
    function initCounters() {
        const els = document.querySelectorAll('[data-counter]');
        if (!els.length) return;

        const io = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = parseFloat(el.dataset.counter.replace(/[^0-9.]/g, ''));
                const suffix = el.dataset.counter.replace(/[0-9.]/g, '');
                const duration = 1400;
                const start = performance.now();
                const from = 0;

                function step(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const value = from + (target - from) * eased;
                    el.textContent = (Number.isInteger(target)
                        ? Math.round(value)
                        : value.toFixed(1)) + suffix;
                    if (progress < 1) requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
                io.unobserve(el);
            });
        }, { threshold: 0.5 });

        els.forEach(el => io.observe(el));
    }

    /* ── 3. Nav scroll state ── */
    function initNavScroll() {
        const nav = document.querySelector('.top-nav');
        if (!nav) return;

        function update() {
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }
        update();
        window.addEventListener('scroll', update, { passive: true });
    }

    /* ── 4. Page transitions ── */
    function initPageTransitions() {
        document.querySelectorAll('a[href]').forEach(a => {
            const href = a.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') ||
                href.startsWith('mailto') || href.startsWith('tel') ||
                a.target === '_blank' || a.hasAttribute('data-no-transition')) return;

            try {
                const url = new URL(href, location.href);
                if (url.origin !== location.origin) return;
            } catch (e) { return; }

            a.addEventListener('click', function (e) {
                if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
                e.preventDefault();
                const dest = a.href;
                document.body.classList.add('page-leaving');
                setTimeout(() => { location.href = dest; }, 240);
            });
        });
    }

    /* ── 5. Floating card parallax tilt ── */
    function initParallax() {
        const hero = document.querySelector('.landing-hero');
        if (!hero) return;
        const cards = hero.querySelectorAll('.art-card');
        if (!cards.length) return;

        hero.addEventListener('mousemove', function (e) {
            const rect = hero.getBoundingClientRect();
            const cx = (e.clientX - rect.left) / rect.width - 0.5;
            const cy = (e.clientY - rect.top) / rect.height - 0.5;

            cards.forEach((card, i) => {
                const factor = i % 2 === 0 ? 8 : -6;
                card.style.transform = `translate(${cx * factor}px, ${cy * factor}px)`;
                card.style.transition = 'transform 0.1s linear';
            });
        });

        hero.addEventListener('mouseleave', function () {
            cards.forEach(card => {
                card.style.transform = '';
                card.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
            });
        });
    }

    /* ── 6. Form loading states ── */
    function initFormLoading() {
        document.querySelectorAll('form[data-loading]').forEach(form => {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('[type="submit"]');
                if (!btn) return;
                const original = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('btn-loading');
                btn.dataset.original = original;
            });
        });

        // Global: add data-loading to auth forms automatically
        document.querySelectorAll('.auth-card form, .auth-panel form').forEach(form => {
            form.dataset.loading = '';
        });
    }

    /* ── 7. XP bar entrance animation ── */
    function initXpBar() {
        const fill = document.querySelector('.xp-bar-fill');
        if (!fill) return;
        const targetWidth = fill.style.width || fill.getAttribute('data-width') || '0%';
        fill.style.width = targetWidth;
        fill.style.transform = 'scaleX(0)';
        fill.style.transformOrigin = 'left';
        fill.style.transition = 'transform 1.2s cubic-bezier(0.16, 1, 0.3, 1)';
        setTimeout(() => { fill.style.transform = 'scaleX(1)'; }, 300);
    }

    /* ── 8. Lazy image fade-in ── */
    function initLazyImages() {
        document.querySelectorAll('img.img-lazy').forEach(img => {
            if (img.complete) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', () => img.classList.add('loaded'));
            }
        });
    }

    /* ── 9. Upgraded toast system ── */
    const TOAST_ICONS = {
        success: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        error:   `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
        warning: `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
        info:    `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
    };
    const TOAST_LABELS = { success: 'Success', error: 'Error', warning: 'Warning', info: 'Info' };

    function showToastV2(message, type = 'info', duration = 4500) {
        const container = document.getElementById('toast-container') ||
            (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.style.cssText = 'position:fixed;top:1.25rem;right:1.25rem;z-index:9999;display:flex;flex-direction:column;gap:0.6rem;pointer-events:none;';
                document.body.appendChild(c);
                return c;
            })();

        const toast = document.createElement('div');
        toast.className = `toast-v2 toast-${type}`;
        toast.style.pointerEvents = 'auto';
        toast.innerHTML = `
            <div class="toast-v2-icon">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
            <div class="toast-v2-body">
                <div class="toast-v2-title">${TOAST_LABELS[type] || type}</div>
                <div class="toast-v2-msg">${message}</div>
            </div>
            <button onclick="this.closest('.toast-v2').remove()" style="background:none;border:none;cursor:pointer;padding:0 0 0 8px;opacity:0.5;line-height:1;font-size:1.1rem;color:inherit;" aria-label="Dismiss">&times;</button>
            <div class="toast-v2-progress" style="animation-duration:${duration}ms;"></div>
        `;

        container.appendChild(toast);
        // Trigger transition
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => toast.remove(), 400);
        }, duration);

        return toast;
    }

    // Override the global showToast
    window.showToast = function(message, type = 'info', duration = 4500) {
        return showToastV2(message, type, duration);
    };
    window.showToastV2 = showToastV2;

    /* ── 10. Admin topbar elevation ── */
    function initAdminTopbar() {
        const topbar = document.querySelector('.admin-topbar');
        if (!topbar) return;
        const main = document.querySelector('.admin-main-wrap main, main');
        const target = main || window;
        (main || window).addEventListener('scroll', function () {
            const scrolled = main ? main.scrollTop > 10 : window.scrollY > 10;
            topbar.classList.toggle('elevated', scrolled);
        }, { passive: true });
    }

    /* ── 11. Hero headline word split ── */
    function initHeroWords() {
        document.querySelectorAll('.landing-title [data-words]').forEach(el => {
            const words = el.textContent.trim().split(' ');
            el.innerHTML = words.map((w, i) =>
                `<span class="hero-word" style="animation-delay:${0.1 + i * 0.08}s">${w}</span>`
            ).join(' ');
        });
    }

    /* ── Init ── */
    function init() {
        initReveal();
        initCounters();
        initNavScroll();
        initParallax();
        initFormLoading();
        initXpBar();
        initLazyImages();
        initAdminTopbar();
        initHeroWords();
        // Page transitions are opt-in to avoid breaking forms/special links
        // initPageTransitions(); // uncomment to enable
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Re-run on Livewire/Alpine navigation if present
    document.addEventListener('livewire:navigated', init);
})();

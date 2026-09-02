(function ($) {
    'use strict';

    /* Scroll reveal */
    var revealEls = document.querySelectorAll('.reveal');
    if (revealEls.length && 'IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function (el) { observer.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    var header = document.querySelector('.landing-header');
    var navToggle = document.querySelector('.nav-toggle');
    var landingNav = document.querySelector('.landing-nav');

    if (header) {
        window.addEventListener('scroll', function () {
            header.classList.toggle('is-scrolled', window.scrollY > 8);
        }, { passive: true });
    }

    if (navToggle && landingNav) {
        navToggle.addEventListener('click', function () {
            var open = landingNav.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        landingNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                landingNav.classList.remove('is-open');
                navToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* Smooth anchor scroll */
    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        anchor.addEventListener('click', function (e) {
            var id = this.getAttribute('href');
            if (id.length <= 1) return;
            var target = document.querySelector(id);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    /* Product screenshots: 24s loop, always visible */
    var frame = document.querySelector('.hero-shots-frame');
    if (frame) {
        var shots = frame.querySelectorAll('[data-shot]');
        var tabs = document.querySelectorAll('[data-shot-tab]');
        var order = [];
        shots.forEach(function (img) { order.push(img.getAttribute('data-shot')); });
        var stepMs = parseInt(frame.getAttribute('data-shot-autoplay') || '0', 10);
        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var timer = null;
        var progress = frame.querySelector('.hero-demo-progress span');

        var show = function (name) {
            shots.forEach(function (img) {
                img.classList.toggle('is-active', img.getAttribute('data-shot') === name);
            });
            tabs.forEach(function (t) {
                t.classList.toggle('is-active', t.getAttribute('data-shot-tab') === name);
            });
            if (progress) {
                progress.style.animation = 'none';
                void progress.offsetWidth;
                if (stepMs > 0 && !reduce) {
                    progress.style.animation = 'mock-progress ' + stepMs + 'ms linear forwards';
                }
            }
        };

        var next = function () {
            var current = frame.querySelector('[data-shot].is-active');
            var name = current ? current.getAttribute('data-shot') : order[0];
            var idx = order.indexOf(name);
            show(order[(idx + 1) % order.length]);
        };

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                show(tab.getAttribute('data-shot-tab'));
                if (timer) {
                    clearInterval(timer);
                    timer = setInterval(next, stepMs);
                }
            });
        });

        if (stepMs > 0 && !reduce && order.length > 1) {
            frame.classList.add('is-playing');
            if (progress) {
                progress.style.animation = 'mock-progress ' + stepMs + 'ms linear forwards';
            }
            timer = setInterval(next, stepMs);
        }
    }

    /* Animated counters in hero mock */
    document.querySelectorAll('[data-count-to]').forEach(function (el) {
        var target = parseFloat(el.getAttribute('data-count-to'));
        var suffix = el.getAttribute('data-count-suffix') || '';
        var prefix = el.getAttribute('data-count-prefix') || '';
        var decimals = parseInt(el.getAttribute('data-count-decimals') || '0', 10);
        if (isNaN(target)) return;

        var run = function () {
            var start = 0;
            var duration = 1200;
            var startTime = null;
            var step = function (ts) {
                if (!startTime) startTime = ts;
                var p = Math.min((ts - startTime) / duration, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                var val = start + (target - start) * eased;
                el.textContent = prefix + val.toLocaleString('es-ES', {
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                }) + suffix;
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        };

        if ('IntersectionObserver' in window) {
            var cObs = new IntersectionObserver(function (entries) {
                if (entries[0].isIntersecting) {
                    run();
                    cObs.disconnect();
                }
            }, { threshold: 0.5 });
            cObs.observe(el);
        } else {
            run();
        }
    });

    /* Modal: close on Escape */
    if ($) {
        $(document).on('keydown', function (e) {
            if (e.key === 'Escape') {
                $('#upgrade-modal').hide();
            }
        });

        /* Copy public link */
        $(document).on('click', '[data-copy]', function () {
            var sel = $(this).attr('data-copy');
            var input = document.querySelector(sel);
            if (!input) return;
            input.select();
            input.setSelectionRange(0, 99999);
            var btn = $(this);
            var orig = btn.text();
            navigator.clipboard.writeText(input.value).then(function () {
                btn.text('¡Copiado!');
                setTimeout(function () { btn.text(orig); }, 2000);
            });
        });
    }

    /* Table row click-through */
    if ($) {
        $('.data-table tbody tr[data-href]').on('click', function (e) {
            if ($(e.target).closest('a, button, form').length) return;
            window.location = $(this).data('href');
        });
    }

    /* Collapsible sidebar is bound in layouts/panel.blade.php so a stale app.js cannot double-toggle. */

    /* Analytics CTA (sin PII) */
    var csrf = document.querySelector('meta[name="csrf-token"]');
    document.querySelectorAll('[data-analytics]').forEach(function (el) {
        el.addEventListener('click', function () {
            var name = el.getAttribute('data-analytics');
            if (!name || !csrf) return;
            try {
                fetch('/a/e', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf.getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: name }),
                    keepalive: true
                });
            } catch (err) {}
        });
    });
})(window.jQuery);

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

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var finePointer = window.matchMedia('(pointer: fine)').matches;

    /* Cursor glow + magnetic buttons (marketing, pointer fino) */
    var glow = document.querySelector('.cursor-glow');
    if (glow && !reduceMotion && finePointer) {
        document.body.classList.add('has-cursor-glow');
        var gx = 0, gy = 0, tx = 0, ty = 0;
        document.addEventListener('pointermove', function (e) {
            tx = e.clientX;
            ty = e.clientY;
        }, { passive: true });
        var tickGlow = function () {
            gx += (tx - gx) * 0.18;
            gy += (ty - gy) * 0.18;
            glow.style.transform = 'translate3d(' + (gx - 180) + 'px,' + (gy - 180) + 'px,0)';
            requestAnimationFrame(tickGlow);
        };
        requestAnimationFrame(tickGlow);
    }

    if (!reduceMotion && finePointer) {
        document.querySelectorAll('.btn-magnetic').forEach(function (btn) {
            btn.addEventListener('pointermove', function (e) {
                var r = btn.getBoundingClientRect();
                var x = e.clientX - r.left - r.width / 2;
                var y = e.clientY - r.top - r.height / 2;
                btn.style.transform = 'translate(' + (x * 0.18) + 'px,' + (y * 0.22) + 'px)';
            });
            btn.addEventListener('pointerleave', function () {
                btn.style.transform = '';
            });
        });
    }

    /* Hero orb parallax */
    var orbs = document.querySelector('.landing-hero .hero-bg-orbs');
    if (orbs && !reduceMotion && finePointer) {
        document.addEventListener('pointermove', function (e) {
            var px = (e.clientX / window.innerWidth - 0.5) * 24;
            var py = (e.clientY / window.innerHeight - 0.5) * 16;
            orbs.style.transform = 'translate(' + px + 'px,' + py + 'px)';
        }, { passive: true });
    }

    /* 3D tilt on product mock */
    var tilt = document.querySelector('.mock-tilt');
    if (tilt && !reduceMotion && finePointer) {
        tilt.addEventListener('pointermove', function (e) {
            var r = tilt.getBoundingClientRect();
            var rx = ((e.clientY - r.top) / r.height - 0.5) * -8;
            var ry = ((e.clientX - r.left) / r.width - 0.5) * 10;
            tilt.style.transform = 'perspective(1100px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
        });
        tilt.addEventListener('pointerleave', function () {
            tilt.style.transform = '';
        });
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

    /* Interactive product mock tabs */
    var mock = document.querySelector('.screenshot-mock');
    if (mock) {
        var tabs = mock.querySelectorAll('[data-mock-tab]');
        var panels = mock.querySelectorAll('[data-mock-panel]');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var name = tab.getAttribute('data-mock-tab');
                tabs.forEach(function (t) { t.classList.toggle('active', t === tab); });
                panels.forEach(function (p) {
                    p.hidden = p.getAttribute('data-mock-panel') !== name;
                });
            });
        });
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

    /* Table row click-through */
    $('.data-table tbody tr[data-href]').on('click', function (e) {
        if ($(e.target).closest('a, button, form').length) return;
        window.location = $(this).data('href');
    });
})(window.jQuery);

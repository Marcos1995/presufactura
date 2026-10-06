<script>
(function () {
    document.documentElement.classList.add('js');
    var key = 'pf-theme';
    function apply(dark) {
        document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
        document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
        document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
            b.setAttribute('aria-pressed', dark ? 'true' : 'false');
        });
    }
    document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var dark = document.documentElement.getAttribute('data-theme') !== 'dark';
            try { localStorage.setItem(key, dark ? 'dark' : 'light'); } catch (e) {}
            apply(dark);
        });
    });
    apply(document.documentElement.getAttribute('data-theme') === 'dark');
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    en.target.classList.add('is-in');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.12 });
        document.querySelectorAll('main > section').forEach(function (s) {
            s.classList.add('gesto');
            io.observe(s);
        });
    }
})();
</script>

<script>
(function () {
    var stored = null;
    try { stored = localStorage.getItem('pf-theme'); } catch (e) {}
    var dark = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light');
    document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
})();
</script>

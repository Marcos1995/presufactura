<div class="cookie-banner" id="cookie-banner" hidden>
    <div class="cookie-banner-inner">
        <p>
            Usamos cookies técnicas necesarias para el funcionamiento del servicio (sesión y seguridad).
            Consulta nuestra <a href="{{ route('legal.cookies') }}">política de cookies</a>.
        </p>
        <div class="cookie-banner-actions">
            <button type="button" class="btn btn-secondary btn-sm" data-cookie-consent="essential">Solo necesarias</button>
            <button type="button" class="btn btn-primary btn-sm" data-cookie-consent="accepted">Aceptar</button>
        </div>
    </div>
</div>
<script>
(function () {
    var key = 'cookie_consent';
    if (localStorage.getItem(key)) {
        return;
    }

    var banner = document.getElementById('cookie-banner');
    if (!banner) {
        return;
    }

    banner.hidden = false;

    banner.querySelectorAll('[data-cookie-consent]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            localStorage.setItem(key, btn.getAttribute('data-cookie-consent'));
            banner.hidden = true;
        });
    });
})();
</script>

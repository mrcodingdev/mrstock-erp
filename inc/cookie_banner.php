<link rel="stylesheet" href="<?= BASE_URL ?>/css/cookies.css?v=1">
<aside id="mrstockCookieBanner" class="cookie-preview session-cookie-banner" aria-labelledby="mrstock-cookie-title" hidden>
    <header>
        <span class="cookie-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 13.7a8.8 8.8 0 1 1-10.1-10.1 4.8 4.8 0 0 0 5.9 5.9 3.5 3.5 0 0 0 4.2 4.2Z"></path><path d="M7.6 10.1h.01m2.7 5.6h.01m5.1-.8h.01"></path></svg>
        </span>
        <h2 id="mrstock-cookie-title">Cookies e privacidade</h2>
    </header>
    <p>Usamos cookies essenciais para manter sua sessão e proteger o acesso ao sistema. Saiba mais na <a href="<?= BASE_URL ?>/privacidade.php#cookies">Política de Privacidade</a>.</p>
    <button type="button" class="cookie-button">Entendi</button>
</aside>
<script>
(() => {
    const aviso = document.getElementById('mrstockCookieBanner');
    const chave = 'mrstock_cookie_consent';

    try {
        if (localStorage.getItem(chave)) return;
    } catch {
        // O aviso ainda pode ser dispensado nesta página.
    }

    aviso.hidden = false;
    aviso.querySelector('button').addEventListener('click', () => {
        aviso.hidden = true;
        try {
            localStorage.setItem(chave, 'accepted');
        } catch {
            // O navegador não permitiu guardar a dispensa.
        }
    });
})();
</script>

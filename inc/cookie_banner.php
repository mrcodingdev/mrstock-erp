<!-- Card Flutuante Compacto de Consentimento de Cookies LGPD (MrStock ERP) -->
<style>
@keyframes mrStockSlideInLeft {
    0% { opacity: 0; transform: translateX(20px); }
    100% { opacity: 1; transform: translateX(0); }
}
</style>
<div id="mrstockCookieBanner" class="mrstock-cookie-banner" role="dialog" aria-live="polite" aria-label="Consentimento de Cookies LGPD" style="display: none; position: fixed; bottom: 20px; right: 20px; width: 360px; max-width: calc(100vw - 40px); z-index: 1060; background: #1e293b; border: 1px solid rgba(255, 255, 255, 0.12); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3); border-radius: 12px; padding: 1.25rem; color: #f1f5f9; font-family: 'Inter', sans-serif;">
    <div class="d-flex align-items-start gap-2 mb-2">
        <div style="background: rgba(40, 73, 54, 0.3); border: 1px solid rgba(106, 228, 155, 0.3); border-radius: 6px; padding: 6px 8px; color: #6ae49b; font-size: 1rem; flex-shrink: 0;">
            <i class="fas fa-cookie-bite"></i>
        </div>
        <div>
            <div class="fw-bold text-white" style="font-size: 0.9rem;">Privacidade &amp; Cookies</div>
            <small class="text-white-50" style="font-size: 0.75rem;">Conformidade LGPD</small>
        </div>
    </div>
    <p class="mb-3" style="font-size: 0.8125rem; color: #cbd5e1; line-height: 1.45;">
        Utilizamos cookies técnicos essenciais para autenticação de operadores, integridade de sessão e ergonomia visual sob a LGPD.
    </p>
    <div class="d-flex justify-content-end align-items-center gap-2">
        <a href="<?= BASE_URL ?>/privacidade.php#cookies" class="btn btn-secondary" style="background-color: #475569; border-color: #475569; color: #fff; font-weight: 500; font-size: 0.8125rem; padding: 6px 14px; border-radius: 6px;" aria-label="Gerenciar preferências e consultar a política de cookies">
            Gerenciar
        </a>
        <button type="button" id="btnAcceptCookies" class="btn btn-success" style="background-color: #284936; border-color: #284936; color: #fff; font-weight: 600; font-size: 0.8125rem; padding: 6px 14px; border-radius: 6px;" aria-label="Aceitar cookies essenciais e fechar aviso">
            Aceitar
        </button>
    </div>
</div>

<script>
(function() {
    function initCookieBanner() {
        try {
            var consent = localStorage.getItem('mrstock_cookie_consent');
            var banner = document.getElementById('mrstockCookieBanner');
            var btn = document.getElementById('btnAcceptCookies');

            if (!consent && banner) {
                banner.style.display = 'block';
                banner.style.animation = 'mrStockSlideInLeft 0.5s cubic-bezier(0.16, 1, 0.3, 1) both';
            }

            if (btn && banner) {
                btn.addEventListener('click', function() {
                    localStorage.setItem('mrstock_cookie_consent', 'accepted');
                    banner.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    banner.style.opacity = '0';
                    banner.style.transform = 'translateY(15px)';
                    setTimeout(function() {
                        banner.style.display = 'none';
                    }, 300);
                });
            }
        } catch (e) {
            console.warn('LocalStorage indisponível para controle de cookies:', e);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCookieBanner);
    } else {
        initCookieBanner();
    }
})();
</script>

(() => {
    const script = document.currentScript;
    const medirNestaPagina = script.hasAttribute('data-medir-visitas');
    const raiz = new URL(script.dataset.raiz || './', document.baseURI);
    const id = 'G-T2P9BHGJRM';
    const chave = 'mrstock_analytics';
    const controle = '[data-preferencias-medicao]';
    let carregada = false;
    let escolha = null;
    try {
        escolha = localStorage.getItem(chave);
    } catch {
        // Sem armazenamento, a medição permanece desativada.
    }

    function carregarMedicao() {
        if (!medirNestaPagina || carregada || location.hostname !== 'mrstock.com.br') return;
        carregada = true;
        window['ga-disable-' + id] = false;
        window.dataLayer = window.dataLayer || [];
        function gtag() {
            window.dataLayer.push(arguments);
        }

        const dadosDePublicidade = {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
        };
        const origemAnterior = document.referrer ? new URL(document.referrer).origin : '';
        gtag('consent', 'default', { ...dadosDePublicidade, analytics_storage: 'denied' });
        gtag('consent', 'update', { ...dadosDePublicidade, analytics_storage: 'granted' });
        gtag('js', new Date());
        gtag('config', id, {
            allow_google_signals: false,
            allow_ad_personalization_signals: false,
            cookie_domain: 'none',
            page_location: location.origin + location.pathname,
            page_referrer: origemAnterior,
        });

        const tag = document.createElement('script');
        tag.src = 'https://www.googletagmanager.com/gtag/js?id=' + id;
        tag.async = true;
        tag.referrerPolicy = 'no-referrer';
        document.head.append(tag);
    }

    document.body.insertAdjacentHTML('beforeend', `
        <button type="button" class="cookie-open" hidden>Preferências de cookies</button>
        <aside class="cookie-preview" hidden aria-labelledby="cookie-title">
            <header>
                <span class="cookie-icon" aria-hidden="true">
                    <svg viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.4 13.7a8.8 8.8 0 1 1-10.1-10.1 4.8 4.8 0 0 0 5.9 5.9 3.5 3.5 0 0 0 4.2 4.2Z"></path><path d="M7.6 10.1h.01m2.7 5.6h.01m5.1-.8h.01"></path></svg>
                </span>
                <h2 id="cookie-title" tabindex="-1">Cookies e privacidade</h2>
                <button type="button" class="cookie-close" aria-label="Fechar aviso">
                    <svg aria-hidden="true" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                </button>
            </header>
            <p>O navegador guarda suas preferências. Com sua permissão, usamos o Google Analytics para medir visitas às páginas de apresentação. Consulte a <a data-documento="privacidade.php#cookies">Política de Privacidade</a> e os <a data-documento="termos.php">Termos de Uso</a>.</p>
            <div class="cookie-actions">
                <button type="button" class="cookie-button cookie-expand" aria-expanded="false" aria-controls="cookie-options">
                    Personalizar <svg aria-hidden="true" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                </button>
                <button type="button" class="cookie-button" data-choice="nao">Recusar</button>
                <button type="button" class="cookie-button" data-choice="sim">Permitir</button>
            </div>
            <div class="cookie-preferences" id="cookie-options" hidden>
                <div class="cookie-option">
                    <span><strong>Essenciais</strong><span>Mantêm a sessão e guardam sua escolha.</span></span>
                    <span class="cookie-required">Sempre ativos</span>
                </div>
                <label class="cookie-option">
                    <span><strong>Medição de visitas</strong><span>Mede visitas às páginas de apresentação.</span></span>
                    <span class="cookie-check"><input type="checkbox" id="cookie-analytics" aria-label="Medição de visitas"></span>
                </label>
                <div class="cookie-save"><button type="button" class="cookie-button">Salvar preferências</button></div>
            </div>
        </aside>
    `);

    const painel = document.querySelector('.cookie-preview');
    const reabrir = document.querySelector('.cookie-open');
    if (!medirNestaPagina) {
        painel.classList.add('session-cookie-banner');
        reabrir.classList.add('session-cookie-banner');
    }
    painel.querySelectorAll('[data-documento]').forEach(link => {
        link.href = new URL(link.dataset.documento, raiz);
    });
    const personalizar = painel.querySelector('.cookie-expand');
    const opcoes = painel.querySelector('#cookie-options');
    const analytics = painel.querySelector('#cookie-analytics');
    let origemDoFoco = null;

    function alternarOpcoes(visiveis) {
        opcoes.hidden = !visiveis;
        personalizar.setAttribute('aria-expanded', String(visiveis));
        if (visiveis) analytics.checked = escolha === 'sim';
    }

    function mostrarPainel(origem = null) {
        origemDoFoco = origem;
        alternarOpcoes(false);
        reabrir.hidden = true;
        painel.hidden = false;
        if (origem) painel.querySelector('h2').focus();
    }

    function fecharPainel(restaurarFoco = true) {
        painel.hidden = true;
        reabrir.hidden = escolha !== null;
        if (!restaurarFoco) return;
        const destino = escolha === null ? reabrir
            : origemDoFoco?.matches(controle) ? origemDoFoco
                : document.querySelector('.contact-float, .login-card input');
        destino?.focus();
    }

    function desativarMedicao() {
        window['ga-disable-' + id] = true;
        for (const nome of ['_ga', '_ga_' + id.slice(2)]) {
            const expirado = nome + '=; Max-Age=0; Path=/; Secure; SameSite=Lax';
            document.cookie = expirado;
            document.cookie = expirado + '; Domain=' + location.hostname;
        }
    }

    function guardarEscolha(valor) {
        if (valor === 'nao') desativarMedicao();
        try {
            localStorage.setItem(chave, valor);
        } catch {
            escolha = 'nao';
            document.body.classList.remove('cookie-choice-pending');
            fecharPainel();
            return;
        }
        escolha = valor;
        document.body.classList.remove('cookie-choice-pending');
        fecharPainel();
        if (escolha === 'sim') carregarMedicao();
        else if (carregada) location.reload();
    }

    painel.querySelector('.cookie-close').addEventListener('click', () => fecharPainel());
    personalizar.addEventListener('click', () => alternarOpcoes(opcoes.hidden));
    painel.querySelectorAll('[data-choice]').forEach(botao => {
        botao.addEventListener('click', () => guardarEscolha(botao.dataset.choice));
    });
    painel.querySelector('.cookie-save button').addEventListener('click', () => {
        guardarEscolha(analytics.checked ? 'sim' : 'nao');
    });
    painel.addEventListener('keydown', evento => {
        if (evento.key === 'Escape') fecharPainel();
    });
    reabrir.addEventListener('click', () => mostrarPainel(reabrir));
    document.addEventListener('click', evento => {
        const botao = evento.target.closest(controle);
        if (botao) mostrarPainel(botao);
    });
    window.addEventListener('storage', evento => {
        if (evento.storageArea !== localStorage || (evento.key !== chave && evento.key !== null)) return;
        if (evento.newValue === escolha) return;
        if (evento.newValue !== 'sim' && carregada) {
            desativarMedicao();
            location.reload();
            return;
        }
        escolha = ['sim', 'nao'].includes(evento.newValue) ? evento.newValue : null;
        document.body.classList.toggle('cookie-choice-pending', escolha === null);
        if (escolha === null) mostrarPainel();
        else {
            fecharPainel(false);
            if (escolha === 'sim') carregarMedicao();
        }
    });

    // Explorar monta seu rodapé a partir de um template.
    function mostrarControles() {
        document.querySelectorAll(controle).forEach(botao => { botao.hidden = false; });
    }
    mostrarControles();
    new MutationObserver(mostrarControles).observe(document.body, { childList: true, subtree: true });

    if (escolha === 'sim') carregarMedicao();
    else if (escolha === 'nao') desativarMedicao();
    else {
        escolha = null;
        document.body.classList.add('cookie-choice-pending');
        mostrarPainel();
    }
})();

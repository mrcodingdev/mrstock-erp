(() => {
    const header = document.querySelector('.site-header');
    const figure = document.querySelector('.hero-figure');
    const monitor = figure?.querySelector('.monitor');
    const toggle = document.querySelector('.motion-toggle');
    const reveals = document.querySelectorAll('[data-home-reveal]');
    const isPublicPage = document.getElementById('mrstock-public-map')?.hasAttribute('data-public-page');
    const systemMotion = matchMedia('(prefers-reduced-motion: no-preference)');
    const preference = 'mrstock.landing.motion';
    let override;
    let pendingFrame;
    let motionEnabled;

    const clamp = value => Math.min(1, Math.max(0, value));

    function updateMonitor(height) {
        const scroll = window.scrollY;
        const safeTop = getComputedStyle(header).position === 'sticky' ? header.offsetHeight : 0;
        // A figura mantém sua posição de layout enquanto o monitor gira.
        const distanceToHeader = figure.getBoundingClientRect().top - safeTop;
        const readingSpace = Math.min(height * .2, 168);
        const alignmentEnd = Math.max(1, Math.min(height * .5, scroll + distanceToHeader - readingSpace));
        const progress = clamp(scroll / alignmentEnd);
        const visibility = clamp((distanceToHeader - 8) / Math.min(48, height * .05));
        const notes = clamp((progress - .48) / .52) * visibility;
        monitor.style.setProperty('--tilt', ((1 - progress) ** 2).toFixed(4));
        monitor.style.setProperty('--note-reveal', notes.toFixed(3));
    }

    function updateMotion() {
        pendingFrame = undefined;
        if (!motionEnabled) return;

        const height = window.innerHeight;
        // Medir os pais evita que a própria transformação desloque o limiar.
        const positions = Array.from(reveals, item => item.getBoundingClientRect().top);
        if (monitor) updateMonitor(height);
        reveals.forEach((item, index) => {
            const progress = clamp((height * .975 - positions[index]) / (height * .23));
            const eased = progress * progress * (3 - 2 * progress);
            item.style.setProperty('--home-reveal', eased.toFixed(3));
        });
    }

    function scheduleMotion() {
        if (motionEnabled && (monitor || reveals.length) && pendingFrame === undefined) {
            pendingFrame = requestAnimationFrame(updateMotion);
        }
    }

    function syncMotion() {
        motionEnabled = override ?? (isPublicPage || systemMotion.matches);
        if (toggle) {
            toggle.hidden = false;
            toggle.textContent = motionEnabled ? 'Desativar animações' : 'Ativar animações';
        }
        document.documentElement.classList.toggle('has-motion', motionEnabled);
        if (motionEnabled) scheduleMotion();
    }

    function restorePreference() {
        try {
            const saved = localStorage.getItem(preference);
            override = saved === 'on' || saved === 'off' ? saved === 'on' : undefined;
        } catch {
            // A preferência vale nesta visita mesmo com armazenamento indisponível.
        }
        syncMotion();
    }

    toggle?.addEventListener('click', () => {
        override = !motionEnabled;
        try {
            localStorage.setItem(preference, override ? 'on' : 'off');
        } catch {
            // A escolha já está ativa na página.
        }
        syncMotion();
    });
    systemMotion.addEventListener('change', syncMotion);
    if (monitor || reveals.length) {
        window.addEventListener('scroll', scheduleMotion, { passive: true });
        window.addEventListener('resize', scheduleMotion);
        document.fonts?.ready.then(scheduleMotion);
    }
    window.addEventListener('pageshow', restorePreference);
    restorePreference();
})();

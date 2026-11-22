(() => {
    const demo = document.querySelector('.demo-view');
    if (!demo) return;

    const screen = demo.querySelector('.demo-window');
    const scenes = [...demo.querySelectorAll('.demo-scene')];
    const frames = [...demo.querySelectorAll('.demo-frame')];
    const stepLinks = [...document.querySelectorAll('.demo-step-nav a')];
    const moduleLabel = screen.querySelector('[data-demo-module]');
    const valueLabel = screen.querySelector('[data-demo-value]');
    const stageFits = matchMedia('(min-width: 68rem) and (min-height: 44rem)');
    const canClip = CSS.supports('clip-path', 'inset(0 0 0 50%)');
    const wipeStart = .71;
    const wipeEnd = .42;
    let loading = false;
    let ready = false;
    let active = -1;
    let pending;
    let visualScroll = window.scrollY;
    let lastFrame;

    function progress(top, start, end) {
        const value = Math.max(0, Math.min(1, (start - top) / (start - end)));
        return value * value * (3 - 2 * value);
    }

    function scrollOffset(timestamp, height) {
        const position = window.scrollY;
        const distance = position - visualScroll;
        if (screen.hidden || Math.abs(distance) < .65 || Math.abs(distance) > height * .75) {
            visualScroll = position;
            lastFrame = undefined;
            return 0;
        }

        // Suaviza os degraus da roda, sem mover a página ou acumular atraso entre quadros.
        const elapsed = lastFrame === undefined ? 1000 / 60 : timestamp - lastFrame;
        visualScroll += distance * (1 - Math.exp(-elapsed / 58));
        lastFrame = timestamp;
        schedule();
        return position - visualScroll;
    }

    function update(timestamp) {
        pending = undefined;
        const height = window.innerHeight;
        const offset = scrollOffset(timestamp, height);
        const tops = scenes.map(scene => scene.getBoundingClientRect().top + offset);
        const end = height * (screen.hidden ? .47 : wipeEnd);
        let base = 0;
        tops.forEach((top, index) => {
            if (top <= end) base = index;
        });
        const next = base + 1;
        const after = tops[next] ?? Infinity;
        const wipe = progress(after, height * wipeStart, end);
        const selected = !screen.hidden && wipe >= .5 ? next : base;

        if (selected !== active) {
            active = selected;
            stepLinks.forEach((link, index) => {
                if (index === selected) link.setAttribute('aria-current', 'step');
                else link.removeAttribute('aria-current');
            });
            moduleLabel.textContent = frames[selected].dataset.module;
            valueLabel.textContent = frames[selected].dataset.value;
        }
        if (screen.hidden) return;

        // O foco recua antes da troca. A próxima captura cobre a anterior com uma borda opaca.
        const focus = progress(tops[base], height * .41, height * .15)
            * (1 - progress(after, height * .84, height * wipeStart));
        frames.forEach((frame, index) => {
            const outgoing = index === base;
            const incoming = index === next && wipe > 0;
            const hidden = !outgoing && !incoming;
            if (frame.hidden !== hidden) frame.hidden = hidden;
            if (frame.hidden) return;
            frame.style.setProperty('--demo-inset', incoming ? (100 * (1 - wipe)).toFixed(3) + '%' : '0%');
            frame.style.setProperty('--demo-focus', outgoing ? focus.toFixed(3) : '0');
        });
    }

    function schedule() {
        pending ??= requestAnimationFrame(update);
    }

    function resetScroll() {
        visualScroll = window.scrollY;
        lastFrame = undefined;
        schedule();
    }

    function syncLayout() {
        const enabled = canClip && stageFits.matches && document.documentElement.classList.contains('has-motion');
        const pinned = enabled && ready;
        demo.classList.toggle('is-pinned', pinned);
        screen.hidden = !pinned;
        resetScroll();
        if (!enabled || loading) return;

        loading = true;
        const images = frames.map(frame => frame.querySelector('img'));
        Promise.all(images.map(image => {
            image.src = image.dataset.src;
            return image.decode();
        })).then(() => {
            ready = true;
            syncLayout();
        }, () => {
            // As capturas na sequência de leitura continuam disponíveis.
        });
    }

    stageFits.addEventListener('change', syncLayout);
    new MutationObserver(syncLayout).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', resetScroll);
    window.addEventListener('hashchange', resetScroll);
    window.addEventListener('pageshow', syncLayout);
    document.fonts?.ready.then(schedule);
    syncLayout();
})();

const gallery = document.getElementById('galeria');

if (gallery && typeof gallery.showModal === 'function') {
    const image = gallery.querySelector('img');
    const caption = gallery.querySelector('figcaption');
    const viewport = gallery.querySelector('.gallery-viewport');
    const zoom = gallery.querySelector('#alternar-zoom');
    const zoomLabel = zoom.querySelector('span');
    const zoomIcon = zoom.querySelector('i');
    let activeLink;

    function setZoom(expanded) {
        gallery.classList.toggle('zoomed', expanded);
        zoomLabel.textContent = expanded ? 'Ajustar à janela' : 'Ampliar';
        zoomIcon.classList.toggle('fa-magnifying-glass-plus', !expanded);
        zoomIcon.classList.toggle('fa-magnifying-glass-minus', expanded);
        viewport.scrollTo(0, 0);
        if (expanded) viewport.focus();
    }

    document.querySelectorAll('[data-gallery]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;

            event.preventDefault();
            activeLink = link;
            setZoom(false);
            image.alt = link.querySelector('img').alt;
            image.src = link.href;
            caption.textContent = link.closest('figure').querySelector('figcaption').textContent;
            gallery.showModal();
        });
    });

    zoom.addEventListener('click', () => setZoom(!gallery.classList.contains('zoomed')));

    gallery.addEventListener('close', async () => {
        if (gallery.open) return;
        activeLink?.focus({ preventScroll: true });

        // O diálogo permanece visível até o fim da transição discreta de saída.
        const animations = gallery.getAnimations?.() ?? [];
        await Promise.allSettled(animations.map((animation) => animation.finished));

        if (!gallery.open) {
            setZoom(false);
            image.removeAttribute('src');
        }
    });
}

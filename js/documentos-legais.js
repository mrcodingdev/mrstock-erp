const documentoLegal = document.querySelector('.legal-page');
const secoesDoDocumento = [...documentoLegal.querySelectorAll('.legal-section')];
const linksDoIndice = documentoLegal.querySelectorAll('.index a');

function atualizarIndiceLegal() {
    let atual = secoesDoDocumento[0];
    for (const secao of secoesDoDocumento) {
        if (secao.getBoundingClientRect().top > 112) break;
        atual = secao;
    }

    for (const link of linksDoIndice) {
        if (link.hash === '#' + atual.id) {
            link.setAttribute('aria-current', 'location');
        } else {
            link.removeAttribute('aria-current');
        }
    }
}

window.addEventListener('scroll', atualizarIndiceLegal, { passive: true });
window.addEventListener('hashchange', atualizarIndiceLegal);
atualizarIndiceLegal();

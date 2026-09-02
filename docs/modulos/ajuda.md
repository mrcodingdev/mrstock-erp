# 📚 Módulo: Central de Ajuda & Base de Conhecimento
**Arquivo Principal:** `ajuda.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
Serve como o manual operacional digital interativo da Papelaria Real. Centraliza procedimentos passo a passo para o balcão (registro de vendas, concessão de desconto, emissão de NFC-e, cadastro de clientes), a mesa tátil de atalhos de teclado do PDV e canais de suporte técnico.

---

## 2. Interface & Componentes Visuais
- **Hero de Busca Search-First:** Campo de busca em tempo real com contador dinâmico de resultados em `.tabular-nums`.
- **Chips de Filtro Rápido:** Categorização instantânea por áreas operacionais (`Frente de Caixa`, `Estoque`, `Clientes`, `Relatórios`).
- **Mesa de Atalhos de Teclado:** Tabela com `<kbd class="so-kbd">` contendo teclas de atalho (<kbd>F2</kbd>, <kbd>F4</kbd>, <kbd>F7</kbd>, <kbd>F8</kbd>, <kbd>F9</kbd>, <kbd>ESC</kbd>), ação e descrição de fluxo no balcão.
- **Acordeões dos Manuais Operacionais:** 9 módulos estruturados com passos numerados 1, 2, 3 e caixas de dicas operacionais.
- **Card de Contato & Suporte:** Botão circular verde oficial do WhatsApp (`.btn-whatsapp` de 22x22px) e botão para impressão do guia rápido.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Motor de Busca em Tempo Real em Vanilla JavaScript
```javascript
function filtrarAjuda(termo) {
    termo = termo.toLowerCase().trim();
    const items = document.querySelectorAll('.ajuda-accordion-item');
    let visiveis = 0;
    
    items.forEach(item => {
        const texto = item.textContent.toLowerCase();
        if (texto.includes(termo) || termo === '') {
            item.style.display = '';
            visiveis++;
            if (termo.length > 2) {
                const collapseEl = item.querySelector('.accordion-collapse');
                if (collapseEl && !collapseEl.classList.contains('show')) {
                    new bootstrap.Collapse(collapseEl, { show: true });
                }
            }
        } else {
            item.style.display = 'none';
        }
    });
    
    const countEl = document.getElementById('ajudaResultadosCount');
    if (countEl) countEl.textContent = visiveis;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Filtro Semântico de Perfil:** Tópicos administrativos (DRE, Compras, Backup) são filtrados automaticamente se o usuário logado for `caixa`.
- **Links Seguros:** Links externos abrem em nova aba com `rel="noopener noreferrer"`.

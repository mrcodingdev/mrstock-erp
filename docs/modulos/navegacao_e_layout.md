# 🎨 Módulo: Navegação, Topbar Limpa & Design System
**Arquivos Principais:** `inc/header.php`, `inc/footer.php`, `css/style.css`  
**Escopo de Acesso:** Compartilhado em todas as telas

---

## 1. Objetivo & Contexto de Negócio
Define o padrão visual e ergonômico universal do MrStock ERP. Implementa as diretrizes do **SalesOps Design System** e as **14 Zonas de Blindagem Visual Anti-Slop**, garantindo uma interface profissional, ágil e livre de poluição visual.

---

## 2. Interface & Componentes Visuais
- **Topbar Limpa (Regra #2 do GEMINI.md):** Exibe exclusivamente o título direto da página atual (ex: `Frente de Caixa`, `Estoque & Produtos`, `Dashboard`), sem prefixos redundantes ou badges decorativos.
- **Sidebar Colapsável com Anti-FOUC:** Menu lateral expansível/recolhível com memória de estado em `localStorage` e script inline que previne saltos visuais no primeiro paint (CLS 0.000).
- **Botões 100% Sólidos de Fábrica (Regra #1):** Preenchimento sólido, texto branco puro e escurecimento suave no hover.
- **Animações Fluidas Globais (Regra #17):** `@keyframes salesOpsSlideInLeft` (0.5s) presente em todos os cards, tabelas e abas.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Script Inline Anti-FOUC em `inc/header.php`
```html
<script>
  // Executado imediatamente antes do render para evitar Cumulative Layout Shift
  (function() {
    var savedState = localStorage.getItem('sidebar_collapsed');
    if (savedState === 'true') {
      document.documentElement.classList.add('sidebar-is-collapsed');
    }
  })();
</script>
```

### 3.2 Padronização de Cores e Tokens no `css/style.css`
```css
:root {
  --mr-primary: #284936;
  --mr-primary-dark: #1e3628;
  --mr-header-bg: #1a2421;
  --mr-sidebar-bg: #222d31;
  --mr-slate: #0f172a;
  --mr-border: #cbd5e1;
  --mr-radius: 0.625rem;
}

/* Animação Global Fluida */
@keyframes salesOpsSlideInLeft {
  from { opacity: 0; transform: translateX(-16px); }
  to { opacity: 1; transform: translateX(0); }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Menu Condicional:** Itens de menu restritos (Compras, Fornecedores, Relatórios, Configurações) são ocultados no HTML quando o usuário logado possui perfil `caixa`.

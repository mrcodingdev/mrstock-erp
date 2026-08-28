    </div><!-- /main-panel -->

    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php if (!empty($extraScripts)) echo $extraScripts; ?>
    <script>
    /**
     * Alternância Fluida da Sidebar Retrátil no Desktop (Método Osmani & Persistência)
     */
    function toggleSidebarCollapse() {
        const isCollapsed = document.body.classList.toggle('sidebar-collapsed');
        localStorage.setItem('mrstock_sidebar_state', isCollapsed ? 'collapsed' : 'expanded');
        
        // Emite evento de resize para gráficos ou tabelas responsivas se ajustarem
        window.dispatchEvent(new Event('resize'));
    }

    /**
     * Alternância do Drawer da Sidebar no Mobile
     */
    function toggleSidebar() {
        const sidebar = document.getElementById('soSidebar');
        const overlay = document.querySelector('.mobile-overlay');
        if (sidebar) sidebar.classList.toggle('mobile-open');
        if (overlay) overlay.classList.toggle('show');
    }

    /**
     * Alternância do Popover de Perfil do Usuário
     */
    function toggleProfileDropdown() {
        const profile = document.getElementById('soProfile');
        if (profile) {
            profile.classList.toggle('is-open');
        }
    }

    /**
     * Listeners de Inicialização e Interatividade
     */
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Restaura o estado da sidebar a partir do localStorage
        try {
            const savedState = localStorage.getItem('mrstock_sidebar_state');
            if (savedState === 'collapsed') {
                document.body.classList.add('sidebar-collapsed');
            } else {
                document.body.classList.remove('sidebar-collapsed');
            }
        } catch (e) {
            console.warn('LocalStorage indisponível para restaurar sidebar:', e);
        } finally {
            document.documentElement.classList.remove('sidebar-collapsed-preload');
        }

        // 2. Comportamento Acordeão Inteligente nos Itens da Sidebar (SalesOps Accordion)
        document.querySelectorAll('[data-accordion-toggle]').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const parentItem = button.closest('.so-nav__item');
                if (!parentItem) return;

                // Se a sidebar estiver recolhida (collapsed), o clique em qualquer menu grupo EXPANDIRÁ a sidebar automaticamente!
                if (document.body.classList.contains('sidebar-collapsed')) {
                    document.body.classList.remove('sidebar-collapsed');
                    localStorage.setItem('mrstock_sidebar_state', 'expanded');
                    
                    // Abre o grupo selecionado
                    parentItem.classList.add('is-open');
                    button.setAttribute('aria-expanded', 'true');
                    window.dispatchEvent(new Event('resize'));
                    return;
                }

                const isOpen = parentItem.classList.contains('is-open');
                parentItem.classList.toggle('is-open', !isOpen);
                button.setAttribute('aria-expanded', !isOpen ? 'true' : 'false');
            });
        });

        // 3. Fechamento do Dropdown de Perfil ao Clicar Fora ou Pressionar ESC
        document.addEventListener('click', function(event) {
            const profile = document.getElementById('soProfile');
            if (profile && profile.classList.contains('is-open')) {
                if (!profile.contains(event.target)) {
                    profile.classList.remove('is-open');
                }
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const profile = document.getElementById('soProfile');
                if (profile && profile.classList.contains('is-open')) {
                    profile.classList.remove('is-open');
                }
            }
        });

        // 4. Correção Soberana de Z-Index para Dropdowns em Linhas de Tabelas
        document.addEventListener('show.bs.dropdown', function(e) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.style.zIndex = '1050';
                tr.style.position = 'relative';
            }
        });
        document.addEventListener('hidden.bs.dropdown', function(e) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.style.zIndex = '';
                tr.style.position = '';
            }
        });
    });
    </script>
</body>
</html>

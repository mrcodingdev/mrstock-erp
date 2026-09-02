        <!-- ===== RODAPÉ INSTITUCIONAL CORPORATIVO (ESTILO GESTÃOCLICK COMPACTO) ===== -->
        <footer class="so-footer-gestaoclick" style="background: #222d31 !important; border-top: 1px solid rgba(255, 255, 255, 0.08) !important; color: #cbd5e1 !important; margin-top: auto;">
            <div class="so-footer-gestaoclick__container">
                <div class="row g-3 align-items-center">
                    <!-- COLUNA 1: Marca & Sistema (Enxuto com Logo Oficial) -->
                    <div class="col-lg-4 col-md-5 col-12">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <img src="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" alt="MrStock Logo" style="width: 24px; height: 24px; object-fit: contain;">
                            <span class="fw-bold text-white fs-6">MrStock ERP</span>
                            <span class="badge" style="background: #284936; color: #fff; font-size: 0.7rem;">v2.1.0</span>
                        </div>
                        <div style="color: #cbd5e1; font-size: 0.8125rem;">Papelaria Real • Sistema Integrado de Gestão & PDV</div>
                    </div>

                    <!-- COLUNA 2: Mr. Coding (Compacta e Direta) -->
                    <div class="col-lg-4 col-md-4 col-12 text-md-center">
                        <div class="fw-bold text-white fs-6 mb-1"><i class="fas fa-code text-info me-1"></i> Mr. Coding</div>
                        <div style="color: #cbd5e1; font-size: 0.8125rem; font-weight: 500;">Douglas • Nikolas • Cesar • Enzo • Sugahara</div>
                    </div>

                    <!-- COLUNA 3: Precisando de Suporte? (3 botões circulares compactos + telefone) -->
                    <div class="col-lg-4 col-md-3 col-12 text-md-end text-center">
                        <div class="fw-bold text-white fs-6 mb-2">Precisando de suporte?</div>
                        <div class="d-inline-flex align-items-center justify-content-md-end justify-content-center gap-3">
                            <!-- WhatsApp -->
                            <a href="https://wa.me/5511987654321" target="_blank" rel="noopener noreferrer" class="so-footer-gestaoclick__support-item" title="Falar no WhatsApp">
                                <div class="so-footer-gestaoclick__support-circle so-footer-gestaoclick__support-circle--wa">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <span>WhatsApp</span>
                            </a>
                            <!-- Atendimento -->
                            <a href="mailto:contato@mrstock.com.br" class="so-footer-gestaoclick__support-item" title="Enviar E-mail">
                                <div class="so-footer-gestaoclick__support-circle so-footer-gestaoclick__support-circle--help">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <span>Atendimento</span>
                            </a>
                            <!-- Central de Ajuda -->
                            <a href="<?= BASE_URL ?>/ajuda.php" class="so-footer-gestaoclick__support-item" title="Acessar FAQ">
                                <div class="so-footer-gestaoclick__support-circle so-footer-gestaoclick__support-circle--faq">
                                    <i class="fas fa-comments"></i>
                                </div>
                                <span>Ajuda</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- SUB-BARRA INFERIOR (1 Linha Compacta) -->
                <div class="so-footer-gestaoclick__bottom mt-2 pt-2" style="border-top: 1px solid rgba(255,255,255,0.08); font-size: 0.75rem; color: #94a3b8;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center">
                        <span>MrStock ERP © 2026 Papelaria Real</span>
                        <span>(11) 98765-4321 • contato@mrstock.com.br</span>
                    </div>
                </div>
            </div>
        </footer>
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

        // 4. Correção Soberana de Z-Index e Overflow para Dropdowns em Linhas de Tabelas
        document.addEventListener('show.bs.dropdown', function(e) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.style.zIndex = '1050';
                tr.style.position = 'relative';
            }
            const tableResp = e.target.closest('.table-responsive');
            if (tableResp) {
                tableResp.classList.add('dropdown-active');
            }
            const card = e.target.closest('.so-card, .card');
            if (card) {
                card.style.overflow = 'visible';
            }
        });
        document.addEventListener('hidden.bs.dropdown', function(e) {
            const tr = e.target.closest('tr');
            if (tr) {
                tr.style.zIndex = '';
                tr.style.position = '';
            }
            const tableResp = e.target.closest('.table-responsive');
            if (tableResp) {
                tableResp.classList.remove('dropdown-active');
            }
        });
    });
    </script>
</body>
</html>

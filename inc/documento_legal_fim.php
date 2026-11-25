            </div>
        </div>
    </<?= $isLoggedIn ? 'article' : 'main' ?>>
    <footer class="footer">
        <div class="wrap footer-row">
            <span>MrStock ERP © 2026 Equipe Mr. Coding</span>
            <nav class="footer-nav" aria-label="Documentos no rodapé">
                <?php foreach ($documentosLegais as $slug => $titulo): ?>
                    <a href="<?= BASE_URL ?>/<?= $slug ?>.php"<?= $slug === $documentoLegal ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($titulo) ?></a>
                <?php endforeach; ?>
                <?php if ($isLoggedIn): ?>
                    <a href="<?= BASE_URL ?>/dashboard.php">Retornar ao Dashboard</a>
                <?php endif; ?>
            </nav>
            <?php if (!$isLoggedIn): ?>
                <div class="footer-nav">
                    <a href="tel:+5515996441796">(15) 99644-1796</a>
                    <a href="mailto:contato@mrstock.com.br">contato@mrstock.com.br</a>
                </div>
            <?php endif; ?>
        </div>
    </footer>
</div>
<script src="<?= BASE_URL ?>/js/documentos-legais.js?v=1" defer></script>
<?php if ($isLoggedIn): ?>
    <?php require __DIR__ . '/footer.php'; ?>
<?php else: ?>
    <script src="<?= BASE_URL ?>/js/bootstrap.bundle.min.js"></script>
    <?php require __DIR__ . '/cookie_banner.php'; ?>
</body>
</html>
<?php endif; ?>

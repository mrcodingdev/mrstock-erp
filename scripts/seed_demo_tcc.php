<?php
/**
 * MrStock ERP - Script de Povoamento de Dados para Demonstração do TCC
 * Papelaria Real (Sueli & Osnir) - Sorocaba/SP
 * 
 * Executa seeding determinístico de alta fidelidade:
 * 1. 10 Micro-categorias oficiais de Papelaria Real
 * 2. 5 Fornecedores oficiais (Tilibra, BIC, Faber-Castell, Acrilex, Chamex)
 * 3. 12 Clientes com CPFs fictícios e contatos de Sorocaba/SP
 * 4. 50 Produtos (exatamente 5 por categoria) com marcas reais, códigos EAN-13 únicos
 * 5. 20 Lotes ativos, sendo 5 com vencimento em < 30 dias (Nov 2026 - Alerta Dashboard)
 *    Garantia de que produtos.quantidade = soma dos lotes para itens com lotes
 * 6. 12 Compras históricas com itens de compra e movimentações de entrada (Jul a Out 2026)
 * 7. ~540 a ~570 Vendas distribuídas em TODOS os 120 dias contínuos (01/07/2026 a 28/10/2026)
 *    com cupons fiscais NFC-e (44 dígitos) e movimentações de estoque (saida_venda)
 * 8. Distribuição calibrada de Outubro/2026 (4 semanas harmônicas, segundas calmas, sex/sáb fortes e
 *    faturamento por categoria decrescente estrito e balanceado).
 */

require_once __DIR__ . '/../inc/database.php';
require_once __DIR__ . '/seed_core.php';

$res = executarSeedingTCC($pdo, false);
if (!$res['sucesso']) {
    exit(1);
}
exit(0);

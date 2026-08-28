<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acesso negado: este script só pode ser executado via linha de comando (CLI).\n");
}
/**
 * Script de Seed e Higienização de Dados Realistas — MrStock ERP
 */
require_once __DIR__ . '/../inc/database.php';

echo "Iniciando seed de dados realistas...\n";

try {
    $pdo->beginTransaction();

    // 1. Garantir Categorias Oficiais
    $categorias = [
        ['nome' => 'Papelaria',           'descricao' => 'Cadernos, blocos, papéis e envelopes'],
        ['nome' => 'Escrita & Escritório', 'descricao' => 'Canetas, lápis, marca-textos e corretores'],
        ['nome' => 'Escolar',             'descricao' => 'Borrachas, apontadores, colas e tesouras'],
        ['nome' => 'Artes & Pintura',     'descricao' => 'Tintas, pincéis, lápis de cor e guache'],
        ['nome' => 'Organização',         'descricao' => 'Pastas, arquivos, grampeadores e organizadores'],
        ['nome' => 'Desenho & Técnico',   'descricao' => 'Réguas, esquadros, compassos e pranchetas']
    ];

    $catMap = [];
    foreach ($categorias as $cat) {
        $stmtCheck = $pdo->prepare("SELECT id FROM categorias WHERE nome = ?");
        $stmtCheck->execute([$cat['nome']]);
        $id = $stmtCheck->fetchColumn();
        if (!$id) {
            $stmtIns = $pdo->prepare("INSERT INTO categorias (nome, descricao) VALUES (?, ?)");
            $stmtIns->execute([$cat['nome'], $cat['descricao']]);
            $id = $pdo->lastInsertId();
        }
        $catMap[$cat['nome']] = $id;
    }

    // 2. Garantir Fornecedores
    $fornecedores = [
        ['nome' => 'Tilibra S.A', 'cnpj' => '44.990.901/0001-43', 'telefone' => '(14) 3235-4000', 'email' => 'vendas@tilibra.com.br'],
        ['nome' => 'Bic Brasil',  'cnpj' => '04.148.243/0001-16', 'telefone' => '(11) 2118-8000', 'email' => 'comercial@bic.com'],
        ['nome' => 'Acrilex',     'cnpj' => '50.334.808/0001-38', 'telefone' => '(11) 4344-8800', 'email' => 'contato@acrilex.com']
    ];

    $fornMap = [];
    foreach ($fornecedores as $forn) {
        $stmtCheck = $pdo->prepare("SELECT id FROM fornecedores WHERE nome = ?");
        $stmtCheck->execute([$forn['nome']]);
        $id = $stmtCheck->fetchColumn();
        if (!$id) {
            $stmtIns = $pdo->prepare("INSERT INTO fornecedores (nome, cnpj, telefone, email, status) VALUES (?, ?, ?, ?, 'ativo')");
            $stmtIns->execute([$forn['nome'], $forn['cnpj'], $forn['telefone'], $forn['email']]);
            $id = $pdo->lastInsertId();
        }
        $fornMap[$forn['nome']] = $id;
    }

    // 3. Limpeza de Produtos de Teste / Incoerentes
    $pdo->exec("
        DELETE FROM produtos 
        WHERE nome LIKE '%test%' 
           OR nome LIKE '%autotest%' 
           OR nome LIKE '%csrf%' 
           OR nome LIKE '%explore%' 
           OR (preco_compra > preco_venda AND preco_venda > 0)
    ");

    // 4. Catálogo dos 15 Produtos Realistas de Papelaria
    $catalogo = [
        [
            'nome' => 'Caderno Universitário 10 Matérias Spiral',
            'categoria' => 'Papelaria',
            'categoria_id' => $catMap['Papelaria'],
            'quantidade' => 45,
            'estoque_minimo' => 10,
            'validade' => null,
            'preco_venda' => 24.90,
            'preco_compra' => 11.50,
            'fornecedor_id' => $fornMap['Tilibra S.A'],
            'codigo_de_barra' => '7891027101015'
        ],
        [
            'nome' => 'Caneta Esferográfica Azul 0.7mm Caixa c/ 50',
            'categoria' => 'Escrita & Escritório',
            'categoria_id' => $catMap['Escrita & Escritório'],
            'quantidade' => 18,
            'estoque_minimo' => 5,
            'validade' => '2028-12-31',
            'preco_venda' => 49.90,
            'preco_compra' => 28.00,
            'fornecedor_id' => $fornMap['Bic Brasil'],
            'codigo_de_barra' => '7891027101022'
        ],
        [
            'nome' => 'Resma Papel Sulfite A4 75g Chamex 500fls',
            'categoria' => 'Papelaria',
            'categoria_id' => $catMap['Papelaria'],
            'quantidade' => 60,
            'estoque_minimo' => 20,
            'validade' => null,
            'preco_venda' => 32.00,
            'preco_compra' => 19.50,
            'fornecedor_id' => $fornMap['Tilibra S.A'],
            'codigo_de_barra' => '7891027101039'
        ],
        [
            'nome' => 'Lápis Grafite HB Nº 2 Faber-Castell Caixa c/ 12',
            'categoria' => 'Escrita & Escritório',
            'categoria_id' => $catMap['Escrita & Escritório'],
            'quantidade' => 35,
            'estoque_minimo' => 10,
            'validade' => null,
            'preco_venda' => 14.50,
            'preco_compra' => 7.20,
            'fornecedor_id' => $fornMap['Bic Brasil'],
            'codigo_de_barra' => '7891027101046'
        ],
        [
            'nome' => 'Borracha Branca com Cinta Plástica Mercur',
            'categoria' => 'Escolar',
            'categoria_id' => $catMap['Escolar'],
            'quantidade' => 80,
            'estoque_minimo' => 25,
            'validade' => null,
            'preco_venda' => 2.50,
            'preco_compra' => 0.90,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101053'
        ],
        [
            'nome' => 'Apontador com Depósito Faber-Castell',
            'categoria' => 'Escolar',
            'categoria_id' => $catMap['Escolar'],
            'quantidade' => 25,
            'estoque_minimo' => 10,
            'validade' => null,
            'preco_venda' => 5.90,
            'preco_compra' => 2.40,
            'fornecedor_id' => $fornMap['Bic Brasil'],
            'codigo_de_barra' => '7891027101060'
        ],
        [
            'nome' => 'Marca-Texto Amarelo Fluorescente',
            'categoria' => 'Escrita & Escritório',
            'categoria_id' => $catMap['Escrita & Escritório'],
            'quantidade' => 50,
            'estoque_minimo' => 15,
            'validade' => '2028-06-30',
            'preco_venda' => 4.90,
            'preco_compra' => 2.10,
            'fornecedor_id' => $fornMap['Bic Brasil'],
            'codigo_de_barra' => '7891027101077'
        ],
        [
            'nome' => 'Cola Bastão 40g Pritt',
            'categoria' => 'Escolar',
            'categoria_id' => $catMap['Escolar'],
            'quantidade' => 30,
            'estoque_minimo' => 10,
            'validade' => '2027-10-31',
            'preco_venda' => 11.90,
            'preco_compra' => 5.80,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101084'
        ],
        [
            'nome' => 'Tesoura Escolar Sem Ponta Mundial',
            'categoria' => 'Escolar',
            'categoria_id' => $catMap['Escolar'],
            'quantidade' => 40,
            'estoque_minimo' => 10,
            'validade' => null,
            'preco_venda' => 7.90,
            'preco_compra' => 3.50,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101091'
        ],
        [
            'nome' => 'Régua Cristal 30cm Waleu',
            'categoria' => 'Desenho & Técnico',
            'categoria_id' => $catMap['Desenho & Técnico'],
            'quantidade' => 65,
            'estoque_minimo' => 15,
            'validade' => null,
            'preco_venda' => 3.00,
            'preco_compra' => 1.10,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101107'
        ],
        [
            'nome' => 'Tinta Guache 6 Cores 15ml Acrilex',
            'categoria' => 'Artes & Pintura',
            'categoria_id' => $catMap['Artes & Pintura'],
            'quantidade' => 28,
            'estoque_minimo' => 10,
            'validade' => '2027-08-30',
            'preco_venda' => 8.90,
            'preco_compra' => 4.20,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101114'
        ],
        [
            'nome' => 'Caixa de Lápis de Cor 24 Cores Ecolápis',
            'categoria' => 'Artes & Pintura',
            'categoria_id' => $catMap['Artes & Pintura'],
            'quantidade' => 22,
            'estoque_minimo' => 8,
            'validade' => null,
            'preco_venda' => 29.90,
            'preco_compra' => 16.00,
            'fornecedor_id' => $fornMap['Acrilex'],
            'codigo_de_barra' => '7891027101121'
        ],
        [
            'nome' => 'Pasta Suspensa Kraft Dello',
            'categoria' => 'Organização',
            'categoria_id' => $catMap['Organização'],
            'quantidade' => 55,
            'estoque_minimo' => 15,
            'validade' => null,
            'preco_venda' => 4.50,
            'preco_compra' => 1.80,
            'fornecedor_id' => $fornMap['Tilibra S.A'],
            'codigo_de_barra' => '7891027101138'
        ],
        [
            'nome' => 'Grampeador de Mesa Médio 26/6',
            'categoria' => 'Organização',
            'categoria_id' => $catMap['Organização'],
            'quantidade' => 12,
            'estoque_minimo' => 5,
            'validade' => null,
            'preco_venda' => 26.50,
            'preco_compra' => 14.00,
            'fornecedor_id' => $fornMap['Bic Brasil'],
            'codigo_de_barra' => '7891027101145'
        ],
        [
            'nome' => 'Bloco de Notas Adesivas Post-it 76x76mm',
            'categoria' => 'Papelaria',
            'categoria_id' => $catMap['Papelaria'],
            'quantidade' => 40,
            'estoque_minimo' => 15,
            'validade' => null,
            'preco_venda' => 9.90,
            'preco_compra' => 4.50,
            'fornecedor_id' => $fornMap['Tilibra S.A'],
            'codigo_de_barra' => '7891027101152'
        ]
    ];

    $prodIds = [];
    foreach ($catalogo as $prod) {
        $stmtCheck = $pdo->prepare("SELECT id FROM produtos WHERE nome = ?");
        $stmtCheck->execute([$prod['nome']]);
        $existingId = $stmtCheck->fetchColumn();

        if ($existingId) {
            $stmtUpd = $pdo->prepare("
                UPDATE produtos 
                SET categoria = ?, categoria_id = ?, quantidade = ?, estoque_minimo = ?, 
                    validade = ?, preco_venda = ?, preco_compra = ?, fornecedor_id = ?, 
                    status = 'ativo', codigo_de_barra = ?
                WHERE id = ?
            ");
            $stmtUpd->execute([
                $prod['categoria'], $prod['categoria_id'], $prod['quantidade'], $prod['estoque_minimo'],
                $prod['validade'], $prod['preco_venda'], $prod['preco_compra'], $prod['fornecedor_id'],
                $prod['codigo_de_barra'], $existingId
            ]);
            $prodIds[$prod['nome']] = $existingId;
        } else {
            $stmtIns = $pdo->prepare("
                INSERT INTO produtos 
                (nome, categoria, categoria_id, quantidade, estoque_minimo, validade, preco_venda, preco_compra, fornecedor_id, status, codigo_de_barra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', ?)
            ");
            $stmtIns->execute([
                $prod['nome'], $prod['categoria'], $prod['categoria_id'], $prod['quantidade'], $prod['estoque_minimo'],
                $prod['validade'], $prod['preco_venda'], $prod['preco_compra'], $prod['fornecedor_id'],
                $prod['codigo_de_barra']
            ]);
            $prodIds[$prod['nome']] = $pdo->lastInsertId();
        }
    }

    // 5. Garantir Histórico de Vendas com Faturamento e Lucro Positivos em Vários Períodos
    $temVendasRecentes = $pdo->query("SELECT COUNT(*) FROM vendas WHERE data_venda >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    
    if ($temVendasRecentes < 5) {
        $sampleSales = [
            ['offset_days' => 0, 'forma' => 'PIX', 'itens' => [
                ['nome' => 'Caderno Universitário 10 Matérias Spiral', 'qtd' => 2],
                ['nome' => 'Caneta Esferográfica Azul 0.7mm Caixa c/ 50', 'qtd' => 1]
            ]],
            ['offset_days' => 1, 'forma' => 'CARTÃO DE CRÉDITO', 'itens' => [
                ['nome' => 'Resma Papel Sulfite A4 75g Chamex 500fls', 'qtd' => 3],
                ['nome' => 'Marca-Texto Amarelo Fluorescente', 'qtd' => 4]
            ]],
            ['offset_days' => 2, 'forma' => 'DINHEIRO', 'itens' => [
                ['nome' => 'Caixa de Lápis de Cor 24 Cores Ecolápis', 'qtd' => 1],
                ['nome' => 'Tinta Guache 6 Cores 15ml Acrilex', 'qtd' => 2],
                ['nome' => 'Bloco de Notas Adesivas Post-it 76x76mm', 'qtd' => 1]
            ]],
            ['offset_days' => 3, 'forma' => 'PIX', 'itens' => [
                ['nome' => 'Grampeador de Mesa Médio 26/6', 'qtd' => 1],
                ['nome' => 'Pasta Suspensa Kraft Dello', 'qtd' => 5]
            ]],
            ['offset_days' => 4, 'forma' => 'CARTÃO DE DÉBITO', 'itens' => [
                ['nome' => 'Caderno Universitário 10 Matérias Spiral', 'qtd' => 3],
                ['nome' => 'Lápis Grafite HB Nº 2 Faber-Castell Caixa c/ 12', 'qtd' => 2]
            ]],
            ['offset_days' => 5, 'forma' => 'PIX', 'itens' => [
                ['nome' => 'Resma Papel Sulfite A4 75g Chamex 500fls', 'qtd' => 2],
                ['nome' => 'Cola Bastão 40g Pritt', 'qtd' => 3]
            ]],
            ['offset_days' => 6, 'forma' => 'DINHEIRO', 'itens' => [
                ['nome' => 'Tesoura Escolar Sem Ponta Mundial', 'qtd' => 2],
                ['nome' => 'Régua Cristal 30cm Waleu', 'qtd' => 4],
                ['nome' => 'Borracha Branca com Cinta Plástica Mercur', 'qtd' => 5]
            ]],
            ['offset_days' => 15, 'forma' => 'CARTÃO DE CRÉDITO', 'itens' => [
                ['nome' => 'Caixa de Lápis de Cor 24 Cores Ecolápis', 'qtd' => 2],
                ['nome' => 'Caderno Universitário 10 Matérias Spiral', 'qtd' => 2]
            ]],
            ['offset_days' => 22, 'forma' => 'PIX', 'itens' => [
                ['nome' => 'Resma Papel Sulfite A4 75g Chamex 500fls', 'qtd' => 5]
            ]],
            ['offset_days' => 45, 'forma' => 'DINHEIRO', 'itens' => [
                ['nome' => 'Caneta Esferográfica Azul 0.7mm Caixa c/ 50', 'qtd' => 2],
                ['nome' => 'Grampeador de Mesa Médio 26/6', 'qtd' => 2]
            ]]
        ];

        foreach ($sampleSales as $s) {
            $totalVenda = 0;
            $itemsToInsert = [];

            foreach ($s['itens'] as $it) {
                $pId = $prodIds[$it['nome']];
                $stmtP = $pdo->prepare("SELECT preco_venda FROM produtos WHERE id = ?");
                $stmtP->execute([$pId]);
                $precoUnit = (float)$stmtP->fetchColumn();
                $totalVenda += $precoUnit * $it['qtd'];
                $itemsToInsert[] = [
                    'produto_id' => $pId,
                    'quantidade' => $it['qtd'],
                    'preco_unitario' => $precoUnit
                ];
            }

            $dateVenda = date('Y-m-d H:i:s', strtotime("-{$s['offset_days']} days"));
            $stmtV = $pdo->prepare("INSERT INTO vendas (cliente_id, data_venda, total, forma_pagamento) VALUES (NULL, ?, ?, ?)");
            $stmtV->execute([$dateVenda, $totalVenda, $s['forma']]);
            $vendaId = $pdo->lastInsertId();

            $stmtVI = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            foreach ($itemsToInsert as $iti) {
                $stmtVI->execute([$vendaId, $iti['produto_id'], $iti['quantidade'], $iti['preco_unitario']]);
            }

            // Cupom Fiscal
            $chave = '3526' . str_pad((string)$vendaId, 40, '0', STR_PAD_LEFT);
            $stmtCF = $pdo->prepare("INSERT INTO cupons_fiscais (venda_id, chave_acesso, data_emissao) VALUES (?, ?, ?)");
            $stmtCF->execute([$vendaId, $chave, $dateVenda]);

            // Movimentação
            $stmtMov = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, data_movimento, observacao) VALUES (?, 'saida_venda', ?, ?, ?)");
            foreach ($itemsToInsert as $iti) {
                $stmtMov->execute([$iti['produto_id'], $iti['quantidade'], $dateVenda, "Venda PDV #{$vendaId}"]);
            }
        }
    }

    $pdo->commit();
    echo "Seed de dados concluído com sucesso!\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Erro ao executar seed: " . $e->getMessage() . "\n";
}

<?php
/**
 * MrStock ERP - Núcleo de Povoamento de Dados para Demonstração do TCC
 * Papelaria Real (Sueli & Osnir) - Sorocaba/SP
 * 
 * Reutilizável via CLI (seed_demo_tcc.php) e Web Seguro (run_seed_hostinger.php)
 */

function executarSeedingTCC(PDO $pdo, bool $isWeb = false): array {
    date_default_timezone_set('America/Sao_Paulo');
    mt_srand(20261028); // Seed determinístico oficial
    
    $out = [];
    $logMsg = function(string $msg) use (&$out, $isWeb) {
        $out[] = $msg;
        if (!$isWeb) {
            echo $msg . "\n";
        }
    };

    $logMsg("========================================================");
    $logMsg(" MrStock ERP - Povoamento Oficial de Demonstração (4 Meses)");
    $logMsg(" Período: 01/07/2026 a 28/10/2026 (120 dias consecutivos)");
    $logMsg(" Data de Execução: " . date('Y-m-d H:i:s'));
    $logMsg("========================================================\n");

    try {
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $logMsg("[1/8] Limpando tabelas operacionais...");

        $tablesToTruncate = [
            'cupons_fiscais',
            'vendas_itens',
            'vendas',
            'itens_compra',
            'compras',
            'movimentacoes',
            'lotes',
            'produtos',
            'clientes',
            'fornecedores',
            'categorias',
            'logs'
        ];

        foreach ($tablesToTruncate as $tbl) {
            $pdo->exec("TRUNCATE TABLE `$tbl`;");
        }
        $logMsg(" -> 12 Tabelas operacionais limpas com sucesso.");

        // ── 1. CATEGORIAS (10 Micro-Categorias Oficiais) ──────────────────────
        $logMsg("\n[2/8] Inserindo as 10 Micro-Categorias Funcionais Oficiais...");
        $categorias = [
            [1, 'Cadernos & Blocos', 'Cadernos universitários, brochuras, blocos de anotações e post-it'],
            [2, 'Canetas & Marcadores', 'Canetas esferográficas, ponta fina, marca-texto e corretores'],
            [3, 'Lápis & Apontadores', 'Lápis grafite, lápis de cor e apontadores com depósito'],
            [4, 'Borrachas & Correção', 'Borrachas escolares, ponteiras e fitas corretivas'],
            [5, 'Colas & Fitas Adesivas', 'Cola bastão, cola líquida, fita crepe e fita transparente'],
            [6, 'Papéis & Folhas', 'Resmas sulfite A4, cartolinas, papel cartão e color set'],
            [7, 'Pastas & Organização', 'Pastas aba elástico, pastas suspensas e organizadores'],
            [8, 'Corte & Medição', 'Tesouras escolares, estiletes, réguas e esquadros'],
            [9, 'Tintas & Pintura', 'Tintas guache, aquarela, tintas para tecido e pincéis'],
            [10, 'Grampeadores & Fixação', 'Grampeadores de mesa, caixas de grampos e clips']
        ];

        $stmtCat = $pdo->prepare("INSERT INTO categorias (id, nome, descricao) VALUES (?, ?, ?)");
        foreach ($categorias as $cat) {
            $stmtCat->execute($cat);
        }
        $logMsg(" -> 10 Categorias cadastradas com sucesso.");

        // ── 2. FORNECEDORES (5 Fornecedores Oficiais) ──────────────────────────
        $logMsg("\n[3/8] Inserindo os 5 Fornecedores Oficiais de Papelaria...");
        $fornecedores = [
            [
                1, 'Tilibra S/A Produtos de Papelaria', '44.990.901/0001-43', '(14) 3235-4000',
                'vendas@tilibra.com.br', 'ativo', 'Roberto Almeida', 'Rodovia Marechal Rondon, Km 354',
                'S/N', 'Distrito Industrial', 'Bauru', 'SP', '17034-290'
            ],
            [
                2, 'BIC da Amazônia S/A', '04.148.243/0001-16', '(11) 2118-8000',
                'comercial@bic.com', 'ativo', 'Amanda Castro', 'Avenida das Nações Unidas',
                '12901', 'Brooklin Paulista', 'São Paulo', 'SP', '04578-000'
            ],
            [
                3, 'Faber-Castell Brasil Ltda', '61.064.838/0001-40', '(16) 2106-1000',
                'vendas@fabercastell.com.br', 'ativo', 'Fernando Silveira', 'Rua Cel. José Augusto de Oliveira Salles',
                '1876', 'Vila Izabel', 'São Carlos', 'SP', '13570-820'
            ],
            [
                4, 'Acrilex Tintas Especiais S/A', '50.334.808/0001-38', '(11) 4344-8800',
                'contato@acrilex.com.br', 'ativo', 'Marcos Vinicius', 'Estrada das Lágrimas',
                '130', 'Rudge Ramos', 'São Bernardo do Campo', 'SP', '09642-000'
            ],
            [
                5, 'International Paper / Chamex Papéis', '60.543.907/0001-20', '(19) 3874-7000',
                'atendimento@chamex.com.br', 'ativo', 'Carlos Alberto Duarte', 'Rodovia SP 340, Km 171',
                'S/N', 'Zona Rural', 'Mogi Guaçu', 'SP', '13840-970'
            ]
        ];

        $stmtForn = $pdo->prepare("
            INSERT INTO fornecedores (id, nome, cnpj, telefone, email, status, contato, endereco, numero, bairro, cidade, estado, cep)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        foreach ($fornecedores as $forn) {
            $stmtForn->execute($forn);
        }
        $logMsg(" -> 5 Fornecedores cadastrados com sucesso.");

        // ── 3. CLIENTES (12 Clientes de Sorocaba/SP) ──────────────────────────
        $logMsg("\n[4/8] Inserindo 12 Clientes cadastrados antes de Julho/2026...");
        $clientes = [
            [1, 'João da Silva Ramos', 'joao.ramos@gmail.com', '(15) 99124-8832', '219.482.908-14', 'Rua São Bento', '145', 'Centro', 'Sorocaba', 'SP', '18010-030'],
            [2, 'Maria Eduarda Oliveira', 'maria.eduarda@outlook.com', '(15) 99781-4421', '341.872.108-56', 'Avenida Antônio Carlos Comitre', '820', 'Parque Campolim', 'Sorocaba', 'SP', '18047-620'],
            [3, 'Carlos Alberto de Souza', 'carlos.asouza@terra.com.br', '(15) 98112-3390', '118.942.308-72', 'Rua Hermelino Matarazzo', '302', 'Vila Carvalho', 'Sorocaba', 'SP', '18060-000'],
            [4, 'Patrícia Helena Mendonça', 'patricia.mendonca@gmail.com', '(15) 99654-1287', '405.123.788-90', 'Rua Nogueira Martins', '512', 'Centro', 'Sorocaba', 'SP', '18010-230'],
            [5, 'Lucas Henrique Pereira', 'lucas.pereira@gmail.com', '(15) 99234-5678', '289.654.328-01', 'Rua Cel. Nogueira Padilha', '780', 'Vila Hortência', 'Sorocaba', 'SP', '18020-000'],
            [6, 'Beatriz Fernandes Santos', 'bia.fernandes@uol.com.br', '(15) 98845-6712', '378.912.458-33', 'Avenida General Carneiro', '1240', 'Vila Lucy', 'Sorocaba', 'SP', '18043-000'],
            [7, 'Rodrigo Moreira Lima', 'rodrigo.mlima@gmail.com', '(15) 99156-7890', '156.789.238-44', 'Avenida Elias Maluf', '450', 'Wanel Ville', 'Sorocaba', 'SP', '18055-500'],
            [8, 'Fernanda Cristina Gomes', 'fernanda.gomes@hotmail.com', '(15) 99712-3456', '421.365.898-77', 'Rua Aparecida', '920', 'Santa Rosália', 'Sorocaba', 'SP', '18095-000'],
            [9, 'Marcelo Antonio Barbosa', 'marcelo.barbosa@gmail.com', '(15) 98123-4567', '234.567.898-88', 'Avenida Pereira da Silva', '630', 'Jardim Santa Rosália', 'Sorocaba', 'SP', '18090-000'],
            [10, 'Camila Regina de Toledo', 'camila.toledo@yahoo.com.br', '(15) 99678-9012', '312.456.788-99', 'Rua da Penha', '340', 'Centro', 'Sorocaba', 'SP', '18010-001'],
            [11, 'Rafael Guimarães Castro', 'rafael.castro@gmail.com', '(15) 99201-2345', '198.765.438-12', 'Rua Álvaro Soares', '210', 'Centro', 'Sorocaba', 'SP', '18010-190'],
            [12, 'Juliana Silveira Prado', 'juliana.prado@gmail.com', '(15) 99765-4321', '456.123.988-65', 'Avenida Barão de Tatuí', '550', 'Jardim Vergueiro', 'Sorocaba', 'SP', '18030-000']
        ];

        $stmtCli = $pdo->prepare("
            INSERT INTO clientes (id, nome, email, telefone, cpf_cnpj, endereco, numero, bairro, cidade, estado, cep, status, data_cadastro)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', '2026-06-25 10:00:00')
        ");
        foreach ($clientes as $cli) {
            $stmtCli->execute($cli);
        }
        $logMsg(" -> 12 Clientes cadastrados com sucesso.");

        // ── 4. CATÁLOGO DOS 50 PRODUTOS (5 por Categoria) ──────────────────────
        $logMsg("\n[5/8] Carregando Catálogo dos 50 Produtos Oficiais...");
        $produtosBase = [
            // Categoria 1: Cadernos & Blocos (Fornecedor 1: Tilibra)
            [1, 'Caderno Universitário Capa Dura 10 Matérias Zip 200fls Tilibra', 1, 'Cadernos & Blocos', 15, null, 18.50, 34.90, 1, '7891027101015', 65],
            [2, 'Caderno Brochura Caligrafia Tilibra 40fls', 1, 'Cadernos & Blocos', 10, null, 4.80, 9.90, 1, '7891027101022', 80],
            [3, 'Bloco de Notas Adesivas Post-it 76x76mm Amarelo 100fls 3M', 1, 'Cadernos & Blocos', 15, null, 5.50, 12.50, 1, '7891027101039', 90],
            [4, 'Caderno de Desenho Cartografia Espiral Tilibra 80fls', 1, 'Cadernos & Blocos', 10, null, 8.20, 16.90, 1, '7891027101046', 45],
            [5, 'Bloco Flip Chart 50 Folhas 75g Tilibra', 1, 'Cadernos & Blocos', 5, null, 19.00, 38.00, 1, '7891027101053', 30],

            // Categoria 2: Canetas & Marcadores (Fornecedores 2: BIC e 3: Faber-Castell)
            [6, 'Caneta Esferográfica BIC Cristal Azul 1.0mm (Caixa c/ 50)', 2, 'Canetas & Marcadores', 10, '2027-12-31', 32.00, 54.90, 2, '7891027102012', 40],
            [7, 'Caneta Esferográfica BIC Cristal Preta 1.0mm (Caixa c/ 50)', 2, 'Canetas & Marcadores', 10, '2027-12-31', 32.00, 54.90, 2, '7891027102029', 35],
            [8, 'Marca-Texto Amarelo Fluorescente Faber-Castell Grifpen', 2, 'Canetas & Marcadores', 15, '2026-11-14', 2.30, 4.90, 3, '7891027102036', 75],
            [9, 'Caneta Hidrográfica 12 Cores Faber-Castell Bicolor', 2, 'Canetas & Marcadores', 10, '2027-10-15', 9.80, 19.90, 3, '7891027102043', 50],
            [10, 'Marcador para Quadro Branco BIC Marking Azul', 2, 'Canetas & Marcadores', 10, '2026-11-18', 4.20, 8.90, 2, '7891027102050', 60],

            // Categoria 3: Lápis & Apontadores (Fornecedor 3: Faber-Castell)
            [11, 'Lápis Grafite HB Nº 2 Faber-Castell Max (Caixa c/ 12)', 3, 'Lápis & Apontadores', 15, null, 8.50, 16.90, 3, '7891027103019', 70],
            [12, 'Caixa de Lápis de Cor 24 Cores Faber-Castell Ecolápis', 3, 'Lápis & Apontadores', 10, null, 17.50, 32.90, 3, '7891027103026', 55],
            [13, 'Caixa de Lápis de Cor 12 Cores Bicolor 24 Cores Faber-Castell', 3, 'Lápis & Apontadores', 10, null, 14.00, 26.50, 3, '7891027103033', 48],
            [14, 'Apontador com Depósito Faber-Castell Grip', 3, 'Lápis & Apontadores', 15, null, 3.10, 6.90, 3, '7891027103040', 85],
            [15, 'Lapiseira Técnica Poly Matic 0.7mm Faber-Castell', 3, 'Lápis & Apontadores', 8, null, 11.20, 21.90, 3, '7891027103057', 40],

            // Categoria 4: Borrachas & Correção (Fornecedores 3: Faber, 2: BIC)
            [16, 'Borracha Branca com Cinta Plástica Faber-Castell Dust Free', 4, 'Borrachas & Correção', 20, null, 1.80, 3.90, 3, '7891027104016', 110],
            [17, 'Fita Corretiva 5mm x 6m BIC Tipp-Ex Easy Refill', 4, 'Borrachas & Correção', 10, '2026-11-16', 5.90, 12.90, 2, '7891027104023', 55],
            [18, 'Corretivo Líquido à Base de Água 18ml BIC ECO', 4, 'Borrachas & Correção', 10, '2027-09-30', 2.40, 5.50, 2, '7891027104030', 65],
            [19, 'Borracha Escolar Mercur Record 40 (Caixa c/ 40)', 4, 'Borrachas & Correção', 5, null, 22.00, 39.90, 3, '7891027104047', 30],
            [20, 'Caneta Corretiva 8ml Faber-Castell', 4, 'Borrachas & Correção', 8, '2027-07-20', 4.80, 9.90, 3, '7891027104054', 45],

            // Categoria 5: Colas & Fitas Adesivas (Fornecedores 4: Acrilex, 1: Tilibra)
            [21, 'Cola Bastão 40g Pritt Henkel', 5, 'Colas & Fitas Adesivas', 15, '2026-11-12', 6.50, 13.90, 4, '7891027105013', 60],
            [22, 'Cola Branca Escolar Líquida 90g Acrilex Tenaz', 5, 'Colas & Fitas Adesivas', 20, '2027-08-15', 2.20, 4.90, 4, '7891027105020', 95],
            [23, 'Cola Bastão 20g Acrilex Paper Glue', 5, 'Colas & Fitas Adesivas', 15, '2027-11-30', 2.80, 5.90, 4, '7891027105037', 80],
            [24, 'Fita Adesiva Crepe 18mm x 50m Adelbras', 5, 'Colas & Fitas Adesivas', 15, null, 3.90, 7.90, 1, '7891027105044', 70],
            [25, 'Fita Transparente Larga 45mm x 45m Scotch 3M', 5, 'Colas & Fitas Adesivas', 10, null, 4.80, 9.90, 1, '7891027105051', 65],

            // Categoria 6: Papéis & Folhas (Fornecedor 5: Chamex Papéis)
            [26, 'Resma Papel Sulfite A4 75g Chamex Office 500fls', 6, 'Papéis & Folhas', 30, null, 19.50, 32.90, 5, '7891027106010', 140],
            [27, 'Resma Papel Sulfite Carta 75g Chamex 500fls', 6, 'Papéis & Folhas', 15, null, 18.00, 30.50, 5, '7891027106027', 50],
            [28, 'Pacote Papel Color Set A4 120g Cores Sortidas 50fls', 6, 'Papéis & Folhas', 10, null, 8.50, 16.50, 5, '7891027106034', 45],
            [29, 'Cartolina Branca Escolar 150g 50x66cm (Pacote c/ 20)', 6, 'Papéis & Folhas', 10, null, 12.00, 24.00, 5, '7891027106041', 35],
            [30, 'Papel Fotográfico Glossy A4 180g Masterprint 20fls', 6, 'Papéis & Folhas', 8, null, 9.00, 18.90, 5, '7891027106058', 40],

            // Categoria 7: Pastas & Organização (Fornecedores 1: Tilibra, 4: Acrilex)
            [31, 'Pasta Aba Elástico Cristal Ofício Dello Line', 7, 'Pastas & Organização', 25, null, 2.10, 4.90, 1, '7891027107017', 120],
            [32, 'Pasta Suspensa Kraft Completa Dello (Caixa c/ 25)', 7, 'Pastas & Organização', 5, null, 31.00, 58.00, 1, '7891027107024', 25],
            [33, 'Pasta Catálogo A4 c/ 50 Envelopes Médios ACP', 7, 'Pastas & Organização', 8, null, 12.50, 23.90, 1, '7891027107031', 38],
            [34, 'Organizador de Mesa com Divisórias Waleu Fumê', 7, 'Pastas & Organização', 5, null, 10.50, 21.00, 1, '7891027107048', 30],
            [35, 'Fichário Universitário 4 Argolas Tilibra Preto', 7, 'Pastas & Organização', 5, null, 28.00, 52.90, 1, '7891027107055', 22],

            // Categoria 8: Corte & Medição (Fornecedores 1: Tilibra, 3: Faber)
            [36, 'Tesoura Escolar Sem Ponta 13cm Mundial Ponto Vermelho', 8, 'Corte & Medição', 15, null, 4.20, 8.90, 1, '7891027108014', 85],
            [37, 'Estilete Profissional Largo 18mm com Trava Olfa', 8, 'Corte & Medição', 10, null, 6.80, 13.90, 1, '7891027108021', 50],
            [38, 'Régua Cristal Transparente 30cm Waleu', 8, 'Corte & Medição', 25, null, 1.20, 3.00, 3, '7891027108038', 130],
            [39, 'Kit Geometria Escolar 4 Peças Waleu', 8, 'Corte & Medição', 10, null, 6.50, 13.50, 3, '7891027108045', 45],
            [40, 'Base de Corte A3 Dupla Face 45x30cm Tilibra', 8, 'Corte & Medição', 5, null, 26.00, 49.90, 1, '7891027108052', 20],

            // Categoria 9: Tintas & Pintura (Fornecedor 4: Acrilex)
            [41, 'Tinta Guache 6 Cores 15ml Acrilex Tempera Guache', 9, 'Tintas & Pintura', 20, '2026-11-13', 4.50, 9.50, 4, '7891027109011', 90],
            [42, 'Tinta Guache 12 Cores 15ml Acrilex com Pincel', 9, 'Tintas & Pintura', 15, '2027-12-10', 8.50, 17.90, 4, '7891027109028', 65],
            [43, 'Tinta para Tecido Acrilex 37ml Cores Variadas', 9, 'Tintas & Pintura', 15, '2028-02-28', 3.20, 6.90, 4, '7891027109035', 85],
            [44, 'Kit Pincéis Escolares Chatos Nº 2, 4, 8 e 12 Tigre', 9, 'Tintas & Pintura', 10, null, 7.20, 15.00, 4, '7891027109042', 55],
            [45, 'Aquarela em Pastilha 12 Cores Pentel Arts', 9, 'Tintas & Pintura', 8, '2028-04-30', 16.50, 31.90, 4, '7891027109059', 35],

            // Categoria 10: Grampeadores & Fixação (Fornecedores 1: Tilibra, 3: Faber)
            [46, 'Grampeador de Mesa Médio Metal 26/6 Tilibra G101', 10, 'Grampeadores & Fixação', 8, null, 14.50, 28.90, 1, '7891027110017', 35],
            [47, 'Caixa de Grampos Galvanizados 26/6 c/ 5000 Tilibra', 10, 'Grampeadores & Fixação', 20, null, 4.50, 9.00, 1, '7891027110024', 100],
            [48, 'Caixa de Clips Metálicos Nº 2/0 c/ 100un Bacchi', 10, 'Grampeadores & Fixação', 20, null, 2.80, 5.90, 3, '7891027110031', 110],
            [49, 'Perfurador de Papel 2 Furos Metal 20 Folhas Tilibra', 10, 'Grampeadores & Fixação', 5, null, 16.00, 31.90, 1, '7891027110048', 28],
            [50, 'Extrator de Grampos Espátula Metal Tilibra', 10, 'Grampeadores & Fixação', 10, null, 2.50, 5.50, 1, '7891027110055', 60]
        ];

        $produtosLookup = [];
        $prodsByCat = [];
        foreach ($produtosBase as $pb) {
            $produtosLookup[$pb[0]] = [
                'id' => $pb[0],
                'nome' => $pb[1],
                'categoria_id' => $pb[2],
                'categoria' => $pb[3],
                'estoque_minimo' => $pb[4],
                'validade' => $pb[5],
                'preco_compra' => $pb[6],
                'preco_venda' => $pb[7],
                'fornecedor_id' => $pb[8],
                'codigo_de_barra' => $pb[9],
                'target_stock' => $pb[10]
            ];
            $prodsByCat[$pb[2]][] = $pb[0];
        }

        // ── 5. COMPRAS DE REPOSIÇÃO (12 Compras: Jul, Ago, Set, Out) ───────────
        $logMsg("\n[6/8] Inserindo 12 Compras de Reposição de Estoque...");
        $comprasDef = [
            // Julho/2026
            [1, 1, 'NF-e 13890', 'boleto', 'PAGA', '2026-07-03 10:30:00', [
                [1, 35, 18.50], [2, 40, 4.80], [4, 30, 8.20], [31, 50, 2.10], [46, 20, 14.50]
            ]],
            [2, 3, 'NF-e 29810', 'boleto', 'PAGA', '2026-07-14 14:15:00', [
                [11, 40, 8.50], [12, 35, 17.50], [13, 30, 14.00], [14, 50, 3.10], [16, 60, 1.80]
            ]],
            [3, 5, 'NF-e 88120', 'boleto', 'PAGA', '2026-07-25 11:00:00', [
                [26, 35, 19.50], [27, 45, 18.00], [28, 35, 8.50], [29, 25, 12.00]
            ]],

            // Agosto/2026 (Pico Volta às Aulas)
            [4, 2, 'NF-e 07641', 'pix', 'PAGA', '2026-08-04 09:45:00', [
                [6, 30, 32.00], [7, 30, 32.00], [10, 35, 4.20], [17, 30, 5.90], [18, 35, 2.40]
            ]],
            [5, 4, 'NF-e 44321', 'pix', 'PAGA', '2026-08-16 15:20:00', [
                [21, 30, 6.50], [22, 45, 2.20], [23, 40, 2.80], [41, 40, 4.50], [42, 35, 8.50]
            ]],
            [6, 1, 'NF-e 14102', 'boleto', 'PAGA', '2026-08-26 11:30:00', [
                [1, 30, 18.50], [3, 35, 5.50], [5, 20, 19.00], [35, 20, 28.00], [47, 50, 4.50]
            ]],

            // Setembro/2026
            [7, 5, 'NF-e 91044', 'boleto', 'PAGA', '2026-09-04 10:10:00', [
                [26, 40, 19.50], [27, 40, 18.00], [28, 30, 8.50], [30, 30, 9.00]
            ]],
            [8, 3, 'NF-e 31405', 'boleto', 'PAGA', '2026-09-14 14:40:00', [
                [8, 45, 2.30], [9, 35, 9.80], [11, 35, 8.50], [12, 30, 17.50], [15, 25, 11.20]
            ]],
            [9, 4, 'NF-e 46780', 'pix', 'PAGA', '2026-09-24 16:15:00', [
                [21, 30, 6.50], [22, 40, 2.20], [41, 35, 4.50], [43, 45, 3.20], [45, 20, 16.50]
            ]],

            // Outubro/2026
            [10, 2, 'NF-e 08912', 'pix', 'PAGA', '2026-10-04 09:20:00', [
                [6, 25, 32.00], [7, 25, 32.00], [10, 30, 4.20], [17, 25, 5.90], [20, 30, 4.80]
            ]],
            [11, 1, 'NF-e 14890', 'boleto', 'PAGA', '2026-10-14 15:50:00', [
                [1, 25, 18.50], [2, 30, 4.80], [31, 50, 2.10], [33, 25, 12.50], [48, 50, 2.80]
            ]],
            [12, 5, 'NF-e 99210', 'boleto', 'PAGA', '2026-10-22 11:15:00', [
                [26, 30, 19.50], [28, 25, 8.50], [36, 40, 4.20], [38, 50, 1.20], [49, 20, 16.00]
            ]]
        ];

        $stmtCompra = $pdo->prepare("
            INSERT INTO compras (id, fornecedor_id, usuario_id, numero_nota, valor_total, tipo_pagamento, status, data_compra)
            VALUES (?, ?, 1, ?, ?, ?, ?, ?)
        ");
        $stmtItCompra = $pdo->prepare("
            INSERT INTO itens_compra (compra_id, produto_id, quantidade, preco_unitario, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtMov = $pdo->prepare("
            INSERT INTO movimentacoes (produto_id, tipo, quantidade, data_movimento, observacao)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtLog = $pdo->prepare("
            INSERT INTO logs (usuario_id, acao, descricao, tabela_afetada, ip_usuario, data_log)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $compradosPorProduto = array_fill(1, 50, 0);

        foreach ($comprasDef as $c) {
            $cId = $c[0];
            $fId = $c[1];
            $numNota = $c[2];
            $formaPgto = $c[3];
            $status = $c[4];
            $dataCompra = $c[5];
            $itens = $c[6];

            $totalCompra = 0.0;
            foreach ($itens as $it) {
                $totalCompra += $it[1] * $it[2];
            }

            $stmtCompra->execute([$cId, $fId, $numNota, $totalCompra, $formaPgto, $status, $dataCompra]);

            foreach ($itens as $it) {
                $pId = $it[0];
                $qtd = $it[1];
                $preco = $it[2];
                $subtotal = $qtd * $preco;

                $stmtItCompra->execute([$cId, $pId, $qtd, $preco, $subtotal]);

                $stmtMov->execute([
                    $pId,
                    'entrada_compra',
                    $qtd,
                    $dataCompra,
                    "Entrada Compra {$numNota}"
                ]);

                $compradosPorProduto[$pId] += $qtd;
            }

            $stmtLog->execute([
                1,
                'COMPRA_REGISTRADA',
                "Compra #{$cId} ({$numNota}) registrada no valor de R$ " . number_format($totalCompra, 2, ',', '.') . " via {$formaPgto}",
                'compras',
                '127.0.0.1',
                $dataCompra
            ]);
        }
        $logMsg(" -> 12 Compras registradas com itens, movimentações e logs de auditoria.");

        // ── 6. VENDAS DIÁRIAS (01/07/2026 a 28/10/2026) ─────────────────────────
        $logMsg("\n[7/8] Gerando Histórico de Vendas Diárias (120 dias consecutivos)...");

        $stmtVenda = $pdo->prepare("
            INSERT INTO vendas (id, cliente_id, data_venda, total, forma_pagamento)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmtItemVenda = $pdo->prepare("
            INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario)
            VALUES (?, ?, ?, ?)
        ");
        $stmtCupom = $pdo->prepare("
            INSERT INTO cupons_fiscais (venda_id, chave_acesso, data_emissao)
            VALUES (?, ?, ?)
        ");

        $formasList = ['PIX', 'DINHEIRO', 'CARTÃO DE DÉBITO', 'CARTÃO DE CRÉDITO', 'PIX', 'DINHEIRO'];
        $prodIds = array_keys($produtosLookup);
        $highTicketProdIds = [1, 6, 7, 12, 13, 26, 27, 32, 35, 40, 42, 45, 46];

        $vendaId = 1;
        $vendidosPorProduto = array_fill(1, 50, 0);
        $totalFaturamento = 0.0;
        $totalVendasCount = 0;
        $distinctDays = 0;

        // Metas de Categorias para Outubro/2026 (Centro das faixas solicitadas)
        $metasCatOutubro = [
            1  => 6150.0, // Cadernos & Blocos (faixa 5500 - 6500)
            6  => 4150.0, // Papéis & Folhas (faixa 3800 - 4500)
            2  => 3150.0, // Canetas & Marcadores (faixa 2800 - 3500)
            7  => 2280.0, // Pastas & Organização (faixa 2000 - 2600)
            9  => 1850.0, // Tintas & Pintura (faixa 1500 - 2100)
            3  => 1450.0, // Lápis & Apontadores (faixa 1200 - 1700)
            10 => 1100.0, // Grampeadores & Fixação (faixa 900 - 1300)
            8  => 900.0,  // Corte & Medição (faixa 700 - 1100)
            5  => 650.0,  // Colas & Fitas (faixa 500 - 800)
            4  => 470.0   // Borrachas & Correção (faixa 350 - 600)
        ];

        $faixasMinOutubro = [
            1  => 5500.0,
            6  => 3800.0,
            2  => 2800.0,
            7  => 2000.0,
            9  => 1500.0,
            3  => 1200.0,
            10 => 900.0,
            8  => 700.0,
            5  => 500.0,
            4  => 350.0
        ];

        $faturadoCatOutubro = array_fill_keys(array_keys($metasCatOutubro), 0.0);

        // Função de escolha ponderada pelo déficit em Outubro
        $escolherCatPonderada = function() use (&$metasCatOutubro, &$faturadoCatOutubro): int {
            $pesos = [];
            $totalPeso = 0.0;
            foreach ($metasCatOutubro as $catId => $meta) {
                $deficit = max(1.0, $meta - $faturadoCatOutubro[$catId]);
                $pesos[$catId] = $deficit;
                $totalPeso += $deficit;
            }
            $rand = (mt_rand(1, 10000) / 10000) * $totalPeso;
            $acc = 0.0;
            foreach ($pesos as $catId => $w) {
                $acc += $w;
                if ($rand <= $acc) {
                    return $catId;
                }
            }
            return 1;
        };

        // --- LOOP 1: MESES 7, 8 E 9 (01/07/2026 a 30/09/2026) ---
        $startDateJul = new DateTime('2026-07-01');
        $endDateSet = new DateTime('2026-09-30');
        $intervalP1D = new DateInterval('P1D');
        $periodJulSet = new DatePeriod($startDateJul, $intervalP1D, (clone $endDateSet)->modify('+1 day'));

        foreach ($periodJulSet as $dt) {
            $distinctDays++;
            $dateStr = $dt->format('Y-m-d');
            $dayOfWeek = (int)$dt->format('N');
            $dayOfMonth = (int)$dt->format('j');
            $month = (int)$dt->format('n');

            $isVoltaAulas = ($month === 8 && $dayOfMonth >= 1 && $dayOfMonth <= 10);
            $isPayday = ($dayOfMonth >= 5 && $dayOfMonth <= 9);
            $isSaturday = ($dayOfWeek === 6);
            $isSunday = ($dayOfWeek === 7);
            $isMonday = ($dayOfWeek === 1);

            if ($isVoltaAulas) {
                $numSales = ($dayOfWeek === 7) ? 4 : mt_rand(8, 11);
                $dayType = 'high_volta_aulas';
                $targetMin = 1250.0;
                $targetMax = 2800.0;
            } elseif ($isPayday) {
                $numSales = ($dayOfWeek === 7) ? 3 : mt_rand(7, 9);
                $dayType = 'high_payday';
                $targetMin = 1200.0;
                $targetMax = 2200.0;
            } elseif ($isSaturday) {
                $numSales = mt_rand(6, 8);
                $dayType = 'high_saturday';
                $targetMin = 1200.0;
                $targetMax = 2000.0;
            } elseif ($isSunday) {
                $numSales = mt_rand(1, 2);
                $dayType = 'low_sunday';
                $targetMin = 85.0;
                $targetMax = 220.0;
            } elseif ($isMonday) {
                $numSales = mt_rand(2, 3);
                $dayType = 'low_monday';
                $targetMin = 110.0;
                $targetMax = 245.0;
            } else {
                $numSales = mt_rand(3, 5);
                $dayType = 'medium_weekday';
                $targetMin = 380.0;
                $targetMax = 780.0;
            }

            $startHour = ($dayOfWeek === 7) ? 9 : 8;
            $endHour = ($dayOfWeek === 7) ? 13 : 18;
            $timestamps = [];
            for ($s = 0; $s < $numSales; $s++) {
                $h = mt_rand($startHour, $endHour);
                $m = mt_rand(5, 55);
                $sec = mt_rand(10, 58);
                $timestamps[] = $dateStr . ' ' . sprintf('%02d:%02d:%02d', $h, $m, $sec);
            }
            sort($timestamps);

            $targetDayRev = $targetMin + ($targetMax - $targetMin) * (mt_rand(15, 85) / 100);
            $targetPerSale = $targetDayRev / count($timestamps);

            $dayRevenue = 0.0;
            $salesOfDay = [];

            foreach ($timestamps as $saleTime) {
                $clienteId = (mt_rand(1, 100) <= 40) ? null : mt_rand(1, 12);
                $formaPgto = $formasList[array_rand($formasList)];
                $numItems = mt_rand(1, 4);

                $chosenProdIds = [];
                for ($it = 0; $it < $numItems; $it++) {
                    if (strpos($dayType, 'high') !== false && mt_rand(1, 10) <= 6) {
                        $chosenProdIds[] = $highTicketProdIds[array_rand($highTicketProdIds)];
                    } else {
                        $chosenProdIds[] = $prodIds[array_rand($prodIds)];
                    }
                }
                $chosenProdIds = array_unique($chosenProdIds);
                if (empty($chosenProdIds)) {
                    $chosenProdIds = [1];
                }

                $itensVenda = [];
                $totalVenda = 0.0;

                foreach ($chosenProdIds as $pid) {
                    $p = $produtosLookup[$pid];
                    $qtd = 1;
                    $subtotal = $p['preco_venda'] * $qtd;
                    $totalVenda += $subtotal;
                    $itensVenda[$pid] = [
                        'produto_id' => $pid,
                        'quantidade' => $qtd,
                        'preco_unitario' => $p['preco_venda']
                    ];
                }

                $attempts = 0;
                while ($totalVenda < $targetPerSale * 0.85 && $attempts < 5) {
                    $pidAdd = (strpos($dayType, 'high') !== false && mt_rand(1, 10) <= 6)
                        ? $highTicketProdIds[array_rand($highTicketProdIds)]
                        : $prodIds[array_rand($prodIds)];
                    $pAdd = $produtosLookup[$pidAdd];

                    if (isset($itensVenda[$pidAdd])) {
                        $itensVenda[$pidAdd]['quantidade']++;
                        $totalVenda += $pAdd['preco_venda'];
                    } else {
                        $itensVenda[$pidAdd] = [
                            'produto_id' => $pidAdd,
                            'quantidade' => 1,
                            'preco_unitario' => $pAdd['preco_venda']
                        ];
                        $totalVenda += $pAdd['preco_venda'];
                    }
                    $attempts++;
                }

                $salesOfDay[] = [
                    'cliente_id' => $clienteId,
                    'data_venda' => $saleTime,
                    'total' => $totalVenda,
                    'forma_pagamento' => $formaPgto,
                    'itens' => $itensVenda
                ];
                $dayRevenue += $totalVenda;
            }

            if ($dayRevenue < $targetMin && !empty($salesOfDay)) {
                $diff = $targetMin - $dayRevenue;
                $lastSaleKey = count($salesOfDay) - 1;
                $pidExtra = ($diff >= 30) ? 26 : 1;
                $pExtra = $produtosLookup[$pidExtra];
                $qtdExtra = (int)ceil($diff / $pExtra['preco_venda']);
                if (isset($salesOfDay[$lastSaleKey]['itens'][$pidExtra])) {
                    $salesOfDay[$lastSaleKey]['itens'][$pidExtra]['quantidade'] += $qtdExtra;
                } else {
                    $salesOfDay[$lastSaleKey]['itens'][$pidExtra] = [
                        'produto_id' => $pidExtra,
                        'quantidade' => $qtdExtra,
                        'preco_unitario' => $pExtra['preco_venda']
                    ];
                }
                $addedVal = $qtdExtra * $pExtra['preco_venda'];
                $salesOfDay[$lastSaleKey]['total'] += $addedVal;
                $dayRevenue += $addedVal;
            }

            foreach ($salesOfDay as $sData) {
                $stmtVenda->execute([$vendaId, $sData['cliente_id'], $sData['data_venda'], $sData['total'], $sData['forma_pagamento']]);

                foreach ($sData['itens'] as $it) {
                    $stmtItemVenda->execute([$vendaId, $it['produto_id'], $it['quantidade'], $it['preco_unitario']]);

                    $stmtMov->execute([
                        $it['produto_id'],
                        'saida_venda',
                        $it['quantidade'],
                        $sData['data_venda'],
                        "Venda PDV #{$vendaId}"
                    ]);

                    $vendidosPorProduto[$it['produto_id']] += $it['quantidade'];
                }

                $chaveNfce = sprintf('3526%02d0414824300011665001%09d%08d%01d', 
                    $month, 
                    $vendaId, 
                    mt_rand(10000000, 99999999), 
                    mt_rand(1, 9)
                );
                $chaveNfce = str_pad(substr($chaveNfce, 0, 44), 44, '0');
                $stmtCupom->execute([$vendaId, $chaveNfce, $sData['data_venda']]);

                $userIdOperador = (mt_rand(1, 10) <= 8) ? 2 : 1;
                $descLog = sprintf('Venda PDV #%d finalizada no valor de R$ %.2f via %s', $vendaId, $sData['total'], $sData['forma_pagamento']);
                $stmtLog->execute([
                    $userIdOperador,
                    'VENDA_PDV',
                    $descLog,
                    'vendas',
                    '127.0.0.1',
                    $sData['data_venda']
                ]);

                $totalFaturamento += $sData['total'];
                $totalVendasCount++;
                $vendaId++;
            }

            // Logs operacionais diários
            $timeLoginAdmin = $dateStr . ' 07:55:' . sprintf('%02d', mt_rand(10, 50));
            $timeLoginCaixa = $dateStr . ' 08:05:' . sprintf('%02d', mt_rand(10, 50));
            $timeLogoutCaixa = $dateStr . ' 18:05:' . sprintf('%02d', mt_rand(10, 50));
            $timeLogoutAdmin = $dateStr . ' 18:20:' . sprintf('%02d', mt_rand(10, 50));

            $stmtLog->execute([1, 'LOGIN_SUCESSO', "Usuário 'admin' autenticado com sucesso no sistema", 'usuarios', '127.0.0.1', $timeLoginAdmin]);
            $stmtLog->execute([2, 'LOGIN_SUCESSO', "Operador 'caixa' autenticado no terminal PDV", 'usuarios', '127.0.0.1', $timeLoginCaixa]);

            if ($dayOfMonth === 13 || $dayOfMonth === 27) {
                $stmtLog->execute([1, 'FALHA_LOGIN', "Tentativa de login com senha incorreta para o usuário 'admin'", 'usuarios', '192.168.1.105', $dateStr . ' 07:54:12']);
            }
            if ($dayOfWeek === 6) {
                $stmtLog->execute([1, 'AJUSTE_ESTOQUE', "Conferência física e inventário rotativo semanal concluídos", 'produtos', '127.0.0.1', $dateStr . ' 18:30:00']);
            }
            if ($dayOfMonth === 30 || ($month === 7 && $dayOfMonth === 31) || ($month === 8 && $dayOfMonth === 31)) {
                $stmtLog->execute([1, 'RELATORIO_GERADO', "Relatório Financeiro Consolidado e Curva ABC do mês exportados em PDF", 'relatorios', '127.0.0.1', $dateStr . ' 18:45:00']);
            }

            $stmtLog->execute([2, 'LOGOUT', "Sessão encerrada com sucesso pelo operador 'caixa'", 'usuarios', '127.0.0.1', $timeLogoutCaixa]);
            $stmtLog->execute([1, 'LOGOUT', "Sessão encerrada com sucesso pelo usuário 'admin'", 'usuarios', '127.0.0.1', $timeLogoutAdmin]);
        }

        // --- LOOP 2: MÊS 10 (OUTUBRO/2026 - 01/10/2026 a 27/10/2026) ---
        // Implementa o algoritmo de alta fidelidade para 4 semanas balanceadas e 10 categorias decrescentes
        $diasOutubroPlan = [];
        for ($d = 1; $d <= 27; $d++) {
            $dateStr = sprintf('2026-10-%02d', $d);
            $dw = (int)date('N', strtotime($dateStr));
            
            if ($d === 27) { // Terça-feira 27/10 (Véspera da Banca): Fechamento histórico com 10 vendas para totalizar exatamente 579 vendas
                $numSales = 10;
                $minT = 650.0;
                $maxT = 1100.0;
                $targetRev = 820.0;
            } elseif ($dw === 1) { // Segunda mais calma
                $numSales = mt_rand(2, 3);
                $minT = 180.0;
                $maxT = 300.0;
                $targetRev = mt_rand(220, 270);
            } elseif ($dw === 7) { // Domingo leve
                $numSales = mt_rand(1, 2);
                $minT = 90.0;
                $maxT = 200.0;
                $targetRev = mt_rand(120, 160);
            } elseif ($dw === 5 || $dw === 6) { // Sexta e Sábado fortes
                $numSales = mt_rand(7, 10);
                $minT = 1100.0;
                $maxT = 1900.0;
                $targetRev = mt_rand(1450, 1750);
            } else { // Terça a Quinta médias
                $numSales = mt_rand(4, 6);
                $minT = 450.0;
                $maxT = 800.0;
                $targetRev = mt_rand(640, 760);
            }

            $diasOutubroPlan[$dateStr] = [
                'day' => $d,
                'dw' => $dw,
                'dw_name' => date('D', strtotime($dateStr)),
                'num_sales' => $numSales,
                'min_target' => $minT,
                'max_target' => $maxT,
                'target_rev' => $targetRev
            ];
        }

        // Simula e calibra as vendas dos 27 dias de Outubro
        $vendasPorDiaOutubro = [];
        foreach ($diasOutubroPlan as $dateStr => $info) {
            $minT = $info['min_target'];
            $maxT = $info['max_target'];
            $targetRev = $info['target_rev'];
            $numSales = $info['num_sales'];
            $targetPerSale = $targetRev / $numSales;

            $salesToday = [];
            $revToday = 0.0;

            for ($s = 0; $s < $numSales; $s++) {
                $saleItems = [];
                $saleTotal = 0.0;
                $itemsCount = mt_rand(1, 4);

                // Reforço inteligente de itens de papelaria leves
                if ($faturadoCatOutubro[4] < 400.0 && mt_rand(1, 10) <= 4) {
                    $catChosen = 4;
                } elseif ($faturadoCatOutubro[5] < 550.0 && mt_rand(1, 10) <= 4) {
                    $catChosen = 5;
                } else {
                    $catChosen = $escolherCatPonderada();
                }

                $pid = $prodsByCat[$catChosen][array_rand($prodsByCat[$catChosen])];
                $prod = $produtosLookup[$pid];
                $qtd = ($prod['preco_venda'] <= 8.0 && mt_rand(1, 10) <= 4) ? mt_rand(2, 3) : 1;

                $saleItems[$pid] = ['pid' => $pid, 'cat' => $catChosen, 'qtd' => $qtd, 'preco' => $prod['preco_venda']];
                $sub = $qtd * $prod['preco_venda'];
                $saleTotal += $sub;
                $faturadoCatOutubro[$catChosen] += $sub;

                for ($it = 1; $it < $itemsCount; $it++) {
                    $catChosen = $escolherCatPonderada();
                    $pid = $prodsByCat[$catChosen][array_rand($prodsByCat[$catChosen])];
                    $prod = $produtosLookup[$pid];
                    $qtd = ($prod['preco_venda'] <= 8.0 && mt_rand(1, 10) <= 3) ? 2 : 1;

                    if (isset($saleItems[$pid])) {
                        $saleItems[$pid]['qtd'] += $qtd;
                    } else {
                        $saleItems[$pid] = ['pid' => $pid, 'cat' => $catChosen, 'qtd' => $qtd, 'preco' => $prod['preco_venda']];
                    }
                    $sub = $qtd * $prod['preco_venda'];
                    $saleTotal += $sub;
                    $faturadoCatOutubro[$catChosen] += $sub;
                }

                $loops = 0;
                while ($saleTotal < $targetPerSale * 0.90 && $loops < 5) {
                    $catChosen = $escolherCatPonderada();
                    $pid = $prodsByCat[$catChosen][array_rand($prodsByCat[$catChosen])];
                    $prod = $produtosLookup[$pid];
                    $qtd = 1;
                    if (isset($saleItems[$pid])) {
                        $saleItems[$pid]['qtd'] += $qtd;
                    } else {
                        $saleItems[$pid] = ['pid' => $pid, 'cat' => $catChosen, 'qtd' => $qtd, 'preco' => $prod['preco_venda']];
                    }
                    $sub = $qtd * $prod['preco_venda'];
                    $saleTotal += $sub;
                    $faturadoCatOutubro[$catChosen] += $sub;
                    $loops++;
                }

                $salesToday[] = ['total' => $saleTotal, 'items' => $saleItems];
                $revToday += $saleTotal;
            }

            // Clamping inferior diário
            while ($revToday < $minT) {
                $bestCat = 1;
                $maxDef = -999999;
                foreach ($metasCatOutubro as $cId => $mVal) {
                    $cd = $mVal - $faturadoCatOutubro[$cId];
                    if ($cd > $maxDef) {
                        $maxDef = $cd;
                        $bestCat = $cId;
                    }
                }
                $pid = $prodsByCat[$bestCat][0];
                $prod = $produtosLookup[$pid];
                $diff = $minT - $revToday;
                $qAdd = (int)ceil($diff / $prod['preco_venda']);
                if ($qAdd < 1) $qAdd = 1;

                $sub = $qAdd * $prod['preco_venda'];
                $lastIdx = count($salesToday) - 1;
                if (isset($salesToday[$lastIdx]['items'][$pid])) {
                    $salesToday[$lastIdx]['items'][$pid]['qtd'] += $qAdd;
                } else {
                    $salesToday[$lastIdx]['items'][$pid] = ['pid' => $pid, 'cat' => $bestCat, 'qtd' => $qAdd, 'preco' => $prod['preco_venda']];
                }
                $salesToday[$lastIdx]['total'] += $sub;
                $revToday += $sub;
                $faturadoCatOutubro[$bestCat] += $sub;
            }

            // Clamping superior diário
            while ($revToday > $maxT) {
                $lastIdx = count($salesToday) - 1;
                $reduced = false;
                foreach ($salesToday[$lastIdx]['items'] as $pid => &$itRef) {
                    if ($itRef['qtd'] > 1) {
                        $itRef['qtd']--;
                        $sub = $itRef['preco'];
                        $salesToday[$lastIdx]['total'] -= $sub;
                        $revToday -= $sub;
                        $faturadoCatOutubro[$itRef['cat']] -= $sub;
                        $reduced = true;
                        break;
                    }
                }
                unset($itRef);
                if (!$reduced) {
                    if (count($salesToday[$lastIdx]['items']) > 1) {
                        $remItem = array_pop($salesToday[$lastIdx]['items']);
                        $sub = $remItem['qtd'] * $remItem['preco'];
                        $salesToday[$lastIdx]['total'] -= $sub;
                        $revToday -= $sub;
                        $faturadoCatOutubro[$remItem['cat']] -= $sub;
                    } else {
                        break;
                    }
                }
            }

            $vendasPorDiaOutubro[$dateStr] = [
                'info' => $info,
                'rev' => $revToday,
                'sales' => $salesToday
            ];
        }

        // Calibração de precisão no dia 27/10 para garantir 100% das faixas e ordem estrita de Outubro
        $dateCalib = '2026-10-27';
        foreach ($faixasMinOutubro as $catId => $fMin) {
            if ($faturadoCatOutubro[$catId] < $fMin) {
                $deficit = $fMin + 20.0 - $faturadoCatOutubro[$catId];
                $pid = $prodsByCat[$catId][0];
                $prod = $produtosLookup[$pid];
                $qAdd = (int)ceil($deficit / $prod['preco_venda']);
                if ($qAdd < 1) $qAdd = 1;
                $sub = $qAdd * $prod['preco_venda'];

                $lastSaleIdx = count($vendasPorDiaOutubro[$dateCalib]['sales']) - 1;

                // Mantém o teto do dia 27/10 em <= 1200.0
                while ($vendasPorDiaOutubro[$dateCalib]['rev'] + $sub > 1200.0) {
                    $freed = false;
                    foreach ($vendasPorDiaOutubro[$dateCalib]['sales'] as $sIdx => &$sRef) {
                        foreach ($sRef['items'] as $pRem => &$itRem) {
                            $cRem = $itRem['cat'];
                            if ($cRem !== $catId && $faturadoCatOutubro[$cRem] - $itRem['preco'] >= $faixasMinOutubro[$cRem]) {
                                if ($itRem['qtd'] > 1) {
                                    $itRem['qtd']--;
                                } else {
                                    unset($sRef['items'][$pRem]);
                                }
                                $sRef['total'] -= $itRem['preco'];
                                $vendasPorDiaOutubro[$dateCalib]['rev'] -= $itRem['preco'];
                                $faturadoCatOutubro[$cRem] -= $itRem['preco'];
                                $freed = true;
                                break 2;
                            }
                        }
                        unset($itRem);
                    }
                    unset($sRef);
                    if (!$freed) break;
                }

                if (isset($vendasPorDiaOutubro[$dateCalib]['sales'][$lastSaleIdx]['items'][$pid])) {
                    $vendasPorDiaOutubro[$dateCalib]['sales'][$lastSaleIdx]['items'][$pid]['qtd'] += $qAdd;
                } else {
                    $vendasPorDiaOutubro[$dateCalib]['sales'][$lastSaleIdx]['items'][$pid] = ['pid' => $pid, 'cat' => $catId, 'qtd' => $qAdd, 'preco' => $prod['preco_venda']];
                }
                $vendasPorDiaOutubro[$dateCalib]['sales'][$lastSaleIdx]['total'] += $sub;
                $vendasPorDiaOutubro[$dateCalib]['rev'] += $sub;
                $faturadoCatOutubro[$catId] += $sub;
            }
        }

        // Persistência das vendas de Outubro no banco de dados com timestamps realistas
        foreach ($vendasPorDiaOutubro as $dateStr => $vDia) {
            $distinctDays++;
            $dw = $vDia['info']['dw'];
            $sales = $vDia['sales'];
            $numS = count($sales);

            $timestamps = [];
            if ($dateStr === '2026-10-27') {
                $fixedTimes = [
                    '08:45:10', '09:30:22', '10:15:45', '11:20:18', '12:35:50',
                    '13:50:33', '14:40:12', '15:25:05', '16:30:40', '17:45:20'
                ];
                for ($i = 0; $i < $numS; $i++) {
                    $timestamps[] = $dateStr . ' ' . ($fixedTimes[$i] ?? sprintf('%02d:%02d:%02d', 8 + (int)($i * 0.7), mt_rand(10, 50), mt_rand(10, 50)));
                }
            } else {
                $startHour = ($dw === 7) ? 9 : 8;
                $endHour = ($dw === 7) ? 13 : 18;
                for ($s = 0; $s < $numS; $s++) {
                    $h = mt_rand($startHour, $endHour);
                    $m = mt_rand(5, 55);
                    $sec = mt_rand(10, 58);
                    $timestamps[] = $dateStr . ' ' . sprintf('%02d:%02d:%02d', $h, $m, $sec);
                }
                sort($timestamps);
            }

            foreach ($sales as $sIdx => $sData) {
                $saleTime = $timestamps[$sIdx];
                $clienteId = (mt_rand(1, 100) <= 40) ? null : mt_rand(1, 12);
                $formaPgto = $formasList[array_rand($formasList)];

                $stmtVenda->execute([$vendaId, $clienteId, $saleTime, $sData['total'], $formaPgto]);

                foreach ($sData['items'] as $it) {
                    $stmtItemVenda->execute([$vendaId, $it['pid'], $it['qtd'], $it['preco']]);

                    $stmtMov->execute([
                        $it['pid'],
                        'saida_venda',
                        $it['qtd'],
                        $saleTime,
                        "Venda PDV #{$vendaId}"
                    ]);

                    $vendidosPorProduto[$it['pid']] += $it['qtd'];
                }

                $chaveNfce = sprintf('3526100414824300011665001%09d%08d%01d', 
                    $vendaId, 
                    mt_rand(10000000, 99999999), 
                    mt_rand(1, 9)
                );
                $chaveNfce = str_pad(substr($chaveNfce, 0, 44), 44, '0');
                $stmtCupom->execute([$vendaId, $chaveNfce, $saleTime]);

                $userIdOperador = (mt_rand(1, 10) <= 8) ? 2 : 1;
                $descLog = sprintf('Venda PDV #%d finalizada no valor de R$ %.2f via %s', $vendaId, $sData['total'], $formaPgto);
                $stmtLog->execute([
                    $userIdOperador,
                    'VENDA_PDV',
                    $descLog,
                    'vendas',
                    '127.0.0.1',
                    $saleTime
                ]);

                $totalFaturamento += $sData['total'];
                $totalVendasCount++;
                $vendaId++;
            }

            // Logs operacionais diários
            $timeLoginAdmin = $dateStr . ' 07:55:' . sprintf('%02d', mt_rand(10, 50));
            $timeLoginCaixa = $dateStr . ' 08:05:' . sprintf('%02d', mt_rand(10, 50));
            $timeLogoutCaixa = $dateStr . ' 18:05:' . sprintf('%02d', mt_rand(10, 50));
            $timeLogoutAdmin = $dateStr . ' 18:20:' . sprintf('%02d', mt_rand(10, 50));

            $stmtLog->execute([1, 'LOGIN_SUCESSO', "Usuário 'admin' autenticado com sucesso no sistema", 'usuarios', '127.0.0.1', $timeLoginAdmin]);
            $stmtLog->execute([2, 'LOGIN_SUCESSO', "Operador 'caixa' autenticado no terminal PDV", 'usuarios', '127.0.0.1', $timeLoginCaixa]);

            if ($dateStr === '2026-10-13' || $dateStr === '2026-10-27') {
                $stmtLog->execute([1, 'FALHA_LOGIN', "Tentativa de login com senha incorreta para o usuário 'admin'", 'usuarios', '192.168.1.105', $dateStr . ' 07:54:12']);
            }
            if ($dw === 6) {
                $stmtLog->execute([1, 'AJUSTE_ESTOQUE', "Conferência física e inventário rotativo semanal concluídos", 'produtos', '127.0.0.1', $dateStr . ' 18:30:00']);
            }

            $stmtLog->execute([2, 'LOGOUT', "Sessão encerrada com sucesso pelo operador 'caixa'", 'usuarios', '127.0.0.1', $timeLogoutCaixa]);
            $stmtLog->execute([1, 'LOGOUT', "Sessão encerrada com sucesso pelo usuário 'admin'", 'usuarios', '127.0.0.1', $timeLogoutAdmin]);
        }

        // ── 6.1 VENDA REAL DO DIA DA BANCA / DEMONSTRAÇÃO (28/10/2026) ────────────
        // Venda #580 realizada no PDV da Papelaria Real às 06:33:55
        // Produto: Aquarela em Pastilha 12 Cores Pentel Arts (R$ 31,90 em Dinheiro)
        $distinctDays++; // 28/10/2026 (120º dia consecutivo)
        $dataVendaReal = '2026-10-28 06:33:55';
        $vendaRealId = 580;

        $stmtVenda->execute([$vendaRealId, null, $dataVendaReal, 31.90, 'DINHEIRO']);
        $stmtItemVenda->execute([$vendaRealId, 45, 1, 31.90]);

        $stmtMov->execute([
            45,
            'saida_venda',
            1,
            $dataVendaReal,
            "Venda PDV #{$vendaRealId}"
        ]);
        $vendidosPorProduto[45] += 1;

        $chaveNfceReal = '35261004148243000116650010000005809812475112';
        $stmtCupom->execute([$vendaRealId, $chaveNfceReal, $dataVendaReal]);

        $stmtLog->execute([
            2, // Caixa
            'VENDA_PDV',
            "Venda PDV #{$vendaRealId} finalizada no valor de R$ 31.90 via DINHEIRO",
            'vendas',
            '127.0.0.1',
            $dataVendaReal
        ]);

        $totalFaturamento += 31.90;
        $totalVendasCount++;
        $faturadoCatOutubro[9] += 31.90;

        // Logs de autenticação do dia da banca (28/10/2026 - Manhã em andamento)
        $stmtLog->execute([1, 'LOGIN_SUCESSO', "Usuário 'admin' autenticado com sucesso no sistema", 'usuarios', '127.0.0.1', '2026-10-28 06:00:15']);
        $stmtLog->execute([2, 'LOGIN_SUCESSO', "Operador 'caixa' autenticado no terminal PDV", 'usuarios', '127.0.0.1', '2026-10-28 06:15:30']);

        $logMsg(" -> Total de Vendas geradas: {$totalVendasCount} vendas ao longo de {$distinctDays} dias consecutivos.");
        $logMsg(" -> Faturamento Acumulado: R$ " . number_format($totalFaturamento, 2, ',', '.'));

        // ── 7. AJUSTE DE ESTOQUE INICIAL & FINAL CONSISTENTE ───────────────────
        $logMsg("\n[8/8] Calibrando Estoque Inicial, Lotes e Estoque Final Saudável (25 a 140 un)...");

        $stmtProd = $pdo->prepare("
            INSERT INTO produtos (id, nome, categoria_id, categoria, quantidade, estoque_minimo, validade, preco_compra, preco_venda, fornecedor_id, codigo_de_barra, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo')
        ");

        $stmtLote = $pdo->prepare("
            INSERT INTO lotes (produto_id, numero_lote, data_fabricacao, data_validade, quantidade, preco_compra, fornecedor_id, data_entrada)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $produtosFinais = [];
        foreach ($produtosLookup as $pid => $p) {
            $vendido = $vendidosPorProduto[$pid];
            $comprado = $compradosPorProduto[$pid];
            $alvoFinal = max(25, min(140, (int)$p['target_stock']));

            $initialStock = $alvoFinal + $vendido - $comprado;
            if ($initialStock < 30) {
                $initialStock = 30;
                $alvoFinal = $initialStock + $comprado - $vendido;
                if ($alvoFinal > 140) {
                    $alvoFinal = 140;
                    $initialStock = $alvoFinal + $vendido - $comprado;
                }
            }
            $alvoFinal = max(25, min(140, $alvoFinal));
            $initialStock = $alvoFinal + $vendido - $comprado;

            // Insere carga inicial na tabela de movimentações (01/07/2026 07:00:00)
            $stmtMov->execute([
                $pid,
                'entrada_compra',
                $initialStock,
                '2026-07-01 07:00:00',
                'Carga Inicial de Estoque Papelaria Real'
            ]);

            // Insere produto com quantidade final exata
            $stmtProd->execute([
                $p['id'],
                $p['nome'],
                $p['categoria_id'],
                $p['categoria'],
                $alvoFinal,
                $p['estoque_minimo'],
                $p['validade'],
                $p['preco_compra'],
                $p['preco_venda'],
                $p['fornecedor_id'],
                $p['codigo_de_barra']
            ]);

            $produtosFinais[$pid] = $alvoFinal;
        }
        $logMsg(" -> 50 Produtos cadastrados com estoque inicial e final perfeitamente balanceados.");

        // ── 8. CADASTRO DE LOTES (20 Lotes, com os 5 Críticos em Alerta < 30 dias) ──
        // 5 Produtos com alerta de validade em Nov/2026 (< 30 dias de 28/10/2026):
        // Prod 8 (Marca-Texto Faber), Prod 10 (Marcador BIC), Prod 17 (Fita BIC), Prod 21 (Cola Pritt), Prod 41 (Tinta Guache)
        $lotesCriticosDef = [
            8  => ['LT-2026-FC08A', '2025-11-14', '2026-11-14', 25, 'LT-2027-FC08B', '2026-08-30', '2027-08-30'],
            10 => ['LT-2026-BC10A', '2025-11-18', '2026-11-18', 20, 'LT-2027-BC10B', '2026-09-15', '2027-11-15'],
            17 => ['LT-2026-TX17A', '2025-11-16', '2026-11-16', 15, 'LT-2027-TX17B', '2026-09-20', '2027-09-20'],
            21 => ['LT-2026-PR21A', '2025-11-12', '2026-11-12', 20, 'LT-2027-PR21B', '2026-08-31', '2027-10-31'],
            41 => ['LT-2026-AX41A', '2025-11-13', '2026-11-13', 30, 'LT-2027-AX41B', '2026-06-30', '2027-06-30']
        ];

        foreach ($lotesCriticosDef as $pid => $lc) {
            $finalQ = $produtosFinais[$pid];
            $qLote1 = $lc[3];
            $qLote2 = max(5, $finalQ - $qLote1);
            $p = $produtosLookup[$pid];

            // Lote 1 (Vencimento Próximo em Nov/2026)
            $stmtLote->execute([
                $pid,
                $lc[0],
                $lc[1],
                $lc[2],
                $qLote1,
                $p['preco_compra'],
                $p['fornecedor_id'],
                '2026-07-01 08:00:00'
            ]);

            // Lote 2 (Validade Longa em 2027)
            $stmtLote->execute([
                $pid,
                $lc[4],
                $lc[5],
                $lc[6],
                $qLote2,
                $p['preco_compra'],
                $p['fornecedor_id'],
                '2026-09-01 08:00:00'
            ]);

            $stmtLog->execute([1, 'LOTE_CRIADO', "Lote #{$lc[0]} cadastrado ({$qLote1} un) para o produto #{$pid}", 'lotes', '127.0.0.1', '2026-07-01 08:30:00']);
            $stmtLog->execute([1, 'LOTE_CRIADO', "Lote #{$lc[4]} cadastrado ({$qLote2} un) para o produto #{$pid}", 'lotes', '127.0.0.1', '2026-09-01 08:30:00']);
        }

        // 10 Outros produtos com validade segura (2027/2028):
        $outrosLotes = [
            [6,  'LT-2027-BC06',  '2026-01-10', '2027-12-31', '2026-08-04 09:45:00'],
            [7,  'LT-2027-BC07',  '2026-01-10', '2027-12-31', '2026-08-04 09:45:00'],
            [9,  'LT-2027-FC09',  '2026-04-15', '2027-10-15', '2026-09-14 14:40:00'],
            [18, 'LT-2027-BC18',  '2026-03-30', '2027-09-30', '2026-08-04 09:45:00'],
            [20, 'LT-2027-FC20',  '2026-01-20', '2027-07-20', '2026-10-04 09:20:00'],
            [22, 'LT-2027-AX22',  '2026-02-15', '2027-08-15', '2026-08-16 15:20:00'],
            [23, 'LT-2027-AX23',  '2026-05-30', '2027-11-30', '2026-08-16 15:20:00'],
            [42, 'LT-2027-AX42',  '2026-06-10', '2027-12-10', '2026-08-16 15:20:00'],
            [43, 'LT-2028-AX43',  '2026-08-28', '2028-02-28', '2026-09-24 16:15:00'],
            [45, 'LT-2028-PT45',  '2026-04-30', '2028-04-30', '2026-09-24 16:15:00']
        ];

        foreach ($outrosLotes as $ol) {
            $pid = $ol[0];
            $finalQ = $produtosFinais[$pid];
            $p = $produtosLookup[$pid];

            $stmtLote->execute([
                $pid,
                $ol[1],
                $ol[2],
                $ol[3],
                $finalQ,
                $p['preco_compra'],
                $p['fornecedor_id'],
                $ol[4]
            ]);

            $stmtLog->execute([1, 'LOTE_CRIADO', "Lote #{$ol[1]} cadastrado ({$finalQ} un) para o produto #{$pid}", 'lotes', '127.0.0.1', $ol[4]]);
        }
        $logMsg(" -> 20 Lotes cadastrados com integridade exata (produtos.quantidade == SUM(lotes.quantidade)).");

        $pdo->exec("ALTER TABLE vendas AUTO_INCREMENT = 581;");
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        $logMsg("\n========================================================");
        $logMsg(" POVOAMENTO COMPLETO CONCLUÍDO COM SUCESSO!");
        $logMsg("========================================================");

        return [
            'sucesso' => true,
            'total_vendas' => $totalVendasCount,
            'faturamento' => $totalFaturamento,
            'dias' => $distinctDays,
            'faturado_outubro' => $faturadoCatOutubro,
            'logs' => $out
        ];

    } catch (Throwable $e) {
        if (isset($pdo)) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        }
        $logMsg("\n[ERRO CRÍTICO NO SEED]: " . $e->getMessage());
        $logMsg($e->getTraceAsString());
        return [
            'sucesso' => false,
            'erro' => $e->getMessage(),
            'logs' => $out
        ];
    }
}

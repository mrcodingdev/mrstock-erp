<?php
/**
 * MrStock ERP — Central de Ajuda, Base de Conhecimento & FAQ Operacional
 * Versão 2.1.0 (SalesOps Enterprise Edition + Benchmark Corporativo de Suporte)
 */
$pageTitle  = 'Central de Ajuda';
$activePage = 'ajuda';

require_once __DIR__ . '/inc/database.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/functions.php';

$userId   = (int)($_SESSION['user_id'] ?? 1);
$userRole = $_SESSION['user_perfil'] ?? $_SESSION['usuario_nivel'] ?? 'caixa';
$userName = $_SESSION['user_name'] ?? $_SESSION['username'] ?? 'Usuário';
$isAdmin  = is_admin();

// ══ CONTRATO DE DADOS ESTRUTURADOS DO BACKEND ═════════════════════════════════

// 1. Cards Bento de Acesso Rápido
$bentoCards = [
    [
        'categoria' => 'atalhos',
        'titulo'    => 'Atalhos do PDV',
        'subtitulo' => 'Guia F1 a F10 e Tecla ESC',
        'icone'     => 'fa-keyboard',
        'bg_icon'   => '#284936',
        'borda_top' => 'so-bento-card--primary',
        'badge'     => 'Operação Ágil',
        'badge_bg'  => 'bg-success',
        'preview'   => '<code>F2</code> Leitor | <code>F4</code> Pagar | <code>F9</code> Cancelar'
    ],
    [
        'categoria' => 'estoque',
        'titulo'    => 'Estoque & Etiquetas',
        'subtitulo' => '10 Famílias & Barcode 128',
        'icone'     => 'fa-boxes-stacked',
        'bg_icon'   => '#0284c7',
        'borda_top' => 'so-bento-card--info',
        'badge'     => 'Gôndola & Saldos',
        'badge_bg'  => 'bg-info',
        'preview'   => 'Classificação por Famílias & Etiquetas Térmicas'
    ],
    [
        'categoria' => 'gestao',
        'titulo'    => 'Finanças & Curva ABC',
        'subtitulo' => 'DRE Gerencial e Lucro Real',
        'icone'     => 'fa-chart-line',
        'bg_icon'   => '#16a34a',
        'borda_top' => 'so-bento-card--success',
        'badge'     => 'Gestão e BI',
        'badge_bg'  => 'bg-primary',
        'preview'   => 'Auditoria de CMV, Margem Bruta e Pareto'
    ],
    [
        'categoria' => 'suporte',
        'titulo'    => 'Suporte Técnico',
        'subtitulo' => 'Equipe Mr. Coding ETEC',
        'icone'     => 'fa-headset',
        'bg_icon'   => '#d97706',
        'borda_top' => 'so-bento-card--warning',
        'badge'     => 'Atendimento Direto',
        'badge_bg'  => 'bg-warning text-dark',
        'preview'   => 'WhatsApp Oficial & Contingência Local'
    ],
];

// 2. Chips de Filtro Rápido
$chipsCategorias = [
    ['id' => 'todos',    'label' => 'Todos',                     'icon' => 'fa-layer-group'],
    ['id' => 'pdv',      'label' => 'Frente de Caixa & PDV',     'icon' => 'fa-cash-register'],
    ['id' => 'vendas',   'label' => 'Vendas & NFC-e',             'icon' => 'fa-receipt'],
    ['id' => 'estoque',  'label' => 'Produtos & Estoque',         'icon' => 'fa-boxes-stacked'],
    ['id' => 'clientes', 'label' => 'Clientes',                  'icon' => 'fa-users'],
    ['id' => 'gestao',   'label' => 'Gestão & Relatórios (Admin)','icon' => 'fa-chart-pie'],
    ['id' => 'atalhos',  'label' => 'Atalhos & Teclado',          'icon' => 'fa-keyboard'],
    ['id' => 'suporte',  'label' => 'Suporte & FAQ',              'icon' => 'fa-headset'],
];

// 3. Mesa de Atalhos de Teclado do PDV
$atalhosPdv = [
    [
        'tecla'      => 'F1',
        'acao'       => 'Manual do Caixa / Ajuda Rápida',
        'descricao'  => 'Abre a janela flutuante com o resumo dos comandos operacionais sem interromper a venda em andamento.',
        'badge_tipo' => 'Geral',
        'badge_cor'  => 'bg-secondary'
    ],
    [
        'tecla'      => 'F2',
        'acao'       => 'Focar Leitor de Código de Barras',
        'descricao'  => 'Direciona imediatamente o cursor para o campo de bipagem automática por leitor óptico ou busca textual.',
        'badge_tipo' => 'Essencial',
        'badge_cor'  => 'bg-primary'
    ],
    [
        'tecla'      => 'F4',
        'acao'       => 'Finalizar Venda / Pagamento / NFC-e',
        'descricao'  => 'Abre o modal de recebimento com cálculo dinâmico de troco, seleção de pagamento e emissão do cupom fiscal.',
        'badge_tipo' => 'Fechamento',
        'badge_cor'  => 'bg-success'
    ],
    [
        'tecla'      => 'F7',
        'acao'       => 'Aplicar Desconto no Total',
        'descricao'  => 'Concede abatimento em reais (R$) ou percentual no valor total do carrinho com recálculo instantâneo.',
        'badge_tipo' => 'Desconto',
        'badge_cor'  => 'bg-info'
    ],
    [
        'tecla'      => 'F8',
        'acao'       => 'Identificar Cliente / CPF na Nota',
        'descricao'  => 'Foca no campo de seleção de cliente cadastrado ou digitação de CPF/CNPJ para inclusão no documento fiscal.',
        'badge_tipo' => 'Fiscal',
        'badge_cor'  => 'bg-warning text-dark'
    ],
    [
        'tecla'      => 'F9',
        'acao'       => 'Cancelar Venda Atual',
        'descricao'  => 'Limpa todos os itens do carrinho atual e restabelece a frente de caixa para a próxima operação.',
        'badge_tipo' => 'Perigo',
        'badge_cor'  => 'bg-danger'
    ],
    [
        'tecla'      => 'F10',
        'acao'       => 'Consultar Estoque Rápido',
        'descricao'  => 'Abre a consulta instantânea de preços de venda, estoque disponível e saldo físico por item.',
        'badge_tipo' => 'Consulta',
        'badge_cor'  => 'bg-secondary'
    ],
    [
        'tecla'      => 'ESC',
        'acao'       => 'Fechar Modais e Janelas',
        'descricao'  => 'Fecha qualquer janela suspensa ativa (pagamentos, atalhos, avisos de estoque) e devolve o foco ao leitor.',
        'badge_tipo' => 'Navegação',
        'badge_cor'  => 'bg-dark'
    ],
];

// 4. Módulos Operacionais da Base de Conhecimento
$modulosAjuda = [
    [
        'id'        => 'M1',
        'categoria' => 'pdv',
        'keywords'  => 'pdv caixa frente venda cupom nfce troco desconto leitor bipar cartao dinheiro pix balcao f2 f4 f7 f8 f9 f10',
        'titulo'    => 'Módulo 1: Operando a Frente de Caixa (PDV)',
        'icone'     => 'fa-cash-register',
        'cor_icone' => 'text-success',
        'perfil'    => 'Acesso Geral',
        'perfil_bg' => 'bg-success',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-barcode',
                'tit'   => 'Registro e Bipagem de Itens',
                'desc'  => 'Pressione <kbd class="so-kbd so-kbd-sm">F2</kbd> para focar no leitor. Bipe os produtos continuamente ou clique nos cards da grade de catálogo rápido por famílias funcionais.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-calculator',
                'tit'   => 'Ajuste de Quantidade & Desconto',
                'desc'  => 'Ajuste a quantidade diretamente nas teclas táteis <code>[-]</code> e <code>[+]</code> do cupom. Pressione <kbd class="so-kbd so-kbd-sm">F7</kbd> para aplicar desconto em reais com recálculo automático.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-user-tag',
                'tit'   => 'Identificação Fiscal do Cliente',
                'desc'  => 'Pressione <kbd class="so-kbd so-kbd-sm">F8</kbd> para selecionar um cliente já cadastrado na base ou informe o CPF/CNPJ no campo de identificação do consumidor para vinculação fiscal ao DANFE.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-receipt',
                'tit'   => 'Pagamento, Troco & Emissão NFC-e',
                'desc'  => 'Pressione <kbd class="so-kbd so-kbd-sm">F4</kbd>. Ao selecionar Dinheiro, use as cédulas rápidas (R$ 10 a 100) para cálculo imediato de troco e emita o cupom térmico com QR Code.'
            ],
        ],
        'dica'      => 'Dica de Balcão: O PDV opera 100% via teclado. O operador não precisa tirar a mão do teclado para fechar vendas e calcular trocos.',
        'dica_tipo' => 'so-callout-success'
    ],
    [
        'id'        => 'M2',
        'categoria' => 'vendas',
        'keywords'  => 'vendas historico consulta fiscal nfce cupom danfe reimpressao estorno devolucao cancelamento comprovante',
        'titulo'    => 'Módulo 2: Vendas, Consulta Fiscal NFC-e & Reimpressão',
        'icone'     => 'fa-receipt',
        'cor_icone' => 'text-primary',
        'perfil'    => 'Acesso Geral',
        'perfil_bg' => 'bg-success',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-magnifying-glass',
                'tit'   => 'Consulta de Vendas no Histórico',
                'desc'  => 'Acesse <em>Operação de Vendas > Histórico</em>. Filtre por período, número do cupom fiscal, operador de caixa ou forma de pagamento utilizada.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-print',
                'tit'   => 'Reimpressão de Cupom Térmico',
                'desc'  => 'Localize a venda desejada e clique no botão de impressão rápida para gerar a 2ª via do comprovante fiscal em formato padronizado de 80mm.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-ban',
                'tit'   => 'Cancelamento e Estorno com Devolução',
                'desc'  => 'Ao estornar uma venda autorizada, o MrStock devolve automaticamente os produtos para o estoque físico e registra a operação no log de auditoria.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-file-invoice-dollar',
                'tit'   => 'Auditoria Fiscal e Chave de Acesso',
                'desc'  => 'Consulte a chave de 44 dígitos, protocolo de autorização e QR Code de validação na tela de <em>Consulta Fiscal NFC-e</em>.'
            ],
        ],
        'dica'      => 'Atenção Operacional: O cancelamento de cupons NFC-e deve observar o prazo regulamentar da SEFAZ para evitar pendências fiscais.',
        'dica_tipo' => 'so-callout-info'
    ],
    [
        'id'        => 'M3',
        'categoria' => 'estoque',
        'keywords'  => 'estoque produtos cadastro categoria familias lucro custo margem etiqueta barcode codigo barras avaria inventario perda saldo validade',
        'titulo'    => 'Módulo 3: Catálogo, Gestão de Estoque & Etiquetas Térmicas',
        'icone'     => 'fa-boxes-stacked',
        'cor_icone' => 'text-info',
        'perfil'    => $isAdmin ? 'Acesso Total (Admin)' : 'Consulta & Saldos (Caixa)',
        'perfil_bg' => $isAdmin ? 'bg-primary' : 'bg-success',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-tags',
                'tit'   => '10 Famílias Funcionais da Papelaria',
                'desc'  => 'Produtos são organizados por famílias específicas (<em>Cadernos & Blocos, Canetas & Marcadores, Papéis & Folhas</em>, etc.), sem termos genéricos.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-percent',
                'tit'   => 'Formação de Preço e Margem de Lucro',
                'desc'  => 'O sistema calcula em tempo real a margem bruta estimada a partir do custo e preço de venda. Operadores de caixa nunca visualizam os valores de custo.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-barcode',
                'tit'   => 'Gerador de Etiquetas Code 128',
                'desc'  => 'No menu <em>Estoque > Gerador de Etiquetas</em>, gere folhas e rolos térmicos com código de barras vetorial de alta definição para gôndolas.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-arrow-right-arrow-left',
                'tit'   => 'Ajustes Manuais & Inventário',
                'desc'  => 'Para quebras, avarias, doações ou conferência de inventário, utilize <em>Movimentações</em> com justificativa obrigatória registrada no histórico.'
            ],
        ],
        'dica'      => 'Boas Práticas: Mantenha sempre preenchido o campo de "Estoque Mínimo" para ser alertado no Dashboard antes que ocorra ruptura na prateleira.',
        'dica_tipo' => 'so-callout-warning'
    ],
    [
        'id'        => 'M4',
        'categoria' => 'clientes',
        'keywords'  => 'clientes fornecedor cadastro compras ordem de compra entrada mercadoria whatsapp contato fornecedores viacep',
        'titulo'    => 'Módulo 4: Gestão de Clientes & Atendimento Humanizado',
        'icone'     => 'fa-users',
        'cor_icone' => 'text-success',
        'perfil'    => 'Acesso Geral',
        'perfil_bg' => 'bg-success',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-address-card',
                'tit'   => 'Cadastro Completo de Clientes',
                'desc'  => 'Cadastre clientes com CPF/CNPJ validado, e-mail e endereço preenchido automaticamente via CEP (ViaCEP API) para emissão fiscal ágil e fidelização.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-brands fa-whatsapp',
                'tit'   => 'Contato Direto via WhatsApp Oficial',
                'desc'  => 'Utilize o botão circular verde oficial <span class="btn-whatsapp d-inline-flex"><i class="fab fa-whatsapp"></i></span> para abrir conversas instantâneas.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-chart-simple',
                'tit'   => 'Histórico de Compras e Tíquete Médio',
                'desc'  => 'Consulte o total gasto, data da última visita e volume de compras de cada cliente cadastrado no balcão.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-user-pen',
                'tit'   => 'Edição Rápida & Inativação',
                'desc'  => 'Atualize números de contato ou inative cadastros mantendo o histórico de vendas integralmente preservado.'
            ],
        ],
        'dica'      => 'Padronização: Mantenha os números de WhatsApp com DDD e 9 dígitos para garantir a abertura correta do canal web.',
        'dica_tipo' => 'so-callout-success'
    ],
    [
        'id'        => 'M5',
        'categoria' => 'gestao',
        'keywords'  => 'compras fornecedores entrada nota fiscal reposicao custos estoque parceiros',
        'titulo'    => 'Módulo 5: Gestão de Fornecedores & Ordens de Compra',
        'icone'     => 'fa-truck-field',
        'cor_icone' => 'text-primary',
        'perfil'    => 'Exclusivo Admin',
        'perfil_bg' => 'bg-primary',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-handshake',
                'tit'   => 'Homologação de Parceiros Comerciais',
                'desc'  => 'Cadastre grandes distribuidores e fabricantes da papelaria (Tilibra, Faber-Castell, Bic, Chamex) com CNPJ e contatos de representantes.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-cart-plus',
                'tit'   => 'Entrada de Compras e Reabastecimento',
                'desc'  => 'Lance pedidos em <em>Compras > Nova Compra</em>. O estoque físico é incrementado automaticamente e o custo médio é recalculado.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-money-bill-transfer',
                'tit'   => 'Controle de Status Financeiro (PAGA / PENDENTE)',
                'desc'  => 'Monitore compromissos a pagar com fornecedores e vincule comprovantes de pagamento.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-rotate-left',
                'tit'   => 'Estorno Automático de Compras',
                'desc'  => 'Ao cancelar uma ordem de compra, o MrStock estorna as unidades injetadas no estoque prevenindo distorções contábeis.'
            ],
        ],
        'dica'      => 'Gestão de Suprimentos: Sempre informe o número da Nota Fiscal do fornecedor para auditoria cruzada de notas.',
        'dica_tipo' => 'so-callout-info'
    ],
    [
        'id'        => 'M6',
        'categoria' => 'gestao',
        'keywords'  => 'financas relatorios dre curva abc cmv lucro faturamento receita analise bi pareto margem bruta excel pdf',
        'titulo'    => 'Módulo 6: Inteligência Financeira, DRE Gerencial & Curva ABC',
        'icone'     => 'fa-chart-pie',
        'cor_icone' => 'text-warning',
        'perfil'    => 'Exclusivo Admin',
        'perfil_bg' => 'bg-primary',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-arrow-trend-up',
                'tit'   => 'Curva ABC (Princípio de Pareto)',
                'desc'  => '<strong>Classe A (80% da receita):</strong> Produtos de alto giro que não podem faltar.<br><strong>Classe B (15%):</strong> Giro intermediário.<br><strong>Classe C (5%):</strong> Cauda longa.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-file-invoice',
                'tit'   => 'DRE Gerencial e Apuração do CMV',
                'desc'  => 'Consolida a Receita Bruta, desconta deduções e o <strong>CMV (Custo da Mercadoria Vendida)</strong>, entregando o <strong>Lucro Bruto Real</strong>.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-chart-simple',
                'tit'   => 'Auditoria de Lucratividade por Família',
                'desc'  => 'Analise quais famílias funcionais geram maior rentabilidade líquida para direcionar promoções e negociações com fornecedores.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-file-export',
                'tit'   => 'Exportação e Relatórios PDF/Excel',
                'desc'  => 'Gere relatórios consolidados em tela, baixe planilhas Excel formatadas ou emita PDFs de inventário e shelf-life em 1-clique.'
            ],
        ],
        'dica'      => 'Estratégia de Compras: Concentre o capital de giro nos produtos Classe A para otimizar o fluxo de caixa da papelaria.',
        'dica_tipo' => 'so-callout-warning'
    ],
    [
        'id'        => 'M7',
        'categoria' => 'gestao',
        'keywords'  => 'contingencia problema erro leitor internet impressora travada bobina reimprimir backup sql seguranca rbac perfis versao patch',
        'titulo'    => 'Módulo 7: Resolução de Problemas, Segurança & Contingência',
        'icone'     => 'fa-shield-halved',
        'cor_icone' => 'text-danger',
        'perfil'    => 'Exclusivo Admin',
        'perfil_bg' => 'bg-primary',
        'passos'    => [
            [
                'num'   => 1,
                'icone' => 'fa-wifi',
                'tit'   => 'Operação sem Internet (Modo Local)',
                'desc'  => 'A arquitetura híbrida (XAMPP local) permite que o PDV e os caixas continuem emitindo vendas normalmente mesmo em oscilações de rede.'
            ],
            [
                'num'   => 2,
                'icone' => 'fa-barcode',
                'tit'   => 'Falha no Leitor de Código de Barras',
                'desc'  => 'Pressione <kbd class="so-kbd so-kbd-sm">F2</kbd> e digite parte do nome do item ou selecione diretamente na grade visual de produtos.'
            ],
            [
                'num'   => 3,
                'icone' => 'fa-print',
                'tit'   => 'Travamento de Impressora / Bobina',
                'desc'  => 'Após trocar a bobina, acesse <em>Histórico de Vendas</em> e clique em reimprimir sem necessidade de recriar o cupom.'
            ],
            [
                'num'   => 4,
                'icone' => 'fa-database',
                'tit'   => 'Rotina de Backup Preventivo SQL',
                'desc'  => 'Acesse <em>Configurações > Sistema & Backup</em> e clique em <strong>Baixar Backup SQL</strong> para salvar todas as 14 tabelas do banco.'
            ],
        ],
        'dica'      => 'Recomendação de Segurança: Execute o download do arquivo de backup SQL ao final de cada expediente comercial.',
        'dica_tipo' => 'so-callout-danger'
    ],
];

// 5. Perguntas Frequentes (FAQ)
$faqItems = [
    [
        'id'        => 'F1',
        'categoria' => 'suporte',
        'keywords'  => 'faq internet queda offline contingencia funcionamento local xampp rede',
        'pergunta'  => 'O que fazer se a conexão com a internet cair durante o expediente?',
        'resposta'  => 'O MrStock ERP opera em arquitetura local e híbrida. As operações de venda, registro de itens e cupom continuam funcionando normalmente no servidor XAMPP local sem qualquer interrupção no balcão da loja.'
    ],
    [
        'id'        => 'F2',
        'categoria' => 'gestao',
        'keywords'  => 'faq operador caixa permissoes usuario criar senha admin rbac seguranca',
        'pergunta'  => 'Como cadastrar novos operadores de caixa e definir permissões de acesso?',
        'resposta'  => 'Acesse o menu <em>Configurações > Sistema & Backup</em> (disponível para o perfil Administrador). O perfil "Operador de Caixa" possui acesso restrito ao PDV e Vendas, sem permissão para visualização de custos de compra, margens de lucro ou relatórios estratégicos.'
    ],
    [
        'id'        => 'F3',
        'categoria' => 'pdv',
        'keywords'  => 'faq troco calculo dinheiro cedulas rapidez balcao pagamento f4',
        'pergunta'  => 'Como funciona o cálculo automático de troco no balcão?',
        'resposta'  => 'No modal de pagamento (<kbd class="so-kbd so-kbd-sm">F4</kbd>), ao selecionar a opção Dinheiro, digite o valor recebido ou clique nos botões rápidos de cédulas (R$ 10, 20, 50, 100). O valor do troco é calculado e exibido em destaque instantaneamente.'
    ],
    [
        'id'        => 'F4',
        'categoria' => 'estoque',
        'keywords'  => 'faq etiqueta impressao gondola code 128 codigo de barras termica',
        'pergunta'  => 'Como gerar e imprimir etiquetas de gôndola para os produtos?',
        'resposta'  => 'Acesse <em>Catálogo & Estoque > Gerador de Etiquetas</em>. Selecione os produtos desejados e clique no botão de impressão para obter o documento padronizado em código de barras Code 128 Vetorial para impressoras térmicas e convencionais.'
    ],
    [
        'id'        => 'F5',
        'categoria' => 'vendas',
        'keywords'  => 'faq diferenca cancelar item cancelar venda f9 lixeira estorno',
        'pergunta'  => 'Qual a diferença entre cancelar um item do carrinho e cancelar a venda inteira?',
        'resposta'  => 'Para cancelar um item avulso, clique no ícone de lixeira na linha correspondente antes do fechamento. Para cancelar a venda inteira, pressione <kbd class="so-kbd so-kbd-sm">F9</kbd> no teclado ou o botão vermelho de cancelamento no PDV.'
    ],
    [
        'id'        => 'F6',
        'categoria' => 'gestao',
        'keywords'  => 'faq dre lucro real cmv receita faturamento resultado analise financeiro',
        'pergunta'  => 'Onde consultar o lucro bruto real obtido em determinado período?',
        'resposta'  => 'Acesse <em>Relatórios > Análise & DRE Gerencial</em>. O sistema calcula a Receita Líquida deduzida do Custo das Mercadorias Vendidas (CMV), exibindo o lucro bruto exato com precisão centesimal e margem percentual.'
    ],
];

// 6. Equipe Técnica & Suporte Acadêmico
$equipeSuporte = [
    ['nome' => 'Douglas',          'funcao' => 'Direção Técnica e Arquitetura'],
    ['nome' => 'Nikolas',          'funcao' => 'Banco de Dados e Modelagem DER'],
    ['nome' => 'Cesar',            'funcao' => 'Requisitos de Negócio e Cliente'],
    ['nome' => 'Enzo',             'funcao' => 'Documentação e Processos'],
    ['nome' => 'Eduardo Sugahara', 'funcao' => 'Demonstração e Navegação do Sistema'],
];

require_once __DIR__ . '/inc/header.php';
?>

<!-- ══ ESTILOS ESPECÍFICOS DA CENTRAL DE AJUDA & TECLADO TÁTIL ═════════════════ -->
<style>
/* KBD Corporativo Tátil SalesOps */
.so-kbd {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 30px;
    padding: 0 0.55rem;
    font-family: 'Consolas', 'Courier New', monospace;
    font-size: 0.85rem;
    font-weight: 700;
    color: #1e293b;
    background-color: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-shadow: 0 2px 0 #94a3b8;
    vertical-align: middle;
    user-select: none;
    letter-spacing: 0.5px;
    line-height: 1;
}
.so-kbd-sm {
    min-width: 26px;
    height: 22px;
    padding: 0 0.35rem;
    font-size: 0.75rem;
    box-shadow: 0 1.5px 0 #94a3b8;
}

/* Chips de Filtro Rápido */
.so-filter-chip {
    border-radius: 999px;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 0.45rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
    border: 1px solid #cbd5e1;
    background-color: #ffffff;
    color: #334155;
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    user-select: none;
    text-decoration: none;
}
.so-filter-chip:hover {
    background-color: #f8fafc;
    border-color: #94a3b8;
    color: #0f172a;
}
.so-filter-chip.active {
    background-color: var(--mr-bg-primary, #284936) !important;
    border-color: var(--mr-bg-primary, #284936) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 6px rgba(40, 73, 54, 0.25);
}

/* Bento Hero e Transições */
.so-bento-hero {
    background: linear-gradient(135deg, rgba(40, 73, 54, 0.08) 0%, rgba(106, 228, 155, 0.14) 100%);
    border-bottom: 1px solid #cbd5e1;
}
.so-bento-item {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.so-bento-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.06);
}

/* Callouts e Caixas de Alerta */
.so-callout-info    { border-left: 4px solid #0284c7 !important; background-color: #f0f9ff; }
.so-callout-success { border-left: 4px solid #16a34a !important; background-color: #f0fdf4; }
.so-callout-warning { border-left: 4px solid #d97706 !important; background-color: #fffbeb; }
.so-callout-danger  { border-left: 4px solid #dc2626 !important; background-color: #fef2f2; }

/* Estilização para Impressão Limpa */
@media print {
    .so-sidebar, .so-header, .so-search-hero, .so-chips-bar, .no-print, .btn-print-guide {
        display: none !important;
    }
    .main-panel {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .so-card {
        border: 1px solid #94a3b8 !important;
        box-shadow: none !important;
        break-inside: avoid;
        margin-bottom: 1.5rem !important;
    }
    .accordion-collapse {
        display: block !important;
    }
    .accordion-button::after {
        display: none !important;
    }
}
</style>

<div class="content-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h2 class="fw-bold text-dark m-0"><i class="fas fa-circle-question text-primary me-2"></i>Central de Ajuda</h2>
        <p class="text-muted m-0">Consulte manuais operacionais, guia tátil de atalhos do caixa e respostas para dúvidas frequentes.</p>
    </div>
    <div class="d-flex align-items-center gap-2 no-print">
        <button type="button" class="btn btn-secondary text-white fw-semibold shadow-sm btn-print-guide" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir Guia Rápido
        </button>
    </div>
</div>

<div class="content-body">

    <!-- ══ BARRA DE BUSCA HERO SEARCH-FIRST ══════════════════════════════════════ -->
    <div class="so-card mb-4 border overflow-hidden so-search-hero">
        <div class="p-4 text-center so-bento-hero">
            <span class="badge bg-primary text-white px-3 py-1 rounded-pill mb-2 fw-semibold">Base de Conhecimento SalesOps Enterprise v2.1</span>
            <h3 class="fw-bold text-dark mb-2">Como podemos te ajudar hoje?</h3>
            <p class="text-muted mb-3" style="font-size: 0.95rem;">Digite uma palavra-chave para filtrar instantaneamente atalhos, módulos operacionais e perguntas frequentes.</p>
            
            <div class="so-search-box mx-auto position-relative" style="max-width: 620px;">
                <label for="ajudaSearchInput" class="visually-hidden">Buscar na Central de Ajuda</label>
                <i class="fas fa-search search-icon fs-6 text-primary"></i>
                <input type="text" id="ajudaSearchInput" class="form-control form-control-lg shadow-sm bg-white" 
                       placeholder="Ex: atalhos, troco, desconto, nfc-e, etiqueta, estoque, cancelamento, dre..." 
                       oninput="filtrarAjuda(this.value)" autocomplete="off">
            </div>
            
            <div class="mt-3 d-flex justify-content-center align-items-center gap-2 text-muted small" id="searchFeedbackContainer">
                <span id="searchFeedbackLabel">Consulte mais de 20 tópicos operacionais da Papelaria Real</span>
                <span class="badge bg-primary text-white tabular-nums px-2 py-1" id="searchCountBadge">20 tópicos</span>
            </div>
        </div>
    </div>

    <!-- ══ CHIPS DE FILTRO RÁPIDO POR CATEGORIA ═════════════════════════════════ -->
    <div class="so-chips-bar mb-4 no-print">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted small fw-semibold me-1"><i class="fas fa-filter text-primary me-1"></i>Filtrar por:</span>
            <?php foreach ($chipsCategorias as $chip): ?>
                <button type="button" 
                        class="so-filter-chip <?= $chip['id'] === 'todos' ? 'active' : '' ?>" 
                        data-category-filter="<?= htmlspecialchars($chip['id']) ?>" 
                        onclick="filtrarPorCategoria('<?= htmlspecialchars($chip['id']) ?>', this)">
                    <i class="fas <?= htmlspecialchars($chip['icon']) ?>"></i>
                    <span><?= htmlspecialchars($chip['label']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ══ CARDS BENTO DE ACESSO RÁPIDO (GRID 4 COLUNAS) ═════════════════════════ -->
    <div class="row g-3 mb-4" id="bentoCardsGrid">
        <?php foreach ($bentoCards as $card): ?>
            <div class="col-md-6 col-xl-3 ajuda-bento-card" data-categoria="<?= htmlspecialchars($card['categoria']) ?>">
                <div class="so-card <?= htmlspecialchars($card['borda_top']) ?> so-bento-item h-100 p-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge <?= htmlspecialchars($card['badge_bg']) ?> text-white small"><?= htmlspecialchars($card['badge']) ?></span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center text-white shadow-sm flex-shrink-0"
                             style="width: 44px; height: 44px; background: <?= htmlspecialchars($card['bg_icon']) ?>; font-size: 1.15rem;">
                            <i class="fas <?= htmlspecialchars($card['icone']) ?>"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark m-0"><?= htmlspecialchars($card['titulo']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($card['subtitulo']) ?></small>
                        </div>
                    </div>
                    <div class="mt-3 pt-2 border-top small text-muted">
                        <?= $card['preview'] ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ══ SEÇÃO 1: MESA DE ATALHOS DE TECLADO DO PDV ═══════════════════════════ -->
    <div class="so-card mb-4 border ajuda-section" data-categoria="atalhos" data-keywords="atalho teclado f1 f2 f4 f7 f8 f9 f10 esc pdv caixa tecla operacao comando balcao leitor cupom">
        <div class="so-card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="so-card-title text-white m-0">
                <i class="fas fa-keyboard text-success me-2"></i>Mesa Tátil de Atalhos de Teclado (Frente de Caixa)
            </h5>
            <span class="badge bg-success text-white">100% Operável por Teclado</span>
        </div>
        <div class="so-card-body p-4">
            <p class="text-muted small mb-3">
                O Frente de Caixa (PDV) do MrStock ERP foi desenvolvido para permitir agilidade máxima e ergonomia no balcão, dispensando o uso de mouse nas operações cotidianas de checkout:
            </p>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light text-secondary small">
                        <tr>
                            <th width="15%" class="text-center" scope="col">Tecla de Atalho</th>
                            <th width="30%" scope="col">Ação Operacional</th>
                            <th width="45%" scope="col">Descrição do Fluxo no Caixa</th>
                            <th width="10%" class="text-center" scope="col">Tipo</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php foreach ($atalhosPdv as $at): ?>
                            <tr>
                                <td class="text-center">
                                    <kbd class="so-kbd"><?= htmlspecialchars($at['tecla']) ?></kbd>
                                </td>
                                <td>
                                    <strong class="text-dark"><?= htmlspecialchars($at['acao']) ?></strong>
                                </td>
                                <td class="text-muted">
                                    <?= htmlspecialchars($at['descricao']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= htmlspecialchars($at['badge_cor']) ?> text-white"><?= htmlspecialchars($at['badge_tipo']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 2: MANUAIS OPERACIONAIS PASSO A PASSO (ACCORDION) ════════════ -->
    <div class="so-card mb-4 border ajuda-section" data-categoria="pdv vendas estoque clientes gestao" data-keywords="manuais modais modulos passo a passo estoque nfc-e compras dre curva abc contingencia">
        <div class="so-card-header d-flex justify-content-between align-items-center">
            <h5 class="so-card-title m-0">
                <i class="fas fa-book-open text-primary me-2"></i>Manuais Operacionais Passo a Passo
            </h5>
            <span class="badge bg-primary text-white tabular-nums" id="modulosCountBadge"><?= count($modulosAjuda) ?> Módulos</span>
        </div>
        <div class="so-card-body p-0">
            <div class="accordion accordion-flush" id="accordionManuais">
                <?php foreach ($modulosAjuda as $index => $mod): ?>
                    <div class="accordion-item ajuda-item" 
                         data-categoria="<?= htmlspecialchars($mod['categoria']) ?>" 
                         data-keywords="<?= htmlspecialchars($mod['keywords']) ?>">
                        <h2 class="accordion-header" id="heading<?= htmlspecialchars($mod['id']) ?>">
                            <button class="accordion-button collapsed fw-bold text-dark d-flex align-items-center justify-content-between gap-2" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse<?= htmlspecialchars($mod['id']) ?>" 
                                    aria-expanded="false" 
                                    aria-controls="collapse<?= htmlspecialchars($mod['id']) ?>">
                                <div class="d-flex align-items-center">
                                    <i class="fas <?= htmlspecialchars($mod['icone']) ?> <?= htmlspecialchars($mod['cor_icone']) ?> me-2"></i>
                                    <span><?= htmlspecialchars($mod['titulo']) ?></span>
                                </div>
                                <span class="badge <?= htmlspecialchars($mod['perfil_bg']) ?> text-white ms-auto me-3 small"><?= htmlspecialchars($mod['perfil']) ?></span>
                            </button>
                        </h2>
                        <div id="collapse<?= htmlspecialchars($mod['id']) ?>" 
                             class="accordion-collapse collapse" 
                             aria-labelledby="heading<?= htmlspecialchars($mod['id']) ?>" 
                             data-bs-parent="#accordionManuais">
                            <div class="accordion-body p-4">
                                <div class="row g-3 mb-3">
                                    <?php foreach ($mod['passos'] as $passo): ?>
                                        <div class="col-md-6 col-12">
                                            <div class="border rounded p-3 h-100 bg-light">
                                                <div class="d-flex align-items-center gap-2 mb-2">
                                                    <span class="badge bg-primary text-white rounded-pill tabular-nums">Passo <?= htmlspecialchars((string)$passo['num']) ?></span>
                                                    <h6 class="fw-bold text-dark m-0">
                                                        <i class="fas <?= htmlspecialchars($passo['icone']) ?> text-primary me-1"></i>
                                                        <?= htmlspecialchars($passo['tit']) ?>
                                                    </h6>
                                                </div>
                                                <p class="text-muted small m-0">
                                                    <?= $passo['desc'] ?>
                                                </p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="alert <?= htmlspecialchars($mod['dica_tipo']) ?> border shadow-sm m-0 p-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fas fa-lightbulb text-warning fs-5"></i>
                                        <span class="small text-dark fw-semibold"><?= htmlspecialchars($mod['dica']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 3: PERGUNTAS FREQUENTES (FAQ OPERACIONAL) ══════════════════════ -->
    <div class="so-card mb-4 border ajuda-section" data-categoria="suporte gestao pdv estoque vendas" data-keywords="faq perguntas frequentes duvidas respostas cancelamento internet troco etiqueta lucro dre">
        <div class="so-card-header bg-light d-flex justify-content-between align-items-center">
            <h5 class="so-card-title text-dark m-0">
                <i class="fas fa-comments-question text-primary me-2"></i>Perguntas Frequentes (FAQ Operacional)
            </h5>
            <span class="badge bg-secondary text-white tabular-nums"><?= count($faqItems) ?> Perguntas</span>
        </div>
        <div class="so-card-body p-0">
            <div class="accordion accordion-flush" id="accordionFaq">
                <?php foreach ($faqItems as $index => $faq): ?>
                    <div class="accordion-item ajuda-item" 
                         data-categoria="<?= htmlspecialchars($faq['categoria']) ?>" 
                         data-keywords="<?= htmlspecialchars($faq['keywords']) ?>">
                        <h2 class="accordion-header" id="headingFaq<?= htmlspecialchars($faq['id']) ?>">
                            <button class="accordion-button collapsed fw-bold text-dark" 
                                    type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapseFaq<?= htmlspecialchars($faq['id']) ?>" 
                                    aria-expanded="false">
                                <i class="fas fa-circle-question text-muted me-2"></i>
                                <?= htmlspecialchars($faq['pergunta']) ?>
                            </button>
                        </h2>
                        <div id="collapseFaq<?= htmlspecialchars($faq['id']) ?>" 
                             class="accordion-collapse collapse" 
                             data-bs-parent="#accordionFaq">
                            <div class="accordion-body p-4 bg-light">
                                <p class="text-muted small m-0 leading-relaxed">
                                    <?= $faq['resposta'] ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ══ SEÇÃO 4: CONTATO, SUPORTE & EQUIPE MR. CODING ═══════════════════════ -->
    <div class="so-card border ajuda-section" data-categoria="suporte" data-keywords="suporte equipe mr coding etec douglas nikolas cesar enzo sugahara whatsapp contato tcc">
        <div class="so-card-header bg-primary text-white">
            <h5 class="so-card-title text-white m-0">
                <i class="fas fa-headset me-2"></i>Equipe Técnica & Suporte Acadêmico (Mr. Coding)
            </h5>
        </div>
        <div class="so-card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7 col-12">
                    <h6 class="fw-bold text-dark mb-2">Projeto de Conclusão de Curso (TCC) — ETEC Fernando Prestes (Sorocaba/SP)</h6>
                    <p class="text-muted small mb-3">
                        O <strong>MrStock ERP</strong> foi arquitetado e implementado pela equipe de engenharia de software <strong>Mr. Coding</strong>:
                    </p>
                    <div class="row g-2 small">
                        <?php foreach ($equipeSuporte as $membro): ?>
                            <div class="col-md-6 col-12">
                                <span class="fw-bold text-dark">• <?= htmlspecialchars($membro['nome']) ?>:</span> 
                                <span class="text-muted"><?= htmlspecialchars($membro['funcao']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-5 col-12 text-lg-end">
                    <div class="border rounded p-3 bg-light text-center">
                        <span class="text-muted small d-block mb-1">Canal de Atendimento Oficial</span>
                        <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                            <i class="fas fa-phone text-muted small"></i>
                            <strong class="text-dark tabular-nums">(15) 99123-4567</strong>
                            <a href="https://wa.me/5515991234567?text=Ola%20equipe%20MrStock%2C%20preciso%20de%20suporte%20no%20ERP" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="btn-whatsapp" 
                               title="Conversar via WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                        <a href="https://wa.me/5515991234567?text=Ola%20equipe%20MrStock%2C%20preciso%20de%20suporte%20no%20ERP" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="btn btn-success fw-bold px-4 shadow-sm w-100 text-white">
                            <i class="fab fa-whatsapp me-1"></i> Abrir Conversa no WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ══ SCRIPT DE FILTRAGEM DINÂMICA SEARCH-FIRST & CHIPS ═══════════════════════ -->
<script>
let categoriaAtiva = 'todos';

function escapeHTML(str) {
    return (str || '').replace(/[&<>'"]/g, tag => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        "'": '&#39;',
        '"': '&quot;'
    }[tag] || tag));
}

function filtrarPorCategoria(categoria, chipElement) {
    categoriaAtiva = categoria;
    
    // Atualiza estado visual dos chips
    document.querySelectorAll('.so-filter-chip').forEach(el => el.classList.remove('active'));
    if (chipElement) {
        chipElement.classList.add('active');
    }

    // Executa filtragem combinada com o input atual
    const termo = document.getElementById('ajudaSearchInput').value;
    filtrarAjuda(termo);
}

function filtrarAjuda(termo) {
    termo = (termo || '').toLowerCase().trim();
    const itens = document.querySelectorAll('.ajuda-item, .ajuda-section, .ajuda-bento-card');
    const feedbackLabel = document.getElementById('searchFeedbackLabel');
    const countBadge = document.getElementById('searchCountBadge');
    let visiveis = 0;

    itens.forEach(el => {
        const catEl = el.getAttribute('data-categoria') || '';
        const keywords = (el.getAttribute('data-keywords') || '') + ' ' + el.innerText.toLowerCase();

        // Checagem de Categoria
        const matchCategoria = (categoriaAtiva === 'todos') || catEl.includes(categoriaAtiva);

        // Checagem de Termo Textual
        const matchTermo = !termo || keywords.includes(termo);

        if (matchCategoria && matchTermo) {
            el.style.display = '';
            visiveis++;

            // Se for accordion e houver busca textual ativa, expande automaticamente
            if (termo && el.classList.contains('ajuda-item')) {
                const collapseEl = el.querySelector('.accordion-collapse');
                if (collapseEl && !collapseEl.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(collapseEl, { toggle: false });
                    bsCollapse.show();
                }
            }
        } else {
            el.style.display = 'none';
        }
    });

    // Atualização de Feedback e Contadores
    if (countBadge) {
        countBadge.textContent = visiveis + (visiveis === 1 ? ' resultado' : ' resultados');
    }

    if (feedbackLabel) {
        if (termo || categoriaAtiva !== 'todos') {
            feedbackLabel.innerHTML = `Exibindo <strong>${visiveis}</strong> tópicos encontrados${termo ? ` para "<em>${escapeHTML(termo)}</em>"` : ''}.`;
        } else {
            feedbackLabel.textContent = 'Consulte mais de 20 tópicos operacionais da Papelaria Real';
        }
    }
}
</script>

<?php require_once __DIR__ . '/inc/footer.php'; ?>


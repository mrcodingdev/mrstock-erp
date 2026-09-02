# 🧾 Módulo: Emissor de Cupom Fiscal Térmico
**Arquivo Principal:** `vendas/cupom.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
O módulo de Cupom Fiscal é responsável por formatar e exibir o comprovante de venda para impressão imediata em impressoras térmicas de balcão (bobinas de 80mm ou 58mm) ou em folha A4. Ele reproduz fielmente as informações fiscais da Papelaria Real, discriminação de itens, totais, tributos aproximados (Lei 12.741/2012) e o QR Code oficial de validação.

---

## 2. Interface & Componentes Visuais
- **Dimensionamento Dinâmico por CSS Variables:**
  - `80mm`: Largura de tela `360px` / Largura de impressão `80mm`.
  - `58mm`: Largura de tela `280px` / Largura de impressão `58mm`.
  - `A4`: Largura de tela `700px` / Largura de impressão `210mm`.
- **Cabeçalho Fiscal Completo:** Razão Social (`Papelaria Real Ltda - ME`), CNPJ, Endereço e Telefone.
- **Tabela de Itens em Tipografia Monospace / Tabular:** Código, descrição, quantidade, valor unitário e total.
- **QR Code SVG Vetorial Inline:** Renderizado no rodapé com Chave de Acesso de 44 dígitos didática SEFAZ-SP.
- **Painel de Ações com Botões Sólidos:** Botão "Imprimir Cupom" (`.btn-primary`), "Nova Venda" (`.btn-success`) e "Ver NFC-e" (`.btn-secondary`).

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Ingestão de Configuração e Cálculos Tributários
```php
$vendaId = (int)($_GET['id'] ?? 0);
$venda = get_venda_by_id($pdo, $vendaId);
$itens = get_itens_venda($pdo, $vendaId);

// Ingestão do formato da impressora
$formatoImpressora = get_app_config($pdo, 'pdv_impressora', '80mm');
$larguraTela = ($formatoImpressora === '58mm') ? '280px' : (($formatoImpressora === 'A4') ? '700px' : '360px');
$larguraPrint = ($formatoImpressora === '58mm') ? '58mm' : (($formatoImpressora === 'A4') ? '210mm' : '80mm');

// Discriminação Tributária (Lei Federal 12.741/2012 - IBPT)
$valorTotal = (float)$venda['total'];
$tribFederal = $valorTotal * 0.1345; // 13,45% Impostos Federais
$tribEstadual = $valorTotal * 0.1800; // 18,00% ICMS SP
$tribTotal = $tribFederal + $tribEstadual;

// Geração de Chave Didática e QR Code SVG
$chaveAcesso = gerar_chave_nfce_simulada($vendaId, $venda['data_venda']);
$qrCodeSvg = gerarQRCodeSVG("https://www.nfce.fazenda.sp.gov.br/qrcode?p=" . $chaveAcesso . "|2|1|1|" . sha1($chaveAcesso));
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Parâmetros Sanitizados:** ID da venda convertido para inteiro estrito `(int)$_GET['id']`.
- **Prevenção de XSS:** Todos os textos de produtos e clientes escapados com `htmlspecialchars()`.
- **CSS @media print Otimizado:** Ocultação automática de botões, barras de navegação e menus durante a impressão física.

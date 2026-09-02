# 🏷️ Módulo: Gerador de Etiquetas de Código de Barras (Code 128B)
**Arquivos Principais:** `produtos/etiquetas.php`, `inc/barcode_helper.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
Gera folhas de etiquetas adesivas padronizadas para gôndolas e produtos da Papelaria Real que não possuem código de barras original de fábrica. O módulo utiliza um algoritmo matemático puro em PHP que renderiza elementos `<svg>` vetoriais ultra-nítidos sem dependência de bibliotecas gráficas pesadas (GD/Imagick) ou conexões com a internet.

---

## 2. Interface & Componentes Visuais
- **Seletor de Quantidade e Layout:** Permite definir quantas etiquetas imprimir de cada produto (ex: 1, 5, 10, 20 unidades) e o tamanho da etiqueta (Pimaco A4 / Bobina Térmica).
- **Preview Interativo em Grade:** Exibição da etiqueta contendo Nome da Empresa (`Papelaria Real`), Nome do Produto, Código EAN/128, Código de Barras Vetorial e Preço de Venda em destaque `.tabular-nums`.
- **Botão de Impressão Rápida:** Dispara `window.print()` com CSS `@media print` formatado para folhas A4 adesivas.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Algoritmo Vetorial SVG Code 128 (Subset B) em `inc/barcode_helper.php`
```php
function gerarBarcodeSVG(string $code, int $height = 50, float $moduleWidth = 1.8): string {
    $patterns = [
        ' ' => '212222', '!' => '222122', '"' => '222221', '#' => '121223',
        // Padrões binários de barras e espaços do Code 128B...
    ];
    
    $checksum = 104; // Start Code B
    // Cálculo do dígito verificador ponderado módulo 103
    for ($i = 0; $i < strlen($code); $i++) {
        $checksum += (ord($code[$i]) - 32) * ($i + 1);
    }
    $checksumChar = chr(($checksum % 103) + 32);
    
    // Constrói os retângulos SVG
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' height='{$height}' viewBox='0 0 " . ($totalWidth) . " {$height}'>";
    $x = 10;
    foreach ($barSequence as $bar) {
        if ($bar['is_black']) {
            $svg .= "<rect x='{$x}' y='0' width='{$bar['w']}' height='{$height}' fill='#000000'/>";
        }
        $x += $bar['w'];
    }
    $svg .= "</svg>";
    return $svg;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Sanitização de Código:** O código de barras é filtrado para aceitar apenas caracteres válidos da tabela ASCII imprimível (32 a 126).
- **Sem I/O de Rede:** 100% autônomo e seguro contra vazamentos de dados ou bloqueios de internet.

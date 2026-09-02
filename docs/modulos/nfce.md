# 📄 Módulo: Danfe NFC-e Didática & Validação Fiscal
**Arquivo Principal:** `vendas/nfce.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
O módulo de NFC-e (Nota Fiscal de Consumidor Eletrônica) simula o ambiente da Secretaria da Fazenda de São Paulo (SEFAZ-SP), apresentando a visualização formal do Documento Auxiliar da NFC-e (DANFE). Ele consolida a conformidade da Papelaria Real com a legislação de transparência fiscal e serve como peça central de demonstração acadêmica na banca do TCC.

---

## 2. Interface & Componentes Visuais
- **Visual Padrão DANFE SEFAZ:** Margens precisas, grade corporativa e código de barras Code 128B no topo.
- **Chave de Acesso de 44 Dígitos Formatada em 11 Blocos de 4:** `3526 0950 3348 0800 0138 6500 1000 0001 2310 0000 1234`.
- **Discriminação de Tributos:** Informações consolidadas de impostos estaduais e federais calculados pelo valor real dos itens.
- **Painel de Ações:** Botões para impressão direta em A4 e download do payload XML simulado.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Algoritmo de Geração de Chave Didática de 44 Dígitos
```php
function gerar_chave_nfce_simulada(int $vendaId, string $dataVenda): string {
    $uf = "35"; // São Paulo
    $anoMes = date('ym', strtotime($dataVenda)); // 2609
    $cnpj = "50334808000138"; // CNPJ Papelaria Real
    $modelo = "65"; // Modelo NFC-e
    $serie = "001";
    $numero = str_pad($vendaId, 9, '0', STR_PAD_LEFT);
    $tipoEmissao = "1"; // Emissão Normal
    $codigoNumerico = str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    
    $chaveSemDV = $uf . $anoMes . $cnpj . $modelo . $serie . $numero . $tipoEmissao . $codigoNumerico;
    
    // Cálculo do Dígito Verificador (Módulo 11)
    $pesos = [2, 3, 4, 5, 6, 7, 8, 9];
    $soma = 0;
    $pesoIdx = 0;
    for ($i = strlen($chaveSemDV) - 1; $i >= 0; $i--) {
        $soma += (int)$chaveSemDV[$i] * $pesos[$pesoIdx];
        $pesoIdx = ($pesoIdx + 1) % count($pesos);
    }
    $resto = $soma % 11;
    $dv = ($resto == 0 || $resto == 1) ? 0 : (11 - $resto);
    
    return $chaveSemDV . $dv;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Acesso Público para Consulta de Comprovante:** Operadores e clientes podem visualizar a DANFE mediante o ID da venda.
- **Integridade Criptográfica:** O payload de validação utiliza hash SHA-1 concatenado à URL institucional da SEFAZ-SP.

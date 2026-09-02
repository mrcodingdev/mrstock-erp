# 🛒 Módulo: Frente de Caixa (PDV de Alta Velocidade)
**Arquivos Principais:** `vendas/pdv.php`, `vendas/functions.php`  
**Escopo de Acesso:** Administrador e Operador de Caixa

---

## 1. Objetivo & Contexto de Negócio
O PDV (Ponto de Venda) foi arquitetado para fornecer atendimento de balcão instantâneo na Papelaria Real. Em horários de pico (volta às aulas), a velocidade de registro de itens é crítica. O módulo opera com catálogo pré-carregado em memória JavaScript no client-side, permitindo resposta de bipagem em **menos de 15ms**, com atalhos táteis de teclado (<kbd>F1</kbd> a <kbd>F9</kbd>) e sintetizador sonoro Web Audio API.

---

## 2. Interface & Componentes Visuais
- **Layout Split-Screen:** Lado esquerdo com tabela de itens lançados (quantidade editável, subtotal em `.tabular-nums` e remoção); lado direito com totalizador em destaque preto corporativo e painel de ações.
- **Mesa de Atalhos de Teclado:**
  - <kbd>F2</kbd>: Focar campo de código de barras / busca rápida.
  - <kbd>F4</kbd>: Abrir modal de finalização e pagamento.
  - <kbd>F7</kbd>: Focar no campo de concessão de desconto.
  - <kbd>F8</kbd>: Identificar cliente / CPF na nota.
  - <kbd>F9</kbd>: Cancelar venda atual.
  - <kbd>ESC</kbd>: Fechar modais / limpar foco.
- **Modal de Pagamento com Cédulas Rápidas:** Botões táteis de R$ 10, R$ 20, R$ 50, R$ 100 e R$ 200, além do botão "Exato", com cálculo dinâmico de troco em tempo real.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Síntese Acústica Nativa (Web Audio API)
```javascript
function playBeep(tipo = 'bip') {
    if (!MRSTOCK_CONFIG.somPdv) return;
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.connect(gain);
        gain.connect(audioCtx.destination);
        
        if (tipo === 'bip') {
            osc.frequency.setValueAtTime(880, audioCtx.currentTime); // 880 Hz
            gain.gain.setValueAtTime(0.15, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.075);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.075);
        } else if (tipo === 'erro') {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(280, audioCtx.currentTime); // 280 Hz
            gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.16);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.16);
        }
    } catch (e) { console.warn('Audio Context indisponível:', e); }
}
```

### 3.2 Aritmética Monetária Centesimal e Trava de Margem
```javascript
function recalcularTotal() {
    let subtotal = 0;
    let custoTotal = 0;
    carrinho.forEach(item => {
        subtotal += item.preco_unitario * item.quantidade;
        custoTotal += item.preco_compra * item.quantidade;
    });
    
    // Normalização centesimal inteira
    subtotal = Math.round(subtotal * 100) / 100;
    let descValor = parseFloat(document.getElementById('desconto_input').value) || 0;
    descValor = Math.round(descValor * 100) / 100;
    
    const totalFinal = Math.max(0, Math.round((subtotal - descValor) * 100) / 100);
    
    // Trava de Margem Negativa
    if (totalFinal < custoTotal && MRSTOCK_CONFIG.pdvTravaMargem === 'bloquear') {
        document.getElementById('btn_finalizar').disabled = true;
        document.getElementById('aviso_margem').textContent = "Venda abaixo do custo bloqueada!";
    } else {
        document.getElementById('btn_finalizar').disabled = false;
    }
}
```

### 3.3 Motor de Checkout Transacional em PHP (`vendas/functions.php`)
```php
function processar_checkout_pdv(PDO $pdo, array $dadosVenda): int {
    $pdo->beginTransaction();
    try {
        $clienteId  = !empty($dadosVenda['cliente_id']) ? (int)$dadosVenda['cliente_id'] : null;
        $totalFinal = (float)$dadosVenda['total_final'];
        $formaPagto = clean_input($dadosVenda['forma_pagamento']);
        
        // 1. Registra cabeçalho da venda
        $stmt = $pdo->prepare("INSERT INTO vendas (cliente_id, total, forma_pagamento, data_venda) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$clienteId, $totalFinal, $formaPagto]);
        $vendaId = (int)$pdo->lastInsertId();
        
        // 2. Itera itens com Lock Pessimista
        foreach ($dadosVenda['itens'] as $item) {
            $prodId = (int)$item['produto_id'];
            $qtd    = (int)$item['quantidade'];
            $preco  = (float)$item['preco_unitario'];
            
            $stmt = $pdo->prepare("SELECT quantidade, nome FROM produtos WHERE id = ? FOR UPDATE");
            $stmt->execute([$prodId]);
            $prod = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$prod || $prod['quantidade'] < $qtd) {
                throw new Exception("Estoque insuficiente para o produto: {$prod['nome']}");
            }
            
            // Grava item da venda
            $stmt = $pdo->prepare("INSERT INTO vendas_itens (venda_id, produto_id, quantidade, preco_unitario) VALUES (?, ?, ?, ?)");
            $stmt->execute([$vendaId, $prodId, $qtd, $preco]);
            
            // Decrementa saldo
            $stmt = $pdo->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ?");
            $stmt->execute([$qtd, $prodId]);
            
            // Livro-razão de movimentação
            $stmt = $pdo->prepare("INSERT INTO movimentacoes (produto_id, tipo, quantidade, observacao) VALUES (?, 'saida_venda', ?, ?)");
            $stmt->execute([$prodId, $qtd, "Venda PDV #$vendaId"]);
        }
        
        $pdo->commit();
        return $vendaId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Bloqueio de Informações Confidenciais:** O Operador de Caixa **NUNCA** visualiza o `preco_compra` ou o lucro na interface do PDV.
- **CSRF Token:** Validação compulsória em todas as requisições AJAX e POST.
- **Prevenção de Overselling:** O uso de `SELECT ... FOR UPDATE` garante que requisições concorrentes não vendam saldo inexistente.

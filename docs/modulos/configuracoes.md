# ⚙️ Módulo: Configurações do Sistema & Backup SQL
**Arquivos Principais:** `configuracoes.php`, `config.php`, `inc/auth.php`  
**Escopo de Acesso:** Exclusivo Administrador (`require_admin()`)

---

## 1. Objetivo & Contexto de Negócio
Centraliza os 21 parâmetros operacionais, fiscais, de estoque e de experiência do usuário da Papelaria Real. Organizado em **7 abas segmentadas full-width**, o módulo também fornece a ferramenta de **Backup SQL em 1-Clique**, gerando o dump completo das 14 tabelas com integridade transacional.

---

## 2. Interface & Componentes Visuais (7 Abas Full-Width)
1. **Perfil:** Nome de exibição, e-mail institucional e cargo.
2. **Segurança:** Troca de senha com exigência da senha atual e criptografia **BCrypt Cost 12**.
3. **Loja & Fiscal:** Razão Social (`Papelaria Real Ltda - ME`), CNPJ, Telefone, WhatsApp, Endereço e Alíquota padrão.
4. **PDV & Caixa:** Formato de Impressora (80mm, 58mm, A4), Feedback Sonoro (ligado/desligado), Limite Máximo de Desconto (%) e Trava de Margem Negativa (bloquear/aviso/nenhum).
5. **Estoque & Alertas:** Estoque Mínimo Padrão (unidades), Janela de Alerta de Vencimento (dias) e Bloqueio de Saldo Negativo.
6. **Aparência & Ergonomia:** Densidade de Tabelas (compacta/padrão/confortável), Tamanho da Fonte e Linhas Zebradas.
7. **Sistema & Backup:** Informações de versão (`v2.1.0 Enterprise`), status do banco de dados e botão para download do dump SQL completo.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Funções de Leitura e Gravação de Parâmetros
```php
function get_app_config(PDO $pdo, string $chave, string $default = ''): string {
    $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
    $stmt->execute([$chave]);
    $val = $stmt->fetchColumn();
    return ($val !== false) ? (string)$val : $default;
}

function set_app_config(PDO $pdo, string $chave, string $valor): bool {
    $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    return $stmt->execute([$chave, $valor]);
}
```

### 3.2 Rotina de Dump SQL em 1-Clique das 14 Tabelas
```php
function gerar_backup_sql(PDO $pdo): string {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $sqlDump = "-- MrStock ERP v2.1.0 Backup SQL\n-- Data: " . date('Y-m-d H:i:s') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";
    
    foreach ($tables as $t) {
        $create = $pdo->query("SHOW CREATE TABLE `{$t}`")->fetch(PDO::FETCH_ASSOC);
        $sqlDump .= "DROP TABLE IF EXISTS `{$t}`;\n" . $create['Create Table'] . ";\n\n";
        
        $rows = $pdo->query("SELECT * FROM `{$t}`")->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) {
            $sqlDump .= "LOCK TABLES `{$t}` WRITE;\nINSERT INTO `{$t}` VALUES ";
            $valRows = [];
            foreach ($rows as $r) {
                $quoted = array_map(function($v) use ($pdo) {
                    return is_null($v) ? 'NULL' : $pdo->quote($v);
                }, array_values($r));
                $valRows[] = "(" . implode(",", $quoted) . ")";
            }
            $sqlDump .= implode(",\n", $valRows) . ";\nUNLOCK TABLES;\n\n";
        }
    }
    $sqlDump .= "SET FOREIGN_KEY_CHECKS=1;\n";
    return $sqlDump;
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Proteção Estrita:** Usuários não-administradores têm o acesso bloqueado com redirect.
- **Validação de Senha Anterior:** A alteração de credenciais exige a confirmação da senha vigente antes de aplicar o hash `PASSWORD_BCRYPT`.
- **Proteção CSRF Universal:** Tokens validados em cada uma das 7 abas.

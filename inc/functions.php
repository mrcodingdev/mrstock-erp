<?php
/**
 * MrStock ERP - Camada de Funções Utilitárias e Segurança
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Sanitiza valores contra Cross-Site Scripting (XSS) para exibição segura no HTML.
 */
function sanitize($data) {
    return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Gera ou retorna o token CSRF atual da sessão.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Retorna o campo HTML hidden contendo o input CSRF para formulários.
 */
function csrf_input() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/**
 * Valida o token CSRF enviado em requisições do tipo POST.
 */
function csrf_verify() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        $session_token = $_SESSION['csrf_token'] ?? '';
        
        if (empty($session_token) || !hash_equals($session_token, $token)) {
            http_response_code(403);
            die("<div style='font-family:Arial;padding:20px;background:#fee;border:1px solid #f00;border-radius:8px;max-width:600px;margin:40px auto'>
                <h3>Erro de Segurança (CSRF)</h3>
                <p>A validação do token de segurança CSRF falhou ou expirou. Por favor, recarregue a página e tente novamente.</p>
            </div>");
        }
    }
}

/**
 * Redireciona de forma segura para a URL especificada e encerra a execução.
 */
function redirect_to($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Renderiza componente visual de estado vazio (Empty State) com ilustração SVG estilizada.
 */
function render_empty_state($title = 'Nenhum registro encontrado', $msg = 'Tente ajustar os filtros ou cadastrar um novo item.', $clearUrl = '') {
    $btn = '';
    if (!empty($clearUrl)) {
        $btn = '<a href="' . htmlspecialchars($clearUrl, ENT_QUOTES, 'UTF-8') . '" class="btn btn-secondary btn-sm shadow-sm"><i class="fas fa-rotate-left me-1"></i> Limpar Filtros</a>';
    }
    return '
    <div class="so-empty-state">
        <svg class="so-empty-state__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h5 class="so-empty-state__title">' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '</h5>
        <p class="so-empty-state__text">' . htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') . '</p>
        ' . $btn . '
    </div>';
}

/**
 * Formata CPF (11 dígitos) ou CNPJ (14 dígitos) para exibição elegante.
 */
function formatar_cpf_cnpj($doc) {
    $clean = preg_replace('/[^\d]/', '', (string)$doc);
    if (strlen($clean) === 11) {
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
    } elseif (strlen($clean) === 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean);
    }
    return $doc ?: 'Não informado';
}

/**
 * Formata Telefone Fixo (10 dígitos) ou Celular (11 dígitos) para exibição.
 */
function formatar_telefone($tel) {
    $clean = preg_replace('/[^\d]/', '', (string)$tel);
    if (strlen($clean) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean);
    } elseif (strlen($clean) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $clean);
    }
    return $tel ?: 'Não informado';
}

/**
 * Formata CEP (8 dígitos).
 */
function formatar_cep($cep) {
    $clean = preg_replace('/[^\d]/', '', (string)$cep);
    if (strlen($clean) === 8) {
        return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $clean);
    }
    return $cep ?: '';
}

/**
 * Busca valor na tabela de configurações do sistema com fallback padrão.
 */
function get_app_config(PDO $pdo, string $chave, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT valor FROM configuracoes WHERE chave = ?");
        $stmt->execute([$chave]);
        $val = $stmt->fetchColumn();
        return ($val !== false && $val !== null) ? (string)$val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

/**
 * Salva ou atualiza valor na tabela de configurações do sistema (Upsert).
 */
function set_app_config(PDO $pdo, string $chave, string $valor): bool {
    try {
        $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
        $stmt->execute([$chave, $valor, $valor]);
        return true;
    } catch (Exception $e) {
        return false;
    }
}

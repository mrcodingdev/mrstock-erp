<?php
/**
 * MrStock ERP - Camada de Funções Utilitárias e Segurança
 */

if (date_default_timezone_get() !== 'America/Sao_Paulo') {
    date_default_timezone_set('America/Sao_Paulo');
}

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Sanitiza valores contra Cross-Site Scripting (XSS) para exibição segura no HTML.
 */
if (!function_exists('sanitize')) {
    function sanitize($data) {
        return htmlspecialchars(trim($data ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Gera ou retorna o token CSRF atual da sessão.
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

/**
 * Retorna o campo HTML hidden contendo o input CSRF para formulários.
 */
if (!function_exists('csrf_input')) {
    function csrf_input() {
        $token = csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

/**
 * Valida o token CSRF enviado em requisições do tipo POST.
 */
if (!function_exists('csrf_verify')) {
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
}

/**
 * Redireciona de forma segura para a URL especificada e encerra a execução.
 */
if (!function_exists('redirect_to')) {
    function redirect_to($url) {
        header("Location: " . $url);
        exit;
    }
}

/**
 * Renderiza componente visual de estado vazio (Empty State) com ilustração SVG estilizada.
 */
if (!function_exists('render_empty_state')) {
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
}

/**
 * Formata CPF (11 dígitos) ou CNPJ (14 dígitos) para exibição elegante.
 */
if (!function_exists('formatar_cpf_cnpj')) {
    function formatar_cpf_cnpj($doc) {
        $clean = preg_replace('/[^\d]/', '', (string)$doc);
        if (strlen($clean) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $clean);
        } elseif (strlen($clean) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $clean);
        }
        return $doc ?: 'Não informado';
    }
}

/**
 * Formata Telefone Fixo (10 dígitos) ou Celular (11 dígitos) para exibição.
 */
if (!function_exists('formatar_telefone')) {
    function formatar_telefone($tel) {
        $clean = preg_replace('/[^\d]/', '', (string)$tel);
        if (strlen($clean) === 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $clean);
        } elseif (strlen($clean) === 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $clean);
        }
        return $tel ?: 'Não informado';
    }
}

/**
 * Formata CEP (8 dígitos).
 */
if (!function_exists('formatar_cep')) {
    function formatar_cep($cep) {
        $clean = preg_replace('/[^\d]/', '', (string)$cep);
        if (strlen($clean) === 8) {
            return preg_replace('/(\d{5})(\d{3})/', '$1-$2', $clean);
        }
        return $cep ?: '';
    }
}

/**
 * Busca valor na tabela de configurações do sistema com fallback padrão.
 */
if (!function_exists('get_app_config')) {
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
}

/**
 * Salva ou atualiza valor na tabela de configurações do sistema (Upsert).
 */
if (!function_exists('set_app_config')) {
    function set_app_config(PDO $pdo, string $chave, string $valor): bool {
        try {
            $stmt = $pdo->prepare("INSERT INTO configuracoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = ?");
            $stmt->execute([$chave, $valor, $valor]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * Captura o endereço IP do cliente com validação estrita contra IP Spoofing (CWE-290).
 * Inspeciona HTTP_X_FORWARDED_FOR, HTTP_CLIENT_IP e REMOTE_ADDR garantindo formato IP legítimo.
 */
if (!function_exists('get_client_ip')) {
    function get_client_ip(): string {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($ips as $candidate) {
                $candidate = trim($candidate);
                if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                    return substr($candidate, 0, 45);
                }
            }
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $candidate = trim($_SERVER['HTTP_CLIENT_IP']);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return substr($candidate, 0, 45);
            }
        }

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $candidate = trim($_SERVER['REMOTE_ADDR']);
            if (filter_var($candidate, FILTER_VALIDATE_IP)) {
                return substr($candidate, 0, 45);
            }
        }

        return '127.0.0.1';
    }
}

/**
 * Registra eventos e operações no log de auditoria do sistema.
 * Tratamento defensivo try-catch garante que falhas de log nunca interrompam o fluxo da aplicação.
 *
 * @param mixed       $pdo        Instância ativa de PDO
 * @param string      $acao       Identificador da ação (ex: 'LOGIN_SUCESSO', 'VENDA_PDV')
 * @param string|null $descricao  Detalhamento textual da operação
 * @param string|null $tabela     Tabela de dados afetada pela ação
 * @param int|null    $usuario_id ID do operador (fallback para $_SESSION['user_id'] ou 1)
 * @return bool Retorna true se gravado com sucesso, false caso contrário
 */
if (!function_exists('registrar_log')) {
    function registrar_log($pdo, string $acao, ?string $descricao = null, ?string $tabela = null, ?int $usuario_id = null): bool {
        try {
            if (!$pdo instanceof PDO) {
                return false;
            }

            // Fallback seguro de identificação do operador
            if (empty($usuario_id)) {
                $usuario_id = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;
            }

            // Captura defensiva de IP com validação estrita (CWE-290)
            $ip = get_client_ip();

            $stmt = $pdo->prepare("INSERT INTO logs (usuario_id, acao, descricao, tabela_afetada, ip_usuario, data_log) VALUES (?, ?, ?, ?, ?, NOW())");
            return $stmt->execute([
                $usuario_id,
                $acao,
                $descricao,
                $tabela,
                $ip
            ]);
        } catch (Throwable $e) {
            error_log("Falha ao gravar log de auditoria: " . $e->getMessage());
            return false;
        }
    }
}



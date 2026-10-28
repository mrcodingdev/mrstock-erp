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

/**
 * Obtém metadados de versão e commit do Git (com detecção dinâmica de .git/HEAD
 * e fallback resiliente para version.json e constantes do sistema).
 *
 * @return array{version: string, edition: string, commit: string, branch: string, date: string, repo_url: string, full_commit?: string}
 */
if (!function_exists('mrstock_get_git_info')) {
    function mrstock_get_git_info(): array {
        $defaults = [
            'version'  => defined('MRSTOCK_VERSION') ? MRSTOCK_VERSION : 'v2.2.0',
            'edition'  => defined('MRSTOCK_EDITION') ? MRSTOCK_EDITION : 'Papelaria Real',
            'commit'   => '712b0d1',
            'branch'   => 'main',
            'date'     => defined('MRSTOCK_BUILD_DATE') ? MRSTOCK_BUILD_DATE : '21/09/2026',
            'repo_url' => 'https://github.com/mrcodingdev/mrstock-erp',
        ];

        // 1. Tentar ler version.json se existir
        $baseDir = defined('ROOT_PATH') ? ROOT_PATH : realpath(__DIR__ . '/..');
        $jsonFile = $baseDir . '/version.json';
        if (file_exists($jsonFile)) {
            $jsonContent = @file_get_contents($jsonFile);
            if ($jsonContent !== false) {
                $decoded = json_decode($jsonContent, true);
                if (is_array($decoded)) {
                    $defaults = array_merge($defaults, array_filter($decoded, function($v) {
                        return $v !== null && $v !== '';
                    }));
                }
            }
        }

        // 2. Tentar ler dinamicamente do repositório .git local
        $gitDir = $baseDir . '/.git';
        $headFile = $gitDir . '/HEAD';

        if (file_exists($headFile) && is_readable($headFile)) {
            try {
                $headContent = trim((string)@file_get_contents($headFile));

                if (strpos($headContent, 'ref:') === 0) {
                    $ref = trim(substr($headContent, 4));
                    // Extrai branch (ex: refs/heads/main -> main)
                    $parts = explode('/', $ref);
                    $defaults['branch'] = end($parts);

                    $refFile = $gitDir . '/' . $ref;
                    if (file_exists($refFile) && is_readable($refFile)) {
                        $fullHash = trim((string)@file_get_contents($refFile));
                        if (!empty($fullHash)) {
                            $defaults['commit'] = substr($fullHash, 0, 7);
                            $defaults['full_commit'] = $fullHash;
                        }
                    } else {
                        // Tenta packed-refs se o arquivo do ref avulso não existir
                        $packedFile = $gitDir . '/packed-refs';
                        if (file_exists($packedFile) && is_readable($packedFile)) {
                            $lines = @file($packedFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            if ($lines !== false) {
                                foreach ($lines as $line) {
                                    $line = trim($line);
                                    if ($line === '' || strpos($line, '#') === 0 || strpos($line, '^') === 0) {
                                        continue;
                                    }
                                    $tokens = preg_split('/\s+/', $line, 2);
                                    if (count($tokens) === 2 && $tokens[1] === $ref) {
                                        $defaults['commit'] = substr($tokens[0], 0, 7);
                                        $defaults['full_commit'] = $tokens[0];
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } elseif (preg_match('/^[0-9a-f]{40}$/i', $headContent)) {
                    // HEAD desanexado com hash direto
                    $defaults['commit'] = substr($headContent, 0, 7);
                    $defaults['full_commit'] = $headContent;
                }
            } catch (Throwable $e) {
                // Fallback silencioso para version.json / constantes
            }
        }

        return $defaults;
    }
}

/**
 * Renderiza o ícone vetorial SVG oficial da marca PIX (Banco Central do Brasil).
 */
if (!function_exists('pix_icon_svg')) {
    function pix_icon_svg(int $width = 14, int $height = 14, string $color = '#32BCAD'): string {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="' . $width . '" height="' . $height . '" class="icon-pix" style="vertical-align: -0.15em; display: inline-block;" aria-hidden="true"><path fill="' . htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . '" fill-rule="evenodd" d="M112.57 391.19c20.056 0 38.928-7.808 53.12-22l76.693-76.692c5.385-5.404 14.765-5.384 20.15 0l76.989 76.989c14.191 14.172 33.045 21.98 53.12 21.98h15.098l-97.138 97.139c-30.326 30.344-79.505 30.344-109.85 0l-97.415-97.416h9.232zm280.068-271.294c-20.056 0-38.929 7.809-53.12 22l-76.97 76.99c-5.551 5.53-14.6 5.568-20.15-.02l-76.711-76.693c-14.192-14.191-33.046-21.999-53.12-21.999h-9.234l97.416-97.416c30.344-30.344 79.523-30.344 109.867 0l97.138 97.138h-15.116zM22.758 200.753l58.024-58.024h31.787c13.84 0 27.384 5.605 37.172 15.394l76.694 76.693c7.178 7.179 16.596 10.768 26.033 10.768 9.417 0 18.854-3.59 26.014-10.75l76.989-76.99c9.787-9.787 23.331-15.393 37.171-15.393h37.654l58.3 58.302c30.343 30.344 30.343 79.523 0 109.867l-58.3 58.303H392.64c-13.84 0-27.384-5.605-37.171-15.394l-76.97-76.99c-13.914-13.894-38.172-13.894-52.066.02l-76.694 76.674c-9.788 9.788-23.332 15.413-37.172 15.413H80.782L22.758 310.62c-30.344-30.345-30.344-79.524 0-109.868"/></svg>';
    }
}

/**
 * Renderiza forma de pagamento em texto limpo com ícone contextual oficial.
 */
if (!function_exists('render_forma_pagamento')) {
    function render_forma_pagamento(string $forma): string {
        $formaUpper = mb_strtoupper(trim($forma), 'UTF-8');
        if (strpos($formaUpper, 'DINHEIRO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-money-bill-wave text-success"></i><span>Dinheiro</span></span>';
        } elseif (strpos($formaUpper, 'PIX') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-2">' . pix_icon_svg(14, 14) . '<span>PIX</span></span>';
        } elseif (strpos($formaUpper, 'CRÉDITO') !== false || strpos($formaUpper, 'CREDITO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-credit-card text-primary"></i><span>Cartão de Crédito</span></span>';
        } elseif (strpos($formaUpper, 'DÉBITO') !== false || strpos($formaUpper, 'DEBITO') !== false) {
            return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-credit-card text-info"></i><span>Cartão de Débito</span></span>';
        }
        return '<span class="text-dark d-inline-flex align-items-center gap-2"><i class="fas fa-wallet text-secondary"></i><span>' . htmlspecialchars($forma, ENT_QUOTES, 'UTF-8') . '</span></span>';
    }
}

/**
 * Alias de compatibilidade para render_forma_pagamento.
 */
if (!function_exists('render_forma_pagamento_badge')) {
    function render_forma_pagamento_badge(string $forma): string {
        return render_forma_pagamento($forma);
    }
}





<?php
/**
 * MrStock ERP - Módulo de Rate Limiting & Proteção contra Brute Force
 * Controla tentativas consecutivas de autenticação por IP e usuário.
 * Mitiga ataques automatizados de força bruta e credential stuffing.
 */

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', realpath(__DIR__ . '/..'));
}

require_once ROOT_PATH . '/inc/functions.php';

/**
 * Criação idempotente da tabela de controle de tentativas de login.
 * Executa uma única vez por ciclo de execução.
 *
 * @param PDO $pdo
 * @return void
 */
if (!function_exists('garantir_tabela_rate_limit')) {
    function garantir_tabela_rate_limit(PDO $pdo): void {
        static $verificado = false;
        if ($verificado) {
            return;
        }

        try {
            $sql = "CREATE TABLE IF NOT EXISTS login_tentativas (
                id INT AUTO_INCREMENT PRIMARY KEY,
                ip_usuario VARCHAR(45) NOT NULL,
                username VARCHAR(100) NOT NULL,
                data_tentativa DATETIME NOT NULL,
                INDEX idx_ip_data (ip_usuario, data_tentativa),
                INDEX idx_user_data (username, data_tentativa)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            $pdo->exec($sql);
            $verificado = true;
        } catch (Throwable $e) {
            error_log("Erro ao inicializar tabela login_tentativas: " . $e->getMessage());
        }
    }
}

/**
 * Verifica se um endereço IP excedeu o limite de tentativas na janela de tempo.
 * Fallback gracioso em caso de falha de banco de dados (não quebra a navegação).
 *
 * @param PDO    $pdo
 * @param string $ip
 * @param int    $maxTentativas Limite máximo de tentativas antes do bloqueio (padrão: 5)
 * @param int    $minutosJanela Janela de observação em minutos (padrão: 15)
 * @return array ['bloqueado' => bool, 'minutos_restantes' => int, 'tentativas_atuais' => int]
 */
if (!function_exists('check_rate_limit')) {
    function check_rate_limit(PDO $pdo, string $ip, int $maxTentativas = 5, int $minutosJanela = 15): array {
        garantir_tabela_rate_limit($pdo);

        $resultado = [
            'bloqueado'         => false,
            'minutos_restantes' => 0,
            'tentativas_atuais' => 0
        ];

        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS total, MIN(data_tentativa) AS primeira_tentativa
                FROM login_tentativas
                WHERE ip_usuario = ? AND data_tentativa >= (NOW() - INTERVAL ? MINUTE)
            ");
            $stmt->execute([$ip, $minutosJanela]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $tentativas = (int)($row['total'] ?? 0);
            $resultado['tentativas_atuais'] = $tentativas;

            if ($tentativas >= $maxTentativas) {
                $resultado['bloqueado'] = true;

                if (!empty($row['primeira_tentativa'])) {
                    $primeiroTimestamp = strtotime($row['primeira_tentativa']);
                    $expiracao = $primeiroTimestamp + ($minutosJanela * 60);
                    $segundosRestantes = max(0, $expiracao - time());
                    $minutosRestantes = (int)ceil($segundosRestantes / 60);
                    $resultado['minutos_restantes'] = max(1, $minutosRestantes);
                } else {
                    $resultado['minutos_restantes'] = $minutosJanela;
                }
            }
        } catch (Throwable $e) {
            error_log("Erro no rate limiter: " . $e->getMessage());
            return [
                'bloqueado'         => false,
                'minutos_restantes' => 0,
                'tentativas_atuais' => 0
            ];
        }

        return $resultado;
    }
}

/**
 * Registra uma tentativa de login falha no banco de dados.
 * Dispara logs de auditoria e alerta de Brute Force caso atinja o limite.
 *
 * @param PDO    $pdo
 * @param string $ip
 * @param string $username
 * @param int    $maxTentativas Limite para disparar o alerta de Brute Force (padrão: 5)
 * @param int    $minutosJanela Janela de observação em minutos (padrão: 15)
 * @return void
 */
if (!function_exists('registrar_tentativa_falha')) {
    function registrar_tentativa_falha(PDO $pdo, string $ip, string $username, int $maxTentativas = 5, int $minutosJanela = 15): void {
        garantir_tabela_rate_limit($pdo);

        try {
            // 1. Grava a tentativa na tabela login_tentativas
            $stmt = $pdo->prepare("INSERT INTO login_tentativas (ip_usuario, username, data_tentativa) VALUES (?, ?, NOW())");
            $stmt->execute([$ip, $username]);

            // 2. Dispara log de auditoria padrão para falha de login
            registrar_log(
                $pdo,
                'FALHA_LOGIN',
                "Tentativa de login rejeitada para o usuário '{$username}' a partir do IP {$ip}",
                'login_tentativas',
                1
            );

            // 3. Avalia se atingiu o limite para emissão de alerta de Brute Force
            $status = check_rate_limit($pdo, $ip, $maxTentativas, $minutosJanela);
            if ($status['bloqueado']) {
                registrar_log(
                    $pdo,
                    'ALERTA_BRUTE_FORCE',
                    "Bloqueio de Brute Force acionado para o IP {$ip}. Total de tentativas: {$status['tentativas_atuais']}/{$maxTentativas} na janela de {$minutosJanela}m (Usuário alvo: '{$username}')",
                    'login_tentativas',
                    1
                );
            }

            // 4. Limpeza periódica leve (probabilidade de 5% por falha)
            if (mt_rand(1, 20) === 1) {
                limpar_tentativas_expiradas($pdo, 60);
            }
        } catch (Throwable $e) {
            error_log("Erro ao registrar tentativa falha de login: " . $e->getMessage());
        }
    }
}

/**
 * Limpa o histórico de tentativas do IP e do usuário após login bem-sucedido.
 *
 * @param PDO    $pdo
 * @param string $ip
 * @param string $username
 * @return void
 */
if (!function_exists('limpar_tentativas_ip')) {
    function limpar_tentativas_ip(PDO $pdo, string $ip, string $username): void {
        garantir_tabela_rate_limit($pdo);

        try {
            $stmt = $pdo->prepare("DELETE FROM login_tentativas WHERE ip_usuario = ? OR (username = ? AND username != '')");
            $stmt->execute([$ip, $username]);
        } catch (Throwable $e) {
            error_log("Erro ao limpar tentativas de login: " . $e->getMessage());
        }
    }
}

/**
 * Remove tentativas antigas fora da janela de retenção para otimização da tabela.
 *
 * @param PDO $pdo
 * @param int $minutosJanela Retenção padrão de 60 minutos
 * @return void
 */
if (!function_exists('limpar_tentativas_expiradas')) {
    function limpar_tentativas_expiradas(PDO $pdo, int $minutosJanela = 60): void {
        garantir_tabela_rate_limit($pdo);

        try {
            $stmt = $pdo->prepare("DELETE FROM login_tentativas WHERE data_tentativa < (NOW() - INTERVAL ? MINUTE)");
            $stmt->execute([$minutosJanela]);
        } catch (Throwable $e) {
            error_log("Erro ao limpar tentativas expiradas: " . $e->getMessage());
        }
    }
}

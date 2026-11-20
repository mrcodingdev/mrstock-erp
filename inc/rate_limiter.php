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
 * Verifica a janela agregada por IP e, quando informado, por IP e usuário.
 *
 * @param PDO    $pdo
 * @param string $ip
 * @param int|null $maxTentativas Limite agregado opcional (padrão de ambiente: 5)
 * @param int    $minutosJanela Janela de observação em minutos (padrão: 15)
 * @return array ['bloqueado' => bool, 'minutos_restantes' => int, 'tentativas_atuais' => int]
 */
if (!function_exists('check_rate_limit')) {
    function check_rate_limit(PDO $pdo, string $ip, ?int $maxTentativas = null, int $minutosJanela = 15, ?string $username = null): array {
        garantir_tabela_rate_limit($pdo);

        $valorAmbiente = $_ENV['MRSTOCK_LOGIN_IP_MAX_ATTEMPTS'] ?? $_SERVER['MRSTOCK_LOGIN_IP_MAX_ATTEMPTS']
            ?? getenv('MRSTOCK_LOGIN_IP_MAX_ATTEMPTS');
        $maxTentativas ??= filter_var($valorAmbiente, FILTER_VALIDATE_INT, ['options' => ['min_range' => 5]]) ?: 5;

        $resultado = [
            'bloqueado'         => false,
            'minutos_restantes' => 0,
            'tentativas_atuais' => 0
        ];

        try {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS total_ip, MIN(data_tentativa) AS primeira_ip,
                       SUM(CASE WHEN username = ? THEN 1 ELSE 0 END) AS total_par,
                       MIN(CASE WHEN username = ? THEN data_tentativa END) AS primeira_par
                FROM login_tentativas
                WHERE ip_usuario = ? AND data_tentativa >= (NOW() - INTERVAL ? MINUTE)
            ");
            $chaveUsuario = login_tentativas_chave_usuario($username ?? '');
            $stmt->execute([$chaveUsuario, $chaveUsuario, $ip, $minutosJanela]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            $resultado['tentativas_atuais'] = (int)($row['total_ip'] ?? 0);
            $bloqueios = [];
            if ($resultado['tentativas_atuais'] >= $maxTentativas) {
                $bloqueios[] = $row['primeira_ip'];
            }
            if ($username !== null && (int)($row['total_par'] ?? 0) >= 5) {
                $bloqueios[] = $row['primeira_par'];
            }
            foreach ($bloqueios as $primeiraTentativa) {
                $primeiroTimestamp = $primeiraTentativa ? strtotime($primeiraTentativa) : false;
                $minutos = $minutosJanela;
                if ($primeiroTimestamp !== false) {
                    $segundos = $primeiroTimestamp + $minutosJanela * 60 - time();
                    $minutos = min($minutosJanela, max(1, (int)ceil($segundos / 60)));
                }
                $resultado['bloqueado'] = true;
                $resultado['minutos_restantes'] = max($resultado['minutos_restantes'], $minutos);
            }
        } catch (Throwable $e) {
            error_log('Falha na consulta ao rate limiter. Autenticação liberada pela política de contingência.');
            return [
                'bloqueado'         => false,
                'minutos_restantes' => 0,
                'tentativas_atuais' => 0
            ];
        }

        return $resultado;
    }
}

function login_tentativas_chave_usuario(string $username): string {
    // Registra também nomes inválidos ou acima do limite da coluna.
    return preg_match('/^.{0,100}$/usD', $username) === 1 ? $username : '#' . hash('sha256', $username);
}

/**
 * Registra uma tentativa de login falha no banco de dados.
 * Dispara logs de auditoria e alerta de Brute Force caso atinja o limite.
 *
 * @param PDO    $pdo
 * @param string $ip
 * @param string $username
 * @param int|null $maxTentativas Limite agregado opcional
 * @param int    $minutosJanela Janela de observação em minutos (padrão: 15)
 * @return void
 */
if (!function_exists('registrar_tentativa_falha')) {
    function registrar_tentativa_falha(PDO $pdo, string $ip, string $username, ?int $maxTentativas = null, int $minutosJanela = 15): void {
        garantir_tabela_rate_limit($pdo);

        try {
            // 1. Grava a tentativa na tabela login_tentativas
            $stmt = $pdo->prepare("INSERT INTO login_tentativas (ip_usuario, username, data_tentativa) VALUES (?, ?, NOW())");
            $stmt->execute([$ip, login_tentativas_chave_usuario($username)]);

            // 2. Dispara log de auditoria padrão para falha de login
            registrar_log(
                $pdo,
                'FALHA_LOGIN',
                "Tentativa de login rejeitada para o usuário '{$username}' a partir do IP {$ip}",
                'login_tentativas',
                1
            );

            // 3. Avalia se atingiu o limite para emissão de alerta de Brute Force
            $status = check_rate_limit($pdo, $ip, $maxTentativas, $minutosJanela, $username);
            if ($status['bloqueado']) {
                registrar_log(
                    $pdo,
                    'ALERTA_BRUTE_FORCE',
                    "Bloqueio do rate limiter para o IP {$ip}. Tentativas no IP: {$status['tentativas_atuais']} na janela de {$minutosJanela} min.",
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
 * Limpa as falhas do par autenticado, preservando as tentativas dos demais pares.
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
            $stmt = $pdo->prepare("DELETE FROM login_tentativas WHERE ip_usuario = ? AND username = ?");
            $stmt->execute([$ip, login_tentativas_chave_usuario($username)]);
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

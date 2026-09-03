<?php
require_once __DIR__ . '/inc/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . (($_SESSION['user_perfil'] ?? '') == 'caixa' ? "/vendas/pdv.php" : "/dashboard.php"));
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    csrf_verify(); // Proteção contra ataques CSRF

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user) {
        $autenticado = false;
        
        if (password_verify($password, $user['password'])) {
            $autenticado = true;
        } elseif ($user['password'] === $password) {
            // Senha legado em texto plano coincide: migra automaticamente para hash Bcrypt
            $novoHash = password_hash($password, PASSWORD_DEFAULT);
            $stmtUpd = $pdo->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmtUpd->execute([$novoHash, $user['id']]);
            $autenticado = true;
        }

        if ($autenticado) {
            session_regenerate_id(true); // Proteção contra Session Fixation
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_name']     = $user['username'];
            $_SESSION['username']      = $user['username'];
            $_SESSION['user_perfil']   = $user['perfil'];
            $_SESSION['usuario_nivel'] = $user['perfil'];
            $_SESSION['perfil']        = $user['perfil'];

            registrar_log($pdo, 'LOGIN_SUCESSO', "Usuário {$user['username']} ({$user['perfil']}) autenticou-se no sistema", 'usuarios', (int)$user['id']);

            header("Location: " . BASE_URL . ($user['perfil'] == 'caixa' ? "/vendas/pdv.php" : "/dashboard.php"));
            exit;
        } else {
            $erro = "Credenciais inválidas. Tente novamente.";
            $uidFallback = ($user && !empty($user['id'])) ? (int)$user['id'] : 1;
            registrar_log($pdo, 'FALHA_LOGIN', "Tentativa de login rejeitada para o usuário '$username' (Senha incorreta ou inexistente)", 'usuarios', $uidFallback);
        }
    } else {
        $erro = "Credenciais inválidas. Tente novamente.";
        $uidFallback = ($user && !empty($user['id'])) ? (int)$user['id'] : 1;
        registrar_log($pdo, 'FALHA_LOGIN', "Tentativa de login rejeitada para o usuário '$username' (Senha incorreta ou inexistente)", 'usuarios', $uidFallback);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MrStock ERP - Login</title>
    <link rel="preload" href="<?= BASE_URL ?>/webfonts/inter_font_1.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= BASE_URL ?>/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
    <link href="<?= BASE_URL ?>/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/inter.css">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/mr_stock_logo_branca.ico" type="image/x-icon">
    <style>
        :root {
            --brand-primary: #1a4231;
            --brand-secondary: #2c6e53;
            --brand-accent: #39e07b;
            --bg-color: #e8f3ee;
            --text-dark: #2d3748;
            --text-muted: #475569; /* Contraste Slate-600: 7.0:1 contra branco (WCAG AAA Compliance) */
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            width: 100%;
        }

        /* Fundo Animado com Formas Geométricas */
        .bg-shapes {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }
        .shape {
            position: absolute;
            background: linear-gradient(135deg, rgba(44,110,83,0.12), rgba(26,66,49,0.03));
            border-radius: 50%;
            animation: float 25s infinite ease-in-out alternate;
        }
        .shape-1 { width: 900px; height: 900px; top: -300px; left: -250px; animation-delay: 0s; }
        .shape-2 { width: 800px; height: 800px; bottom: -250px; right: -200px; animation-delay: -5s; background: linear-gradient(135deg, rgba(44,110,83,0.08), rgba(26,66,49,0.02)); }
        .shape-3 { width: 500px; height: 500px; top: 15%; left: 55%; animation-delay: -10s; }
        .shape-4 { 
            width: 350px; height: 350px; bottom: 5%; left: 2%; 
            animation-delay: -15s; border-radius: 40px; transform: rotate(45deg);
            border: 5px solid rgba(44,110,83,0.08); background: transparent;
        }
        .shape-5 {
            width: 250px; height: 250px; top: 5%; right: 25%;
            animation-delay: -2s; border-radius: 30px;
            border: 4px solid rgba(44,110,83,0.1); background: transparent; transform: rotate(15deg);
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50px, 50px) rotate(30deg); }
        }

        /* Card de Login */
        .login-card {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 520px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.2);
            z-index: 1;
            overflow: hidden;
            margin: auto;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Lado Esquerdo */
        .login-left {
            flex: 1.1;
            background: linear-gradient(145deg, var(--brand-secondary), var(--brand-primary));
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            color: #ffffff;
            position: relative;
        }
        .login-left::before {
            content: ''; position: absolute; top: -60px; left: -60px; width: 250px; height: 250px; border-radius: 50%; border: 35px solid rgba(255,255,255,0.06); pointer-events: none;
        }
        .login-left::after {
            content: ''; position: absolute; bottom: -80px; right: -50px; width: 300px; height: 300px; border-radius: 50%; background: rgba(255,255,255,0.03); pointer-events: none;
        }
        
        .left-logo {
            font-size: 1.3rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 12px;
            position: absolute;
            top: 40px;
            left: 45px;
            letter-spacing: -0.5px;
        }
        .left-logo i { font-size: 1.8rem; color: var(--brand-accent); }
        
        .left-content { 
            z-index: 2; 
            margin-top: 40px;
        }
        .left-content h1 {
            font-size: 3.5rem;
            font-weight: 800;
            margin-bottom: 15px;
            line-height: 1.1;
            letter-spacing: -1.5px;
        }
        .left-content p {
            font-size: 1.1rem;
            color: #f1f5f9; /* Branco suave de alto contraste contra o fundo verde */
            line-height: 1.6;
            max-width: 90%;
        }

        /* Lado Direito */
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }
        
        .right-header {
            margin-bottom: 40px;
        }
        .right-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .right-header p {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin: 0;
        }

        .form-group {
            margin-bottom: 24px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }
        
        /* Ajuste do wrapper para o ícone alinhar perfeitamente com o input */
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-wrapper i {
            position: absolute;
            left: 18px;
            color: #a0aec0;
            font-size: 1.2rem;
            transition: color 0.3s ease;
            pointer-events: none;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 15px 16px 15px 50px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1.05rem;
            color: var(--text-dark);
            transition: all 0.3s ease;
            outline: none;
            background: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        .form-control-custom:focus {
            border-color: var(--brand-secondary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(44,110,83,0.1);
        }
        .form-control-custom:focus + i, 
        .form-control-custom:not(:placeholder-shown) + i {
            color: var(--brand-secondary);
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: var(--brand-primary);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 12px rgba(26,66,49,0.2);
            font-family: 'Inter', sans-serif;
        }
        .btn-login:hover {
            background: var(--brand-secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(26,66,49,0.3);
        }
        .btn-login:active {
            transform: translateY(0);
        }

        .error-msg {
            background: #fff5f5;
            color: #e53e3e;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid #f56565;
            margin-bottom: 25px;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            html, body {
                padding: 14px;
                align-items: center;
            }
            .login-card {
                flex-direction: column;
                width: 100%;
                max-width: 440px;
                min-height: auto;
                margin: auto;
                border-radius: 14px;
            }
            .login-left {
                padding: 28px 20px;
                text-align: center;
                align-items: center;
            }
            .left-logo {
                position: static;
                margin-bottom: 12px;
                justify-content: center;
                font-size: 1.2rem;
            }
            .left-content {
                margin-top: 0;
                text-align: center;
            }
            .left-content h1 {
                font-size: 1.6rem;
                margin-bottom: 6px;
                letter-spacing: -0.5px;
            }
            .left-content p {
                font-size: 0.875rem;
                max-width: 100%;
                margin: 0 auto;
                line-height: 1.4;
                opacity: 0.95;
            }
            .login-right {
                padding: 28px 20px;
            }
            .right-header {
                margin-bottom: 20px;
                text-align: center;
            }
            .right-header h2 {
                font-size: 1.4rem;
                margin-bottom: 4px;
            }
            .right-header p {
                font-size: 0.875rem;
            }
            .form-group {
                margin-bottom: 16px;
            }
            .form-control-custom {
                padding: 12px 14px 12px 44px;
                font-size: 0.95rem;
            }
            .input-wrapper i {
                left: 15px;
                font-size: 1rem;
            }
            .btn-login {
                padding: 13px;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            html, body {
                padding: 10px;
            }
            .login-card {
                max-width: 100%;
            }
            .login-left {
                padding: 22px 16px;
            }
            .login-right {
                padding: 22px 16px;
            }
        }
    </style>
</head>
<body>

    <!-- Background Animado -->
    <div class="bg-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
        <div class="shape shape-4"></div>
        <div class="shape shape-5"></div>
    </div>

    <!-- Container Principal -->
    <main class="login-card" role="main">
        <!-- Lado Esquerdo -->
        <div class="login-left">
            <div class="left-logo">
                <img src="<?= BASE_URL ?>/assets/img/logo-mrstock.svg" alt="MrStock Logo" width="35" height="35" style="height:35px; width:35px; margin-right:5px; margin-bottom: 4px;"> MrStock
            </div>
            <div class="left-content">
                <h1>Olá,<br>bem-vindo!</h1>
                <p>O sistema perfeito para organizar o estoque e as vendas da sua papelaria com muita praticidade.</p>
            </div>
        </div>

        <!-- Lado Direito -->
        <div class="login-right">
            <div class="right-header">
                <h2>Bem-vindo</h2>
                <p>Faça o login com a sua conta.</p>
            </div>

            <?php if ($erro): ?>
                <div class="error-msg">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/login.php" method="POST" autocomplete="off">
                <?= csrf_input() ?>
                <div class="form-group">
                    <label for="username">Usuário</label>
                    <div class="input-wrapper">
                        <input type="text" id="username" name="username" class="form-control-custom" placeholder="Digite seu usuário" required autofocus>
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Senha</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control-custom" placeholder="Digite sua senha" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    Entrar <i class="fas fa-arrow-right"></i>
                </button>
            </form>
        </div>
    </main>

</body>
</html>

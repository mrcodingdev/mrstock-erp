# 🔐 Módulo: Autenticação, Sessão & Logout
**Arquivos Principais:** `login.php`, `logout.php`, `inc/auth.php`  
**Escopo de Acesso:** Público / Usuários Autenticados

---

## 1. Objetivo & Contexto de Negócio
Gerencia a porta de entrada segura do MrStock ERP. Implementa os mais rigorosos padrões de segurança cibernética (OWASP Top 10), garantindo a autenticidade de operadores de caixa e administradores, isolamento de sessão e proteção contra ataques de força bruta.

---

## 2. Interface & Componentes Visuais
- **Design Split-Screen Corporativo:** Lado esquerdo com gradiente institucional verde Papelaria Real (`#1a4231`), formas geométricas animadas e logotipo; lado direito com formulário limpo.
- **Formulário Acessível:** Inputs com ícones temáticos, foco visível de alto contraste e botão de login 100% sólido.
- **Feedback de Erro Seguro:** Mensagens genéricas de falha ("Usuário ou senha incorretos"), prevenindo enumeração de contas.

---

## 3. Detalhamento Linha por Linha das Funções & Backend

### 3.1 Validação de Credenciais com BCrypt e Regeneração de Sessão
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $username = clean_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT id, username, password, perfil FROM usuarios WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Regeneração atômica de ID de sessão (mitiga Session Fixation)
        session_regenerate_id(true);
        
        $_SESSION['usuario_id']   = (int)$user['id'];
        $_SESSION['usuario_nome'] = $user['username'];
        $_SESSION['usuario_perfil'] = $user['perfil'];
        
        // Log de autenticação
        log_sistema($pdo, $user['id'], "Login", "Autenticação bem-sucedida", "usuarios");
        
        // Redirecionamento inteligente por perfil
        if ($user['perfil'] === 'caixa') {
            redirect('vendas/pdv.php');
        } else {
            redirect('dashboard.php');
        }
    } else {
        set_flash("Usuário ou senha inválidos.", "danger");
    }
}
```

---

## 4. Segurança & Controle de Acesso (RBAC)
- **Cookies Seguros:** `session_set_cookie_params()` configurado com `HttpOnly`, `SameSite=Lax` e `use_strict_mode`.
- **Destruição Completa no Logout (`logout.php`):** `session_unset()`, `session_destroy()`, expiração de cookies e headers `no-cache, no-store`.

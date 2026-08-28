# Módulo de Autenticação, Login & Hardening de Sessão

**Arquivos:** `login.php`, `logout.php`, `config.php`, `inc/auth.php`  
**Acesso:** Público (Não autenticado)  
**Objetivo:** Prover uma barreira de autenticação segura, protegida contra força bruta, sequestro de sessão (*Session Hijacking*) e ataques CSRF/XSS.

---

## 1. Arquitetura de Segurança do Login

```mermaid
flowchart TD
    A[Acesso a login.php] --> B[Gera Token CSRF na Sessão]
    B --> C[Operador envia Usuário e Senha via POST]
    C --> D{Valida CSRF Token?}
    D -- Não --> E[Retorna HTTP 403 Forbidden]
    D -- Sim --> F[Busca Usuário no Banco via PDO Prepared Statement]
    F --> G{password_verify(senha, hash)?}
    G -- Não --> H[Exibe Alerta de Credenciais Inválidas]
    G -- Sim --> I[session_regenerate_id(true)]
    I --> J[Grava user_id e perfil na Sessão]
    J --> K{Perfil == admin?}
    K -- Sim --> L[Redireciona para dashboard.php]
    K -- Não --> M[Redireciona para vendas/pdv.php]
```

---

## 2. Medidas de Hardening Implementadas

1. **Bcrypt Cost 12:** Senhas armazenadas com salt dinâmico e alto custo computacional.
2. **Regeneração de ID de Sessão:** `session_regenerate_id(true)` impede ataques de fixação de sessão.
3. **Cookies Defensivos:** Parâmetros `HttpOnly` e `SameSite=Lax` previnem leitura via JavaScript ou envio cruzado indevido.
4. **Logout Seguro (`logout.php`):** Limpa o array `$_SESSION`, destrói os cookies no navegador e encerra a sessão no servidor.
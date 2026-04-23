<?php
declare(strict_types=1);

function auth_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $secure = !empty($_SERVER['HTTPS']);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    session_name('SYSPOLOSSID');
    @session_start();
}

function auth_user(): ?array
{
    auth_start_session();
    return $_SESSION['user'] ?? null;
}

function auth_require(): array
{
    $u = auth_user();
    if (!$u) {
        if (is_ajax()) json_err('Sessão expirada. Faça login novamente.', 401);
        header('Location: ?p=login');
        exit;
    }
    return $u;
}

function auth_login(string $email, string $senha): bool
{
    auth_start_session();
    $u = db_one("SELECT * FROM sys_usuarios WHERE email = :e AND ies_ativo='S'", [':e' => strtolower(trim($email))]);
    if (!$u || !password_verify($senha, $u['senha_hash'])) return false;

    db_exec("UPDATE sys_usuarios SET dat_ultimo_login = NOW() WHERE cod_usuario = :c", [':c' => $u['cod_usuario']]);

    $_SESSION['user'] = [
        'cod_usuario' => (int)$u['cod_usuario'],
        'email'       => $u['email'],
        'nome'        => $u['nome'],
    ];
    session_regenerate_id(true);
    return true;
}

function auth_logout(): void
{
    auth_start_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], (bool)$p['secure'], (bool)$p['httponly']);
    }
    session_destroy();
}

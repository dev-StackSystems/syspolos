<?php
declare(strict_types=1);

/**
 * Autenticação stateless via cookie assinado (HMAC-SHA256).
 *
 * Não usa sessão PHP — em ambiente serverless (Vercel) cada lambda
 * tem um /tmp diferente, então sessões padrão não persistem entre requests.
 * O cookie carrega o user (id/email/nome) + expiração + assinatura.
 *
 * Validade: 1 ano. Renovado a cada login. Só expira ao logout explícito
 * (limpa o cookie) ou expiração da data.
 */

const AUTH_COOKIE_NAME = 'SYSPOLOSAUTH';
const AUTH_COOKIE_TTL  = 31536000; // 1 ano em segundos

function auth_secret(): string
{
    static $s = null;
    if ($s !== null) return $s;
    $env = getenv('APP_SECRET');
    if ($env) { $s = $env; return $s; }
    // fallback estável derivado da DATABASE_URL (se ela mudar, cookies expiram)
    $s = hash('sha256', 'syspolos-auth-v1:' . (defined('DATABASE_URL') ? DATABASE_URL : ''));
    return $s;
}

function auth_cookie_set(array $user): void
{
    $payload = json_encode([
        'u' => (int)$user['cod_usuario'],
        'e' => $user['email'],
        'n' => $user['nome'],
        'x' => time() + AUTH_COOKIE_TTL,
    ], JSON_UNESCAPED_UNICODE);
    $payload64 = rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    $sig       = hash_hmac('sha256', $payload64, auth_secret());
    $value     = $payload64 . '.' . $sig;

    $secure = !empty($_SERVER['HTTPS']);
    setcookie(AUTH_COOKIE_NAME, $value, [
        'expires'  => time() + AUTH_COOKIE_TTL,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    // Disponibiliza imediatamente nesta request também
    $_COOKIE[AUTH_COOKIE_NAME] = $value;
}

function auth_cookie_clear(): void
{
    $secure = !empty($_SERVER['HTTPS']);
    setcookie(AUTH_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'secure'   => $secure,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[AUTH_COOKIE_NAME]);
}

function auth_user(): ?array
{
    static $cached = null;
    if ($cached !== null) return $cached === false ? null : $cached;

    $raw = $_COOKIE[AUTH_COOKIE_NAME] ?? '';
    if ($raw === '' || !str_contains($raw, '.')) { $cached = false; return null; }

    [$payload64, $sig] = explode('.', $raw, 2);
    $expected = hash_hmac('sha256', $payload64, auth_secret());
    if (!hash_equals($expected, $sig)) { $cached = false; return null; }

    $payload = base64_decode(strtr($payload64, '-_', '+/'), true);
    if ($payload === false) { $cached = false; return null; }

    $data = json_decode($payload, true);
    if (!is_array($data) || ($data['x'] ?? 0) < time()) { $cached = false; return null; }

    $cached = [
        'cod_usuario' => (int)$data['u'],
        'email'       => (string)$data['e'],
        'nome'        => (string)$data['n'],
    ];
    return $cached;
}

function auth_require(): array
{
    $u = auth_user();
    if (!$u) {
        if (function_exists('is_ajax') && is_ajax()) {
            json_err('Não autenticado.', 401);
        }
        header('Location: ?p=login');
        exit;
    }
    return $u;
}

function auth_login(string $email, string $senha): bool
{
    $u = db_one(
        "SELECT * FROM sys_usuarios WHERE email = :e AND ies_ativo='S'",
        [':e' => strtolower(trim($email))]
    );
    if (!$u || !password_verify($senha, $u['senha_hash'])) return false;

    db_exec("UPDATE sys_usuarios SET dat_ultimo_login = NOW() WHERE cod_usuario = :c", [':c' => $u['cod_usuario']]);

    auth_cookie_set([
        'cod_usuario' => (int)$u['cod_usuario'],
        'email'       => $u['email'],
        'nome'        => $u['nome'],
    ]);
    return true;
}

function auth_logout(): void
{
    auth_cookie_clear();
}

<?php
declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars($s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function p(array $src, string $k, $default = null): mixed
{
    $v = $src[$k] ?? $default;
    if (is_string($v)) $v = trim($v);
    return $v === '' ? $default : $v;
}

function p_int(array $src, string $k, int $default = 0): int
{
    return (int)($src[$k] ?? $default);
}

function json_ok(array $extra = []): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge(['ok' => 1], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function json_err(string $msg, int $code = 400): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

function fmt_date_br(?string $date): string
{
    if (!$date) return '';
    $t = strtotime($date);
    return $t ? date('d/m/Y', $t) : '';
}

function url(string $page, array $params = []): string
{
    return '?' . http_build_query(array_merge(['p' => $page], $params));
}

function flash_set(string $tipo, string $msg): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $_SESSION['_flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function flash_get(): ?array
{
    if (session_status() !== PHP_SESSION_ACTIVE) @session_start();
    $f = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $f;
}

function is_ajax(): bool
{
    return (!empty($_POST['ajax']))
        || (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest');
}

function turnos(): array
{
    return ['Manhã' => 'Manhã', 'Tarde' => 'Tarde', 'Noite' => 'Noite', 'Integral' => 'Integral'];
}

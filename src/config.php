<?php
declare(strict_types=1);

// Carrega .env em ambiente local (Vercel injeta via env vars reais)
$envFile = __DIR__ . '/../.env';
if (is_file($envFile)) {
    foreach (parse_ini_file($envFile, false, INI_SCANNER_RAW) as $k => $v) {
        if (getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

$databaseUrl = getenv('DATABASE_URL') ?: '';
if ($databaseUrl === '') {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    exit('<h1>Configuração pendente</h1><p>Defina a variável de ambiente <code>DATABASE_URL</code> (connection string do Neon).</p>');
}

define('DATABASE_URL', $databaseUrl);
define('APP_TZ', getenv('APP_TZ') ?: 'America/Fortaleza');
date_default_timezone_set(APP_TZ);

mb_internal_encoding('UTF-8');

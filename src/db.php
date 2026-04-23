<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $parts = parse_url(DATABASE_URL);
    if (!$parts || empty($parts['host'])) {
        throw new RuntimeException('DATABASE_URL inválida.');
    }
    $host = $parts['host'];
    $port = $parts['port'] ?? 5432;
    $user = urldecode($parts['user'] ?? '');
    $pass = urldecode($parts['pass'] ?? '');
    $db   = ltrim($parts['path'] ?? '', '/');
    parse_str($parts['query'] ?? '', $qs);
    $sslmode = $qs['sslmode'] ?? 'require';

    $dsn = "pgsql:host={$host};port={$port};dbname={$db};sslmode={$sslmode}";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
    return $pdo;
}

function db_all(string $sql, array $params = []): array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_one(string $sql, array $params = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $r = $st->fetch();
    return $r === false ? null : $r;
}

function db_val(string $sql, array $params = []): mixed
{
    $st = db()->prepare($sql);
    $st->execute($params);
    $r = $st->fetchColumn();
    return $r === false ? null : $r;
}

function db_exec(string $sql, array $params = []): int
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->rowCount();
}

function db_insert_returning(string $sql, array $params = [])
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st->fetchColumn();
}

<?php
$dburl = $argv[1];
$parts = parse_url($dburl);
$host = $parts['host']; $port = $parts['port'] ?? 5432;
$user = urldecode($parts['user']); $pass = urldecode($parts['pass']);
$db = ltrim($parts['path'], '/');
$endpoint = explode('.', $host)[0];
$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require;options=endpoint=$endpoint";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

echo "=== Usuários existentes ===\n";
foreach ($pdo->query("SELECT cod_usuario, email, nome, ies_ativo FROM usuarios ORDER BY cod_usuario")->fetchAll(PDO::FETCH_ASSOC) as $u) {
    echo "  #{$u['cod_usuario']} {$u['email']} | {$u['nome']} | ativo={$u['ies_ativo']}\n";
}

// garante admin@syspolos.com
$exists = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE email='admin@syspolos.com'")->fetchColumn();
if ($exists === 0) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("INSERT INTO usuarios (email, senha_hash, nome) VALUES (:e, :h, :n)")
        ->execute([':e'=>'admin@syspolos.com', ':h'=>$hash, ':n'=>'Administrador']);
    echo "\n>>> Admin criado: admin@syspolos.com / admin123\n";
} else {
    // reset da senha para garantir que você tem acesso
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $pdo->prepare("UPDATE usuarios SET senha_hash=:h, ies_ativo='S' WHERE email='admin@syspolos.com'")
        ->execute([':h'=>$hash]);
    echo "\n>>> Senha do admin@syspolos.com resetada para: admin123\n";
}

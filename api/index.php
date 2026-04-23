<?php
declare(strict_types=1);

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/auth.php';

auth_start_session();

$action = isset($_GET['a']) ? basename((string)$_GET['a']) : null;
$page   = isset($_GET['p']) ? basename((string)$_GET['p']) : 'home';

// Rotas públicas (não exigem login)
$publicas = ['login', 'login_entrar'];

$isPublic = ($action && in_array($action, $publicas, true)) || (!$action && in_array($page, $publicas, true));

if (!$isPublic && !auth_user()) {
    if ($action) {
        json_err('Sessão expirada. Faça login novamente.', 401);
    }
    header('Location: ?p=login');
    exit;
}

// Rotas de action: respondem JSON / fazem redirect
if ($action !== null && $action !== '') {
    $file = __DIR__ . '/../src/actions/' . $action . '.php';
    if (!is_file($file)) {
        json_err('Ação não encontrada: ' . $action, 404);
    }
    try {
        require $file;
    } catch (Throwable $e) {
        json_err('Erro: ' . $e->getMessage(), 500);
    }
    exit;
}

// Rotas de página: renderizam HTML
$file = __DIR__ . '/../src/pages/' . $page . '.php';
if (!is_file($file)) {
    http_response_code(404);
    $pageTitle = 'Não encontrada';
    require __DIR__ . '/../src/layout_header.php';
    echo '<div class="alert alert-danger">Página não encontrada: <code>' . e($page) . '</code></div>';
    echo '<a href="' . url('home') . '" class="btn btn-brand">Voltar ao painel</a>';
    require __DIR__ . '/../src/layout_footer.php';
    exit;
}

try {
    if ($page === 'login') {
        // login usa layout mínimo (sem navbar)
        ?><!doctype html><html lang="pt-BR"><head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1">
          <title>Entrar · Auditoria de Leitura</title>
          <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
          <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
          <style>
            :root{--brand-600:#4f46e5;--brand-700:#4338ca;--line:#e2e8f0;--ink:#0f172a;--muted:#64748b;}
            body{font-family:'Inter',system-ui,sans-serif; background:linear-gradient(135deg,#eef2ff 0%, #faf5ff 100%); color:var(--ink); min-height:100vh; margin:0}
            .form-control{border:1px solid var(--line); border-radius:8px; padding:.55rem .75rem; font-size:.92rem}
            .form-control:focus{border-color:var(--brand-600); box-shadow:0 0 0 3px rgba(79,70,229,.12); outline:0}
            .form-label{font-weight:500; font-size:.85rem; color:#334155; margin-bottom:.3rem}
            .btn-brand{background:var(--brand-600); color:#fff; border:1px solid var(--brand-600); font-weight:600; padding:.6rem 1rem; border-radius:8px; transition:all .15s ease}
            .btn-brand:hover{background:var(--brand-700); color:#fff; transform:translateY(-1px); box-shadow:0 4px 12px rgba(79,70,229,.35)}
            .alert{border-radius:8px}
          </style>
        </head><body>
        <?php
        require $file;
        ?></body></html><?php
    } else {
        require __DIR__ . '/../src/layout_header.php';
        require $file;
        require __DIR__ . '/../src/layout_footer.php';
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><strong>Erro:</strong> ' . e($e->getMessage()) . '</div>';
    echo '<pre class="bg-light p-2 small">' . e($e->getTraceAsString()) . '</pre>';
}

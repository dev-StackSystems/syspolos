<?php
declare(strict_types=1);

require __DIR__ . '/../src/config.php';
require __DIR__ . '/../src/db.php';
require __DIR__ . '/../src/helpers.php';

$action = isset($_GET['a']) ? basename((string)$_GET['a']) : null;
$page   = isset($_GET['p']) ? basename((string)$_GET['p']) : 'home';

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
    require __DIR__ . '/../src/layout_header.php';
    require $file;
    require __DIR__ . '/../src/layout_footer.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo '<div class="alert alert-danger"><strong>Erro:</strong> ' . e($e->getMessage()) . '</div>';
    echo '<pre class="bg-light p-2 small">' . e($e->getTraceAsString()) . '</pre>';
}

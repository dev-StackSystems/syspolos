<?php
declare(strict_types=1);

$email = trim((string)($_POST['email'] ?? ''));
$senha = (string)($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    header('Location: ?p=login&err=1');
    exit;
}

if (!auth_login($email, $senha)) {
    header('Location: ?p=login&err=1');
    exit;
}

header('Location: ?p=home');
exit;

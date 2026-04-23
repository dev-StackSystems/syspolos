<?php
declare(strict_types=1);

$cod   = p_int($_POST, 'cod_usuario');
$nome  = p($_POST, 'nome');
$email = strtolower(trim((string)($_POST['email'] ?? '')));
$senha = (string)($_POST['senha'] ?? '');
$ativo = isset($_POST['ies_ativo']) ? 'S' : 'N';

if (!$nome)  json_err('Informe o nome.');
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_err('Email inválido.');
if ($cod === 0 && strlen($senha) < 4) json_err('Informe uma senha (mínimo 4 caracteres).');
if ($senha !== '' && strlen($senha) < 4) json_err('Senha precisa ter no mínimo 4 caracteres.');

try {
    if ($cod > 0) {
        if ($senha !== '') {
            $hash = password_hash($senha, PASSWORD_BCRYPT);
            db_exec("UPDATE sys_usuarios SET nome=:n, email=:e, senha_hash=:h, ies_ativo=:a WHERE cod_usuario=:c",
                    [':n'=>$nome, ':e'=>$email, ':h'=>$hash, ':a'=>$ativo, ':c'=>$cod]);
        } else {
            db_exec("UPDATE sys_usuarios SET nome=:n, email=:e, ies_ativo=:a WHERE cod_usuario=:c",
                    [':n'=>$nome, ':e'=>$email, ':a'=>$ativo, ':c'=>$cod]);
        }
    } else {
        $hash = password_hash($senha, PASSWORD_BCRYPT);
        $cod = (int)db_insert_returning(
            "INSERT INTO sys_usuarios (nome, email, senha_hash, ies_ativo) VALUES (:n, :e, :h, :a) RETURNING cod_usuario",
            [':n'=>$nome, ':e'=>$email, ':h'=>$hash, ':a'=>$ativo]
        );
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23505') json_err('Já existe um usuário com esse email.');
    json_err('Erro ao salvar: ' . $e->getMessage(), 500);
}

json_ok(['cod_usuario' => $cod]);

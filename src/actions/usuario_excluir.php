<?php
declare(strict_types=1);

$cod = p_int($_POST, 'cod_usuario');
if ($cod <= 0) json_err('Código inválido.');

$me = auth_user();
if ($me && (int)$me['cod_usuario'] === $cod) json_err('Você não pode excluir o próprio usuário.');

$total = (int)db_val("SELECT COUNT(*) FROM sys_usuarios");
if ($total <= 1) json_err('Não é possível excluir o único usuário do sistema.');

db_exec("DELETE FROM sys_usuarios WHERE cod_usuario = :c", [':c' => $cod]);
json_ok();

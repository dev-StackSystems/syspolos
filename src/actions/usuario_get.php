<?php
declare(strict_types=1);

$cod = (int)($_GET['cod_usuario'] ?? 0);
if ($cod <= 0) json_err('Código inválido.');

$row = db_one("SELECT cod_usuario, email, nome, ies_ativo FROM sys_usuarios WHERE cod_usuario = :c", [':c' => $cod]);
if (!$row) json_err('Usuário não encontrado.', 404);

json_ok(['dados' => $row]);

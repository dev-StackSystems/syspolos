<?php
declare(strict_types=1);

$cod = p_int($_POST, 'cod_escola');
if ($cod <= 0) json_err('Código inválido.');

$temAud = (int)db_val("SELECT COUNT(*) FROM auditorias WHERE cod_escola = :c", [':c' => $cod]);
if ($temAud > 0) json_err('Não é possível excluir: existem ' . $temAud . ' auditoria(s) vinculada(s).');

db_exec("DELETE FROM escolas WHERE cod_escola = :c", [':c' => $cod]);
json_ok();

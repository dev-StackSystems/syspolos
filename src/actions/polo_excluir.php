<?php
declare(strict_types=1);

$cod = p_int($_POST, 'cod_polo');
if ($cod <= 0) json_err('Código inválido.');

$temEscola = (int)db_val("SELECT COUNT(*) FROM escolas WHERE cod_polo = :c", [':c' => $cod]);
if ($temEscola > 0) json_err('Não é possível excluir: existem ' . $temEscola . ' escola(s) vinculada(s).');

db_exec("DELETE FROM polos WHERE cod_polo = :c", [':c' => $cod]);
json_ok();

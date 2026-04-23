<?php
declare(strict_types=1);

$cod = p_int($_POST, 'cod_auditoria');
if ($cod <= 0) json_err('Código inválido.');

db_exec("DELETE FROM auditorias WHERE cod_auditoria = :c", [':c' => $cod]);
json_ok();

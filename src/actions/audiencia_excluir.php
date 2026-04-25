<?php
declare(strict_types=1);

$cod = p_int($_POST, 'cod_audiencia');
if ($cod <= 0) json_err('Código inválido.');

db_exec("DELETE FROM audiencias WHERE cod_audiencia = :c", [':c' => $cod]);
json_ok();

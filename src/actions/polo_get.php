<?php
declare(strict_types=1);

$cod = (int)($_GET['cod_polo'] ?? 0);
if ($cod <= 0) json_err('Código inválido.');

$row = db_one("SELECT * FROM polos WHERE cod_polo = :c", [':c' => $cod]);
if (!$row) json_err('Polo não encontrado.', 404);

json_ok(['dados' => $row]);

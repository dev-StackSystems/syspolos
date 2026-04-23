<?php
declare(strict_types=1);

$cod = (int)($_GET['cod_escola'] ?? 0);
if ($cod <= 0) json_err('Código inválido.');

$row = db_one("SELECT * FROM escolas WHERE cod_escola = :c", [':c' => $cod]);
if (!$row) json_err('Escola não encontrada.', 404);

json_ok(['dados' => $row]);

<?php
declare(strict_types=1);

$cod_polo  = p_int($_POST, 'cod_polo');
$polo_nome = p($_POST, 'polo_nome');
$ies_ativo = isset($_POST['ies_ativo']) ? 'S' : 'N';

if (!$polo_nome) json_err('Informe o nome do polo.');
if (mb_strlen($polo_nome) > 100) json_err('Nome excede 100 caracteres.');

try {
    if ($cod_polo > 0) {
        db_exec(
            "UPDATE polos SET polo_nome = :n, ies_ativo = :a WHERE cod_polo = :c",
            [':n' => $polo_nome, ':a' => $ies_ativo, ':c' => $cod_polo]
        );
    } else {
        $cod_polo = (int)db_insert_returning(
            "INSERT INTO polos (polo_nome, ies_ativo) VALUES (:n, :a) RETURNING cod_polo",
            [':n' => $polo_nome, ':a' => $ies_ativo]
        );
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23505') json_err('Já existe um polo com esse nome.');
    json_err('Erro ao salvar: ' . $e->getMessage(), 500);
}

json_ok(['cod_polo' => $cod_polo]);

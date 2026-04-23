<?php
declare(strict_types=1);

$cod         = p_int($_POST, 'cod_escola');
$cod_polo    = p_int($_POST, 'cod_polo');
$nome        = p($_POST, 'escola_nome');
$localidade  = p($_POST, 'localidade');
$diretor     = p($_POST, 'diretor');
$coordenador = p($_POST, 'coordenador');
$ativo       = isset($_POST['ies_ativo']) ? 'S' : 'N';

if ($cod_polo <= 0) json_err('Selecione um polo.');
if (!$nome)         json_err('Informe o nome da escola.');

if ($cod > 0) {
    db_exec(
        "UPDATE escolas
            SET cod_polo = :cp, escola_nome = :n, localidade = :l,
                diretor = :d, coordenador = :co, ies_ativo = :a
          WHERE cod_escola = :c",
        [':cp'=>$cod_polo, ':n'=>$nome, ':l'=>$localidade, ':d'=>$diretor,
         ':co'=>$coordenador, ':a'=>$ativo, ':c'=>$cod]
    );
} else {
    $cod = (int)db_insert_returning(
        "INSERT INTO escolas (cod_polo, escola_nome, localidade, diretor, coordenador, ies_ativo)
         VALUES (:cp, :n, :l, :d, :co, :a) RETURNING cod_escola",
        [':cp'=>$cod_polo, ':n'=>$nome, ':l'=>$localidade, ':d'=>$diretor,
         ':co'=>$coordenador, ':a'=>$ativo]
    );
}

json_ok(['cod_escola' => $cod]);

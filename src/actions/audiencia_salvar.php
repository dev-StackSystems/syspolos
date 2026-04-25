<?php
declare(strict_types=1);

$cod        = p_int($_POST, 'cod_audiencia');
$cod_escola = p_int($_POST, 'cod_escola');
$cod_polo   = p_int($_POST, 'cod_polo');
$data       = p($_POST, 'dat_realizacao');
$turno      = p($_POST, 'ies_turno');
$turma      = p($_POST, 'turma');
$tecnico    = p($_POST, 'tecnico_responsavel');
$conclusao  = $_POST['txt_conclusao'] ?? '';

if ($cod_polo <= 0)   json_err('Selecione o polo.');
if ($cod_escola <= 0) json_err('Selecione a escola.');
if (!$data)           json_err('Informe a data de realização.');
if (!$turno)          json_err('Informe o turno.');
if (!$turma)          json_err('Informe a turma.');

// valida relação escola/polo
$poloEscola = (int)db_val("SELECT cod_polo FROM escolas WHERE cod_escola = :c", [':c' => $cod_escola]);
if ($poloEscola !== $cod_polo) json_err('A escola selecionada não pertence ao polo informado.');

$fields = [
    ':ce'   => $cod_escola,
    ':cp'   => $cod_polo,
    ':dt'   => $data,
    ':tu'   => $turno,
    ':tm'   => $turma,
    ':qa'   => p_int($_POST, 'qtd_alunos'),
    ':qp'   => p_int($_POST, 'qtd_pcd'),
    ':tr'   => $tecnico,
    ':lf'   => p_int($_POST, 'lei_fluencia'),
    ':lsf'  => p_int($_POST, 'lei_sem_fluencia'),
    ':lfr'  => p_int($_POST, 'lei_frases'),
    ':lpa'  => p_int($_POST, 'lei_palavras'),
    ':lsi'  => p_int($_POST, 'lei_silabas'),
    ':lnl'  => p_int($_POST, 'lei_nao_leitor'),
    ':eo'   => p_int($_POST, 'esc_ortografico'),
    ':ea'   => p_int($_POST, 'esc_alfabetico'),
    ':esa'  => p_int($_POST, 'esc_silabico_alfabetico'),
    ':es'   => p_int($_POST, 'esc_silabico'),
    ':eps'  => p_int($_POST, 'esc_pre_silabico'),
    ':con'  => $conclusao,
];

if ($cod > 0) {
    $fields[':c'] = $cod;
    db_exec("
        UPDATE audiencias SET
            cod_escola=:ce, cod_polo=:cp, dat_realizacao=:dt, ies_turno=:tu, turma=:tm,
            qtd_alunos=:qa, qtd_pcd=:qp, tecnico_responsavel=:tr,
            lei_fluencia=:lf, lei_sem_fluencia=:lsf, lei_frases=:lfr,
            lei_palavras=:lpa, lei_silabas=:lsi, lei_nao_leitor=:lnl,
            esc_ortografico=:eo, esc_alfabetico=:ea, esc_silabico_alfabetico=:esa,
            esc_silabico=:es, esc_pre_silabico=:eps,
            txt_conclusao=:con, dat_alteracao=NOW()
         WHERE cod_audiencia=:c
    ", $fields);
} else {
    $cod = (int)db_insert_returning("
        INSERT INTO audiencias (
            cod_escola, cod_polo, dat_realizacao, ies_turno, turma,
            qtd_alunos, qtd_pcd, tecnico_responsavel,
            lei_fluencia, lei_sem_fluencia, lei_frases, lei_palavras, lei_silabas, lei_nao_leitor,
            esc_ortografico, esc_alfabetico, esc_silabico_alfabetico, esc_silabico, esc_pre_silabico,
            txt_conclusao
        ) VALUES (
            :ce, :cp, :dt, :tu, :tm,
            :qa, :qp, :tr,
            :lf, :lsf, :lfr, :lpa, :lsi, :lnl,
            :eo, :ea, :esa, :es, :eps,
            :con
        ) RETURNING cod_audiencia
    ", $fields);
}

json_ok(['cod_audiencia' => $cod]);

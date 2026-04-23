<?php
$cod = (int)($_GET['cod_auditoria'] ?? 0);
$pageTitle = 'Ficha de auditoria';

$a = db_one("
    SELECT a.*, e.escola_nome, e.localidade, e.diretor, e.coordenador, p.polo_nome
      FROM auditorias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     WHERE a.cod_auditoria = :c
", [':c' => $cod]);

if (!$a) {
    echo '<div class="alert alert-warning">Auditoria não encontrada.</div>';
    return;
}

$tot_lei = (int)$a['lei_fluencia']+(int)$a['lei_sem_fluencia']+(int)$a['lei_frases']+(int)$a['lei_palavras']+(int)$a['lei_silabas']+(int)$a['lei_nao_leitor'];
$tot_esc = (int)$a['esc_ortografico']+(int)$a['esc_alfabetico']+(int)$a['esc_silabico_alfabetico']+(int)$a['esc_silabico']+(int)$a['esc_pre_silabico'];

$rows_leitura = [
    'Leitura de texto COM fluência' => $a['lei_fluencia'],
    'Leitura de texto SEM fluência' => $a['lei_sem_fluencia'],
    'Leitura de frases'             => $a['lei_frases'],
    'Leitura de palavras'           => $a['lei_palavras'],
    'Leitura de sílabas'            => $a['lei_silabas'],
    'Não leitor'                    => $a['lei_nao_leitor'],
];
$rows_escrita = [
    'Ortográfico'         => $a['esc_ortografico'],
    'Alfabético'          => $a['esc_alfabetico'],
    'Silábico-alfabético' => $a['esc_silabico_alfabetico'],
    'Silábico'            => $a['esc_silabico'],
    'Pré-silábico'        => $a['esc_pre_silabico'],
];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Ficha de Auditoria <span class="badge badge-polo"><?= e($a['polo_nome']) ?></span></h4>
  <div>
    <a href="<?= url('auditoria_form', ['cod_auditoria'=>$cod]) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Editar</a>
    <a href="<?= url('auditorias') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
    <button class="btn btn-sm btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
  </div>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-header card-header-brand">IDENTIFICAÇÃO</div>
  <div class="card-body">
    <div class="row">
      <div class="col-md-6"><strong>Escola:</strong> <?= e($a['escola_nome']) ?></div>
      <div class="col-md-6"><strong>Localidade:</strong> <?= e($a['localidade']) ?></div>
      <div class="col-md-6"><strong>Diretor(a):</strong> <?= e($a['diretor']) ?></div>
      <div class="col-md-6"><strong>Coordenador(a):</strong> <?= e($a['coordenador']) ?></div>
      <div class="col-md-3"><strong>Data:</strong> <?= fmt_date_br($a['dat_realizacao']) ?></div>
      <div class="col-md-3"><strong>Turno:</strong> <?= e($a['ies_turno']) ?></div>
      <div class="col-md-3"><strong>Turma:</strong> <?= e($a['turma']) ?></div>
      <div class="col-md-3"><strong>Qtd alunos:</strong> <?= (int)$a['qtd_alunos'] ?> &nbsp; <strong>PCD:</strong> <?= (int)$a['qtd_pcd'] ?></div>
      <div class="col-md-12 mt-2"><strong>Técnico responsável:</strong> <?= e($a['tecnico_responsavel']) ?></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">CRITÉRIOS DE LEITURA</div>
      <table class="table tbl-compact mb-0">
        <tbody>
        <?php foreach ($rows_leitura as $lbl => $val): ?>
          <tr><td><?= e($lbl) ?></td><td class="text-end" style="width:80px;"><strong><?= (int)$val ?></strong></td></tr>
        <?php endforeach; ?>
          <tr class="table-secondary"><td><strong>TOTAL</strong></td><td class="text-end"><strong><?= $tot_lei ?></strong></td></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">CRITÉRIOS DE ESCRITA</div>
      <table class="table tbl-compact mb-0">
        <tbody>
        <?php foreach ($rows_escrita as $lbl => $val): ?>
          <tr><td><?= e($lbl) ?></td><td class="text-end" style="width:80px;"><strong><?= (int)$val ?></strong></td></tr>
        <?php endforeach; ?>
          <tr class="table-secondary"><td><strong>TOTAL</strong></td><td class="text-end"><strong><?= $tot_esc ?></strong></td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card shadow-sm mt-3">
  <div class="card-header card-header-brand">CONCLUSÃO E PARECER TÉCNICO</div>
  <div class="card-body">
    <?php if (trim((string)$a['txt_conclusao'])): ?>
      <p style="white-space:pre-wrap;"><?= e($a['txt_conclusao']) ?></p>
    <?php else: ?>
      <em class="text-muted">(sem parecer)</em>
    <?php endif; ?>
  </div>
</div>

<p class="text-muted small mt-3">
  Cadastro: <?= fmt_date_br($a['dat_cadastro']) ?>
  <?php if ($a['dat_alteracao']): ?> · Última alteração: <?= fmt_date_br($a['dat_alteracao']) ?><?php endif; ?>
</p>

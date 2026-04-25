<?php
$cod = (int)($_GET['cod_audiencia'] ?? 0);
$pageTitle = 'Ficha de Audiência';

$a = db_one("
    SELECT a.*, e.escola_nome, e.localidade, e.diretor, e.coordenador, p.polo_nome
      FROM audiencias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     WHERE a.cod_audiencia = :c
", [':c' => $cod]);

if (!$a) {
    echo '<div class="alert alert-warning">Audiência não encontrada.</div>';
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

function pctv(int $v, int $total): string {
    if ($total <= 0) return '—';
    return number_format(($v/$total)*100, 1, ',', '.') . '%';
}
?>
<style>
.ficha-wrap{background:#fff; border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow-sm); overflow:hidden}
.ficha-header{text-align:center; padding:1.6rem 1.5rem 1.2rem; border-bottom:2px solid var(--ink)}
.ficha-header .title{font-size:1.1rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink); margin-bottom:.15rem}
.ficha-header .sub{font-size:.85rem; color:var(--ink-2); font-weight:500; letter-spacing:.04em; text-transform:uppercase}
.ficha-header .meta{font-size:.78rem; color:var(--muted); margin-top:.5rem}
.ficha-section{padding:0 1.5rem 1.2rem}
.ficha-section-title{font-weight:700; font-size:.78rem; text-transform:uppercase; letter-spacing:.1em; padding:.7rem 0; border-bottom:1px solid var(--line); margin-top:1rem; display:flex; justify-content:space-between; align-items:center}
.ficha-section-title .count{background:var(--ink); color:#fff; padding:.2rem .6rem; border-radius:4px; font-size:.68rem; font-variant-numeric:tabular-nums}
.ficha-grid{display:grid; grid-template-columns:repeat(2, 1fr); gap:.4rem 1.5rem; padding:.8rem 0; font-size:.85rem}
.ficha-grid .item{padding:.2rem 0; color:var(--ink-2)}
.ficha-grid .item .l{color:var(--muted); font-size:.7rem; text-transform:uppercase; letter-spacing:.05em; font-weight:600; display:block; margin-bottom:.1rem}
.ficha-grid .item .v{color:var(--ink); font-weight:500}
.ficha-grid .full{grid-column:1/-1}
.rep-table{width:100%; border-collapse:collapse; font-size:.82rem}
.rep-table thead th{background:var(--ink); color:#fff; font-weight:600; text-transform:uppercase; letter-spacing:.04em; font-size:.7rem; padding:.55rem .6rem; text-align:left}
.rep-table tbody td{padding:.5rem .6rem; border-bottom:1px solid var(--line); color:var(--ink-2)}
.rep-table .total-row td{background:var(--bg-2); font-weight:700; color:var(--ink); border-top:2px solid var(--ink)}
.parecer{background:var(--bg); border:1px solid var(--line); border-radius:8px; padding:.9rem 1.1rem; white-space:pre-wrap; font-size:.88rem; color:var(--ink-2); line-height:1.55}
.ficha-footer{font-size:.72rem; color:var(--muted); text-align:center; padding:1rem 1.5rem; border-top:1px solid var(--line); background:var(--bg)}

@media print{
  body{background:#fff; font-size:11pt}
  .topbar, footer, .no-print{display:none!important}
  main.container-fluid{padding:0!important; max-width:none!important}
  .ficha-wrap{border:0; box-shadow:none; border-radius:0}
  .ficha-section{padding:0 0 .8rem}
  @page{size:A4; margin:1.5cm 1cm}
}
</style>

<!-- Controles (não imprimem) -->
<div class="no-print">
  <div class="page-title">
    <div>
      <h4><i class="bi bi-clipboard-check"></i> Ficha de Audiência <span class="badge badge-polo ms-2"><?= e($a['polo_nome']) ?></span></h4>
      <div class="muted-sub"><?= e($a['escola_nome']) ?> &middot; <?= fmt_date_br($a['dat_realizacao']) ?> &middot; <?= e($a['ies_turno']) ?></div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= url('audiencias') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
      <a href="<?= url('audiencia_form', ['cod_audiencia'=>$cod]) ?>" class="btn btn-ghost btn-sm"><i class="bi bi-pencil"></i> Editar</a>
      <button class="btn btn-brand btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / PDF</button>
    </div>
  </div>
</div>

<!-- Ficha -->
<div class="ficha-wrap">

  <div class="ficha-header">
    <div class="title">Ficha de Audiência de Leitura</div>
    <div class="sub">Secretaria Municipal de Educação</div>
    <div class="meta">
      <?= e($a['polo_nome']) ?> &middot; Emitida em <?= date('d/m/Y H:i') ?>
    </div>
  </div>

  <div class="ficha-section">
    <div class="ficha-section-title"><span>Identificação</span></div>
    <div class="ficha-grid">
      <div class="item"><span class="l">Escola</span><span class="v"><?= e($a['escola_nome']) ?></span></div>
      <div class="item"><span class="l">Localidade</span><span class="v"><?= e($a['localidade']) ?: '—' ?></span></div>
      <div class="item"><span class="l">Diretor(a)</span><span class="v"><?= e($a['diretor']) ?: '—' ?></span></div>
      <div class="item"><span class="l">Coordenador(a)</span><span class="v"><?= e($a['coordenador']) ?: '—' ?></span></div>
      <div class="item"><span class="l">Data de realização</span><span class="v"><?= fmt_date_br($a['dat_realizacao']) ?></span></div>
      <div class="item"><span class="l">Turno</span><span class="v"><?= e($a['ies_turno']) ?></span></div>
      <div class="item"><span class="l">Turma</span><span class="v"><?= e($a['turma']) ?></span></div>
      <div class="item"><span class="l">Quantidade de alunos / PCD</span><span class="v"><?= (int)$a['qtd_alunos'] ?> alunos · <?= (int)$a['qtd_pcd'] ?> PCD</span></div>
      <div class="item full"><span class="l">Técnico responsável</span><span class="v"><?= e($a['tecnico_responsavel']) ?: '—' ?></span></div>
    </div>
  </div>

  <div class="ficha-section">
    <div class="ficha-section-title">
      <span>Critérios de leitura</span>
      <span class="count">Total: <?= $tot_lei ?></span>
    </div>
    <table class="rep-table">
      <thead><tr><th>Critério</th><th style="width:100px;text-align:right">Quantidade</th><th style="width:80px;text-align:right">%</th></tr></thead>
      <tbody>
      <?php foreach ($rows_leitura as $lbl => $v): ?>
        <tr><td><?= e($lbl) ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$v ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= pctv((int)$v, $tot_lei) ?></td></tr>
      <?php endforeach; ?>
        <tr class="total-row"><td>TOTAL</td><td style="text-align:right"><?= $tot_lei ?></td><td style="text-align:right"><?= $tot_lei > 0 ? '100%' : '—' ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="ficha-section">
    <div class="ficha-section-title">
      <span>Critérios de escrita</span>
      <span class="count">Total: <?= $tot_esc ?></span>
    </div>
    <table class="rep-table">
      <thead><tr><th>Critério</th><th style="width:100px;text-align:right">Quantidade</th><th style="width:80px;text-align:right">%</th></tr></thead>
      <tbody>
      <?php foreach ($rows_escrita as $lbl => $v): ?>
        <tr><td><?= e($lbl) ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$v ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= pctv((int)$v, $tot_esc) ?></td></tr>
      <?php endforeach; ?>
        <tr class="total-row"><td>TOTAL</td><td style="text-align:right"><?= $tot_esc ?></td><td style="text-align:right"><?= $tot_esc > 0 ? '100%' : '—' ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="ficha-section">
    <div class="ficha-section-title"><span>Conclusão e parecer técnico</span></div>
    <?php if (trim((string)$a['txt_conclusao'])): ?>
      <div class="parecer"><?= e($a['txt_conclusao']) ?></div>
    <?php else: ?>
      <div class="parecer" style="font-style:italic; color:var(--muted)">(Sem parecer registrado)</div>
    <?php endif; ?>
  </div>

  <div class="ficha-footer">
    Registrado em <?= fmt_date_br($a['dat_cadastro']) ?>
    <?php if ($a['dat_alteracao']): ?> · Última alteração em <?= fmt_date_br($a['dat_alteracao']) ?><?php endif; ?>
    &middot; Emitida via Sistema de Audiência de Leitura
  </div>

</div>

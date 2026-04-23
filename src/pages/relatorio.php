<?php
$pageTitle = 'Relatório de Auditoria';

$fil_polo = (int)($_GET['cod_polo'] ?? 0);
$fil_de   = $_GET['de']  ?? '';
$fil_ate  = $_GET['ate'] ?? '';

$where = ['1=1']; $args = [];
if ($fil_polo > 0) { $where[]='a.cod_polo = :p';        $args[':p']=$fil_polo; }
if ($fil_de)       { $where[]='a.dat_realizacao >= :d'; $args[':d']=$fil_de; }
if ($fil_ate)      { $where[]='a.dat_realizacao <= :t'; $args[':t']=$fil_ate; }

// Totais gerais
$tot = db_one("
    SELECT COUNT(*)                                         AS qtd_audit,
           COALESCE(SUM(qtd_alunos),0)                      AS tot_alunos,
           COALESCE(SUM(qtd_pcd),0)                         AS tot_pcd,
           COALESCE(SUM(lei_fluencia),0)                    AS lei_fluencia,
           COALESCE(SUM(lei_sem_fluencia),0)                AS lei_sem_fluencia,
           COALESCE(SUM(lei_frases),0)                      AS lei_frases,
           COALESCE(SUM(lei_palavras),0)                    AS lei_palavras,
           COALESCE(SUM(lei_silabas),0)                     AS lei_silabas,
           COALESCE(SUM(lei_nao_leitor),0)                  AS lei_nao_leitor,
           COALESCE(SUM(esc_ortografico),0)                 AS esc_ortografico,
           COALESCE(SUM(esc_alfabetico),0)                  AS esc_alfabetico,
           COALESCE(SUM(esc_silabico_alfabetico),0)         AS esc_silabico_alfabetico,
           COALESCE(SUM(esc_silabico),0)                    AS esc_silabico,
           COALESCE(SUM(esc_pre_silabico),0)                AS esc_pre_silabico
      FROM auditorias a
     WHERE " . implode(' AND ', $where) . "
", $args);

// Detalhe: auditorias agrupadas por polo
$rows = db_all("
    SELECT p.cod_polo, p.polo_nome, a.cod_auditoria, a.dat_realizacao, a.ies_turno,
           a.turma, a.qtd_alunos, a.qtd_pcd, e.escola_nome, e.localidade,
           (a.lei_fluencia+a.lei_sem_fluencia+a.lei_frases+a.lei_palavras+a.lei_silabas+a.lei_nao_leitor) AS tot_lei,
           (a.esc_ortografico+a.esc_alfabetico+a.esc_silabico_alfabetico+a.esc_silabico+a.esc_pre_silabico) AS tot_esc
      FROM auditorias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     WHERE " . implode(' AND ', $where) . "
     ORDER BY p.polo_nome, a.dat_realizacao DESC, e.escola_nome
", $args);

// Agrupa por polo
$porPolo = [];
foreach ($rows as $r) {
    $porPolo[$r['polo_nome']][] = $r;
}

$polos = db_all("SELECT cod_polo, polo_nome FROM polos ORDER BY polo_nome");

$totLei = (int)$tot['lei_fluencia']+(int)$tot['lei_sem_fluencia']+(int)$tot['lei_frases']+(int)$tot['lei_palavras']+(int)$tot['lei_silabas']+(int)$tot['lei_nao_leitor'];
$totEsc = (int)$tot['esc_ortografico']+(int)$tot['esc_alfabetico']+(int)$tot['esc_silabico_alfabetico']+(int)$tot['esc_silabico']+(int)$tot['esc_pre_silabico'];

function pct(int $v, int $total): string {
    if ($total <= 0) return '—';
    return number_format(($v / $total) * 100, 1, ',', '.') . '%';
}

$periodoLabel = '';
if ($fil_de && $fil_ate) $periodoLabel = 'Período: ' . fmt_date_br($fil_de) . ' a ' . fmt_date_br($fil_ate);
elseif ($fil_de) $periodoLabel = 'A partir de ' . fmt_date_br($fil_de);
elseif ($fil_ate) $periodoLabel = 'Até ' . fmt_date_br($fil_ate);
else $periodoLabel = 'Todo o histórico';

$poloLabel = $fil_polo > 0 ? db_val("SELECT polo_nome FROM polos WHERE cod_polo = :c", [':c'=>$fil_polo]) : 'Todos os polos';
?>
<style>
.report-wrap{background:#fff; border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow-sm); padding:0; overflow:hidden}
.report-header{text-align:center; padding:1.6rem 1.5rem 1.2rem; border-bottom:2px solid var(--ink)}
.report-header .title{font-size:1.1rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--ink); margin-bottom:.15rem}
.report-header .sub{font-size:.85rem; color:var(--ink-2); font-weight:500; letter-spacing:.04em; text-transform:uppercase}
.report-header .meta{font-size:.78rem; color:var(--muted); margin-top:.5rem}
.report-section{padding:0 1.5rem 1.2rem}
.report-section-title{font-weight:700; font-size:.78rem; text-transform:uppercase; letter-spacing:.1em; padding:.7rem 0; border-bottom:1px solid var(--line); margin-top:1rem}
.rep-table{width:100%; border-collapse:collapse; margin-top:.4rem; font-size:.82rem}
.rep-table thead th{
    background:var(--ink); color:#fff;
    font-weight:600; text-transform:uppercase; letter-spacing:.04em;
    font-size:.7rem; padding:.55rem .6rem; text-align:left;
    border-right:1px solid rgba(255,255,255,.1);
}
.rep-table thead th:last-child{border-right:0}
.rep-table tbody td{padding:.45rem .6rem; border-bottom:1px solid var(--line); color:var(--ink-2)}
.rep-table .grp-row td{
    background:var(--brand-50); color:var(--brand-700);
    font-weight:700; text-transform:uppercase; letter-spacing:.04em;
    font-size:.72rem; padding:.55rem .6rem;
}
.rep-table .total-row td{
    background:var(--bg-2); font-weight:700; color:var(--ink);
    border-top:2px solid var(--ink);
}
.rep-summary{display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:.8rem; padding:0 1.5rem; margin-bottom:1rem}
.rep-summary .item{text-align:center; padding:.9rem; background:var(--bg); border:1px solid var(--line); border-radius:10px}
.rep-summary .item .n{font-size:1.6rem; font-weight:700; color:var(--brand-600); font-variant-numeric:tabular-nums; line-height:1}
.rep-summary .item .l{font-size:.7rem; color:var(--muted); text-transform:uppercase; letter-spacing:.06em; font-weight:600; margin-top:.3rem}
.rep-kv{display:flex; flex-wrap:wrap; gap:.4rem 1.5rem; font-size:.82rem; color:var(--ink-2); padding:.4rem 0}
.rep-kv strong{color:var(--ink); font-weight:600}
.rep-footer{font-size:.72rem; color:var(--muted); text-align:center; padding:1rem 1.5rem; border-top:1px solid var(--line); background:var(--bg)}

@media print{
  body{background:#fff; font-size:11pt}
  .topbar, footer, .no-print{display:none!important}
  main.container-fluid{padding:0!important; max-width:none!important}
  .report-wrap{border:0; box-shadow:none; border-radius:0}
  .report-section{padding:0 0 .8rem}
  .rep-summary{padding:0; page-break-inside:avoid}
  .rep-table thead{display:table-header-group}
  .rep-table tr{page-break-inside:avoid}
  @page{size:A4; margin:1.5cm 1cm}
}
</style>

<!-- Controles (não imprimem) -->
<div class="no-print">
  <div class="page-title">
    <div>
      <h4><i class="bi bi-graph-up"></i> Relatório consolidado</h4>
      <div class="muted-sub">Totais gerais e por polo. Use o botão Imprimir para salvar em PDF.</div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-ghost btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / Salvar PDF</button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <input type="hidden" name="p" value="relatorio">
        <div class="col-md-3">
          <label class="form-label">Polo</label>
          <select name="cod_polo" class="form-select form-select-sm">
            <option value="0">Todos</option>
            <?php foreach ($polos as $po): ?>
              <option value="<?= (int)$po['cod_polo'] ?>" <?= $fil_polo==$po['cod_polo']?'selected':'' ?>><?= e($po['polo_nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">De</label>
          <input type="date" name="de" class="form-control form-control-sm" value="<?= e($fil_de) ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Até</label>
          <input type="date" name="ate" class="form-control form-control-sm" value="<?= e($fil_ate) ?>">
        </div>
        <div class="col-md-3 d-flex gap-2">
          <button class="btn btn-sm btn-brand flex-fill"><i class="bi bi-funnel"></i> Aplicar</button>
          <a href="<?= url('relatorio') ?>" class="btn btn-sm btn-ghost"><i class="bi bi-x"></i></a>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Relatório -->
<div class="report-wrap">

  <div class="report-header">
    <div class="title">Relatório de Auditoria de Leitura</div>
    <div class="sub">Secretaria Municipal de Educação</div>
    <div class="meta">
      <?= e($poloLabel) ?> &middot; <?= e($periodoLabel) ?> &middot; Emitido em <?= date('d/m/Y H:i') ?>
    </div>
  </div>

  <!-- Resumo -->
  <div class="report-section">
    <div class="report-section-title">Resumo geral</div>
    <div class="rep-summary">
      <div class="item"><div class="n"><?= (int)$tot['qtd_audit'] ?></div><div class="l">Auditorias</div></div>
      <div class="item"><div class="n"><?= (int)$tot['tot_alunos'] ?></div><div class="l">Alunos avaliados</div></div>
      <div class="item"><div class="n"><?= (int)$tot['tot_pcd'] ?></div><div class="l">PCD</div></div>
      <div class="item"><div class="n"><?= $totLei ?></div><div class="l">Total leitura</div></div>
      <div class="item"><div class="n"><?= $totEsc ?></div><div class="l">Total escrita</div></div>
    </div>
  </div>

  <!-- Leitura -->
  <div class="report-section">
    <div class="report-section-title">Critérios de leitura</div>
    <table class="rep-table">
      <thead>
        <tr><th>Critério</th><th style="width:90px;text-align:right">Quantidade</th><th style="width:80px;text-align:right">%</th></tr>
      </thead>
      <tbody>
        <?php
        $itLei = [
          'Leitura de texto com fluência' => $tot['lei_fluencia'],
          'Leitura de texto sem fluência' => $tot['lei_sem_fluencia'],
          'Leitura de frases'             => $tot['lei_frases'],
          'Leitura de palavras'           => $tot['lei_palavras'],
          'Leitura de sílabas'            => $tot['lei_silabas'],
          'Não leitor'                    => $tot['lei_nao_leitor'],
        ];
        foreach ($itLei as $lbl => $v): ?>
          <tr><td><?= e($lbl) ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$v ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= pct((int)$v, $totLei) ?></td></tr>
        <?php endforeach; ?>
        <tr class="total-row"><td>TOTAL</td><td style="text-align:right"><?= $totLei ?></td><td style="text-align:right">100%</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Escrita -->
  <div class="report-section">
    <div class="report-section-title">Critérios de escrita</div>
    <table class="rep-table">
      <thead>
        <tr><th>Critério</th><th style="width:90px;text-align:right">Quantidade</th><th style="width:80px;text-align:right">%</th></tr>
      </thead>
      <tbody>
        <?php
        $itEsc = [
          'Ortográfico'         => $tot['esc_ortografico'],
          'Alfabético'          => $tot['esc_alfabetico'],
          'Silábico-alfabético' => $tot['esc_silabico_alfabetico'],
          'Silábico'            => $tot['esc_silabico'],
          'Pré-silábico'        => $tot['esc_pre_silabico'],
        ];
        foreach ($itEsc as $lbl => $v): ?>
          <tr><td><?= e($lbl) ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$v ?></td><td style="text-align:right; font-variant-numeric:tabular-nums"><?= pct((int)$v, $totEsc) ?></td></tr>
        <?php endforeach; ?>
        <tr class="total-row"><td>TOTAL</td><td style="text-align:right"><?= $totEsc ?></td><td style="text-align:right">100%</td></tr>
      </tbody>
    </table>
  </div>

  <!-- Detalhado por polo -->
  <?php if ($porPolo): ?>
  <div class="report-section">
    <div class="report-section-title">Auditorias por polo</div>
    <table class="rep-table">
      <thead>
        <tr>
          <th style="width:90px">Data</th>
          <th>Escola</th>
          <th>Localidade</th>
          <th style="width:110px">Turma / Turno</th>
          <th style="width:70px; text-align:right">Alunos</th>
          <th style="width:80px; text-align:right">Leitura</th>
          <th style="width:80px; text-align:right">Escrita</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($porPolo as $poloNome => $audits): ?>
          <tr class="grp-row">
            <td colspan="7"><?= e($poloNome) ?> <span style="font-weight:500; text-transform:none; letter-spacing:normal;">(<?= count($audits) ?> auditoria<?= count($audits)>1?'s':'' ?>)</span></td>
          </tr>
          <?php foreach ($audits as $r): ?>
            <tr>
              <td><?= fmt_date_br($r['dat_realizacao']) ?></td>
              <td><?= e($r['escola_nome']) ?></td>
              <td><?= e($r['localidade']) ?></td>
              <td><?= e($r['turma']) ?> · <?= e($r['ies_turno']) ?></td>
              <td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$r['qtd_alunos'] ?></td>
              <td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$r['tot_lei'] ?></td>
              <td style="text-align:right; font-variant-numeric:tabular-nums"><?= (int)$r['tot_esc'] ?></td>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="report-section">
    <div class="empty"><div class="empty-ico"><i class="bi bi-inbox"></i></div><h5>Sem auditorias no período</h5></div>
  </div>
  <?php endif; ?>

  <div class="rep-footer">
    Relatório gerado automaticamente pelo Sistema de Auditoria de Leitura — <?= date('d/m/Y H:i') ?>
  </div>

</div>

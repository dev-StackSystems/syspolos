<?php
$pageTitle = 'Painel';

$kpi = [
    'polos'      => (int)db_val("SELECT COUNT(*) FROM polos"),
    'escolas'    => (int)db_val("SELECT COUNT(*) FROM escolas"),
    'auditorias' => (int)db_val("SELECT COUNT(*) FROM auditorias"),
    'alunos'     => (int)db_val("SELECT COALESCE(SUM(qtd_alunos),0) FROM auditorias"),
];

$recentes = db_all("
    SELECT a.cod_auditoria, a.dat_realizacao, a.turma, e.escola_nome, p.polo_nome
      FROM auditorias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     ORDER BY a.cod_auditoria DESC
     LIMIT 10
");

$porPolo = db_all("
    SELECT p.cod_polo, p.polo_nome,
           COUNT(DISTINCT e.cod_escola) AS qtd_escolas,
           COUNT(a.cod_auditoria)       AS qtd_auditorias,
           COALESCE(SUM(a.qtd_alunos),0) AS tot_alunos
      FROM polos p
      LEFT JOIN escolas    e ON e.cod_polo      = p.cod_polo
      LEFT JOIN auditorias a ON a.cod_polo      = p.cod_polo
     GROUP BY p.cod_polo, p.polo_nome
     ORDER BY p.polo_nome
");
?>
<h4 class="mb-3"><i class="bi bi-speedometer2"></i> Painel</h4>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= $kpi['polos'] ?></div><div class="kpi-lbl">Polos</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= $kpi['escolas'] ?></div><div class="kpi-lbl">Escolas</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= $kpi['auditorias'] ?></div><div class="kpi-lbl">Auditorias</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= $kpi['alunos'] ?></div><div class="kpi-lbl">Alunos avaliados</div>
  </div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">RESUMO POR POLO</div>
      <div class="card-body p-0">
        <table class="table tbl-compact mb-0">
          <thead><tr><th>Polo</th><th class="text-center">Escolas</th><th class="text-center">Auditorias</th><th class="text-center">Alunos</th><th style="width:60px;"></th></tr></thead>
          <tbody>
          <?php foreach ($porPolo as $po): ?>
            <tr>
              <td><strong><?= e($po['polo_nome']) ?></strong></td>
              <td class="text-center"><?= (int)$po['qtd_escolas'] ?></td>
              <td class="text-center"><?= (int)$po['qtd_auditorias'] ?></td>
              <td class="text-center"><?= (int)$po['tot_alunos'] ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?= url('auditorias', ['cod_polo'=>$po['cod_polo']]) ?>"><i class="bi bi-arrow-right"></i></a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">AUDITORIAS RECENTES</div>
      <div class="card-body p-0">
        <table class="table tbl-compact mb-0">
          <thead><tr><th style="width:90px;">Data</th><th>Polo</th><th>Escola</th><th>Turma</th><th style="width:60px;"></th></tr></thead>
          <tbody>
          <?php foreach ($recentes as $r): ?>
            <tr>
              <td><?= fmt_date_br($r['dat_realizacao']) ?></td>
              <td><span class="badge badge-polo"><?= e($r['polo_nome']) ?></span></td>
              <td><?= e($r['escola_nome']) ?></td>
              <td><?= e($r['turma']) ?></td>
              <td><a class="btn btn-sm btn-outline-secondary" href="<?= url('auditoria_view', ['cod_auditoria'=>$r['cod_auditoria']]) ?>"><i class="bi bi-eye"></i></a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$recentes): ?>
            <tr><td colspan="5" class="text-center text-muted p-4">Nenhuma auditoria ainda. <a href="<?= url('auditoria_form') ?>">Criar a primeira.</a></td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

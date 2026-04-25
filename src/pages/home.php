<?php
$pageTitle = 'Painel';

$kpi = [
    'polos'      => (int)db_val("SELECT COUNT(*) FROM polos"),
    'escolas'    => (int)db_val("SELECT COUNT(*) FROM escolas"),
    'audiencias' => (int)db_val("SELECT COUNT(*) FROM audiencias"),
    'alunos'     => (int)db_val("SELECT COALESCE(SUM(qtd_alunos),0) FROM audiencias"),
];

$recentes = db_all("
    SELECT a.cod_audiencia, a.dat_realizacao, a.turma, a.qtd_alunos, e.escola_nome, p.polo_nome
      FROM audiencias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     ORDER BY a.cod_audiencia DESC
     LIMIT 8
");

$porPolo = db_all("
    SELECT p.cod_polo, p.polo_nome,
           COUNT(DISTINCT e.cod_escola) AS qtd_escolas,
           COUNT(a.cod_audiencia)       AS qtd_audiencias,
           COALESCE(SUM(a.qtd_alunos),0) AS tot_alunos
      FROM polos p
      LEFT JOIN escolas    e ON e.cod_polo = p.cod_polo
      LEFT JOIN audiencias a ON a.cod_polo = p.cod_polo
     GROUP BY p.cod_polo, p.polo_nome
     ORDER BY p.polo_nome
");
?>

<!-- Hero -->
<div class="page-hero">
  <div class="row align-items-center">
    <div class="col-md-8">
      <h1>Bem-vindo(a) ao painel de audiências</h1>
      <p>Gerencie avaliações de leitura e escrita por polo e escola em um único lugar.</p>
      <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('audiencia_form') ?>" class="btn btn-light"><i class="bi bi-plus-lg"></i> Nova audiência</a>
        <a href="<?= url('audiencias') ?>" class="btn btn-outline-light" style="color:#fff;border-color:rgba(255,255,255,.5);"><i class="bi bi-clipboard-check"></i> Ver todas</a>
        <a href="<?= url('relatorio') ?>" class="btn btn-outline-light" style="color:#fff;border-color:rgba(255,255,255,.5);"><i class="bi bi-graph-up"></i> Relatório</a>
      </div>
    </div>
    <div class="col-md-4 d-none d-md-block text-end">
      <i class="bi bi-book-half" style="font-size:5rem;opacity:.25"></i>
    </div>
  </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-ico ind"><i class="bi bi-geo-alt"></i></div>
      <div>
        <div class="kpi-num"><?= $kpi['polos'] ?></div>
        <div class="kpi-lbl">Polos</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-ico emr"><i class="bi bi-building"></i></div>
      <div>
        <div class="kpi-num"><?= $kpi['escolas'] ?></div>
        <div class="kpi-lbl">Escolas</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-ico amb"><i class="bi bi-clipboard-check"></i></div>
      <div>
        <div class="kpi-num"><?= $kpi['audiencias'] ?></div>
        <div class="kpi-lbl">Audiências</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card">
      <div class="kpi-ico vio"><i class="bi bi-people"></i></div>
      <div>
        <div class="kpi-num"><?= $kpi['alunos'] ?></div>
        <div class="kpi-lbl">Alunos avaliados</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Resumo por polo -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-pie-chart" style="color:var(--brand-600)"></i>
        <span>Resumo por polo</span>
        <a href="<?= url('polos') ?>" class="ms-auto text-muted-2" style="font-size:.82rem">Ver todos <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php if ($porPolo): ?>
      <table class="table tbl-compact">
        <thead><tr><th>Polo</th><th class="text-center">Escolas</th><th class="text-center">Audiências</th><th class="text-center">Alunos</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($porPolo as $po): ?>
          <tr>
            <td><span class="badge badge-polo"><?= e($po['polo_nome']) ?></span></td>
            <td class="text-center"><?= (int)$po['qtd_escolas'] ?></td>
            <td class="text-center"><?= (int)$po['qtd_audiencias'] ?></td>
            <td class="text-center"><?= (int)$po['tot_alunos'] ?></td>
            <td class="text-end"><a class="btn btn-sm btn-ghost" href="<?= url('audiencias', ['cod_polo'=>$po['cod_polo']]) ?>"><i class="bi bi-arrow-right"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty"><div class="empty-ico"><i class="bi bi-inbox"></i></div><h5>Sem polos</h5></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Recentes -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-clock-history" style="color:var(--brand-600)"></i>
        <span>Audiências recentes</span>
        <a href="<?= url('audiencias') ?>" class="ms-auto text-muted-2" style="font-size:.82rem">Ver todas <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php if ($recentes): ?>
      <table class="table tbl-compact">
        <thead><tr><th style="width:90px">Data</th><th>Polo</th><th>Escola</th><th>Turma</th><th class="text-center">Alunos</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($recentes as $r): ?>
          <tr>
            <td><?= fmt_date_br($r['dat_realizacao']) ?></td>
            <td><span class="badge badge-polo"><?= e($r['polo_nome']) ?></span></td>
            <td><?= e($r['escola_nome']) ?></td>
            <td class="text-muted-2"><?= e($r['turma']) ?></td>
            <td class="text-center"><?= (int)$r['qtd_alunos'] ?></td>
            <td class="text-end"><a class="btn btn-sm btn-ghost" href="<?= url('audiencia_view', ['cod_audiencia'=>$r['cod_audiencia']]) ?>"><i class="bi bi-eye"></i></a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <div class="empty">
          <div class="empty-ico"><i class="bi bi-clipboard"></i></div>
          <h5>Nenhuma audiência ainda</h5>
          <p>Comece criando a primeira.</p>
          <a href="<?= url('audiencia_form') ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Nova audiência</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Quick actions -->
<div class="row g-3 mt-1">
  <div class="col-md-4">
    <a href="<?= url('audiencia_form') ?>" class="card h-100 text-decoration-none" style="transition:all .2s ease;">
      <div class="card-body d-flex align-items-start gap-3">
        <div class="icon-sq" style="background:var(--brand-50); color:var(--brand-600); width:44px; height:44px; font-size:1.3rem;"><i class="bi bi-plus-circle"></i></div>
        <div>
          <div style="font-weight:600; color:var(--ink)">Nova audiência</div>
          <div class="text-muted-2" style="font-size:.82rem">Cadastrar avaliação de uma turma</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= url('importar') ?>" class="card h-100 text-decoration-none" style="transition:all .2s ease;">
      <div class="card-body d-flex align-items-start gap-3">
        <div class="icon-sq" style="background:#ecfdf5; color:#059669; width:44px; height:44px; font-size:1.3rem;"><i class="bi bi-cloud-upload"></i></div>
        <div>
          <div style="font-weight:600; color:var(--ink)">Importar XLSX</div>
          <div class="text-muted-2" style="font-size:.82rem">Carregar planilha existente</div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="<?= url('relatorio') ?>" class="card h-100 text-decoration-none" style="transition:all .2s ease;">
      <div class="card-body d-flex align-items-start gap-3">
        <div class="icon-sq" style="background:#faf5ff; color:#7c3aed; width:44px; height:44px; font-size:1.3rem;"><i class="bi bi-graph-up"></i></div>
        <div>
          <div style="font-weight:600; color:var(--ink)">Relatório consolidado</div>
          <div class="text-muted-2" style="font-size:.82rem">Totais por polo e critério</div>
        </div>
      </div>
    </a>
  </div>
</div>

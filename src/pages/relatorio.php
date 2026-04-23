<?php
$pageTitle = 'Relatório consolidado';

$fil_polo = (int)($_GET['cod_polo'] ?? 0);
$fil_de   = $_GET['de']  ?? '';
$fil_ate  = $_GET['ate'] ?? '';

$where = ['1=1']; $args = [];
if ($fil_polo > 0) { $where[]='a.cod_polo = :p';       $args[':p']=$fil_polo; }
if ($fil_de)       { $where[]='a.dat_realizacao >= :d'; $args[':d']=$fil_de; }
if ($fil_ate)      { $where[]='a.dat_realizacao <= :t'; $args[':t']=$fil_ate; }

$tot = db_one("
    SELECT COUNT(*)                        AS qtd_audit,
           COALESCE(SUM(qtd_alunos),0)     AS tot_alunos,
           COALESCE(SUM(qtd_pcd),0)        AS tot_pcd,
           COALESCE(SUM(lei_fluencia),0)            AS lei_fluencia,
           COALESCE(SUM(lei_sem_fluencia),0)        AS lei_sem_fluencia,
           COALESCE(SUM(lei_frases),0)              AS lei_frases,
           COALESCE(SUM(lei_palavras),0)            AS lei_palavras,
           COALESCE(SUM(lei_silabas),0)             AS lei_silabas,
           COALESCE(SUM(lei_nao_leitor),0)          AS lei_nao_leitor,
           COALESCE(SUM(esc_ortografico),0)         AS esc_ortografico,
           COALESCE(SUM(esc_alfabetico),0)          AS esc_alfabetico,
           COALESCE(SUM(esc_silabico_alfabetico),0) AS esc_silabico_alfabetico,
           COALESCE(SUM(esc_silabico),0)            AS esc_silabico,
           COALESCE(SUM(esc_pre_silabico),0)        AS esc_pre_silabico
      FROM auditorias a
     WHERE " . implode(' AND ', $where) . "
", $args);

// Resumo por polo: respeita filtros de data e (opcionalmente) de polo
$wherePP = ['1=1']; $argsPP = [];
if ($fil_polo > 0) { $wherePP[] = 'p.cod_polo = :p';                    $argsPP[':p'] = $fil_polo; }
if ($fil_de)       { $wherePP[] = '(a.dat_realizacao IS NULL OR a.dat_realizacao >= :d)'; $argsPP[':d'] = $fil_de; }
if ($fil_ate)      { $wherePP[] = '(a.dat_realizacao IS NULL OR a.dat_realizacao <= :t)'; $argsPP[':t'] = $fil_ate; }

$porPolo = db_all("
    SELECT p.polo_nome,
           COUNT(a.cod_auditoria)        AS qtd_audit,
           COALESCE(SUM(a.qtd_alunos),0) AS tot_alunos,
           COALESCE(SUM(a.lei_fluencia+a.lei_sem_fluencia+a.lei_frases+a.lei_palavras+a.lei_silabas+a.lei_nao_leitor),0) AS tot_lei,
           COALESCE(SUM(a.esc_ortografico+a.esc_alfabetico+a.esc_silabico_alfabetico+a.esc_silabico+a.esc_pre_silabico),0) AS tot_esc
      FROM polos p
      LEFT JOIN auditorias a ON a.cod_polo = p.cod_polo
     WHERE " . implode(' AND ', $wherePP) . "
     GROUP BY p.polo_nome
     ORDER BY p.polo_nome
", $argsPP);

$polos = db_all("SELECT cod_polo, polo_nome FROM polos ORDER BY polo_nome");

$totLei = (int)$tot['lei_fluencia']+(int)$tot['lei_sem_fluencia']+(int)$tot['lei_frases']+(int)$tot['lei_palavras']+(int)$tot['lei_silabas']+(int)$tot['lei_nao_leitor'];
$totEsc = (int)$tot['esc_ortografico']+(int)$tot['esc_alfabetico']+(int)$tot['esc_silabico_alfabetico']+(int)$tot['esc_silabico']+(int)$tot['esc_pre_silabico'];

function pct(int $v, int $total): string {
    if ($total <= 0) return '0%';
    return number_format(($v / $total) * 100, 1, ',', '.') . '%';
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-bar-chart"></i> Relatório consolidado</h4>
  <button class="btn btn-sm btn-outline-dark" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
</div>

<div class="card shadow-sm mb-3">
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
      <div class="col-md-2">
        <button class="btn btn-sm btn-brand w-100"><i class="bi bi-funnel"></i> Aplicar</button>
      </div>
      <div class="col-md-1">
        <a href="<?= url('relatorio') ?>" class="btn btn-sm btn-outline-secondary w-100">X</a>
      </div>
    </form>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= (int)$tot['qtd_audit'] ?></div><div class="kpi-lbl">Auditorias</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= (int)$tot['tot_alunos'] ?></div><div class="kpi-lbl">Alunos</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= (int)$tot['tot_pcd'] ?></div><div class="kpi-lbl">PCD</div>
  </div></div></div>
  <div class="col-md-3"><div class="card shadow-sm"><div class="card-body text-center">
    <div class="kpi-num"><?= $totLei ?></div><div class="kpi-lbl">Total leitura</div>
  </div></div></div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">LEITURA — DISTRIBUIÇÃO</div>
      <table class="table tbl-compact mb-0">
        <thead><tr><th>Critério</th><th class="text-end">Qtd</th><th class="text-end" style="width:90px;">%</th></tr></thead>
        <tbody>
        <?php
        $itensLei = [
            'Com fluência'        => $tot['lei_fluencia'],
            'Sem fluência'        => $tot['lei_sem_fluencia'],
            'Frases'              => $tot['lei_frases'],
            'Palavras'            => $tot['lei_palavras'],
            'Sílabas'             => $tot['lei_silabas'],
            'Não leitor'          => $tot['lei_nao_leitor'],
        ];
        foreach ($itensLei as $lbl => $v): ?>
          <tr><td><?= e($lbl) ?></td><td class="text-end"><?= (int)$v ?></td><td class="text-end"><?= pct((int)$v, $totLei) ?></td></tr>
        <?php endforeach; ?>
        <tr class="table-secondary"><th>Total</th><th class="text-end"><?= $totLei ?></th><th class="text-end">100%</th></tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card shadow-sm">
      <div class="card-header card-header-brand">ESCRITA — DISTRIBUIÇÃO</div>
      <table class="table tbl-compact mb-0">
        <thead><tr><th>Critério</th><th class="text-end">Qtd</th><th class="text-end" style="width:90px;">%</th></tr></thead>
        <tbody>
        <?php
        $itensEsc = [
            'Ortográfico'         => $tot['esc_ortografico'],
            'Alfabético'          => $tot['esc_alfabetico'],
            'Silábico-alfabético' => $tot['esc_silabico_alfabetico'],
            'Silábico'            => $tot['esc_silabico'],
            'Pré-silábico'        => $tot['esc_pre_silabico'],
        ];
        foreach ($itensEsc as $lbl => $v): ?>
          <tr><td><?= e($lbl) ?></td><td class="text-end"><?= (int)$v ?></td><td class="text-end"><?= pct((int)$v, $totEsc) ?></td></tr>
        <?php endforeach; ?>
        <tr class="table-secondary"><th>Total</th><th class="text-end"><?= $totEsc ?></th><th class="text-end">100%</th></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="card shadow-sm mt-3">
  <div class="card-header card-header-brand">RESUMO POR POLO</div>
  <table class="table tbl-compact mb-0">
    <thead>
      <tr><th>Polo</th><th class="text-center">Auditorias</th><th class="text-center">Alunos</th><th class="text-center">Total Leitura</th><th class="text-center">Total Escrita</th></tr>
    </thead>
    <tbody>
    <?php foreach ($porPolo as $po): ?>
      <tr>
        <td><strong><?= e($po['polo_nome']) ?></strong></td>
        <td class="text-center"><?= (int)$po['qtd_audit'] ?></td>
        <td class="text-center"><?= (int)$po['tot_alunos'] ?></td>
        <td class="text-center"><?= (int)$po['tot_lei'] ?></td>
        <td class="text-center"><?= (int)$po['tot_esc'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

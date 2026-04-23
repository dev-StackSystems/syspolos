<?php
$pageTitle = 'Auditorias';

$fil_polo    = (int)($_GET['cod_polo']   ?? 0);
$fil_escola  = (int)($_GET['cod_escola'] ?? 0);
$fil_de      = $_GET['de'] ?? '';
$fil_ate     = $_GET['ate'] ?? '';
$fil_turno   = $_GET['turno'] ?? '';

$where = ['1=1']; $args = [];
if ($fil_polo > 0)   { $where[]='a.cod_polo = :polo';     $args[':polo']=$fil_polo; }
if ($fil_escola > 0) { $where[]='a.cod_escola = :esc';    $args[':esc']=$fil_escola; }
if ($fil_de !== '')  { $where[]='a.dat_realizacao >= :de'; $args[':de']=$fil_de; }
if ($fil_ate !== '') { $where[]='a.dat_realizacao <= :ate';$args[':ate']=$fil_ate; }
if ($fil_turno !== ''){ $where[]='a.ies_turno = :tu';      $args[':tu']=$fil_turno; }

$lista = db_all("
    SELECT a.*, e.escola_nome, p.polo_nome,
           (a.lei_fluencia + a.lei_sem_fluencia + a.lei_frases + a.lei_palavras + a.lei_silabas + a.lei_nao_leitor) AS tot_leitura,
           (a.esc_ortografico + a.esc_alfabetico + a.esc_silabico_alfabetico + a.esc_silabico + a.esc_pre_silabico) AS tot_escrita
      FROM auditorias a
      JOIN escolas e ON e.cod_escola = a.cod_escola
      JOIN polos   p ON p.cod_polo   = a.cod_polo
     WHERE " . implode(' AND ', $where) . "
     ORDER BY a.dat_realizacao DESC, a.cod_auditoria DESC
     LIMIT 500
", $args);

$polos   = db_all("SELECT cod_polo, polo_nome FROM polos ORDER BY polo_nome");
$escolas = db_all("SELECT cod_escola, escola_nome FROM escolas ORDER BY escola_nome");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-clipboard-check"></i> Auditorias</h4>
  <a href="<?= url('auditoria_form') ?>" class="btn btn-brand btn-sm"><i class="bi bi-plus-lg"></i> Nova auditoria</a>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <input type="hidden" name="p" value="auditorias">
      <div class="col-md-2">
        <label class="form-label">Polo</label>
        <select name="cod_polo" class="form-select form-select-sm">
          <option value="0">Todos</option>
          <?php foreach ($polos as $po): ?>
            <option value="<?= (int)$po['cod_polo'] ?>" <?= $fil_polo==$po['cod_polo']?'selected':'' ?>><?= e($po['polo_nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Escola</label>
        <select name="cod_escola" class="form-select form-select-sm">
          <option value="0">Todas</option>
          <?php foreach ($escolas as $es): ?>
            <option value="<?= (int)$es['cod_escola'] ?>" <?= $fil_escola==$es['cod_escola']?'selected':'' ?>><?= e($es['escola_nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Turno</label>
        <select name="turno" class="form-select form-select-sm">
          <option value="">Todos</option>
          <?php foreach (turnos() as $t): ?>
            <option value="<?= e($t) ?>" <?= $fil_turno===$t?'selected':'' ?>><?= e($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">De</label>
        <input type="date" name="de" class="form-control form-control-sm" value="<?= e($fil_de) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label">Até</label>
        <input type="date" name="ate" class="form-control form-control-sm" value="<?= e($fil_ate) ?>">
      </div>
      <div class="col-md-1">
        <button class="btn btn-sm btn-brand w-100"><i class="bi bi-search"></i></button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table tbl-compact mb-0">
      <thead>
        <tr>
          <th style="width:100px;">Data</th>
          <th style="width:90px;">Polo</th>
          <th>Escola</th>
          <th>Turma</th>
          <th>Turno</th>
          <th class="text-center">Alunos</th>
          <th class="text-center">Total Leitura</th>
          <th class="text-center">Total Escrita</th>
          <th style="width:140px;"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($lista as $r): ?>
        <tr>
          <td><?= fmt_date_br($r['dat_realizacao']) ?></td>
          <td><span class="badge badge-polo"><?= e($r['polo_nome']) ?></span></td>
          <td><strong><?= e($r['escola_nome']) ?></strong></td>
          <td><?= e($r['turma']) ?></td>
          <td><?= e($r['ies_turno']) ?></td>
          <td class="text-center"><?= (int)$r['qtd_alunos'] ?></td>
          <td class="text-center"><?= (int)$r['tot_leitura'] ?></td>
          <td class="text-center"><?= (int)$r['tot_escrita'] ?></td>
          <td>
            <a href="<?= url('auditoria_view', ['cod_auditoria' => $r['cod_auditoria']]) ?>" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="bi bi-eye"></i></a>
            <a href="<?= url('auditoria_form', ['cod_auditoria' => $r['cod_auditoria']]) ?>" class="btn btn-sm btn-outline-primary" title="Editar"><i class="bi bi-pencil"></i></a>
            <button class="btn btn-sm btn-outline-danger" title="Excluir" onclick="excluirAud(<?= (int)$r['cod_auditoria'] ?>)"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$lista): ?>
        <tr><td colspan="9" class="text-center text-muted p-4">Nenhuma auditoria encontrada.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
function excluirAud(cod){
  if (!confirm('Excluir esta auditoria? Esta ação não pode ser desfeita.')) return;
  var fd = new FormData();
  fd.append('cod_auditoria', cod); fd.append('ajax','1');
  fetch('?a=auditoria_excluir', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { alert(r.erro); return; }
      location.reload();
    });
}
</script>

<?php
$pageTitle = 'Escolas';

$fil_polo = (int)($_GET['cod_polo'] ?? 0);
$fil_q    = trim($_GET['q'] ?? '');

$where = ['1=1'];
$args  = [];
if ($fil_polo > 0) { $where[] = 'e.cod_polo = :polo'; $args[':polo'] = $fil_polo; }
if ($fil_q !== '') {
    $where[] = '(e.escola_nome ILIKE :q OR e.localidade ILIKE :q OR e.diretor ILIKE :q)';
    $args[':q'] = '%' . $fil_q . '%';
}

$escolas = db_all("
    SELECT e.*, p.polo_nome,
           (SELECT COUNT(*) FROM audiencias a WHERE a.cod_escola = e.cod_escola) AS qtd_audiencias
      FROM escolas e
      JOIN polos p ON p.cod_polo = e.cod_polo
     WHERE " . implode(' AND ', $where) . "
     ORDER BY p.polo_nome, e.escola_nome
", $args);

$polos = db_all("SELECT cod_polo, polo_nome FROM polos WHERE ies_ativo='S' ORDER BY polo_nome");
?>
<div class="page-title">
  <div>
    <h4><i class="bi bi-building"></i> Escolas</h4>
    <div class="muted-sub"><?= count($escolas) ?> escola(s)</div>
  </div>
  <button class="btn btn-brand btn-sm" onclick="abrirEscola(0)"><i class="bi bi-plus-lg"></i> Nova escola</button>
</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
      <input type="hidden" name="p" value="escolas">
      <div class="col-md-3">
        <label class="form-label">Polo</label>
        <select name="cod_polo" class="form-select form-select-sm">
          <option value="0">Todos</option>
          <?php foreach ($polos as $po): ?>
            <option value="<?= (int)$po['cod_polo'] ?>" <?= $fil_polo == $po['cod_polo'] ? 'selected' : '' ?>><?= e($po['polo_nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label">Buscar</label>
        <input type="text" name="q" class="form-control form-control-sm" value="<?= e($fil_q) ?>" placeholder="Nome, localidade, diretor...">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button class="btn btn-sm btn-brand flex-fill"><i class="bi bi-search"></i> Filtrar</button>
        <a href="<?= url('escolas') ?>" class="btn btn-sm btn-ghost"><i class="bi bi-x"></i></a>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <?php if ($escolas): ?>
  <table class="table tbl-compact">
    <thead>
      <tr>
        <th style="width:100px;">Polo</th>
        <th>Escola</th>
        <th>Localidade</th>
        <th>Diretor</th>
        <th>Coordenador</th>
        <th class="text-center" style="width:90px;">Audit.</th>
        <th style="width:120px;"></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($escolas as $es): ?>
      <tr>
        <td><span class="badge badge-polo"><?= e($es['polo_nome']) ?></span></td>
        <td><strong style="color:var(--ink)"><?= e($es['escola_nome']) ?></strong></td>
        <td class="text-muted-2"><?= e($es['localidade']) ?: '—' ?></td>
        <td class="text-muted-2"><?= e($es['diretor']) ?: '—' ?></td>
        <td class="text-muted-2"><?= e($es['coordenador']) ?: '—' ?></td>
        <td class="text-center"><a href="<?= url('audiencias', ['cod_escola' => $es['cod_escola']]) ?>"><?= (int)$es['qtd_audiencias'] ?></a></td>
        <td>
          <button class="btn btn-sm btn-ghost" onclick="abrirEscola(<?= (int)$es['cod_escola'] ?>)" title="Editar"><i class="bi bi-pencil"></i></button>
          <button class="btn btn-sm btn-outline-danger" onclick="excluirEscola(<?= (int)$es['cod_escola'] ?>, '<?= e($es['escola_nome']) ?>')" title="Excluir"><i class="bi bi-trash"></i></button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty">
      <div class="empty-ico"><i class="bi bi-building"></i></div>
      <h5>Nenhuma escola encontrada</h5>
      <p>Cadastre a primeira escola para começar.</p>
      <button class="btn btn-brand btn-sm" onclick="abrirEscola(0)"><i class="bi bi-plus-lg"></i> Nova escola</button>
    </div>
  <?php endif; ?>
</div>

<!-- Modal escola -->
<div class="modal fade" id="mdlEscola" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="frmEscola" onsubmit="salvarEscola(event)">
        <div class="modal-header card-header-brand">
          <h5 class="modal-title m-0"><i class="bi bi-building"></i> <span id="ttlEscola">Nova escola</span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cod_escola" id="e_cod" value="0">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Polo <span class="text-danger">*</span></label>
              <select class="form-select" name="cod_polo" id="e_polo" required>
                <option value="">Selecione...</option>
                <?php foreach ($polos as $po): ?>
                  <option value="<?= (int)$po['cod_polo'] ?>"><?= e($po['polo_nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Nome da escola <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="escola_nome" id="e_nome" required maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label">Localidade</label>
              <input type="text" class="form-control" name="localidade" id="e_loc" maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label">Diretor(a)</label>
              <input type="text" class="form-control" name="diretor" id="e_dir" maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label">Coordenador(a)</label>
              <input type="text" class="form-control" name="coordenador" id="e_coord" maxlength="200">
            </div>
            <div class="col-md-6 d-flex align-items-end">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" name="ies_ativo" id="e_ativo" value="S" checked>
                <label class="form-check-label" for="e_ativo">Ativa</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var mdlEscola;
document.addEventListener('DOMContentLoaded', function(){
  mdlEscola = new bootstrap.Modal(document.getElementById('mdlEscola'));
});

function abrirEscola(cod){
  document.getElementById('frmEscola').reset();
  document.getElementById('e_cod').value = cod || 0;
  document.getElementById('ttlEscola').textContent = cod > 0 ? 'Editar escola' : 'Nova escola';
  if (cod > 0) {
    fetch('?a=escola_get&cod_escola=' + cod).then(r => r.json()).then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      var d = r.dados;
      document.getElementById('e_polo').value  = d.cod_polo;
      document.getElementById('e_nome').value  = d.escola_nome || '';
      document.getElementById('e_loc').value   = d.localidade || '';
      document.getElementById('e_dir').value   = d.diretor || '';
      document.getElementById('e_coord').value = d.coordenador || '';
      document.getElementById('e_ativo').checked = (d.ies_ativo === 'S');
      mdlEscola.show();
    });
  } else {
    mdlEscola.show();
  }
}

function salvarEscola(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmEscola'));
  fd.append('ajax','1');
  fetch('?a=escola_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      mdlEscola.hide();
      toast('Escola salva.', 'ok');
      setTimeout(function(){ location.reload(); }, 400);
    });
}

function excluirEscola(cod, nome){
  if (!confirm('Excluir a escola "' + nome + '"?\nSó é possível se não houver audiências vinculadas.')) return;
  var fd = new FormData();
  fd.append('cod_escola', cod); fd.append('ajax','1');
  fetch('?a=escola_excluir', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      toast('Escola excluída.', 'ok');
      setTimeout(function(){ location.reload(); }, 400);
    });
}
</script>

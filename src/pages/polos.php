<?php
/** @var string $pageTitle */
$pageTitle = 'Polos';

$polos = db_all("
    SELECT p.*,
           (SELECT COUNT(*) FROM escolas e WHERE e.cod_polo = p.cod_polo) AS qtd_escolas,
           (SELECT COUNT(*) FROM auditorias a WHERE a.cod_polo = p.cod_polo) AS qtd_auditorias
      FROM polos p
     ORDER BY p.polo_nome
");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-geo-alt"></i> Polos</h4>
  <button class="btn btn-brand btn-sm" onclick="abrirPolo(0)"><i class="bi bi-plus-lg"></i> Novo polo</button>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table tbl-compact mb-0">
      <thead>
        <tr>
          <th style="width:60px;">#</th>
          <th>Nome</th>
          <th class="text-center" style="width:120px;">Escolas</th>
          <th class="text-center" style="width:120px;">Auditorias</th>
          <th class="text-center" style="width:100px;">Status</th>
          <th style="width:140px;"></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($polos as $po): ?>
        <tr>
          <td><?= (int)$po['cod_polo'] ?></td>
          <td><strong><?= e($po['polo_nome']) ?></strong></td>
          <td class="text-center"><a href="<?= url('escolas', ['cod_polo' => $po['cod_polo']]) ?>"><?= (int)$po['qtd_escolas'] ?></a></td>
          <td class="text-center"><a href="<?= url('auditorias', ['cod_polo' => $po['cod_polo']]) ?>"><?= (int)$po['qtd_auditorias'] ?></a></td>
          <td class="text-center">
            <?php if ($po['ies_ativo'] === 'S'): ?>
              <span class="badge bg-success">Ativo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Inativo</span>
            <?php endif; ?>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" onclick="abrirPolo(<?= (int)$po['cod_polo'] ?>)"><i class="bi bi-pencil"></i></button>
            <button class="btn btn-sm btn-outline-danger" onclick="excluirPolo(<?= (int)$po['cod_polo'] ?>, '<?= e($po['polo_nome']) ?>')"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$polos): ?>
        <tr><td colspan="6" class="text-center text-muted p-4">Nenhum polo cadastrado.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal polo -->
<div class="modal fade" id="mdlPolo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="frmPolo" onsubmit="salvarPolo(event)">
        <div class="modal-header card-header-brand">
          <h5 class="modal-title">Polo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cod_polo" id="f_cod_polo" value="0">
          <div class="mb-3">
            <label class="form-label">Nome do polo *</label>
            <input type="text" class="form-control" name="polo_nome" id="f_polo_nome" required maxlength="100">
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ies_ativo" id="f_polo_ativo" value="S" checked>
            <label class="form-check-label" for="f_polo_ativo">Ativo</label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var mdlPolo;
document.addEventListener('DOMContentLoaded', function(){
  mdlPolo = new bootstrap.Modal(document.getElementById('mdlPolo'));
});

function abrirPolo(cod){
  document.getElementById('frmPolo').reset();
  document.getElementById('f_cod_polo').value = cod || 0;
  if (cod > 0) {
    fetch('?a=polo_get&cod_polo=' + cod).then(r => r.json()).then(function(r){
      if (r.erro) { alert(r.erro); return; }
      document.getElementById('f_polo_nome').value = r.dados.polo_nome || '';
      document.getElementById('f_polo_ativo').checked = (r.dados.ies_ativo === 'S');
      mdlPolo.show();
    });
  } else {
    mdlPolo.show();
  }
}

function salvarPolo(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmPolo'));
  fd.append('ajax','1');
  fetch('?a=polo_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { alert(r.erro); return; }
      mdlPolo.hide();
      location.reload();
    });
}

function excluirPolo(cod, nome){
  if (!confirm('Excluir o polo "' + nome + '"?\nSó é possível se não houver escolas vinculadas.')) return;
  var fd = new FormData();
  fd.append('cod_polo', cod);
  fd.append('ajax','1');
  fetch('?a=polo_excluir', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { alert(r.erro); return; }
      location.reload();
    });
}
</script>

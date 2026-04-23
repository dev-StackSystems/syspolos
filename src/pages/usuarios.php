<?php
$pageTitle = 'Usuários';

$lista = db_all("
    SELECT cod_usuario, email, nome, ies_ativo, dat_cadastro, dat_ultimo_login
      FROM sys_usuarios
     ORDER BY nome
");
?>
<div class="page-title">
  <div>
    <h4><i class="bi bi-people"></i> Usuários do sistema</h4>
    <div class="muted-sub"><?= count($lista) ?> usuário(s) cadastrado(s)</div>
  </div>
  <button class="btn btn-brand btn-sm" onclick="abrirUsr(0)"><i class="bi bi-plus-lg"></i> Novo usuário</button>
</div>

<div class="card">
  <?php if ($lista): ?>
  <table class="table tbl-compact">
    <thead>
      <tr>
        <th>Nome</th>
        <th>Email</th>
        <th class="text-center" style="width:100px;">Status</th>
        <th style="width:130px;">Último login</th>
        <th style="width:120px;"></th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($lista as $u): ?>
      <tr>
        <td><strong style="color:var(--ink)"><?= e($u['nome']) ?></strong></td>
        <td class="text-muted-2"><?= e($u['email']) ?></td>
        <td class="text-center">
          <?php if ($u['ies_ativo'] === 'S'): ?>
            <span class="badge badge-ok">Ativo</span>
          <?php else: ?>
            <span class="badge badge-off">Inativo</span>
          <?php endif; ?>
        </td>
        <td class="text-muted-2" style="font-size:.82rem">
          <?= $u['dat_ultimo_login'] ? fmt_date_br($u['dat_ultimo_login']) : '—' ?>
        </td>
        <td>
          <button class="btn btn-sm btn-ghost" onclick="abrirUsr(<?= (int)$u['cod_usuario'] ?>)" title="Editar"><i class="bi bi-pencil"></i></button>
          <?php if ($u['email'] !== auth_user()['email']): ?>
            <button class="btn btn-sm btn-outline-danger" onclick="excluirUsr(<?= (int)$u['cod_usuario'] ?>, '<?= e($u['nome']) ?>')" title="Excluir"><i class="bi bi-trash"></i></button>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
    <div class="empty"><div class="empty-ico"><i class="bi bi-people"></i></div><h5>Nenhum usuário</h5></div>
  <?php endif; ?>
</div>

<!-- Modal usuário -->
<div class="modal fade" id="mdlUsr" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="frmUsr" onsubmit="salvarUsr(event)">
        <div class="modal-header card-header-brand">
          <h5 class="modal-title m-0"><i class="bi bi-person"></i> <span id="ttlUsr">Novo usuário</span></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="cod_usuario" id="u_cod" value="0">
          <div class="mb-3">
            <label class="form-label">Nome <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nome" id="u_nome" required maxlength="200">
          </div>
          <div class="mb-3">
            <label class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" name="email" id="u_email" required maxlength="200">
          </div>
          <div class="mb-3">
            <label class="form-label">Senha <span id="u_senha_hint" class="text-danger">*</span></label>
            <input type="password" class="form-control" name="senha" id="u_senha" minlength="4" maxlength="100">
            <div class="form-text" style="font-size:.75rem;" id="u_senha_help">Mínimo 4 caracteres. Na edição, deixe em branco para não alterar.</div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="ies_ativo" id="u_ativo" value="S" checked>
            <label class="form-check-label" for="u_ativo">Ativo</label>
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
var mdlUsr;
document.addEventListener('DOMContentLoaded', function(){
  mdlUsr = new bootstrap.Modal(document.getElementById('mdlUsr'));
});

function abrirUsr(cod){
  document.getElementById('frmUsr').reset();
  document.getElementById('u_cod').value = cod || 0;
  var isEdit = cod > 0;
  document.getElementById('ttlUsr').textContent = isEdit ? 'Editar usuário' : 'Novo usuário';
  document.getElementById('u_senha').required = !isEdit;
  document.getElementById('u_senha_hint').style.display = isEdit ? 'none' : 'inline';

  if (isEdit) {
    fetch('?a=usuario_get&cod_usuario=' + cod).then(r => r.json()).then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      var d = r.dados;
      document.getElementById('u_nome').value  = d.nome || '';
      document.getElementById('u_email').value = d.email || '';
      document.getElementById('u_ativo').checked = (d.ies_ativo === 'S');
      mdlUsr.show();
    });
  } else {
    mdlUsr.show();
  }
}

function salvarUsr(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmUsr'));
  fd.append('ajax','1');
  fetch('?a=usuario_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      mdlUsr.hide();
      toast('Usuário salvo.', 'ok');
      setTimeout(function(){ location.reload(); }, 400);
    });
}

function excluirUsr(cod, nome){
  if (!confirm('Excluir o usuário "' + nome + '"?')) return;
  var fd = new FormData();
  fd.append('cod_usuario', cod); fd.append('ajax','1');
  fetch('?a=usuario_excluir', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      toast('Usuário excluído.', 'ok');
      setTimeout(function(){ location.reload(); }, 400);
    });
}
</script>

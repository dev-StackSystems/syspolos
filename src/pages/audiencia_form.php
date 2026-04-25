<?php
$cod = (int)($_GET['cod_audiencia'] ?? 0);
$pageTitle = $cod ? 'Editar audiencia' : 'N';

$a = [
    'cod_audiencia' => 0, 'cod_escola' => 0, 'cod_polo' => 0,
    'dat_realizacao' => date('Y-m-d'), 'ies_turno' => 'Manhã', 'turma' => '',
    'qtd_alunos' => 0, 'qtd_pcd' => 0, 'tecnico_responsavel' => '',
    'lei_fluencia' => 0, 'lei_sem_fluencia' => 0, 'lei_frases' => 0,
    'lei_palavras' => 0, 'lei_silabas' => 0, 'lei_nao_leitor' => 0,
    'esc_ortografico' => 0, 'esc_alfabetico' => 0, 'esc_silabico_alfabetico' => 0,
    'esc_silabico' => 0, 'esc_pre_silabico' => 0,
    'txt_conclusao' => '',
];
if ($cod > 0) {
    $rowA = db_one("SELECT * FROM audiencias WHERE cod_audiencia = :c", [':c' => $cod]);
    if (!$rowA) {
        echo '<div class="alert alert-warning">Audiência não encontrada.</div>';
        return;
    }
    $a = array_merge($a, $rowA);
}

$polos   = db_all("SELECT cod_polo, polo_nome FROM polos WHERE ies_ativo='S' ORDER BY polo_nome");
$escolas = db_all("SELECT cod_escola, cod_polo, escola_nome FROM escolas WHERE ies_ativo='S' ORDER BY escola_nome");
?>
<div class="page-title">
  <div>
    <h4><i class="bi bi-<?= $cod ? 'pencil-square' : 'clipboard-plus' ?>"></i> <?= e($pageTitle) ?></h4>
    <div class="muted-sub">Preencha os critérios de leitura e escrita da turma avaliada.</div>
  </div>
  <a href="<?= url('audiencias') ?>" class="btn btn-ghost btn-sm"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<form id="frmAud" onsubmit="salvarAud(event)">
  <input type="hidden" name="cod_audiencia" value="<?= (int)$a['cod_audiencia'] ?>">

  <!-- Identificação -->
  <div class="card mb-3">
    <div class="card-header-brand"><i class="bi bi-bookmark-check"></i> Identificação</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Polo <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-select" name="cod_polo" id="f_polo" required onchange="filtrarEscolas()">
              <option value="">Selecione...</option>
              <?php foreach ($polos as $po): ?>
                <option value="<?= (int)$po['cod_polo'] ?>" <?= $a['cod_polo']==$po['cod_polo']?'selected':'' ?>><?= e($po['polo_nome']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-ghost" title="Criar polo" onclick="abrirNovoPolo()"><i class="bi bi-plus-lg"></i></button>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Escola <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-select" name="cod_escola" id="f_escola" required>
              <option value="">Selecione o polo primeiro...</option>
              <?php foreach ($escolas as $es): ?>
                <option value="<?= (int)$es['cod_escola'] ?>" data-polo="<?= (int)$es['cod_polo'] ?>" <?= $a['cod_escola']==$es['cod_escola']?'selected':'' ?>><?= e($es['escola_nome']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-ghost" title="Criar escola" onclick="abrirNovaEscola()"><i class="bi bi-plus-lg"></i></button>
          </div>
          <div class="form-text" style="font-size:.75rem;">Dica: use o botão <b>+</b> para cadastrar um polo ou escola novo sem sair deste formulário.</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Data de realização <span class="text-danger">*</span></label>
          <input type="date" class="form-control" name="dat_realizacao" value="<?= e($a['dat_realizacao']) ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Turno <span class="text-danger">*</span></label>
          <select class="form-select" name="ies_turno" required>
            <?php foreach (turnos() as $t): ?>
              <option value="<?= e($t) ?>" <?= $a['ies_turno']===$t?'selected':'' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Turma <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="turma" value="<?= e($a['turma']) ?>" maxlength="80" required placeholder="Ex.: 2º ano">
        </div>
        <div class="col-md-2">
          <label class="form-label">Qtd alunos</label>
          <input type="number" class="form-control" name="qtd_alunos" value="<?= (int)$a['qtd_alunos'] ?>" min="0">
        </div>
        <div class="col-md-2">
          <label class="form-label">PCD</label>
          <input type="number" class="form-control" name="qtd_pcd" value="<?= (int)$a['qtd_pcd'] ?>" min="0">
        </div>
        <div class="col-md-3">
          <label class="form-label">Técnico(s) responsável(is)</label>
          <input type="text" class="form-control" name="tecnico_responsavel" value="<?= e($a['tecnico_responsavel']) ?>" maxlength="300">
        </div>
      </div>
    </div>
  </div>

  <!-- Leitura -->
  <div class="card mb-3">
    <div class="card-header-brand d-flex justify-content-between">
      <span><i class="bi bi-book"></i> Critérios de leitura</span>
      <span class="total-badge" style="background:rgba(255,255,255,.2); color:#fff;">Total: <span id="tot_lei">0</span></span>
    </div>
    <div class="card-body counter-group">
      <div class="row g-3">
        <?php
        $leit = [
            'lei_fluencia'     => ['Com fluência', 'bi-check-circle', 'Leitura de texto com fluência'],
            'lei_sem_fluencia' => ['Sem fluência', 'bi-dash-circle', 'Leitura de texto sem fluência'],
            'lei_frases'       => ['Frases', 'bi-chat-quote', 'Leitura de frases'],
            'lei_palavras'     => ['Palavras', 'bi-card-text', 'Leitura de palavras'],
            'lei_silabas'      => ['Sílabas', 'bi-type', 'Leitura de sílabas'],
            'lei_nao_leitor'   => ['Não leitor', 'bi-x-circle', 'Não leitor'],
        ];
        foreach ($leit as $k => [$lbl, $ico, $full]): ?>
          <div class="col-md-4 col-lg-2">
            <div class="counter-wrap">
              <label class="form-label" title="<?= e($full) ?>"><i class="bi <?= $ico ?>"></i> <?= e($lbl) ?></label>
              <input type="number" class="form-control lei" name="<?= $k ?>" value="<?= (int)$a[$k] ?>" min="0" onchange="calcTotais()">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Escrita -->
  <div class="card mb-3">
    <div class="card-header-brand d-flex justify-content-between">
      <span><i class="bi bi-pen"></i> Critérios de escrita</span>
      <span class="total-badge" style="background:rgba(255,255,255,.2); color:#fff;">Total: <span id="tot_esc">0</span></span>
    </div>
    <div class="card-body counter-group">
      <div class="row g-3">
        <?php
        $esc = [
            'esc_ortografico'         => ['Ortográfico', 'bi-award'],
            'esc_alfabetico'          => ['Alfabético', 'bi-alphabet'],
            'esc_silabico_alfabetico' => ['Silábico-alfab.', 'bi-layers'],
            'esc_silabico'            => ['Silábico', 'bi-grid-3x2'],
            'esc_pre_silabico'        => ['Pré-silábico', 'bi-dot'],
        ];
        foreach ($esc as $k => [$lbl, $ico]): ?>
          <div class="col-md-4 col-lg-2">
            <div class="counter-wrap">
              <label class="form-label"><i class="bi <?= $ico ?>"></i> <?= e($lbl) ?></label>
              <input type="number" class="form-control esc" name="<?= $k ?>" value="<?= (int)$a[$k] ?>" min="0" onchange="calcTotais()">
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Conclusão -->
  <div class="card mb-3">
    <div class="card-header-brand"><i class="bi bi-chat-left-text"></i> Conclusão e parecer técnico</div>
    <div class="card-body">
      <textarea class="form-control" name="txt_conclusao" rows="5" maxlength="5000" placeholder="Observações, diagnóstico, recomendações..."><?= e($a['txt_conclusao']) ?></textarea>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end">
    <a href="<?= url('audiencias') ?>" class="btn btn-ghost">Cancelar</a>
    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Salvar audiencia</button>
  </div>
</form>

<!-- Modal: Novo Polo -->
<div class="modal fade" id="mdlPolo" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="frmNovoPolo" onsubmit="salvarNovoPolo(event)">
        <div class="modal-header card-header-brand">
          <h5 class="modal-title m-0"><i class="bi bi-geo-alt"></i> Novo polo</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Nome do polo <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="polo_nome" id="np_nome" required maxlength="100" placeholder="Ex.: POLO 9">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Criar e usar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Nova Escola -->
<div class="modal fade" id="mdlEscola" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="frmNovaEscola" onsubmit="salvarNovaEscola(event)">
        <div class="modal-header card-header-brand">
          <h5 class="modal-title m-0"><i class="bi bi-building"></i> Nova escola</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Polo <span class="text-danger">*</span></label>
              <select class="form-select" name="cod_polo" id="ne_polo" required>
                <option value="">Selecione...</option>
                <?php foreach ($polos as $po): ?>
                  <option value="<?= (int)$po['cod_polo'] ?>"><?= e($po['polo_nome']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Nome da escola <span class="text-danger">*</span></label>
              <input type="text" class="form-control" name="escola_nome" id="ne_nome" required maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label">Localidade</label>
              <input type="text" class="form-control" name="localidade" id="ne_loc" maxlength="200">
            </div>
            <div class="col-md-6">
              <label class="form-label">Diretor(a)</label>
              <input type="text" class="form-control" name="diretor" id="ne_dir" maxlength="200">
            </div>
            <div class="col-md-12">
              <label class="form-label">Coordenador(a)</label>
              <input type="text" class="form-control" name="coordenador" id="ne_coord" maxlength="200">
            </div>
            <input type="hidden" name="ies_ativo" value="S">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Criar e usar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
var mdlPolo, mdlEscola;
document.addEventListener('DOMContentLoaded', function(){
  mdlPolo   = new bootstrap.Modal(document.getElementById('mdlPolo'));
  mdlEscola = new bootstrap.Modal(document.getElementById('mdlEscola'));
  filtrarEscolas();
  calcTotais();
});

function filtrarEscolas(){
  var polo = document.getElementById('f_polo').value;
  var sel  = document.getElementById('f_escola');
  Array.from(sel.options).forEach(function(op){
    if (!op.value) { op.hidden = false; return; }
    op.hidden = (polo && op.dataset.polo !== polo);
  });
  var cur = sel.options[sel.selectedIndex];
  if (cur && cur.hidden) sel.value = '';
}

function calcTotais(){
  var l=0, es=0;
  document.querySelectorAll('.lei').forEach(function(i){ l += parseInt(i.value||0,10); });
  document.querySelectorAll('.esc').forEach(function(i){ es += parseInt(i.value||0,10); });
  document.getElementById('tot_lei').textContent = l;
  document.getElementById('tot_esc').textContent = es;
}

// ===== criação inline de polo =====
function abrirNovoPolo(){
  document.getElementById('frmNovoPolo').reset();
  mdlPolo.show();
  setTimeout(function(){ document.getElementById('np_nome').focus(); }, 300);
}
function salvarNovoPolo(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmNovoPolo'));
  fd.append('ajax','1');
  fetch('?a=polo_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      var nome = fd.get('polo_nome');
      // adiciona no select principal e no select do modal de escola
      ['f_polo','ne_polo'].forEach(function(id){
        var sel = document.getElementById(id);
        var op = new Option(nome, r.cod_polo);
        sel.appendChild(op);
      });
      document.getElementById('f_polo').value = r.cod_polo;
      filtrarEscolas();
      mdlPolo.hide();
      toast('Polo <b>' + nome + '</b> criado.', 'ok');
    });
}

// ===== criação inline de escola =====
function abrirNovaEscola(){
  document.getElementById('frmNovaEscola').reset();
  // herda polo já selecionado no formulário principal, se houver
  var polo = document.getElementById('f_polo').value;
  if (polo) document.getElementById('ne_polo').value = polo;
  mdlEscola.show();
  setTimeout(function(){ document.getElementById('ne_nome').focus(); }, 300);
}
function salvarNovaEscola(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmNovaEscola'));
  fd.append('ajax','1');
  fetch('?a=escola_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { toast(r.erro, 'erro'); return; }
      var nome = fd.get('escola_nome');
      var codPolo = fd.get('cod_polo');
      // adiciona no select principal
      var sel = document.getElementById('f_escola');
      var op = new Option(nome, r.cod_escola);
      op.dataset.polo = codPolo;
      sel.appendChild(op);
      // seleciona polo e escola automaticamente
      document.getElementById('f_polo').value = codPolo;
      filtrarEscolas();
      sel.value = r.cod_escola;
      mdlEscola.hide();
      toast('Escola <b>' + nome + '</b> criada e selecionada.', 'ok');
    });
}

function salvarAud(ev){
  ev.preventDefault();
  var btn = ev.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Salvando...';
  var fd = new FormData(document.getElementById('frmAud'));
  fd.append('ajax','1');
  fetch('?a=audiencia_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) {
        toast(r.erro, 'erro');
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Salvar audiencia';
        return;
      }
      location.href = '?p=audiencia_view&cod_audiencia=' + r.cod_audiencia;
    })
    .catch(function(){
      toast('Erro de rede.', 'erro');
      btn.disabled = false; btn.innerHTML = '<i class="bi bi-check-lg"></i> Salvar audiencia';
    });
}
</script>

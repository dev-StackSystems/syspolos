<?php
$cod = (int)($_GET['cod_auditoria'] ?? 0);
$pageTitle = $cod ? 'Editar auditoria' : 'Nova auditoria';

$a = [
    'cod_auditoria' => 0, 'cod_escola' => 0, 'cod_polo' => 0,
    'dat_realizacao' => date('Y-m-d'), 'ies_turno' => 'Manhã', 'turma' => '',
    'qtd_alunos' => 0, 'qtd_pcd' => 0, 'tecnico_responsavel' => '',
    'lei_fluencia' => 0, 'lei_sem_fluencia' => 0, 'lei_frases' => 0,
    'lei_palavras' => 0, 'lei_silabas' => 0, 'lei_nao_leitor' => 0,
    'esc_ortografico' => 0, 'esc_alfabetico' => 0, 'esc_silabico_alfabetico' => 0,
    'esc_silabico' => 0, 'esc_pre_silabico' => 0,
    'txt_conclusao' => '',
];
if ($cod > 0) {
    $rowA = db_one("SELECT * FROM auditorias WHERE cod_auditoria = :c", [':c' => $cod]);
    if (!$rowA) {
        echo '<div class="alert alert-warning">Auditoria não encontrada.</div>';
        return;
    }
    $a = array_merge($a, $rowA);
}

$polos   = db_all("SELECT cod_polo, polo_nome FROM polos WHERE ies_ativo='S' ORDER BY polo_nome");
$escolas = db_all("SELECT cod_escola, cod_polo, escola_nome FROM escolas WHERE ies_ativo='S' ORDER BY escola_nome");
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0"><i class="bi bi-clipboard-plus"></i> <?= e($pageTitle) ?></h4>
  <a href="<?= url('auditorias') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
</div>

<form id="frmAud" onsubmit="salvarAud(event)">
  <input type="hidden" name="cod_auditoria" value="<?= (int)$a['cod_auditoria'] ?>">

  <!-- Identificação -->
  <div class="card shadow-sm mb-3">
    <div class="card-header card-header-brand">IDENTIFICAÇÃO</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-3">
          <label class="form-label">Polo *</label>
          <select class="form-select" name="cod_polo" id="f_polo" required onchange="filtrarEscolas()">
            <option value="">Selecione...</option>
            <?php foreach ($polos as $po): ?>
              <option value="<?= (int)$po['cod_polo'] ?>" <?= $a['cod_polo']==$po['cod_polo']?'selected':'' ?>><?= e($po['polo_nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Escola *</label>
          <select class="form-select" name="cod_escola" id="f_escola" required>
            <option value="">Selecione o polo primeiro...</option>
            <?php foreach ($escolas as $es): ?>
              <option value="<?= (int)$es['cod_escola'] ?>" data-polo="<?= (int)$es['cod_polo'] ?>" <?= $a['cod_escola']==$es['cod_escola']?'selected':'' ?>><?= e($es['escola_nome']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Data de realização *</label>
          <input type="date" class="form-control" name="dat_realizacao" value="<?= e($a['dat_realizacao']) ?>" required>
        </div>
        <div class="col-md-2">
          <label class="form-label">Turno *</label>
          <select class="form-select" name="ies_turno" required>
            <?php foreach (turnos() as $t): ?>
              <option value="<?= e($t) ?>" <?= $a['ies_turno']===$t?'selected':'' ?>><?= e($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Turma *</label>
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
  <div class="card shadow-sm mb-3">
    <div class="card-header card-header-brand">CRITÉRIOS DE LEITURA</div>
    <div class="card-body counter-group">
      <div class="row g-3">
        <?php
        $leit = [
            'lei_fluencia'     => 'Leitura de texto COM fluência',
            'lei_sem_fluencia' => 'Leitura de texto SEM fluência',
            'lei_frases'       => 'Leitura de frases',
            'lei_palavras'     => 'Leitura de palavras',
            'lei_silabas'      => 'Leitura de sílabas',
            'lei_nao_leitor'   => 'Não leitor',
        ];
        foreach ($leit as $k => $lbl): ?>
          <div class="col-md-4 col-lg-2">
            <label class="form-label"><?= e($lbl) ?></label>
            <input type="number" class="form-control lei" name="<?= $k ?>" value="<?= (int)$a[$k] ?>" min="0" onchange="calcTotais()">
          </div>
        <?php endforeach; ?>
      </div>
      <div class="text-end mt-2">
        <span class="badge bg-dark">Total leitura: <span id="tot_lei">0</span></span>
      </div>
    </div>
  </div>

  <!-- Escrita -->
  <div class="card shadow-sm mb-3">
    <div class="card-header card-header-brand">CRITÉRIOS DE ESCRITA</div>
    <div class="card-body counter-group">
      <div class="row g-3">
        <?php
        $esc = [
            'esc_ortografico'         => 'Ortográfico',
            'esc_alfabetico'          => 'Alfabético',
            'esc_silabico_alfabetico' => 'Silábico-alfabético',
            'esc_silabico'            => 'Silábico',
            'esc_pre_silabico'        => 'Pré-silábico',
        ];
        foreach ($esc as $k => $lbl): ?>
          <div class="col-md-4 col-lg-2">
            <label class="form-label"><?= e($lbl) ?></label>
            <input type="number" class="form-control esc" name="<?= $k ?>" value="<?= (int)$a[$k] ?>" min="0" onchange="calcTotais()">
          </div>
        <?php endforeach; ?>
      </div>
      <div class="text-end mt-2">
        <span class="badge bg-dark">Total escrita: <span id="tot_esc">0</span></span>
      </div>
    </div>
  </div>

  <!-- Conclusão -->
  <div class="card shadow-sm mb-3">
    <div class="card-header card-header-brand">CONCLUSÃO E PARECER TÉCNICO</div>
    <div class="card-body">
      <textarea class="form-control" name="txt_conclusao" rows="5" maxlength="5000"><?= e($a['txt_conclusao']) ?></textarea>
    </div>
  </div>

  <div class="d-flex gap-2 justify-content-end">
    <a href="<?= url('auditorias') ?>" class="btn btn-outline-secondary">Cancelar</a>
    <button type="submit" class="btn btn-brand"><i class="bi bi-check-lg"></i> Salvar auditoria</button>
  </div>
</form>

<script>
function filtrarEscolas(){
  var polo = document.getElementById('f_polo').value;
  var sel  = document.getElementById('f_escola');
  var atual = sel.value;
  Array.from(sel.options).forEach(function(op){
    if (!op.value) { op.hidden = false; return; }
    op.hidden = (polo && op.dataset.polo !== polo);
  });
  // se escola atual não pertence ao polo, reseta
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
function salvarAud(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmAud'));
  fd.append('ajax','1');
  fetch('?a=auditoria_salvar', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) { alert(r.erro); return; }
      location.href = '?p=auditoria_view&cod_auditoria=' + r.cod_auditoria;
    })
    .catch(function(){ alert('Erro de rede.'); });
}
document.addEventListener('DOMContentLoaded', function(){
  filtrarEscolas(); calcTotais();
});
</script>

<?php
$pageTitle = 'Importar XLSX';
?>
<h4 class="mb-3"><i class="bi bi-upload"></i> Importar planilha de auditoria</h4>

<div class="card shadow-sm">
  <div class="card-header card-header-brand">Upload</div>
  <div class="card-body">
    <p class="text-muted">
      Faça upload do arquivo <strong>AUDIÊNCIA DE LEITURA.xlsx</strong> (ou similar).
      O sistema lê todas as abas (POLO 1..8) e cria automaticamente polos, escolas e auditorias.
      Fichas com escola já cadastrada serão <strong>atualizadas</strong> (não duplicadas).
    </p>

    <form id="frmImp" enctype="multipart/form-data" onsubmit="enviarXlsx(event)">
      <div class="mb-3">
        <label class="form-label">Arquivo .xlsx</label>
        <input type="file" class="form-control" name="arquivo" id="f_arq" accept=".xlsx" required>
      </div>
      <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="dry_run" id="f_dry" value="1" checked>
        <label class="form-check-label" for="f_dry">Simular apenas (não grava no banco) — use para revisar antes</label>
      </div>
      <button type="submit" class="btn btn-brand"><i class="bi bi-play-fill"></i> Processar</button>
    </form>
  </div>
</div>

<div id="resultado" class="mt-4"></div>

<script>
function enviarXlsx(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmImp'));
  fd.append('ajax','1');
  document.getElementById('resultado').innerHTML =
    '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processando...</div>';
  fetch('?a=importar_xlsx', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      if (r.erro) {
        document.getElementById('resultado').innerHTML =
          '<div class="alert alert-danger"><strong>Erro:</strong> ' + r.erro + '</div>';
        return;
      }
      var html = '<div class="card shadow-sm"><div class="card-header card-header-brand">Resultado</div><div class="card-body">';
      html += '<p><strong>' + (r.dry_run ? 'Simulação' : 'Importação concluída') + '</strong></p>';
      html += '<ul>';
      html += '<li>Polos processados: ' + r.resumo.polos + '</li>';
      html += '<li>Escolas novas: ' + r.resumo.escolas_novas + ' · atualizadas: ' + r.resumo.escolas_atualizadas + '</li>';
      html += '<li>Auditorias novas: ' + r.resumo.auditorias_novas + ' · atualizadas: ' + r.resumo.auditorias_atualizadas + '</li>';
      if (r.resumo.avisos && r.resumo.avisos.length) {
        html += '<li class="text-warning">Avisos: ' + r.resumo.avisos.length + '</li>';
      }
      html += '</ul>';
      if (r.detalhes) {
        html += '<hr><h6>Detalhes</h6><div style="max-height:420px;overflow:auto;"><table class="table table-sm tbl-compact"><thead><tr><th>Polo</th><th>Escola</th><th>Turma</th><th>Data</th><th>Status</th></tr></thead><tbody>';
        r.detalhes.forEach(function(d){
          html += '<tr><td>'+d.polo+'</td><td>'+d.escola+'</td><td>'+(d.turma||'')+'</td><td>'+(d.data||'')+'</td><td><span class="badge bg-'+(d.status==='nova'?'success':(d.status==='atualizada'?'info':'secondary'))+'">'+d.status+'</span></td></tr>';
        });
        html += '</tbody></table></div>';
      }
      if (r.resumo.avisos && r.resumo.avisos.length) {
        html += '<hr><h6 class="text-warning">Avisos</h6><ul>';
        r.resumo.avisos.forEach(function(a){ html += '<li>'+a+'</li>'; });
        html += '</ul>';
      }
      if (r.dry_run) {
        html += '<hr><p>Para importar de verdade, desmarque a caixa "Simular apenas" e envie novamente.</p>';
      } else {
        html += '<hr><a href="?p=auditorias" class="btn btn-brand btn-sm"><i class="bi bi-arrow-right"></i> Ver auditorias</a>';
      }
      html += '</div></div>';
      document.getElementById('resultado').innerHTML = html;
    })
    .catch(function(e){
      document.getElementById('resultado').innerHTML =
        '<div class="alert alert-danger">Erro de rede: ' + e.message + '</div>';
    });
}
</script>

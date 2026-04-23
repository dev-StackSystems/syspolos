<?php
$pageTitle = 'Importar XLSX';
?>
<div class="page-title">
  <div>
    <h4><i class="bi bi-cloud-upload"></i> Importar XLSX</h4>
    <div class="muted-sub">Carregue o arquivo de auditorias e o sistema cria polos, escolas e auditorias automaticamente.</div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-upload" style="color:var(--brand-600)"></i> Upload do arquivo</div>
      <div class="card-body">
        <form id="frmImp" enctype="multipart/form-data" onsubmit="enviarXlsx(event)">
          <div class="mb-3">
            <label class="form-label">Arquivo .xlsx</label>
            <input type="file" class="form-control" name="arquivo" id="f_arq" accept=".xlsx" required>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="dry_run" id="f_dry" value="1" checked>
            <label class="form-check-label" for="f_dry"><strong>Simular apenas</strong> — não grava no banco (use para revisar antes)</label>
          </div>
          <button type="submit" class="btn btn-brand"><i class="bi bi-play-fill"></i> Processar</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header"><i class="bi bi-info-circle" style="color:var(--brand-600)"></i> Como funciona</div>
      <div class="card-body" style="font-size:.88rem; color:var(--ink-2); line-height:1.6;">
        <p>O importador lê <b>todas as abas</b> da planilha (POLO 1..8) e extrai as fichas preenchidas.</p>
        <ul class="mb-0 ps-3">
          <li>Fichas sem <b>data</b>, <b>escola</b> ou <b>turma</b> são ignoradas.</li>
          <li>Escolas já cadastradas são <b>atualizadas</b>, não duplicadas.</li>
          <li>Auditorias duplicadas (mesma escola + data + turma) são <b>atualizadas</b>.</li>
          <li>Sempre <b>simule primeiro</b> para revisar o que será importado.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<div id="resultado" class="mt-3"></div>

<script>
function enviarXlsx(ev){
  ev.preventDefault();
  var fd = new FormData(document.getElementById('frmImp'));
  fd.append('ajax','1');
  var btn = ev.target.querySelector('button[type=submit]');
  btn.disabled = true; btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processando...';
  document.getElementById('resultado').innerHTML =
    '<div class="alert alert-info"><i class="bi bi-hourglass-split"></i> Processando o arquivo, aguarde...</div>';
  fetch('?a=importar_xlsx', { method:'POST', body:fd })
    .then(r => r.json())
    .then(function(r){
      btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill"></i> Processar';
      if (r.erro) {
        document.getElementById('resultado').innerHTML =
          '<div class="alert alert-danger"><strong>Erro:</strong> ' + r.erro + '</div>';
        return;
      }
      var html = '<div class="card">';
      html += '<div class="card-header-brand"><i class="bi bi-check-circle"></i> ' + (r.dry_run ? 'Simulação concluída' : 'Importação concluída') + '</div>';
      html += '<div class="card-body">';
      html += '<div class="row g-3 mb-3">';
      html += '<div class="col-md-3"><div class="kpi-card"><div class="kpi-ico ind"><i class="bi bi-geo-alt"></i></div><div><div class="kpi-num">' + r.resumo.polos + '</div><div class="kpi-lbl">Polos processados</div></div></div></div>';
      html += '<div class="col-md-3"><div class="kpi-card"><div class="kpi-ico emr"><i class="bi bi-building"></i></div><div><div class="kpi-num">' + (r.resumo.escolas_novas + r.resumo.escolas_atualizadas) + '</div><div class="kpi-lbl">Escolas (' + r.resumo.escolas_novas + ' novas)</div></div></div></div>';
      html += '<div class="col-md-3"><div class="kpi-card"><div class="kpi-ico amb"><i class="bi bi-clipboard-check"></i></div><div><div class="kpi-num">' + (r.resumo.auditorias_novas + r.resumo.auditorias_atualizadas) + '</div><div class="kpi-lbl">Auditorias (' + r.resumo.auditorias_novas + ' novas)</div></div></div></div>';
      html += '<div class="col-md-3"><div class="kpi-card"><div class="kpi-ico vio"><i class="bi bi-exclamation-triangle"></i></div><div><div class="kpi-num">' + (r.resumo.avisos ? r.resumo.avisos.length : 0) + '</div><div class="kpi-lbl">Avisos</div></div></div></div>';
      html += '</div>';
      if (r.detalhes && r.detalhes.length) {
        html += '<h6 class="mt-3" style="font-size:.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Detalhes</h6>';
        html += '<div style="max-height:360px;overflow:auto;border:1px solid var(--line);border-radius:8px;"><table class="table tbl-compact mb-0"><thead><tr><th>Polo</th><th>Escola</th><th>Turma</th><th>Data</th><th>Status</th></tr></thead><tbody>';
        r.detalhes.forEach(function(d){
          var badge = 'badge-off';
          if (d.status === 'nova') badge = 'badge-ok';
          else if (d.status === 'atualizada') badge = 'badge-polo';
          html += '<tr><td>'+d.polo+'</td><td>'+d.escola+'</td><td>'+(d.turma||'')+'</td><td>'+(d.data||'')+'</td><td><span class="badge '+badge+'">'+d.status+'</span></td></tr>';
        });
        html += '</tbody></table></div>';
      }
      if (r.resumo.avisos && r.resumo.avisos.length) {
        html += '<h6 class="mt-3" style="font-size:.78rem;color:var(--warn);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Avisos</h6>';
        html += '<ul style="font-size:.85rem;color:var(--ink-2);">';
        r.resumo.avisos.forEach(function(a){ html += '<li>'+a+'</li>'; });
        html += '</ul>';
      }
      if (r.dry_run) {
        html += '<div class="alert alert-warning mt-3"><i class="bi bi-info-circle"></i> <strong>Modo simulação:</strong> nada foi gravado. Desmarque a caixa e envie novamente para importar.</div>';
      } else {
        html += '<div class="mt-3"><a href="?p=auditorias" class="btn btn-brand btn-sm"><i class="bi bi-arrow-right"></i> Ver auditorias</a></div>';
      }
      html += '</div></div>';
      document.getElementById('resultado').innerHTML = html;
    })
    .catch(function(e){
      btn.disabled = false; btn.innerHTML = '<i class="bi bi-play-fill"></i> Processar';
      document.getElementById('resultado').innerHTML =
        '<div class="alert alert-danger">Erro de rede: ' + e.message + '</div>';
    });
}
</script>

</main>
<footer class="no-print">
  Audiência de Leitura · <?= date('Y') ?>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Utilitário de toast leve
window.toast = function(msg, tipo){
  tipo = tipo || 'info';
  var map = { ok:'success', success:'success', erro:'danger', danger:'danger', warn:'warning', info:'info' };
  var cls = 'alert alert-' + (map[tipo] || 'info');
  var el = document.createElement('div');
  el.className = cls;
  el.style.cssText = 'position:fixed;top:70px;right:20px;z-index:9999;min-width:260px;max-width:380px;box-shadow:0 10px 30px rgba(0,0,0,.15);opacity:0;transform:translateY(-8px);transition:all .25s ease';
  el.innerHTML = msg;
  document.body.appendChild(el);
  requestAnimationFrame(function(){ el.style.opacity='1'; el.style.transform='translateY(0)'; });
  setTimeout(function(){ el.style.opacity='0'; setTimeout(function(){ el.remove(); }, 300); }, 3200);
};
</script>
</body>
</html>

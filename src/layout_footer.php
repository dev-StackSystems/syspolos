</main>
<footer>
  &copy; <?= date('Y') ?> Auditoria de Leitura · <a href="https://neon.tech" target="_blank" class="text-muted">Neon</a> · <a href="https://vercel.com" target="_blank" class="text-muted">Vercel</a>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmar(msg, cb){ if (confirm(msg)) cb(); }
function postJSON(url, data, cb){
  var fd = new FormData();
  Object.keys(data).forEach(function(k){ fd.append(k, data[k]); });
  fd.append('ajax','1');
  fetch(url, { method:'POST', body:fd })
    .then(r => r.json())
    .then(cb)
    .catch(function(e){ alert('Erro de comunicação.'); console.error(e); });
}
</script>
</body>
</html>

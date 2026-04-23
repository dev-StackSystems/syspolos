<?php
$pageTitle = 'Entrar';
?>
<style>
.login-wrap{min-height:70vh; display:flex; align-items:center; justify-content:center; padding:2rem 1rem;}
.login-card{width:100%; max-width:420px; background:#fff; border:1px solid var(--line); border-radius:14px; box-shadow:var(--shadow-lg); overflow:hidden}
.login-header{background:linear-gradient(135deg,var(--brand-600),#7c3aed); color:#fff; padding:1.6rem; text-align:center}
.login-header .ico{width:52px; height:52px; border-radius:14px; background:rgba(255,255,255,.18); display:inline-flex; align-items:center; justify-content:center; font-size:1.4rem; margin-bottom:.75rem}
.login-header h1{color:#fff; font-size:1.15rem; margin:0 0 .15rem; font-weight:700; letter-spacing:-.01em}
.login-header p{color:rgba(255,255,255,.85); font-size:.85rem; margin:0}
.login-body{padding:1.5rem 1.6rem}
</style>

<div class="login-wrap">
  <div class="login-card">
    <div class="login-header">
      <div class="ico"><i class="bi bi-book-half"></i></div>
      <h1>Auditoria de Leitura</h1>
      <p>Entre para acessar o sistema</p>
    </div>
    <div class="login-body">
      <?php if (!empty($_GET['err'])): ?>
        <div class="alert alert-danger" style="font-size:.85rem">
          <i class="bi bi-exclamation-circle"></i> Email ou senha incorretos.
        </div>
      <?php endif; ?>
      <form method="POST" action="?a=login_entrar">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" required autofocus placeholder="seu@email.com">
        </div>
        <div class="mb-3">
          <label class="form-label">Senha</label>
          <input type="password" class="form-control" name="senha" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn btn-brand w-100"><i class="bi bi-box-arrow-in-right"></i> Entrar</button>
      </form>
    </div>
  </div>
</div>

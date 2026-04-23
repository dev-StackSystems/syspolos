<?php
$pageTitle   = $pageTitle   ?? 'Auditoria de Leitura';
$currentPage = $_GET['p']   ?? 'home';
$flash       = flash_get();
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> — Auditoria de Leitura</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  :root { --brand:#1a56db; --brand-dk:#1646b0; }
  body { background:#f4f6f9; font-size:.95rem; }
  .navbar-brand { font-weight:600; letter-spacing:.2px; }
  .card-header-brand { background:var(--brand); color:#fff; font-weight:600; padding:.6rem .9rem; border-bottom:0; }
  .btn-brand { background:var(--brand); color:#fff; border:1px solid var(--brand); }
  .btn-brand:hover { background:var(--brand-dk); color:#fff; }
  .kpi-num { font-size:2rem; font-weight:700; color:var(--brand); line-height:1; }
  .kpi-lbl { color:#666; font-size:.8rem; text-transform:uppercase; letter-spacing:.5px; }
  .table thead th { background:var(--brand); color:#fff; font-weight:600; }
  .tbl-compact td, .tbl-compact th { padding:.45rem .6rem; vertical-align:middle; }
  .badge-polo { background:var(--brand); }
  .form-section-title { background:#eef3ff; color:var(--brand-dk); font-weight:600; padding:.4rem .7rem; border-left:4px solid var(--brand); margin-bottom:.6rem; }
  label.form-label { font-weight:500; font-size:.85rem; margin-bottom:.15rem; color:#333; }
  .counter-group input { text-align:center; font-weight:600; }
  footer { color:#888; text-align:center; padding:1.5rem 0; font-size:.8rem; }
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= url('home') ?>">
      <i class="bi bi-book-half"></i> Auditoria de Leitura
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link<?= $currentPage==='home'?' active':'' ?>" href="<?= url('home') ?>"><i class="bi bi-speedometer2"></i> Painel</a></li>
        <li class="nav-item"><a class="nav-link<?= in_array($currentPage,['auditorias','auditoria_form','auditoria_view'])?' active':'' ?>" href="<?= url('auditorias') ?>"><i class="bi bi-clipboard-check"></i> Auditorias</a></li>
        <li class="nav-item"><a class="nav-link<?= $currentPage==='escolas'?' active':'' ?>" href="<?= url('escolas') ?>"><i class="bi bi-building"></i> Escolas</a></li>
        <li class="nav-item"><a class="nav-link<?= $currentPage==='polos'?' active':'' ?>" href="<?= url('polos') ?>"><i class="bi bi-geo-alt"></i> Polos</a></li>
        <li class="nav-item"><a class="nav-link<?= $currentPage==='relatorio'?' active':'' ?>" href="<?= url('relatorio') ?>"><i class="bi bi-bar-chart"></i> Relatório</a></li>
        <li class="nav-item"><a class="nav-link<?= $currentPage==='importar'?' active':'' ?>" href="<?= url('importar') ?>"><i class="bi bi-upload"></i> Importar</a></li>
      </ul>
      <a href="<?= url('auditoria_form') ?>" class="btn btn-sm btn-brand"><i class="bi bi-plus-lg"></i> Nova auditoria</a>
    </div>
  </div>
</nav>
<main class="container-fluid pb-5">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

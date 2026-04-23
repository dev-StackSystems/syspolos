<?php
$pageTitle   = $pageTitle   ?? 'Auditoria de Leitura';
$currentPage = $_GET['p']   ?? 'home';
$flash       = flash_get();
?><!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · Auditoria de Leitura</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<style>
  :root{
    --brand:#4f46e5;      /* indigo-600 */
    --brand-600:#4f46e5;
    --brand-700:#4338ca;
    --brand-50:#eef2ff;
    --brand-100:#e0e7ff;
    --surface:#ffffff;
    --bg:#f8fafc;         /* slate-50 */
    --bg-2:#f1f5f9;       /* slate-100 */
    --ink:#0f172a;        /* slate-900 */
    --ink-2:#334155;      /* slate-700 */
    --muted:#64748b;      /* slate-500 */
    --line:#e2e8f0;       /* slate-200 */
    --line-2:#cbd5e1;
    --ok:#059669; --ok-50:#ecfdf5;
    --warn:#d97706; --warn-50:#fffbeb;
    --danger:#dc2626; --danger-50:#fef2f2;
    --shadow-sm:0 1px 2px rgba(15,23,42,.06), 0 1px 3px rgba(15,23,42,.08);
    --shadow-md:0 4px 6px -1px rgba(15,23,42,.08), 0 2px 4px -2px rgba(15,23,42,.06);
    --shadow-lg:0 10px 15px -3px rgba(15,23,42,.1), 0 4px 6px -4px rgba(15,23,42,.05);
    --radius:10px;
    --radius-lg:14px;
  }

  *{box-sizing:border-box}
  html,body{height:100%}
  body{
    font-family:'Inter',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    background:var(--bg);
    color:var(--ink);
    font-size:14px;
    line-height:1.5;
    -webkit-font-smoothing:antialiased;
  }
  h1,h2,h3,h4,h5,h6{font-weight:600;letter-spacing:-.01em;color:var(--ink)}
  h4{font-size:1.15rem}
  .text-muted-2{color:var(--muted)!important}

  /* NAVBAR */
  .topbar{
    background:#fff;
    border-bottom:1px solid var(--line);
    box-shadow:var(--shadow-sm);
    padding:.6rem 0;
    position:sticky; top:0; z-index:100;
  }
  .brand{
    display:flex; align-items:center; gap:.55rem;
    font-weight:700; font-size:1.05rem;
    color:var(--ink); text-decoration:none;
    letter-spacing:-.01em;
  }
  .brand-ico{
    width:34px; height:34px; border-radius:9px;
    background:linear-gradient(135deg,var(--brand-600),#7c3aed);
    color:#fff; display:flex; align-items:center; justify-content:center;
    font-size:1.1rem;
    box-shadow:0 4px 10px rgba(79,70,229,.35);
  }
  .nav-pill{
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.45rem .8rem;
    border-radius:8px;
    color:var(--ink-2);
    font-weight:500;
    text-decoration:none;
    transition:all .15s ease;
  }
  .nav-pill:hover{background:var(--bg-2); color:var(--ink)}
  .nav-pill.active{background:var(--brand-50); color:var(--brand-700)}
  .nav-pill i{font-size:1rem}

  /* CARDS */
  .card{
    background:var(--surface);
    border:1px solid var(--line);
    border-radius:var(--radius-lg);
    box-shadow:var(--shadow-sm);
    overflow:hidden;
  }
  .card-header{
    background:var(--surface);
    border-bottom:1px solid var(--line);
    padding:.85rem 1.1rem;
    font-weight:600;
    color:var(--ink);
    display:flex; align-items:center; gap:.5rem;
  }
  .card-header-brand{
    background:linear-gradient(135deg,var(--brand-600),#6366f1);
    color:#fff;
    border-bottom:0;
    padding:.8rem 1.1rem;
    font-weight:600;
    letter-spacing:.02em;
    text-transform:uppercase;
    font-size:.78rem;
  }
  .card-body{padding:1.1rem}

  /* BUTTONS */
  .btn{font-weight:500; border-radius:8px; padding:.5rem .95rem; transition:all .15s ease; font-size:.88rem}
  .btn-sm{padding:.35rem .7rem; font-size:.82rem}
  .btn-brand{background:var(--brand-600); color:#fff; border:1px solid var(--brand-600); box-shadow:0 1px 2px rgba(79,70,229,.25)}
  .btn-brand:hover{background:var(--brand-700); border-color:var(--brand-700); color:#fff; transform:translateY(-1px); box-shadow:0 4px 10px rgba(79,70,229,.35)}
  .btn-ghost{background:transparent; color:var(--ink-2); border:1px solid var(--line)}
  .btn-ghost:hover{background:var(--bg-2); color:var(--ink); border-color:var(--line-2)}
  .btn-outline-primary{color:var(--brand-600); border-color:var(--line)}
  .btn-outline-primary:hover{background:var(--brand-50); color:var(--brand-700); border-color:var(--brand-100)}
  .btn-outline-secondary{color:var(--muted); border-color:var(--line)}
  .btn-outline-secondary:hover{background:var(--bg-2); color:var(--ink)}
  .btn-outline-danger{color:var(--danger); border-color:var(--line)}
  .btn-outline-danger:hover{background:var(--danger-50); color:var(--danger); border-color:#fecaca}

  /* FORM */
  .form-control, .form-select{
    border:1px solid var(--line);
    border-radius:8px;
    padding:.5rem .7rem;
    font-size:.9rem;
    color:var(--ink);
    transition:all .12s ease;
  }
  .form-control:focus, .form-select:focus{
    border-color:var(--brand-600);
    box-shadow:0 0 0 3px rgba(79,70,229,.12);
  }
  .form-control-sm, .form-select-sm{padding:.35rem .6rem; font-size:.85rem}
  label.form-label{font-weight:500; font-size:.82rem; color:var(--ink-2); margin-bottom:.25rem}
  .form-check-input:checked{background-color:var(--brand-600); border-color:var(--brand-600)}

  /* TABLES */
  .table{font-size:.88rem; color:var(--ink-2); margin-bottom:0}
  .table thead th{
    background:var(--bg-2);
    color:var(--muted);
    font-weight:600; text-transform:uppercase; font-size:.7rem;
    letter-spacing:.04em;
    border-bottom:1px solid var(--line);
    padding:.65rem .85rem;
  }
  .table tbody tr{transition:background .12s ease}
  .table tbody tr:hover{background:var(--brand-50)}
  .table td{padding:.65rem .85rem; vertical-align:middle; border-bottom:1px solid var(--line)}
  .table tbody tr:last-child td{border-bottom:0}
  .tbl-compact td, .tbl-compact th{padding:.5rem .75rem}

  /* BADGES */
  .badge{font-weight:500; padding:.35em .65em; border-radius:6px; font-size:.72rem; letter-spacing:.02em}
  .badge-polo{background:var(--brand-50); color:var(--brand-700); border:1px solid var(--brand-100)}
  .badge-ok{background:var(--ok-50); color:var(--ok)}
  .badge-off{background:var(--bg-2); color:var(--muted)}

  /* KPI */
  .kpi-card{
    background:var(--surface);
    border:1px solid var(--line);
    border-radius:var(--radius-lg);
    padding:1.2rem 1.25rem;
    display:flex; align-items:center; gap:1rem;
    box-shadow:var(--shadow-sm);
    transition:all .18s ease;
  }
  .kpi-card:hover{transform:translateY(-2px); box-shadow:var(--shadow-md); border-color:var(--line-2)}
  .kpi-ico{
    width:48px; height:48px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.4rem; flex:0 0 auto;
  }
  .kpi-ico.ind{background:var(--brand-50); color:var(--brand-600)}
  .kpi-ico.emr{background:#ecfdf5; color:#059669}
  .kpi-ico.amb{background:#fffbeb; color:#d97706}
  .kpi-ico.vio{background:#faf5ff; color:#7c3aed}
  .kpi-num{font-size:1.65rem; font-weight:700; color:var(--ink); line-height:1; font-variant-numeric:tabular-nums; letter-spacing:-.02em}
  .kpi-lbl{color:var(--muted); font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; font-weight:600; margin-top:.25rem}

  /* HERO */
  .page-hero{
    background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 60%,#ec4899 120%);
    color:#fff;
    border-radius:var(--radius-lg);
    padding:1.75rem 1.9rem;
    margin-bottom:1.5rem;
    box-shadow:0 8px 25px -10px rgba(79,70,229,.5);
    position:relative; overflow:hidden;
  }
  .page-hero::before{
    content:""; position:absolute; right:-80px; top:-80px; width:260px; height:260px;
    background:radial-gradient(circle,rgba(255,255,255,.14),transparent 65%);
    pointer-events:none;
  }
  .page-hero h1{color:#fff; font-size:1.5rem; margin:0 0 .2rem; letter-spacing:-.015em}
  .page-hero p{color:rgba(255,255,255,.85); margin:0 0 1rem; font-size:.95rem}
  .page-hero .btn{font-weight:600}

  /* PAGE TITLE */
  .page-title{
    display:flex; align-items:center; justify-content:space-between;
    gap:1rem; margin-bottom:1.25rem;
  }
  .page-title h4{margin:0; display:flex; align-items:center; gap:.5rem}
  .page-title .muted-sub{color:var(--muted); font-size:.82rem; margin-top:.15rem}

  /* ALERT */
  .alert{border-radius:10px; border:1px solid transparent; padding:.75rem 1rem; font-size:.9rem}
  .alert-info{background:var(--brand-50); color:var(--brand-700); border-color:var(--brand-100)}
  .alert-success{background:var(--ok-50); color:var(--ok); border-color:#a7f3d0}
  .alert-danger{background:var(--danger-50); color:var(--danger); border-color:#fecaca}
  .alert-warning{background:var(--warn-50); color:var(--warn); border-color:#fde68a}

  /* EMPTY STATE */
  .empty{
    text-align:center; padding:2.5rem 1rem;
    color:var(--muted);
  }
  .empty-ico{
    width:64px; height:64px; margin:0 auto 1rem;
    border-radius:50%; background:var(--bg-2);
    display:flex; align-items:center; justify-content:center;
    font-size:1.6rem; color:var(--muted);
  }
  .empty h5{color:var(--ink-2); font-weight:600}

  /* MODAL */
  .modal-content{border:0; border-radius:var(--radius-lg); box-shadow:var(--shadow-lg)}
  .modal-header{padding:1rem 1.25rem}
  .modal-body{padding:1.25rem}
  .modal-footer{padding:.8rem 1.25rem; background:var(--bg); border-top:1px solid var(--line)}

  /* COUNTERS */
  .counter-group .form-control{text-align:center; font-weight:600; font-size:1rem; font-variant-numeric:tabular-nums}
  .counter-wrap{
    background:var(--bg);
    border:1px solid var(--line);
    border-radius:10px;
    padding:.9rem;
    height:100%;
  }
  .counter-wrap .form-label{margin-bottom:.4rem; font-size:.78rem}
  .total-badge{
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.4rem .75rem; border-radius:8px;
    background:var(--ink); color:#fff;
    font-weight:600; font-size:.82rem; font-variant-numeric:tabular-nums;
  }

  /* PRINT */
  @media print{
    .topbar, .no-print, footer{display:none!important}
    body{background:#fff}
    .card{box-shadow:none; border:1px solid #ddd}
    .page-hero{background:#fff; color:#000; box-shadow:none; border:1px solid #ddd}
    .page-hero h1, .page-hero p{color:#000}
  }

  /* UTIL */
  .divider{height:1px; background:var(--line); margin:1.25rem 0}
  .icon-sq{width:32px;height:32px;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;background:var(--brand-50);color:var(--brand-600)}
  footer{color:var(--muted); text-align:center; padding:1.5rem 0; font-size:.78rem}
  a{color:var(--brand-600); text-decoration:none}
  a:hover{color:var(--brand-700); text-decoration:underline}
  .btn a, a.btn{text-decoration:none}
  .gap-2{gap:.5rem}
  .skeleton{display:inline-block; background:linear-gradient(90deg,#eee 0%,#f5f5f5 50%,#eee 100%); background-size:200% 100%; animation:sk 1.4s infinite; border-radius:4px}
  @keyframes sk{0%{background-position:200% 0}100%{background-position:-200% 0}}
</style>
</head>
<body>
<header class="topbar no-print">
  <div class="container-fluid d-flex align-items-center gap-3">
    <a class="brand" href="<?= url('home') ?>">
      <span class="brand-ico"><i class="bi bi-book-half"></i></span>
      <span>Auditoria de Leitura</span>
    </a>
    <nav class="d-none d-lg-flex align-items-center gap-1 ms-3">
      <a class="nav-pill<?= $currentPage==='home'?' active':'' ?>" href="<?= url('home') ?>"><i class="bi bi-grid-1x2"></i> Painel</a>
      <a class="nav-pill<?= in_array($currentPage,['auditorias','auditoria_form','auditoria_view'])?' active':'' ?>" href="<?= url('auditorias') ?>"><i class="bi bi-clipboard-check"></i> Auditorias</a>
      <a class="nav-pill<?= $currentPage==='escolas'?' active':'' ?>" href="<?= url('escolas') ?>"><i class="bi bi-building"></i> Escolas</a>
      <a class="nav-pill<?= $currentPage==='polos'?' active':'' ?>" href="<?= url('polos') ?>"><i class="bi bi-geo-alt"></i> Polos</a>
      <a class="nav-pill<?= $currentPage==='relatorio'?' active':'' ?>" href="<?= url('relatorio') ?>"><i class="bi bi-graph-up"></i> Relatório</a>
      <a class="nav-pill<?= $currentPage==='importar'?' active':'' ?>" href="<?= url('importar') ?>"><i class="bi bi-cloud-upload"></i> Importar</a>
    </nav>
    <div class="ms-auto d-flex align-items-center gap-2">
      <a href="<?= url('auditoria_form') ?>" class="btn btn-brand btn-sm">
        <i class="bi bi-plus-lg"></i> Nova auditoria
      </a>
      <?php $me = auth_user(); if ($me): ?>
        <div class="dropdown">
          <button class="btn btn-ghost btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> <?= e(explode(' ', $me['nome'])[0]) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow" style="border:1px solid var(--line); border-radius:10px;">
            <li><span class="dropdown-item-text small text-muted-2"><?= e($me['email']) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= url('usuarios') ?>"><i class="bi bi-people"></i> Usuários</a></li>
            <li><a class="dropdown-item text-danger" href="?a=login_sair"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <!-- Mobile nav -->
  <div class="d-lg-none container-fluid pt-2">
    <div class="d-flex gap-1 flex-wrap">
      <a class="nav-pill<?= $currentPage==='home'?' active':'' ?>" href="<?= url('home') ?>"><i class="bi bi-grid-1x2"></i></a>
      <a class="nav-pill<?= in_array($currentPage,['auditorias','auditoria_form','auditoria_view'])?' active':'' ?>" href="<?= url('auditorias') ?>"><i class="bi bi-clipboard-check"></i></a>
      <a class="nav-pill<?= $currentPage==='escolas'?' active':'' ?>" href="<?= url('escolas') ?>"><i class="bi bi-building"></i></a>
      <a class="nav-pill<?= $currentPage==='polos'?' active':'' ?>" href="<?= url('polos') ?>"><i class="bi bi-geo-alt"></i></a>
      <a class="nav-pill<?= $currentPage==='relatorio'?' active':'' ?>" href="<?= url('relatorio') ?>"><i class="bi bi-graph-up"></i></a>
      <a class="nav-pill<?= $currentPage==='importar'?' active':'' ?>" href="<?= url('importar') ?>"><i class="bi bi-cloud-upload"></i></a>
    </div>
  </div>
</header>

<main class="container-fluid py-4 pb-5" style="max-width:1400px;">
<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show" role="alert">
    <?= e($flash['msg']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

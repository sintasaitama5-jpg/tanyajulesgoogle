<!DOCTYPE html>
<html lang="<?= \App\Lang::current() ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Invoice') ?> — <?= e(setting('app_name','InvoiceApp')) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { font-family: 'Plus Jakarta Sans', sans-serif; }

  /* Sidebar links — plain CSS, no @apply */
  .nav-link {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 14px; border-radius: 10px;
    color: #94a3b8; font-size: 13.5px; font-weight: 500;
    text-decoration: none; transition: all .15s ease;
    white-space: nowrap;
  }
  .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
  .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
  .nav-link i { width: 18px; text-align: center; font-size: 14px; flex-shrink: 0; }

  /* Mobile overlay */
  #sidebarOverlay { display: none; }
  #sidebarOverlay.open { display: block; }

  /* Mobile sidebar */
  @media (max-width: 1023px) {
    #sidebar {
      transform: translateX(-100%);
      transition: transform .25s ease;
      position: fixed; z-index: 50;
    }
    #sidebar.open { transform: translateX(0); }
    #mainContent { margin-left: 0 !important; }
  }

  /* Scrollbar */
  ::-webkit-scrollbar { width: 5px; height: 5px; }
  ::-webkit-scrollbar-track { background: transparent; }
  ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

  /* Card hover */
  .stat-card { transition: box-shadow .2s, transform .2s; }
  .stat-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,.08); transform: translateY(-2px); }

  /* Fade-in */
  @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  .fade-up { animation: fadeUp .3s ease forwards; }
</style>
</head>
<body class="bg-slate-50 min-h-screen">
<?php $user = \App\Auth::user(); ?>
<?php $cur = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>

<!-- Mobile overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/40 z-40 lg:hidden" onclick="toggleSidebar()"></div>

<div class="flex min-h-screen">

  <!-- ── Sidebar ─────────────────────────────────────────── -->
  <aside id="sidebar"
         class="w-64 bg-slate-900 flex flex-col min-h-screen fixed left-0 top-0 z-50 lg:translate-x-0">

    <!-- Logo -->
    <div class="px-5 py-5 border-b border-white/10 flex items-center gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-500 flex items-center justify-center flex-shrink-0">
        <i class="fa fa-file-invoice text-white text-sm"></i>
      </div>
      <div class="min-w-0">
        <div class="text-white font-bold text-sm truncate"><?= e(setting('app_name','InvoiceApp')) ?></div>
        <div class="text-slate-400 text-xs truncate"><?= e($user['name'] ?? '') ?> · <?= ucfirst($user['role'] ?? '') ?></div>
      </div>
      <!-- Close btn mobile -->
      <button onclick="toggleSidebar()" class="ml-auto text-slate-500 hover:text-white lg:hidden">
        <i class="fa fa-xmark"></i>
      </button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
      <div class="text-slate-600 text-xs font-semibold uppercase tracking-wider px-3 mb-2">Menu</div>

      <a href="/" class="nav-link <?= $cur === '/' ? 'active' : '' ?>">
        <i class="fa fa-gauge-high"></i> <?= __t('dashboard') ?>
      </a>
      <a href="/invoices" class="nav-link <?= str_starts_with($cur, '/invoices') ? 'active' : '' ?>">
        <i class="fa fa-file-invoice"></i> <?= __t('invoices') ?>
      </a>
      <a href="/clients" class="nav-link <?= str_starts_with($cur, '/clients') ? 'active' : '' ?>">
        <i class="fa fa-users"></i> <?= __t('clients') ?>
      </a>
      <a href="/companies" class="nav-link <?= str_starts_with($cur, '/companies') ? 'active' : '' ?>">
        <i class="fa fa-building"></i> <?= __t('companies') ?>
      </a>

      <?php if (\App\Auth::isAdmin()): ?>
      <div class="text-slate-600 text-xs font-semibold uppercase tracking-wider px-3 mt-4 mb-2">Admin</div>
      <a href="/users" class="nav-link <?= str_starts_with($cur, '/users') ? 'active' : '' ?>">
        <i class="fa fa-user-shield"></i> <?= __t('users') ?>
      </a>
      <a href="/settings" class="nav-link <?= str_starts_with($cur, '/settings') ? 'active' : '' ?>">
        <i class="fa fa-gear"></i> <?= __t('settings') ?>
      </a>
      <?php endif; ?>
    </nav>

    <!-- Bottom -->
    <div class="px-3 py-4 border-t border-white/10 space-y-0.5">
      <a href="/lang?lang=<?= \App\Lang::other() ?>" class="nav-link">
        <i class="fa fa-language"></i>
        <?= \App\Lang::current() === 'id' ? 'English' : 'Indonesia' ?>
      </a>
      <a href="/logout" class="nav-link" style="color:#f87171;" onmouseover="this.style.background='rgba(239,68,68,.1)'" onmouseout="this.style.background='transparent'">
        <i class="fa fa-right-from-bracket"></i> <?= __t('logout') ?>
      </a>
    </div>
  </aside>

  <!-- ── Main ────────────────────────────────────────────── -->
  <div id="mainContent" class="flex-1 flex flex-col min-h-screen" style="margin-left:256px;">

    <!-- Top bar -->
    <header class="sticky top-0 z-30 bg-white border-b border-slate-200 px-4 lg:px-6 h-14 flex items-center gap-3">
      <!-- Hamburger mobile -->
      <button onclick="toggleSidebar()" class="lg:hidden text-slate-500 hover:text-slate-800 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100">
        <i class="fa fa-bars"></i>
      </button>

      <!-- Breadcrumb / title -->
      <div class="text-sm font-semibold text-slate-700 truncate">
        <?= e($pageTitle ?? 'Dashboard') ?>
      </div>

      <div class="ml-auto flex items-center gap-2">
        <!-- Quick create -->
        <a href="/invoices/create"
           class="hidden sm:flex items-center gap-2 bg-slate-900 hover:bg-slate-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors">
          <i class="fa fa-plus"></i> Invoice Baru
        </a>
        <!-- User badge -->
        <div class="flex items-center gap-2 pl-2 border-l border-slate-200">
          <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-xs font-bold">
            <?= strtoupper(substr($user['name'] ?? 'A', 0, 1)) ?>
          </div>
          <span class="text-xs text-slate-600 hidden sm:block font-medium"><?= e($user['name'] ?? '') ?></span>
        </div>
      </div>
    </header>

    <!-- Content area -->
    <main class="flex-1 p-4 lg:p-6 fade-up">

      <!-- Flash messages -->
      <?php $success = flash('success'); $error = flash('error'); ?>
      <?php if ($success): ?>
      <div class="mb-5 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm flex items-center gap-2">
        <i class="fa fa-circle-check text-green-500"></i> <?= e($success) ?>
      </div>
      <?php endif; ?>
      <?php if ($error): ?>
      <div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-xl text-sm flex items-center gap-2">
        <i class="fa fa-circle-exclamation text-red-500"></i> <?= e($error) ?>
      </div>
      <?php endif; ?>

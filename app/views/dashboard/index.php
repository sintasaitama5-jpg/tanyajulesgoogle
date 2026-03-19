<?php $pageTitle = __t('dashboard'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<!-- Header row -->
<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-xl font-bold text-slate-800"><?= __t('dashboard') ?></h1>
    <p class="text-slate-500 text-sm mt-0.5"><?= date('l, d F Y') ?></p>
  </div>
  <a href="/invoices/create"
     class="sm:hidden bg-slate-900 text-white text-sm px-4 py-2 rounded-xl flex items-center gap-2">
    <i class="fa fa-plus text-xs"></i> Buat
  </a>
</div>

<!-- Stat cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
  <?php
  $cards = [
    ['label' => __t('total_invoices'), 'value' => $stats['total'],    'icon' => 'fa-file-invoice',  'bg' => 'bg-blue-500',   'light' => 'bg-blue-50',  'text' => 'text-blue-600'],
    ['label' => __t('paid'),           'value' => $stats['paid'],     'icon' => 'fa-circle-check',  'bg' => 'bg-emerald-500','light' => 'bg-emerald-50','text' => 'text-emerald-600'],
    ['label' => __t('unpaid'),         'value' => $stats['unpaid'],   'icon' => 'fa-clock',         'bg' => 'bg-amber-500',  'light' => 'bg-amber-50',  'text' => 'text-amber-600'],
    ['label' => __t('cancelled'),      'value' => $stats['cancelled'],'icon' => 'fa-circle-xmark',  'bg' => 'bg-red-400',    'light' => 'bg-red-50',    'text' => 'text-red-500'],
  ];
  foreach ($cards as $i => $c):
  ?>
  <div class="stat-card bg-white rounded-2xl p-5 border border-slate-100 flex items-center gap-4"
       style="animation-delay: <?= $i * 0.05 ?>s">
    <div class="<?= $c['light'] ?> rounded-xl w-12 h-12 flex items-center justify-center flex-shrink-0">
      <i class="fa <?= $c['icon'] ?> <?= $c['text'] ?> text-lg"></i>
    </div>
    <div class="min-w-0">
      <div class="text-2xl font-bold text-slate-800"><?= number_format((int)$c['value']) ?></div>
      <div class="text-slate-500 text-xs font-medium truncate"><?= $c['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Revenue banner + quick stats -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
  <!-- Revenue -->
  <div class="lg:col-span-2 rounded-2xl p-6 text-white relative overflow-hidden"
       style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
    <div class="absolute right-0 top-0 w-48 h-48 rounded-full bg-white/5 -translate-y-1/2 translate-x-1/2"></div>
    <div class="absolute right-12 bottom-0 w-24 h-24 rounded-full bg-white/5 translate-y-1/2"></div>
    <div class="relative">
      <div class="text-slate-400 text-sm font-medium"><?= __t('total_revenue') ?></div>
      <div class="text-3xl lg:text-4xl font-bold mt-1 mb-1">
        <?= formatCurrency((float)$stats['revenue']) ?>
      </div>
      <div class="text-slate-400 text-xs">
        <?= __t('status_paid') ?> · <?= $stats['paid'] ?> invoice
      </div>
    </div>
  </div>

  <!-- Quick info -->
  <div class="bg-white rounded-2xl p-5 border border-slate-100 space-y-3">
    <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Ringkasan</div>
    <?php
    $typeStats = [
      'barang'     => ['label' => __t('type_barang'),     'icon' => 'fa-box',        'color' => 'text-blue-500'],
      'jasa'       => ['label' => __t('type_jasa'),       'icon' => 'fa-wrench',     'color' => 'text-purple-500'],
      'penginapan' => ['label' => __t('type_penginapan'), 'icon' => 'fa-hotel',      'color' => 'text-emerald-500'],
    ];
    foreach ($typeStats as $type => $info):
      $count = \App\Database::get()
        ->query("SELECT COUNT(*) FROM invoices WHERE type='$type'")->fetchColumn();
    ?>
    <div class="flex items-center gap-3">
      <div class="w-8 h-8 rounded-lg bg-slate-50 flex items-center justify-center flex-shrink-0">
        <i class="fa <?= $info['icon'] ?> <?= $info['color'] ?> text-sm"></i>
      </div>
      <div class="flex-1 min-w-0">
        <div class="text-xs font-medium text-slate-700 truncate"><?= $info['label'] ?></div>
      </div>
      <div class="text-sm font-bold text-slate-800"><?= $count ?></div>
    </div>
    <?php endforeach; ?>

    <div class="border-t border-slate-100 pt-3">
      <?php $clients = \App\Database::get()->query("SELECT COUNT(*) FROM clients")->fetchColumn(); ?>
      <?php $companies = \App\Database::get()->query("SELECT COUNT(*) FROM companies")->fetchColumn(); ?>
      <div class="flex justify-between text-xs text-slate-500">
        <span><i class="fa fa-users mr-1"></i><?= $clients ?> Klien</span>
        <span><i class="fa fa-building mr-1"></i><?= $companies ?> Perusahaan</span>
      </div>
    </div>
  </div>
</div>

<!-- Recent invoices -->
<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
  <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
    <h2 class="font-semibold text-slate-800 flex items-center gap-2">
      <i class="fa fa-clock-rotate-left text-slate-400 text-sm"></i>
      <?= __t('recent_invoices') ?>
    </h2>
    <a href="/invoices" class="text-blue-500 hover:text-blue-700 text-xs font-medium">
      Lihat semua <i class="fa fa-arrow-right ml-1"></i>
    </a>
  </div>

  <!-- Desktop table -->
  <div class="hidden md:block overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
        <tr>
          <th class="text-left px-5 py-3"><?= __t('invoice_number') ?></th>
          <th class="text-left px-4 py-3"><?= __t('clients') ?></th>
          <th class="text-left px-4 py-3"><?= __t('invoice_type') ?></th>
          <th class="text-right px-4 py-3"><?= __t('total') ?></th>
          <th class="text-left px-4 py-3"><?= __t('status') ?></th>
          <th class="text-left px-4 py-3"><?= __t('invoice_date') ?></th>
          <th class="text-right px-4 py-3"></th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <?php foreach ($recent as $inv):
          $statusStyle = match($inv['status']) {
            'paid'      => 'background:#d1fae5;color:#065f46',
            'cancelled' => 'background:#fee2e2;color:#991b1b',
            default     => 'background:#fef3c7;color:#92400e',
          };
          $statusLabel = __t('status_' . $inv['status']);
          $typeLabel   = match($inv['type']) {
            'barang'      => __t('type_barang'),
            'jasa'        => __t('type_jasa'),
            'maintenance' => __t('type_maintenance'),
            'service'     => __t('type_service'),
            'barang_jasa' => __t('type_barang_jasa'),
            'penginapan'  => __t('type_penginapan'),
            default      => ucfirst($inv['type']),
          };
          $typeIcon = match($inv['type']) {
            'barang'     => 'fa-box text-blue-400',
            'jasa'       => 'fa-wrench text-purple-400',
            'penginapan' => 'fa-hotel text-emerald-400',
            default      => 'fa-file text-slate-400',
          };
        ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="px-5 py-3">
            <a href="/invoices/edit?id=<?= $inv['id'] ?>"
               class="font-mono text-xs text-blue-600 hover:text-blue-800 hover:underline font-semibold">
              <?= e($inv['invoice_number'] ?: '—') ?>
            </a>
          </td>
          <td class="px-4 py-3 text-slate-700 text-xs"><?= e($inv['client_name'] ?: '—') ?></td>
          <td class="px-4 py-3">
            <span class="flex items-center gap-1.5 text-xs text-slate-500">
              <i class="fa <?= $typeIcon ?> text-xs"></i><?= $typeLabel ?>
            </span>
          </td>
          <td class="px-4 py-3 text-right font-semibold text-slate-800 text-sm">
            <?= formatCurrency($inv['total'], $inv['currency']) ?>
          </td>
          <td class="px-4 py-3">
            <span class="px-2 py-0.5 rounded-full text-xs font-medium" style="<?= $statusStyle ?>">
              <?= $statusLabel ?>
            </span>
          </td>
          <td class="px-4 py-3 text-slate-400 text-xs"><?= e($inv['issue_date']) ?></td>
          <td class="px-4 py-3 text-right">
            <a href="/invoices/edit?id=<?= $inv['id'] ?>"
               class="text-slate-400 hover:text-slate-700 text-xs px-2 py-1 rounded hover:bg-slate-100">
              <i class="fa fa-pen-to-square"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($recent)): ?>
        <tr>
          <td colspan="7" class="px-5 py-12 text-center">
            <div class="text-slate-300 text-4xl mb-3"><i class="fa fa-file-invoice"></i></div>
            <div class="text-slate-500 text-sm">Belum ada invoice</div>
            <a href="/invoices/create" class="inline-block mt-3 text-blue-500 hover:underline text-sm">
              Buat invoice pertama
            </a>
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Mobile list -->
  <div class="md:hidden divide-y divide-slate-100">
    <?php foreach ($recent as $inv):
      $statusStyle = match($inv['status']) {
        'paid'      => 'background:#d1fae5;color:#065f46',
        'cancelled' => 'background:#fee2e2;color:#991b1b',
        default     => 'background:#fef3c7;color:#92400e',
      };
    ?>
    <a href="/invoices/edit?id=<?= $inv['id'] ?>"
       class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors">
      <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
        <i class="fa fa-file-invoice text-slate-500 text-sm"></i>
      </div>
      <div class="flex-1 min-w-0">
        <div class="font-mono text-xs font-semibold text-slate-800 truncate">
          <?= e($inv['invoice_number'] ?: '—') ?>
        </div>
        <div class="text-xs text-slate-500 truncate"><?= e($inv['client_name'] ?: '—') ?></div>
      </div>
      <div class="text-right flex-shrink-0">
        <div class="text-sm font-bold text-slate-800"><?= formatCurrency($inv['total'], $inv['currency']) ?></div>
        <span class="text-xs px-1.5 py-0.5 rounded-full font-medium" style="<?= $statusStyle ?>">
          <?= __t('status_' . $inv['status']) ?>
        </span>
      </div>
    </a>
    <?php endforeach; ?>
    <?php if (empty($recent)): ?>
    <div class="px-4 py-8 text-center text-slate-400 text-sm">Belum ada invoice</div>
    <?php endif; ?>
  </div>
</div>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

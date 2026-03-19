<?php $pageTitle = __t('invoices'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-xl font-bold text-slate-800"><?= __t('invoices') ?></h1>
  <a href="/invoices/create" class="bg-slate-900 hover:bg-slate-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
    <i class="fa fa-plus text-xs"></i> <?= __t('create_invoice') ?>
  </a>
</div>

<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-4">
  <form method="GET" action="/invoices" class="flex flex-wrap gap-3 items-end">
    <div>
      <label class="block text-xs text-slate-500 mb-1"><?= __t('search') ?></label>
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Nomor / Klien..."
             class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-48 focus:outline-none focus:ring-2 focus:ring-slate-400">
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1"><?= __t('invoice_type') ?></label>
      <select name="type" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        <option value=""><?= __t('all') ?></option>
        <option value="barang"      <?= $type==='barang'      ?'selected':'' ?>><?= __t('type_barang') ?></option>
        <option value="jasa"        <?= $type==='jasa'        ?'selected':'' ?>><?= __t('type_jasa') ?></option>
        <option value="maintenance" <?= $type==='maintenance' ?'selected':'' ?>><?= __t('type_maintenance') ?></option>
        <option value="service"     <?= $type==='service'     ?'selected':'' ?>><?= __t('type_service') ?></option>
        <option value="barang_jasa" <?= $type==='barang_jasa' ?'selected':'' ?>><?= __t('type_barang_jasa') ?></option>
        <option value="penginapan"  <?= $type==='penginapan'  ?'selected':'' ?>><?= __t('type_penginapan') ?></option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-slate-500 mb-1"><?= __t('status') ?></label>
      <select name="status" class="border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        <option value=""><?= __t('all') ?></option>
        <option value="unpaid"    <?= $status==='unpaid'    ?'selected':'' ?>><?= __t('status_unpaid') ?></option>
        <option value="paid"      <?= $status==='paid'      ?'selected':'' ?>><?= __t('status_paid') ?></option>
        <option value="cancelled" <?= $status==='cancelled' ?'selected':'' ?>><?= __t('status_cancelled') ?></option>
      </select>
    </div>
    <button type="submit" class="bg-slate-800 hover:bg-slate-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
      <i class="fa fa-search mr-1"></i> <?= __t('filter') ?>
    </button>
    <?php if ($search || $type || $status): ?>
    <a href="/invoices" class="text-slate-500 hover:text-slate-700 text-sm px-3 py-2">
      <i class="fa fa-xmark mr-1"></i> Reset
    </a>
    <?php endif; ?>
  </form>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
        <tr>
          <th class="text-left px-5 py-3"><?= __t('invoice_number') ?></th>
          <th class="text-left px-4 py-3"><?= __t('bill_to') ?></th>
          <th class="text-left px-4 py-3"><?= __t('invoice_type') ?></th>
          <th class="text-right px-4 py-3"><?= __t('total') ?></th>
          <th class="text-left px-4 py-3"><?= __t('status') ?></th>
          <th class="text-left px-4 py-3"><?= __t('invoice_date') ?></th>
          <th class="text-right px-4 py-3">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-50">
        <?php foreach ($invoices as $inv):
          $cd = json_decode($inv['client_data'] ?? '{}', true) ?: [];
          $clientName = $inv['client_name'] ?: ($cd['name'] ?? '—');
          $statusClass = match($inv['status']) {
            'paid'      => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-600',
            default     => 'bg-amber-100 text-amber-700',
          };
          $typeLabel = match($inv['type']) {
            'barang'      => __t('type_barang'),
            'jasa'        => __t('type_jasa'),
            'maintenance' => __t('type_maintenance'),
            'service'     => __t('type_service'),
            'barang_jasa' => __t('type_barang_jasa'),
            'penginapan'  => __t('type_penginapan'),
            default      => ucfirst($inv['type']),
          };
        ?>
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-5 py-3">
            <div class="font-mono text-xs text-blue-700 font-medium"><?= e($inv['invoice_number']) ?></div>
            <div class="text-slate-400 text-xs"><?= e($inv['updated_at']) ?></div>
          </td>
          <td class="px-4 py-3">
            <div class="text-slate-700 font-medium"><?= e($clientName) ?></div>
            <?php if ($cd['company'] ?? ''): ?><div class="text-slate-400 text-xs"><?= e($cd['company']) ?></div><?php endif; ?>
          </td>
          <td class="px-4 py-3">
            <span class="bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full"><?= e($typeLabel) ?></span>
          </td>
          <td class="px-4 py-3 text-right font-semibold text-slate-800"><?= formatCurrency($inv['total'], $inv['currency']) ?></td>
          <td class="px-4 py-3">
            <div class="relative group inline-block">
              <span class="<?= $statusClass ?> px-2 py-0.5 rounded-full text-xs font-medium cursor-pointer">
                <?= __t('status_' . $inv['status']) ?> <i class="fa fa-chevron-down text-[9px]"></i>
              </span>
              <div class="hidden group-hover:block absolute left-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-10 w-36">
                <?php foreach (['unpaid','paid','cancelled'] as $s): ?>
                <a href="/invoices/status?id=<?= $inv['id'] ?>&status=<?= $s ?>"
                   class="block px-3 py-2 text-xs hover:bg-slate-50 text-slate-700"><?= __t('status_'.$s) ?></a>
                <?php endforeach; ?>
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-slate-500 text-xs"><?= e($inv['issue_date']) ?></td>
          <td class="px-4 py-3">
            <div class="flex items-center gap-1 justify-end">
              <a href="/invoices/edit?id=<?= $inv['id'] ?>"
                 class="p-1.5 text-slate-500 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                <i class="fa fa-pen text-xs"></i>
              </a>
              <a href="/invoices/pdf?id=<?= $inv['id'] ?>" target="_blank"
                 class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="PDF">
                <i class="fa fa-file-pdf text-xs"></i>
              </a>
              <a href="/invoices/download?id=<?= $inv['id'] ?>"
                 class="p-1.5 text-slate-500 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors" title="Download">
                <i class="fa fa-download text-xs"></i>
              </a>
              <a href="/invoices/delete?id=<?= $inv['id'] ?>"
                 onclick="return confirm('<?= __t('confirm_delete') ?>')"
                 class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Delete">
                <i class="fa fa-trash text-xs"></i>
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($invoices)): ?>
        <tr><td colspan="7" class="px-5 py-10 text-center text-slate-400"><?= __t('not_found') ?></td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

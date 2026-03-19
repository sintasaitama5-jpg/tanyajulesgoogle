<?php $pageTitle = __t('clients'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-xl font-bold text-slate-800"><?= __t('clients') ?></h1>
  <div class="flex gap-2">
    <a href="/clients/export" class="border border-slate-300 text-slate-600 hover:bg-slate-50 text-sm px-3 py-2 rounded-lg transition-colors flex items-center gap-1">
      <i class="fa fa-file-csv text-xs"></i> <?= __t('export_clients') ?>
    </a>
    <button onclick="document.getElementById('clientModal').classList.remove('hidden')"
            class="bg-slate-900 hover:bg-slate-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
      <i class="fa fa-plus text-xs"></i> <?= __t('add_client') ?>
    </button>
  </div>
</div>

<!-- Search -->
<form method="GET" action="/clients" class="mb-4 flex gap-2">
  <input type="text" name="q" value="<?= e($search) ?>" placeholder="<?= __t('search') ?>..."
         class="border border-slate-300 rounded-lg px-3 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-slate-400">
  <button class="bg-slate-800 text-white px-4 py-2 rounded-lg text-sm"><?= __t('search') ?></button>
  <?php if ($search): ?><a href="/clients" class="px-3 py-2 text-slate-500 hover:text-slate-700 text-sm">Reset</a><?php endif; ?>
</form>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
      <tr>
        <th class="text-left px-5 py-3"><?= __t('client_name') ?></th>
        <th class="text-left px-4 py-3"><?= __t('company_name') ?></th>
        <th class="text-left px-4 py-3"><?= __t('phone') ?></th>
        <th class="text-left px-4 py-3"><?= __t('email_addr') ?></th>
        <th class="text-right px-4 py-3">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
      <?php foreach ($clients as $c): ?>
      <tr class="hover:bg-slate-50">
        <td class="px-5 py-3 font-medium text-slate-800"><?= e($c['name']) ?></td>
        <td class="px-4 py-3 text-slate-500"><?= e($c['company']) ?></td>
        <td class="px-4 py-3 text-slate-500"><?= e($c['phone']) ?></td>
        <td class="px-4 py-3 text-slate-500"><?= e($c['email']) ?></td>
        <td class="px-4 py-3 text-right">
          <button onclick='editClient(<?= json_encode($c) ?>)'
                  class="text-blue-500 hover:text-blue-700 text-xs px-2 py-1 rounded hover:bg-blue-50">
            <i class="fa fa-pen"></i>
          </button>
          <a href="/clients/delete?id=<?= $c['id'] ?>" onclick="return confirm('<?= __t('confirm_delete') ?>')"
             class="text-red-400 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50">
            <i class="fa fa-trash"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($clients)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400"><?= __t('not_found') ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal -->
<div id="clientModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-semibold text-slate-800" id="modalTitle"><?= __t('add_client') ?></h3>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-xmark"></i></button>
    </div>
    <form method="POST" action="/clients/save" class="p-6 space-y-3">
      <input type="hidden" name="client_id" id="modalClientId">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="text-xs text-slate-500"><?= __t('client_name') ?> *</label>
          <input type="text" name="name" id="mName" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div>
          <label class="text-xs text-slate-500"><?= __t('company_name') ?></label>
          <input type="text" name="company" id="mCompany" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div>
          <label class="text-xs text-slate-500"><?= __t('phone') ?></label>
          <input type="text" name="phone" id="mPhone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div class="col-span-2">
          <label class="text-xs text-slate-500"><?= __t('address') ?></label>
          <input type="text" name="address" id="mAddress" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div>
          <label class="text-xs text-slate-500"><?= __t('city') ?></label>
          <input type="text" name="city" id="mCity" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div>
          <label class="text-xs text-slate-500"><?= __t('email_addr') ?></label>
          <input type="email" name="email" id="mEmail" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
        <div class="col-span-2">
          <label class="text-xs text-slate-500"><?= __t('npwp') ?></label>
          <input type="text" name="npwp" id="mNpwp" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm mt-1 focus:outline-none focus:ring-2 focus:ring-slate-300">
        </div>
      </div>
      <div class="flex gap-2 pt-2">
        <button type="submit" class="flex-1 bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-xl text-sm font-medium transition-colors"><?= __t('save') ?></button>
        <button type="button" onclick="closeModal()" class="flex-1 border border-slate-300 text-slate-600 py-2.5 rounded-xl text-sm font-medium hover:bg-slate-50"><?= __t('cancel') ?></button>
      </div>
    </form>
  </div>
</div>

<script>
function editClient(c) {
  document.getElementById('modalTitle').textContent = '<?= __t('edit_client') ?>';
  document.getElementById('modalClientId').value = c.id;
  document.getElementById('mName').value    = c.name    || '';
  document.getElementById('mCompany').value = c.company || '';
  document.getElementById('mPhone').value   = c.phone   || '';
  document.getElementById('mAddress').value = c.address || '';
  document.getElementById('mCity').value    = c.city    || '';
  document.getElementById('mEmail').value   = c.email   || '';
  document.getElementById('mNpwp').value    = c.npwp    || '';
  document.getElementById('clientModal').classList.remove('hidden');
}
function closeModal() {
  document.getElementById('clientModal').classList.add('hidden');
  document.getElementById('modalClientId').value = '';
  document.getElementById('modalTitle').textContent = '<?= __t('add_client') ?>';
}
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

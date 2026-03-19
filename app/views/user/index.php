<?php $pageTitle = __t('users'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-xl font-bold text-slate-800"><?= __t('users') ?></h1>
  <button onclick="openUserModal()"
          class="bg-slate-900 hover:bg-slate-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
    <i class="fa fa-plus text-xs"></i> <?= __t('add_user') ?>
  </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
      <tr>
        <th class="text-left px-5 py-3"><?= __t('users') ?></th>
        <th class="text-left px-4 py-3"><?= __t('email') ?></th>
        <th class="text-left px-4 py-3"><?= __t('role') ?></th>
        <th class="text-left px-4 py-3"><?= __t('active') ?></th>
        <th class="text-right px-4 py-3">Aksi</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
      <?php foreach ($users as $u): ?>
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-5 py-3">
          <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-slate-700 text-white text-xs font-bold flex items-center justify-center flex-shrink-0">
              <?= strtoupper(substr($u['name'], 0, 1)) ?>
            </div>
            <span class="font-medium text-slate-800"><?= e($u['name']) ?></span>
          </div>
        </td>
        <td class="px-4 py-3 text-slate-500"><?= e($u['email']) ?></td>
        <td class="px-4 py-3">
          <span class="<?= $u['role'] === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600' ?> px-2 py-0.5 rounded-full text-xs font-medium">
            <?= $u['role'] === 'admin' ? __t('role_admin') : __t('role_staff') ?>
          </span>
        </td>
        <td class="px-4 py-3">
          <?php if ($u['active']): ?>
            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full text-xs"><?= __t('active') ?></span>
          <?php else: ?>
            <span class="bg-red-100 text-red-600 px-2 py-0.5 rounded-full text-xs">Nonaktif</span>
          <?php endif; ?>
        </td>
        <td class="px-4 py-3 text-right">
          <button onclick='openUserModal(<?= json_encode($u) ?>)'
                  class="text-blue-500 hover:text-blue-700 text-xs px-2 py-1 rounded hover:bg-blue-50 mr-1">
            <i class="fa fa-pen"></i>
          </button>
          <?php if ($u['id'] != \App\Auth::id()): ?>
          <a href="/users/delete?id=<?= $u['id'] ?>" onclick="return confirm('<?= __t('confirm_delete') ?>')"
             class="text-red-400 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50">
            <i class="fa fa-trash"></i>
          </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($users)): ?>
      <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400"><?= __t('not_found') ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Modal -->
<div id="userModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-800" id="uModalTitle"><?= __t('add_user') ?></h2>
      <button onclick="closeUserModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-xmark text-lg"></i></button>
    </div>

    <form method="POST" action="/users/save" class="p-6 space-y-4">
      <input type="hidden" name="user_id" id="uId" value="">

      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1">Nama *</label>
        <input type="text" name="name" id="uName" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('email') ?> *</label>
        <input type="email" name="email" id="uEmail" required
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('password') ?> <span class="text-slate-400 font-normal" id="pwdHint">(isi untuk ubah)</span></label>
        <input type="password" name="password" id="uPwd" autocomplete="new-password"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('role') ?></label>
          <select name="role" id="uRole"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <option value="staff"><?= __t('role_staff') ?></option>
            <option value="admin"><?= __t('role_admin') ?></option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('active') ?></label>
          <select name="active" id="uActive"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <option value="1"><?= __t('active') ?></option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
          <i class="fa fa-save mr-1"></i> <?= __t('save') ?>
        </button>
        <button type="button" onclick="closeUserModal()"
                class="px-5 border border-slate-300 text-slate-600 hover:bg-slate-50 py-2.5 rounded-lg text-sm transition-colors">
          <?= __t('cancel') ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openUserModal(u = null) {
  document.getElementById('uModalTitle').textContent = u ? '<?= __t('edit_user') ?>' : '<?= __t('add_user') ?>';
  document.getElementById('uId').value     = u?.id    ?? '';
  document.getElementById('uName').value   = u?.name  ?? '';
  document.getElementById('uEmail').value  = u?.email ?? '';
  document.getElementById('uPwd').value    = '';
  document.getElementById('uRole').value   = u?.role   ?? 'staff';
  document.getElementById('uActive').value = u?.active ?? 1;
  document.getElementById('pwdHint').textContent = u ? '(isi untuk ubah)' : '';
  document.getElementById('userModal').classList.remove('hidden');
}
function closeUserModal() { document.getElementById('userModal').classList.add('hidden'); }
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

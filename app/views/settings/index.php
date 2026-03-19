<?php $pageTitle = __t('settings'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="mb-6">
  <h1 class="text-xl font-bold text-slate-800"><?= __t('settings') ?></h1>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

  <!-- App Settings -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <h2 class="font-semibold text-slate-700 mb-5 flex items-center gap-2">
      <i class="fa fa-gear text-slate-400"></i> <?= __t('settings') ?> Aplikasi
    </h2>
    <form method="POST" action="/settings/save" class="space-y-4">
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('app_name') ?></label>
        <input type="text" name="app_name" value="<?= e(setting('app_name','InvoiceApp')) ?>"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('default_lang') ?></label>
          <select name="default_lang"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <option value="id" <?= setting('default_lang','id')==='id'?'selected':'' ?>>Indonesia</option>
            <option value="en" <?= setting('default_lang','id')==='en'?'selected':'' ?>>English</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('default_currency') ?></label>
          <select name="default_currency"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <option value="IDR" <?= setting('default_currency','IDR')==='IDR'?'selected':'' ?>>IDR – Rupiah</option>
            <option value="USD" <?= setting('default_currency','IDR')==='USD'?'selected':'' ?>>USD – Dollar</option>
            <option value="SGD" <?= setting('default_currency','IDR')==='SGD'?'selected':'' ?>>SGD – Singapore Dollar</option>
            <option value="EUR" <?= setting('default_currency','IDR')==='EUR'?'selected':'' ?>>EUR – Euro</option>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('default_notes') ?> (Indonesia)</label>
        <textarea name="default_notes_id" rows="3"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"><?= e(setting('default_notes_id')) ?></textarea>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('default_notes') ?> (English)</label>
        <textarea name="default_notes_en" rows="3"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"><?= e(setting('default_notes_en')) ?></textarea>
      </div>
      <button type="submit"
              class="w-full bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
        <i class="fa fa-save mr-1"></i> <?= __t('save') ?>
      </button>
    </form>
  </div>

  <!-- Invoice Format Manager -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center justify-between mb-5">
      <h2 class="font-semibold text-slate-700 flex items-center gap-2">
        <i class="fa fa-hashtag text-slate-400"></i> <?= __t('invoice_format') ?>
      </h2>
      <button onclick="openFmtModal()"
              class="bg-slate-900 hover:bg-slate-700 text-white text-xs px-3 py-1.5 rounded-lg transition-colors">
        <i class="fa fa-plus mr-1"></i> <?= __t('add_format') ?>
      </button>
    </div>

    <!-- Hint -->
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-xs text-amber-700">
      <strong><?= __t('format_hint') ?></strong><br>
      Contoh: <code class="bg-amber-100 px-1 rounded">INV/{TYPE}/{YEAR}/{MON}/{SEQ}</code> →
      <code class="bg-amber-100 px-1 rounded">INV/JSA/2025/III/0001</code>
    </div>

    <div class="space-y-3">
      <?php foreach ($formats as $fmt): ?>
      <div class="border border-slate-200 rounded-xl p-4">
        <div class="flex items-start justify-between">
          <div>
            <div class="font-medium text-slate-800 text-sm"><?= e($fmt['name']) ?></div>
            <code class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded mt-1 inline-block"><?= e($fmt['format']) ?></code>
            <div class="text-xs text-slate-400 mt-1">
              Padding: <?= $fmt['padding'] ?> digit ·
              <?= $fmt['reset_yearly'] ? 'Reset tiap tahun' : 'Tidak reset' ?> ·
              Seq terakhir: #<?= $fmt['last_seq'] ?>
            </div>
          </div>
          <div class="flex gap-1 flex-shrink-0 ml-3">
            <button onclick='openFmtModal(<?= json_encode($fmt) ?>)'
                    class="text-blue-500 hover:text-blue-700 text-xs px-2 py-1 rounded hover:bg-blue-50">
              <i class="fa fa-pen"></i>
            </button>
            <form method="POST" action="/formats/delete" class="inline" onsubmit="return confirm('<?= __t('confirm_delete') ?>')">
              <input type="hidden" name="format_id" value="<?= $fmt['id'] ?>">
              <button type="submit" class="text-red-400 hover:text-red-600 text-xs px-2 py-1 rounded hover:bg-red-50">
                <i class="fa fa-trash"></i>
              </button>
            </form>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (empty($formats)): ?>
      <div class="text-center text-slate-400 text-sm py-8">
        Belum ada format — <button onclick="openFmtModal()" class="text-blue-500 hover:underline"><?= __t('add_format') ?></button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Format Modal -->
<div id="fmtModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-800" id="fmtModalTitle"><?= __t('add_format') ?></h2>
      <button onclick="closeFmtModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-xmark text-lg"></i></button>
    </div>

    <form method="POST" action="/formats/save" class="p-6 space-y-4">
      <input type="hidden" name="format_id" id="ffId" value="">
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('format_name') ?> *</label>
        <input type="text" name="format_name" id="ffName" required placeholder="Contoh: Format Jasa 2025"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('format_pattern') ?> *</label>
        <input type="text" name="format_pattern" id="ffPattern" required
               placeholder="INV/{TYPE}/{YEAR}/{MON}/{SEQ}"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-slate-400"
               oninput="previewFmt()">
        <div class="text-xs text-slate-400 mt-1"><?= __t('format_hint') ?></div>
        <div class="text-xs text-blue-600 mt-1 font-mono" id="ffPreview"></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('padding') ?></label>
          <input type="number" name="format_padding" id="ffPad" min="1" max="8" value="4"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400"
                 oninput="previewFmt()">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Invoice</label>
          <select name="format_type"
                  class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
            <option value="all">Semua</option>
            <option value="barang">Barang</option>
            <option value="jasa">Jasa</option>
            <option value="penginapan">Penginapan</option>
          </select>
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
        <input type="checkbox" name="format_reset_yearly" id="ffReset" value="1" checked class="rounded">
        <?= __t('reset_yearly') ?>
      </label>

      <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
          <i class="fa fa-save mr-1"></i> <?= __t('save') ?>
        </button>
        <button type="button" onclick="closeFmtModal()"
                class="px-5 border border-slate-300 text-slate-600 hover:bg-slate-50 py-2.5 rounded-lg text-sm transition-colors">
          <?= __t('cancel') ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openFmtModal(f = null) {
  document.getElementById('fmtModalTitle').textContent = f ? 'Edit Format' : '<?= __t('add_format') ?>';
  document.getElementById('ffId').value      = f?.id      ?? '';
  document.getElementById('ffName').value    = f?.name    ?? '';
  document.getElementById('ffPattern').value = f?.format  ?? '';
  document.getElementById('ffPad').value     = f?.padding ?? 4;
  document.getElementById('ffReset').checked = f ? !!f.reset_yearly : true;
  previewFmt();
  document.getElementById('fmtModal').classList.remove('hidden');
}
function closeFmtModal() { document.getElementById('fmtModal').classList.add('hidden'); }

function previewFmt() {
  const pat  = document.getElementById('ffPattern').value;
  const pad  = parseInt(document.getElementById('ffPad').value) || 4;
  const now  = new Date();
  const year = now.getFullYear();
  const mon  = now.getMonth() + 1;
  const months = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
  const seq  = '1'.padStart(pad, '0');
  let preview = pat
    .replace('{YEAR}',  year)
    .replace('{MONTH}', String(mon).padStart(2,'0'))
    .replace('{MON}',   months[mon])
    .replace('{TYPE}',  'JSA')
    .replace('{SEQ}',   seq);
  document.getElementById('ffPreview').textContent = preview ? '→ ' + preview : '';
}
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

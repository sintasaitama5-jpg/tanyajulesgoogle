<?php $pageTitle = __t('companies'); ?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex items-center justify-between mb-6">
  <h1 class="text-xl font-bold text-slate-800"><?= __t('companies') ?></h1>
  <button onclick="openModal()"
          class="bg-slate-900 hover:bg-slate-700 text-white text-sm px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
    <i class="fa fa-plus text-xs"></i> <?= __t('add_company') ?>
  </button>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
  <?php foreach ($companies as $co): ?>
  <?php
    $color1 = $co['brand_color']  ?? '#1e3a8a';
    $color2 = $co['brand_color2'] ?? '#3b82f6';
    $style  = $co['brand_style']  ?? 'solid';
    $bg     = $style === 'gradient'
              ? "background: linear-gradient(135deg, $color1, $color2);"
              : "background: $color1;";
    $logoUrl = $co['logo_path'] ? '/uploads/' . e($co['logo_path']) : '';
  ?>
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Brand Header -->
    <div class="h-16 flex items-center px-5 gap-3" style="<?= $bg ?>">
      <?php if ($logoUrl): ?>
        <img src="<?= e($logoUrl) ?>" class="h-10 w-10 object-contain rounded bg-white/20 p-1" alt="logo">
      <?php else: ?>
        <div class="h-10 w-10 rounded bg-white/20 flex items-center justify-center text-white font-bold text-lg">
          <?= strtoupper(substr($co['name'], 0, 1)) ?>
        </div>
      <?php endif; ?>
      <div>
        <div class="text-white font-semibold text-sm leading-tight"><?= e($co['name']) ?></div>
        <div class="text-white/70 text-xs"><?= e($co['type']) ?></div>
      </div>
      <?php if ($co['is_default']): ?>
        <span class="ml-auto bg-white/20 text-white text-xs px-2 py-0.5 rounded-full">Default</span>
      <?php endif; ?>
    </div>

    <div class="p-4 text-sm text-slate-600 space-y-1">
      <?php if ($co['address']): ?><div class="flex gap-2"><i class="fa fa-map-marker-alt text-slate-400 w-4 mt-0.5 text-xs"></i><?= e($co['address']) ?><?= $co['city'] ? ', ' . e($co['city']) : '' ?></div><?php endif; ?>
      <?php if ($co['phone']):   ?><div class="flex gap-2"><i class="fa fa-phone text-slate-400 w-4 text-xs"></i><?= e($co['phone']) ?></div><?php endif; ?>
      <?php if ($co['email']):   ?><div class="flex gap-2"><i class="fa fa-envelope text-slate-400 w-4 text-xs"></i><?= e($co['email']) ?></div><?php endif; ?>
      <?php if ($co['npwp']):    ?><div class="flex gap-2"><i class="fa fa-id-card text-slate-400 w-4 text-xs"></i>NPWP: <?= e($co['npwp']) ?></div><?php endif; ?>
    </div>

    <div class="px-4 pb-4 flex gap-2">
      <button onclick='editCompany(<?= json_encode($co) ?>)'
              class="flex-1 border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs px-3 py-1.5 rounded-lg transition-colors">
        <i class="fa fa-pen mr-1"></i> <?= __t('edit') ?>
      </button>
      <?php if (!$co['is_default']): ?>
      <a href="/companies/default?id=<?= $co['id'] ?>"
         class="border border-blue-200 text-blue-600 hover:bg-blue-50 text-xs px-3 py-1.5 rounded-lg transition-colors">
        <i class="fa fa-star mr-1"></i> <?= __t('set_default') ?>
      </a>
      <?php endif; ?>
      <a href="/companies/delete?id=<?= $co['id'] ?>" onclick="return confirm('<?= __t('confirm_delete') ?>')"
         class="border border-red-200 text-red-500 hover:bg-red-50 text-xs px-3 py-1.5 rounded-lg transition-colors">
        <i class="fa fa-trash"></i>
      </a>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($companies)): ?>
  <div class="col-span-3 bg-white rounded-xl border border-slate-100 p-12 text-center text-slate-400">
    <i class="fa fa-building text-3xl mb-3 block"></i>
    <?= __t('not_found') ?> — <button onclick="openModal()" class="text-blue-500 hover:underline"><?= __t('add_company') ?></button>
  </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div id="companyModal" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h2 class="font-semibold text-slate-800" id="modalTitle"><?= __t('add_company') ?></h2>
      <button onclick="closeModal()" class="text-slate-400 hover:text-slate-700"><i class="fa fa-xmark text-lg"></i></button>
    </div>

    <form method="POST" action="/companies/save" enctype="multipart/form-data" class="p-6 space-y-4">
      <input type="hidden" name="company_id" id="fCompanyId" value="">
      <input type="hidden" name="existing_logo"  id="fExistingLogo"  value="">
      <input type="hidden" name="existing_stamp" id="fExistingStamp" value="">

      <div class="grid grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('company_name') ?> *</label>
          <input type="text" name="name" id="fName" required
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('company_type') ?></label>
          <input type="text" name="type" id="fType" placeholder="CV / PT / Perseorangan"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('npwp') ?> <span class="text-slate-400 font-normal">(opsional)</span></label>
          <input type="text" name="npwp" id="fNpwp" placeholder="XX.XXX.XXX.X-XXX.XXX"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('address') ?></label>
          <input type="text" name="address" id="fAddress"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('city') ?></label>
          <input type="text" name="city" id="fCity"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('phone') ?></label>
          <input type="text" name="phone" id="fPhone"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
        <div class="col-span-2">
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('email_addr') ?></label>
          <input type="email" name="email" id="fEmail"
                 class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        </div>
      </div>

      <!-- Branding -->
      <div class="border border-slate-100 rounded-xl p-4 space-y-3">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Branding</div>
        <div class="grid grid-cols-3 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('brand_color1') ?></label>
            <div class="flex gap-2 items-center">
              <input type="color" name="brand_color" id="fColor1" value="#1e3a8a"
                     class="h-9 w-14 border border-slate-300 rounded-lg cursor-pointer p-1">
              <input type="text" id="fColor1Hex" value="#1e3a8a"
                     class="flex-1 border border-slate-300 rounded-lg px-2 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-slate-400"
                     oninput="document.getElementById('fColor1').value=this.value">
            </div>
          </div>
          <div id="color2Block">
            <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('brand_color2') ?></label>
            <div class="flex gap-2 items-center">
              <input type="color" name="brand_color2" id="fColor2" value="#3b82f6"
                     class="h-9 w-14 border border-slate-300 rounded-lg cursor-pointer p-1">
              <input type="text" id="fColor2Hex" value="#3b82f6"
                     class="flex-1 border border-slate-300 rounded-lg px-2 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-slate-400"
                     oninput="document.getElementById('fColor2').value=this.value">
            </div>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('brand_style') ?></label>
            <select name="brand_style" id="fStyle" onchange="toggleColor2()"
                    class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
              <option value="solid"><?= __t('solid') ?></option>
              <option value="gradient"><?= __t('gradient') ?></option>
            </select>
          </div>
        </div>
        <!-- Preview -->
        <div class="h-10 rounded-lg transition-all" id="colorPreview" style="background:#1e3a8a;"></div>
      </div>

      <!-- Logo & Stamp -->
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('logo') ?> <span class="text-slate-400 font-normal">(PNG/JPG, max 2MB)</span></label>
          <div id="logoPreviewWrap" class="hidden mb-2">
            <img id="logoPreview" src="" class="h-16 object-contain border border-slate-200 rounded-lg p-1 bg-white">
          </div>
          <input type="file" name="logo" accept="image/*"
                 class="w-full text-sm text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('stamp') ?> <span class="text-slate-400 font-normal">(PNG/JPG, max 2MB)</span></label>
          <div id="stampPreviewWrap" class="hidden mb-2">
            <img id="stampPreview" src="" class="h-16 object-contain border border-slate-200 rounded-lg p-1 bg-white">
          </div>
          <input type="file" name="stamp" accept="image/*"
                 class="w-full text-sm text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button type="submit"
                class="flex-1 bg-slate-900 hover:bg-slate-700 text-white py-2.5 rounded-lg text-sm font-medium transition-colors">
          <i class="fa fa-save mr-1"></i> <?= __t('save') ?>
        </button>
        <button type="button" onclick="closeModal()"
                class="px-5 border border-slate-300 text-slate-600 hover:bg-slate-50 py-2.5 rounded-lg text-sm transition-colors">
          <?= __t('cancel') ?>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openModal(data = null) {
  const m = document.getElementById('companyModal');
  document.getElementById('modalTitle').textContent = data ? '<?= __t('edit_company') ?>' : '<?= __t('add_company') ?>';
  document.getElementById('fCompanyId').value    = data?.id ?? '';
  document.getElementById('fName').value         = data?.name ?? '';
  document.getElementById('fType').value         = data?.type ?? '';
  document.getElementById('fNpwp').value         = data?.npwp ?? '';
  document.getElementById('fAddress').value      = data?.address ?? '';
  document.getElementById('fCity').value         = data?.city ?? '';
  document.getElementById('fPhone').value        = data?.phone ?? '';
  document.getElementById('fEmail').value        = data?.email ?? '';
  document.getElementById('fExistingLogo').value  = data?.logo_path ?? '';
  document.getElementById('fExistingStamp').value = data?.stamp_path ?? '';

  const c1 = data?.brand_color  ?? '#1e3a8a';
  const c2 = data?.brand_color2 ?? '#3b82f6';
  const st = data?.brand_style  ?? 'solid';
  document.getElementById('fColor1').value    = c1;
  document.getElementById('fColor1Hex').value = c1;
  document.getElementById('fColor2').value    = c2;
  document.getElementById('fColor2Hex').value = c2;
  document.getElementById('fStyle').value     = st;

  if (data?.logo_path) {
    document.getElementById('logoPreview').src = '/uploads/' + data.logo_path;
    document.getElementById('logoPreviewWrap').classList.remove('hidden');
  } else { document.getElementById('logoPreviewWrap').classList.add('hidden'); }

  if (data?.stamp_path) {
    document.getElementById('stampPreview').src = '/uploads/' + data.stamp_path;
    document.getElementById('stampPreviewWrap').classList.remove('hidden');
  } else { document.getElementById('stampPreviewWrap').classList.add('hidden'); }

  toggleColor2();
  updatePreview();
  m.classList.remove('hidden');
}

function editCompany(data) { openModal(data); }
function closeModal() { document.getElementById('companyModal').classList.add('hidden'); }

function toggleColor2() {
  const st = document.getElementById('fStyle').value;
  document.getElementById('color2Block').style.opacity = st === 'gradient' ? '1' : '0.3';
}

function updatePreview() {
  const c1 = document.getElementById('fColor1').value;
  const c2 = document.getElementById('fColor2').value;
  const st = document.getElementById('fStyle').value;
  const p  = document.getElementById('colorPreview');
  p.style.background = st === 'gradient'
    ? `linear-gradient(135deg, ${c1}, ${c2})`
    : c1;
}

document.getElementById('fColor1').addEventListener('input', function() {
  document.getElementById('fColor1Hex').value = this.value;
  updatePreview();
});
document.getElementById('fColor2').addEventListener('input', function() {
  document.getElementById('fColor2Hex').value = this.value;
  updatePreview();
});
document.getElementById('fStyle').addEventListener('change', updatePreview);
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

<?php
$isEdit    = !empty($invoice);
$pageTitle = $isEdit ? __t('edit_invoice') : __t('create_invoice');
$inv       = $invoice ?? [];
$cd        = $inv['company_data'] ?? [];
$cl        = $inv['client_data']  ?? [];
$items     = $inv['items'] ?? [];
?>
<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex items-center gap-3 mb-6">
  <a href="/invoices" class="text-slate-400 hover:text-slate-700"><i class="fa fa-arrow-left"></i></a>
  <h1 class="text-xl font-bold text-slate-800"><?= $pageTitle ?></h1>
  <?php if ($isEdit): ?>
  <span class="ml-auto flex gap-2">
    <a href="/invoices/pdf?id=<?= $inv['id'] ?>" target="_blank"
       class="bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
      <i class="fa fa-eye text-xs"></i> Preview PDF
    </a>
    <a href="/invoices/download?id=<?= $inv['id'] ?>"
       class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
      <i class="fa fa-download text-xs"></i> <?= __t('download_pdf') ?>
    </a>
  </span>
  <?php endif; ?>
</div>

<form method="POST" action="/invoices/save" id="invoiceForm">
<input type="hidden" name="invoice_id" value="<?= e($inv['id'] ?? '') ?>">

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

<!-- LEFT: Main form -->
<div class="xl:col-span-2 space-y-4">

  <!-- Basic Info -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-4"><?= __t('invoice') ?> Info</h2>
    <div class="grid grid-cols-2 gap-4">

      <!-- Type -->
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('invoice_type') ?> *</label>
        <select name="type" id="invType" required
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
          <optgroup label="— Barang">
          <option value="barang"      <?= ($inv['type']??'barang')==='barang'      ?'selected':'' ?>><?= __t('type_barang') ?></option>
          </optgroup>
          <optgroup label="— Jasa">
          <option value="jasa"             <?= ($inv['type']??'')==='jasa'             ?'selected':'' ?>><?= __t('type_jasa') ?></option>
          <option value="jasa_maintenance" <?= ($inv['type']??'')==='jasa_maintenance' ?'selected':'' ?>><?= __t('type_jasa_maintenance') ?></option>
          <option value="jasa_service"     <?= ($inv['type']??'')==='jasa_service'     ?'selected':'' ?>><?= __t('type_jasa_service') ?></option>
          </optgroup>
          <optgroup label="— Campuran">
          <option value="barang_jasa" <?= ($inv['type']??'')==='barang_jasa' ?'selected':'' ?>><?= __t('type_barang_jasa') ?></option>
          </optgroup>
          <optgroup label="— Lainnya">
          <option value="penginapan"  <?= ($inv['type']??'')==='penginapan'  ?'selected':'' ?>><?= __t('type_penginapan') ?></option>
          </optgroup>
        </select>
      </div>

      <!-- Format -->
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('invoice_format') ?></label>
        <select name="format_id" id="formatSelect"
                class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
          <option value="">— Manual —</option>
          <?php foreach ($formats as $fmt): ?>
          <option value="<?= $fmt['id'] ?>" <?= ($inv['format_id']??0)==$fmt['id']?'selected':'' ?>>
            <?= e($fmt['name']) ?> (<?= e($fmt['format']) ?>)
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Invoice Number -->
      <div class="col-span-2">
        <div class="flex items-center justify-between mb-1">
          <label class="text-xs font-medium text-slate-600"><?= __t('invoice_number') ?></label>
          <label class="flex items-center gap-1.5 text-xs text-slate-500 cursor-pointer">
            <input type="checkbox" name="show_invoice_number" value="1" class="rounded"
                   <?= ($inv['show_invoice_number'] ?? 1) ? 'checked' : '' ?>>
            Tampilkan di PDF
          </label>
        </div>
        <input type="text" name="invoice_number" id="invNumber"
               value="<?= e($inv['invoice_number'] ?? '') ?>"
               placeholder="Kosongkan untuk auto-generate"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
        <div class="text-xs mt-1 text-slate-400" id="fmtHint">
          <?php if (!$isEdit && empty($inv['invoice_number'])): ?>
          <i class="fa fa-wand-magic-sparkles mr-1 text-blue-400"></i>Kosong = auto-generate saat simpan
          <?php elseif ($isEdit): ?>
          <i class="fa fa-pen mr-1 text-slate-300"></i>Edit nomor atau biarkan untuk mempertahankan nomor saat ini
          <?php endif; ?>
        </div>
      </div>

      <!-- Dates -->
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('invoice_date') ?></label>
        <input type="date" name="issue_date" value="<?= e($inv['issue_date'] ?? date('Y-m-d')) ?>"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('due_date') ?></label>
        <input type="date" name="due_date" value="<?= e($inv['due_date'] ?? '') ?>"
               class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
      </div>

      <!-- Status + Currency + Lang -->
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('status') ?></label>
        <select name="status" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
          <option value="unpaid"    <?= ($inv['status']??'unpaid')==='unpaid'    ?'selected':'' ?>><?= __t('status_unpaid') ?></option>
          <option value="paid"      <?= ($inv['status']??'')==='paid'      ?'selected':'' ?>><?= __t('status_paid') ?></option>
          <option value="cancelled" <?= ($inv['status']??'')==='cancelled' ?'selected':'' ?>><?= __t('status_cancelled') ?></option>
        </select>
        <label class="flex items-center gap-1.5 mt-1.5 text-xs text-slate-500 cursor-pointer">
          <input type="checkbox" name="show_watermark" value="1" class="rounded"
                 <?= (!isset($inv['id']) || !empty($inv['show_watermark'])) ? 'checked' : '' ?>>
          Tampilkan watermark LUNAS / BELUM LUNAS di PDF <span class="text-slate-300">(opsional)</span>
        </label>
      </div>
      <div>
        <label class="block text-xs font-medium text-slate-600 mb-1"><?= __t('currency') ?></label>
        <select name="currency" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-400">
          <?php foreach (['IDR','USD','SGD','MYR','EUR'] as $cur): ?>
          <option value="<?= $cur ?>" <?= ($inv['currency']??'IDR')===$cur?'selected':'' ?>><?= $cur ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>
  </div>

  <!-- FROM Company -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-slate-700"><?= __t('bill_from') ?></h2>
      <?php if (!empty($companies)): ?>
      <select id="loadCompany" class="border border-slate-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400">
        <option value=""><?= __t('load_company') ?>...</option>
        <?php foreach ($companies as $co): ?>
        <option value="<?= $co['id'] ?>"><?= e($co['name']) ?><?= $co['is_default'] ? ' ★' : '' ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
    </div>
    <input type="hidden" name="company_id" id="companyId" value="<?= e($inv['company_id'] ?? '') ?>">
    <input type="hidden" name="company_logo" id="companyLogo" value="<?= e($cd['logo_path'] ?? '') ?>">
    <input type="hidden" name="company_brand_color"  id="compBrandColor"  value="<?= e($cd['brand_color']  ?? '#1e3a8a') ?>">
    <input type="hidden" name="company_brand_color2" id="compBrandColor2" value="<?= e($cd['brand_color2'] ?? '#3b82f6') ?>">
    <input type="hidden" name="company_brand_style"  id="compBrandStyle"  value="<?= e($cd['brand_style']  ?? 'solid') ?>">
    <div class="grid grid-cols-2 gap-3">
      <div class="col-span-2"><input type="text" name="company_name" id="compName" value="<?= e($cd['name'] ?? '') ?>" placeholder="<?= __t('company_name') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="company_type" id="compType" value="<?= e($cd['type'] ?? '') ?>" placeholder="<?= __t('company_type') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="company_phone" id="compPhone" value="<?= e($cd['phone'] ?? '') ?>" placeholder="<?= __t('phone') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div class="col-span-2"><input type="text" name="company_address" id="compAddress" value="<?= e($cd['address'] ?? '') ?>" placeholder="<?= __t('address') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="company_city" id="compCity" value="<?= e($cd['city'] ?? '') ?>" placeholder="<?= __t('city') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="email" name="company_email" id="compEmail" value="<?= e($cd['email'] ?? '') ?>" placeholder="<?= __t('email_addr') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div class="col-span-2"><input type="text" name="company_npwp" id="compNpwp" value="<?= e($cd['npwp'] ?? '') ?>" placeholder="<?= __t('npwp') ?> (opsional)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
    </div>
  </div>

  <!-- TO Client -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-slate-700"><?= __t('bill_to') ?></h2>
      <?php if (!empty($clients)): ?>
      <select id="loadClient" class="border border-slate-300 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-slate-400">
        <option value=""><?= __t('load_client') ?>...</option>
        <?php foreach ($clients as $cl2): ?>
        <option value="<?= $cl2['id'] ?>"><?= e($cl2['name']) ?><?= $cl2['company'] ? ' ('.$cl2['company'].')' : '' ?></option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
    </div>
    <input type="hidden" name="client_id" id="clientId" value="<?= e($inv['client_id'] ?? '') ?>">
    <div class="grid grid-cols-2 gap-3">
      <div class="col-span-2"><input type="text" name="client_name" id="clName" value="<?= e($cl['name'] ?? '') ?>" placeholder="<?= __t('client_name') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="client_company" id="clCompany" value="<?= e($cl['company'] ?? '') ?>" placeholder="<?= __t('company_name') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="client_phone" id="clPhone" value="<?= e($cl['phone'] ?? '') ?>" placeholder="<?= __t('phone') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div class="col-span-2"><input type="text" name="client_address" id="clAddress" value="<?= e($cl['address'] ?? '') ?>" placeholder="<?= __t('address') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="text" name="client_city" id="clCity" value="<?= e($cl['city'] ?? '') ?>" placeholder="<?= __t('city') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div><input type="email" name="client_email" id="clEmail" value="<?= e($cl['email'] ?? '') ?>" placeholder="<?= __t('email_addr') ?>" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
      <div class="col-span-2"><input type="text" name="client_npwp" id="clNpwp" value="<?= e($cl['npwp'] ?? '') ?>" placeholder="<?= __t('npwp') ?> (opsional)" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"></div>
    </div>
    <div class="flex items-center gap-2 mt-3 text-xs text-slate-400">
      <i class="fa fa-circle-check text-green-400"></i>
      Data klien otomatis disimpan ke address book
    </div>
    <!-- client auto-saved always -->
  </div>

  <!-- Items -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-4"><?= __t('description') ?></h2>
    <div class="overflow-x-auto">
      <table class="w-full text-sm" id="itemsTable">
        <thead class="bg-slate-50">
          <tr class="text-xs text-slate-500">
            <th class="text-left px-2 py-2 rounded-tl-lg w-8">#</th>
            <th class="text-left px-2 py-2"><?= __t('description') ?></th>
            <th class="text-center px-2 py-2 w-20">Jasa?</th>
            <th class="text-left px-2 py-2 w-16"><?= __t('qty') ?></th>
            <th class="text-left px-2 py-2 w-24"><?= __t('unit') ?></th>
            <th class="text-right px-2 py-2 w-32"><?= __t('unit_price') ?></th>
            <th class="text-right px-2 py-2 w-32"><?= __t('amount') ?></th>
            <th class="w-8"></th>
          </tr>
        </thead>
        <tbody id="itemsBody">
          <?php foreach ($items as $i => $item): ?>
          <tr class="item-row border-t border-slate-100">
            <td class="px-2 py-1.5 text-slate-400 text-xs row-num"><?= $i+1 ?></td>
            <td class="px-2 py-1.5">
              <input type="text" name="item_desc[]" value="<?= e($item['desc']) ?>" placeholder="Deskripsi item" required
                     class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300">
            </td>
            <td class="px-2 py-1.5 text-center">
              <label class="inline-flex flex-col items-center gap-0.5 cursor-pointer" title="Centang jika item ini adalah Jasa (kena PPh 23)">
                <input type="checkbox" name="item_kind_map[<?= $i ?>]" value="1" class="item-kind rounded"
                       <?= !empty($item['is_jasa']) ? 'checked' : '' ?>>
                <span class="text-xs text-slate-400 leading-none">jasa</span>
              </label>
            </td>
            <td class="px-2 py-1.5">
              <input type="number" name="item_qty[]" value="<?= e($item['qty']) ?>" min="0" step="any"
                     class="item-qty w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300">
            </td>
            <td class="px-2 py-1.5">
              <input type="text" name="item_unit[]" value="<?= e($item['unit']) ?>" placeholder="pcs/jam/malam"
                     class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300">
            </td>
            <td class="px-2 py-1.5">
              <input type="number" name="item_price[]" value="<?= e($item['price']) ?>" min="0" step="any"
                     class="item-price w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300">
            </td>
            <td class="px-2 py-1.5">
              <input type="number" name="item_amount[]" value="<?= e($item['amount']) ?>" readonly
                     class="item-amount w-full border-0 bg-slate-100 rounded px-2 py-1.5 text-sm text-right text-slate-600 focus:outline-none font-medium">
            </td>
            <td class="px-1 py-1.5">
              <button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-colors">
                <i class="fa fa-times"></i>
              </button>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
          <tr class="item-row border-t border-slate-100">
            <td class="px-2 py-1.5 text-slate-400 text-xs row-num">1</td>
            <td class="px-2 py-1.5"><input type="text" name="item_desc[]" placeholder="Deskripsi item" required class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
            <td class="px-2 py-1.5 text-center"><label class="inline-flex flex-col items-center gap-0.5 cursor-pointer" title="Jasa = kena PPh 23"><input type="checkbox" name="item_kind_map[0]" value="1" class="item-kind rounded"><span class="text-xs text-slate-400 leading-none">jasa</span></label></td>
            <td class="px-2 py-1.5"><input type="number" name="item_qty[]" value="1" min="0" step="any" class="item-qty w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
            <td class="px-2 py-1.5"><input type="text" name="item_unit[]" placeholder="pcs" class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
            <td class="px-2 py-1.5"><input type="number" name="item_price[]" value="0" min="0" step="any" class="item-price w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
            <td class="px-2 py-1.5"><input type="number" name="item_amount[]" value="0" readonly class="item-amount w-full border-0 bg-slate-100 rounded px-2 py-1.5 text-sm text-right text-slate-600 font-medium focus:outline-none"></td>
            <td class="px-1 py-1.5"><button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-colors"><i class="fa fa-times"></i></button></td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <button type="button" onclick="addRow()"
            class="mt-3 text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1 font-medium">
      <i class="fa fa-plus text-xs"></i> <?= __t('add_item') ?>
    </button>
  </div>

  <!-- Notes -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-3"><?= __t('notes') ?></h2>
    <textarea name="notes" rows="3" placeholder="Catatan / syarat pembayaran..."
              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300"><?= e($inv['notes'] ?? setting('default_notes_' . (\App\Lang::current()))) ?></textarea>
  </div>

</div><!-- end left -->

<!-- RIGHT: Totals & Options -->
<div class="space-y-4">

  <!-- Totals -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-4"><?= __t('total') ?></h2>

    <div class="space-y-3 text-sm">
      <div class="flex justify-between">
        <span class="text-slate-500"><?= __t('subtotal') ?></span>
        <span id="dispSubtotal" class="font-medium">0</span>
      </div>

      <!-- Discount -->
      <div class="border-t pt-3">
        <label class="text-xs text-slate-500"><?= __t('discount') ?></label>
        <div class="flex gap-2 mt-1">
          <select name="discount_type" id="discType" class="border border-slate-200 rounded px-2 py-1 text-xs">
            <option value="percent" <?= ($inv['discount_type']??'percent')==='percent'?'selected':'' ?>>%</option>
            <option value="amount"  <?= ($inv['discount_type']??'')==='amount' ?'selected':'' ?>>Rp</option>
          </select>
          <input type="number" name="discount" id="discVal" value="<?= e($inv['discount'] ?? 0) ?>" min="0" step="any"
                 class="flex-1 border border-slate-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
        </div>
        <div class="flex justify-between mt-2 text-xs text-slate-400">
          <span><?= __t('discount') ?></span><span id="dispDiscount">0</span>
        </div>
      </div>

      <!-- Tax options -->
      <div class="border-t pt-3 space-y-2">
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="ppn_enabled" id="ppnEnabled" value="1" <?= !empty($inv['ppn_enabled'])?'checked':'' ?>
                 class="rounded text-blue-600">
          <span class="text-xs text-slate-600"><?= __t('ppn') ?></span>
          <input type="number" name="ppn_rate" value="<?= e($inv['ppn_rate'] ?? 11) ?>" min="0" max="100" step="0.1"
                 class="ml-auto w-16 border border-slate-200 rounded px-2 py-0.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
          <span class="text-xs text-slate-400">%</span>
        </label>
        <div class="flex justify-between text-xs text-slate-400 pl-5" id="ppnRow">
          <span><?= __t('ppn') ?></span><span id="dispPpn">0</span>
        </div>

        <!-- Entity type selector -->
        <div class="rounded-lg bg-slate-50 border border-slate-200 p-3 space-y-2">
          <div class="text-xs font-semibold text-slate-600"><?= __t('entity_type') ?></div>
          <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
            <input type="radio" name="entity_type" value="badan" id="entityBadan"
                   <?= ($inv['entity_type'] ?? 'badan') === 'badan' ? 'checked' : '' ?>
                   class="text-blue-600" onchange="switchEntity()">
            <?= __t('entity_badan') ?>
          </label>
          <label class="flex items-center gap-2 cursor-pointer text-xs text-slate-700">
            <input type="radio" name="entity_type" value="pribadi" id="entityPribadi"
                   <?= ($inv['entity_type'] ?? '') === 'pribadi' ? 'checked' : '' ?>
                   class="text-blue-600" onchange="switchEntity()">
            <?= __t('entity_pribadi') ?>
          </label>
        </div>

        <!-- PPh 23 (Badan) -->
        <div id="pph23Block">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="pph23_enabled" id="pph23Enabled" value="1" <?= !empty($inv['pph23_enabled'])?'checked':'' ?>
                   class="rounded text-blue-600">
            <span class="text-xs text-slate-600"><?= __t('pph23') ?></span>
            <input type="number" name="pph23_rate" value="<?= e($inv['pph23_rate'] ?? 2) ?>" min="0" max="100" step="0.1"
                   class="ml-auto w-16 border border-slate-200 rounded px-2 py-0.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
            <span class="text-xs text-slate-400">%</span>
          </label>
          <div class="text-xs text-slate-400 pl-5 mt-0.5"><?= __t('pph_note_badan') ?></div>
          <div class="flex justify-between text-xs text-slate-400 pl-5 mt-1">
            <span><?= __t('pph23') ?> (-)</span><span id="dispPph23">0</span>
          </div>
        </div>

        <!-- PPh 21 (Pribadi) -->
        <div id="pph21Block" class="hidden">
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="pph21_enabled" id="pph21Enabled" value="1" <?= !empty($inv['pph21_enabled'])?'checked':'' ?>
                   class="rounded text-blue-600">
            <span class="text-xs text-slate-600"><?= __t('pph21') ?></span>
            <input type="number" name="pph21_rate" value="<?= e($inv['pph21_rate'] ?? 5) ?>" min="0" max="100" step="0.1"
                   class="ml-auto w-16 border border-slate-200 rounded px-2 py-0.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
            <span class="text-xs text-slate-400">%</span>
          </label>
          <div class="text-xs text-slate-400 pl-5 mt-0.5"><?= __t('pph_note_pribadi') ?></div>
          <div class="flex justify-between text-xs text-slate-400 pl-5 mt-1">
            <span><?= __t('pph21') ?> (-)</span><span id="dispPph21">0</span>
          </div>
        </div>

        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" name="materai_enabled" id="materaiEnabled" value="1" <?= !empty($inv['materai_enabled'])?'checked':'' ?>
                 class="rounded text-blue-600">
          <span class="text-xs text-slate-600"><?= __t('materai') ?></span>
          <input type="number" name="materai_amount" value="<?= e($inv['materai_amount'] ?? 10000) ?>" min="0"
                 class="ml-auto w-20 border border-slate-200 rounded px-2 py-0.5 text-xs text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
        </label>
      </div>

      <!-- Total -->
      <div class="border-t pt-3 flex justify-between text-base font-bold">
        <span><?= __t('total_due') ?></span>
        <span id="dispTotal" class="text-blue-700">0</span>
      </div>

      <!-- Paid -->
      <div class="flex items-center justify-between gap-2">
        <span class="text-xs text-slate-500"><?= __t('paid_amount') ?></span>
        <input type="number" name="paid_amount" value="<?= e($inv['paid_amount'] ?? 0) ?>" min="0" step="any" id="paidAmount"
               class="w-32 border border-slate-200 rounded px-2 py-1 text-sm text-right focus:outline-none focus:ring-1 focus:ring-slate-300">
      </div>
      <div class="flex justify-between text-sm font-semibold text-red-600">
        <span><?= __t('balance_due') ?></span><span id="dispBalance">0</span>
      </div>

      <!-- Terbilang -->
      <div id="terbilangBox" class="text-xs text-slate-400 italic bg-slate-50 rounded p-2 mt-1"></div>
    </div>
  </div>

  <!-- Design -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-4"><?= __t('template') ?></h2>
    <div class="space-y-3">
      <div>
        <label class="text-xs text-slate-500 mb-1 block"><?= __t('template') ?></label>
        <select name="template" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
          <option value="minimal" <?= ($inv['template']??'minimal')==='minimal'?'selected':'' ?>><?= __t('template_minimal') ?></option>
          <option value="branded" <?= ($inv['template']??'')==='branded'?'selected':'' ?>><?= __t('template_branded') ?></option>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500 mb-1 block"><?= __t('orientation') ?></label>
        <select name="orientation" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
          <option value="portrait"  <?= ($inv['orientation']??'portrait')==='portrait' ?'selected':'' ?>><?= __t('portrait') ?></option>
          <option value="landscape" <?= ($inv['orientation']??'')==='landscape'?'selected':'' ?>><?= __t('landscape') ?></option>
        </select>
      </div>
      <div>
        <label class="text-xs text-slate-500 mb-1 block">Bahasa PDF</label>
        <select name="inv_lang" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-slate-300">
          <option value="id" <?= ($inv['lang']??'id')==='id'?'selected':'' ?>>Indonesia</option>
          <option value="en" <?= ($inv['lang']??'')==='en'?'selected':'' ?>>English</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Signature & Stamp -->
  <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
    <h2 class="font-semibold text-slate-700 mb-4"><?= __t('show_ttd') ?></h2>
    <div class="space-y-3">
      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="show_ttd" value="1" id="showTtd" <?= !empty($inv['show_ttd'])?'checked':'' ?> class="rounded">
        <?= __t('show_ttd') ?>
      </label>
      <div id="ttdFields" class="space-y-2 <?= empty($inv['show_ttd'])?'hidden':'' ?>">
        <input type="text" name="ttd_name" value="<?= e($inv['ttd_name']??'') ?>" placeholder="<?= __t('ttd_name') ?> 1"
               class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-300">
        <input type="text" name="ttd_position" value="<?= e($inv['ttd_position']??'') ?>" placeholder="<?= __t('ttd_position') ?>"
               class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-300">
        <input type="text" name="ttd_name2" value="<?= e($inv['ttd_name2']??'') ?>" placeholder="<?= __t('ttd_name') ?> 2 (opsional)"
               class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-300">
        <input type="text" name="ttd_position2" value="<?= e($inv['ttd_position2']??'') ?>" placeholder="<?= __t('ttd_position') ?> 2"
               class="w-full border border-slate-200 rounded px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-slate-300">
      </div>
      <label class="flex items-center gap-2 text-sm cursor-pointer">
        <input type="checkbox" name="show_stamp" value="1" <?= !empty($inv['show_stamp'])?'checked':'' ?> class="rounded">
        <?= __t('show_stamp') ?>
      </label>
    </div>
  </div>

  <!-- Submit -->
  <button type="submit"
          class="w-full bg-slate-900 hover:bg-slate-700 text-white font-semibold py-3 rounded-xl transition-colors flex items-center justify-center gap-2">
    <i class="fa fa-floppy-disk"></i> <?= __t('save') ?>
  </button>

</div><!-- end right -->
</div><!-- end grid -->
</form>

<script>
// ── Item rows ──────────────────────────────────────────────────────────────
function addRow() {
  const tbody = document.getElementById('itemsBody');
  const n = tbody.querySelectorAll('tr').length + 1;
  const row = document.createElement('tr');
  row.className = 'item-row border-t border-slate-100';
  row.innerHTML = `
    <td class="px-2 py-1.5 text-slate-400 text-xs row-num">${n}</td>
    <td class="px-2 py-1.5"><input type="text" name="item_desc[]" placeholder="Deskripsi item" required class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
    <td class="px-2 py-1.5 text-center"><label class="inline-flex flex-col items-center gap-0.5 cursor-pointer" title="Jasa = kena PPh 23"><input type="checkbox" name="item_kind_map[${n-1}]" value="1" class="item-kind rounded"><span class="text-xs text-slate-400 leading-none">jasa</span></label></td>
    <td class="px-2 py-1.5"><input type="number" name="item_qty[]" value="1" min="0" step="any" class="item-qty w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
    <td class="px-2 py-1.5"><input type="text" name="item_unit[]" placeholder="pcs" class="w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
    <td class="px-2 py-1.5"><input type="number" name="item_price[]" value="0" min="0" step="any" class="item-price w-full border-0 bg-slate-50 rounded px-2 py-1.5 text-sm text-right focus:outline-none focus:bg-white focus:ring-1 focus:ring-slate-300"></td>
    <td class="px-2 py-1.5"><input type="number" name="item_amount[]" value="0" readonly class="item-amount w-full border-0 bg-slate-100 rounded px-2 py-1.5 text-sm text-right text-slate-600 font-medium focus:outline-none"></td>
    <td class="px-1 py-1.5"><button type="button" onclick="removeRow(this)" class="text-slate-300 hover:text-red-500 transition-colors"><i class="fa fa-times"></i></button></td>
  `;
  tbody.appendChild(row);
  bindRowEvents(row);
  reindex();
}

function removeRow(btn) {
  const tbody = document.getElementById('itemsBody');
  if (tbody.querySelectorAll('tr').length <= 1) return;
  btn.closest('tr').remove();
  reindex();
  recalc();
}

function reindex() {
  document.querySelectorAll('#itemsBody tr').forEach((r, i) => {
    const n = r.querySelector('.row-num');
    if (n) n.textContent = i + 1;
  });
}

function bindRowEvents(row) {
  row.querySelector('.item-qty')?.addEventListener('input', calcRow.bind(null, row));
  row.querySelector('.item-price')?.addEventListener('input', calcRow.bind(null, row));
}

function calcRow(row) {
  const qty    = parseFloat(row.querySelector('.item-qty')?.value  || 0);
  const price  = parseFloat(row.querySelector('.item-price')?.value || 0);
  const amount = qty * price;
  const amtEl  = row.querySelector('.item-amount');
  if (amtEl) amtEl.value = amount;
  recalc();
}

function recalc() {
  let subtotal = 0;
  document.querySelectorAll('#itemsBody .item-amount').forEach(el => {
    subtotal += parseFloat(el.value || 0);
  });

  const discType = document.getElementById('discType').value;
  const discVal  = parseFloat(document.getElementById('discVal').value || 0);
  const discount = discType === 'percent' ? subtotal * (discVal / 100) : discVal;
  const afterDisc = subtotal - discount;

  const ppnEnabled   = document.getElementById('ppnEnabled').checked;
  const ppnRate      = parseFloat(document.querySelector('[name="ppn_rate"]').value || 11);
  const ppn          = ppnEnabled ? afterDisc * (ppnRate / 100) : 0;

  const entityType   = document.querySelector('[name="entity_type"]:checked')?.value ?? 'badan';
  const pph23Enabled = (entityType === 'badan')   && document.getElementById('pph23Enabled').checked;
  const pph21Enabled = (entityType === 'pribadi') && document.getElementById('pph21Enabled').checked;
  const pph23Rate    = parseFloat(document.querySelector('[name="pph23_rate"]').value || 2);
  const pph21Rate    = parseFloat(document.querySelector('[name="pph21_rate"]').value || 5);
  let jasaSubtotal = 0;
  document.querySelectorAll('#itemsBody tr.item-row').forEach(row => {
    if (row.querySelector('.item-kind')?.checked)
      jasaSubtotal += parseFloat(row.querySelector('.item-amount')?.value || 0);
  });
  const jasaBase = jasaSubtotal > 0 ? jasaSubtotal : afterDisc;
  const pph23 = pph23Enabled ? jasaBase * (pph23Rate / 100) : 0;
  const pph21 = pph21Enabled ? (jasaBase * 0.5) * (pph21Rate / 100) : 0;

  const materaiEnabled = document.getElementById('materaiEnabled').checked;
  const materai        = materaiEnabled ? parseFloat(document.querySelector('[name="materai_amount"]').value || 10000) : 0;

  const total   = afterDisc + ppn - pph23 - pph21 + materai;
  const paid    = parseFloat(document.getElementById('paidAmount').value || 0);
  const balance = total - paid;

  const fmt = n => n.toLocaleString('id-ID');
  document.getElementById('dispSubtotal').textContent = fmt(subtotal);
  document.getElementById('dispDiscount').textContent = fmt(discount);
  document.getElementById('dispPpn').textContent      = fmt(ppn);
  document.getElementById('dispPph23').textContent = fmt(pph23);
  const el21 = document.getElementById('dispPph21'); if(el21) el21.textContent = fmt(pph21);
  document.getElementById('dispTotal').textContent    = fmt(total);
  document.getElementById('dispBalance').textContent  = fmt(balance);

  // Terbilang (simple version displayed)
  document.getElementById('terbilangBox').textContent = terbilangJS(Math.round(total)) + ' Rupiah';
}

// Terbilang JS (lightweight)
function terbilangJS(n) {
  if (n === 0) return 'Nol';
  const sat = ['','Satu','Dua','Tiga','Empat','Lima','Enam','Tujuh','Delapan','Sembilan',
               'Sepuluh','Sebelas','Dua Belas','Tiga Belas','Empat Belas','Lima Belas',
               'Enam Belas','Tujuh Belas','Delapan Belas','Sembilan Belas'];
  function t(x) {
    if (x < 20) return sat[x];
    if (x < 100) return sat[Math.floor(x/10)+10-10] + ' Puluh' + (x%10 ? ' '+sat[x%10] : '');
    if (x < 200) return 'Seratus' + (x%100 ? ' '+t(x%100) : '');
    if (x < 1000) return sat[Math.floor(x/100)] + ' Ratus' + (x%100 ? ' '+t(x%100) : '');
    if (x < 2000) return 'Seribu' + (x%1000 ? ' '+t(x%1000) : '');
    if (x < 1e6)  return t(Math.floor(x/1000)) + ' Ribu' + (x%1000 ? ' '+t(x%1000) : '');
    if (x < 1e9)  return t(Math.floor(x/1e6)) + ' Juta' + (x%1e6 ? ' '+t(x%1e6) : '');
    return t(Math.floor(x/1e9)) + ' Miliar' + (x%1e9 ? ' '+t(x%1e9) : '');
  }
  return t(Math.abs(n));
}

// ── Load company ───────────────────────────────────────────────────────────
document.getElementById('loadCompany')?.addEventListener('change', function() {
  const id = this.value;
  if (!id) return;
  fetch('/companies/get?id=' + id)
    .then(r => r.json())
    .then(c => {
      if (!c) return;
      document.getElementById('companyId').value    = c.id;
      document.getElementById('compName').value     = c.name    || '';
      document.getElementById('compType').value     = c.type    || '';
      document.getElementById('compAddress').value  = c.address || '';
      document.getElementById('compCity').value     = c.city    || '';
      document.getElementById('compPhone').value    = c.phone   || '';
      document.getElementById('compEmail').value    = c.email   || '';
      document.getElementById('compNpwp').value     = c.npwp    || '';
      document.getElementById('companyLogo').value  = c.logo_path || '';
      document.getElementById('compBrandColor').value  = c.brand_color  || '#1e3a8a';
      document.getElementById('compBrandColor2').value = c.brand_color2 || '#3b82f6';
      document.getElementById('compBrandStyle').value  = c.brand_style  || 'solid';
    });
});

// ── Load client ────────────────────────────────────────────────────────────
document.getElementById('loadClient')?.addEventListener('change', function() {
  const id = this.value;
  if (!id) return;
  fetch('/clients/get?id=' + id)
    .then(r => r.json())
    .then(c => {
      if (!c) return;
      document.getElementById('clientId').value   = c.id;
      document.getElementById('clName').value     = c.name    || '';
      document.getElementById('clCompany').value  = c.company || '';
      document.getElementById('clAddress').value  = c.address || '';
      document.getElementById('clCity').value     = c.city    || '';
      document.getElementById('clPhone').value    = c.phone   || '';
      document.getElementById('clEmail').value    = c.email   || '';
      document.getElementById('clNpwp').value     = c.npwp    || '';
    });
});

// ── Show TTD toggle ────────────────────────────────────────────────────────
document.getElementById('showTtd').addEventListener('change', function() {
  document.getElementById('ttdFields').classList.toggle('hidden', !this.checked);
});

// ── Invoice format hint ────────────────────────────────────────────────────
document.getElementById('formatSelect')?.addEventListener('change', function() {
  const id = this.value;
  const numEl = document.getElementById('invNumber');
  const hint  = document.getElementById('fmtHint');
  if (!id) {
    hint.innerHTML = '<span class="text-slate-400"><i class="fa fa-magic mr-1 text-blue-400"></i>Kosongkan untuk auto-generate</span>';
    numEl.readOnly    = false;
    numEl.value       = '';
    numEl.placeholder = 'Kosongkan untuk auto-generate';
    numEl.style.background = '';
    return;
  }
  fetch('/formats/get?id=' + id)
    .then(r => r.json())
    .then(f => {
      if (f) {
        hint.innerHTML = '<span class="text-blue-600"><i class="fa fa-wand-magic-sparkles mr-1"></i>Pola: <code class=\"bg-blue-50 px-1 rounded\">' + f.format + '</code> · Urutan #' + (f.last_seq+1) + '</span>';
        numEl.readOnly    = true;
        numEl.value       = '';
        numEl.placeholder = 'Akan di-generate otomatis…';
        numEl.style.background = '#f8fafc';
      }
    });
});

// Pastikan readOnly field tidak blok submit
document.getElementById('invoiceForm')?.addEventListener('submit', function() {
  const numEl = document.getElementById('invNumber');
  if (numEl && numEl.readOnly) {
    numEl.removeAttribute('readonly');
    numEl.value = '';
  }
});

// ── Bind existing rows ─────────────────────────────────────────────────────
document.querySelectorAll('#itemsBody tr.item-row').forEach(row => bindRowEvents(row));

// Trigger initial recalc
document.querySelectorAll('#itemsBody tr.item-row').forEach(row => calcRow(row));

// Listen to all tax/discount changes
// Recalc when item-kind checkbox changes
document.getElementById('itemsBody').addEventListener('change', function(e) {
  if (e.target.classList.contains('item-kind')) recalc();
});

['discType','discVal','ppnEnabled','pph23Enabled','pph21Enabled','materaiEnabled','paidAmount'].forEach(id => {
  document.getElementById(id)?.addEventListener('change', recalc);
  document.getElementById(id)?.addEventListener('input', recalc);
});
document.querySelectorAll('[name="ppn_rate"],[name="pph23_rate"],[name="pph21_rate"],[name="materai_amount"]').forEach(el => {
  el.addEventListener('input', recalc);
});
document.querySelectorAll('[name="entity_type"]').forEach(el => el.addEventListener('change', recalc));

function switchEntity() {
  const type = document.querySelector('[name="entity_type"]:checked')?.value;
  document.getElementById('pph23Block')?.classList.toggle('hidden', type === 'pribadi');
  document.getElementById('pph21Block')?.classList.toggle('hidden', type !== 'pribadi');
  recalc();
}
document.addEventListener('DOMContentLoaded', () => { switchEntity(); recalc(); });
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

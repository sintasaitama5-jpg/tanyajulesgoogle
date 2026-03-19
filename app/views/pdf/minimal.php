<?php
$co      = $invoice['company_data'] ?? [];
$cl      = $invoice['client_data']  ?? [];
$items   = $invoice['items']        ?? [];
$cur     = $invoice['currency']     ?? 'IDR';
$lang    = $invoice['lang']         ?? 'id';

$subtotal   = (float)($invoice['subtotal'] ?? 0);
$discount   = (float)($invoice['discount'] ?? 0);
$discType   = $invoice['discount_type'] ?? 'percent';
$discAmount = $discType === 'percent' ? $subtotal * ($discount/100) : $discount;
$afterDisc  = $subtotal - $discAmount;

$ppnEnabled  = !empty($invoice['ppn_enabled']);
$ppnRate     = (float)($invoice['ppn_rate'] ?? 11);
$ppnAmount   = $ppnEnabled ? $afterDisc * ($ppnRate/100) : 0;

$pph23Enabled = !empty($invoice['pph23_enabled']);
$pph23Rate    = (float)($invoice['pph23_rate'] ?? 2);
$pph23Amount  = $pph23Enabled ? $afterDisc * ($pph23Rate/100) : 0;

$materaiEnabled = !empty($invoice['materai_enabled']);
$materaiAmount  = $materaiEnabled ? (float)($invoice['materai_amount'] ?? 10000) : 0;

$total      = (float)($invoice['total'] ?? 0);
$paid       = (float)($invoice['paid_amount'] ?? 0);
$balance    = $total - $paid;

$logoPath   = $co['logo_path'] ?? '';
$logoBase64 = '';
if ($logoPath) {
    $logoFile = BASE_PATH . '/uploads/' . $logoPath;
    if (file_exists($logoFile)) {
        $ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
        $mime = match($ext) { 'png' => 'image/png', 'gif' => 'image/gif', default => 'image/jpeg' };
        $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
    }
}

$stampPath   = $co['stamp_path'] ?? '';
$stampBase64 = '';
if ($stampPath) {
    $stampFile = BASE_PATH . '/uploads/' . $stampPath;
    if (file_exists($stampFile)) {
        $ext = strtolower(pathinfo($stampFile, PATHINFO_EXTENSION));
        $mime = match($ext) { 'png' => 'image/png', 'gif' => 'image/gif', default => 'image/jpeg' };
        $stampBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($stampFile));
    }
}

$typeLabels = [
    'barang'     => $lang === 'id' ? 'Invoice Barang'      : 'Goods Invoice',
    'jasa'       => $lang === 'id' ? 'Invoice Jasa'        : 'Service Invoice',
    'penginapan' => $lang === 'id' ? 'Invoice Penginapan'  : 'Accommodation Invoice',
];
$invTypeLabel = $typeLabels[$invoice['type'] ?? 'barang'] ?? 'Invoice';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9pt; color: #1a1a1a; background: white; }
  .page { padding: 0; }
  .header { border-bottom: 3px solid #1a1a1a; padding-bottom: 14px; margin-bottom: 20px; }
  .header-inner { display: table; width: 100%; }
  .header-left { display: table-cell; vertical-align: top; width: 60%; }
  .header-right { display: table-cell; vertical-align: top; text-align: right; }
  .company-name { font-size: 14pt; font-weight: bold; color: #111; }
  .company-detail { font-size: 8pt; color: #555; line-height: 1.6; margin-top: 3px; }
  .inv-title { font-size: 22pt; font-weight: bold; letter-spacing: -0.5px; color: #111; }
  .inv-type { font-size: 8pt; color: #777; margin-top: 2px; }
  .parties { display: table; width: 100%; margin-bottom: 20px; }
  .party { display: table-cell; width: 50%; vertical-align: top; }
  .party-label { font-size: 7pt; text-transform: uppercase; letter-spacing: 1px; color: #888; margin-bottom: 5px; }
  .party-name { font-size: 10pt; font-weight: bold; color: #111; }
  .party-detail { font-size: 8pt; color: #555; line-height: 1.7; }
  .meta { display: table-cell; vertical-align: top; text-align: right; width: 50%; }
  .meta table { margin-left: auto; }
  .meta td { font-size: 8.5pt; padding: 1.5px 0; }
  .meta td.label { color: #888; padding-right: 12px; }
  .meta td.value { font-weight: 600; text-align: right; }
  .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  .items-table thead tr { background: #1a1a1a; color: white; }
  .items-table thead th { padding: 7px 10px; font-size: 8pt; text-align: left; font-weight: 600; letter-spacing: 0.3px; }
  .items-table thead th.right { text-align: right; }
  .items-table tbody tr { border-bottom: 1px solid #e8e8e8; }
  .items-table tbody tr:nth-child(even) { background: #f9f9f9; }
  .items-table tbody td { padding: 7px 10px; font-size: 8.5pt; vertical-align: top; }
  .items-table tbody td.right { text-align: right; }
  .items-table tbody td.num { color: #888; font-size: 8pt; }
  .totals { display: table; width: 100%; }
  .totals-spacer { display: table-cell; width: 55%; vertical-align: top; }
  .totals-box { display: table-cell; width: 45%; }
  .total-row { display: table; width: 100%; border-bottom: 1px solid #eee; }
  .total-label { display: table-cell; font-size: 8.5pt; color: #666; padding: 4px 0; }
  .total-value { display: table-cell; text-align: right; font-size: 8.5pt; font-weight: 600; padding: 4px 0; }
  .total-final { display: table; width: 100%; background: #1a1a1a; color: white; margin-top: 2px; border-radius: 3px; }
  .total-final .total-label { color: white; font-weight: 700; font-size: 9pt; padding: 7px 10px; }
  .total-final .total-value { color: white; font-weight: 700; font-size: 9pt; padding: 7px 10px; }
  .terbilang { font-size: 7.5pt; color: #666; font-style: italic; margin-top: 6px; border: 1px solid #e5e5e5; padding: 6px 10px; border-radius: 3px; }
  .notes { margin-top: 20px; border-top: 1px solid #e5e5e5; padding-top: 12px; font-size: 8pt; color: #777; line-height: 1.6; }
  .signature { display: table; width: 100%; margin-top: 24px; }
  .sig-box { display: table-cell; width: 40%; vertical-align: top; border: 1px solid #e5e5e5; padding: 10px; border-radius: 4px; margin-right: 10px; }
  .sig-label { font-size: 7.5pt; color: #888; }
  .sig-space { height: 55px; }
  .sig-name { font-size: 8.5pt; font-weight: bold; border-top: 1px solid #aaa; padding-top: 4px; margin-top: 4px; }
  .sig-pos { font-size: 7.5pt; color: #666; }
  .logo-img { max-height: 55px; max-width: 140px; }
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
  <div class="header">
    <div class="header-inner">
      <div class="header-left">
        <?php if ($logoBase64): ?>
        <img src="<?= $logoBase64 ?>" class="logo-img" style="margin-bottom:6px;">
        <br>
        <?php endif; ?>
        <div class="company-name"><?= e($co['name'] ?? '') ?></div>
        <div class="company-detail">
          <?php if ($co['type'] ?? ''): ?><?= e($co['type']) ?><br><?php endif; ?>
          <?php if ($co['address'] ?? ''): ?><?= e($co['address']) ?><?php if ($co['city'] ?? ''): ?>, <?= e($co['city']) ?><?php endif; ?><br><?php endif; ?>
          <?php if ($co['phone'] ?? ''): ?>Telp: <?= e($co['phone']) ?><br><?php endif; ?>
          <?php if ($co['email'] ?? ''): ?><?= e($co['email']) ?><br><?php endif; ?>
          <?php if ($co['npwp'] ?? ''): ?>NPWP: <?= e($co['npwp']) ?><?php endif; ?>
        </div>
      </div>
      <div class="header-right">
        <div class="inv-title"><?= __t('invoice') ?></div>
        <div class="inv-type"><?= $invTypeLabel ?></div>
        <div style="margin-top:14px;">
          <table>
            <?php if (!empty($invoice['show_invoice_number'])): ?>
            <tr><td class="label" style="font-size:8pt;color:#888;padding-right:10px;">No.</td><td class="value" style="font-size:9pt;font-weight:700;"><?= e($invoice['invoice_number']) ?></td></tr>
            <?php endif; ?>
            <tr><td class="label" style="font-size:8pt;color:#888;padding-right:10px;"><?= $lang==='id'?'Tanggal':'Date' ?></td><td class="value" style="font-size:8.5pt;"><?= e($invoice['issue_date']) ?></td></tr>
            <?php if ($invoice['due_date']): ?><tr><td class="label" style="font-size:8pt;color:#888;padding-right:10px;"><?= $lang==='id'?'Jatuh Tempo':'Due Date' ?></td><td class="value" style="font-size:8.5pt;"><?= e($invoice['due_date']) ?></td></tr><?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Parties -->
  <div class="parties">
    <div class="party">
      <div class="party-label"><?= $lang==='id'?'Kepada':'Bill To' ?></div>
      <div class="party-name"><?= e($cl['name'] ?? '') ?></div>
      <div class="party-detail">
        <?php if ($cl['company'] ?? ''): ?><?= e($cl['company']) ?><br><?php endif; ?>
        <?php if ($cl['address'] ?? ''): ?><?= e($cl['address']) ?><?php if ($cl['city'] ?? ''): ?>, <?= e($cl['city']) ?><?php endif; ?><br><?php endif; ?>
        <?php if ($cl['phone'] ?? ''): ?>Telp: <?= e($cl['phone']) ?><br><?php endif; ?>
        <?php if ($cl['email'] ?? ''): ?><?= e($cl['email']) ?><br><?php endif; ?>
        <?php if ($cl['npwp'] ?? ''): ?>NPWP: <?= e($cl['npwp']) ?><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Items -->
  <table class="items-table">
    <thead>
      <tr>
        <th style="width:24px;">#</th>
        <th><?= $lang==='id'?'Deskripsi':'Description' ?></th>
        <th style="width:50px;" class="right"><?= $lang==='id'?'Qty':'Qty' ?></th>
        <th style="width:50px;"><?= $lang==='id'?'Satuan':'Unit' ?></th>
        <th style="width:100px;" class="right"><?= $lang==='id'?'Harga Satuan':'Unit Price' ?></th>
        <th style="width:100px;" class="right"><?= $lang==='id'?'Jumlah':'Amount' ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($items as $i => $item): ?>
      <tr>
        <td class="num"><?= $i+1 ?></td>
        <td><?= e($item['desc'] ?? '') ?></td>
        <td class="right"><?= number_format($item['qty'] ?? 0, 2, ',', '.') ?></td>
        <td><?= e($item['unit'] ?? '') ?></td>
        <td class="right"><?= formatCurrency($item['price'] ?? 0, $cur) ?></td>
        <td class="right"><?= formatCurrency($item['amount'] ?? 0, $cur) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Totals -->
  <div class="totals">
    <div class="totals-spacer"></div>
    <div class="totals-box">
      <div class="total-row"><div class="total-label"><?= $lang==='id'?'Subtotal':'Subtotal' ?></div><div class="total-value"><?= formatCurrency($subtotal,$cur) ?></div></div>
      <?php if ($discAmount): ?>
      <div class="total-row"><div class="total-label"><?= $lang==='id'?'Diskon':'Discount' ?><?= $discType==='percent'?" ($discount%)":" (nominal)" ?></div><div class="total-value">- <?= formatCurrency($discAmount,$cur) ?></div></div>
      <?php endif; ?>
      <?php if ($ppnEnabled): ?>
      <div class="total-row"><div class="total-label">PPN <?= $ppnRate ?>%</div><div class="total-value"><?= formatCurrency($ppnAmount,$cur) ?></div></div>
      <?php endif; ?>
      <?php if ($pph23Enabled): ?>
      <div class="total-row"><div class="total-label">PPh 23 <?= $pph23Rate ?>%</div><div class="total-value">- <?= formatCurrency($pph23Amount,$cur) ?></div></div>
      <?php endif; ?>
      <?php if ($materaiEnabled): ?>
      <div class="total-row"><div class="total-label"><?= $lang==='id'?'Materai':'Stamp Duty' ?></div><div class="total-value"><?= formatCurrency($materaiAmount,$cur) ?></div></div>
      <?php endif; ?>
      <div class="total-final">
        <div class="total-label"><?= $lang==='id'?'Total Tagihan':'Total Due' ?></div>
        <div class="total-value"><?= formatCurrency($total,$cur) ?></div>
      </div>
      <?php if ($paid > 0): ?>
      <div class="total-row"><div class="total-label"><?= $lang==='id'?'Sudah Dibayar':'Paid Amount' ?></div><div class="total-value"><?= formatCurrency($paid,$cur) ?></div></div>
      <div class="total-row"><div class="total-label" style="font-weight:bold;"><?= $lang==='id'?'Sisa Tagihan':'Balance Due' ?></div><div class="total-value" style="color:#c0392b;font-weight:bold;"><?= formatCurrency($balance,$cur) ?></div></div>
      <?php endif; ?>

      <?php if ($cur === 'IDR'): ?>
      <div class="terbilang"><em><?= $lang==='id'?'Terbilang:':'In Words:' ?></em> <?= terbilang($total) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Notes -->
  <?php if ($invoice['notes'] ?? ''): ?>
  <div class="notes"><strong><?= $lang==='id'?'Catatan:':'Notes:' ?></strong> <?= nl2br(e($invoice['notes'])) ?></div>
  <?php endif; ?>

  <!-- Signature -->
  <?php if (!empty($invoice['show_ttd']) && ($invoice['ttd_name'] || $invoice['ttd_name2'])): ?>
  <div class="signature" style="margin-top:30px;">
    <?php if ($invoice['ttd_name']): ?>
    <div class="sig-box">
      <div class="sig-label"><?= $lang==='id'?'Dibuat oleh,':'Prepared by,' ?></div>
      <div class="sig-space">
        <?php if ($stampBase64 && !empty($invoice['show_stamp'])): ?>
        <img src="<?= $stampBase64 ?>" style="max-height:50px;max-width:90px;opacity:0.8;margin-top:5px;">
        <?php endif; ?>
      </div>
      <div class="sig-name"><?= e($invoice['ttd_name']) ?></div>
      <?php if ($invoice['ttd_position']): ?><div class="sig-pos"><?= e($invoice['ttd_position']) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if ($invoice['ttd_name2']): ?>
    <div class="sig-box" style="margin-left:20px;">
      <div class="sig-label"><?= $lang==='id'?'Diterima oleh,':'Received by,' ?></div>
      <div class="sig-space"></div>
      <div class="sig-name"><?= e($invoice['ttd_name2']) ?></div>
      <?php if ($invoice['ttd_position2']): ?><div class="sig-pos"><?= e($invoice['ttd_position2']) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</div>
</body>
</html>

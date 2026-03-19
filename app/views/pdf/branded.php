<?php
$co    = $invoice['company_data'] ?? [];
$cl    = $invoice['client_data']  ?? [];
$items = $invoice['items']        ?? [];
$cur   = $invoice['currency']     ?? 'IDR';
$lang  = $invoice['lang']         ?? 'id';

$subtotal    = (float)($invoice['subtotal'] ?? 0);
$discount    = (float)($invoice['discount'] ?? 0);
$discType    = $invoice['discount_type'] ?? 'percent';
$discAmount  = $discType === 'percent' ? $subtotal * ($discount/100) : $discount;
$afterDisc   = $subtotal - $discAmount;
$ppnEnabled  = !empty($invoice['ppn_enabled']);
$ppnRate     = (float)($invoice['ppn_rate'] ?? 11);
$ppnAmount   = $ppnEnabled ? $afterDisc * ($ppnRate/100) : 0;
$pph23Enabled = !empty($invoice['pph23_enabled']);
$pph23Rate    = (float)($invoice['pph23_rate'] ?? 2);
$pph23Amount  = $pph23Enabled ? $afterDisc * ($pph23Rate/100) : 0;
$materaiEnabled = !empty($invoice['materai_enabled']);
$materaiAmount  = $materaiEnabled ? (float)($invoice['materai_amount'] ?? 10000) : 0;
$total   = (float)($invoice['total'] ?? 0);
$paid    = (float)($invoice['paid_amount'] ?? 0);
$balance = $total - $paid;

$color1    = $co['brand_color']  ?? '#1e3a8a';
$color2    = $co['brand_color2'] ?? '#3b82f6';
$style     = $co['brand_style']  ?? 'solid';

$headerBg  = $style === 'gradient'
    ? "background: linear-gradient(135deg, $color1, $color2);"
    : "background: $color1;";

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
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'DejaVu Sans',Arial,sans-serif; font-size:9pt; color:#1a1a1a; background:white; }
  .header { <?= $headerBg ?> color:white; padding:22px 24px; margin:-10px -10px 20px -10px; }
  .header-inner { display:table; width:100%; }
  .header-left  { display:table-cell; vertical-align:middle; width:65%; }
  .header-right { display:table-cell; vertical-align:middle; text-align:right; }
  .company-name  { font-size:15pt; font-weight:bold; color:white; }
  .company-detail { font-size:7.5pt; color:rgba(255,255,255,0.8); line-height:1.6; margin-top:4px; }
  .inv-badge { display:inline-block; background:rgba(255,255,255,0.2); border-radius:4px; padding:4px 12px; margin-bottom:6px; }
  .inv-title { font-size:18pt; font-weight:bold; color:white; }
  .inv-type  { font-size:7.5pt; color:rgba(255,255,255,0.75); margin-top:2px; }
  .inv-meta  { margin-top:8px; }
  .inv-meta td { font-size:8pt; color:rgba(255,255,255,0.9); padding:1px 0; }
  .inv-meta td.label { padding-right:10px; color:rgba(255,255,255,0.6); }
  .section { padding:0 2px; }
  .parties { display:table; width:100%; margin-bottom:20px; }
  .party { display:table-cell; width:50%; vertical-align:top; }
  .party-label { font-size:7pt; text-transform:uppercase; letter-spacing:1px; color:<?= $color1 ?>; font-weight:bold; margin-bottom:5px; border-bottom:2px solid <?= $color1 ?>; padding-bottom:3px; }
  .party-name   { font-size:10pt; font-weight:bold; }
  .party-detail { font-size:8pt; color:#555; line-height:1.7; }
  .items-table { width:100%; border-collapse:collapse; margin-bottom:16px; }
  .items-table thead tr { background:<?= $color1 ?>; color:white; }
  .items-table thead th { padding:7px 10px; font-size:8pt; text-align:left; }
  .items-table thead th.right { text-align:right; }
  .items-table tbody tr { border-bottom:1px solid #eee; }
  .items-table tbody tr:nth-child(even) { background:#f7f9ff; }
  .items-table tbody td { padding:7px 10px; font-size:8.5pt; vertical-align:top; }
  .items-table tbody td.right { text-align:right; }
  .totals { display:table; width:100%; }
  .totals-left  { display:table-cell; width:55%; }
  .totals-right { display:table-cell; width:45%; }
  .total-row { display:table; width:100%; }
  .total-label { display:table-cell; font-size:8.5pt; color:#555; padding:3px 0; }
  .total-value { display:table-cell; text-align:right; font-size:8.5pt; font-weight:600; padding:3px 0; }
  .total-final { background:<?= $color1 ?>; color:white; display:table; width:100%; border-radius:4px; padding:2px 0; margin-top:3px; }
  .total-final .total-label { color:rgba(255,255,255,0.9); font-weight:700; font-size:9pt; padding:7px 10px; }
  .total-final .total-value { color:white; font-weight:700; font-size:9pt; padding:7px 10px; }
  .terbilang { font-size:7.5pt; color:#666; font-style:italic; margin-top:6px; border:1px solid #ddd; padding:5px 8px; border-radius:3px; border-left:3px solid <?= $color1 ?>; }
  .notes { margin-top:18px; border-top:2px solid <?= $color1 ?>; padding-top:10px; font-size:8pt; color:#666; }
  .sig-block { margin-top:28px; display:table; width:100%; }
  .sig-box { display:table-cell; width:40%; border:1px solid #ddd; padding:10px 12px; border-radius:4px; border-top:3px solid <?= $color1 ?>; }
  .sig-label { font-size:7.5pt; color:#888; }
  .sig-space { height:55px; }
  .sig-name  { font-size:8.5pt; font-weight:bold; border-top:1px solid #bbb; padding-top:4px; margin-top:4px; }
  .sig-pos   { font-size:7.5pt; color:#666; }
  .logo-img  { max-height:50px; max-width:130px; }
</style>
</head>
<body>

<!-- Branded Header -->
<div class="header">
  <div class="header-inner">
    <div class="header-left">
      <?php if ($logoBase64): ?>
      <img src="<?= $logoBase64 ?>" class="logo-img" style="margin-bottom:6px;"><br>
      <?php endif; ?>
      <div class="company-name"><?= e($co['name'] ?? '') ?></div>
      <div class="company-detail">
        <?php if ($co['type'] ?? ''): ?><?= e($co['type']) ?><br><?php endif; ?>
        <?php if ($co['address'] ?? ''): ?><?= e($co['address']) ?><?php if ($co['city'] ?? ''): ?>, <?= e($co['city']) ?><?php endif; ?><br><?php endif; ?>
        <?php if ($co['phone'] ?? ''): ?>T: <?= e($co['phone']) ?> <?php endif; ?>
        <?php if ($co['email'] ?? ''): ?> | <?= e($co['email']) ?><?php endif; ?>
        <?php if ($co['npwp'] ?? ''): ?><br>NPWP: <?= e($co['npwp']) ?><?php endif; ?>
      </div>
    </div>
    <div class="header-right">
      <div class="inv-badge">
        <div class="inv-title"><?= __t('invoice') ?></div>
      </div>
      <div class="inv-type"><?= $invTypeLabel ?></div>
      <div class="inv-meta">
        <table style="margin-left:auto;">
          <?php if (!empty($invoice['show_invoice_number'])): ?>
          <tr><td class="label">No.</td><td style="font-weight:bold;font-size:9pt;color:white;"><?= e($invoice['invoice_number']) ?></td></tr>
          <?php endif; ?>
          <tr><td class="label"><?= $lang==='id'?'Tanggal':'Date' ?></td><td><?= e($invoice['issue_date']) ?></td></tr>
          <?php if ($invoice['due_date']): ?><tr><td class="label"><?= $lang==='id'?'Jatuh Tempo':'Due Date' ?></td><td><?= e($invoice['due_date']) ?></td></tr><?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Parties -->
<div class="parties section">
  <div class="party">
    <div class="party-label"><?= $lang==='id'?'Kepada':'Bill To' ?></div>
    <div class="party-name"><?= e($cl['name'] ?? '') ?></div>
    <div class="party-detail">
      <?php if ($cl['company'] ?? ''): ?><?= e($cl['company']) ?><br><?php endif; ?>
      <?php if ($cl['address'] ?? ''): ?><?= e($cl['address']) ?><?php if ($cl['city'] ?? ''): ?>, <?= e($cl['city']) ?><?php endif; ?><br><?php endif; ?>
      <?php if ($cl['phone'] ?? ''): ?>T: <?= e($cl['phone']) ?><br><?php endif; ?>
      <?php if ($cl['email'] ?? ''): ?><?= e($cl['email']) ?><br><?php endif; ?>
      <?php if ($cl['npwp'] ?? ''): ?>NPWP: <?= e($cl['npwp']) ?><?php endif; ?>
    </div>
  </div>
</div>

<!-- Items -->
<div class="section">
<table class="items-table">
  <thead>
    <tr>
      <th style="width:22px;">#</th>
      <th><?= $lang==='id'?'Deskripsi':'Description' ?></th>
      <th class="right" style="width:50px;"><?= $lang==='id'?'Qty':'Qty' ?></th>
      <th style="width:48px;"><?= $lang==='id'?'Sat.':'Unit' ?></th>
      <th class="right" style="width:100px;"><?= $lang==='id'?'Harga':'Price' ?></th>
      <th class="right" style="width:100px;"><?= $lang==='id'?'Jumlah':'Amount' ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr>
      <td style="color:#aaa;font-size:7.5pt;"><?= $i+1 ?></td>
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
  <div class="totals-left"></div>
  <div class="totals-right">
    <div class="total-row"><div class="total-label"><?= $lang==='id'?'Subtotal':'Subtotal' ?></div><div class="total-value"><?= formatCurrency($subtotal,$cur) ?></div></div>
    <?php if ($discAmount): ?>
    <div class="total-row"><div class="total-label"><?= $lang==='id'?'Diskon':'Discount' ?></div><div class="total-value">- <?= formatCurrency($discAmount,$cur) ?></div></div>
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
    <div class="total-row"><div class="total-label"><?= $lang==='id'?'Sudah Dibayar':'Paid' ?></div><div class="total-value"><?= formatCurrency($paid,$cur) ?></div></div>
    <div class="total-row"><div class="total-label" style="font-weight:bold;color:#c0392b;"><?= $lang==='id'?'Sisa':'Balance' ?></div><div class="total-value" style="color:#c0392b;font-weight:bold;"><?= formatCurrency($balance,$cur) ?></div></div>
    <?php endif; ?>

    <?php if ($cur === 'IDR'): ?>
    <div class="terbilang"><em><?= $lang==='id'?'Terbilang':'In Words' ?>:</em> <?= terbilang($total) ?></div>
    <?php endif; ?>
  </div>
</div>
</div>

<?php if ($invoice['notes'] ?? ''): ?>
<div class="notes section"><strong><?= $lang==='id'?'Catatan:':'Notes:' ?></strong> <?= nl2br(e($invoice['notes'])) ?></div>
<?php endif; ?>

<?php if (!empty($invoice['show_ttd']) && ($invoice['ttd_name'] || $invoice['ttd_name2'])): ?>
<div class="sig-block section">
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

</body>
</html>

<?php
namespace App\Controllers;

use App\Auth;
use App\Database;
use App\Lang;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class InvoiceController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::get();

        $type   = $_GET['type']   ?? '';
        $status = $_GET['status'] ?? '';
        $search = $_GET['q']      ?? '';

        $where  = '1=1';
        $params = [];

        if ($type)   { $where .= ' AND i.type = ?';   $params[] = $type; }
        if ($status) { $where .= ' AND i.status = ?'; $params[] = $status; }
        if ($search) {
            $where .= ' AND (i.invoice_number LIKE ? OR c.name LIKE ? OR i.type LIKE ?)';
            $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
        }

        $stmt = $db->prepare("
            SELECT i.*, c.name as client_name, c.company as client_company
            FROM invoices i
            LEFT JOIN clients c ON i.client_id = c.id
            WHERE $where
            ORDER BY i.created_at DESC
        ");
        $stmt->execute($params);
        $invoices = $stmt->fetchAll();

        render('invoice/index', compact('invoices', 'type', 'status', 'search'));
    }

    public function create(): void
    {
        Auth::require();
        $db = Database::get();

        $companies = $db->query("SELECT * FROM companies ORDER BY is_default DESC, name ASC")->fetchAll();
        $clients   = $db->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();
        $formats   = $db->query("SELECT * FROM invoice_formats ORDER BY name ASC")->fetchAll();
        $invoice   = null;

        $defaultCompany = $db->query("SELECT * FROM companies WHERE is_default=1 LIMIT 1")->fetch() ?: null;

        render('invoice/form', compact('invoice', 'companies', 'clients', 'formats', 'defaultCompany'));
    }

    public function edit(): void
    {
        Auth::require();
        $db = Database::get();

        $id      = (int)($_GET['id'] ?? 0);
        $invoice = $db->prepare("SELECT * FROM invoices WHERE id=?")->execute([$id]) ?
            $db->prepare("SELECT * FROM invoices WHERE id=?")->execute([$id]) : null;

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id=?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) { flash('error', __t('not_found')); redirect('/invoices'); }

        $invoice['items']        = json_decode($invoice['items'] ?? '[]', true) ?: [];
        $invoice['company_data'] = json_decode($invoice['company_data'] ?? '{}', true) ?: [];
        $invoice['client_data']  = json_decode($invoice['client_data'] ?? '{}', true) ?: [];

        $companies = $db->query("SELECT * FROM companies ORDER BY is_default DESC, name ASC")->fetchAll();
        $clients   = $db->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();
        $formats   = $db->query("SELECT * FROM invoice_formats ORDER BY name ASC")->fetchAll();
        $defaultCompany = null;

        render('invoice/form', compact('invoice', 'companies', 'clients', 'formats', 'defaultCompany'));
    }

    public function save(): void
    {
        Auth::require();
        $db = Database::get();

        $isEdit   = !empty($_POST['invoice_id']);
        $id       = (int)($_POST['invoice_id'] ?? 0);
        $formatId = (int)($_POST['format_id'] ?? 0);

        // Invoice number logic
        if ($isEdit) {
            $stmt = $db->prepare("SELECT invoice_number FROM invoices WHERE id=?");
            $stmt->execute([$id]);
            $inv = $stmt->fetch();
            $invoiceNumber = trim($_POST['invoice_number'] ?? '') ?: $inv['invoice_number'];
        } else {
            $manualNumber = trim($_POST['invoice_number'] ?? '');
            if ($formatId) {
                $stmt = $db->prepare("SELECT * FROM invoice_formats WHERE id=?");
                $stmt->execute([$formatId]);
                $fmt = $stmt->fetch();
                $invoiceNumber = $fmt
                    ? generateInvoiceNumber($fmt, $_POST['type'] ?? '')
                    : ($manualNumber ?: generateFallbackInvoiceNumber($_POST['type'] ?? 'INV'));
            } else {
                $invoiceNumber = $manualNumber ?: generateFallbackInvoiceNumber($_POST['type'] ?? 'INV');
            }
        }

        // Items
        $items      = [];
        $descs      = $_POST['item_desc']  ?? [];
        $qtys       = $_POST['item_qty']   ?? [];
        $units      = $_POST['item_unit']  ?? [];
        $prices     = $_POST['item_price'] ?? [];
        $amounts    = $_POST['item_amount']?? [];
        $kinds      = $_POST['item_kind']  ?? [];

        // item_kind checkboxes hanya mengirim value jika dicentang, jadi perlu mapping per index
        // Karena checkbox tidak mengirim index, kita pakai item_kind_idx hidden field trick
        // Fallback: parse dari item_kind_map
        $kindMap    = $_POST['item_kind_map'] ?? [];

        foreach ($descs as $i => $desc) {
            if (trim($desc) === '') continue;
            $items[] = [
                'desc'    => trim($desc),
                'qty'     => (float)($qtys[$i]    ?? 0),
                'unit'    => trim($units[$i]   ?? ''),
                'price'   => (float)($prices[$i]  ?? 0),
                'amount'  => (float)($amounts[$i] ?? 0),
                'is_jasa' => !empty($kindMap[$i]) ? 1 : 0,
            ];
        }

        // Totals
        $subtotal    = array_sum(array_column($items, 'amount'));
        $discType    = $_POST['discount_type'] ?? 'percent';
        $discVal     = (float)($_POST['discount'] ?? 0);
        $discAmount  = $discType === 'percent' ? $subtotal * ($discVal / 100) : $discVal;
        $afterDisc   = $subtotal - $discAmount;

        $ppnEnabled  = !empty($_POST['ppn_enabled']);
        $ppnRate     = (float)($_POST['ppn_rate'] ?? 11);
        $ppnAmount   = $ppnEnabled ? $afterDisc * ($ppnRate / 100) : 0;

        $entityType   = $_POST['entity_type'] ?? 'badan';

        // PPh 23 (Badan Usaha) — 2% x bruto jasa
        $pph23Enabled = ($entityType === 'badan') && !empty($_POST['pph23_enabled']);
        $pph23Rate    = (float)($_POST['pph23_rate'] ?? 2);

        // PPh 21 (Orang Pribadi) — tarif progresif x 50% bruto
        $pph21Enabled = ($entityType === 'pribadi') && !empty($_POST['pph21_enabled']);
        $pph21Rate    = (float)($_POST['pph21_rate'] ?? 5);

        // Base: item jasa saja, atau seluruh afterDisc jika tidak ada per-item flag
        $jasaSubtotal = array_sum(array_map(fn($it) => !empty($it['is_jasa']) ? $it['amount'] : 0, $items));
        $jasaBase     = $jasaSubtotal > 0 ? $jasaSubtotal : $afterDisc;

        $pph23Amount  = $pph23Enabled ? $jasaBase * ($pph23Rate / 100) : 0;
        $pph21Base    = $jasaBase * 0.5; // DPP PPh 21 = 50% bruto
        $pph21Amount  = $pph21Enabled ? $pph21Base * ($pph21Rate / 100) : 0;

        $materaiEnabled = !empty($_POST['materai_enabled']);
        $materaiAmount  = $materaiEnabled ? (float)($_POST['materai_amount'] ?? 10000) : 0;

        $total = $afterDisc + $ppnAmount - $pph23Amount - $pph21Amount + $materaiAmount;

        // Company & client snapshots
        $companyId   = (int)($_POST['company_id'] ?? 0);
        $clientId    = (int)($_POST['client_id'] ?? 0);

        $companyData = [
            'name'     => $_POST['company_name']  ?? '',
            'type'     => $_POST['company_type']  ?? '',
            'address'  => $_POST['company_address']?? '',
            'city'     => $_POST['company_city']  ?? '',
            'phone'    => $_POST['company_phone'] ?? '',
            'email'    => $_POST['company_email'] ?? '',
            'npwp'     => $_POST['company_npwp']  ?? '',
            'logo_path'=> $_POST['company_logo']  ?? '',
            'brand_color'  => $_POST['company_brand_color']  ?? '#1e3a8a',
            'brand_color2' => $_POST['company_brand_color2'] ?? '#3b82f6',
            'brand_style'  => $_POST['company_brand_style']  ?? 'solid',
        ];

        $clientData = [
            'name'    => $_POST['client_name']    ?? '',
            'company' => $_POST['client_company'] ?? '',
            'address' => $_POST['client_address'] ?? '',
            'city'    => $_POST['client_city']    ?? '',
            'phone'   => $_POST['client_phone']   ?? '',
            'email'   => $_POST['client_email']   ?? '',
            'npwp'    => $_POST['client_npwp']    ?? '',
        ];

        // Save client to address book if checked
        if (!empty($clientData['name'])) { // auto-save always
            if ($clientId) {
                $db->prepare("UPDATE clients SET name=?,company=?,address=?,city=?,phone=?,email=?,npwp=? WHERE id=?")
                   ->execute([$clientData['name'],$clientData['company'],$clientData['address'],$clientData['city'],$clientData['phone'],$clientData['email'],$clientData['npwp'],$clientId]);
            } else {
                $stmt = $db->prepare("INSERT INTO clients (name,company,address,city,phone,email,npwp,created_by) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$clientData['name'],$clientData['company'],$clientData['address'],$clientData['city'],$clientData['phone'],$clientData['email'],$clientData['npwp'],Auth::id()]);
                $clientId = (int)$db->lastInsertId();
            }
        }

        $data = [
            'invoice_number'  => $invoiceNumber,
            'type'            => $_POST['type']        ?? 'barang',
            'status'          => $_POST['status']      ?? 'unpaid',
            'format_id'       => $formatId,
            'company_id'      => $companyId,
            'client_id'       => $clientId,
            'company_data'    => json_encode($companyData),
            'client_data'     => json_encode($clientData),
            'issue_date'      => $_POST['issue_date']  ?? '',
            'due_date'        => $_POST['due_date']    ?? '',
            'template'        => $_POST['template']    ?? 'minimal',
            'orientation'     => $_POST['orientation'] ?? 'portrait',
            'items'           => json_encode($items),
            'subtotal'        => $subtotal,
            'discount'        => $discVal,
            'discount_type'   => $discType,
            'ppn_enabled'     => (int)$ppnEnabled,
            'ppn_rate'        => $ppnRate,
            'entity_type'     => $entityType,
            'pph23_enabled'   => (int)$pph23Enabled,
            'pph23_rate'      => $pph23Rate,
            'pph21_enabled'   => (int)$pph21Enabled,
            'pph21_rate'      => $pph21Rate,
            'materai_enabled' => (int)$materaiEnabled,
            'materai_amount'  => $materaiAmount,
            'total'           => $total,
            'paid_amount'     => (float)($_POST['paid_amount'] ?? 0),
            'currency'        => $_POST['currency']    ?? 'IDR',
            'notes'           => $_POST['notes']       ?? '',
            'show_ttd'        => (int)!empty($_POST['show_ttd']),
            'show_stamp'      => (int)!empty($_POST['show_stamp']),
            'ttd_name'        => $_POST['ttd_name']    ?? '',
            'ttd_position'    => $_POST['ttd_position']?? '',
            'ttd_name2'       => $_POST['ttd_name2']   ?? '',
            'ttd_position2'   => $_POST['ttd_position2']?? '',
            'show_invoice_number' => (int)!empty($_POST['show_invoice_number']),
            'show_watermark'  => (int)!empty($_POST['show_watermark']),
            'lang'            => $_POST['inv_lang']    ?? 'id',
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($isEdit) {
            $sets   = implode(', ', array_map(fn($k) => "$k=?", array_keys($data)));
            $values = array_values($data);
            $values[] = $id;
            $db->prepare("UPDATE invoices SET $sets WHERE id=?")->execute($values);
        } else {
            $data['created_by'] = Auth::id();
            $cols   = implode(', ', array_keys($data));
            $places = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO invoices ($cols) VALUES ($places)")->execute(array_values($data));
            $id = (int)$db->lastInsertId();
        }

        // Regenerate PDF
        $this->generatePdf($id);

        flash('success', __t('saved'));
        redirect('/invoices/edit?id=' . $id);
    }

    public function delete(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::get();

        $stmt = $db->prepare("SELECT pdf_path FROM invoices WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row && $row['pdf_path']) {
            $pdfFile = BASE_PATH . '/pdfs/' . $row['pdf_path'];
            if (file_exists($pdfFile)) unlink($pdfFile);
        }

        $db->prepare("DELETE FROM invoices WHERE id=?")->execute([$id]);
        flash('success', __t('deleted'));
        redirect('/invoices');
    }

    public function updateStatus(): void
    {
        Auth::require();
        $id     = (int)($_GET['id'] ?? 0);
        $status = $_GET['status'] ?? 'unpaid';
        if (!in_array($status, ['unpaid','paid','cancelled'])) redirect('/invoices');

        $db = Database::get();
        $db->prepare("UPDATE invoices SET status=?, updated_at=? WHERE id=?")
           ->execute([$status, date('Y-m-d H:i:s'), $id]);

        // Regenerate PDF with new watermark
        $this->generatePdf($id);

        flash('success', __t('saved'));
        redirect('/invoices');
    }

    public function pdf(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $this->generatePdf($id, true);
    }

    public function download(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::get();

        $stmt = $db->prepare("SELECT * FROM invoices WHERE id=?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) { http_response_code(404); exit; }

        $pdfFile = BASE_PATH . '/pdfs/' . $invoice['pdf_path'];
        if (!file_exists($pdfFile)) {
            $this->generatePdf($id);
            $stmt->execute([$id]);
            $invoice = $stmt->fetch();
            $pdfFile = BASE_PATH . '/pdfs/' . $invoice['pdf_path'];
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $invoice['invoice_number'] . '.pdf"');
        header('Content-Length: ' . filesize($pdfFile));
        readfile($pdfFile);
        exit;
    }

    public function getFormat(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM invoice_formats WHERE id=?");
        $stmt->execute([$id]);
        $fmt = $stmt->fetch();

        header('Content-Type: application/json');
        echo json_encode($fmt ?: []);
        exit;
    }

    public function saveFormat(): void
    {
        Auth::require();
        $db = Database::get();
        $id = (int)($_POST['format_id'] ?? 0);

        $data = [
            'name'         => trim($_POST['format_name']  ?? ''),
            'format'       => trim($_POST['format_pattern']?? ''),
            'padding'      => (int)($_POST['format_padding']?? 4),
            'invoice_type' => $_POST['format_type']       ?? 'all',
            'reset_yearly' => (int)!empty($_POST['format_reset_yearly']),
        ];

        if ($id) {
            $sets = implode(', ', array_map(fn($k) => "$k=?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE invoice_formats SET $sets WHERE id=?")->execute($vals);
        } else {
            $data['created_by'] = Auth::id();
            $data['last_year']  = (int)date('Y');
            $cols   = implode(', ', array_keys($data));
            $places = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO invoice_formats ($cols) VALUES ($places)")->execute(array_values($data));
        }

        flash('success', __t('saved'));
        redirect('/settings');
    }

    public function deleteFormat(): void
    {
        Auth::require();
        $id = (int)($_POST['format_id'] ?? 0);
        Database::get()->prepare("DELETE FROM invoice_formats WHERE id=?")->execute([$id]);
        flash('success', __t('deleted'));
        redirect('/settings');
    }

    // ── PDF Generation ────────────────────────────────────────────────────
    private function generatePdf(int $id, bool $stream = false): void
    {
        $db = Database::get();
        $stmt = $db->prepare("SELECT * FROM invoices WHERE id=?");
        $stmt->execute([$id]);
        $invoice = $stmt->fetch();

        if (!$invoice) {
            if ($stream) { http_response_code(404); exit; }
            return;
        }

        $invoice['items']        = json_decode($invoice['items']       ?? '[]', true)  ?: [];
        $invoice['company_data'] = json_decode($invoice['company_data']?? '{}', true)  ?: [];
        $invoice['client_data']  = json_decode($invoice['client_data'] ?? '{}', true)  ?: [];

        // Render HTML template
        $template = $invoice['template'] ?? 'minimal';
        $invLang  = $invoice['lang'] ?? 'id';

        // Temporarily switch language for PDF
        \App\Lang::load($invLang);

        ob_start();
        $data = compact('invoice');
        extract($data);
        require BASE_PATH . '/views/pdf/' . $template . '.php';
        $html = ob_get_clean();

        // Restore current session language
        $sessionLang = $_SESSION['lang'] ?? 'id';
        \App\Lang::load($sessionLang);

        $isLandscape = ($invoice['orientation'] ?? 'portrait') === 'landscape';

        $mpdfConfig = [
            'mode'              => 'utf-8',
            'format'            => $isLandscape ? 'A4-L' : 'A4',
            'orientation'       => $isLandscape ? 'L' : 'P',
            'margin_left'       => 10,
            'margin_right'      => 10,
            'margin_top'        => 10,
            'margin_bottom'     => 15,
            'margin_header'     => 0,
            'margin_footer'     => 5,
            'tempDir'           => BASE_PATH . '/data/mpdf_tmp',
        ];

        @mkdir(BASE_PATH . '/data/mpdf_tmp', 0775, true);

        $mpdf = new Mpdf($mpdfConfig);
        $mpdf->SetTitle($invoice['invoice_number']);
        $mpdf->SetAuthor('InvoiceApp');

        // Watermark — opsional
        if (!empty($invoice['show_watermark'])) {
            if ($invoice['status'] === 'paid') {
                $mpdf->SetWatermarkText(__t('watermark_paid'));
                $mpdf->showWatermarkText = true;
                $mpdf->watermarkTextAlpha = 0.07;
            } elseif ($invoice['status'] === 'unpaid') {
                $mpdf->SetWatermarkText(__t('watermark_unpaid'));
                $mpdf->showWatermarkText = true;
                $mpdf->watermarkTextAlpha = 0.05;
            }
        }

        $mpdf->WriteHTML($html);

        $filename = preg_replace('/[^A-Za-z0-9\-_]/', '_', $invoice['invoice_number']) . '_' . $id . '.pdf';
        $pdfDir   = BASE_PATH . '/pdfs';
        @mkdir($pdfDir, 0775, true);

        // Delete old PDF
        if ($invoice['pdf_path']) {
            $old = $pdfDir . '/' . $invoice['pdf_path'];
            if (file_exists($old)) @unlink($old);
        }

        $mpdf->Output($pdfDir . '/' . $filename, 'F');

        // Update record
        $db->prepare("UPDATE invoices SET pdf_path=? WHERE id=?")->execute([$filename, $id]);

        if ($stream) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . $filename . '"');
            readfile($pdfDir . '/' . $filename);
            exit;
        }
    }
}

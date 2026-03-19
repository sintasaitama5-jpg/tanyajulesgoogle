<?php

use App\Lang;

function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

function render(string $view, array $data = []): void
{
    extract($data);
    $viewFile = dirname(__DIR__) . '/views/' . $view . '.php';
    if (!file_exists($viewFile)) {
        die("View not found: $view");
    }
    require $viewFile;
}

function __t(string $key, array $replace = []): string
{
    return Lang::get($key, $replace);
}

function e(mixed $val): string
{
    return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
}

function setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $db = \App\Database::get();
        $stmt = $db->prepare("SELECT value FROM settings WHERE key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? $row['value'] : $default;
    }
    return $cache[$key];
}

function formatCurrency(float $amount, string $currency = 'IDR'): string
{
    if ($currency === 'IDR') {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
    return $currency . ' ' . number_format($amount, 2, '.', ',');
}

function terbilang(float $number): string
{
    $number = (int) round($number);
    if ($number === 0) return 'Nol';

    $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan',
              'Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
              'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];

    function _tb(int $n, array $w): string {
        if ($n < 20) return $w[$n];
        if ($n < 100) return $w[(int)($n/10)+10-10] . ' Puluh' . ($n % 10 ? ' ' . $w[$n % 10] : '');
        if ($n < 200) return 'Seratus' . ($n % 100 ? ' ' . _tb($n % 100, $w) : '');
        if ($n < 1000) return $w[(int)($n/100)] . ' Ratus' . ($n % 100 ? ' ' . _tb($n % 100, $w) : '');
        if ($n < 2000) return 'Seribu' . ($n % 1000 ? ' ' . _tb($n % 1000, $w) : '');
        if ($n < 1000000) return _tb((int)($n/1000), $w) . ' Ribu' . ($n % 1000 ? ' ' . _tb($n % 1000, $w) : '');
        if ($n < 1000000000) return _tb((int)($n/1000000), $w) . ' Juta' . ($n % 1000000 ? ' ' . _tb($n % 1000000, $w) : '');
        return _tb((int)($n/1000000000), $w) . ' Miliar' . ($n % 1000000000 ? ' ' . _tb($n % 1000000000, $w) : '');
    }

    $negative = $number < 0;
    $result = _tb(abs($number), $words);
    return ($negative ? 'Minus ' : '') . $result . ' Rupiah';
}

function generateInvoiceNumber(array $format, string $type = ''): string
{
    $db = \App\Database::get();
    $year = date('Y');
    $month = date('m');

    // Reset seq if yearly reset enabled and year changed
    if ($format['reset_yearly'] && $format['last_year'] != $year) {
        $db->prepare("UPDATE invoice_formats SET last_seq = 0, last_year = ? WHERE id = ?")
           ->execute([$year, $format['id']]);
        $format['last_seq'] = 0;
    }

    $seq = $format['last_seq'] + 1;
    $padded = str_pad($seq, $format['padding'], '0', STR_PAD_LEFT);

    $typeMap = ['barang' => 'BRG', 'jasa' => 'JSA', 'barang_jasa' => 'BJA', 'jasa_maintenance' => 'MNT', 'jasa_service' => 'SVC', 'penginapan' => 'PNP'];
    $typeCode = $typeMap[$type] ?? strtoupper(substr($type, 0, 3));

    $monthNames = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    $fmt = $format['format'];
    $fmt = str_replace('{YEAR}',  $year,                $fmt);
    $fmt = str_replace('{MONTH}', $month,               $fmt);
    $fmt = str_replace('{MON}',   $monthNames[(int)$month], $fmt);
    $fmt = str_replace('{TYPE}',  $typeCode,            $fmt);
    $fmt = str_replace('{SEQ}',   $padded,              $fmt);

    // Check uniqueness
    $check = $db->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number = ?");
    $check->execute([$fmt]);
    if ($check->fetchColumn() > 0) {
        // Increment and retry
        $seq++;
        $padded = str_pad($seq, $format['padding'], '0', STR_PAD_LEFT);
        $fmt = str_replace($padded, str_pad($seq - 1, $format['padding'], '0', STR_PAD_LEFT), $fmt);
    }

    // Save new last_seq
    $db->prepare("UPDATE invoice_formats SET last_seq = ?, last_year = ? WHERE id = ?")
       ->execute([$seq, $year, $format['id']]);

    return $fmt;
}

function generateFallbackInvoiceNumber(string $type = 'INV'): string
{
    $db  = \App\Database::get();
    $typeMap = ['barang' => 'BRG', 'jasa' => 'JSA', 'barang_jasa' => 'BJA', 'jasa_maintenance' => 'MNT', 'jasa_service' => 'SVC', 'penginapan' => 'PNP'];
    $prefix  = $typeMap[$type] ?? 'INV';
    $year    = date('Y');
    $month   = date('m');

    // Get next seq from settings
    $key  = 'fallback_seq_' . $year;
    $stmt = $db->prepare("SELECT value FROM settings WHERE key=?");
    $stmt->execute([$key]);
    $row  = $stmt->fetch();
    $seq  = $row ? ((int)$row['value'] + 1) : 1;

    // Ensure unique
    do {
        $number = $prefix . '/' . $year . '/' . $month . '/' . str_pad($seq, 4, '0', STR_PAD_LEFT);
        $check  = $db->prepare("SELECT COUNT(*) FROM invoices WHERE invoice_number=?");
        $check->execute([$number]);
        if ($check->fetchColumn() == 0) break;
        $seq++;
    } while (true);

    // Save seq
    $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)")->execute([$key, $seq]);
    return $number;
}

function uploadFile(string $key, string $dir, array $allowed = ['jpg','jpeg','png','gif']): string
{
    if (!isset($_FILES[$key]) || $_FILES[$key]['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    $file = $_FILES[$key];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed)) return '';
    $filename = uniqid() . '.' . $ext;
    $dest = dirname(__DIR__) . '/' . $dir . '/' . $filename;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return '';
}

function getUploadUrl(string $filename, string $dir): string
{
    if (!$filename) return '';
    return '/uploads/' . $filename;
}

function csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verifyCsrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch');
    }
}

function flash(string $key, mixed $value = null): mixed
{
    if ($value !== null) {
        $_SESSION['flash'][$key] = $value;
        return $value;
    }
    $val = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $val;
}

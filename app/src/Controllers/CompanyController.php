<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class CompanyController
{
    public function index(): void
    {
        Auth::require();
        $companies = Database::get()->query("SELECT * FROM companies ORDER BY is_default DESC, name ASC")->fetchAll();
        render('company/index', compact('companies'));
    }

    public function save(): void
    {
        Auth::require();
        $db = Database::get();
        $id = (int)($_POST['company_id'] ?? 0);

        // Handle logo upload
        $logoPath  = $_POST['existing_logo']  ?? '';
        $stampPath = $_POST['existing_stamp'] ?? '';

        $uploadDir = BASE_PATH . '/uploads';
        @mkdir($uploadDir, 0775, true);

        if (!empty($_FILES['logo']['name'])) {
            $uploaded = uploadFile('logo', 'uploads');
            if ($uploaded) {
                // Delete old
                if ($logoPath && file_exists($uploadDir . '/' . $logoPath)) unlink($uploadDir . '/' . $logoPath);
                $logoPath = $uploaded;
            }
        }
        if (!empty($_FILES['stamp']['name'])) {
            $uploaded = uploadFile('stamp', 'uploads');
            if ($uploaded) {
                if ($stampPath && file_exists($uploadDir . '/' . $stampPath)) unlink($uploadDir . '/' . $stampPath);
                $stampPath = $uploaded;
            }
        }

        $data = [
            'name'          => trim($_POST['name']           ?? ''),
            'type'          => trim($_POST['type']           ?? ''),
            'address'       => trim($_POST['address']        ?? ''),
            'city'          => trim($_POST['city']           ?? ''),
            'phone'         => trim($_POST['phone']          ?? ''),
            'email'         => trim($_POST['email']          ?? ''),
            'npwp'          => trim($_POST['npwp']           ?? ''),
            'logo_path'     => $logoPath,
            'stamp_path'    => $stampPath,
            'brand_color'   => $_POST['brand_color']         ?? '#1e3a8a',
            'brand_color2'  => $_POST['brand_color2']        ?? '#3b82f6',
            'brand_style'   => $_POST['brand_style']         ?? 'solid',
        ];

        if ($id) {
            $sets = implode(', ', array_map(fn($k) => "$k=?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE companies SET $sets WHERE id=?")->execute($vals);
        } else {
            $data['created_by'] = Auth::id();
            $cols   = implode(', ', array_keys($data));
            $places = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO companies ($cols) VALUES ($places)")->execute(array_values($data));
        }

        flash('success', __t('saved'));
        redirect('/companies');
    }

    public function delete(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::get();
        $stmt = $db->prepare("SELECT logo_path, stamp_path FROM companies WHERE id=?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $uploadDir = BASE_PATH . '/uploads';
            foreach (['logo_path','stamp_path'] as $f) {
                if ($row[$f] && file_exists($uploadDir . '/' . $row[$f])) unlink($uploadDir . '/' . $row[$f]);
            }
        }
        $db->prepare("DELETE FROM companies WHERE id=?")->execute([$id]);
        flash('success', __t('deleted'));
        redirect('/companies');
    }

    public function setDefault(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        $db = Database::get();
        $db->exec("UPDATE companies SET is_default=0");
        $db->prepare("UPDATE companies SET is_default=1 WHERE id=?")->execute([$id]);
        flash('success', __t('saved'));
        redirect('/companies');
    }

    public function get(): void
    {
        Auth::require();
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = Database::get()->prepare("SELECT * FROM companies WHERE id=?");
        $stmt->execute([$id]);
        $company = $stmt->fetch();
        header('Content-Type: application/json');
        echo json_encode($company ?: []);
        exit;
    }
}

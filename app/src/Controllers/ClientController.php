<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class ClientController
{
    public function index(): void
    {
        Auth::require();
        $db     = Database::get();
        $search = $_GET['q'] ?? '';
        $params = [];
        $where  = '1=1';

        if ($search) {
            $where .= ' AND (name LIKE ? OR company LIKE ? OR email LIKE ?)';
            $params = ["%$search%", "%$search%", "%$search%"];
        }

        $stmt = $db->prepare("SELECT * FROM clients WHERE $where ORDER BY name ASC");
        $stmt->execute($params);
        $clients = $stmt->fetchAll();

        render('client/index', compact('clients', 'search'));
    }

    public function save(): void
    {
        Auth::require();
        $db = Database::get();
        $id = (int)($_POST['client_id'] ?? 0);

        $data = [
            'name'    => trim($_POST['name']    ?? ''),
            'company' => trim($_POST['company'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city'    => trim($_POST['city']    ?? ''),
            'phone'   => trim($_POST['phone']   ?? ''),
            'email'   => trim($_POST['email']   ?? ''),
            'npwp'    => trim($_POST['npwp']    ?? ''),
        ];

        if ($id) {
            $sets = implode(', ', array_map(fn($k) => "$k=?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE clients SET $sets WHERE id=?")->execute($vals);
        } else {
            $data['created_by'] = Auth::id();
            $cols   = implode(', ', array_keys($data));
            $places = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO clients ($cols) VALUES ($places)")->execute(array_values($data));
        }

        flash('success', __t('saved'));
        redirect('/clients');
    }

    public function delete(): void
    {
        Auth::require();
        $id = (int)($_GET['id'] ?? 0);
        Database::get()->prepare("DELETE FROM clients WHERE id=?")->execute([$id]);
        flash('success', __t('deleted'));
        redirect('/clients');
    }

    public function get(): void
    {
        Auth::require();
        $id   = (int)($_GET['id'] ?? 0);
        $stmt = Database::get()->prepare("SELECT * FROM clients WHERE id=?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();
        header('Content-Type: application/json');
        echo json_encode($client ?: []);
        exit;
    }

    public function export(): void
    {
        Auth::require();
        $clients = Database::get()->query("SELECT * FROM clients ORDER BY name ASC")->fetchAll();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="clients_' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
        fputcsv($out, ['ID','Name','Company','Address','City','Phone','Email','NPWP','Created At']);
        foreach ($clients as $c) {
            fputcsv($out, [$c['id'],$c['name'],$c['company'],$c['address'],$c['city'],$c['phone'],$c['email'],$c['npwp'],$c['created_at']]);
        }
        fclose($out);
        exit;
    }
}

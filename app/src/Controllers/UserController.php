<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class UserController
{
    public function index(): void
    {
        Auth::requireAdmin();
        $users = Database::get()->query("SELECT id,name,email,role,active,created_at FROM users ORDER BY name ASC")->fetchAll();
        render('user/index', compact('users'));
    }

    public function save(): void
    {
        Auth::requireAdmin();
        $db  = Database::get();
        $id  = (int)($_POST['user_id'] ?? 0);
        $pwd = $_POST['password'] ?? '';

        $data = [
            'name'   => trim($_POST['name']  ?? ''),
            'email'  => trim($_POST['email'] ?? ''),
            'role'   => in_array($_POST['role'] ?? '', ['admin','staff']) ? $_POST['role'] : 'staff',
            'active' => (int)!empty($_POST['active']),
        ];

        if ($id) {
            if ($pwd) $data['password'] = password_hash($pwd, PASSWORD_BCRYPT);
            $sets = implode(', ', array_map(fn($k) => "$k=?", array_keys($data)));
            $vals = array_values($data);
            $vals[] = $id;
            $db->prepare("UPDATE users SET $sets WHERE id=?")->execute($vals);
        } else {
            $data['password'] = password_hash($pwd ?: 'changeme123', PASSWORD_BCRYPT);
            $cols   = implode(', ', array_keys($data));
            $places = implode(', ', array_fill(0, count($data), '?'));
            $db->prepare("INSERT INTO users ($cols) VALUES ($places)")->execute(array_values($data));
        }

        flash('success', __t('saved'));
        redirect('/users');
    }

    public function delete(): void
    {
        Auth::requireAdmin();
        $id = (int)($_GET['id'] ?? 0);
        if ($id === Auth::id()) { flash('error', 'Cannot delete yourself.'); redirect('/users'); }
        Database::get()->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
        flash('success', __t('deleted'));
        redirect('/users');
    }
}

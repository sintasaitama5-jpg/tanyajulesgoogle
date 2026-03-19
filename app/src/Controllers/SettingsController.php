<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class SettingsController
{
    public function index(): void
    {
        Auth::requireAdmin();
        $db      = Database::get();
        $formats = $db->query("SELECT * FROM invoice_formats ORDER BY name ASC")->fetchAll();
        render('settings/index', compact('formats'));
    }

    public function save(): void
    {
        Auth::requireAdmin();
        $db   = Database::get();
        $keys = ['app_name','default_currency','default_lang','default_notes_id','default_notes_en'];
        $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)");
        foreach ($keys as $k) {
            $stmt->execute([$k, trim($_POST[$k] ?? '')]);
        }
        flash('success', __t('saved'));
        redirect('/settings');
    }
}

<?php
namespace App\Controllers;

use App\Auth;
use App\Database;

class DashboardController
{
    public function index(): void
    {
        Auth::require();
        $db = Database::get();

        $stats = [
            'total'     => $db->query("SELECT COUNT(*) FROM invoices")->fetchColumn(),
            'paid'      => $db->query("SELECT COUNT(*) FROM invoices WHERE status='paid'")->fetchColumn(),
            'unpaid'    => $db->query("SELECT COUNT(*) FROM invoices WHERE status='unpaid'")->fetchColumn(),
            'cancelled' => $db->query("SELECT COUNT(*) FROM invoices WHERE status='cancelled'")->fetchColumn(),
            'revenue'   => $db->query("SELECT COALESCE(SUM(total),0) FROM invoices WHERE status='paid'")->fetchColumn(),
        ];

        $recent = $db->query("
            SELECT i.*, c.name as client_name
            FROM invoices i
            LEFT JOIN clients c ON i.client_id = c.id
            ORDER BY i.created_at DESC
            LIMIT 8
        ")->fetchAll();

        render('dashboard/index', compact('stats', 'recent'));
    }
}

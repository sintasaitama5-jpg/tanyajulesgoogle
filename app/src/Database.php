<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $pdo = null;

    public static function get(): PDO
    {
        if (self::$pdo === null) {
            $dbPath = dirname(__DIR__) . '/data/invoice.db';
            self::$pdo = new PDO('sqlite:' . $dbPath);
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            self::$pdo->exec('PRAGMA journal_mode=WAL;');
            self::migrate();
        }
        return self::$pdo;
    }

    private static function migrate(): void
    {
        $db = self::$pdo;
        $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'staff',
            active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS companies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            type TEXT DEFAULT '',
            address TEXT DEFAULT '',
            city TEXT DEFAULT '',
            phone TEXT DEFAULT '',
            email TEXT DEFAULT '',
            npwp TEXT DEFAULT '',
            logo_path TEXT DEFAULT '',
            stamp_path TEXT DEFAULT '',
            brand_color TEXT DEFAULT '#1e3a8a',
            brand_color2 TEXT DEFAULT '#3b82f6',
            brand_style TEXT DEFAULT 'solid',
            is_default INTEGER DEFAULT 0,
            created_by INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS clients (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            company TEXT DEFAULT '',
            address TEXT DEFAULT '',
            city TEXT DEFAULT '',
            phone TEXT DEFAULT '',
            email TEXT DEFAULT '',
            npwp TEXT DEFAULT '',
            created_by INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS invoice_formats (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            format TEXT NOT NULL,
            last_seq INTEGER DEFAULT 0,
            invoice_type TEXT DEFAULT 'all',
            padding INTEGER DEFAULT 4,
            reset_yearly INTEGER DEFAULT 1,
            last_year INTEGER DEFAULT 0,
            created_by INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS invoices (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            invoice_number TEXT UNIQUE NOT NULL,
            type TEXT NOT NULL,
            status TEXT DEFAULT 'unpaid',
            format_id INTEGER DEFAULT 0,
            company_id INTEGER DEFAULT 0,
            client_id INTEGER DEFAULT 0,
            company_data TEXT DEFAULT '{}',
            client_data TEXT DEFAULT '{}',
            issue_date TEXT DEFAULT '',
            due_date TEXT DEFAULT '',
            template TEXT DEFAULT 'minimal',
            orientation TEXT DEFAULT 'portrait',
            items TEXT DEFAULT '[]',
            subtotal REAL DEFAULT 0,
            discount REAL DEFAULT 0,
            discount_type TEXT DEFAULT 'percent',
            ppn_enabled INTEGER DEFAULT 0,
            ppn_rate REAL DEFAULT 11,
            pph23_enabled INTEGER DEFAULT 0,
            pph23_rate REAL DEFAULT 2,
            materai_enabled INTEGER DEFAULT 0,
            materai_amount REAL DEFAULT 10000,
            total REAL DEFAULT 0,
            paid_amount REAL DEFAULT 0,
            currency TEXT DEFAULT 'IDR',
            notes TEXT DEFAULT '',
            entity_type TEXT DEFAULT 'badan',
            pph21_enabled INTEGER DEFAULT 0,
            pph21_rate REAL DEFAULT 5,
            show_invoice_number INTEGER DEFAULT 1,
            show_watermark INTEGER DEFAULT 1,
            show_ttd INTEGER DEFAULT 0,
            show_stamp INTEGER DEFAULT 0,
            ttd_name TEXT DEFAULT '',
            ttd_position TEXT DEFAULT '',
            ttd_name2 TEXT DEFAULT '',
            ttd_position2 TEXT DEFAULT '',
            pdf_path TEXT DEFAULT '',
            lang TEXT DEFAULT 'id',
            created_by INTEGER DEFAULT 0,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT DEFAULT ''
        );
        ");

        // Seed default admin if no users exist
        $count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($count == 0) {
            $hash = password_hash('admin123', PASSWORD_BCRYPT);
            $db->exec("INSERT INTO users (name, email, password, role) VALUES ('Administrator', 'admin@invoice.app', '$hash', 'admin')");
        }

        // Seed default settings
        $defaults = [
            'app_name' => 'InvoiceApp',
            'default_currency' => 'IDR',
            'default_lang' => 'id',
            'default_notes_id' => 'Pembayaran dalam 14 hari. Nomor NPWP diperlukan. Harga belum termasuk PPN 11%.',
            'default_notes_en' => 'Payment due within 14 days. NPWP number required. Prices exclude 11% VAT.',
        ];
        $stmt = $db->prepare("INSERT OR IGNORE INTO settings (key, value) VALUES (?, ?)");
        foreach ($defaults as $k => $v) {
            $stmt->execute([$k, $v]);
        }

        // Migration: add columns to existing DB
        $cols = array_column($db->query("PRAGMA table_info(invoices)")->fetchAll(), 'name');
        if (!in_array('show_invoice_number', $cols)) {
            $db->exec("ALTER TABLE invoices ADD COLUMN show_invoice_number INTEGER DEFAULT 1");
        }
        if (!in_array('entity_type', $cols)) {
            $db->exec("ALTER TABLE invoices ADD COLUMN entity_type TEXT DEFAULT 'badan'");
        }
        if (!in_array('pph21_enabled', $cols)) {
            $db->exec("ALTER TABLE invoices ADD COLUMN pph21_enabled INTEGER DEFAULT 0");
        }
        if (!in_array('pph21_rate', $cols)) {
            $db->exec("ALTER TABLE invoices ADD COLUMN pph21_rate REAL DEFAULT 5");
        }
    }
}

# Invoice App

Aplikasi pembuatan invoice profesional berbasis PHP + SQLite + Docker.

## Fitur

- Invoice Barang, Jasa, Penginapan
- Multi-user dengan role Admin / Staff
- Bilingual Indonesia / English
- Nomor invoice custom & auto-generate (anti-dobel)
- Pajak opsional: PPN 11%, PPh 23, Materai Rp10.000
- Tanda tangan + stempel opsional
- Address book klien & profil perusahaan
- Branding kustom: logo, warna solid/gradasi
- PDF portrait & landscape, 2 template (minimal & branded)
- Watermark LUNAS / BELUM LUNAS otomatis
- Terbilang otomatis (Bahasa Indonesia)
- Multi-currency: IDR, USD, SGD, EUR
- History semua invoice, bisa diedit
- Semua data tersimpan di folder terpisah untuk backup mudah

## Struktur Volume (Backup)

```
invoice-app/
├── data/       ← SQLite database (backup ini)
├── uploads/    ← Logo & stempel perusahaan (backup ini)
└── pdfs/       ← PDF yang sudah di-generate (backup ini)
```

Untuk backup, cukup zip ketiga folder di atas. Tidak perlu backup folder `app/`.

## Quick Start

```bash
# 1. Clone / extract project
cd invoice-app

# 2. Jalankan
docker compose up -d --build

# 3. Buka browser
open http://localhost:8080

# Default login:
# Email    : admin@invoice.app
# Password : admin123
```

**Ganti password segera setelah login pertama!** (Settings → Users)

## Port & Custom Domain

Untuk ganti port, edit `docker-compose.yml`:
```yaml
ports:
  - "80:80"   # ganti 8080 ke port yang diinginkan
```

## Format Nomor Invoice

Masuk ke **Settings → Invoice Format**, buat format baru.

| Token    | Output contoh |
|----------|---------------|
| `{YEAR}` | 2025          |
| `{MONTH}`| 03            |
| `{MON}`  | III           |
| `{TYPE}` | JSA / BRG / PNP |
| `{SEQ}`  | 0001          |

Contoh format: `INV/{TYPE}/{YEAR}/{MON}/{SEQ}` → `INV/JSA/2025/III/0001`

## Backup Otomatis (Opsional)

Tambahkan cron job untuk sync ke cloud:

```bash
# Backup harian ke Google Drive via rclone
0 2 * * * rclone sync /path/to/invoice-app/data gdrive:backup/invoice/data
0 2 * * * rclone sync /path/to/invoice-app/uploads gdrive:backup/invoice/uploads
0 2 * * * rclone sync /path/to/invoice-app/pdfs gdrive:backup/invoice/pdfs
```

## Update Aplikasi

```bash
docker compose down
docker compose up -d --build
```

Data di `data/`, `uploads/`, `pdfs/` tidak terpengaruh karena di-mount sebagai volume.

## Troubleshooting

**PDF tidak bisa di-generate:**
```bash
docker exec invoice_app chown -R www-data:www-data /var/www/html/data /var/www/html/pdfs
```

**Upload logo gagal:**
```bash
docker exec invoice_app chown -R www-data:www-data /var/www/html/uploads
```

**Reset database:**
```bash
rm data/invoice.db
docker compose restart
```

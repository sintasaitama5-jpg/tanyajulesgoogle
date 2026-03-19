<?php
return [
    // Navigation
    'dashboard'         => 'Dashboard',
    'invoices'          => 'Invoice',
    'clients'           => 'Klien',
    'companies'         => 'Perusahaan',
    'users'             => 'Pengguna',
    'settings'          => 'Pengaturan',
    'logout'            => 'Keluar',

    // Auth
    'login'             => 'Masuk',
    'login_title'       => 'Masuk ke Akun Anda',
    'email'             => 'Email',
    'password'          => 'Kata Sandi',
    'login_failed'      => 'Email atau password salah.',

    // Dashboard
    'total_invoices'    => 'Total Invoice',
    'paid'              => 'Lunas',
    'unpaid'            => 'Belum Lunas',
    'cancelled'         => 'Dibatalkan',
    'recent_invoices'   => 'Invoice Terbaru',
    'total_revenue'     => 'Total Pendapatan',

    // Invoice
    'create_invoice'    => 'Buat Invoice',
    'edit_invoice'      => 'Edit Invoice',
    'invoice_number'    => 'Nomor Invoice',
    'invoice_type'      => 'Jenis Invoice',
    'invoice_date'      => 'Tanggal Invoice',
    'due_date'          => 'Jatuh Tempo',
    'type_barang'            => 'Invoice Barang',
    'type_jasa'              => 'Invoice Jasa',
    'type_barang_jasa'       => 'Invoice Barang & Jasa',
    'type_jasa_maintenance'  => 'Invoice Jasa Maintenance',
    'type_jasa_service'      => 'Invoice Jasa Service',
    'type_maintenance'  => 'Invoice Jasa Maintenance',
    'type_service'      => 'Invoice Jasa Service',
    'type_penginapan'   => 'Invoice Penginapan',
    'item_kind_barang'  => 'Barang',
    'item_kind_jasa'    => 'Jasa (kena PPh 23)',
    'status'            => 'Status',
    'status_unpaid'     => 'Belum Lunas',
    'status_paid'       => 'Lunas',
    'status_cancelled'  => 'Dibatalkan',
    'template'          => 'Template',
    'template_minimal'  => 'Minimalis',
    'template_branded'  => 'Bermerek',
    'orientation'       => 'Orientasi',
    'portrait'          => 'Potrait',
    'landscape'         => 'Lanskap',

    // Items
    'description'       => 'Deskripsi',
    'qty'               => 'Qty',
    'unit'              => 'Satuan',
    'unit_price'        => 'Harga Satuan',
    'amount'            => 'Jumlah',
    'add_item'          => 'Tambah Item',
    'subtotal'          => 'Subtotal',
    'discount'          => 'Diskon',
    'total'             => 'Total',
    'total_due'         => 'Total Tagihan',
    'paid_amount'       => 'Sudah Dibayar',
    'balance_due'       => 'Sisa Tagihan',
    'terbilang'         => 'Terbilang',

    // Tax
    'ppn'               => 'PPN',
    'pph23'             => 'PPh 23 (Badan)',
    'pph21'             => 'PPh 21 (Orang Pribadi)',
    'entity_type'       => 'Jenis Pemotong Pajak',
    'entity_badan'      => 'Badan Usaha (CV/PT) — PPh 23',
    'entity_pribadi'    => 'Orang Pribadi — PPh 21',
    'pph_note_badan'    => 'PPh 23: 2% × bruto jasa (4% tanpa NPWP)',
    'pph_note_pribadi'  => 'PPh 21: tarif progresif × 50% bruto (Ps.17)',
    'materai'           => 'Materai',

    // Parties
    'bill_from'         => 'Dari',
    'bill_to'           => 'Kepada',
    'company_name'      => 'Nama Perusahaan',
    'company_type'      => 'Jenis Usaha',
    'address'           => 'Alamat',
    'city'              => 'Kota',
    'phone'             => 'Telepon',
    'email_addr'        => 'Email',
    'npwp'              => 'NPWP',
    'client_name'       => 'Nama Klien',
    'contact'           => 'Kontak',

    // Signature
    'show_ttd'          => 'Tampilkan Tanda Tangan',
    'show_stamp'        => 'Tampilkan Stempel',
    'ttd_name'          => 'Nama Penanda Tangan',
    'ttd_position'      => 'Jabatan',

    // Notes
    'notes'             => 'Catatan',
    'notes_footer'      => 'Catatan Bawah',

    // Format
    'invoice_format'    => 'Format Nomor Invoice',
    'format_name'       => 'Nama Format',
    'format_pattern'    => 'Pola Format',
    'format_hint'       => 'Gunakan: {YEAR} {MONTH} {MON} {TYPE} {SEQ}',
    'padding'           => 'Padding Angka',
    'reset_yearly'      => 'Reset Urutan Tiap Tahun',
    'add_format'        => 'Tambah Format',
    'use_format'        => 'Gunakan Format',

    // Client
    'add_client'        => 'Tambah Klien',
    'edit_client'       => 'Edit Klien',
    'load_client'       => 'Muat Data Klien',
    'export_clients'    => 'Ekspor Klien',
    'save_client'       => 'Simpan Klien',

    // Company
    'add_company'       => 'Tambah Profil Perusahaan',
    'edit_company'      => 'Edit Perusahaan',
    'load_company'      => 'Muat Data Perusahaan',
    'set_default'       => 'Jadikan Default',
    'logo'              => 'Logo',
    'stamp'             => 'Stempel',
    'brand_color'       => 'Warna Brand',
    'brand_style'       => 'Gaya Warna',
    'solid'             => 'Solid',
    'gradient'          => 'Gradasi',

    // Branding
    'brand_color1'      => 'Warna Utama',
    'brand_color2'      => 'Warna Kedua (Gradasi)',

    // Actions
    'save'              => 'Simpan',
    'cancel'            => 'Batal',
    'delete'            => 'Hapus',
    'edit'              => 'Edit',
    'view'              => 'Lihat',
    'download_pdf'      => 'Unduh PDF',
    'preview'           => 'Pratinjau',
    'search'            => 'Cari',
    'filter'            => 'Filter',
    'all'               => 'Semua',
    'confirm_delete'    => 'Yakin ingin menghapus?',
    'new'               => 'Baru',

    // Users
    'add_user'          => 'Tambah Pengguna',
    'edit_user'         => 'Edit Pengguna',
    'role'              => 'Peran',
    'role_admin'        => 'Admin',
    'role_staff'        => 'Staff',
    'active'            => 'Aktif',

    // Settings
    'app_name'          => 'Nama Aplikasi',
    'default_lang'      => 'Bahasa Default',
    'default_currency'  => 'Mata Uang Default',
    'default_notes'     => 'Catatan Default',

    // Messages
    'saved'             => 'Berhasil disimpan.',
    'deleted'           => 'Berhasil dihapus.',
    'error'             => 'Terjadi kesalahan.',
    'unauthorized'      => 'Akses ditolak.',
    'not_found'         => 'Data tidak ditemukan.',

    // Currency
    'currency'          => 'Mata Uang',

    // PDF
    'invoice'           => 'INVOICE',
    'page'              => 'Halaman',
    'generated'         => 'Dibuat pada',
    'watermark_paid'    => 'LUNAS',
    'watermark_unpaid'  => 'BELUM LUNAS',
];

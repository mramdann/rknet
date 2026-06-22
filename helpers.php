<?php
// helpers.php — fungsi bantu yang dipakai bersama portal pelanggan & admin.

if (!function_exists('formatRupiah')) {
    /**
     * Ubah angka menjadi format rupiah, contoh: 100000 -> "Rp100.000".
     */
    function formatRupiah(int $angka): string
    {
        return 'Rp' . number_format($angka, 0, ',', '.');
    }
}

if (!function_exists('badgeStatus')) {
    /**
     * Kembalikan kelas warna & label badge berdasarkan status.
     */
    function badgeStatus(string $status): array
    {
        return match ($status) {
            'lunas'      => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Telah Dibayar'],
            'menunggu'   => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Menunggu Pembayaran'],
            'aktif'      => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Aktif'],
            'nonaktif'   => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Nonaktif'],
            // Status lead
            'baru'       => ['kelas' => 'bg-info-subtle text-info',           'label' => 'Baru'],
            'dihubungi'  => ['kelas' => 'bg-primary-subtle text-primary',     'label' => 'Dihubungi'],
            'terjadwal'  => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Terjadwal'],
            'selesai'    => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Selesai'],
            'batal'      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Batal'],
            // Status area
            'tercakup'   => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Tercakup'],
            'segera'     => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Segera'],
            // Status notifikasi
            'terkirim'   => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Terkirim'],
            'draft'      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Draft'],
            default      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => ucfirst($status)],
        };
    }
}

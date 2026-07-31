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
            'verifikasi' => ['kelas' => 'bg-info-subtle text-info',           'label' => 'Menunggu Verifikasi'],
            'ditolak'    => ['kelas' => 'bg-danger-subtle text-danger',       'label' => 'Bukti Ditolak'],
            'pending'    => ['kelas' => 'bg-warning-subtle text-warning',     'label' => 'Menunggu Persetujuan'],
            'aktif'      => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Aktif'],
            'nonaktif'   => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Nonaktif'],
            // Status notifikasi
            'terkirim'   => ['kelas' => 'bg-success-subtle text-success',     'label' => 'Terkirim'],
            'draft'      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => 'Draft'],
            default      => ['kelas' => 'bg-secondary-subtle text-secondary', 'label' => ucfirst($status)],
        };
    }
}

if (!function_exists('tanggalIndonesia')) {
    /**
     * Format tanggal menjadi "d MMM yyyy" Bahasa Indonesia (kosong = hari ini).
     */
    function tanggalIndonesia(string $tanggal = ''): string
    {
        $bulan = [1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
                  7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'];
        $ts = $tanggal === '' ? time() : strtotime($tanggal);
        if ($ts === false) return $tanggal;
        return date('d', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
    }
}

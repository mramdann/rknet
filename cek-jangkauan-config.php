<?php
// cek-jangkauan-config.php — data area yang sudah terjangkau jaringan Starlite (dummy).

// Area terjangkau dikelompokkan per provinsi
$areaTerjangkau = [
    'BANTEN' => ['Kota Serang', 'Kota Tangerang', 'Kota Tangerang Selatan', 'Kabupaten Tangerang'],
    'JAWA BARAT' => ['Kota Bogor', 'Kota Depok', 'Kota Cirebon', 'Kabupaten Cirebon', 'Kabupaten Cianjur', 'Kabupaten Purwakarta', 'Kota Tasikmalaya'],
    'JAWA TENGAH' => ['Kota Semarang', 'Kota Pekalongan', 'Kabupaten Pekalongan', 'Kota Tegal', 'Kabupaten Brebes', 'Kabupaten Demak', 'Kabupaten Pemalang'],
    'JAWA TIMUR' => ['Kota Malang', 'Kabupaten Gresik'],
];

/**
 * Kembalikan daftar kata kunci kota terjangkau (huruf kecil) untuk pencocokan
 * sederhana terhadap hasil geocoding. Prefix "Kota"/"Kabupaten" dibuang.
 */
function daftarKeywordTerjangkau(array $areaTerjangkau): array
{
    $keyword = [];
    foreach ($areaTerjangkau as $daftarKota) {
        foreach ($daftarKota as $kota) {
            $bersih = trim(str_ireplace(['Kabupaten', 'Kota'], '', $kota));
            $keyword[] = mb_strtolower($bersih);
        }
    }
    return array_values(array_unique($keyword));
}

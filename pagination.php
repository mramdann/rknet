<?php
// pagination.php — helper paginasi sisi-server (LIMIT/OFFSET + nav).
require_once __DIR__ . '/db.php';   // kueri(), kueriNilai()

const PER_HALAMAN = 5;

function halamanSaatIni(): int
{
    $hal = (int) ($_GET['hal'] ?? 1);
    return $hal < 1 ? 1 : $hal;
}

function ambilPaginasi(string $sqlBase, string $sqlCount, array $params, int $perHalaman = PER_HALAMAN): array
{
    $total = (int) kueriNilai($sqlCount, $params);
    $totalHal = max(1, (int) ceil($total / $perHalaman));
    $hal = min(halamanSaatIni(), $totalHal);
    $offset = ($hal - 1) * $perHalaman;
    $baris = kueri($sqlBase . " LIMIT $perHalaman OFFSET $offset", $params);
    return ['baris' => $baris, 'hal' => $hal, 'totalHal' => $totalHal, 'total' => $total];
}

function tampilPaginasi(int $hal, int $totalHal, array $queryTambahan = []): void
{
    if ($totalHal <= 1) {
        return;
    }
    $tautan = function (int $h) use ($queryTambahan) {
        return '?' . http_build_query(array_merge($queryTambahan, ['hal' => $h]));
    };
    echo '<nav class="mt-3"><ul class="pagination pagination-sm justify-content-center mb-0">';
    echo '<li class="page-item' . ($hal <= 1 ? ' disabled' : '') . '">'
       . '<a class="page-link" href="' . htmlspecialchars($tautan(max(1, $hal - 1))) . '">&laquo;</a></li>';
    for ($i = 1; $i <= $totalHal; $i++) {
        echo '<li class="page-item' . ($i === $hal ? ' active' : '') . '">'
           . '<a class="page-link" href="' . htmlspecialchars($tautan($i)) . '">' . $i . '</a></li>';
    }
    echo '<li class="page-item' . ($hal >= $totalHal ? ' disabled' : '') . '">'
       . '<a class="page-link" href="' . htmlspecialchars($tautan(min($totalHal, $hal + 1))) . '">&raquo;</a></li>';
    echo '</ul></nav>';
}

<?php
// logout.php — hapus sesi pelanggan lalu kembali ke login.
require __DIR__ . '/../auth.php';
logoutPelanggan();
header('Location: login.php');
exit;

<?php
// logout.php (admin) — hapus sesi admin lalu kembali ke login.
require __DIR__ . '/../auth.php';
logoutAdmin();
header('Location: login.php');
exit;

<?php
session_start();

// Tentukan mau dilempar ke halaman login admin atau siswa,
// berdasarkan peran yang sedang login SEBELUM sesinya dihapus
$tujuan = (($_SESSION['data']['role'] ?? '') === 'siswa')
    ? '../view/login_siswa.php'
    : '../view/login_admin.php';

$_SESSION = [];
session_destroy();

header("Location: " . $tujuan);
exit;
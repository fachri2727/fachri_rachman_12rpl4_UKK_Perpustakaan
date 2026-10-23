<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Loloskan hanya request POST; selain itu kembalikan ke halaman tujuan
function wajib_post($tujuan = null)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: " . ($tujuan ?? 'daftar_siswa.php'));
        exit;
    }
}

// Loloskan hanya admin yang sudah login; selain itu arahkan ke login admin
function wajib_admin()
{
    if (empty($_SESSION['data']) || ($_SESSION['data']['role'] ?? '') !== 'admin') {
        header("Location: ../view/login_admin.php");
        exit;
    }
}

// Loloskan hanya siswa yang sudah login; selain itu arahkan ke login siswa
function wajib_siswa()
{
    if (empty($_SESSION['data']) || ($_SESSION['data']['role'] ?? '') !== 'siswa') {
        header("Location: ../view/login_siswa.php");
        exit;
    }
}

// Buat/ambil token CSRF untuk sesi yang sedang berjalan
function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Cetak input tersembunyi berisi token CSRF, dipakai di dalam <form>
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

// Hentikan proses jika token CSRF pada form tidak cocok dengan token sesi
function wajib_csrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $token)) {
        pesan('Sesi tidak valid, silakan ulangi'); // kembali ke halaman sebelumnya
    }
}

// Tampilkan pesan (alert) lalu arahkan ke halaman tujuan
function pesan($teks, $tujuan = null)
{
    if ($tujuan === null) {
        $tujuan = $_SERVER['HTTP_REFERER'] ?? 'daftar_siswa.php';
    }
    echo "<script>
            alert(" . json_encode($teks) . ");
            window.location=" . json_encode($tujuan) . ";
          </script>";
    exit;
}

// Escape teks agar aman ditampilkan di HTML (cegah XSS)
function e($teks)
{
    return htmlspecialchars((string) $teks, ENT_QUOTES, 'UTF-8');
}
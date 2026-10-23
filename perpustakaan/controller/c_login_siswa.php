<?php
 
session_start();
 
include_once __DIR__ . '/../model/m_siswa.php';
 
$halaman_login = '../view/login_siswa.php';
 
// Kembali ke halaman login dengan pesan alert
function kembali($pesan, $tujuan)
{
    echo "<script>
            alert(" . json_encode($pesan) . ");
            window.location=" . json_encode($tujuan) . ";
          </script>";
    exit;
}
 
// Hanya proses jika form dikirim lewat POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . $halaman_login);
    exit;
}
 
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? ''; // jangan di-trim, password harus sama persis
 
if ($username === '' || $password === '') {
    kembali('Username dan password wajib diisi', $halaman_login);
}
 
try {
    $model = new m_siswa();
    $data  = $model->login($username);
} catch (Exception $e) {
    error_log($e->getMessage());
    kembali('Tidak bisa terhubung ke database. Pastikan database sudah diimpor.', $halaman_login);
}
 
// Username tidak ditemukan / akun nonaktif
if ($data === null) {
    kembali('Username tidak ditemukan atau akun nonaktif', $halaman_login);
}
 
// Cek password (mendukung dua bentuk: hash ATAU teks biasa, TANPA mengubah nilai di database)
$password_tersimpan = $data['password'];
$info_hash          = password_get_info($password_tersimpan);
$password_cocok     = false;
 
if (!empty($info_hash['algo'])) {
    // Password tersimpan sebagai hash -> cocokkan dengan password_verify
    $password_cocok = password_verify($password, $password_tersimpan);
} else {
    // Password tersimpan sebagai teks biasa -> cocokkan langsung (tidak diubah/disimpan ulang)
    $password_cocok = hash_equals($password_tersimpan, $password);
}
 
if (!$password_cocok) {
    kembali('Password salah', $halaman_login);
}
 
// ===== LOGIN BERHASIL =====
session_regenerate_id(true);
unset($data['password']);          // jangan simpan password di session
$data['role']     = 'siswa';
$_SESSION['data'] = $data;
 
header("Location: ../view/dashboard_siswa.php");
exit;
<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_siswa.php';
include_once __DIR__ . '/../model/m_peminjaman.php';

// Halaman ini khusus siswa yang sudah login
wajib_siswa();

$siswa          = null;
$pengajuan_aktif = [];
$total_denda     = 0;

try {
    $m_siswa  = new m_siswa();
    $id_siswa = (int) ($_SESSION['data']['id_siswa'] ?? 0);

    // Ambil data terbaru dari database (bukan hanya dari session),
    // supaya kalau admin mengubah data siswa, dashboard tetap akurat
    $siswa = $m_siswa->ambil_satu($id_siswa);

    $m_peminjaman    = new m_peminjaman();
    $pengajuan_aktif = $m_peminjaman->aktif_siswa($id_siswa);

    // Total denda berjalan dari semua buku yang sedang dipinjam
    foreach ($pengajuan_aktif as $pj) {
        $total_denda += (int) ($pj->denda ?? 0);
    }

    if ($siswa === null) {
        // Akun kemungkinan sudah dihapus/dinonaktifkan admin setelah login
        $_SESSION = [];
        session_destroy();
        header("Location: ../view/login_siswa.php");
        exit;
    }
 
} catch (Exception $e) {
    error_log($e->getMessage());
    echo 'Data tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi benar.<br>';
    echo 'Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); // tampil sementara untuk debug
    exit;
}
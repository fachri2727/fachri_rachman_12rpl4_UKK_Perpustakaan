<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_peminjaman.php';

wajib_admin(); // konfirmasi peminjaman hanya untuk admin

// Setelah aksi apa pun, admin dikembalikan ke dashboard
$kembali = '../view/dashboard_admin.php';

$m_peminjaman            = null;
$daftar_menunggu         = [];
$daftar_menunggu_kembali = [];
$daftar_dipinjam         = [];
$daftar_riwayat          = [];

try {
    $m_peminjaman = new m_peminjaman();

    if (!empty($_GET['aksi'])) {

        $aksi = $_GET['aksi'];

        // Semua aksi admin: wajib POST + token CSRF, id peminjaman dari form
        wajib_post($kembali);
        wajib_csrf();
        $id_peminjaman = (int) ($_POST['id_peminjaman'] ?? 0);

        // SETUJUI PENGAJUAN PEMINJAMAN
        if ($aksi === 'setuju') {
            $hasil = $m_peminjaman->setujui($id_peminjaman);
        }
        // TOLAK PENGAJUAN PEMINJAMAN (buku jadi tersedia lagi)
        elseif ($aksi === 'tolak') {
            $hasil = $m_peminjaman->tolak($id_peminjaman);
        }
        // KONFIRMASI PENGEMBALIAN (hitung denda, buku jadi tersedia lagi)
        elseif ($aksi === 'konfirmasi_kembali') {
            $hasil = $m_peminjaman->konfirmasi_kembali($id_peminjaman);
        }
        // TANDAI DIKEMBALIKAN LANGSUNG (tanpa menunggu siswa mengajukan; denda sampai hari ini)
        elseif ($aksi === 'kembalikan_langsung') {
            $hasil = $m_peminjaman->kembalikan_langsung($id_peminjaman);
        }
        // TOLAK PENGAJUAN PENGEMBALIAN (buku dianggap masih dipinjam)
        elseif ($aksi === 'tolak_kembali') {
            $hasil = $m_peminjaman->tolak_kembali($id_peminjaman);
        }
        else {
            pesan('Aksi tidak dikenal', $kembali);
        }

        pesan($hasil['pesan'], $kembali);

    } else {
        // Tanpa aksi: siapkan daftar untuk view/dashboard_admin.php
        $daftar_menunggu         = $m_peminjaman->daftar_menunggu();
        $daftar_menunggu_kembali = $m_peminjaman->daftar_menunggu_kembali();
        $daftar_dipinjam         = $m_peminjaman->daftar_dipinjam();
        // Dashboard menampilkan riwayat terbaru; ?riwayat=semua memuat lebih banyak
        $batas_riwayat_db        = (($_GET['riwayat'] ?? '') === 'semua') ? 200 : 30;
        $daftar_riwayat          = $m_peminjaman->daftar_riwayat($batas_riwayat_db);
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    if (!empty($_GET['aksi'])) {
        // Pesan error asli ditampilkan sementara untuk debug (hapus bagian ini kalau sudah dipublikasikan)
        pesan('Terjadi kesalahan pada server: ' . $e->getMessage(), $kembali);
    }
    echo 'Data tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi benar.<br>';
    echo 'Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); // tampil sementara untuk debug
    exit;
}
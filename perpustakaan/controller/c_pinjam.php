<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_buku.php';
include_once __DIR__ . '/../model/m_peminjaman.php';

wajib_siswa(); // seluruh transaksi pinjam buku hanya untuk siswa yang sudah login

$id_siswa = (int) ($_SESSION['data']['id_siswa'] ?? 0);
$kembali  = '../view/peminjaman_buku.php';

$m_buku         = null;
$m_peminjaman   = null;
$daftar_buku    = [];
$pengajuan_saya = [];
$buku_saya      = [];
$riwayat_saya   = [];
$total_denda    = 0;

try {
    $m_buku       = new m_buku();
    $m_peminjaman = new m_peminjaman();

    if (!empty($_GET['aksi'])) {

        $aksi = $_GET['aksi'];

        // =====================
        // AJUKAN PEMINJAMAN
        // =====================
        if ($aksi === 'ajukan') {
            wajib_post($kembali);
            wajib_csrf();

            $id_buku = (int) ($_POST['id_buku'] ?? 0);
            if ($id_buku <= 0) {
                pesan('Pilih buku yang akan dipinjam', $kembali);
            }

            // Semua pengecekan + penyimpanan dilakukan di model dalam satu transaksi
            $hasil = $m_peminjaman->ajukan($id_siswa, $id_buku);
            pesan($hasil['pesan'], $kembali);
        }

        // =====================
        // AJUKAN PENGEMBALIAN
        // =====================
        elseif ($aksi === 'kembali') {
            wajib_post($kembali);
            wajib_csrf();

            $id_peminjaman = (int) ($_POST['id_peminjaman'] ?? 0);
            $hasil = $m_peminjaman->ajukan_kembali($id_peminjaman, $id_siswa);
            pesan($hasil['pesan'], $kembali);
        }

        else {
            pesan('Aksi tidak dikenal', $kembali);
        }
    }

    // Tanpa aksi: siapkan katalog buku + daftar pengajuan aktif milik siswa ini
    $daftar_buku    = $m_buku->tampil_data();
    $pengajuan_saya = $m_peminjaman->aktif_siswa($id_siswa);

    // id buku yang sedang dipinjam/diajukan siswa ini (untuk menonaktifkan tombol pinjam ganda)
    foreach ($pengajuan_saya as $pj) {
        $buku_saya[] = (int) $pj->id_buku;
    }
    $riwayat_saya   = $m_peminjaman->riwayat_siswa($id_siswa, 10);

    // Total denda berjalan dari semua buku yang sedang dipinjam
    foreach ($pengajuan_saya as $pj) {
        $total_denda += (int) ($pj->denda ?? 0);
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
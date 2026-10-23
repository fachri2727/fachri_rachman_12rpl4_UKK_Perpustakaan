<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_buku.php';
 
$m_buku      = null;
$daftar_buku = [];
$cari         = '';
$buku_edit   = null;
 
function ambil_input_buku()
{
    return [
        'judul_buku'   => trim($_POST['judul_buku'] ?? ''),
        'pengarang'    => trim($_POST['pengarang'] ?? ''),
        'penerbit'     => trim($_POST['penerbit'] ?? ''),
        'tahun_terbit' => trim($_POST['tahun_terbit'] ?? ''),
        'stok'         => trim($_POST['stok'] ?? ''),
    ];
}
 
// Kembalikan teks error, atau null jika data valid
function cek_input_buku($in)
{
    if ($in['judul_buku'] === '' || $in['pengarang'] === '' || $in['penerbit'] === '' || $in['tahun_terbit'] === '' || $in['stok'] === '') {
        return 'Judul, pengarang, penerbit, tahun terbit, dan stok wajib diisi';
    }
    if (strlen($in['judul_buku']) > 150 || strlen($in['pengarang']) > 100 || strlen($in['penerbit']) > 100) {
        return 'Judul/pengarang/penerbit terlalu panjang';
    }
    if (!ctype_digit($in['tahun_terbit']) || (int) $in['tahun_terbit'] < 1900 || (int) $in['tahun_terbit'] > (int) date('Y') + 1) {
        return 'Tahun terbit tidak valid';
    }
    if (!ctype_digit($in['stok']) || (int) $in['stok'] < 1 || (int) $in['stok'] > 999) {
        return 'Stok harus berupa angka 1 sampai 999';
    }
    return null;
}
 
try {
 
    $m_buku = new m_buku();
 
    if (!empty($_GET['aksi'])) {
 
        $aksi = $_GET['aksi'];
 
        // =====================
        // TAMBAH DATA (admin)
        // =====================
        if ($aksi === 'tambah') {
            wajib_admin();
            wajib_post();
            wajib_csrf();
 
            $in    = ambil_input_buku();
            $error = cek_input_buku($in);
            if ($error) {
                pesan($error);
            }
 
            $ok = $m_buku->tambah_data(
                $in['judul_buku'], $in['pengarang'], $in['penerbit'],
                (int) $in['tahun_terbit'], (int) $in['stok']
            );
 
            if ($ok) {
                pesan('Data berhasil ditambahkan', '../view/daftar_buku.php');
            }
            pesan('Data gagal ditambahkan');
        }
 
        // =====================
        // EDIT DATA (admin)
        // =====================
        elseif ($aksi === 'edit') {
            wajib_admin();
            wajib_post();
            wajib_csrf();
 
            $id_buku = (int) ($_POST['id_buku'] ?? 0);
            $in      = ambil_input_buku();
 
            if ($m_buku->ambil_satu($id_buku) === null) {
                pesan('Data tidak ditemukan', '../view/daftar_buku.php');
            }
 
            $error = cek_input_buku($in);
            if ($error) {
                pesan($error);
            }
 
            $hasil = $m_buku->update_data(
                $id_buku, $in['judul_buku'], $in['pengarang'], $in['penerbit'],
                (int) $in['tahun_terbit'], (int) $in['stok']
            );
 
            // berhasil: ke daftar buku; gagal: kembali ke form (isian tetap)
            pesan($hasil['pesan'], $hasil['ok'] ? '../view/daftar_buku.php' : null);
        }
 
        // =====================
        // HAPUS DATA (admin)
        // =====================
        elseif ($aksi === 'hapus') {
            wajib_admin();
            wajib_post();
            wajib_csrf();
 
            $id_buku = (int) ($_POST['id_buku'] ?? 0);
 
            $hasil = $m_buku->hapus_data($id_buku);
            pesan($hasil['pesan'], '../view/daftar_buku.php');
        }
 
        else {
            pesan('Aksi tidak dikenal', '../view/daftar_buku.php');
        }
 
    } else {
        // Tanpa aksi: siapkan daftar untuk view/daftar_buku.php
        wajib_admin();
        if (isset($_GET['id'])) {
            // dipakai view/edit_buku.php
            $buku_edit = $m_buku->ambil_satu((int) $_GET['id']);
        } else {
            // Kata pencarian dari kotak cari (?cari=...); kosong = tampilkan semua
            $cari = (isset($_GET['cari']) && is_string($_GET['cari'])) ? trim($_GET['cari']) : '';
            $daftar_buku = $m_buku->tampil_data($cari);
        }
    }
 
} catch (Exception $e) {
    error_log($e->getMessage());
    if (!empty($_GET['aksi'])) {
        pesan('Terjadi kesalahan pada server. Pastikan database sudah diimpor.', '../view/daftar_buku.php');
    }
    echo 'Data tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi benar.<br>';
    echo 'Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); // tampil sementara untuk debug
    exit;
}
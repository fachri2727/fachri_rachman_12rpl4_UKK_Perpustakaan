<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_admin.php';

wajib_admin(); // seluruh pengelolaan data admin hanya untuk admin yang sudah login

$m_admin      = null;
$daftar_admin = [];
$cari         = '';
$admin_edit   = null;

function ambil_input_admin()
{
    return [
        'username'   => trim($_POST['username'] ?? ''),
        'nama_admin' => trim($_POST['nama_admin'] ?? ''),
    ];
}

// Kembalikan teks error, atau null jika data valid
function cek_input_admin($in, $password, $password_wajib)
{
    if ($in['username'] === '' || $in['nama_admin'] === '') {
        return 'Username dan nama wajib diisi';
    }
    if (strlen($in['nama_admin']) > 100) {
        return 'Nama maksimal 100 karakter';
    }
    if (!preg_match('/^[A-Za-z0-9_.]{3,50}$/', $in['username'])) {
        return 'Username 3-50 karakter: huruf, angka, titik, atau underscore';
    }
    if (($password_wajib || $password !== '') && strlen($password) < 6) {
        return 'Password minimal 6 karakter';
    }
    return null;
}

try {

    $m_admin = new admin();

    if (!empty($_GET['aksi'])) {

        $aksi = $_GET['aksi'];

        // =====================
        // TAMBAH DATA (registrasi admin baru)
        // =====================
        if ($aksi === 'tambah') {
            wajib_post('../view/register_admin.php');
            wajib_csrf();

            $in         = ambil_input_admin();
            $password   = $_POST['password'] ?? '';
            $konfirmasi = $_POST['konfirmasi'] ?? '';

            $error = cek_input_admin($in, $password, true);
            if (!$error && $password !== $konfirmasi) {
                $error = 'Konfirmasi password tidak sama';
            }
            if (!$error && $m_admin->username_sudah_ada($in['username'])) {
                $error = 'Username sudah dipakai, pilih yang lain';
            }
            if ($error) {
                pesan($error);
            }

            // Password disimpan apa adanya (teks biasa), tidak di-hash
            $ok = $m_admin->tambah_data($in['username'], $in['nama_admin'], $password);

            if ($ok) {
                pesan('Admin baru berhasil didaftarkan', '../view/daftar_admin.php');
            }
            pesan('Registrasi admin gagal');
        }

        // =====================
        // EDIT DATA
        // =====================
        elseif ($aksi === 'edit') {
            wajib_post();
            wajib_csrf();

            $id_admin = (int) ($_POST['id_admin'] ?? 0);
            $in       = ambil_input_admin();
            $password = $_POST['password'] ?? '';

            if ($m_admin->ambil_satu($id_admin) === null) {
                pesan('Data tidak ditemukan', '../view/daftar_admin.php');
            }

            // Password kosong = password lama tidak diubah
            $error = cek_input_admin($in, $password, false);
            if (!$error && $m_admin->username_sudah_ada($in['username'], $id_admin)) {
                $error = 'Username sudah dipakai, pilih yang lain';
            }
            if ($error) {
                pesan($error);
            }

            if ($password === '') {
                $ok = $m_admin->update_data_tanpa_password($id_admin, $in['username'], $in['nama_admin']);
            } else {
                // Password disimpan apa adanya (teks biasa), tidak di-hash
                $ok = $m_admin->update_data($id_admin, $in['username'], $in['nama_admin'], $password);
            }

            // Kalau admin mengubah datanya sendiri, perbarui juga data di session
            if ($ok && $id_admin === (int) ($_SESSION['data']['id_admin'] ?? 0)) {
                $_SESSION['data']['username']   = $in['username'];
                $_SESSION['data']['nama_admin'] = $in['nama_admin'];
            }

            if ($ok) {
                pesan('Data berhasil diubah', '../view/daftar_admin.php');
            }
            pesan('Data gagal diubah');
        }

        // =====================
        // HAPUS DATA
        // =====================
        elseif ($aksi === 'hapus') {
            wajib_post();
            wajib_csrf();

            $id_admin = (int) ($_POST['id_admin'] ?? 0);

            if ($id_admin === (int) ($_SESSION['data']['id_admin'] ?? 0)) {
                pesan('Akun yang sedang dipakai login tidak bisa dihapus', '../view/daftar_admin.php');
            }
            if ($m_admin->jumlah_admin() <= 1) {
                pesan('Admin terakhir tidak boleh dihapus', '../view/daftar_admin.php');
            }

            $ok = $m_admin->hapus_data($id_admin);
            pesan($ok ? 'Data berhasil dihapus' : 'Data gagal dihapus', '../view/daftar_admin.php');
        }

        else {
            pesan('Aksi tidak dikenal', '../view/daftar_admin.php');
        }

    } else {
        // Tanpa aksi: siapkan daftar untuk view/daftar_admin.php
        if (isset($_GET['id'])) {
            // dipakai view/edit_admin.php
            $admin_edit = $m_admin->ambil_satu((int) $_GET['id']);
        } else {
            // Kata pencarian dari kotak cari (?cari=...); kosong = tampilkan semua
            $cari = (isset($_GET['cari']) && is_string($_GET['cari'])) ? trim($_GET['cari']) : '';
            $daftar_admin = $m_admin->tampil_data($cari);
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    if (!empty($_GET['aksi'])) {
        pesan('Terjadi kesalahan pada server. Pastikan database sudah diimpor.', '../view/daftar_admin.php');
    }
    echo 'Data tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi benar.<br>';
    echo 'Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); // tampil sementara untuk debug
    exit;
}
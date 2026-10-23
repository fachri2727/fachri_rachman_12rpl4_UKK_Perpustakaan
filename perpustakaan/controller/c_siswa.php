<?php
include_once __DIR__ . '/auth.php';
include_once __DIR__ . '/../model/m_siswa.php';

$m_siswa      = null;
$daftar_siswa = [];
$cari         = '';
$siswa_edit    = null;

function ambil_input_siswa()
{
    return [
        'nis'        => trim($_POST['nis'] ?? ''),
        'nama_siswa' => trim($_POST['nama_siswa'] ?? ''),
        'username'   => trim($_POST['username'] ?? ''),
        'kelas'      => trim($_POST['kelas'] ?? ''),
    ];
}

// Kembalikan teks error, atau null jika data valid
function cek_input_siswa($in, $password, $password_wajib)
{
    if ($in['nis'] === '' || $in['nama_siswa'] === '' || $in['username'] === '' || $in['kelas'] === '') {
        return 'NIS, nama, username, dan kelas wajib diisi';
    }
    if (strlen($in['nis']) > 20 || strlen($in['kelas']) > 20) {
        return 'NIS dan kelas maksimal 20 karakter';
    }
    if (strlen($in['nama_siswa']) > 100) {
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

    $m_siswa = new m_siswa();

    if (!empty($_GET['aksi'])) {

        $aksi = $_GET['aksi'];

        // =====================
        // REGISTRASI (siswa daftar sendiri)
        // =====================
        if ($aksi === 'registrasi') {
            wajib_post('../view/register.php');

            $in         = ambil_input_siswa();
            $password   = $_POST['password'] ?? '';
            $konfirmasi = $_POST['konfirmasi'] ?? '';

            $error = cek_input_siswa($in, $password, true);
            if (!$error && $password !== $konfirmasi) {
                $error = 'Konfirmasi password tidak sama';
            }
            if (!$error && $m_siswa->username_sudah_ada($in['username'])) {
                $error = 'Username sudah dipakai, pilih yang lain';
            }
            if ($error) {
                pesan($error); // kembali ke form, isian tetap
            }

            // Password disimpan apa adanya (teks biasa), tidak di-hash
            $ok = $m_siswa->tambah_data(
                $in['nis'], $in['nama_siswa'], $in['username'],
                $password, $in['kelas']
            );

            if ($ok) {
                pesan('Registrasi berhasil, silakan login', '../view/login_siswa.php');
            }
            pesan('Registrasi gagal');
        }

        // =====================
        // TAMBAH DATA (admin)
        // =====================
        elseif ($aksi === 'tambah') {
            wajib_admin();
            wajib_post();
            wajib_csrf();

            $in       = ambil_input_siswa();
            $password = $_POST['password'] ?? '';

            $error = cek_input_siswa($in, $password, true);
            if (!$error && $m_siswa->username_sudah_ada($in['username'])) {
                $error = 'Username sudah dipakai, pilih yang lain';
            }
            if ($error) {
                pesan($error);
            }

            // Password disimpan apa adanya (teks biasa), tidak di-hash
            $ok = $m_siswa->tambah_data(
                $in['nis'], $in['nama_siswa'], $in['username'],
                $password, $in['kelas']
            );

            if ($ok) {
                pesan('Data berhasil ditambahkan', '../view/daftar_siswa.php');
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

            $id_siswa = (int) ($_POST['id_siswa'] ?? 0);
            $in       = ambil_input_siswa();
            $password = $_POST['password'] ?? '';

            if ($m_siswa->ambil_satu($id_siswa) === null) {
                pesan('Data tidak ditemukan', '../view/daftar_siswa.php');
            }

            // Password kosong = password lama tidak diubah
            $error = cek_input_siswa($in, $password, false);
            if (!$error && $m_siswa->username_sudah_ada($in['username'], $id_siswa)) {
                $error = 'Username sudah dipakai, pilih yang lain';
            }
            if ($error) {
                pesan($error);
            }

            if ($password === '') {
                $ok = $m_siswa->update_data_tanpa_password(
                    $id_siswa, $in['nis'], $in['nama_siswa'], $in['username'], $in['kelas']
                );
            } else {
                // Password disimpan apa adanya (teks biasa), tidak di-hash
                $ok = $m_siswa->update_data(
                    $id_siswa, $in['nis'], $in['nama_siswa'], $in['username'],
                    $password, $in['kelas']
                );
            }

            if ($ok) {
                pesan('Data berhasil diubah', '../view/daftar_siswa.php');
            }
            pesan('Data gagal diubah');
        }

        // =====================
        // HAPUS DATA (admin)
        // =====================
        elseif ($aksi === 'hapus') {
            wajib_admin();
            wajib_post();
            wajib_csrf();

            $id_siswa = (int) ($_POST['id_siswa'] ?? 0);

            if ($id_siswa === (int) ($_SESSION['data']['id_siswa'] ?? 0)) {
                pesan('Akun yang sedang dipakai login tidak bisa dihapus', '../view/daftar_siswa.php');
            }

            $ok = $m_siswa->hapus_data($id_siswa);
            pesan($ok ? 'Data berhasil dihapus' : 'Data gagal dihapus', '../view/daftar_siswa.php');
        }

        else {
            pesan('Aksi tidak dikenal', '../view/daftar_siswa.php');
        }

    } else {
        // Tanpa aksi: siapkan daftar untuk view/daftar_siswa.php
        wajib_admin();
        if (isset($_GET['id'])) {
            // dipakai view/edit_siswa.php
            $siswa_edit = $m_siswa->ambil_satu((int) $_GET['id']);
        } else {
            // Kata pencarian dari kotak cari (?cari=...); kosong = tampilkan semua
            $cari = (isset($_GET['cari']) && is_string($_GET['cari'])) ? trim($_GET['cari']) : '';
            $daftar_siswa = $m_siswa->tampil_data($cari);
        }
    }

} catch (Exception $e) {
    error_log($e->getMessage());
    if (!empty($_GET['aksi'])) {
        pesan('Terjadi kesalahan pada server. Pastikan database sudah diimpor.', '../view/daftar_siswa.php');
    }
    echo 'Data tidak dapat dimuat. Pastikan database sudah diimpor dan koneksi benar.<br>';
    echo 'Detail: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'); // tampil sementara untuk debug
    exit;
}
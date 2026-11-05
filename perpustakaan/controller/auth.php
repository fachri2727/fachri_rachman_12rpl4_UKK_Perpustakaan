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
// ===========================================================
//  PENCARIAN DASHBOARD (server-side, tanpa JavaScript, tanpa ubah database)
//  Dipakai dashboard admin (c_peminjaman_admin.php) dan dashboard siswa (c_dashboard.php)
// ===========================================================


// Ambil kata kunci dari URL (?nama=...), dirapikan dan dibatasi 100 karakter
function ambil_cari($nama)
{
    $nilai = isset($_GET[$nama]) && is_string($_GET[$nama]) ? $_GET[$nama] : '';
    $nilai = trim(preg_replace('/\s+/u', ' ', $nilai));
    return function_exists('mb_substr') ? mb_substr($nilai, 0, 100) : substr($nilai, 0, 100);
}

// Saring daftar data. $teks = fungsi yang mengembalikan satu string berisi semua teks yang boleh dicari
// dari satu baris. Kata kunci dipisah spasi dan SEMUA kata harus cocok (tidak peka huruf besar/kecil).
function saring_data(array $daftar, $kata, callable $teks)
{
    if ($kata === '') {
        return $daftar;
    }
    $kata_kata = explode(' ', $kata);
    $hasil     = [];
    foreach ($daftar as $baris) {
        $isi    = $teks($baris);
        $cocok  = true;
        foreach ($kata_kata as $k) {
            $ketemu = function_exists('mb_stripos') ? mb_stripos($isi, $k, 0, 'UTF-8') : stripos($isi, $k);
            if ($ketemu === false) {
                $cocok = false;
                break;
            }
        }
        if ($cocok) {
            $hasil[] = $baris;
        }
    }
    return $hasil;
}

// Bangun URL halaman dengan parameter yang diubah. Nilai '' menghapus parameter itu.
// Parameter lain (pencarian di tabel lain, ?riwayat=semua) ikut dipertahankan.
function url_dashboard($file, array $ubah, $anchor = '')
{
    $q = array_merge($_GET, $ubah);
    $q = array_filter($q, function ($v) {
        return $v !== '' && $v !== null;
    });
    return $file . ($q ? '?' . http_build_query($q) : '') . ($anchor !== '' ? '#' . $anchor : '');
}

// Cetak form pencarian untuk satu tabel. Tidak dicetak kalau tabelnya memang tidak punya data ($jumlah_total = 0).
function form_cari($file, $param, $placeholder, $anchor, $cari, $jumlah_hasil, $jumlah_total)
{
    if ($jumlah_total < 1) {
        return '';
    }
    $html = '<form class="cari-bar" method="GET" action="' . e($file) . '#' . e($anchor) . '">';
    // Pertahankan pencarian di tabel lain saat form ini dikirim
    foreach ($_GET as $k => $v) {
        if ($k === $param || !is_string($v) || $v === '') {
            continue;
        }
        $html .= '<input type="hidden" name="' . e($k) . '" value="' . e($v) . '">';
    }
    $html .= '<input type="text" name="' . e($param) . '" value="' . e($cari) . '" placeholder="' . e($placeholder)
           . '" maxlength="100" autocomplete="off">';
    $html .= '<button type="submit" class="btn btn-cari">Cari</button>';
    if ($cari !== '') {
        $html .= '<a class="btn btn-batal" href="' . e(url_dashboard($file, [$param => ''], $anchor)) . '">Reset</a>';
    }
    $html .= '</form>';
    if ($cari !== '') {
        $html .= '<p class="info-cari">Menampilkan ' . (int) $jumlah_hasil . ' hasil untuk &quot;<strong>'
               . e($cari) . '</strong>&quot;</p>';
    }
    return $html;
}

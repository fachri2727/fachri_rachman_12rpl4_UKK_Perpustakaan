<?php
// Controller memuat data peminjaman sekaligus mengecek admin sudah login (wajib_admin)
include_once __DIR__ . '/../controller/c_peminjaman_admin.php';

$aktif       = 'dashboard';
$nama_admin  = $_SESSION['data']['nama_admin'] ?? ($_SESSION['data']['username'] ?? 'Admin');

// Riwayat: 10 terbaru, atau semua kalau ada ?riwayat=semua
$tampil_semua   = (($_GET['riwayat'] ?? '') === 'semua');
$riwayat_tampil = $tampil_semua ? $daftar_riwayat : array_slice($daftar_riwayat, 0, 10);

// Angka ringkasan
$jml_menunggu         = count($daftar_menunggu);
$jml_menunggu_kembali = count($daftar_menunggu_kembali);
$total_estimasi_denda = 0;
foreach ($daftar_menunggu_kembali as $d) {
    $total_estimasi_denda += (int) $d->estimasi_denda;
}

// Buku yang sedang dipinjam: hitung yang terlambat dan denda berjalannya
$jml_dipinjam   = count($daftar_dipinjam);
$jml_terlambat  = 0;
$denda_berjalan = 0;
foreach ($daftar_dipinjam as $d) {
    if ($d->hari_terlambat > 0) {
        $jml_terlambat++;
    }
    $denda_berjalan += (int) $d->denda_berjalan;
}
$total_denda_sementara = $total_estimasi_denda + $denda_berjalan;

function label_status_admin($status)
{
    $peta = [
        'disetujui'    => ['Disetujui', 'status-tersedia'],
        'ditolak'      => ['Ditolak', 'status-dipinjam'],
        'dikembalikan' => ['Dikembalikan', 'status-tersedia'],
    ];
    return isset($peta[$status]) ? $peta[$status] : [ucfirst($status), 'status-dipinjam'];
}

// Cetak satu tombol aksi (form POST kecil dengan token CSRF)
function form_aksi_peminjaman($aksi, $id_peminjaman, $label, $kelas_tombol, $tanya)
{
    return '<form action="../controller/c_peminjaman_admin.php?aksi=' . e($aksi) . '" method="POST" style="display:inline"'
         . ' onsubmit="return confirm(' . e(json_encode($tanya)) . ')">'
         . csrf_field()
         . '<input type="hidden" name="id_peminjaman" value="' . (int) $id_peminjaman . '">'
         . '<button type="submit" class="btn ' . e($kelas_tombol) . '">' . e($label) . '</button>'
         . '</form>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin</title>
<link rel="stylesheet" href="admin.css">
<style>
  .container.lebar { max-width: 1180px; }

  .ringkasan { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-top: 24px; }
  .ringkasan a.angka { display: block; text-decoration: none; background: var(--krem-bg); border: 1px solid var(--tan); border-radius: 10px; padding: 14px 18px; color: var(--teks); transition: transform .15s ease, box-shadow .15s ease; }
  .ringkasan a.angka:hover { transform: translateY(-2px); box-shadow: 0 6px 14px rgba(78, 52, 46, .12); }
  .ringkasan .angka strong { display: block; font-size: 28px; color: var(--coklat-tua); }
  .ringkasan .angka span { font-size: 13px; color: var(--coklat); }
  .ringkasan .angka.perlu { border-color: var(--emas-pudar); background: #fbf0dc; }
  .ringkasan .angka.perlu strong { color: var(--merah-bata); }

  .bagian-judul { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; flex-wrap: wrap; margin: 34px 0 0; }
  .bagian-judul h2 { margin: 0; }
  .bagian-judul .jumlah { font-size: 13px; color: var(--coklat-muda); }
  .bagian-judul a { font-size: 13px; color: var(--coklat); font-weight: 600; }
  .tabel-scroll { overflow-x: auto; }
  .tabel-scroll table { min-width: 720px; }
  td .btn { padding: 6px 12px; font-size: 13px; }
  .nis { color: var(--coklat-muda); }
  .judul-kelola { margin: 40px 0 0; }
</style>
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>

<div class="container lebar">
    <div class="dashboard-hero">
        <div class="avatar"><?= strtoupper(substr($nama_admin, 0, 1)) ?></div>
        <div>
            <h2>Selamat datang, <?= e($nama_admin) ?></h2>
            <p>Konfirmasi peminjaman dan pengembalian buku bisa langsung Anda proses di halaman ini.</p>
        </div>
    </div>

    <div class="ringkasan">
        <a class="angka <?= $jml_menunggu > 0 ? 'perlu' : '' ?>" href="#peminjaman">
            <strong><?= $jml_menunggu ?></strong><span>Pengajuan peminjaman menunggu</span>
        </a>
        <a class="angka <?= $jml_menunggu_kembali > 0 ? 'perlu' : '' ?>" href="#pengembalian">
            <strong><?= $jml_menunggu_kembali ?></strong><span>Pengajuan pengembalian menunggu</span>
        </a>
        <a class="angka <?= $jml_terlambat > 0 ? 'perlu' : '' ?>" href="#dipinjam">
            <strong><?= $jml_terlambat ?></strong><span>Buku terlambat (belum dikembalikan)</span>
        </a>
        <a class="angka" href="#dipinjam">
            <strong>Rp<?= number_format($total_denda_sementara, 0, ',', '.') ?></strong><span>Denda sementara (belum tercatat)</span>
        </a>
    </div>

    <!-- ============ 1. PENGAJUAN PEMINJAMAN ============ -->
    <div class="bagian-judul" id="peminjaman">
        <h2>Menunggu Konfirmasi Peminjaman</h2>
        <span class="jumlah"><?= $jml_menunggu ?> pengajuan</span>
    </div>
    <div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Judul Buku</th>
                <th>Tgl Pengajuan</th>
                <th>Batas Kembali</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_menunggu)):
        foreach ($daftar_menunggu as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->nama_siswa) ?> <span class="nis">(<?= e($data->nis) ?>)</span></td>
                <td><?= e($data->judul_buku) ?></td>
                <td><?= date('d M Y', strtotime($data->tgl_pinjam)) ?></td>
                <td><?= date('d M Y', strtotime($data->tgl_pengembalian)) ?></td>
                <td>
                    <?= form_aksi_peminjaman('setuju', $data->id_peminjaman, 'Setujui', 'btn-tambah', 'Setujui peminjaman ini?') ?>
                    <?= form_aksi_peminjaman('tolak', $data->id_peminjaman, 'Tolak', 'btn-hapus', 'Tolak peminjaman ini?') ?>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr><td colspan="6" class="pesan-kosong">Tidak ada pengajuan yang menunggu konfirmasi</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- ============ 2. PENGAJUAN PENGEMBALIAN ============ -->
    <div class="bagian-judul" id="pengembalian">
        <h2>Menunggu Konfirmasi Pengembalian</h2>
        <span class="jumlah"><?= $jml_menunggu_kembali ?> pengajuan &middot; denda dihitung sampai tanggal siswa mengajukan kembali</span>
    </div>
    <div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Judul Buku</th>
                <th>Batas Kembali</th>
                <th>Diajukan Kembali</th>
                <th>Hari Terlambat</th>
                <th>Estimasi Denda</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_menunggu_kembali)):
        foreach ($daftar_menunggu_kembali as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->nama_siswa) ?> <span class="nis">(<?= e($data->nis) ?>)</span></td>
                <td><?= e($data->judul_buku) ?></td>
                <td><?= date('d M Y', strtotime($data->tgl_pengembalian)) ?></td>
                <td><?= !empty($data->tgl_ajukan_kembali) ? date('d M Y, H:i', strtotime($data->tgl_ajukan_kembali)) : '&mdash;' ?></td>
                <td>
                    <?php if ($data->hari_terlambat > 0): ?>
                        <span class="status status-dipinjam"><?= (int) $data->hari_terlambat ?> hari</span>
                    <?php else: ?>
                        <span class="status status-tersedia">Tepat waktu</span>
                    <?php endif; ?>
                </td>
                <td>Rp<?= number_format($data->estimasi_denda, 0, ',', '.') ?></td>
                <td>
                    <?= form_aksi_peminjaman('konfirmasi_kembali', $data->id_peminjaman, 'Konfirmasi Kembali', 'btn-tambah', 'Konfirmasi buku ini sudah dikembalikan?') ?>
                    <?= form_aksi_peminjaman('tolak_kembali', $data->id_peminjaman, 'Tolak', 'btn-hapus', 'Tolak pengajuan pengembalian ini?') ?>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr><td colspan="8" class="pesan-kosong">Tidak ada pengajuan pengembalian yang menunggu konfirmasi</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- ============ 3. SEDANG DIPINJAM ============ -->
    <div class="bagian-judul" id="dipinjam">
        <h2>Sedang Dipinjam</h2>
        <span class="jumlah"><?= $jml_dipinjam ?> buku &middot; yang paling terlambat di atas</span>
    </div>
    <div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Judul Buku</th>
                <th>Batas Kembali</th>
                <th>Keterlambatan</th>
                <th>Denda Berjalan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_dipinjam)):
        foreach ($daftar_dipinjam as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->nama_siswa) ?> <span class="nis">(<?= e($data->nis) ?>)</span></td>
                <td><?= e($data->judul_buku) ?></td>
                <td><?= date('d M Y', strtotime($data->tgl_pengembalian)) ?></td>
                <td>
                    <?php if ($data->hari_terlambat > 0): ?>
                        <span class="status status-dipinjam">Terlambat <?= (int) $data->hari_terlambat ?> hari</span>
                    <?php else: ?>
                        <span class="status status-tersedia">Belum jatuh tempo</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($data->denda_berjalan > 0): ?>
                        Rp<?= number_format($data->denda_berjalan, 0, ',', '.') ?>
                    <?php else: ?>
                        &mdash;
                    <?php endif; ?>
                </td>
                <td>
                    <?= form_aksi_peminjaman('kembalikan_langsung', $data->id_peminjaman, 'Tandai Dikembalikan', 'btn-tambah', 'Tandai buku ini sudah dikembalikan? Denda dihitung sampai hari ini.') ?>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr><td colspan="7" class="pesan-kosong">Tidak ada buku yang sedang dipinjam</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <!-- ============ 4. RIWAYAT ============ -->
    <div class="bagian-judul" id="riwayat">
        <h2>Riwayat Peminjaman</h2>
        <?php if ($tampil_semua): ?>
            <a href="dashboard_admin.php#riwayat">Tampilkan 10 terbaru</a>
        <?php elseif (count($daftar_riwayat) > 10): ?>
            <a href="dashboard_admin.php?riwayat=semua#riwayat">Lihat semua &rarr;</a>
        <?php endif; ?>
    </div>
    <div class="tabel-scroll">
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Judul Buku</th>
                <th>Tgl Pengajuan</th>
                <th>Tgl Dikembalikan</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($riwayat_tampil)):
        foreach ($riwayat_tampil as $data):
            list($label, $kelas) = label_status_admin($data->status); ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->nama_siswa) ?> <span class="nis">(<?= e($data->nis) ?>)</span></td>
                <td><?= e($data->judul_buku) ?></td>
                <td><?= date('d M Y', strtotime($data->tgl_pinjam)) ?></td>
                <td><?= !empty($data->tgl_dikembalikan) ? date('d M Y', strtotime($data->tgl_dikembalikan)) : '&mdash;' ?></td>
                <td>
                    <?php if ($data->status !== 'dikembalikan'): ?>
                        &mdash;
                    <?php elseif ((int) $data->denda > 0): ?>
                        Rp<?= number_format((int) $data->denda, 0, ',', '.') ?>
                    <?php else: ?>
                        Tidak ada
                    <?php endif; ?>
                </td>
                <td><span class="status <?= e($kelas) ?>"><?= e($label) ?></span></td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr><td colspan="7" class="pesan-kosong">Belum ada riwayat peminjaman</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>

    <h2 class="judul-kelola">Kelola Data</h2>
    <div class="kartu-menu">
        <a class="kartu" href="daftar_siswa.php">
            <h3>Data Siswa</h3>
            <p>Kelola anggota perpustakaan: tambah, ubah, dan hapus data siswa.</p>
        </a>
        <a class="kartu" href="daftar_buku.php">
            <h3>Data Buku</h3>
            <p>Kelola koleksi buku: tambah, ubah, dan hapus data buku.</p>
        </a>
        <a class="kartu" href="daftar_admin.php">
            <h3>Data Admin</h3>
            <p>Kelola akun admin: tambah, ubah, dan hapus data admin.</p>
        </a>
    </div>
</div>

</body>
</html>
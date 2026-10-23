<?php
// ambil data dari controller (sekaligus cek login admin)
include_once __DIR__ . '/../controller/c_siswa.php';
$aktif = 'daftar';
$id_login = (int) ($_SESSION['data']['id_siswa'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Siswa</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>

<div class="container">
    <h2>Daftar Anggota Perpustakaan</h2>

    <div class="toolbar">
        <a class="btn btn-tambah" href="tambah_siswa.php">+ Tambah Anggota</a>

        <form class="cari-bar" method="GET" action="daftar_siswa.php">
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari NIS, nama, username, atau kelas" maxlength="100" autocomplete="off">
            <button type="submit" class="btn btn-cari">Cari</button>
            <?php if ($cari !== ''): ?>
                <a class="btn btn-batal" href="daftar_siswa.php">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <?php if ($cari !== ''): ?>
        <p class="info-cari">Menampilkan <?= count($daftar_siswa) ?> hasil untuk &quot;<strong><?= e($cari) ?></strong>&quot;</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Kelas</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_siswa)):
        foreach ($daftar_siswa as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->nis) ?></td>
                <td><?= e($data->nama_siswa) ?></td>
                <td><?= e($data->username) ?></td>
                <td><?= e($data->kelas) ?></td>
                <td>
                    <a class="btn btn-ubah" href="edit_siswa.php?id=<?= (int) $data->id_siswa ?>">Ubah</a>
                    <?php if ((int) $data->id_siswa !== $id_login): ?>
                    <form action="../controller/c_siswa.php?aksi=hapus" method="POST"
                          onsubmit="return confirm('Yakin hapus data?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_siswa" value="<?= (int) $data->id_siswa ?>">
                        <button type="submit" class="btn btn-hapus">Hapus</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr>
                <td colspan="6" class="pesan-kosong">
                    <?= $cari !== '' ? 'Tidak ada data yang cocok dengan pencarian' : 'Data tidak tersedia' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
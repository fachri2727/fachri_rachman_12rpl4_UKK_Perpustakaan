<?php
// ambil data dari controller (sekaligus cek login admin)
include_once __DIR__ . '/../controller/c_buku.php';
$aktif = 'buku';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Buku</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Daftar Buku</h2>
 
    <div class="toolbar">
        <a class="btn btn-tambah" href="create_buku.php">+ Tambah Buku</a>

        <form class="cari-bar" method="GET" action="daftar_buku.php">
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari judul, pengarang, penerbit, tahun, atau status" maxlength="100" autocomplete="off">
            <button type="submit" class="btn btn-cari">Cari</button>
            <?php if ($cari !== ''): ?>
                <a class="btn btn-batal" href="daftar_buku.php">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <?php if ($cari !== ''): ?>
        <p class="info-cari">Menampilkan <?= count($daftar_buku) ?> hasil untuk &quot;<strong><?= e($cari) ?></strong>&quot;</p>
    <?php endif; ?>
 
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Judul</th>
                <th>Pengarang</th>
                <th>Penerbit</th>
                <th>Tahun</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_buku)):
        foreach ($daftar_buku as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= e($data->judul_buku) ?></td>
                <td><?= e($data->pengarang) ?></td>
                <td><?= e($data->penerbit) ?></td>
                <td><?= e($data->tahun_terbit) ?></td>
                <td><?= (int) $data->tersedia ?> / <?= (int) $data->stok ?> <small>tersedia</small></td>
                <td>
                    <span class="status status-<?= $data->tersedia > 0 ? 'tersedia' : 'dipinjam' ?>">
                        <?= $data->tersedia > 0 ? 'Tersedia' : 'Habis' ?>
                    </span>
                </td>
                <td>
                    <a class="btn btn-ubah" href="edit_buku.php?id=<?= (int) $data->id_buku ?>">Ubah</a>
                    <form action="../controller/c_buku.php?aksi=hapus" method="POST"
                          onsubmit="return confirm('Yakin hapus data?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_buku" value="<?= (int) $data->id_buku ?>">
                        <button type="submit" class="btn btn-hapus">Hapus</button>
                    </form>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr>
                <td colspan="8" class="pesan-kosong">
                    <?= $cari !== '' ? 'Tidak ada data yang cocok dengan pencarian' : 'Data tidak tersedia' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
 
</body>
</html>
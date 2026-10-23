<?php
// ambil data dari controller (sekaligus cek login admin)
include_once __DIR__ . '/../controller/c_admin.php';
$aktif       = 'admin';
$id_login    = (int) ($_SESSION['data']['id_admin'] ?? 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>

<div class="container">
    <h2>Daftar Admin</h2>

    <div class="toolbar">
        <a class="btn btn-tambah" href="register_admin.php">+ Tambah Admin</a>

        <form class="cari-bar" method="GET" action="daftar_admin.php">
            <input type="text" name="cari" value="<?= e($cari) ?>" placeholder="Cari nama atau username" maxlength="100" autocomplete="off">
            <button type="submit" class="btn btn-cari">Cari</button>
            <?php if ($cari !== ''): ?>
                <a class="btn btn-batal" href="daftar_admin.php">Reset</a>
            <?php endif; ?>
        </form>
    </div>
    <?php if ($cari !== ''): ?>
        <p class="info-cari">Menampilkan <?= count($daftar_admin) ?> hasil untuk &quot;<strong><?= e($cari) ?></strong>&quot;</p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        if (!empty($daftar_admin)):
        foreach ($daftar_admin as $data): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td>
                    <?= e($data->nama_admin) ?>
                    <?php if ((int) $data->id_admin === $id_login): ?>
                        <span class="status status-tersedia">Anda</span>
                    <?php endif; ?>
                </td>
                <td><?= e($data->username) ?></td>
                <td>
                    <a class="btn btn-ubah" href="edit_admin.php?id=<?= (int) $data->id_admin ?>">Ubah</a>
                    <?php if ((int) $data->id_admin !== $id_login): ?>
                    <form action="../controller/c_admin.php?aksi=hapus" method="POST"
                          onsubmit="return confirm('Yakin hapus admin ini?')">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id_admin" value="<?= (int) $data->id_admin ?>">
                        <button type="submit" class="btn btn-hapus">Hapus</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach;
        else: ?>
            <tr>
                <td colspan="4" class="pesan-kosong">
                    <?= $cari !== '' ? 'Tidak ada data yang cocok dengan pencarian' : 'Data tidak tersedia' ?>
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
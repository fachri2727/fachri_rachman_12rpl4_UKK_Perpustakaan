<?php
// Include controller: sekaligus cek admin sudah login & ambil data admin (dari ?id=)
include_once __DIR__ . '/../controller/c_admin.php';
$aktif = 'admin';

// Kalau id tidak valid / data tidak ditemukan, kembali ke daftar
if (empty($admin_edit)) {
    header("Location: daftar_admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ubah Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>

<div class="container">
    <h2>Ubah Data Admin</h2>

    <form class="form-siswa" action="../controller/c_admin.php?aksi=edit" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id_admin" value="<?= (int) $admin_edit->id_admin ?>">

        <div class="form-group">
            <label for="nama_admin">Nama Lengkap</label>
            <input type="text" id="nama_admin" name="nama_admin" maxlength="100"
                   value="<?= e($admin_edit->nama_admin) ?>" required autofocus>
        </div>

        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="50"
                   value="<?= e($admin_edit->username) ?>" required>
        </div>

        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" minlength="6"
                   placeholder="Kosongkan jika tidak ingin diubah">
        </div>

        <div class="form-aksi">
            <button type="submit" class="btn btn-ubah">Simpan Perubahan</button>
            <a class="btn btn-batal" href="daftar_admin.php">Batal</a>
        </div>
    </form>
</div>

</body>
</html>
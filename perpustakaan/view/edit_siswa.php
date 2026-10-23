<?php
// Include controller: sekaligus cek admin sudah login & ambil data siswa (dari ?id=)
include_once __DIR__ . '/../controller/c_siswa.php';
$aktif = 'daftar';
 
// Kalau id tidak valid / data tidak ditemukan, kembali ke daftar
if (empty($siswa_edit)) {
    header("Location: daftar_siswa.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ubah Siswa</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Ubah Data Anggota</h2>
 
    <form class="form-siswa" action="../controller/c_siswa.php?aksi=edit" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id_siswa" value="<?= (int) $siswa_edit->id_siswa ?>">
 
        <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" name="nis" maxlength="20"
                   value="<?= e($siswa_edit->nis) ?>" required autofocus>
        </div>
 
        <div class="form-group">
            <label for="nama_siswa">Nama Lengkap</label>
            <input type="text" id="nama_siswa" name="nama_siswa" maxlength="100"
                   value="<?= e($siswa_edit->nama_siswa) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="50"
                   value="<?= e($siswa_edit->username) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" id="kelas" name="kelas" maxlength="20"
                   value="<?= e($siswa_edit->kelas) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="password">Password Baru</label>
            <input type="password" id="password" name="password" minlength="6"
                   placeholder="Kosongkan jika tidak ingin diubah">
        </div>
 
        <div class="form-aksi">
            <button type="submit" class="btn btn-ubah">Simpan Perubahan</button>
            <a class="btn btn-batal" href="daftar_siswa.php">Batal</a>
        </div>
    </form>
</div>
 
</body>
</html>
<?php
// Include controller: sekaligus cek admin sudah login (wajib_admin)
include_once __DIR__ . '/../controller/c_siswa.php';
$aktif = 'daftar';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Siswa</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Tambah Anggota Baru</h2>
 
    <form class="form-siswa" action="../controller/c_siswa.php?aksi=tambah" method="POST">
        <?= csrf_field() ?>
 
        <div class="form-group">
            <label for="nis">NIS</label>
            <input type="text" id="nis" name="nis" maxlength="20" required autofocus>
        </div>
 
        <div class="form-group">
            <label for="nama_siswa">Nama Lengkap</label>
            <input type="text" id="nama_siswa" name="nama_siswa" maxlength="100" required>
        </div>
 
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="50"
                   placeholder="huruf, angka, titik, atau underscore" required>
        </div>
 
        <div class="form-group">
            <label for="kelas">Kelas</label>
            <input type="text" id="kelas" name="kelas" maxlength="20" required>
        </div>
 
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="6"
                   placeholder="Minimal 6 karakter" required>
        </div>
 
        <div class="form-aksi">
            <button type="submit" class="btn btn-tambah">Simpan</button>
            <a class="btn btn-batal" href="daftar_siswa.php">Batal</a>
        </div>
    </form>
</div>
 
</body>
</html>
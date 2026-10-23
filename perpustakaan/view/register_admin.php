<?php
include_once __DIR__ . '/../controller/auth.php';
wajib_admin(); // hanya admin yang sudah login yang boleh buka halaman ini
$aktif = 'admin';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Admin</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Daftarkan Admin Baru</h2>
 
    <form class="form-siswa" action="../controller/c_admin.php?aksi=tambah" method="POST">
        <?= csrf_field() ?>
 
        <div class="form-group">
            <label for="nama_admin">Nama Lengkap</label>
            <input type="text" id="nama_admin" name="nama_admin" maxlength="100" required autofocus>
        </div>
 
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" maxlength="50"
                   placeholder="huruf, angka, titik, atau underscore" required>
        </div>
 
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" minlength="6"
                   placeholder="Minimal 6 karakter" required>
        </div>
 
        <div class="form-group">
            <label for="konfirmasi">Konfirmasi Password</label>
            <input type="password" id="konfirmasi" name="konfirmasi" minlength="6" required>
        </div>
 
        <div class="form-aksi">
            <button type="submit" class="btn btn-tambah">Daftarkan</button>
            <a class="btn btn-batal" href="daftar_admin.php">Batal</a>
        </div>
    </form>
</div>
 
</body>
</html>
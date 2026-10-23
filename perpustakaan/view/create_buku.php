<?php
// Include controller: sekaligus cek admin sudah login (wajib_admin)
include_once __DIR__ . '/../controller/c_buku.php';
$aktif = 'buku';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Buku</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Tambah Buku Baru</h2>
 
    <form class="form-siswa" action="../controller/c_buku.php?aksi=tambah" method="POST">
        <?= csrf_field() ?>
 
        <div class="form-group">
            <label for="judul_buku">Judul Buku</label>
            <input type="text" id="judul_buku" name="judul_buku" maxlength="150" required autofocus>
        </div>
 
        <div class="form-group">
            <label for="pengarang">Pengarang</label>
            <input type="text" id="pengarang" name="pengarang" maxlength="100" required>
        </div>
 
        <div class="form-group">
            <label for="penerbit">Penerbit</label>
            <input type="text" id="penerbit" name="penerbit" maxlength="100" required>
        </div>
 
        <div class="fAorm-group">
            <label for="tahun_terbit">Tahun Terbit</label>
            <input type="number" id="tahun_terbit" name="tahun_terbit" min="1900" max="<?= date('Y') + 1 ?>" required>
        </div>
 
        <div class="form-group">
            <label for="stok">Jumlah Stok (eksemplar)</label>
            <input type="number" id="stok" name="stok" min="1" max="999" value="1" required>
        </div>
 
        <div class="form-aksi">
            <button type="submit" class="btn btn-tambah">Simpan</button>
            <a class="btn btn-batal" href="daftar_buku.php">Batal</a>
        </div>
    </form>
</div>
 
</body>
</html>
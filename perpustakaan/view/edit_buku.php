<?php
// Include controller: sekaligus cek admin sudah login & ambil data buku (dari ?id=)
include_once __DIR__ . '/../controller/c_buku.php';
$aktif = 'buku';
 
// Kalau id tidak valid / data tidak ditemukan, kembali ke daftar
if (empty($buku_edit)) {
    header("Location: daftar_buku.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ubah Buku</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php include __DIR__ . '/_nav_admin.php'; ?>
 
<div class="container">
    <h2>Ubah Data Buku</h2>
 
    <form class="form-siswa" action="../controller/c_buku.php?aksi=edit" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="id_buku" value="<?= (int) $buku_edit->id_buku ?>">
 
        <div class="form-group">
            <label for="judul_buku">Judul Buku</label>
            <input type="text" id="judul_buku" name="judul_buku" maxlength="150"
                   value="<?= e($buku_edit->judul_buku) ?>" required autofocus>
        </div>
 
        <div class="form-group">
            <label for="pengarang">Pengarang</label>
            <input type="text" id="pengarang" name="pengarang" maxlength="100"
                   value="<?= e($buku_edit->pengarang) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="penerbit">Penerbit</label>
            <input type="text" id="penerbit" name="penerbit" maxlength="100"
                   value="<?= e($buku_edit->penerbit) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="tahun_terbit">Tahun Terbit</label>
            <input type="number" id="tahun_terbit" name="tahun_terbit" min="1900" max="<?= date('Y') + 1 ?>"
                   value="<?= e($buku_edit->tahun_terbit) ?>" required>
        </div>
 
        <div class="form-group">
            <label for="stok">Jumlah Stok (eksemplar)</label>
            <input type="number" id="stok" name="stok" min="<?= max(1, (int) $buku_edit->dipinjam) ?>" max="999"
                   value="<?= (int) $buku_edit->stok ?>" required>
            <small style="color:var(--coklat-muda);">
                Sedang dipinjam/diajukan: <?= (int) $buku_edit->dipinjam ?> eksemplar. Stok tidak boleh kurang dari angka ini.
            </small>
        </div>
 
        <div class="form-aksi">
            <button type="submit" class="btn btn-ubah">Simpan Perubahan</button>
            <a class="btn btn-batal" href="daftar_buku.php">Batal</a>
        </div>
    </form>
</div>
 
</body>
</html>
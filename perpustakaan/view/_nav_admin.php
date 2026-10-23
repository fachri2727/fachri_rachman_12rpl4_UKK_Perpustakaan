<?php
$aktif      = $aktif ?? '';
$nama_admin = $_SESSION['data']['nama_admin'] ?? ($_SESSION['data']['username'] ?? 'Admin');
?>
<div class="navbar">
  <div class="brand">Perpustakaan · Admin</div>
  <div class="menu">
    <span>Halo, <?= e($nama_admin) ?></span>
    <a href="dashboard_admin.php" class="<?= $aktif === 'dashboard' ? 'aktif' : '' ?>">Dashboard</a>
    <a href="daftar_siswa.php" class="<?= $aktif === 'daftar' ? 'aktif' : '' ?>">Data Siswa</a>
    <a href="daftar_buku.php" class="<?= $aktif === 'buku' ? 'aktif' : '' ?>">Data Buku</a>
    <a href="daftar_admin.php" class="<?= $aktif === 'admin' ? 'aktif' : '' ?>">Data Admin</a>
    <a href="../controller/logout.php" class="keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?');">Keluar</a>
  </div>
</div>
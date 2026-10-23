<?php
// ambil data dari controller (sekaligus cek login siswa)
include_once __DIR__ . '/../controller/c_dashboard.php';

function label_status_siswa($status)
{
    $peta = [
        'menunggu'              => ['Menunggu Konfirmasi', 'due'],
        'disetujui'             => ['Disetujui', 'ok'],
        'ditolak'               => ['Ditolak', 'late'],
        'menunggu_pengembalian' => ['Menunggu Konfirmasi Kembali', 'due'],
        'dikembalikan'          => ['Sudah Dikembalikan', 'ok'],
    ];
    return $peta[$status] ?? [ucfirst($status), 'due'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Dashboard Perpustakaan Siswa</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --cream:#F3E9DA;
    --paper:#FBF5EA;
    --wood-dark:#2E2016;
    --wood:#4A3324;
    --leather:#8A5A3B;
    --clay:#B4703F;
    --ink:#33251A;
    --ink-soft:#6B5646;
    --line:#DCCBAF;
    --good:#5C7A54;
    --warn:#B4703F;
    --bad:#A2483A;
    box-sizing:border-box;
    padding-top:env(safe-area-inset-top,0px);
    padding-bottom:env(safe-area-inset-bottom,0px);
  }
  *{box-sizing:border-box;}
  html{scroll-padding-top:env(safe-area-inset-top,0px);}
  html{height:100%;}
  body{
    margin:0;
    background:var(--cream);
    color:var(--ink);
    font-family:'Inter',sans-serif;
    display:flex;
    min-height:100%;
  }
  h1,h2,h3,.serif{font-family:'Fraunces',serif;}
 
  /* Sidebar */
  .sidebar{
    width:230px;
    flex-shrink:0;
    background:var(--wood-dark);
    color:var(--cream);
    padding:28px 20px;
    display:flex;
    flex-direction:column;
    gap:34px;
  }
  .brand{display:flex;align-items:center;gap:10px;}
  .brand-mark{
    width:34px;height:34px;border-radius:6px;
    background:linear-gradient(160deg,var(--leather),var(--clay));
    display:flex;align-items:center;justify-content:center;
    font-family:'Fraunces',serif;font-weight:600;font-size:17px;color:var(--paper);
  }
  .brand-name{font-family:'Fraunces',serif;font-size:17px;line-height:1.15;}
  .brand-name span{display:block;font-size:11px;color:#C9B79A;font-family:'Inter',sans-serif;margin-top:2px;}
  nav{display:flex;flex-direction:column;gap:3px;}
  .nav-item{
    display:flex;align-items:center;gap:11px;
    padding:10px 12px;border-radius:8px;
    color:#D8C6AA;text-decoration:none;font-size:14px;
    cursor:pointer;border:1px solid transparent;
  }
  .nav-item:hover{background:#3C2A1C;}
  .nav-item.active{background:#4A3324;color:var(--paper);border-color:#5C3F29;}
  .nav-icon{width:16px;text-align:center;opacity:.85;}
  .sidebar-foot{
    margin-top:auto;padding-top:18px;border-top:1px solid #4A3324;
    font-size:12px;color:#9C8567;line-height:1.5;
  }
  .sidebar-foot a{color:#E8C9A8;text-decoration:none;font-weight:600;}
  .sidebar-foot a:hover{text-decoration:underline;}
 
  /* Main */
  main{flex:1;padding:32px 40px 60px;max-width:1180px;}
  .topbar{
    display:flex;justify-content:space-between;align-items:flex-start;
    margin-bottom:30px;gap:20px;flex-wrap:wrap;
  }
  .greeting h1{font-size:26px;margin:0 0 4px;color:var(--wood);font-weight:500;}
  .greeting p{margin:0;color:var(--ink-soft);font-size:14px;}
 
  /* Stats */
  .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:32px;}
  .stat-card{background:var(--paper);border:1px solid var(--line);border-radius:12px;padding:18px 20px;}
  .stat-card .label{font-size:12.5px;color:var(--ink-soft);margin-bottom:10px;}
  .stat-card .value{font-family:'Fraunces',serif;font-size:22px;color:var(--wood);word-break:break-word;}
  .stat-card .sub{font-size:12px;margin-top:6px;color:var(--ink-soft);}
  .stat-card.accent{background:var(--wood);border-color:var(--wood);}
  .stat-card.accent .label,.stat-card.accent .sub{color:#C9B79A;}
  .stat-card.accent .value{color:var(--paper);}
 
  .grid-2{display:grid;grid-template-columns:1.55fr 1fr;gap:22px;align-items:start;}
 
  .panel{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:22px 24px;margin-bottom:22px;}
  .panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
  .panel-head h2{font-size:17px;margin:0;color:var(--wood);font-weight:500;}
 
  .empty-state{text-align:center;padding:30px 10px;color:var(--ink-soft);font-size:13.5px;line-height:1.7;}
  .empty-state .icon{font-size:26px;margin-bottom:8px;}
 
  .info-list{display:flex;flex-direction:column;gap:0;}
  .info-row{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #EEE2CE;font-size:13.5px;}
  .info-row:last-child{border-bottom:none;}
  .info-row .k{color:var(--ink-soft);}
  .info-row .v{color:var(--ink);font-weight:600;text-align:right;}
  .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:600;background:#E4EADD;color:var(--good);}
  .badge.ok{background:#E4EADD;color:var(--good);}
  .badge.due{background:#F5E2CD;color:var(--warn);}
  .badge.late{background:#F1DAD4;color:var(--bad);}
  .notice{background:#F5E2CD;border:1px solid #E5C9A4;color:var(--wood);border-radius:10px;padding:12px 16px;font-size:13.5px;margin-bottom:22px;line-height:1.6;}
  .notice.bad{background:#F1DAD4;border-color:#E6B8AE;}
  .notice a{color:var(--leather);font-weight:600;}
  .denda{color:var(--bad);font-weight:600;}

  table{width:100%;border-collapse:collapse;font-size:13.5px;}
  thead th{text-align:left;font-weight:600;color:var(--ink-soft);font-size:11.5px;padding:0 10px 10px;border-bottom:1px solid var(--line);}
  tbody td{padding:12px 10px;border-bottom:1px solid #EEE2CE;vertical-align:middle;}
  tbody tr:last-child td{border-bottom:none;}
  .book-title{font-weight:600;color:var(--ink);}
  .book-sub{font-size:12px;color:var(--ink-soft);margin-top:2px;}
 
  /* Sidebar tetap di tempat saat halaman di-scroll (tidak ikut terbawa naik) */
  @media (min-width:921px){
    .sidebar{position:sticky;top:0;align-self:flex-start;height:100vh;overflow-y:auto;}
  }

  @media (max-width:920px){
    body{flex-direction:column;}
    .sidebar{width:100%;flex-direction:row;align-items:center;padding:16px 20px;overflow-x:auto;}
    .sidebar-foot{display:none;}
    nav{flex-direction:row;}
    main{padding:24px 18px 40px;}
    .stats{grid-template-columns:repeat(2,1fr);}
    .grid-2{grid-template-columns:1fr;}
  }
</style>
</head>
<body>
 
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-mark">P</div>
      <div class="brand-name">Perpustakaan<span>Perpustakaan Siswa</span></div>
    </div>
    <nav>
      <a class="nav-item active"><span class="nav-icon">⌂</span> Dashboard</a>
      <a href="peminjaman_buku.php" class="nav-item"><span class="nav-icon">📖</span> Pinjam Buku</a>
      <a href="../controller/logout.php" class="nav-item" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><span class="nav-icon">⏻</span> Keluar</a>
    </nav>
    <div class="sidebar-foot">
      Kartu Anggota<br>
      <strong style="color:#D8C6AA">NIS <?= e($siswa->nis) ?></strong>
    </div>
  </aside>
 
  <main>
    <div class="topbar">
      <div class="greeting">
        <h1>Selamat datang, <?= e($siswa->nama_siswa) ?> 👋</h1>
        <p>Kelas <?= e($siswa->kelas) ?> — berikut ringkasan akun perpustakaanmu.</p>
      </div>
    </div>
 
    <?php if ($total_denda > 0): ?>
      <div class="notice bad">
        Kamu punya denda berjalan <strong>Rp<?= number_format($total_denda, 0, ',', '.') ?></strong>.
        <a href="peminjaman_buku.php">Lihat detail &amp; ajukan pengembalian &rarr;</a>
      </div>
    <?php endif; ?>

    <div class="stats">
      <div class="stat-card accent">
        <div class="label">Nama Lengkap</div>
        <div class="value"><?= e($siswa->nama_siswa) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">NIS</div>
        <div class="value"><?= e($siswa->nis) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Kelas</div>
        <div class="value"><?= e($siswa->kelas) ?></div>
      </div>
      <div class="stat-card">
        <div class="label">Username</div>
        <div class="value"><?= e($siswa->username) ?></div>
      </div>
    </div>
 
    <div class="grid-2">
      <div>
        <div class="panel">
          <div class="panel-head">
            <h2>Pengajuan Peminjaman Aktif</h2>
            <a href="peminjaman_buku.php" style="font-size:12.5px;color:var(--leather);text-decoration:none;font-weight:600;">Pinjam buku &rarr;</a>
          </div>
          <?php if (!empty($pengajuan_aktif)): ?>
          <table>
            <thead>
              <tr><th>Judul</th><th>Tgl Pengajuan</th><th>Batas Kembali</th><th>Status</th><th>Denda</th></tr>
            </thead>
            <tbody>
            <?php foreach ($pengajuan_aktif as $p): [$label, $kelas] = label_status_siswa($p->status); ?>
              <tr>
                <td>
                  <div class="book-title"><?= e($p->judul_buku) ?></div>
                  <div class="book-sub"><?= e($p->pengarang) ?></div>
                </td>
                <td><?= date('d M Y', strtotime($p->tgl_pinjam)) ?></td>
                <td><?= date('d M Y', strtotime($p->tgl_pengembalian)) ?></td>
                <td>
                  <span class="badge <?= $kelas ?>"><?= e($label) ?></span>
                  <?php if (($p->hari_terlambat ?? 0) > 0): ?>
                    <div style="margin-top:5px;"><span class="badge late">Terlambat <?= (int) ($p->hari_terlambat ?? 0) ?> hari</span></div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (($p->denda ?? 0) > 0): ?>
                    <span class="denda">Rp<?= number_format($p->denda ?? 0, 0, ',', '.') ?></span>
                    <div class="book-sub"><?= $p->status === 'menunggu_pengembalian' ? 'Dihitung sampai tgl pengajuan kembali' : 'Bertambah setiap hari' ?></div>
                  <?php else: ?>
                    &mdash;
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
          <div class="empty-state">
            <div class="icon">📚</div>
            Belum ada buku yang sedang dipinjam.<br>
            <a href="peminjaman_buku.php" style="color:var(--leather);font-weight:600;">Ajukan peminjaman buku</a>
          </div>
          <?php endif; ?>
        </div>
      </div>
 
      <div>
        <div class="panel">
          <div class="panel-head">
            <h2>Informasi Akun</h2>
          </div>
          <div class="info-list">
            <div class="info-row"><span class="k">NIS</span><span class="v"><?= e($siswa->nis) ?></span></div>
            <div class="info-row"><span class="k">Nama</span><span class="v"><?= e($siswa->nama_siswa) ?></span></div>
            <div class="info-row"><span class="k">Username</span><span class="v"><?= e($siswa->username) ?></span></div>
            <div class="info-row"><span class="k">Kelas</span><span class="v"><?= e($siswa->kelas) ?></span></div>
            <div class="info-row"><span class="k">Status</span><span class="v"><span class="badge">Aktif</span></span></div>
          </div>
        </div>
 
        <div class="panel">
          <div class="panel-head">
            <h2>Pengumuman</h2>
          </div>
          <div style="font-size:13px;color:var(--ink-soft);line-height:1.7;">
            Ingin mengubah data akun (nama, kelas, atau username)? Hubungi petugas perpustakaan/admin sekolah.
          </div>
        </div>
      </div>
    </div>
  </main>
 
</body>
</html>
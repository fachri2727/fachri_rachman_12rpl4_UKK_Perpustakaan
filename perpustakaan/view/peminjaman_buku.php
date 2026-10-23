<?php
// ambil data dari controller (sekaligus cek login siswa)
include_once __DIR__ . '/../controller/c_pinjam.php';

function label_status($status)
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
<title>Pinjam Buku</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{
    --cream:#F3E9DA; --paper:#FBF5EA; --wood-dark:#2E2016; --wood:#4A3324;
    --leather:#8A5A3B; --clay:#B4703F; --ink:#33251A; --ink-soft:#6B5646;
    --line:#DCCBAF; --good:#5C7A54; --warn:#B4703F; --bad:#A2483A;
    box-sizing:border-box;
    padding-top:env(safe-area-inset-top,0px);
    padding-bottom:env(safe-area-inset-bottom,0px);
  }
  *{box-sizing:border-box;}
  html{height:100%;}
  body{margin:0;background:var(--cream);color:var(--ink);font-family:'Inter',sans-serif;display:flex;min-height:100%;}
  h1,h2,h3{font-family:'Fraunces',serif;}

  .sidebar{width:230px;flex-shrink:0;background:var(--wood-dark);color:var(--cream);padding:28px 20px;display:flex;flex-direction:column;gap:34px;}
  .brand{display:flex;align-items:center;gap:10px;}
  .brand-mark{width:34px;height:34px;border-radius:6px;background:linear-gradient(160deg,var(--leather),var(--clay));display:flex;align-items:center;justify-content:center;font-family:'Fraunces',serif;font-weight:600;font-size:17px;color:var(--paper);}
  .brand-name{font-family:'Fraunces',serif;font-size:17px;line-height:1.15;}
  .brand-name span{display:block;font-size:11px;color:#C9B79A;font-family:'Inter',sans-serif;margin-top:2px;}
  nav{display:flex;flex-direction:column;gap:3px;}
  .nav-item{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:8px;color:#D8C6AA;text-decoration:none;font-size:14px;cursor:pointer;border:1px solid transparent;}
  .nav-item:hover{background:#3C2A1C;}
  .nav-item.active{background:#4A3324;color:var(--paper);border-color:#5C3F29;}
  .nav-icon{width:16px;text-align:center;opacity:.85;}

  main{flex:1;padding:32px 40px 60px;max-width:1180px;}
  .panel{background:var(--paper);border:1px solid var(--line);border-radius:14px;padding:22px 24px;margin-bottom:22px;}
  .panel-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;}
  .panel-head h2{font-size:17px;margin:0;color:var(--wood);font-weight:500;}
  .panel-head p{margin:2px 0 0;font-size:13px;color:var(--ink-soft);}

  table{width:100%;border-collapse:collapse;font-size:13.5px;}
  thead th{text-align:left;font-weight:600;color:var(--ink-soft);font-size:11.5px;padding:0 10px 10px;border-bottom:1px solid var(--line);}
  tbody td{padding:12px 10px;border-bottom:1px solid #EEE2CE;vertical-align:middle;}
  tbody tr:last-child td{border-bottom:none;}
  .book-title{font-weight:600;color:var(--ink);}
  .book-sub{font-size:12px;color:var(--ink-soft);margin-top:2px;}
  .badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:600;}
  .badge.ok{background:#E4EADD;color:var(--good);}
  .badge.due{background:#F5E2CD;color:var(--warn);}
  .badge.late{background:#F1DAD4;color:var(--bad);}

  .btn{display:inline-block;padding:8px 16px;border:none;border-radius:7px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;cursor:pointer;background:var(--leather);}
  .btn:hover{opacity:.9;}
  .btn:disabled{background:#C9B79A;cursor:not-allowed;}
  .pesan-kosong{text-align:center;color:var(--ink-soft);font-style:italic;padding:24px !important;}

  .notice{background:#F5E2CD;border:1px solid #E5C9A4;color:var(--wood);border-radius:10px;padding:12px 16px;font-size:13.5px;margin-bottom:22px;line-height:1.6;}
  .notice.bad{background:#F1DAD4;border-color:#E6B8AE;}
  .notice a{color:var(--leather);font-weight:600;}
  .denda{color:var(--bad);font-weight:600;}

  [hidden]{display:none !important;}
  .cari-katalog{display:flex;flex-wrap:wrap;gap:12px;align-items:center;margin-bottom:14px;}
  .cari-katalog input[type="text"]{flex:1;min-width:240px;padding:10px 12px;border:1px solid var(--line);border-radius:8px;background:var(--cream);font-size:14px;color:var(--ink);font-family:'Inter',sans-serif;}
  .cari-katalog input[type="text"]:focus{outline:none;border-color:var(--leather);background:#fff;}
  .cek{display:flex;align-items:center;gap:7px;font-size:13px;color:var(--ink-soft);cursor:pointer;white-space:nowrap;}
  .info-cari{font-size:12.5px;color:var(--ink-soft);margin:0 0 10px;}

  /* Sidebar tetap di tempat saat halaman di-scroll (tidak ikut terbawa naik) */
  @media (min-width:921px){
    .sidebar{position:sticky;top:0;align-self:flex-start;height:100vh;overflow-y:auto;}
  }

  @media (max-width:920px){
    body{flex-direction:column;}
    .sidebar{width:100%;flex-direction:row;align-items:center;padding:16px 20px;overflow-x:auto;}
    nav{flex-direction:row;}
    main{padding:24px 18px 40px;}
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
      <a href="dashboard_siswa.php" class="nav-item"><span class="nav-icon">⌂</span> Dashboard</a>
      <a href="peminjaman_buku.php" class="nav-item active"><span class="nav-icon">📖</span> Pinjam Buku</a>
      <a href="../controller/logout.php" class="nav-item" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><span class="nav-icon">⏻</span> Keluar</a>
    </nav>
  </aside>

  <main>
    <?php if ($total_denda > 0): ?>
      <div class="notice bad">
        Kamu punya denda berjalan <strong>Rp<?= number_format($total_denda, 0, ',', '.') ?></strong>.
        Denda bertambah Rp<?= number_format(m_peminjaman::TARIF_DENDA_PER_HARI, 0, ',', '.') ?> setiap hari sampai kamu menekan
        <strong>Ajukan Kembali</strong>, jadi segera kembalikan bukunya.
      </div>
    <?php endif; ?>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Pengajuan Peminjaman Saya</h2>
          <p>Maksimal <?= m_peminjaman::MAKS_PINJAM_AKTIF ?> buku aktif sekaligus (menunggu + disetujui). Denda keterlambatan Rp<?= number_format(m_peminjaman::TARIF_DENDA_PER_HARI, 0, ',', '.') ?> per hari.</p>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Judul</th><th>Tgl Pengajuan</th><th>Batas Kembali</th><th>Status</th><th>Denda</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($pengajuan_saya)): foreach ($pengajuan_saya as $p):
            [$label, $kelas] = label_status($p->status); ?>
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
            <td>
              <?php if ($p->status === 'disetujui'): ?>
                <form action="../controller/c_pinjam.php?aksi=kembali" method="POST"
                      onsubmit="return confirm('Ajukan pengembalian buku ini?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id_peminjaman" value="<?= (int) $p->id_peminjaman ?>">
                  <button type="submit" class="btn">Ajukan Kembali</button>
                </form>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="6" class="pesan-kosong">Belum ada pengajuan peminjaman</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <div class="panel-head">
        <div>
          <h2>Riwayat Peminjaman</h2>
          <p>10 terakhir yang sudah dikembalikan atau ditolak</p>
        </div>
      </div>
      <table>
        <thead>
          <tr><th>Judul</th><th>Tgl Pengajuan</th><th>Tgl Dikembalikan</th><th>Status</th><th>Denda</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($riwayat_saya)): foreach ($riwayat_saya as $r):
            [$label, $kelas] = label_status($r->status); ?>
          <tr>
            <td>
              <div class="book-title"><?= e($r->judul_buku) ?></div>
              <div class="book-sub"><?= e($r->pengarang) ?></div>
            </td>
            <td><?= date('d M Y', strtotime($r->tgl_pinjam)) ?></td>
            <td><?= !empty($r->tgl_dikembalikan) ? date('d M Y', strtotime($r->tgl_dikembalikan)) : '&mdash;' ?></td>
            <td><span class="badge <?= $kelas ?>"><?= e($label) ?></span></td>
            <td>
              <?php if ($r->status !== 'dikembalikan'): ?>
                &mdash;
              <?php elseif ((int) ($r->denda ?? 0) > 0): ?>
                <span class="denda">Rp<?= number_format((int) ($r->denda ?? 0), 0, ',', '.') ?></span>
              <?php else: ?>
                Tidak ada
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="pesan-kosong">Belum ada riwayat peminjaman</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="panel">
      <div class="panel-head">
        <h2>Katalog Buku</h2>
      </div>

      <div class="cari-katalog">
        <input type="text" id="cari-buku" placeholder="Cari judul, pengarang, penerbit, atau tahun…" maxlength="100" autocomplete="off">
        <label class="cek"><input type="checkbox" id="hanya-tersedia"> Tersedia saja</label>
      </div>
      <p class="info-cari" id="info-cari" hidden></p>

      <table id="tabel-katalog">
        <thead>
          <tr><th>Judul</th><th>Penerbit</th><th>Tahun</th><th>Status</th><th>Aksi</th></tr>
        </thead>
        <tbody>
        <?php if (!empty($daftar_buku)): foreach ($daftar_buku as $b): ?>
          <tr data-cari="<?= e($b->judul_buku . ' ' . $b->pengarang . ' ' . $b->penerbit . ' ' . $b->tahun_terbit) ?>"
              data-tersedia="<?= $b->tersedia > 0 ? '1' : '0' ?>">
            <td>
              <div class="book-title"><?= e($b->judul_buku) ?></div>
              <div class="book-sub"><?= e($b->pengarang) ?></div>
            </td>
            <td><?= e($b->penerbit) ?></td>
            <td><?= e($b->tahun_terbit) ?></td>
            <td>
              <?php if ($b->tersedia > 0): ?>
                <span class="badge ok">Tersedia <?= (int) $b->tersedia ?> dari <?= (int) $b->stok ?></span>
              <?php else: ?>
                <span class="badge late">Habis (<?= (int) $b->stok ?> dipinjam)</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (in_array((int) $b->id_buku, $buku_saya, true)): ?>
                <button class="btn" disabled>Sedang Kamu Pinjam</button>
              <?php elseif ($b->tersedia > 0): ?>
                <form action="../controller/c_pinjam.php?aksi=ajukan" method="POST"
                      onsubmit="return confirm('Ajukan peminjaman buku ini?')">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id_buku" value="<?= (int) $b->id_buku ?>">
                  <button type="submit" class="btn">Ajukan Pinjam</button>
                </form>
              <?php else: ?>
                <button class="btn" disabled>Tidak Tersedia</button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr><td colspan="5" class="pesan-kosong">Belum ada data buku</td></tr>
        <?php endif; ?>
          <tr id="baris-kosong" hidden><td colspan="5" class="pesan-kosong">Tidak ada buku yang cocok dengan pencarian</td></tr>
        </tbody>
      </table>
    </div>
  </main>

<script>
  // Pencarian katalog langsung di browser (tanpa reload): semua kata yang diketik harus cocok
  (function () {
    var kotak  = document.getElementById('cari-buku');
    var cek    = document.getElementById('hanya-tersedia');
    var baris  = document.querySelectorAll('#tabel-katalog tbody tr[data-cari]');
    var kosong = document.getElementById('baris-kosong');
    var info   = document.getElementById('info-cari');

    function saring() {
      var kata   = kotak.value.trim().toLowerCase();
      var daftar = kata === '' ? [] : kata.split(/\s+/);
      var hanya  = cek.checked;
      var tampil = 0;

      for (var i = 0; i < baris.length; i++) {
        var teks  = baris[i].getAttribute('data-cari').toLowerCase();
        var cocok = daftar.every(function (k) { return teks.indexOf(k) > -1; });
        if (hanya && baris[i].getAttribute('data-tersedia') !== '1') { cocok = false; }
        baris[i].hidden = !cocok;
        if (cocok) { tampil++; }
      }

      kosong.hidden = (tampil > 0 || baris.length === 0);
      if (kata === '' && !hanya) {
        info.hidden = true;
      } else {
        info.hidden = false;
        info.textContent = 'Menampilkan ' + tampil + ' dari ' + baris.length + ' buku';
      }
    }

    kotak.addEventListener('input', saring);
    cek.addEventListener('change', saring);
  })();
</script>

</body>
</html>
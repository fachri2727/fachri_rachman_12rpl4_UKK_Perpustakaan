<?php
include_once __DIR__ . '/koneksi.php';

class m_peminjaman
{
    private $db;

    // Lama pinjam (hari) sejak tanggal pengajuan, dipakai sebagai batas kembali
    const LAMA_PINJAM_HARI = 7;

    // Maksimal peminjaman aktif per siswa (menunggu + disetujui + menunggu pengembalian)
    const MAKS_PINJAM_AKTIF = 3;

    // Denda per hari keterlambatan (Rupiah). Dihitung dari batas kembali sampai tanggal siswa
    // MENGAJUKAN kembali (bukan tanggal admin mengonfirmasi), jadi siswa tidak dirugikan kalau admin telat memproses.
    const TARIF_DENDA_PER_HARI = 1000;

    public function __construct()
    {
        $koneksi  = new koneksi();
        $this->db = $koneksi->koneksi;
    }

    // ==========================================================
    //  SISI SISWA
    // ==========================================================

    // Hitung peminjaman siswa yang masih aktif
    public function jumlah_aktif($id_siswa)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM tb_peminjaman
             WHERE id_siswa = ? AND status IN ('menunggu', 'disetujui', 'menunggu_pengembalian')"
        );
        $stmt->bind_param("i", $id_siswa);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $hasil['total'];
    }

    // Siswa mengajukan peminjaman. Mengembalikan ['ok' => bool, 'pesan' => string].
    // Satu judul punya beberapa eksemplar (kolom stok): buku bisa dipinjam banyak siswa
    // selama masih ada sisa eksemplar. Semuanya dijalankan dalam SATU transaksi dengan baris
    // buku dikunci, jadi dua siswa yang mengajukan buku yang sama bersamaan diproses satu per
    // satu dan stok tidak bisa terlewati.
    public function ajukan($id_siswa, $id_buku)
    {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT stok FROM tb_buku WHERE id_buku = ? FOR UPDATE");
            $stmt->bind_param("i", $id_buku);
            $stmt->execute();
            $buku = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($buku === null) {
                $this->db->rollback();
                return $this->hasil(false, 'Buku tidak ditemukan');
            }
            if ($this->jumlah_dipinjam_buku($id_buku) >= (int) $buku['stok']) {
                $this->db->rollback();
                return $this->hasil(false, 'Semua eksemplar buku ini sedang dipinjam');
            }
            if ($this->siswa_punya_buku($id_siswa, $id_buku)) {
                $this->db->rollback();
                return $this->hasil(false, 'Kamu sudah meminjam atau mengajukan buku ini');
            }
            if ($this->jumlah_aktif($id_siswa) >= self::MAKS_PINJAM_AKTIF) {
                $this->db->rollback();
                return $this->hasil(false, 'Kamu sudah mencapai batas maksimal ' . self::MAKS_PINJAM_AKTIF . ' buku pinjaman aktif');
            }

            $tgl_pinjam       = date('Y-m-d H:i:s');
            $tgl_pengembalian = date('Y-m-d H:i:s', strtotime('+' . self::LAMA_PINJAM_HARI . ' days'));

            $stmt = $this->db->prepare(
                "INSERT INTO tb_peminjaman (id_siswa, id_buku, tgl_pinjam, tgl_pengembalian, status)
                 VALUES (?, ?, ?, ?, 'menunggu')"
            );
            $stmt->bind_param("iiss", $id_siswa, $id_buku, $tgl_pinjam, $tgl_pengembalian);
            $stmt->execute();
            $stmt->close();

            // Status buku menjadi 'dipinjam' hanya kalau eksemplar terakhir ikut terpakai
            $this->sinkron_status_buku($id_buku);

            $this->db->commit();
            return $this->hasil(true, 'Pengajuan peminjaman berhasil dikirim, menunggu konfirmasi admin');
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Siswa mengajukan pengembalian (status harus 'disetujui' dan milik siswa itu sendiri).
    // Tanggal pengajuan disimpan: denda berhenti bertambah di tanggal ini.
    public function ajukan_kembali($id_peminjaman, $id_siswa)
    {
        $sekarang = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "UPDATE tb_peminjaman SET status = 'menunggu_pengembalian', tgl_ajukan_kembali = ?
             WHERE id_peminjaman = ? AND id_siswa = ? AND status = 'disetujui'"
        );
        $stmt->bind_param("sii", $sekarang, $id_peminjaman, $id_siswa);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        return $ok
            ? $this->hasil(true, 'Pengajuan pengembalian berhasil dikirim, menunggu konfirmasi admin')
            : $this->hasil(false, 'Pengajuan pengembalian gagal (data tidak ditemukan atau sudah diproses)');
    }

    // Daftar peminjaman aktif milik satu siswa (halaman siswa dan dashboard), lengkap dengan
    // hari terlambat & denda. Denda hanya dihitung untuk buku yang sudah disetujui:
    //  - 'disetujui'             : sampai HARI INI (terus bertambah)
    //  - 'menunggu_pengembalian' : sampai tanggal siswa mengajukan kembali (sudah tidak bertambah)
    public function aktif_siswa($id_siswa)
    {
        $sql = "SELECT p.id_peminjaman, p.tgl_pinjam, p.tgl_pengembalian, p.status, p.tgl_ajukan_kembali, p.id_buku,
                       b.judul_buku, b.pengarang
                FROM tb_peminjaman p
                JOIN tb_buku b ON b.id_buku = p.id_buku
                WHERE p.id_siswa = ? AND p.status IN ('menunggu', 'disetujui', 'menunggu_pengembalian')
                ORDER BY p.tgl_pinjam DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_siswa);
        $stmt->execute();

        $data  = [];
        $hasil = $stmt->get_result();
        while ($baris = $hasil->fetch_object()) {
            $baris->hari_terlambat = 0;
            $baris->denda          = 0;
            if ($baris->status === 'disetujui' || $baris->status === 'menunggu_pengembalian') {
                $baris->hari_terlambat = $this->hitung_hari_terlambat($baris->tgl_pengembalian, $baris->tgl_ajukan_kembali);
                $baris->denda          = $baris->hari_terlambat * self::TARIF_DENDA_PER_HARI;
            }
            $data[] = $baris;
        }
        $stmt->close();
        return $data;
    }

    // Riwayat peminjaman siswa yang sudah selesai (dikembalikan) atau ditolak, lengkap dengan denda
    public function riwayat_siswa($id_siswa, $batas = 20)
    {
        $sql = "SELECT p.id_peminjaman, p.tgl_pinjam, p.tgl_pengembalian, p.status,
                       b.judul_buku, b.pengarang,
                       g.tgl_pengembalian AS tgl_dikembalikan, g.denda
                FROM tb_peminjaman p
                JOIN tb_buku b ON b.id_buku = p.id_buku
                LEFT JOIN tb_pengembalian g ON g.id_peminjaman = p.id_peminjaman
                WHERE p.id_siswa = ? AND p.status IN ('dikembalikan', 'ditolak')
                ORDER BY p.tgl_pinjam DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $id_siswa, $batas);
        $stmt->execute();

        $data  = [];
        $hasil = $stmt->get_result();
        while ($baris = $hasil->fetch_object()) {
            $data[] = $baris;
        }
        $stmt->close();
        return $data;
    }

    // ==========================================================
    //  SISI ADMIN
    // ==========================================================

    // Pengajuan peminjaman yang masih menunggu konfirmasi
    public function daftar_menunggu()
    {
        $sql = "SELECT p.id_peminjaman, p.tgl_pinjam, p.tgl_pengembalian,
                       s.nama_siswa, s.nis,
                       b.judul_buku
                FROM tb_peminjaman p
                JOIN tb_siswa s ON s.id_siswa = p.id_siswa
                JOIN tb_buku  b ON b.id_buku  = p.id_buku
                WHERE p.status = 'menunggu'
                ORDER BY p.tgl_pinjam ASC";
        $query = $this->db->query($sql);

        $data = [];
        while ($baris = $query->fetch_object()) {
            $data[] = $baris;
        }
        return $data;
    }

    // Pengajuan pengembalian yang masih menunggu konfirmasi.
    // Hari terlambat dihitung sampai tanggal siswa mengajukan kembali.
    public function daftar_menunggu_kembali()
    {
        $sql = "SELECT p.id_peminjaman, p.id_buku, p.tgl_pinjam, p.tgl_pengembalian, p.tgl_ajukan_kembali,
                       s.nama_siswa, s.nis,
                       b.judul_buku
                FROM tb_peminjaman p
                JOIN tb_siswa s ON s.id_siswa = p.id_siswa
                JOIN tb_buku  b ON b.id_buku  = p.id_buku
                WHERE p.status = 'menunggu_pengembalian'
                ORDER BY p.tgl_ajukan_kembali ASC, p.tgl_pengembalian ASC";
        $query = $this->db->query($sql);

        $data = [];
        while ($baris = $query->fetch_object()) {
            $hari_terlambat        = $this->hitung_hari_terlambat($baris->tgl_pengembalian, $baris->tgl_ajukan_kembali);
            $baris->hari_terlambat = $hari_terlambat;
            $baris->estimasi_denda = $hari_terlambat * self::TARIF_DENDA_PER_HARI;
            $data[] = $baris;
        }
        return $data;
    }

    // Buku yang sedang dipinjam (sudah disetujui, belum dikembalikan), yang paling terlambat di atas.
    // Denda berjalan dihitung sampai HARI INI.
    public function daftar_dipinjam()
    {
        $sql = "SELECT p.id_peminjaman, p.tgl_pinjam, p.tgl_pengembalian,
                       s.nama_siswa, s.nis,
                       b.judul_buku
                FROM tb_peminjaman p
                JOIN tb_siswa s ON s.id_siswa = p.id_siswa
                JOIN tb_buku  b ON b.id_buku  = p.id_buku
                WHERE p.status = 'disetujui'
                ORDER BY p.tgl_pengembalian ASC";
        $query = $this->db->query($sql);

        $data = [];
        while ($baris = $query->fetch_object()) {
            $baris->hari_terlambat = $this->hitung_hari_terlambat($baris->tgl_pengembalian);
            $baris->denda_berjalan = $baris->hari_terlambat * self::TARIF_DENDA_PER_HARI;
            $data[] = $baris;
        }
        return $data;
    }

    // Riwayat yang sudah selesai (ditolak / dikembalikan), lengkap dengan tanggal kembali & denda
    public function daftar_riwayat($batas = 30)
    {
        $sql = "SELECT p.id_peminjaman, p.tgl_pinjam, p.tgl_pengembalian, p.status,
                       s.nama_siswa, s.nis,
                       b.judul_buku,
                       g.tgl_pengembalian AS tgl_dikembalikan, g.denda
                FROM tb_peminjaman p
                JOIN tb_siswa s ON s.id_siswa = p.id_siswa
                JOIN tb_buku  b ON b.id_buku  = p.id_buku
                LEFT JOIN tb_pengembalian g ON g.id_peminjaman = p.id_peminjaman
                WHERE p.status IN ('ditolak', 'dikembalikan')
                ORDER BY p.tgl_pinjam DESC
                LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $batas);
        $stmt->execute();

        $data  = [];
        $hasil = $stmt->get_result();
        while ($baris = $hasil->fetch_object()) {
            $data[] = $baris;
        }
        $stmt->close();
        return $data;
    }

    public function ambil_satu($id_peminjaman)
    {
        $stmt = $this->db->prepare("SELECT * FROM tb_peminjaman WHERE id_peminjaman = ? LIMIT 1");
        $stmt->bind_param("i", $id_peminjaman);
        $stmt->execute();
        $hasil = $stmt->get_result();
        $stmt->close();

        return ($hasil && $hasil->num_rows > 0) ? $hasil->fetch_object() : null;
    }

    // Admin MENYETUJUI pengajuan peminjaman (buku tetap berstatus 'dipinjam')
    public function setujui($id_peminjaman)
    {
        $stmt = $this->db->prepare("UPDATE tb_peminjaman SET status = 'disetujui' WHERE id_peminjaman = ? AND status = 'menunggu'");
        $stmt->bind_param("i", $id_peminjaman);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        return $ok
            ? $this->hasil(true, 'Peminjaman disetujui')
            : $this->hasil(false, 'Peminjaman gagal disetujui (mungkin sudah diproses)');
    }

    // Admin MENOLAK pengajuan peminjaman -> buku otomatis tersedia lagi
    public function tolak($id_peminjaman)
    {
        $this->db->begin_transaction();
        try {
            $p = $this->ambil_terkunci($id_peminjaman);
            if ($p === null || $p['status'] !== 'menunggu') {
                $this->db->rollback();
                return $this->hasil(false, 'Peminjaman gagal ditolak (mungkin sudah diproses)');
            }

            $stmt = $this->db->prepare("UPDATE tb_peminjaman SET status = 'ditolak' WHERE id_peminjaman = ?");
            $stmt->bind_param("i", $id_peminjaman);
            $stmt->execute();
            $stmt->close();

            $this->sinkron_status_buku((int) $p['id_buku']);

            $this->db->commit();
            return $this->hasil(true, 'Peminjaman ditolak');
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Admin MENOLAK pengajuan pengembalian -> kembali ke 'disetujui' (buku masih dianggap dipinjam,
    // tanggal pengajuan dihapus sehingga denda kembali dihitung sampai hari ini)
    public function tolak_kembali($id_peminjaman)
    {
        $stmt = $this->db->prepare(
            "UPDATE tb_peminjaman SET status = 'disetujui', tgl_ajukan_kembali = NULL
             WHERE id_peminjaman = ? AND status = 'menunggu_pengembalian'"
        );
        $stmt->bind_param("i", $id_peminjaman);
        $stmt->execute();
        $ok = $stmt->affected_rows > 0;
        $stmt->close();

        return $ok
            ? $this->hasil(true, 'Pengajuan pengembalian ditolak, buku dianggap masih dipinjam')
            : $this->hasil(false, 'Gagal menolak (mungkin sudah diproses)');
    }

    // Admin MENGONFIRMASI pengajuan pengembalian dari siswa.
    // Denda dihitung sampai tanggal siswa mengajukan kembali (bukan tanggal konfirmasi ini).
    public function konfirmasi_kembali($id_peminjaman)
    {
        return $this->proses_kembali($id_peminjaman, 'menunggu_pengembalian');
    }

    // Admin langsung menandai buku sudah kembali, tanpa menunggu siswa mengajukan
    // (misalnya siswa menyerahkan bukunya langsung ke petugas). Denda dihitung sampai HARI INI.
    public function kembalikan_langsung($id_peminjaman)
    {
        return $this->proses_kembali($id_peminjaman, 'disetujui');
    }

    // Proses pengembalian: hitung denda, catat ke tb_pengembalian, status jadi 'dikembalikan',
    // buku tersedia lagi - semuanya satu transaksi. $status_asal = status yang harus dimiliki saat ini.
    private function proses_kembali($id_peminjaman, $status_asal)
    {
        $this->db->begin_transaction();
        try {
            $p = $this->ambil_terkunci($id_peminjaman);
            if ($p === null || $p['status'] !== $status_asal) {
                $this->db->rollback();
                return $this->hasil(false, 'Pengembalian gagal diproses (mungkin sudah diproses sebelumnya)');
            }

            // Tanggal buku dianggap kembali = tanggal siswa mengajukan kembali,
            // atau hari ini kalau tidak ada (pengembalian langsung / data lama)
            $tgl_dikembalikan = !empty($p['tgl_ajukan_kembali']) ? $p['tgl_ajukan_kembali'] : date('Y-m-d H:i:s');
            $hari_terlambat   = $this->hitung_hari_terlambat($p['tgl_pengembalian'], $tgl_dikembalikan);
            $denda            = $hari_terlambat * self::TARIF_DENDA_PER_HARI;

            $stmt = $this->db->prepare(
                "INSERT INTO tb_pengembalian (id_peminjaman, tgl_pengembalian, denda) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("isi", $id_peminjaman, $tgl_dikembalikan, $denda);
            $stmt->execute();
            $stmt->close();

            $stmt = $this->db->prepare("UPDATE tb_peminjaman SET status = 'dikembalikan' WHERE id_peminjaman = ?");
            $stmt->bind_param("i", $id_peminjaman);
            $stmt->execute();
            $stmt->close();

            $this->sinkron_status_buku((int) $p['id_buku']);

            $this->db->commit();

            $pesan = 'Pengembalian dikonfirmasi';
            if ($denda > 0) {
                $pesan .= ', terlambat ' . $hari_terlambat . ' hari, denda Rp' . number_format($denda, 0, ',', '.');
            } else {
                $pesan .= ', tidak ada denda';
            }
            return $this->hasil(true, $pesan);
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // ==========================================================
    //  HELPER (private)
    // ==========================================================

    private function hasil($ok, $pesan)
    {
        return ['ok' => $ok, 'pesan' => $pesan];
    }

    // Ambil satu baris peminjaman dan kunci selama transaksi berjalan
    private function ambil_terkunci($id_peminjaman)
    {
        $stmt = $this->db->prepare(
            "SELECT id_buku, tgl_pengembalian, status, tgl_ajukan_kembali
             FROM tb_peminjaman WHERE id_peminjaman = ? FOR UPDATE"
        );
        $stmt->bind_param("i", $id_peminjaman);
        $stmt->execute();
        $baris = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $baris; // null kalau tidak ada
    }

    // Samakan kolom status buku dengan stok:
    // 'tersedia' kalau masih ada eksemplar yang bisa dipinjam, 'dipinjam' kalau semuanya sudah terpakai.
    // Dipanggil lewat koneksi yang sama (ikut dalam transaksi) SETELAH status peminjaman diubah.
    private function sinkron_status_buku($id_buku)
    {
        $dipinjam = $this->jumlah_dipinjam_buku($id_buku);
        $stmt = $this->db->prepare(
            "UPDATE tb_buku SET status = IF(stok - ? > 0, 'tersedia', 'dipinjam') WHERE id_buku = ?"
        );
        $stmt->bind_param("ii", $dipinjam, $id_buku);
        $stmt->execute();
        $stmt->close();
    }

    // Jumlah eksemplar satu buku yang sedang dipakai (diajukan / dipinjam / menunggu pengembalian)
    private function jumlah_dipinjam_buku($id_buku)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM tb_peminjaman
             WHERE id_buku = ? AND status IN ('menunggu', 'disetujui', 'menunggu_pengembalian')"
        );
        $stmt->bind_param("i", $id_buku);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $hasil['total'];
    }

    // Apakah siswa ini sedang memegang / mengajukan buku yang sama? (satu eksemplar per judul per siswa)
    private function siswa_punya_buku($id_siswa, $id_buku)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM tb_peminjaman
             WHERE id_siswa = ? AND id_buku = ? AND status IN ('menunggu', 'disetujui', 'menunggu_pengembalian')"
        );
        $stmt->bind_param("ii", $id_siswa, $id_buku);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $hasil['total'] > 0;
    }

    // Hari terlambat = selisih tanggal batas kembali sampai tanggal acuan.
    // $tgl_acuan kosong = hari ini. Hasil 0 kalau belum/tidak terlambat.
    private function hitung_hari_terlambat($tgl_batas_kembali, $tgl_acuan = null)
    {
        $batas  = new DateTime(date('Y-m-d', strtotime($tgl_batas_kembali)));
        $acuan  = empty($tgl_acuan) ? time() : strtotime($tgl_acuan);
        $sampai = new DateTime(date('Y-m-d', $acuan));
        return $sampai > $batas ? (int) $batas->diff($sampai)->days : 0;
    }
}
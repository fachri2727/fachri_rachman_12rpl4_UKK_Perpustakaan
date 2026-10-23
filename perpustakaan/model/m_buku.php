<?php
include_once __DIR__ . '/koneksi.php';

class m_buku
{
    private $db;

    // Status peminjaman yang masih "memakai" satu eksemplar buku
    const STATUS_AKTIF = "'menunggu', 'disetujui', 'menunggu_pengembalian'";

    public function __construct()
    {
        $koneksi  = new koneksi();
        $this->db = $koneksi->koneksi;
    }

    // Tampilkan semua data, atau hanya yang cocok dengan kata pencarian $cari
    // (dicari di: judul, pengarang, penerbit, tahun, status).
    // Tiap buku membawa: stok (total eksemplar), dipinjam (sedang dipinjam/diajukan), tersedia (sisa).
    public function tampil_data($cari = '')
    {
        $sql  = "SELECT b.id_buku, b.judul_buku, b.pengarang, b.penerbit, b.tahun_terbit, b.stok, b.status,
                        (SELECT COUNT(*) FROM tb_peminjaman p
                          WHERE p.id_buku = b.id_buku AND p.status IN (" . self::STATUS_AKTIF . ")) AS dipinjam
                 FROM tb_buku b";
        $kata = '';
        if ($cari !== '') {
            $sql .= " WHERE b.judul_buku LIKE ? OR b.pengarang LIKE ? OR b.penerbit LIKE ? OR b.tahun_terbit LIKE ? OR b.status LIKE ?";
            // Escape karakter khusus LIKE supaya "%" dan "_" yang diketik dicari apa adanya
            $kata = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari) . '%';
        }
        $sql .= " ORDER BY b.judul_buku ASC";

        $stmt = $this->db->prepare($sql);
        if ($cari !== '') {
            $stmt->bind_param("sssss", $kata, $kata, $kata, $kata, $kata);
        }
        $stmt->execute();
        $hasil = $stmt->get_result();

        $data = [];
        while ($baris = $hasil->fetch_object()) {
            $data[] = $this->lengkapi($baris);
        }
        $stmt->close();
        return $data;
    }

    // Ambil satu buku berdasarkan id (untuk form edit); null jika tidak ada
    public function ambil_satu($id_buku)
    {
        $sql  = "SELECT b.id_buku, b.judul_buku, b.pengarang, b.penerbit, b.tahun_terbit, b.stok, b.status,
                        (SELECT COUNT(*) FROM tb_peminjaman p
                          WHERE p.id_buku = b.id_buku AND p.status IN (" . self::STATUS_AKTIF . ")) AS dipinjam
                 FROM tb_buku b WHERE b.id_buku = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $id_buku);
        $stmt->execute();
        $hasil = $stmt->get_result();
        $stmt->close();

        return ($hasil && $hasil->num_rows > 0) ? $this->lengkapi($hasil->fetch_object()) : null;
    }

    // Buku baru: semua eksemplar tersedia
    public function tambah_data($judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok)
    {
        $sql  = "INSERT INTO tb_buku (judul_buku, pengarang, penerbit, tahun_terbit, stok, status)
                 VALUES (?, ?, ?, ?, ?, 'tersedia')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssii", $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok);
        return $stmt->execute();
    }

    // Ubah data buku. Stok tidak boleh lebih kecil dari jumlah eksemplar yang sedang dipinjam/diajukan.
    // Mengembalikan ['ok' => bool, 'pesan' => string].
    public function update_data($id_buku, $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok)
    {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT id_buku FROM tb_buku WHERE id_buku = ? FOR UPDATE");
            $stmt->bind_param("i", $id_buku);
            $stmt->execute();
            $ada = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($ada === null) {
                $this->db->rollback();
                return ['ok' => false, 'pesan' => 'Data tidak ditemukan'];
            }

            $dipinjam = $this->hitung_dipinjam($id_buku);
            if ($stok < $dipinjam) {
                $this->db->rollback();
                return ['ok' => false, 'pesan' => 'Stok tidak boleh kurang dari jumlah eksemplar yang sedang dipinjam (' . $dipinjam . ')'];
            }

            // status mengikuti sisa stok
            $status = ($stok - $dipinjam) > 0 ? 'tersedia' : 'dipinjam';

            $stmt = $this->db->prepare(
                "UPDATE tb_buku SET judul_buku = ?, pengarang = ?, penerbit = ?, tahun_terbit = ?, stok = ?, status = ?
                 WHERE id_buku = ?"
            );
            $stmt->bind_param("sssiisi", $judul_buku, $pengarang, $penerbit, $tahun_terbit, $stok, $status, $id_buku);
            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            return ['ok' => true, 'pesan' => 'Data berhasil diubah'];
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Hapus buku, kecuali masih ada eksemplar yang dipinjam/diajukan.
    // Mengembalikan ['ok' => bool, 'pesan' => string].
    public function hapus_data($id_buku)
    {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare("SELECT id_buku FROM tb_buku WHERE id_buku = ? FOR UPDATE");
            $stmt->bind_param("i", $id_buku);
            $stmt->execute();
            $ada = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($ada === null) {
                $this->db->rollback();
                return ['ok' => false, 'pesan' => 'Data tidak ditemukan'];
            }
            if ($this->hitung_dipinjam($id_buku) > 0) {
                $this->db->rollback();
                return ['ok' => false, 'pesan' => 'Buku masih dipinjam atau diajukan siswa, tidak bisa dihapus'];
            }

            $stmt = $this->db->prepare("DELETE FROM tb_buku WHERE id_buku = ?");
            $stmt->bind_param("i", $id_buku);
            $stmt->execute();
            $stmt->close();

            $this->db->commit();
            return ['ok' => true, 'pesan' => 'Data berhasil dihapus'];
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    // Jumlah eksemplar buku yang sedang dipinjam / diajukan
    private function hitung_dipinjam($id_buku)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) AS total FROM tb_peminjaman WHERE id_buku = ? AND status IN (" . self::STATUS_AKTIF . ")"
        );
        $stmt->bind_param("i", $id_buku);
        $stmt->execute();
        $hasil = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return (int) $hasil['total'];
    }

    // Rapikan angka stok dan hitung sisa eksemplar yang bisa dipinjam
    private function lengkapi($buku)
    {
        $buku->stok     = (int) $buku->stok;
        $buku->dipinjam = (int) $buku->dipinjam;
        $buku->tersedia = max(0, $buku->stok - $buku->dipinjam);
        return $buku;
    }
}
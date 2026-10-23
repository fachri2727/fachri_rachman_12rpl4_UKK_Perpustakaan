<?php
include_once __DIR__ . '/koneksi.php';

class admin
{
    private $db;

    public function __construct()
    {
        $koneksi  = new koneksi();
        $this->db = $koneksi->koneksi;
    }

    // Ambil data admin berdasarkan username (dipakai untuk proses login)
    public function login($username)
    {
        $sql  = "SELECT * FROM tb_admin WHERE username = ? OR nama_admin = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $username, $username);
        $stmt->execute();

        $hasil = $stmt->get_result();

        return ($hasil && $hasil->num_rows > 0) ? $hasil->fetch_assoc() : null;
    }

    // Simpan ulang password sebagai hash (dipakai saat password lama masih teks biasa)
    public function ubah_hash_password($id_admin, $hash)
    {
        $stmt = $this->db->prepare("UPDATE tb_admin SET password = ? WHERE id_admin = ?");
        $stmt->bind_param("si", $hash, $id_admin);
        return $stmt->execute();
    }

    // Cek apakah username sudah dipakai admin lain (abaikan id tertentu saat edit)
    public function username_sudah_ada($username, $kecuali_id = 0)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM tb_admin WHERE username = ? AND id_admin <> ? LIMIT 1");
        $stmt->bind_param("si", $username, $kecuali_id);
        $stmt->execute();
        $hasil = $stmt->get_result();

        return $hasil && $hasil->num_rows > 0;
    }

    // Daftarkan admin baru. Password disimpan apa adanya (teks biasa)
    public function tambah_data($username, $nama_admin, $password)
    {
        $sql  = "INSERT INTO tb_admin (username, nama_admin, password) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sss", $username, $nama_admin, $password);
        return $stmt->execute();
    }

    // Tampilkan semua data, atau hanya yang cocok dengan kata pencarian $cari
    // (dicari di: nama, username)
    public function tampil_data($cari = '')
    {
        $sql  = "SELECT id_admin, username, nama_admin FROM tb_admin";
        $kata = '';
        if ($cari !== '') {
            $sql .= " WHERE nama_admin LIKE ? OR username LIKE ?";
            // Escape karakter khusus LIKE supaya "%" dan "_" yang diketik dicari apa adanya
            $kata = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari) . '%';
        }
        $sql .= " ORDER BY nama_admin ASC";

        $stmt = $this->db->prepare($sql);
        if ($cari !== '') {
            $stmt->bind_param("ss", $kata, $kata);
        }
        $stmt->execute();
        $hasil = $stmt->get_result();

        $data = [];
        while ($baris = $hasil->fetch_object()) {
            $data[] = $baris;
        }
        $stmt->close();
        return $data;
    }

    // Ambil satu admin berdasarkan id (untuk form edit); null jika tidak ada
    public function ambil_satu($id_admin)
    {
        $stmt = $this->db->prepare("SELECT id_admin, username, nama_admin FROM tb_admin WHERE id_admin = ? LIMIT 1");
        $stmt->bind_param("i", $id_admin);
        $stmt->execute();
        $hasil = $stmt->get_result();

        return ($hasil && $hasil->num_rows > 0) ? $hasil->fetch_object() : null;
    }

    // Hitung jumlah admin yang ada (dipakai supaya admin terakhir tidak bisa dihapus)
    public function jumlah_admin()
    {
        $hasil = $this->db->query("SELECT COUNT(*) AS total FROM tb_admin");
        $baris = $hasil->fetch_assoc();
        return (int) $baris['total'];
    }

    public function update_data($id_admin, $username, $nama_admin, $password)
    {
        $sql  = "UPDATE tb_admin SET username = ?, nama_admin = ?, password = ? WHERE id_admin = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssi", $username, $nama_admin, $password, $id_admin);
        return $stmt->execute();
    }

    public function update_data_tanpa_password($id_admin, $username, $nama_admin)
    {
        $sql  = "UPDATE tb_admin SET username = ?, nama_admin = ? WHERE id_admin = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssi", $username, $nama_admin, $id_admin);
        return $stmt->execute();
    }

    public function hapus_data($id_admin)
    {
        $stmt = $this->db->prepare("DELETE FROM tb_admin WHERE id_admin = ?");
        $stmt->bind_param("i", $id_admin);
        return $stmt->execute();
    }
}
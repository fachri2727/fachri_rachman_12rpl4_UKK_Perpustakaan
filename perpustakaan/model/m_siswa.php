<?php
include_once __DIR__ . '/koneksi.php';

class m_siswa
{
    private $db;

    public function __construct()
    {
        $koneksi  = new koneksi();
        $this->db = $koneksi->koneksi;
    }

    // Ambil siswa aktif berdasarkan username (dipakai untuk login)
    public function login($username)
    {
        $sql  = "SELECT * FROM tb_siswa WHERE username = ? AND status_aktif = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $hasil = $stmt->get_result();

        return ($hasil && $hasil->num_rows > 0) ? $hasil->fetch_assoc() : null;
    }

    // Simpan ulang password sebagai hash (dipakai saat password lama masih teks biasa)
    public function ubah_hash_password($id_siswa, $hash)
    {
        $stmt = $this->db->prepare("UPDATE tb_siswa SET password = ? WHERE id_siswa = ?");
        $stmt->bind_param("si", $hash, $id_siswa);
        return $stmt->execute();
    }

    // Tampilkan semua data, atau hanya yang cocok dengan kata pencarian $cari
    // (dicari di: NIS, nama, username, kelas)
    public function tampil_data($cari = '')
    {
        $sql  = "SELECT id_siswa, nis, nama_siswa, username, kelas, role FROM tb_siswa";
        $kata = '';
        if ($cari !== '') {
            $sql .= " WHERE nis LIKE ? OR nama_siswa LIKE ? OR username LIKE ? OR kelas LIKE ?";
            // Escape karakter khusus LIKE supaya "%" dan "_" yang diketik dicari apa adanya
            $kata = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $cari) . '%';
        }
        $sql .= " ORDER BY nama_siswa ASC";

        $stmt = $this->db->prepare($sql);
        if ($cari !== '') {
            $stmt->bind_param("ssss", $kata, $kata, $kata, $kata);
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

    // Ambil satu siswa berdasarkan id (untuk form edit); null jika tidak ada
    public function ambil_satu($id_siswa)
    {
        $stmt = $this->db->prepare("SELECT id_siswa, nis, nama_siswa, username, kelas, role FROM tb_siswa WHERE id_siswa = ? LIMIT 1");
        $stmt->bind_param("i", $id_siswa);
        $stmt->execute();
        $hasil = $stmt->get_result();

        return ($hasil && $hasil->num_rows > 0) ? $hasil->fetch_object() : null;
    }

    // Cek apakah username sudah dipakai (abaikan id tertentu saat edit)
    public function username_sudah_ada($username, $kecuali_id = 0)
    {
        $stmt = $this->db->prepare("SELECT 1 FROM tb_siswa WHERE username = ? AND id_siswa <> ? LIMIT 1");
        $stmt->bind_param("si", $username, $kecuali_id);
        $stmt->execute();
        $hasil = $stmt->get_result();

        return $hasil && $hasil->num_rows > 0;
    }

    public function tambah_data($nis, $nama_siswa, $username, $password, $kelas, $role = 'siswa')
    {
        $sql  = "INSERT INTO tb_siswa (nis, nama_siswa, username, password, kelas, role, status_aktif)
                 VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss", $nis, $nama_siswa, $username, $password, $kelas, $role);
        return $stmt->execute();
    }

    public function update_data($id_siswa, $nis, $nama_siswa, $username, $password, $kelas)
    {
        $sql  = "UPDATE tb_siswa SET nis = ?, nama_siswa = ?, username = ?, password = ?, kelas = ?
                 WHERE id_siswa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("sssssi", $nis, $nama_siswa, $username, $password, $kelas, $id_siswa);
        return $stmt->execute();
    }

    public function update_data_tanpa_password($id_siswa, $nis, $nama_siswa, $username, $kelas)
    {
        $sql  = "UPDATE tb_siswa SET nis = ?, nama_siswa = ?, username = ?, kelas = ?
                 WHERE id_siswa = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssi", $nis, $nama_siswa, $username, $kelas, $id_siswa);
        return $stmt->execute();
    }

    public function hapus_data($id_siswa)
    {
        $stmt = $this->db->prepare("DELETE FROM tb_siswa WHERE id_siswa = ?");
        $stmt->bind_param("i", $id_siswa);
        return $stmt->execute();
    }
}
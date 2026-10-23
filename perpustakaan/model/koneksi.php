<?php
// Zona waktu Indonesia (agar tanggal pinjam/denda tidak meleset)
date_default_timezone_set('Asia/Jakarta');

// Supaya SEMUA error SQL dilempar sebagai Exception (di PHP lama error SQL
// hanya mengembalikan false tanpa pesan, sehingga penyebabnya tidak kelihatan)
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class koneksi
{
    private $host = "sql305.infinityfree.com";
    private $username = "if0_43202897";
    private $password = "fachri12rpl4";
    private $database = "if0_43202897_perpustakaan";

    public $koneksi;

    public function __construct()
    {
        $this->koneksi = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->database
        );

        if ($this->koneksi->connect_error) {
            die("koneksi database gagal: " . $this->koneksi->connect_error);
        }

        $this->koneksi->set_charset("utf8mb4");
    }

}
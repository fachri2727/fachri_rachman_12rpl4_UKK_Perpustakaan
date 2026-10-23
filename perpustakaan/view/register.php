<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Siswa</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #F3E9DA;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 30px 0;
  }
  .login-box {
    background: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 320px;
  }
  .login-box h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #333;
  }
  .form-group {
    margin-bottom: 15px;
  }
  label {
    display: block;
    margin-bottom: 5px;
    font-size: 14px;
    color: #555;
  }
  input[type="text"],
  input[type="password"] {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    box-sizing: border-box;
  }
  button {
    width: 100%;
    padding: 10px;
    background: #5c490c;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 15px;
    cursor: pointer;
    margin-top: 10px;
  }
  button:hover {
    opacity: 0.9;
  }
  a.kembali {
    display: block;
    text-align: center;
    margin-top: 12px;
    font-size: 13px;
    color: #5c490c;
  }
</style>
</head>
<body>
  <div class="login-box">
    <h2>Daftar Akun Siswa</h2>
    <form action="../controller/c_siswa.php?aksi=registrasi" method="POST">
      <div class="form-group">
        <label for="nis">NIS</label>
        <input type="text" id="nis" name="nis" maxlength="20" required autofocus>
      </div>
      <div class="form-group">
        <label for="nama_siswa">Nama Lengkap</label>
        <input type="text" id="nama_siswa" name="nama_siswa" maxlength="100" required>
      </div>
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" maxlength="50"
               placeholder="huruf, angka, titik, atau underscore" required>
      </div>
      <div class="form-group">
        <label for="kelas">Kelas</label>
        <input type="text" id="kelas" name="kelas" maxlength="20" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" minlength="6" required>
      </div>
      <div class="form-group">
        <label for="konfirmasi">Konfirmasi Password</label>
        <input type="password" id="konfirmasi" name="konfirmasi" minlength="6" required>
      </div>
      <button type="submit">Daftar</button>
      <a class="kembali" href="login_siswa.php">Sudah punya akun? Login</a>
    </form>
  </div>
</body>
</html>
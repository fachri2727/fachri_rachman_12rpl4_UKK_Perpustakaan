<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Login Siswa</title>
<style>
  body {
    font-family: Arial, sans-serif;
    background: #F3E9DA;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
  }
  .login-box {
    background: #fff;
    padding: 65px 40px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    width: 300px;
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
    background: #1e56b3;
  }
</style>
</head>
<body>
  <div class="login-box">
    <h2>Login</h2>
    <form action="../controller/c_login_siswa.php" method="POST">
      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" placeholder="Masukkan Username" required autofocus>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
      </div>
      <button name="tombol" type="submit">Login</button>
      <a href="register.php" class="text-decoration-none">Daftar Akun Baru</a><br>
      <a href="login_admin.php" class="text-decoration-none">Login Sebagai Admin</a>
    </form>
  </div>
</body>
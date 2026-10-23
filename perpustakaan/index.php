<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perpustakaan</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,600;9..144,700&family=Inter:wght@400;500;600&display=swap');

  :root{
    --ink:#1f3326;
    --ink-soft:#3d5647;
    --paper:#f6f1e4;
    --paper-line:#e2d8bd;
    --card:#fffdf7;
    --gold-soft:#e8d9bb;
  }

  *{box-sizing:border-box;}
  html, body{ height:100%; }
  body{
    margin:0;
    background:var(--paper);
    background-image:linear-gradient(var(--paper-line) 1px, transparent 1px);
    background-size: 100% 2.35rem;
    background-position: 0 3rem;
    color:var(--ink);
    font-family:'Inter', sans-serif;
    display:flex;
    align-items:center;
    justify-content:center;
    padding: 2rem 1.5rem;
  }

  .panel{
    max-width: 560px;
    width:100%;
    background:var(--card);
    border:1.5px solid var(--ink);
    border-radius:6px;
    padding: 3rem 2.5rem;
    text-align:center;
  }

  .lambang{
    width:56px;
    height:56px;
    margin:0 auto 1.4rem;
    border:1.5px solid var(--ink);
    border-radius:50%;
    display:flex;
    align-items:center;
    justify-content:center;
    font-family:'Fraunces', serif;
    font-size:1.5rem;
    font-weight:700;
    background: var(--gold-soft);
  }

  .eyebrow{
    font-size:0.85rem;
    color: var(--ink-soft);
    margin: 0 0 0.5rem;
  }

  h1{
    font-family:'Fraunces', serif;
    font-size: 2.4rem;
    font-weight:700;
    margin: 0 0 0.8rem;
    line-height:1.15;
  }

  p.desk{
    color: var(--ink-soft);
    font-size:1rem;
    line-height:1.6;
    max-width: 420px;
    margin: 0 auto 1.8rem;
  }

  .btn{
    border:1.5px solid var(--ink);
    background:var(--ink);
    color:var(--paper);
    padding:0.75rem 1.4rem;
    border-radius:3px;
    font-weight:600;
    font-size:0.92rem;
    text-decoration:none;
    display:inline-block;
    transition: transform 0.12s ease, background 0.12s ease;
  }
  .btn:hover{ background: var(--ink-soft); }
  .btn:active{ transform: scale(0.97); }

  @media (max-width: 480px){
    .panel{ padding: 2.2rem 1.5rem; }
    h1{ font-size: 1.9rem; }
  }
</style>
</head>
<body>
  <div class="panel">
    <div class="lambang">P</div>
    <p class="eyebrow">Peminjaman buku</p>
    <h1>Selamat Datang di Perpustakaan</h1>
    <p class="desk">Temukan dan pinjam buku dengan mudah.</p>
    <a href="view/login_admin.php" class="btn">Login</a>
  </div>
</body>
</html>
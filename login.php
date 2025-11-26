<?php
session_start();
include "./config/koneksi.php";

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $q = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    $d = mysqli_fetch_assoc($q);

    // plain password mode for local testing
    if ($d && $password == $d['password']) {
        $_SESSION['user'] = $d;
        header("Location: index.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login - SPK Bibit Padi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">
<style>
body{display:flex;align-items:center;justify-content:center;height:100vh;background:radial-gradient(circle at 10% 10%, rgba(22,163,74,0.06), transparent), #eafaf0;}
.login-card{width:420px;background:#fff;padding:28px;border-radius:14px;box-shadow:0 12px 40px rgba(2,6,23,0.08);}
.brand {display:flex;align-items:center;gap:12px;margin-bottom:14px;}
.brand img{height:48px;border-radius:10px}
.btn-green {background:linear-gradient(90deg,#16a34a,#059669);color:#fff;border:0}
</style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <img src="assets/images/logo.png" alt="logo">
        <div>
            <h4 style="margin:0;color:var(--green-700)">SPK Bibit Padi</h4>
            <small class="text-muted">Sistem Pendukung Keputusan • AHP + TOPSIS</small>
        </div>
    </div>

    <?php if(isset($error)) { echo '<div class="alert alert-danger">'.$error.'</div>'; } ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button name="login" class="btn btn-green w-100">Masuk</button>
    </form>
</div>
</body>
</html>

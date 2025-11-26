<?php
// header.php (Theme E - Green Agriculture)
// session_start must be called in index.php only
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/style.css" rel="stylesheet">

<style>
    /* FIX: Navbar tetap di atas dan tidak ikut scroll */
    .navbar-custom {
        position: fixed !important;
        top: 0;
        left: 0;
        right: 0;
        z-index: 2000;
    }

    /* Dorong konten ke bawah supaya tidak tertutup navbar */
    body {
        padding-top: 64px !important;
}

</style>

<nav class="navbar navbar-light bg-white navbar-custom px-3 d-flex align-items-center" style="height:64px;">
    <button id="hamburger" class="hamburger-btn btn btn-link text-success me-2" aria-label="Toggle sidebar">
        <i class="bi bi-list" style="font-size:24px"></i>
    </button>

    <div class="d-flex align-items-center">
        <img src="assets/images/logo.png" alt="logo" style="height:36px;margin-right:10px;border-radius:6px;object-fit:cover">
        <span class="nav-title fw-bold text-success">SPK Bibit Padi</span>
    </div>

    <div class="ms-auto d-flex align-items-center">
        <div class="me-3 text-secondary small"><?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? '-') ?></div>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
</nav>

<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SPK Bibit Padi - Dashboard</title>
</head>
<body>
<?php include 'header.php'; ?>

<?php include 'sidebar.php'; ?>

<div class="content" style="padding:24px; margin-top:0px;">
    <?php
    $page = isset($_GET['page']) ? $_GET['page'] : 'beranda';
    $pfile = __DIR__ . '/pages/' . $page . '.php';
    if (file_exists($pfile)) {
        include $pfile;
    } else {
        echo '<h3>Halaman tidak ditemukan</h3>';
    }
    ?>
</div>

<?php include 'footer.php'; ?>

<!-- Optional JS for Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>

    /* Modal tidak menempel ke atas */
    .modal-dialog {
        margin-top: 70px !important;
    }

    /* Saat modal terbuka, konten turun agar header tidak ketutup */
    body.modal-open {
        padding-top: 60px !important;
    }

    /* Jika header fixed, pastikan tidak konflik */
    header {
        z-index: 1031;
    }

/* content shift when sidebar hidden */
.content.fullwidth { margin-left: 0 !important; }
@media (min-width: 769px) {
    .content { margin-left: 250px; transition: margin-left 0.28s ease; }
    .sidebar.d-none { display:none !important; }
    .footer-modern { left: 250px; transition: left 0.28s ease; }
}
@media (max-width: 768px) {
    .sidebar { display:none; }
    .content { margin: 0; padding: 16px; }
    .footer-modern { left: 0; }
}

</style>
</body>
</html>

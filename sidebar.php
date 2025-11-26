<?php
// sidebar.php - Theme E (green)
?>
<style>
.sidebar {
    width: 250px;
    background: linear-gradient(180deg,#0f766e,#065f46);
    color: #e6fff6;
    height: calc(100vh - 64px);
    position: fixed;
    top: 64px;
    left: 0;
    padding-top: 18px;
    transition: all 0.28s ease;
    overflow-y: auto;
    box-shadow: 2px 0 18px rgba(2,6,23,0.08);
    z-index: 1000;
}
.sidebar a {
    color: rgba(230,255,246,0.95);
    text-decoration: none;
    padding: 12px 20px;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    transition: background 0.18s, padding-left 0.18s;
    border-left: 4px solid transparent;
}



.sidebar a i { font-size:18px; width:22px; text-align:center }
.sidebar a:hover { background: rgba(255,255,255,0.04); padding-left:24px; color: #fff; }
.sidebar a.active {
    background: rgba(0,0,0,0.12);
    color: #fff;
    border-left-color: rgba(255,255,255,0.9);
    font-weight:600;
}
.sidebar .brand {
    padding: 0 16px 14px 20px;
    margin-bottom: 6px;
    border-bottom: 1px solid rgba(255,255,255,0.04);
    display:flex; align-items:center; gap:10px;
}
.sidebar .brand img { height:40px; border-radius:8px; }
.sidebar .section-title { padding: 12px 20px; color: rgba(230,255,246,0.7); font-size:12px; text-transform:uppercase; letter-spacing:0.08em; margin-top:10px; }
</style>

<div id="sidebar" class="sidebar">
    <div class="brand">
        <img src="assets/images/logo.png" alt="logo">
        <div>
            <div style="font-weight:700">SPK Padi</div>
            <div style="font-size:12px; color:rgba(230,255,246,0.85)">AHP + TOPSIS</div>
        </div>
    </div>

    <a href="index.php?page=beranda" class="<?= ($_GET['page'] ?? 'beranda') == 'beranda' ? 'active' : '' ?>">
        <i class="bi bi-house-door-fill"></i><span>Beranda</span>
    </a>

    <a href="index.php?page=kriteria" class="<?= ($_GET['page'] ?? '') == 'kriteria' ? 'active' : '' ?>">
        <i class="bi bi-sliders2"></i><span>Kriteria</span>
    </a>

    <a href="index.php?page=alternatif" class="<?= ($_GET['page'] ?? '') == 'alternatif' ? 'active' : '' ?>">
        <i class="bi bi-list-stars"></i><span>Alternatif</span>
    </a>

    <a href="index.php?page=nilai" class="<?= ($_GET['page'] ?? '') == 'nilai' ? 'active' : '' ?>">
        <i class="bi bi-calculator-fill"></i><span>Nilai Alternatif</span>
    </a>

    <a href="index.php?page=hasil" class="<?= ($_GET['page'] ?? '') == 'hasil' ? 'active' : '' ?>">
        <i class="bi bi-bar-chart-line-fill"></i><span>Hasil</span>
    </a>

    <div class="section-title">Aksi</div>
    <a href="logout.php"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
</div>

<script>
document.getElementById("hamburger").addEventListener("click", function () {
    const sb = document.getElementById("sidebar");
    const content = document.querySelector(".content");
    sb.classList.toggle("d-none");
    if (content) content.classList.toggle("fullwidth");
});
</script>

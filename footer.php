<?php
// footer.php - modern
?>
<footer class="footer-modern">
    <div class="container-fluid text-center py-2">
        <small class="text-muted">© <?= date('Y') ?> SPK Bibit Padi — AHP + TOPSIS • Dibuat oleh Kelompok 1</small>
    </div>
</footer>

<style>
.footer-modern {
    position: static;
    left: 0 !important;
    right: 0;
    bottom: 0;
    width: 100% !important;
    background: rgba(15,23,42,0.02);
    border-top: 1px solid rgba(0,0,0,0.04);
    padding: 12px 0;
    text-align: center !important;
}
.footer-modern small {
    width: 100%;
    display: block;
    text-align: center;
}


@media (max-width: 768px) {
    .footer-modern { left: 0; }
}


</style>

<?php
// pages/beranda.php
include "./config/koneksi.php";

// QUERY COUNT
$qc = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM kriteria");
$count_kriteria = ($ka = mysqli_fetch_assoc($qc)) ? $ka['jml'] : 0;

$qa = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM alternatif");
$count_alternatif = ($aa = mysqli_fetch_assoc($qa)) ? $aa['jml'] : 0;

$qn = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM nilai_alternatif");
$count_nilai = ($an = mysqli_fetch_assoc($qn)) ? $an['jml'] : 0;

$qv = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM v_perankingan");
$count_hasil = ($av = mysqli_fetch_assoc($qv)) ? $av['jml'] : 0;

// CHART DATA
$chart_labels = [];
$chart_data = [];

$chart_q = mysqli_query($koneksi, "SELECT nama, preferensi FROM v_perankingan ORDER BY ranking ASC LIMIT 5");
while ($r = mysqli_fetch_assoc($chart_q)) {
    $chart_labels[] = $r['nama'];
    $chart_data[] = (float)$r['preferensi'] * 100;
}

if (empty($chart_labels)) {
    $chart_labels = ['Belum Ada Data'];
    $chart_data = [100];
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    .dashboard-cards {
        gap: 18px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        margin-bottom: 22px;
    }

    .card-hero {
        border-radius: 12px;
        background: white;
        border: 0;
        box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
    }

    .small-muted {
        color: #6b7280;
        font-size: 13px;
    }

    .stats-number {
        font-size: 28px;
        font-weight: 700;
    }

    .card-chart {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 260px;
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-3">
            <h3 class="mt-2">Beranda</h3>
            <p class="small-muted">
                Selamat datang, <strong><?= htmlspecialchars($_SESSION['user']['nama_lengkap'] ?? 'User'); ?></strong>
            </p>
        </div>
    </div>

    <div class="dashboard-cards">

        <!-- Kriteria -->
        <div class="card card-hero p-3">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width:48px;height:48px;border-radius:10px;background:rgba(34,197,94,0.15);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-sliders" style="font-size:20px;color:#16a34a;"></i>
                </div>
                <div>
                    <div class="small-muted">Total Kriteria</div>
                    <div class="stats-number"><?= $count_kriteria ?></div>
                </div>
            </div>
        </div>

        <!-- Alternatif -->
        <div class="card card-hero p-3">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width:48px;height:48px;border-radius:10px;background:rgba(6,182,212,0.15);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-list-stars" style="font-size:20px;color:#0ea5e9;"></i>
                </div>
                <div>
                    <div class="small-muted">Total Alternatif</div>
                    <div class="stats-number"><?= $count_alternatif ?></div>
                </div>
            </div>
        </div>

        <!-- Nilai -->
        <div class="card card-hero p-3">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width:48px;height:48px;border-radius:10px;background:rgba(245,158,11,0.15);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-calculator" style="font-size:20px;color:#f59e0b;"></i>
                </div>
                <div>
                    <div class="small-muted">Total Nilai</div>
                    <div class="stats-number"><?= $count_nilai ?></div>
                </div>
            </div>
        </div>

        <!-- Hasil -->
        <div class="card card-hero p-3">
            <div class="d-flex align-items-center">
                <div class="me-3" style="width:48px;height:48px;border-radius:10px;background:rgba(239,68,68,0.15);display:flex;align-items:center;justify-content:center">
                    <i class="bi bi-bar-chart-line" style="font-size:20px;color:#ef4444;"></i>
                </div>
                <div>
                    <div class="small-muted">Data Hasil (Perankingan)</div>
                    <div class="stats-number"><?= $count_hasil ?></div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        <!-- CHART -->
        <div class="col-lg-6 mb-3">
            <div class="card card-hero p-3">
                <h5>Top 5 Alternatif (Preferensi %)</h5>
                <div class="card-chart">
                    <canvas id="donutChart"></canvas>
                </div>
            </div>
        </div>

        <!-- REFERENSI -->
        <!-- REFERENSI -->
        <div class="col-lg-6 mb-3">
            <div class="card card-hero p-4">
                <h5 class="mb-1">Referensi & Catatan</h5>
                <p class="small-muted mb-3">
                    Kumpulan sumber pendukung penelitian metode AHP + TOPSIS.
                </p>

                <!-- GALERI GAMBAR REFERENSI -->
                <div class="row g-3 mb-3">

                    <!-- Foto 1 -->
                    <div class="col-6">
                        <div class="card shadow-sm p-2" style="border-radius:12px;">
                            <img src="./assets/images/Jurnal.png"
                                class="img-fluid rounded"
                                style="width:100%; height:200px; object-fit:cover;"
                                alt="Gambar Penelitian 1">
                            <p class="small-muted text-center mt-2">Ilustrasi Penelitian 1</p>
                        </div>
                    </div>

                    <!-- Foto 2 -->
                    <div class="col-6">
                        <div class="card shadow-sm p-2" style="border-radius:12px;">
                            <img src="./assets/images/Jurnal2.png"
                                class="img-fluid rounded"
                                style="width:100%; height:200px; object-fit:cover;"
                                alt="Gambar Penelitian 2">
                            <p class="small-muted text-center mt-2">Ilustrasi Penelitian 2</p>
                        </div>
                    </div>

                </div>

                <!-- FILE REFERENSI -->
                <div class="mb-3">
                    <h6 class="mb-1">File Referensi</h6>

                    <a href="./assets/file/rakreditasi,+Journal+manager,+1145-3847-1-CE.pdf"
                        target="_blank"
                        class="d-inline-block mt-1"
                        style="font-weight:600; color:#0ea5e9; text-decoration:none;">
                        📄 Jurnal Manager — Analisis AHP + TOPSIS (PDF)
                    </a>
                    <div class="small-muted">(tersimpan dalam folder lokal aplikasi)</div>
                </div>

                <hr>

                <p class="small-muted">
                    Gunakan menu <strong>Data</strong> untuk mengelola Kriteria, Alternatif, Nilai,
                    serta melihat hasil perhitungan perankingan.
                </p>
            </div>
        </div>


    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = <?= json_encode($chart_labels) ?>;
    const dataValues = <?= json_encode($chart_data) ?>;

    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: dataValues,
                backgroundColor: [
                    'rgba(34,197,94,0.9)',
                    'rgba(6,182,212,0.9)',
                    'rgba(59,130,246,0.9)',
                    'rgba(245,158,11,0.9)',
                    'rgba(239,68,68,0.9)'
                ],
                hoverOffset: 8,
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
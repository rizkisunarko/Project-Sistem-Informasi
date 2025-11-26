<?php
include "./config/koneksi.php";

/* ==========================
   HANDLE FORM HASIL
===========================*/

// TAMBAH HASIL (Manual Entry - Optional)
if (isset($_POST['tambah'])) {
    $id_alt = (int)$_POST['id_alternatif'];
    $preferensi = mysqli_real_escape_string($koneksi, $_POST['preferensi']);
    
    // Cek apakah sudah ada hasil untuk alternatif ini
    $cek = mysqli_query($koneksi, "SELECT * FROM hasil_topsis WHERE id_alternatif=$id_alt");
    
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['flash_message'] = '<div class="alert alert-warning">Alternatif ini sudah memiliki hasil perhitungan!</div>';
    } else {
        $q = mysqli_query($koneksi, 
            "INSERT INTO hasil_topsis (id_alternatif, preferensi) VALUES ($id_alt, '$preferensi')");
        
        if ($q) {
            $_SESSION['flash_message'] = '<div class="alert alert-success">Hasil berhasil ditambahkan!</div>';
        } else {
            $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menambahkan hasil: ' . mysqli_error($koneksi) . '</div>';
        }
    }
    
    echo "<script>location.href='index.php?page=hasil';</script>";
    exit;
}

// EDIT HASIL
if (isset($_POST['edit'])) {
    $id = (int)$_POST['id_hasil'];
    $id_alt = (int)$_POST['id_alternatif'];
    $preferensi = mysqli_real_escape_string($koneksi, $_POST['preferensi']);
    
    $q = mysqli_query($koneksi,
        "UPDATE hasil_topsis SET id_alternatif=$id_alt, preferensi='$preferensi' WHERE id_hasil=$id");
    
    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Hasil berhasil diubah!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal mengubah hasil: ' . mysqli_error($koneksi) . '</div>';
    }
    
    echo "<script>location.href='index.php?page=hasil';</script>";
    exit;
}

// HAPUS HASIL
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = mysqli_query($koneksi, "DELETE FROM hasil_topsis WHERE id_hasil=$id");
    
    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Hasil berhasil dihapus!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menghapus hasil: ' . mysqli_error($koneksi) . '</div>';
    }
    
    echo "<script>location.href='index.php?page=hasil';</script>";
    exit;
}

// HITUNG ULANG OTOMATIS (TOPSIS Method)
if (isset($_POST['hitung_otomatis'])) {
    // Hapus hasil lama
    mysqli_query($koneksi, "TRUNCATE TABLE hasil_topsis");
    
    // Ambil semua kriteria
    $kriteria = [];
    $qK = mysqli_query($koneksi, "SELECT * FROM kriteria");
    while ($k = mysqli_fetch_assoc($qK)) {
        $kriteria[] = $k;
    }
    
    // Ambil semua alternatif
    $qA = mysqli_query($koneksi, "SELECT * FROM alternatif");
    $alternatif = [];
    while ($a = mysqli_fetch_assoc($qA)) {
        $alternatif[] = $a;
    }
    
    // STEP 1: Membuat matriks keputusan
    $matriks = [];
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $matriks[$id_a] = [];
        
        foreach ($kriteria as $k) {
            $id_k = $k['id_kriteria'];
            
            // Ambil nilai
            $qN = mysqli_query($koneksi,
                "SELECT nilai FROM nilai_alternatif 
                 WHERE id_alternatif=$id_a AND id_kriteria=$id_k");
            $rowN = mysqli_fetch_assoc($qN);
            $nilai = $rowN ? $rowN['nilai'] : 0;
            
            $matriks[$id_a][$id_k] = $nilai;
        }
    }
    
    // STEP 2: Normalisasi matriks
    $normalized = [];
    $pangkat = [];
    
    // Hitung jumlah kuadrat untuk setiap kriteria
    foreach ($kriteria as $k) {
        $id_k = $k['id_kriteria'];
        $sum_squares = 0;
        
        foreach ($alternatif as $a) {
            $id_a = $a['id_alternatif'];
            $sum_squares += pow($matriks[$id_a][$id_k], 2);
        }
        
        $pangkat[$id_k] = sqrt($sum_squares);
    }
    
    // Normalisasi
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $normalized[$id_a] = [];
        
        foreach ($kriteria as $k) {
            $id_k = $k['id_kriteria'];
            $normalized[$id_a][$id_k] = $pangkat[$id_k] > 0 
                ? $matriks[$id_a][$id_k] / $pangkat[$id_k] 
                : 0;
        }
    }
    
    // STEP 3: Matriks terbobot
    $weighted = [];
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $weighted[$id_a] = [];
        
        foreach ($kriteria as $k) {
            $id_k = $k['id_kriteria'];
            $weighted[$id_a][$id_k] = $normalized[$id_a][$id_k] * $k['bobot'];
        }
    }
    
    // STEP 4: Solusi ideal positif dan negatif
    $ideal_positive = [];
    $ideal_negative = [];
    
    foreach ($kriteria as $k) {
        $id_k = $k['id_kriteria'];
        $values = [];
        
        foreach ($alternatif as $a) {
            $id_a = $a['id_alternatif'];
            $values[] = $weighted[$id_a][$id_k];
        }
        
        if ($k['sifat'] == 'benefit') {
            $ideal_positive[$id_k] = max($values);
            $ideal_negative[$id_k] = min($values);
        } else {
            $ideal_positive[$id_k] = min($values);
            $ideal_negative[$id_k] = max($values);
        }
    }
    
    // STEP 5: Hitung jarak ke solusi ideal
    $d_plus = [];
    $d_minus = [];
    
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $sum_plus = 0;
        $sum_minus = 0;
        
        foreach ($kriteria as $k) {
            $id_k = $k['id_kriteria'];
            $sum_plus += pow($weighted[$id_a][$id_k] - $ideal_positive[$id_k], 2);
            $sum_minus += pow($weighted[$id_a][$id_k] - $ideal_negative[$id_k], 2);
        }
        
        $d_plus[$id_a] = sqrt($sum_plus);
        $d_minus[$id_a] = sqrt($sum_minus);
    }
    
    // STEP 6: Hitung nilai preferensi
    $preferensi_values = [];
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $preferensi_values[$id_a] = ($d_plus[$id_a] + $d_minus[$id_a]) > 0 
            ? $d_minus[$id_a] / ($d_plus[$id_a] + $d_minus[$id_a]) 
            : 0;
    }
    
    // STEP 7: Ranking
    arsort($preferensi_values);
    $ranking = 1;
    $rank_data = [];
    foreach ($preferensi_values as $id_a => $preferensi) {
        $rank_data[$id_a] = $ranking++;
    }
    
    // STEP 8: Simpan ke database
    foreach ($alternatif as $a) {
        $id_a = $a['id_alternatif'];
        $d_plus_val = $d_plus[$id_a];
        $d_minus_val = $d_minus[$id_a];
        $preferensi_val = $preferensi_values[$id_a];
        $ranking_val = $rank_data[$id_a];
        
        mysqli_query($koneksi,
            "INSERT INTO hasil_topsis (id_alternatif, d_plus, d_minus, preferensi, ranking) 
             VALUES ($id_a, '$d_plus_val', '$d_minus_val', '$preferensi_val', $ranking_val)");
    }
    
    $_SESSION['flash_message'] = '<div class="alert alert-success"><strong>Perhitungan Selesai!</strong> Hasil perankingan telah diperbarui menggunakan metode TOPSIS.</div>';
    
    echo "<script>location.href='index.php?page=hasil';</script>";
    exit;
}

/* ==========================
   LOAD DATA
===========================*/

// Query data hasil topsis
$q = mysqli_query($koneksi, "
    SELECT 
        h.id_hasil,
        h.id_alternatif,
        a.kode,
        a.nama,
        h.d_plus,
        h.d_minus,
        h.preferensi,
        h.ranking
    FROM hasil_topsis h
    JOIN alternatif a ON h.id_alternatif = a.id_alternatif
    ORDER BY h.ranking ASC
");

$labels = [];
$values = [];
$rows = [];

while ($d = mysqli_fetch_assoc($q)) {
    $labels[] = $d['nama'];
    $values[] = round($d['preferensi'] * 100, 4);
    $rows[] = $d;
}

// Ambil daftar alternatif untuk dropdown
$alternatif_list = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY nama");

// Cek apakah sudah ada hasil perhitungan
$has_results = count($rows) > 0;
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
.card-modern {
    border-radius: 12px;
    box-shadow: 0 8px 20px rgba(16,24,40,0.06);
    border: 0;
}
.table-modern thead {
    background: #16a34a;
    color: white;
    vertical-align: middle;
}
.table-modern tbody tr:hover {
    background: rgba(34,197,94,0.12);
}
.btn-icon {
    padding: 6px 10px;
    margin: 0 2px;
}
.btn-icon i {
    font-size: 14px;
    display: inline-block !important;
}
.ranking-badge {
    font-size: 1.1rem;
    padding: 8px 12px;
    border-radius: 8px;
}
.ranking-1 {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #000;
    font-weight: bold;
}
.ranking-2 {
    background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
    color: #000;
}
.ranking-3 {
    background: linear-gradient(135deg, #cd7f32 0%, #e8a87c 100%);
    color: #fff;
}
.alert-info-custom {
    background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%);
    border: 0;
    color: white;
}
/* Pastikan icon terlihat */
.bi::before {
    display: inline-block !important;
}
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold text-success">
            <i class="bi bi-trophy-fill"></i> Hasil Perankingan TOPSIS
        </h3>
        
        <div>
            <button class="btn btn-primary px-3 me-2" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalHitungOtomatis">
                <i class="bi bi-calculator"></i> Hitung Ulang TOPSIS
            </button>
            
            <button class="btn btn-success px-3" 
                    data-bs-toggle="modal" 
                    data-bs-target="#modalTambah">
                <i class="bi bi-plus-circle"></i> Tambah Manual
            </button>
        </div>
    </div>

    <?php
    if (isset($_SESSION['flash_message'])) {
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    ?>

    <?php if (!$has_results): ?>
    <div class="alert alert-info-custom mb-4">
        <h5 class="mb-2"><i class="bi bi-info-circle"></i> Belum Ada Hasil Perhitungan</h5>
        <p class="mb-2">Klik tombol <strong>"Hitung Ulang TOPSIS"</strong> untuk menghitung ranking berdasarkan nilai alternatif yang telah diinput.</p>
        <small>Atau tambah hasil secara manual jika diperlukan.</small>
    </div>
    <?php endif; ?>

    <!-- CARD TABEL -->
    <div class="card card-modern mb-4">
        <div class="card-body">
            <h5 class="mb-3">Tabel Hasil Perankingan TOPSIS</h5>

            <?php if ($has_results): ?>
            <div class="table-responsive">
                <table class="table table-modern table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="80" class="text-center">Kode</th>
                            <th>Alternatif</th>
                            <th width="120" class="text-center">D+</th>
                            <th width="120" class="text-center">D-</th>
                            <th width="120" class="text-center">Preferensi</th>
                            <th width="100" class="text-center">Ranking</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $d): ?>
                        <tr>
                            <td class="text-center fw-bold text-success"><?= htmlspecialchars($d['kode']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($d['nama']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    <?= round($d['d_plus'], 5) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">
                                    <?= round($d['d_minus'], 5) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">
                                    <?= round($d['preferensi'], 4) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php
                                $ranking = $d['ranking'];
                                $badge_class = 'bg-success';
                                if ($ranking == 1) $badge_class = 'ranking-1';
                                elseif ($ranking == 2) $badge_class = 'ranking-2';
                                elseif ($ranking == 3) $badge_class = 'ranking-3';
                                ?>
                                <span class="badge <?= $badge_class ?> ranking-badge">
                                    #<?= $ranking ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-warning btn-sm btn-icon"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit<?= $d['id_hasil'] ?>"
                                    title="Edit Hasil">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <a onclick="return confirm('Hapus hasil ini?')"
                                   href="index.php?page=hasil&hapus=<?= $d['id_hasil'] ?>"
                                   class="btn btn-danger btn-sm btn-icon"
                                   title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>

                        <!-- MODAL EDIT untuk setiap baris -->
                        <div class="modal fade" id="modalEdit<?= $d['id_hasil'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="index.php?page=hasil">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Hasil</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <input type="hidden" name="id_hasil" value="<?= $d['id_hasil'] ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Alternatif</label>
                                                <select name="id_alternatif" class="form-control" required>
                                                    <?php
                                                    $alt_list = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY nama");
                                                    while ($a = mysqli_fetch_assoc($alt_list)):
                                                    ?>
                                                    <option value="<?= $a['id_alternatif'] ?>"
                                                        <?= $a['id_alternatif'] == $d['id_alternatif'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($a['kode']) ?> - <?= htmlspecialchars($a['nama']) ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nilai Preferensi</label>
                                                <input type="number" step="0.0001" name="preferensi" 
                                                       class="form-control" value="<?= $d['preferensi'] ?>" required>
                                                <small class="text-muted">Nilai antara 0 - 1</small>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" name="edit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                <p class="mt-3">Belum ada data hasil perhitungan</p>
            </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- CARD CHART -->
    <?php if ($has_results): ?>
    <div class="card card-modern">
        <div class="card-body">
            <h5 class="mb-3">Grafik Ranking Alternatif</h5>
            <canvas id="barChart" height="160"></canvas>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- MODAL HITUNG OTOMATIS -->
<div class="modal fade" id="modalHitungOtomatis" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=hasil">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-calculator"></i> Hitung Ulang TOPSIS
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle"></i> Perhatian!</h6>
                        <ul class="mb-0 ps-3">
                            <li>Proses ini akan menghapus semua hasil perhitungan sebelumnya</li>
                            <li>Sistem akan menghitung ulang menggunakan Metode TOPSIS</li>
                            <li>Pastikan data nilai alternatif sudah lengkap</li>
                        </ul>
                    </div>

                    <p class="mb-0 fw-bold">Lanjutkan perhitungan ulang?</p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="hitung_otomatis" class="btn btn-primary">
                        <i class="bi bi-calculator"></i> Ya, Hitung Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH MANUAL -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=hasil">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Hasil Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        Gunakan fitur ini jika ingin menambah hasil secara manual tanpa perhitungan otomatis.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Alternatif</label>
                        <select name="id_alternatif" class="form-control" required>
                            <option value="">-- Pilih Alternatif --</option>
                            <?php
                            mysqli_data_seek($alternatif_list, 0);
                            while ($a = mysqli_fetch_assoc($alternatif_list)):
                            ?>
                            <option value="<?= $a['id_alternatif'] ?>">
                                <?= htmlspecialchars($a['kode']) ?> - <?= htmlspecialchars($a['nama']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai Preferensi</label>
                        <input type="number" step="0.0001" name="preferensi" 
                               class="form-control" placeholder="Contoh: 0.8542" required>
                        <small class="text-muted">Nilai antara 0 - 1</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="tambah" class="btn btn-success">Tambah</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php if ($has_results): ?>
<script>
let ctx = document.getElementById("barChart").getContext("2d");

new Chart(ctx, {
    type: "bar",
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [{
            label: "Nilai Preferensi (%)",
            data: <?= json_encode($values) ?>,
            backgroundColor: "rgba(34,197,94,0.8)",
            borderColor: "rgba(22,163,74,1)",
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: true },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return " " + ctx.raw.toFixed(4) + "%";
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: "#1e293b" }
            },
            x: {
                ticks: { color: "#1e293b" }
            }
        }
    }
});
</script>
<?php endif; ?>

<script>
// Auto-focus modal
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('select, input:not([type="hidden"])');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
});
</script>
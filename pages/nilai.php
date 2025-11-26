<?php
include "./config/koneksi.php";

/* ==========================
   HANDLE FORM 
===========================*/

// TAMBAH
if (isset($_POST['tambah'])) {
    $alt   = mysqli_real_escape_string($koneksi, $_POST['id_alternatif']);
    $krit  = mysqli_real_escape_string($koneksi, $_POST['id_kriteria']);
    $nilai = mysqli_real_escape_string($koneksi, $_POST['nilai']);

    $q = mysqli_query($koneksi, 
        "INSERT INTO nilai_alternatif (id_alternatif, id_kriteria, nilai)
            VALUES ('$alt','$krit','$nilai')");

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Nilai berhasil ditambahkan!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menambahkan nilai: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=nilai';</script>";
    exit;
}

// EDIT
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id_nilai'];
    $alt   = mysqli_real_escape_string($koneksi, $_POST['id_alternatif']);
    $krit  = mysqli_real_escape_string($koneksi, $_POST['id_kriteria']);
    $nilai = mysqli_real_escape_string($koneksi, $_POST['nilai']);

    $q = mysqli_query($koneksi,
        "UPDATE nilai_alternatif SET
            id_alternatif='$alt',
            id_kriteria='$krit',
            nilai='$nilai'
        WHERE id_nilai=$id");

    $affected_rows = mysqli_affected_rows($koneksi);

    if ($q) {
        if ($affected_rows > 0) {
            $_SESSION['flash_message'] = '<div class="alert alert-success">Nilai berhasil diubah!</div>';
        } else {
            $_SESSION['flash_message'] = '<div class="alert alert-warning">Tidak ada perubahan yang disimpan.</div>';
        }
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal mengubah nilai: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=nilai';</script>";
    exit;
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = mysqli_query($koneksi, "DELETE FROM nilai_alternatif WHERE id_nilai=$id");

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Nilai berhasil dihapus!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menghapus nilai: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=nilai';</script>";
    exit;
}


/* ==========================
   AMBIL DATA
===========================*/
$alternatif = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY nama");
$kriteria   = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode");

$nilai = mysqli_query($koneksi, "
    SELECT n.*, 
           a.nama AS alternatif, 
           k.nama AS kriteria
    FROM nilai_alternatif n
    JOIN alternatif a ON n.id_alternatif=a.id_alternatif
    JOIN kriteria k   ON n.id_kriteria=k.id_kriteria
    ORDER BY n.id_nilai DESC
");

// Simpan data nilai dalam array untuk modal edit
$nilai_data = [];
mysqli_data_seek($nilai, 0); // Reset pointer
while ($d = mysqli_fetch_assoc($nilai)) {
    $nilai_data[] = $d;
}

// Data grafik – rata-rata nilai tiap alternatif
$grafik_q = mysqli_query($koneksi, "
    SELECT a.nama AS alternatif, AVG(n.nilai) AS avg_nilai
    FROM nilai_alternatif n
    JOIN alternatif a ON a.id_alternatif=n.id_alternatif
    GROUP BY n.id_alternatif
    ORDER BY a.nama
");

$chart_label = [];
$chart_data  = [];
while ($d = mysqli_fetch_assoc($grafik_q)) {
    $chart_label[] = $d['alternatif'];
    $chart_data[]  = floatval($d['avg_nilai']);
}

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
.card-modern {
    border-radius: 12px;
    border: 0;
    box-shadow: 0 8px 20px rgba(16,24,40,0.06);
}
.table-modern thead {
    background:#16a34a;
    color:white;
}
.table-modern tbody tr:hover {
    background:rgba(34,197,94,0.12);
}
.btn-icon {
    padding:4px 10px;
}
/* Pastikan input tidak readonly */
.modal input:not([type="hidden"]),
.modal select,
.modal textarea {
    pointer-events: auto !important;
    opacity: 1 !important;
}
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="fw-bold">Data Nilai Alternatif</h3>

        <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Nilai
        </button>
    </div>

    <?php
    if (isset($_SESSION['flash_message'])) {
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    ?>

    <!-- TABEL -->
    <div class="card card-modern mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-modern align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Alternatif</th>
                            <th>Kriteria</th>
                            <th>Nilai</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($nilai_data as $d): ?>
                        <tr>
                            <td><?= $d['id_nilai'] ?></td>
                            <td class="fw-bold text-success"><?= htmlspecialchars($d['alternatif']) ?></td>
                            <td><?= htmlspecialchars($d['kriteria']) ?></td>
                            <td><?= $d['nilai'] ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm btn-icon"
                                    data-bs-toggle="modal" data-bs-target="#modalEdit<?= $d['id_nilai'] ?>">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <a onclick="return confirm('Hapus nilai ini?')"
                                   href="index.php?page=nilai&hapus=<?= $d['id_nilai'] ?>"
                                   class="btn btn-danger btn-sm btn-icon">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- GRAFIK -->
    <div class="card card-modern p-3">
        <h5 class="mb-3">Grafik Rata-Rata Nilai Alternatif</h5>
        <canvas id="chartNilai" height="120"></canvas>
    </div>

</div>

<!-- MODAL EDIT - DIPINDAHKAN KE LUAR TABEL -->
<?php foreach ($nilai_data as $d): ?>
<div class="modal fade" id="modalEdit<?= $d['id_nilai'] ?>" tabindex="-1" aria-labelledby="modalEditLabel<?= $d['id_nilai'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=nilai">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel<?= $d['id_nilai'] ?>">Edit Nilai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_nilai" value="<?= $d['id_nilai'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Alternatif</label>
                        <select name="id_alternatif" class="form-control" required>
                            <?php
                            $alt2 = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY nama");
                            while ($a = mysqli_fetch_assoc($alt2)):
                            ?>
                                <option value="<?= $a['id_alternatif'] ?>"
                                    <?= $a['id_alternatif']==$d['id_alternatif']?'selected':'' ?>>
                                    <?= htmlspecialchars($a['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kriteria</label>
                        <select name="id_kriteria" class="form-control" required>
                            <?php
                            $kr2 = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode");
                            while ($k = mysqli_fetch_assoc($kr2)):
                            ?>
                                <option value="<?= $k['id_kriteria'] ?>"
                                    <?= $k['id_kriteria']==$d['id_kriteria']?'selected':'' ?>>
                                    <?= htmlspecialchars($k['kode']." - ".$k['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai</label>
                        <input type="number" step="0.001" name="nilai"
                               class="form-control" value="<?= $d['nilai'] ?>" required>
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

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=nilai">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Nilai Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Alternatif</label>
                        <select name="id_alternatif" class="form-control" required>
                            <option value="">-- Pilih Alternatif --</option>
                            <?php
                            $a = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY nama");
                            while ($alt = mysqli_fetch_assoc($a)):
                            ?>
                            <option value="<?= $alt['id_alternatif'] ?>"><?= htmlspecialchars($alt['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kriteria</label>
                        <select name="id_kriteria" class="form-control" required>
                            <option value="">-- Pilih Kriteria --</option>
                            <?php
                            $k = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY kode");
                            while ($kr = mysqli_fetch_assoc($k)):
                            ?>
                            <option value="<?= $kr['id_kriteria'] ?>">
                                <?= htmlspecialchars($kr['kode']." - ".$kr['nama']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nilai</label>
                        <input type="number" step="0.001" name="nilai" class="form-control" required>
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

<script>
new Chart(document.getElementById('chartNilai'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_label) ?>,
        datasets: [{
            label: "Rata-rata Nilai",
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: 'rgba(6,182,212,0.85)',
            borderRadius: 8
        }]
    },
    options: {
        plugins: { legend: { display:false }},
        scales: { y: { beginAtZero: true }}
    }
});

// Auto-focus dan debugging
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
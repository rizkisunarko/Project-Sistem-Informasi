<?php
include "./config/koneksi.php";

/* ==========================
   HANDLE FORM ALTERNATIF
===========================*/

// TAMBAH ALTERNATIF
if (isset($_POST['tambah'])) {
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    $q = mysqli_query($koneksi, 
        "INSERT INTO alternatif (kode, nama) VALUES ('$kode', '$nama')");

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Alternatif berhasil ditambahkan!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menambahkan alternatif: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=alternatif';</script>";
    exit;
}

// EDIT ALTERNATIF
if (isset($_POST['edit'])) {
    $id   = (int)$_POST['id_alternatif'];
    $kode = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);

    $q = mysqli_query($koneksi,
        "UPDATE alternatif SET kode='$kode', nama='$nama' WHERE id_alternatif=$id");

    $affected_rows = mysqli_affected_rows($koneksi);

    if ($q) {
        if ($affected_rows > 0) {
            $_SESSION['flash_message'] = '<div class="alert alert-success">Alternatif berhasil diubah!</div>';
        } else {
            $_SESSION['flash_message'] = '<div class="alert alert-warning">Tidak ada perubahan yang disimpan.</div>';
        }
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal mengubah alternatif: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=alternatif';</script>";
    exit;
}

// HAPUS ALTERNATIF
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    
    // Hapus nilai terkait terlebih dahulu (foreign key constraint)
    mysqli_query($koneksi, "DELETE FROM nilai_alternatif WHERE id_alternatif=$id");
    
    // Hapus alternatif
    $q = mysqli_query($koneksi, "DELETE FROM alternatif WHERE id_alternatif=$id");

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Alternatif berhasil dihapus!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menghapus alternatif: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=alternatif';</script>";
    exit;
}

// SIMPAN NILAI (Quick Edit dari Tabel)
if (isset($_POST['simpan_nilai'])) {
    $id_alt = (int)$_POST['id_alternatif'];
    
    foreach ($_POST['nilai'] as $id_krit => $nilai) {
        $id_krit = (int)$id_krit;
        $nilai = mysqli_real_escape_string($koneksi, $nilai);
        
        // Cek apakah sudah ada nilai
        $cek = mysqli_query($koneksi, 
            "SELECT id_nilai FROM nilai_alternatif 
             WHERE id_alternatif=$id_alt AND id_kriteria=$id_krit");
        
        if (mysqli_num_rows($cek) > 0) {
            // Update
            mysqli_query($koneksi,
                "UPDATE nilai_alternatif SET nilai='$nilai' 
                 WHERE id_alternatif=$id_alt AND id_kriteria=$id_krit");
        } else {
            // Insert
            mysqli_query($koneksi,
                "INSERT INTO nilai_alternatif (id_alternatif, id_kriteria, nilai)
                 VALUES ($id_alt, $id_krit, '$nilai')");
        }
    }
    
    $_SESSION['flash_message'] = '<div class="alert alert-success">Nilai berhasil disimpan!</div>';
    echo "<script>location.href='index.php?page=alternatif';</script>";
    exit;
}

/* ==========================
   LOAD DATA
===========================*/

// Ambil daftar kriteria
$kriteria = [];
$qK = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
while ($row = mysqli_fetch_assoc($qK)) {
    $kriteria[] = $row;
}

// Ambil data alternatif + nilai
$alternatif = [];
$qA = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif");
while ($a = mysqli_fetch_assoc($qA)) {

    // ambil nilai per kriteria
    $nilai = [];
    $total = 0;

    foreach ($kriteria as $k) {
        $id_k = $k['id_kriteria'];
        $id_a = $a['id_alternatif'];

        $qN = mysqli_query($koneksi, 
            "SELECT nilai FROM nilai_alternatif 
             WHERE id_alternatif=$id_a AND id_kriteria=$id_k");

        $rowN = mysqli_fetch_assoc($qN);

        $nilai_k = $rowN ? $rowN['nilai'] : 0;
        $nilai[$id_k] = $nilai_k;

        $total += $nilai_k;
    }

    $a['nilai'] = $nilai;
    $a['total'] = $total;

    $alternatif[] = $a;
}

// Labels & values untuk grafik
$chart_labels = array_column($alternatif, 'nama');
$chart_total  = array_column($alternatif, 'total');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
.table-modern thead th {
    padding: 12px 8px;
    white-space: nowrap;
}
.table-modern tbody tr:hover {
    background: rgba(34,197,94,0.12);
}
.table-modern tbody td {
    vertical-align: middle;
    padding: 10px 8px;
}
.badge-kode {
    background: #16a34a;
    padding: 5px 10px;
    border-radius: 6px;
    color: white;
}
.btn-icon {
    padding: 4px 10px;
}
.nilai-input {
    width: 70px;
    text-align: center;
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
        <h3 class="fw-bold">Data Alternatif + Nilai</h3>
        <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Alternatif
        </button>
    </div>

    <?php
    if (isset($_SESSION['flash_message'])) {
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    ?>

    <!-- Tabel Alternatif -->
    <div class="card card-modern mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern table-bordered table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="50" class="text-center">ID</th>
                            <th width="100" class="text-center">Kode</th>
                            <th>Nama Alternatif</th>

                            <?php foreach ($kriteria as $k): ?>
                                <th class="text-center align-middle" width="90">
                                    <div class="fw-bold"><?= $k['kode'] ?></div>
                                    <small class="d-block text-white-50" style="font-size: 0.7rem; line-height: 1.2;"><?= $k['nama'] ?></small>
                                </th>
                            <?php endforeach; ?>

                            <th width="100" class="text-center">Total</th>
                            <th width="180" class="text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($alternatif as $a): ?>
                        <tr>
                            <td class="text-center"><?= $a['id_alternatif'] ?></td>
                            <td class="text-center"><span class="badge badge-kode"><?= htmlspecialchars($a['kode']) ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($a['nama']) ?></td>

                            <?php foreach ($kriteria as $k): 
                                $id_k = $k['id_kriteria']; ?>
                                <td class="text-center align-middle"><?= $a['nilai'][$id_k] ?></td>
                            <?php endforeach; ?>

                            <td class="fw-bold text-success text-center"><?= $a['total'] ?></td>
                            <td class="text-center">
                                <button class="btn btn-info btn-sm btn-icon" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalNilai<?= $a['id_alternatif'] ?>"
                                    title="Edit Nilai">
                                    <i class="bi bi-pencil-fill"></i>
                                </button>
                                
                                <button class="btn btn-warning btn-sm btn-icon"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalEdit<?= $a['id_alternatif'] ?>"
                                    title="Edit Alternatif">
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <a onclick="return confirm('Hapus alternatif ini?\n\nSemua nilai yang terkait juga akan dihapus!')"
                                   href="index.php?page=alternatif&hapus=<?= $a['id_alternatif'] ?>"
                                   class="btn btn-danger btn-sm btn-icon"
                                   title="Hapus">
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

    <!-- Grafik Total Nilai -->
    <div class="card card-modern p-3">
        <h5 class="mb-3">Grafik Total Nilai Alternatif</h5>
        <canvas id="chartAlternatif" height="120"></canvas>
    </div>

</div>

<!-- MODAL TAMBAH ALTERNATIF -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=alternatif">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Alternatif</label>
                        <input type="text" name="kode" class="form-control" 
                               placeholder="Contoh: A1, A2, A3..." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Alternatif</label>
                        <input type="text" name="nama" class="form-control" 
                               placeholder="Nama lengkap alternatif" required>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        Setelah menambahkan alternatif, Anda dapat mengisi nilai untuk setiap kriteria.
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

<!-- MODAL EDIT ALTERNATIF -->
<?php foreach ($alternatif as $a): ?>
<div class="modal fade" id="modalEdit<?= $a['id_alternatif'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=alternatif">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_alternatif" value="<?= $a['id_alternatif'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Kode Alternatif</label>
                        <input type="text" name="kode" class="form-control" 
                               value="<?= htmlspecialchars($a['kode']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Alternatif</label>
                        <input type="text" name="nama" class="form-control" 
                               value="<?= htmlspecialchars($a['nama']) ?>" required>
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

<!-- MODAL EDIT NILAI -->
<?php foreach ($alternatif as $a): ?>
<div class="modal fade" id="modalNilai<?= $a['id_alternatif'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="index.php?page=alternatif">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-fill"></i> 
                        Edit Nilai: <?= htmlspecialchars($a['nama']) ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_alternatif" value="<?= $a['id_alternatif'] ?>">

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="100">Kode</th>
                                    <th>Nama Kriteria</th>
                                    <th width="80">Bobot</th>
                                    <th width="100">Sifat</th>
                                    <th width="120">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kriteria as $k): ?>
                                <tr>
                                    <td class="fw-bold text-success"><?= $k['kode'] ?></td>
                                    <td><?= htmlspecialchars($k['nama']) ?></td>
                                    <td class="text-center"><?= $k['bobot'] ?></td>
                                    <td>
                                        <span class="badge <?= $k['sifat']=='benefit'?'bg-success':'bg-danger' ?>">
                                            <?= ucfirst($k['sifat']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               step="0.001" 
                                               name="nilai[<?= $k['id_kriteria'] ?>]" 
                                               class="form-control form-control-sm text-center" 
                                               value="<?= $a['nilai'][$k['id_kriteria']] ?>" 
                                               required>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Perhatian:</strong> Pastikan nilai yang dimasukkan sesuai dengan skala penilaian yang telah ditentukan.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_nilai" class="btn btn-info">
                        <i class="bi bi-save"></i> Simpan Nilai
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let labels = <?= json_encode($chart_labels) ?>;
let totals = <?= json_encode($chart_total) ?>;

new Chart(document.getElementById('chartAlternatif'), {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: "Total Nilai",
            data: totals,
            backgroundColor: 'rgba(34,197,94,0.8)',
            borderColor: 'rgba(16,122,47,1)',
            borderWidth: 1,
            borderRadius: 8,
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true }
        },
        plugins: {
            legend: { display: false }
        }
    }
});

// Auto-focus dan debugging
document.addEventListener('DOMContentLoaded', function() {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        modal.addEventListener('shown.bs.modal', function() {
            const firstInput = this.querySelector('input:not([type="hidden"]), select');
            if (firstInput) {
                firstInput.focus();
            }
        });
    });
});
</script>
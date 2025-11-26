<?php
// Pastikan session sudah dimulai di index.php
// session_start(); 
include "./config/koneksi.php";

// TAMBAH
if (isset($_POST['tambah'])) {
    $kode  = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $bobot = $_POST['bobot'];
    $sifat = mysqli_real_escape_string($koneksi, $_POST['sifat']);

    $q = mysqli_query(
        $koneksi,
        "INSERT INTO kriteria (kode, nama, bobot, sifat) 
         VALUES ('$kode', '$nama', '$bobot', '$sifat')"
    );

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Kriteria berhasil ditambahkan!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menambahkan kriteria: ' . mysqli_error($koneksi) . '</div>';
    }
    echo "<script>location.href='index.php?page=kriteria';</script>";
    exit;
}

// EDIT
if (isset($_POST['edit'])) {
    $id    = (int)$_POST['id_kriteria'];
    $kode  = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama  = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $bobot = $_POST['bobot'];
    $sifat = mysqli_real_escape_string($koneksi, $_POST['sifat']);

    $q = mysqli_query(
        $koneksi,
        "UPDATE kriteria SET 
             kode='$kode', 
             nama='$nama', 
             bobot='$bobot', 
             sifat='$sifat'
         WHERE id_kriteria=$id"
    );

    $affected_rows = mysqli_affected_rows($koneksi);

    if ($q) {
        if ($affected_rows > 0) {
            $_SESSION['flash_message'] = '<div class="alert alert-success">Kriteria berhasil diubah!</div>';
        } else {
            $_SESSION['flash_message'] = '<div class="alert alert-warning">Tidak ada perubahan yang disimpan.</div>';
        }
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal mengubah kriteria: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=kriteria';</script>";
    exit;
}

// HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $q = mysqli_query($koneksi, "DELETE FROM kriteria WHERE id_kriteria=$id");

    if ($q) {
        $_SESSION['flash_message'] = '<div class="alert alert-success">Kriteria berhasil dihapus!</div>';
    } else {
        $_SESSION['flash_message'] = '<div class="alert alert-danger">Gagal menghapus kriteria: ' . mysqli_error($koneksi) . '</div>';
    }

    echo "<script>location.href='index.php?page=kriteria';</script>";
    exit;
}

/* LOAD DATA Kriteria */
$kriteria = [];
$q = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
while ($d = mysqli_fetch_assoc($q)) {
    $kriteria[] = $d;
}

// data grafik
$labels = array_column($kriteria, "kode");
$bobot  = array_column($kriteria, "bobot");
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    .card-modern {
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(16, 24, 40, 0.06);
        border: 0;
    }

    .table-modern thead {
        background: #16a34a;
        color: white;
    }

    .table-modern tbody tr:hover {
        background: rgba(34, 197, 94, 0.12);
    }

    .btn-icon {
        padding: 4px 10px;
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
        <h3 class="fw-bold">Data Kriteria</h3>
        <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="bi bi-plus-circle"></i> Tambah Kriteria
        </button>
    </div>

    <?php
    if (isset($_SESSION['flash_message'])) {
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
    }
    ?>

    <div class="card card-modern mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-modern align-middle">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Kriteria</th>
                            <th width="100">Bobot</th>
                            <th width="120">Sifat</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kriteria as $d): ?>
                            <tr>
                                <td class="fw-bold text-success"><?= $d['kode'] ?></td>
                                <td><?= $d['nama'] ?></td>
                                <td><?= $d['bobot'] ?></td>
                                <td>
                                    <span class="badge <?= $d['sifat'] == 'benefit' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= ucfirst($d['sifat']) ?>
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm btn-icon"
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit<?= $d['id_kriteria'] ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <a onclick="return confirm('Hapus kriteria ini?')"
                                        href="index.php?page=kriteria&hapus=<?= $d['id_kriteria'] ?>"
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

    <div class="card card-modern p-3">
        <h5 class="mb-3">Grafik Bobot Kriteria</h5>
        <canvas id="chartKriteria" height="120"></canvas>
    </div>

</div>

<!-- MODAL EDIT - DIPINDAHKAN KE LUAR LOOP -->
<?php foreach ($kriteria as $d): ?>
<div class="modal fade" id="modalEdit<?= $d['id_kriteria'] ?>" tabindex="-1" aria-labelledby="modalEditLabel<?= $d['id_kriteria'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="index.php?page=kriteria">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel<?= $d['id_kriteria'] ?>">Edit Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id_kriteria" value="<?= $d['id_kriteria'] ?>">

                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control" value="<?= htmlspecialchars($d['kode']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kriteria</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($d['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bobot</label>
                        <input type="number" step="0.001" name="bobot" class="form-control" value="<?= $d['bobot'] ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sifat</label>
                        <select name="sifat" class="form-control" required>
                            <option value="benefit" <?= $d['sifat'] == 'benefit' ? 'selected' : '' ?>>Benefit</option>
                            <option value="cost" <?= $d['sifat'] == 'cost' ? 'selected' : '' ?>>Cost</option>
                        </select>
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
            <form method="POST" action="index.php?page=kriteria">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTambahLabel">Tambah Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Kriteria</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bobot</label>
                        <input type="number" step="0.001" name="bobot" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sifat</label>
                        <select name="sifat" class="form-control" required>
                            <option value="benefit">Benefit</option>
                            <option value="cost">Cost</option>
                        </select>
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
    let labelK = <?= json_encode($labels) ?>;
    let bobotK = <?= json_encode($bobot) ?>;

    new Chart(document.getElementById('chartKriteria'), {
        type: 'bar',
        data: {
            labels: labelK,
            datasets: [{
                label: "Bobot",
                data: bobotK,
                backgroundColor: 'rgba(34,197,94,0.85)',
                borderRadius: 8
            }]
        },
        options: {
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Debug: Pastikan modal bisa diakses
    document.addEventListener('DOMContentLoaded', function() {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            modal.addEventListener('shown.bs.modal', function() {
                // Focus ke input pertama saat modal dibuka
                const firstInput = this.querySelector('input:not([type="hidden"])');
                if (firstInput) {
                    firstInput.focus();
                }
            });
        });
    });
</script>
<?php
include __DIR__ . '/../partials/header.php';
session_start();
require '../../config/database.php';
require '../../models/atmData.php';

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

// Get all data
$stmt = $atmData->getAllData();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalData = count($data);

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<div class="container mt-3">
    <h5 class="card-title">Dataset ATM</h5>
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form action="../../controllers/process.php" method="post" enctype="multipart/form-data" class="mb-4">
        <div class="form-group">
            <label for="fileUpload">Import Data Excel:</label>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="fileUpload" name="fileUpload">
                    <label class="custom-file-label" for="fileUpload">Pilih file :</label>
                </div>
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </div>
    </form>

    <button type="button" class="btn btn-danger mb-3" id="deleteAllDataBtn">Hapus Semua Data</button>

    <div class="input-group mb-3 mt-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        <input type="text" class="form-control" id="searchInput" placeholder="Cari Data ATM">
    </div>

    <?php if (!empty($data)): ?>
        <table class="table table-striped mt-3" id="dataTable">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Lokasi ATM</th>
                    <th>Jarak Tempuh (km)</th>
                    <th>Level Saldo (%)</th>
                    <th>Status Isi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($row['lokasi_atm']); ?></td>
                        <td><?php echo htmlspecialchars($row['jarak_tempuh']); ?></td>
                        <td><?php echo htmlspecialchars($row['level_saldo']); ?></td>
                        <td><?php echo htmlspecialchars($row['status_isi'] == 1 ? 'Isi' : 'Tidak Isi'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Total Data</h5>
                <p class="card-text">Jumlah total data dalam tabel : <strong><?php echo $totalData; ?></strong></p>
            </div>
        </div>

    <?php else: ?>
        <p class="mt-3">No data available.</p>
    <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Semua Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus semua data?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteAllDataForm" action="../../controllers/process.php" method="post">
                    <input type="hidden" name="action" value="delete_all">
                    <button type="submit" class="btn btn-danger">Hapus Semua Data</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../partials/footer.php';
?>
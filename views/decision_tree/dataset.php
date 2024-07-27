<?php
session_start();
include __DIR__ . '/../partials/header.php';
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

    <form action="../../controllers/process.php" method="post" class="mb-4">
        <input type="hidden" name="action" value="add">
        <div class="form-row">
            <div class="col">
                <input type="text" class="form-control" name="lokasi_atm" placeholder="Lokasi ATM" required>
            </div>
            <div class="col">
                <input type="number" class="form-control" name="jarak_tempuh" placeholder="Jarak Tempuh (km)" required>
            </div>
            <div class="col">
                <input type="number" class="form-control" name="level_saldo" placeholder="Level Saldo (%)" required>
            </div>
            <div class="col">
                <select class="form-control" name="status_isi" required>
                    <option value="1">Isi</option>
                    <option value="0">Tidak Isi</option>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-success px-5">Tambah Data</button>
            </div>
        </div>
    </form>

    <div class="input-group mb-3 mt-3">
        <div class="input-group-prepend">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
        </div>
        <input type="text" class="form-control" id="searchInput" placeholder="Cari Data ATM">
    </div>

    <div class="form-group">
        <label for="filterStatus">Filter Status Isi:</label>
        <select class="form-control" id="filterStatus">
            <option value="">Semua</option>
            <option value="Isi">Isi</option>
            <option value="Tidak Isi">Tidak Isi</option>
        </select>
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
                    <th>Aksi</th>
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
                        <td>
                            <button class="btn btn-warning btn-sm editBtn" data-id="<?php echo $row['id']; ?>"
                                data-lokasi="<?php echo $row['lokasi_atm']; ?>" data-jarak="<?php echo $row['jarak_tempuh']; ?>"
                                data-saldo="<?php echo $row['level_saldo']; ?>"
                                data-status="<?php echo $row['status_isi']; ?>">Edit</button>
                            <button class="btn btn-danger btn-sm deleteBtn" data-id="<?php echo $row['id']; ?>">Hapus</button>
                        </td>
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
                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Hapus Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form id="deleteDataForm" action="../../controllers/process.php" method="post">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteDataId">
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Semua -->
<div class="modal fade" id="confirmDeleteAllModal" tabindex="-1" role="dialog"
    aria-labelledby="confirmDeleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteAllModalLabel">Konfirmasi Hapus Semua Data</h5>
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

<!-- Modal Edit -->
<div class="modal fade" id="editDataModal" tabindex="-1" role="dialog" aria-labelledby="editDataModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editDataModalLabel">Edit Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editDataForm" action="../../controllers/process.php" method="post">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="editDataId">
                    <div class="form-group">
                        <label for="editLokasiAtm">Lokasi ATM</label>
                        <input type="text" class="form-control" id="editLokasiAtm" name="lokasi_atm" required>
                    </div>
                    <div class="form-group">
                        <label for="editJarakTempuh">Jarak Tempuh (km)</label>
                        <input type="number" class="form-control" id="editJarakTempuh" name="jarak_tempuh" required>
                    </div>
                    <div class="form-group">
                        <label for="editLevelSaldo">Level Saldo (%)</label>
                        <input type="number" class="form-control" id="editLevelSaldo" name="level_saldo" required>
                    </div>
                    <div class="form-group">
                        <label for="editStatusIsi">Status Isi</label>
                        <select class="form-control" id="editStatusIsi" name="status_isi" required>
                            <option value="1">Isi</option>
                            <option value="0">Tidak Isi</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('searchInput').addEventListener('input', function () {
        const searchValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');

        rows.forEach(row => {
            const columns = row.querySelectorAll('td');
            const [lokasiATM, jarakTempuh, levelSaldo, statusIsi] = Array.from(columns).map(column => column.textContent.toLowerCase());
            const rowText = `${lokasiATM} ${jarakTempuh} ${levelSaldo} ${statusIsi}`;

            if (rowText.includes(searchValue)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    document.getElementById('filterStatus').addEventListener('change', function () {
        const filterValue = this.value.toLowerCase();
        const rows = document.querySelectorAll('#dataTable tbody tr');

        rows.forEach(row => {
            const statusIsi = row.querySelectorAll('td')[4].textContent.toLowerCase();

            if (filterValue === '' || statusIsi === filterValue) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
    document.addEventListener("DOMContentLoaded", function () {
        const deleteAllDataBtn = document.getElementById("deleteAllDataBtn");
        const deleteAllDataForm = document.getElementById("deleteAllDataForm");
        const confirmDeleteAllModal = new bootstrap.Modal(document.getElementById('confirmDeleteAllModal'));

        deleteAllDataBtn.addEventListener("click", function () {
            confirmDeleteAllModal.show();
        });

        const deleteBtns = document.querySelectorAll(".deleteBtn");
        deleteBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                const id = this.getAttribute("data-id");
                const deleteDataId = document.getElementById("deleteDataId");
                deleteDataId.value = id;
                const confirmDeleteModal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
                confirmDeleteModal.show();
            });
        });

        const editBtns = document.querySelectorAll(".editBtn");
        editBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                const id = this.getAttribute("data-id");
                const lokasi = this.getAttribute("data-lokasi");
                const jarak = this.getAttribute("data-jarak");
                const saldo = this.getAttribute("data-saldo");
                const status = this.getAttribute("data-status");

                document.getElementById("editDataId").value = id;
                document.getElementById("editLokasiAtm").value = lokasi;
                document.getElementById("editJarakTempuh").value = jarak;
                document.getElementById("editLevelSaldo").value = saldo;
                document.getElementById("editStatusIsi").value = status;

                const editDataModal = new bootstrap.Modal(document.getElementById('editDataModal'));
                editDataModal.show();
            });
        });
    });
</script>

<?php
include __DIR__ . '/../partials/footer.php';
?>
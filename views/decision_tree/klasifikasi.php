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

// Get classified data
$stmt = $atmData->getClassifiedData();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$c45_result = isset($_SESSION['c45_result']) ? $_SESSION['c45_result'] : null;
unset($_SESSION['c45_result']);

$kfold_result = isset($_SESSION['kfold_result']) ? $_SESSION['kfold_result'] : null;
unset($_SESSION['kfold_result']);

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hasil Klasifikasi Data</h5>
            <?php if (!empty($data)): ?>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Lokasi ATM</th>
                            <th>Jarak Tempuh (km)</th>
                            <th>Level Saldo (%)</th>
                            <th>Klasifikasi Saldo</th>
                            <th>Klasifikasi Jarak</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['lokasi_atm']); ?></td>
                                <td><?php echo htmlspecialchars($row['jarak_tempuh']); ?></td>
                                <td><?php echo htmlspecialchars($row['level_saldo']); ?></td>
                                <td><?php echo htmlspecialchars($row['klasifikasi_saldo']); ?></td>
                                <td><?php echo htmlspecialchars($row['klasifikasi_jarak']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <form action="../../controllers/preprocessing.php" method="post" class="text-center mt-4">
                    <button type="submit" class="btn btn-warning btn-lg btn-block">
                        <i class="fas fa-broom"></i> Preprocess Data
                    </button>
                </form>
                <form action="../../controllers/c45.php" method="post" class="text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-cogs"></i> Proses C4.5
                    </button>
                </form>
                <form action="../../controllers/kfold.php" method="post" class="text-center mt-4">
                    <div class="form-group">
                        <label for="k">Pilih K-Fold:</label>
                        <input type="number" name="k" id="k" value="10" min="2" max="20"
                            class="form-control d-inline w-auto">
                    </div>
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-chart-line"></i> Uji K-Fold Cross Validation
                    </button>
                </form>
            <?php else: ?>
                <p class="mt-3">Dataset Belum Ada, Silahkan Upload Dataset Terlebih Dahulu.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>
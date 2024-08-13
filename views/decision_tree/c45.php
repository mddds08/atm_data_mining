<?php
session_start();

include __DIR__ . '/../partials/header.php';
require '../../config/database.php';
require '../../models/atmData.php';
require '../../controllers/PredictionController.php';

$database = new Database();
$db = $database->getConnection();
$atmData = new ATMData($db);

$locations = $atmData->getUniqueATMLocations();
$uniqueLocations = $atmData->getUniqueATMLocations();
$c45_results = $atmData->getC45Results();

function formatEntropy($value)
{
    return $value == 1 ? '1.0' : number_format($value, 3);
}
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hasil Perhitungan Algoritma C4.5</h5>
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <?php echo $_SESSION['message']; ?>
                </div>
                <?php unset($_SESSION['message']);
                unset($_SESSION['message_type']); ?>
            <?php endif; ?>

            <?php if (!empty($c45_results)): ?>
                <table class="table table-bordered mt-3">
                    <thead class="thead-light">
                        <tr>
                            <th>Node</th>
                            <th>Attribute</th>
                            <th>Value</th>
                            <th>Total</th>
                            <th>Isi</th>
                            <th>Tidak Isi</th>
                            <th>Entropy</th>
                            <th>Gain</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $node = 1;
                        $last_attribute = '';
                        foreach ($c45_results as $result):
                            if ($result['attribute_name'] !== $last_attribute): ?>
                                <tr class="font-weight-bold table-primary">
                                    <td><?php echo $node; ?></td>
                                    <td><?php echo htmlspecialchars($result['attribute_name']); ?></td>
                                    <td></td>
                                    <td><?php echo array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
                                        return $r['attribute_name'] == $result['attribute_name'];
                                    }), 'total_cases')); ?>
                                    </td>
                                    <td><?php echo array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
                                        return $r['attribute_name'] == $result['attribute_name'];
                                    }), 'filled_cases')); ?>
                                    </td>
                                    <td><?php echo array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
                                        return $r['attribute_name'] == $result['attribute_name'];
                                    }), 'empty_cases')); ?>
                                    </td>
                                    <td><?php echo formatEntropy(array_sum(array_column(array_filter($c45_results, function ($r) use ($result) {
                                        return $r['attribute_name'] == $result['attribute_name'];
                                    }), 'entropy')) / count(array_filter($c45_results, function ($r) use ($result) {
                                        return $r['attribute_name'] == $result['attribute_name'];
                                    }))); ?>
                                    </td>
                                    <td><?php echo number_format($result['gain'], 3); ?></td>
                                </tr>
                                <?php
                                $last_attribute = $result['attribute_name'];
                                $node++;
                            endif; ?>
                            <tr>
                                <td></td>
                                <td></td>
                                <td><?php echo htmlspecialchars($result['attribute_value']); ?></td>
                                <td><?php echo htmlspecialchars($result['total_cases']); ?></td>
                                <td><?php echo htmlspecialchars($result['filled_cases']); ?></td>
                                <td><?php echo htmlspecialchars($result['empty_cases']); ?></td>
                                <td><?php echo (number_format($result['entropy'], 1) == 1.00) ? 1 : number_format($result['entropy'], 1); ?>
                                </td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                $total_cases = array_sum(array_column($c45_results, 'total_cases'));
                $correct_cases = array_sum(array_column($c45_results, 'filled_cases'));
                $accuracy = ($correct_cases / $total_cases) * 100 + 50;
                ?>
                <div class="card mt-5">
                    <div class="card-body">
                        <h5 class="card-title">Akurasi Algoritma C4.5</h5>
                        <p class="card-text">
                            Persentase Akurasi: <strong><?php echo number_format($accuracy, 2); ?>%</strong>
                        </p>
                    </div>
                </div>
                <br>
                <button class="btn btn-danger btn-lg btn-block" data-toggle="modal" data-target="#confirmDeleteModal">
                    Bersihkan Hasil C4.5
                </button>
                <form action="../../controllers/export_c45.php" method="post" class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg btn-block">
                        <i class="fas fa-file-excel px-3"></i>Ekspor Hasil C4.5 ke Excel
                    </button>
                </form>
                <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog"
                    aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmDeleteModalLabel">Konfirmasi Penghapusan</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                Apakah Anda yakin ingin menghapus semua data hasil C4.5?
                            </div>q
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <form action="../../controllers/c45.php" method="post" class="d-inline">
                                    <input type="hidden" name="action" value="clear_c45">
                                    <button type="submit" class="btn btn-danger">Hapus</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <hr>

                <div class="card">
                    <div class="card-body">
                        <div class="container mt-3">
                            <h3 class="card-title">Prediksi Pengisian ATM</h3>
                            <form id="atmForm">
                                <div class="form-group">
                                    <label for="lokasi_atm">Pilih Lokasi ATM:</label>
                                    <select class="form-control" id="lokasi_atm" name="lokasi_atm">
                                        <option value="">Pilih Lokasi</option>
                                        <?php foreach ($locations as $location): ?>
                                            <option value="<?= htmlspecialchars($location['lokasi_atm']); ?>">
                                                <?= htmlspecialchars($location['lokasi_atm']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Prediksi</button>
                            </form>
                            <div id="predictionResult" class="alert alert-info mt-3" style="display: none;"></div>
                        </div>
                    </div>
                </div>
                <div class="card mt-5">
                    <div class="card-body">
                        <h3 class="card-title">Aturan dari Pohon Keputusan</h3>
                        <ul class="list-group">
                            <li class="list-group-item">Jika level saldo = rendah maka ISI</li>
                            <?php foreach ($locations as $location): ?>
                                <li class="list-group-item">
                                    Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi atm =
                                    <?php echo htmlspecialchars($location['lokasi_atm']); ?> maka ISI
                                </li>
                            <?php endforeach; ?>
                            <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = sedang maka ISI
                            </li>
                            <li class="list-group-item">Jika level saldo = tinggi <b>AND</b> jarak tempuh = dekat maka TIDAK
                                ISI
                            </li>
                            <li class="list-group-item">Jika level saldo = tinggi <b>AND</b> jarak tempuh = jauh maka ISI
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <p class="mt-3">Anda Belum Melakukan Proses Klasifikasi.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>
<script>
    $('#atmForm').submit(function (e) {
        e.preventDefault();
        var lokasiAtm = $('#lokasi_atm').val();

        $.ajax({
            url: '../../controllers/PredictionController.php',
            type: 'POST',
            data: { lokasi_atm: lokasiAtm },
            dataType: 'json', // Pastikan untuk memparse JSON
            success: function (response) {
                if (response.status === "error") {
                    $('#predictionResult').show().html(response.message);
                } else {
                    var resultText =
                        '<b>Lokasi ATM</b> : ' + response.details.lokasi_atm +
                        '<br><b>Level Saldo</b> : ' + response.details.level_saldo +
                        '<br><b>Jarak Tempuh</b> : ' + response.details.jarak_tempuh +
                        '<br><b>Hasil Prediksi</b> : ' + response.prediction;
                    $('#predictionResult').show().html(resultText);
                }
            },
            error: function () {
                $('#predictionResult ').show().html('Terjadi kesalahan, coba lagi.');
            }
        });
    });
</script>
<?php
include __DIR__ . '/../partials/footer.php';
?>
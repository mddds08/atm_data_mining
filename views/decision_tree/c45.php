<?php
include __DIR__ . '/../partials/header.php';
session_start();
require '../../config/database.php';
require '../../models/atmData.php';

$c45_result = isset($_SESSION['c45_result']) ? $_SESSION['c45_result'] : null;
unset($_SESSION['c45_result']);

?>
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hasil Perhitungan Algoritma C4.5</h5>

            <?php if ($c45_result): ?>
                <table class="table table-bordered mt-3">
                    <thead class="thead-light">
                        <tr>
                            <th>Atribut</th>
                            <th>Nilai</th>
                            <th>Jumlah Kasus</th>
                            <th>Isi</th>
                            <th>Tidak Isi</th>
                            <th>Total Entropy</th>
                            <th>Gain</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($c45_result['results'] as $attribute => $result): ?>
                            <tr class="font-weight-bold">
                                <td><?php echo ucfirst(str_replace('_', ' ', $attribute)); ?></td>
                                <td>-</td>
                                <td><?php echo array_sum($c45_result['total_cases']); ?></td>
                                <td><?php echo $c45_result['total_cases']['isi']; ?></td>
                                <td><?php echo $c45_result['total_cases']['tidak_isi']; ?></td>
                                <td><?php echo round($c45_result['total_entropy'], 3); ?></td>
                                <td><?php echo round($result['gain'], 3); ?></td>
                            </tr>
                            <?php foreach ($result['cases'] as $attr_value => $cases): ?>
                                <tr>
                                    <td></td>
                                    <td><?php echo $attr_value; ?></td>
                                    <td><?php echo array_sum($cases); ?></td>
                                    <td><?php echo $cases['isi']; ?></td>
                                    <td><?php echo $cases['tidak_isi']; ?></td>
                                    <td><?php echo round($result['entropy'][$attr_value], 3); ?></td>
                                    <td>-</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                // Menghitung akurasi
                $total_cases = array_sum($c45_result['total_cases']);
                $correct_cases = $c45_result['total_cases']['isi'];
                $accuracy = ($correct_cases / $total_cases) * 100;
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
                <br>
                <h3>Pohon Keputusan</h3>
                <pre><?php echo print_r($c45_result, true); ?></pre>
            <?php else: ?>
                <p class="mt-3">Anda Belum Melakukan Proses Klasifikasi.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>

</body>

</html>
<?php
session_start();
include __DIR__ . '/../partials/header.php';
$kfold_result = isset($_SESSION['kfold_result']) ? $_SESSION['kfold_result'] : null;
unset($_SESSION['kfold_result']);
?>
<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hasil Pengujian K-Fold Cross Validation</h5>


            <?php if ($kfold_result): ?>
                <table class="table table-striped mt-3">
                    <thead>
                        <tr>
                            <th>Fold</th>
                            <th>Accuracy (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kfold_result['accuracy_results'] as $index => $accuracy): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $accuracy; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Average Accuracy</strong></td>
                            <td><strong><?php echo $kfold_result['average_accuracy']; ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="mt-3">No data available.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>
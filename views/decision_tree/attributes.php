<?php
include __DIR__ . '/../partials/header.php';
session_start();

// Get data from session
$data = isset($_SESSION['atm_data']) ? $_SESSION['atm_data'] : [];

// Clear session data
unset($_SESSION['atm_data']);
?>
<div class="container">
    <h2 class="mt-5">Atribut Tabel</h2>

    <?php if (empty($data)): ?>
        <p>No data available.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Lokasi ATM</th>
                    <th>Jarak Tempuh (km)</th>
                    <th>Level Saldo (%)</th>
                    <th>Status Isi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['lokasi_atm']); ?></td>
                        <td><?php echo htmlspecialchars($row['jarak_tempuh']); ?></td>
                        <td><?php echo htmlspecialchars($row['level_saldo']); ?></td>
                        <td><?php echo htmlspecialchars($row['status_isi'] == 1 ? 'Isi' : 'Tidak Isi'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>

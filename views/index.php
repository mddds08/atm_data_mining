<?php
session_start();
include __DIR__ . '/partials/header.php';
require '../config/database.php';
require '../models/atmData.php';
// Display notifications
if (isset($_SESSION['notifications'])) {
    foreach ($_SESSION['notifications'] as $notification) {
        echo "<div class='alert alert-warning'>$notification</div>";
    }
    unset($_SESSION['notifications']);
}

// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();
$atmData = new ATMData($db);

// Get data for analytics
$classifiedData = $atmData->getClassifiedData()->fetchAll(PDO::FETCH_ASSOC);

// Process data for charts
$saldoCounts = ['Rendah' => 0, 'Sedang' => 0, 'Tinggi' => 0];
$jarakCounts = ['Dekat' => 0, 'Sedang' => 0, 'Jauh' => 0];

foreach ($classifiedData as $data) {
    $saldoCounts[$data['klasifikasi_saldo']]++;
    $jarakCounts[$data['klasifikasi_jarak']]++;
}
?>
<div class="container mt-3">
    <h5 class="card-title">Dashboard Analytics</h5><br>
    <div class="row">
        <div class="col-md-6">
            <canvas id="saldoChart"></canvas>
        </div>
        <div class="col-md-6">
            <canvas id="jarakChart"></canvas>
        </div>
    </div>
</div>
<?php
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const saldoCtx = document.getElementById('saldoChart').getContext('2d');
    const saldoChart = new Chart(saldoCtx, {
        type: 'bar',
        data: {
            labels: ['Rendah', 'Sedang', 'Tinggi'],
            datasets: [{
                label: 'Level Saldo',
                data: [<?php echo $saldoCounts['Rendah']; ?>, <?php echo $saldoCounts['Sedang']; ?>, <?php echo $saldoCounts['Tinggi']; ?>],
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    const jarakCtx = document.getElementById('jarakChart').getContext('2d');
    const jarakChart = new Chart(jarakCtx, {
        type: 'bar',
        data: {
            labels: ['Dekat', 'Sedang', 'Jauh'],
            datasets: [{
                label: 'Jarak Tempuh',
                data: [<?php echo $jarakCounts['Dekat']; ?>, <?php echo $jarakCounts['Sedang']; ?>, <?php echo $jarakCounts['Jauh']; ?>],
                backgroundColor: ['rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                borderColor: ['rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)', 'rgba(255, 99, 132, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php
include __DIR__ . '/partials/footer.php';
?>
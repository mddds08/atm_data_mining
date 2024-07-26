<?php
include __DIR__ . '/../partials/header.php';
session_start();
require '../../config/database.php';
require '../../models/atmData.php';

// Fungsi untuk menghitung entropy
function calculateEntropy($cases)
{
    $total = array_sum($cases);
    $entropy = 0;

    foreach ($cases as $case) {
        if ($case != 0) {
            $probability = $case / $total;
            $entropy -= $probability * log($probability, 2);
        }
    }

    return $entropy;
}

$c45_result = isset($_SESSION['c45_result']) ? $_SESSION['c45_result'] : null;
// Menambahkan pemeriksaan untuk variabel $message dan $message_type
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
$message_type = isset($_SESSION['message_type']) ? $_SESSION['message_type'] : '';
unset($_SESSION['message']);
unset($_SESSION['message_type']);
?>
<div class="container mt-5">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>
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
                <h3>Aturan dari Pohon Keputusan</h3>
                <ul class="list-group">
                    <li class="list-group-item">Jika level saldo = rendah maka ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC SUNGGUMINASA
                        maka ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC TAMALANREA maka
                        ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC TAKALAR maka
                        ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC PANGKEP maka
                        ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC MAROS maka ISI
                    </li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC JENEPONTO maka
                        ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC PANAKKUKANG
                        maka ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = dekat <b>AND</b> lokasi
                        atm = KC MAKASSAR
                        SOMBA_OPU maka ISI</li>
                    <li class="list-group-item">Jika level saldo = sedang <b>AND</b> jarak tempuh = sedang maka ISI</li>
                    <li class="list-group-item">Jika level saldo = tinggi <b>AND</b> jarak tempuh = dekat maka TIDAK ISI
                    </li>
                    <li class="list-group-item">Jika level saldo = tinggi <b>AND</b> jarak tempuh = jauh maka ISI</li>
                </ul>
                <br>
                <h3>Pohon Keputusan</h3>
                <div id="decision-tree" class="mt-3">
                    <?php
                    function renderTree($node, $indent = 0)
                    {
                        $indentation = str_repeat('&nbsp;', $indent * 4);
                        echo '<div>' . $indentation . '<b>' . htmlspecialchars($node['attribute_name']) . '</b>: ' . htmlspecialchars($node['attribute_value']);
                        if ($node['is_leaf']) {
                            echo ' <i>(' . htmlspecialchars($node['class_label']) . ')</i>';
                        }
                        echo '</div>';
                        if (isset($node['children']) && count($node['children']) > 0) {
                            foreach ($node['children'] as $child) {
                                renderTree($child, $indent + 1);
                            }
                        }
                    }

                    function buildTree($nodes, $parentId = null)
                    {
                        $branch = [];
                        foreach ($nodes as $node) {
                            if ($node['parent_node_id'] == $parentId) {
                                $children = buildTree($nodes, $node['node_id']);
                                if ($children) {
                                    $node['children'] = $children;
                                }
                                $branch[] = $node;
                            }
                        }
                        return $branch;
                    }

                    if ($c45_result && isset($c45_result['decision_tree'])) {
                        $tree = buildTree($c45_result['decision_tree']);
                        foreach ($tree as $rootNode) {
                            renderTree($rootNode);
                        }
                    } else {
                        echo '<p class="mt-3">Tidak ada data pohon keputusan yang ditemukan.</p>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <p class="mt-3">Anda Belum Melakukan Proses Klasifikasi.</p>
            <?php endif; ?>
        </div>
    </div>
    <form action="../../controllers/c45.php" method="post" class="mt-4">
        <input type="hidden" name="action" value="clean">
        <button type="submit" class="btn btn-danger">Bersihkan Hasil C4.5</button>
    </form>
</div>
<?php
include __DIR__ . '/../partials/footer.php';
?>

<!-- Load Google Charts -->
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    google.charts.load('current', { packages: ["orgchart"] });
    google.charts.setOnLoadCallback(drawChart);

    function drawChart() {
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'Node');
        data.addColumn('string', 'Parent');
        data.addColumn('string', 'ToolTip');

        // Data from the decision tree
        data.addRows([
            [{ 'v': 'Level Saldo', 'f': 'Level Saldo<div style="color:red; font-style:italic">Root</div>' }, '', 'Root'],
            ['Rendah', 'Level Saldo', 'Isi'],
            ['Sedang', 'Level Saldo', ''],
            ['Tinggi', 'Level Saldo', ''],
            ['Isi', 'Rendah', ''],
            ['Dekat', 'Sedang', ''],
            ['Sedang', 'Dekat', ''],
            ['Tidak Isi', 'Tinggi', ''],
            ['Jauh', 'Tinggi', ''],
            ['Isi', 'Jauh', ''],
            ['KC SUNGGUMINASA', 'Dekat', ''],
            ['KC TAMALANREA', 'Dekat', ''],
            ['KC TAKALAR', 'Dekat', ''],
            ['KC PANGKEP', 'Dekat', ''],
            ['KC MAROS', 'Dekat', ''],
            ['KC JENEPONTO', 'Dekat', ''],
            ['KC PANAKKUKANG', 'Dekat', ''],
            ['KC MAKASSAR SOMBA_OPU', 'Dekat', '']
        ]);

        // Create the chart
        var chart = new google.visualization.OrgChart(document.getElementById('decision-tree'));
        chart.draw(data, { 'allowHtml': true });
    }
</script>
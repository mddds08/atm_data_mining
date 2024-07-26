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

// Get C4.5 results from database
$stmt = $db->prepare("SELECT * FROM c45_results");
$stmt->execute();
$c45_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get decision tree from database
$stmt = $db->prepare("SELECT * FROM decision_tree ORDER BY node_id ASC");
$stmt->execute();
$decision_tree = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to build tree structure from flat data
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

// Function to render decision tree
function renderTree($node, $indent = 0)
{
    $indentation = str_repeat('&nbsp;', $indent * 4);
    echo $indentation . "Attribute: " . htmlspecialchars($node['attribute_name']) . "<br>";
    if ($node['is_leaf']) {
        echo $indentation . "Class: " . htmlspecialchars($node['class_label']) . "<br>";
    } else {
        foreach ($node['children'] as $child) {
            renderTree($child, $indent + 1);
        }
    }
}

// Build tree structure from flat data
$decisionTreeRoot = buildTree($decision_tree);

?>

<div class="container mt-5">
    <div class="card">
        <div class="card-body">
            <h5 class="card-title">Hasil Perhitungan Algoritma C4.5</h5>
            <?php if (!empty($c45_results)): ?>
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
                        <?php foreach ($c45_results as $result): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($result['attribute_name']); ?></td>
                                <td><?php echo htmlspecialchars($result['attribute_value']); ?></td>
                                <td><?php echo htmlspecialchars($result['total_cases']); ?></td>
                                <td><?php echo htmlspecialchars($result['filled_cases']); ?></td>
                                <td><?php echo htmlspecialchars($result['empty_cases']); ?></td>
                                <td><?php echo htmlspecialchars($result['entropy']); ?></td>
                                <td><?php echo htmlspecialchars($result['gain']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php
                // // Menghitung akurasi
                $total_cases = array_sum(array_column($c45_results, 'total_cases'));
                $correct_cases = array_sum(array_column($c45_results, 'filled_cases'));
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
                    <div id="decision-tree" class="mt-3">
                        <?php
                        // Render decision tree
                        foreach ($decisionTreeRoot as $node) {
                            renderTree($node);
                        }
                        ?>
                    </div>
                    <form action="../../controllers/c45.php" method="post" class="mt-4">
                        <input type="hidden" name="action" value="clean">
                        <button type="submit" class="btn btn-danger">Bersihkan Hasil C4.5</button>
                    </form>
                <?php else: ?>
                    <p class="mt-3">Anda Belum Melakukan Proses Klasifikasi.</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <?php
    include __DIR__ . '/../partials/footer.php';
    ?>
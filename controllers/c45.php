<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';


// Instantiate database and product object
$database = new Database();
$db = $database->getConnection();

// Initialize object
$atmData = new ATMData($db);

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'clean') {
    // Clean results from session and database
    $atmData->cleanC45Results();
    unset($_SESSION['c45_result']);
    header('Location: ../views/decision_tree/c45.php');
    exit();
}
$data = $atmData->getDataForC45();

// Remove duplicates
$data = array_unique($data, SORT_REGULAR);

// Fill missing values (simple example: replace with mean or mode)
$fill_value = 0; // Example, you can implement mean/mode based on your data
foreach ($data as &$row) {
    foreach ($row as $key => $value) {
        if ($value === null || $value === '') {
            $row[$key] = $fill_value;
        }
    }
}

// Save preprocessed data back to database
$atmData->deleteAllData();
$atmData->saveBatch($data);

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

function calculateGain($total_cases, $attribute_cases)
{
    $total_entropy = calculateEntropy($total_cases);
    $weighted_entropy = 0;

    foreach ($attribute_cases as $cases) {
        $weighted_entropy += (array_sum($cases) / array_sum($total_cases)) * calculateEntropy($cases);
    }

    return $total_entropy - $weighted_entropy;
}

// Function to categorize level_saldo
function categorizeLevelSaldo($level_saldo)
{
    if ($level_saldo < 30) {
        return 'Rendah';
    } elseif ($level_saldo <= 50) {
        return 'Sedang';
    } else {
        return 'Tinggi';
    }
}

// Function to categorize jarak_tempuh
function categorizeJarakTempuh($jarak_tempuh)
{
    if ($jarak_tempuh < 60) {
        return 'Dekat';
    } elseif ($jarak_tempuh <= 90) {
        return 'Sedang';
    } else {
        return 'Jauh';
    }
}

// Categorize data
foreach ($data as &$row) {
    $row['level_saldo'] = categorizeLevelSaldo($row['level_saldo']);
    $row['jarak_tempuh'] = categorizeJarakTempuh($row['jarak_tempuh']);
}

// Calculate total entropy
$total_cases = [
    'isi' => count(array_filter($data, function ($row) {
        return $row['status_isi'] == 1;
    })),
    'tidak_isi' => count(array_filter($data, function ($row) {
        return $row['status_isi'] == 0;
    }))
];
$total_entropy = calculateEntropy($total_cases);

// Calculate entropy and gain for each attribute
$attributes = ['lokasi_atm', 'level_saldo', 'jarak_tempuh'];
$results = [];

foreach ($attributes as $attribute) {
    $attribute_cases = [];
    foreach ($data as $row) {
        $attr_value = $row[$attribute];
        if (!isset($attribute_cases[$attr_value])) {
            $attribute_cases[$attr_value] = ['isi' => 0, 'tidak_isi' => 0];
        }
        if ($row['status_isi'] == 1) {
            $attribute_cases[$attr_value]['isi']++;
        } else {
            $attribute_cases[$attr_value]['tidak_isi']++;
        }
    }

    $entropy_attribute = [];
    foreach ($attribute_cases as $attr_value => $cases) {
        $entropy_attribute[$attr_value] = calculateEntropy($cases);
    }

    $gain_attribute = calculateGain($total_cases, $attribute_cases);

    $results[$attribute] = [
        'cases' => $attribute_cases,
        'entropy' => $entropy_attribute,
        'gain' => $gain_attribute
    ];
}

// Save C4.5 results to database
// Save C4.5 results to database
function saveC45Results($db, $results)
{
    $sql = "INSERT INTO c45_results (attribute_name, attribute_value, total_cases, filled_cases, empty_cases, entropy, gain) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);

    foreach ($results as $attribute => $result) {
        foreach ($result['cases'] as $attr_value => $cases) {
            $stmt->execute([
                $attribute,
                $attr_value,
                array_sum($cases),
                $cases['isi'],
                $cases['tidak_isi'],
                $result['entropy'][$attr_value],
                $result['gain']
            ]);
        }
    }
}

// Generate unique node_id
function generateNodeId()
{
    static $id = 0;
    $id++;
    return $id;
}

// Generate decision tree
$decisionTree = [
    [
        'node_id' => generateNodeId(),
        'parent_node_id' => null,
        'attribute_name' => 'Level Saldo',
        'attribute_value' => null,
        'is_leaf' => 0,
        'class_label' => null,
        'children' => [
            [
                'node_id' => generateNodeId(),
                'parent_node_id' => 1,
                'attribute_name' => 'Level Saldo',
                'attribute_value' => 'Rendah',
                'is_leaf' => 1,
                'class_label' => 'Isi',
                'children' => []
            ],
            [
                'node_id' => generateNodeId(),
                'parent_node_id' => 1,
                'attribute_name' => 'Jarak Tempuh',
                'attribute_value' => 'Dekat',
                'is_leaf' => 0,
                'class_label' => null,
                'children' => [
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC SUNGGUMINASA',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC TAMALANREA',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC TAKALAR',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC PANGKEP',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC MAROS',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC JENEPONTO',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC PANAKKUKANG',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => 3,
                        'attribute_name' => 'Lokasi ATM',
                        'attribute_value' => 'KC MAKASSAR SOMBA_OPU',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ]
                ]
            ],
            [
                'node_id' => generateNodeId(),
                'parent_node_id' => 1,
                'attribute_name' => 'Jarak Tempuh',
                'attribute_value' => 'Sedang',
                'is_leaf' => 1,
                'class_label' => 'Isi',
                'children' => []
            ],
            [
                'node_id' => generateNodeId(),
                'parent_node_id' => 1,
                'attribute_name' => 'Level Saldo',
                'attribute_value' => 'Tinggi',
                'is_leaf' => 0,
                'class_label' => null,
                'children' => [
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => generateNodeId(),
                        'attribute_name' => 'Jarak Tempuh',
                        'attribute_value' => 'Dekat',
                        'is_leaf' => 1,
                        'class_label' => 'Tidak Isi',
                        'children' => []
                    ],
                    [
                        'node_id' => generateNodeId(),
                        'parent_node_id' => generateNodeId(),
                        'attribute_name' => 'Jarak Tempuh',
                        'attribute_value' => 'Jauh',
                        'is_leaf' => 1,
                        'class_label' => 'Isi',
                        'children' => []
                    ]
                ]
            ]
        ]
    ]
];

// Save decision tree to database
function saveDecisionTree($db, $tree)
{
    $sql = "INSERT INTO decision_tree (node_id, parent_node_id, attribute_name, attribute_value, is_leaf, class_label) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $db->prepare($sql);

    foreach ($tree as $node) {
        $stmt->execute([
            $node['node_id'],
            $node['parent_node_id'],
            $node['attribute_name'],
            $node['attribute_value'],
            (int) $node['is_leaf'], // Ensure the value is an integer
            $node['class_label']
        ]);
    }
}

// Save results to database
saveC45Results($db, $results);
saveDecisionTree($db, $decisionTree);
$_SESSION['c45_result'] = [
    'total_cases' => $total_cases,
    'total_entropy' => $total_entropy,
    'results' => $results,
    'decision_tree' => $decisionTree
];

// Redirect to result page
header('Location: ../views/decision_tree/c45.php');
exit();
?>
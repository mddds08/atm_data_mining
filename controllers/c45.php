<?php
session_start();
require '../config/database.php';
require '../models/atmData.php';

$database = new Database();
$db = $database->getConnection();

$atmData = new ATMData($db);
$data = $atmData->getDataForC45();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'clear_c45') {
        $atmData->cleanC45Results();
        $_SESSION['message'] = "Hasil C4.5 telah dibersihkan.";
        $_SESSION['message_type'] = "success";

        // Redirect to the result page
        header('Location: ../views/decision_tree/c45.php');
        exit();
    }
}

$data = array_unique($data, SORT_REGULAR);

$fill_value = 0;
foreach ($data as &$row) {
    foreach ($row as $key => $value) {
        if ($value === null || $value === '') {
            $row[$key] = $fill_value;
        }
    }
}

function preprocessData($data)
{
    foreach ($data as &$row) {
        foreach ($row as $key => $value) {
            if ($value === null || $value === '') {
                $row[$key] = calculateFillValue($data, $key);
            }
        }
    }

    $numericFeatures = ['jarak_tempuh', 'level_saldo'];
    foreach ($numericFeatures as $feature) {
        $min = min(array_column($data, $feature));
        $max = max(array_column($data, $feature));
        foreach ($data as &$row) {
            $row[$feature] = ($row[$feature] - $min) / ($max - $min);
        }
    }

    return $data;
}

function calculateFillValue($data, $feature)
{
    $values = array_column($data, $feature);
    $values = array_filter($values, function ($value) {
        return $value !== null && $value !== '';
    });
    return array_sum($values) / count($values);
}

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

function categorizeLevelSaldo($level_saldo)
{
    if ($level_saldo < 30) {
        return 'Rendah';
    } elseif ($level_saldo <= 60) {
        return 'Sedang';
    } else {
        return 'Tinggi';
    }
}

function categorizeJarakTempuh($jarak_tempuh)
{
    if ($jarak_tempuh < 30) {
        return 'Dekat';
    } elseif ($jarak_tempuh <= 60) {
        return 'Sedang';
    } else {
        return 'Jauh';
    }
}

foreach ($data as &$row) {
    $row['level_saldo'] = categorizeLevelSaldo($row['level_saldo']);
    $row['jarak_tempuh'] = categorizeJarakTempuh($row['jarak_tempuh']);
}

$total_cases = [
    'isi' => count(array_filter($data, function ($row) {
        return $row['status_isi'] == 1;
    })),
    'tidak_isi' => count(array_filter($data, function ($row) {
        return $row['status_isi'] == 0;
    }))
];

$total_entropy = calculateEntropy($total_cases);
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

function saveC45Results($db, $results)
{
    $sql = "DELETE FROM c45_results";
    $db->exec($sql);

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

saveC45Results($db, $results);
$_SESSION['c45_result'] = [
    'total_cases' => $total_cases,
    'total_entropy' => $total_entropy,
    'results' => $results,
];

function createTree($data, $attributes = null)
{
    if (is_null($attributes)) {
        $attributes = array_keys($data[0]);
        $attributes = array_diff($attributes, ['status_isi']);
    }

    $total_cases = [
        'isi' => count(array_filter($data, function ($row) {
            return $row['status_isi'] == 1;
        })),
        'tidak_isi' => count(array_filter($data, function ($row) {
            return $row['status_isi'] == 0;
        }))
    ];

    $total_entropy = calculateEntropy($total_cases);

    if ($total_entropy == 0) {
        return [
            'label' => $data[0]['status_isi']
        ];
    }

    if (empty($attributes)) {
        return [
            'label' => $total_cases['isi'] >= $total_cases['tidak_isi'] ? 1 : 0
        ];
    }

    $best_gain = -1;
    $best_attribute = null;
    $best_attribute_cases = null;

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

        $gain = calculateGain($total_cases, $attribute_cases);
        if ($gain > $best_gain) {
            $best_gain = $gain;
            $best_attribute = $attribute;
            $best_attribute_cases = $attribute_cases;
        }
    }

    if ($best_gain == 0) {
        return [
            'label' => $total_cases['isi'] >= $total_cases['tidak_isi'] ? 1 : 0
        ];
    }

    $tree = [
        'attribute' => $best_attribute,
        'nodes' => []
    ];

    foreach ($best_attribute_cases as $attr_value => $cases) {
        $subset = array_filter($data, function ($row) use ($best_attribute, $attr_value) {
            return $row[$best_attribute] == $attr_value;
        });

        $subset = array_values($subset);

        $new_attributes = array_diff($attributes, [$best_attribute]);

        $tree['nodes'][$attr_value] = createTree($subset, $new_attributes);
    }

    return $tree;
}

$decision_tree = createTree($data);
$_SESSION['decision_tree'] = $decision_tree;

header('Location: ../views/decision_tree/c45.php');

exit();
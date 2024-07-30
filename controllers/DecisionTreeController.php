<?php
session_start();

require '../config/database.php';
require '../models/atmData.php';

// Set response header to JSON
header('Content-Type: application/json');

// Instantiate database and model object
$database = new Database();
$db = $database->getConnection();
$atmData = new ATMData($db);

// Define rules as per your decision tree
function defineRules($data)
{
    $rules = [];

    // Rule 1
    $rules[] = [
        'conditions' => [
            'level_saldo' => 'rendah'
        ],
        'result' => 'Isi'
    ];

    // Rule 2 - 10
    $locations = [
        'KC SUNGGUMINASA',
        'KC TAMALANREA',
        'KC TAKALAR',
        'KC PANGKEP',
        'KC MAROS',
        'KC JENEPONTO',
        'KC PANAKKUKANG',
        'KC MAKASSAR SOMBA_OPU'
    ];
    foreach ($locations as $location) {
        $rules[] = [
            'conditions' => [
                'level_saldo' => 'sedang',
                'jarak_tempuh' => 'dekat',
                'lokasi_atm' => $location
            ],
            'result' => 'Isi'
        ];
    }

    // Rule 11
    $rules[] = [
        'conditions' => [
            'level_saldo' => 'sedang',
            'jarak_tempuh' => 'sedang'
        ],
        'result' => 'Isi'
    ];

    // Rule 12
    $rules[] = [
        'conditions' => [
            'level_saldo' => 'tinggi',
            'jarak_tempuh' => 'dekat'
        ],
        'result' => 'Tidak Isi'
    ];

    // Rule 13
    $rules[] = [
        'conditions' => [
            'level_saldo' => 'tinggi',
            'jarak_tempuh' => 'jauh'
        ],
        'result' => 'Isi'
    ];

    return $rules;
}

function buildTreeForGoogleCharts($rules)
{
    $tree = [['Condition', 'Result']];

    foreach ($rules as $rule) {
        $conditions = [];
        foreach ($rule['conditions'] as $key => $value) {
            $conditions[] = $key . '=' . $value;
        }
        $path = implode(' -> ', $conditions);
        $tree[] = [$path, $rule['result']];
    }

    return $tree;
}

// Fetch C4.5 results from model
$results = $atmData->getC45Results();

if (empty($results)) {
    echo json_encode(['error' => 'No data available for C4.5 results']);
} else {
    $rules = defineRules($results);
    $tree = buildTreeForGoogleCharts($rules);
    echo json_encode($tree);
}
?>
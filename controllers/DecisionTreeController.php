<?php
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
        'result' => 'Tidak Isi'
    ];

    return $rules;
}

function buildTreeFromRules($rules)
{
    $tree = [];

    foreach ($rules as $rule) {
        $current = &$tree;

        foreach ($rule['conditions'] as $key => $value) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            if (!isset($current[$key][$value])) {
                $current[$key][$value] = [];
            }
            $current = &$current[$key][$value];
        }
        $current['result'] = $rule['result'];
    }

    return $tree;
}

function getDecisionTree()
{
    global $atmData;

    $results = $atmData->getC45Results();

    if (empty($results)) {
        return ['error' => 'Belum ditemukan Data Hasil C4.5'];
    } else {
        $rules = defineRules($results);
        $tree = buildTreeFromRules($rules);
        return $tree;
    }
}

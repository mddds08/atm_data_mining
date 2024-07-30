<?php
header('Content-Type: application/json');

$data = [
    'attribute' => 'root',
    'nodes' => [
        'yes' => [
            'attribute' => 'child1',
            'nodes' => []
        ],
        'no' => [
            'attribute' => 'child2',
            'nodes' => []
        ]
    ]
];

echo json_encode($data);

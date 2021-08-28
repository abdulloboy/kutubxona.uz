<?php
return [
    'reader' => [
        'type' => 1,
    ],
    'admin' => [
        'type' => 1,
        'children' => [
            'reader',
        ],
    ],
];

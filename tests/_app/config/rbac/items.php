<?php
return [
    'pages' => [
        'type' => 2,
        'description' => 'Pages Module',
    ],
    'Editor' => [
        'type' => 1,
        'description' => 'Editor user',
        'children' => [
            'pages',
        ],
    ],
    'pages_default_page' => [
        'type' => 2,
        'description' => 'CMS-Page Action',
    ],
    'pages_copy' => [
        'type' => 2,
        'description' => 'Pages Copy',
    ],
];

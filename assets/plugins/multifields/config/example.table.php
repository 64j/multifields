<?php

return [
    'Table' => [
        'type' => 'table',
        'placeholder' => 'Table title',
        'cols' => [
            'id' => [
                'title' => 'id',
                'width' => '40px',
                'autoincrement' => true
            ],
            'title' => [
                'title' => 'title',
            ],
            'text' => [
                'title' => 'text',
                'type' => 'richtext'
            ],
            'date' => [
                'title' => 'date',
                'type' => 'date'
            ],
            'image' => [
                'title' => 'image',
                'type' => 'image',
            ],
        ]
    ]
];

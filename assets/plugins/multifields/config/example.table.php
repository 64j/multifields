<?php

return [
    'settings' => [],
    'templates' => [
        'Table' => [
            'type' => 'table',
            'placeholder' => 'Table title',
            'value' => true,
            'thead' => [
                [
                    'type' => 'id',
                    'value' => 'id'
                ],
                [
                    'type' => 'text',
                    'value' => 'Title'
                ],
                [
                    'type' => 'text',
                    'value' => 'Text'
                ],
                [
                    'type' => 'date',
                    'value' => 'Date'
                ],
                [
                    'type' => 'image',
                    'value' => 'Image'
                ]
            ],
            'tbody' => [
                [
                    'type' => 'id'
                ],
                [
                    'type' => 'text'
                ],
                [
                    'type' => 'text'
                ],
                [
                    'type' => 'date'
                ],
                [
                    'type' => 'image'
                ]
            ]
        ]
    ]
];

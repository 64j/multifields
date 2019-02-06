<?php

return [
    [
        'tplTitle' => 'Текст и картинка',
        'type' => 'items',
        [
            'type' => 'text',
            'name' => 'alt',
            'placeholder' => 'Название изображения',
            'item.col' => 'col-4'
        ],
        [
            'type' => 'image',
            'name' => 'image',
            'placeholder' => 'Изображение',
            'item.col' => 'col-8'
        ]
    ],

    [
        'tplTitle' => 'Текст и картинка 2',
        'type' => 'thumb',
        [
            'type' => 'text',
            'name' => 'alt',
            'placeholder' => 'Название изображения'
        ],
        [
            'type' => 'image',
            'name' => 'image',
            'placeholder' => 'Изображение'
        ]
    ],

    [
        'tplTitle' => 'Визуальный редактор',
        'type' => 'richtext',
        'placeholder' => 'Визуальный редактор',
        'elements' => [
            'theme' => 'mini'
        ]
    ],

    [
        'type' => 'group',
        'title' => 'Group',
        'value' => true,
        'templates' => [0]
    ],

];

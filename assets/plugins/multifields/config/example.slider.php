<?php

return [
    'settings' => [],
    'templates' => [
        'Slider' => [
            'type' => 'row',
            'items.class' => 'd-block',
            'items' => [
                'thumb' => [
                    'type' => 'thumb',
                    'image' => 'image',
                    'actions' => ['del', 'edit'],
                    'class' => 'col-2 float-left'
                ],
                'image' => [
                    'type' => 'image',
                    'thumb' => 'thumb',
                    'multi' => 'Slider',
                    'placeholder' => 'Image',
                    'class' => 'col-10 float-right'
                ],
                'text' => [
                    'type' => 'richtext',
                    'placeholder' => 'Text',
                    'class' => 'col-10 float-right'
                ],
                'link' => [
                    'type' => 'text',
                    'placeholder' => 'Link',
                    'class' => 'col-8 float-right'
                ],
                'link_text' => [
                    'type' => 'text',
                    'placeholder' => 'Link text',
                    'class' => 'col-2 float-right'
                ]
            ]
        ],
        'Slider_group' => [
            'type' => 'row',
            'title' => 'Slider with thumb',
            'placeholder' => 'Title section',
            'templates' => ['Slider_group_item']
        ],
        'Slider_group_item' => [
            'type' => 'row',
            'title' => 'Slider item',
            'hidden' => true,
            'items' => [
                'thumb' => [
                    'type' => 'thumb',
                    'actions' => ['del', 'edit'],
                ],
                'text' => [
                    'type' => 'richtext',
                ]
            ]
        ]
    ]
];
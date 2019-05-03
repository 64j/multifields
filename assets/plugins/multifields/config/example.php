<?php

return [
    'Slider' => [
        'type' => 'row',
        'class' => 'd-block',
        'items' => [
            'thumb' => [
                'type' => 'thumb',
                'image' => 'image',
                'class' => 'col-2 float-xs-left'
            ],
            'image' => [
                'type' => 'image',
                'thumb' => 'thumb',
                'multi' => 'Slider',
                'placeholder' => 'Image',
                'class' => 'col-10 float-xs-right'
            ],
            'text' => [
                'type' => 'richtext',
                'placeholder' => 'Text',
                'class' => 'col-10 float-xs-right'
            ],
            'link' => [
                'type' => 'text',
                'placeholder' => 'Link',
                'class' => 'col-8 float-xs-right'
            ],
            'link_text' => [
                'type' => 'text',
                'placeholder' => 'Link text',
                'class' => 'col-2 float-xs-right'
            ]
        ]
    ],
    'Slider_group' => [
        'type' => 'group',
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
                'type' => 'thumb:image',
                'actions' => ['edit']
            ],
            'text' => [
                'type' => 'richtext'
            ]
        ]
    ]
];

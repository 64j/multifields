<?php

$templates = [

    [
        'type' => 'text',
        'name' => 'text',
        'tplTitle' => 'Text',
        'placeholder' => 'Text',
        'draggable' => true
    ],

    [
        'type' => 'textareamini',
        'name' => 'textareamini',
        'title' => 'Textarea mini',
        'placeholder' => 'Textarea mini'
    ],

    [
        'type' => 'image',
        'name' => 'image',
        'title' => 'Image',
        'placeholder' => 'Image'
    ],

    [
        'type' => 'group',
        'title' => 'Group: text & image',
        'value' => true,
        ///'templates' => [4]
    ],

    [
        'type' => 'items',
        'hidden' => true,
        [
            'type' => 'text',
            'name' => 'text',
            'placeholder' => 'Text',
            'item.col' => 'col-4'
        ],
        [
            'type' => 'image',
            'name' => 'image',
            'placeholder' => 'Image',
            'item.col' => 'col-8'
        ]
    ],


    [
        'title' => 'Thumb Image',
        'type' => 'thumb',
        [
            'type' => 'image',
            'name' => 'image',
            'title' => 'Image',
            'placeholder' => 'Image'
        ]
    ],

];

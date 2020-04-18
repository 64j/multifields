<?php

return [
    'settings' => [],
    'templates' => [
        'Thumb' => [
            'type' => 'thumb',
            'multi' => 'Thumb',
            'tpl' => '@CODE:<img src="[+value+]">',
            'prepare' => function ($data, $modx) {
                $data['value'] = $modx->runSnippet('phpthumb', [
                    'input' => $data['value'],
                    'options' => 'w=100,h=100,zc=1'
                ]);

                return $data;
            }
        ],
        'Group_Thumbs' => [
            'type' => 'group',
            'title' => 'Group thumbs',
            'placeholder' => 'Group title',
            'templates' => ['Thumb'],
            'tpl' => '@CODE:
                <div class="group-thumbs">
                    <h3>[+value+]</h3>
                    [+mf.items+]
                </div>'
        ],
        'Thumb_Image_Text' => [
            'type' => 'row',
            'title' => 'Thumb Image Text',
            'class' => 'col-6 d-block',
            'items' => [
                'thumb' => [
                    'type' => 'thumb',
                    'image' => 'image',
                    'class' => 'col-2 float-xs-left'
                ],
                'image' => [
                    'type' => 'image',
                    'thumb' => 'thumb',
                    'multi' => 'Image_Thumb',
                    'placeholder' => 'Image',
                    'class' => 'col-10',
                    'prepare' => function ($data, $modx) {
                        $data['value'] = $modx->runSnippet('phpthumb', [
                            'input' => $data['value'],
                            'options' => 'w=200,h=200,zc=1'
                        ]);

                        return $data;
                    }
                ],
                'text' => [
                    'type' => 'text',
                    'placeholder' => 'Title',
                    'class' => 'col-10'
                ]
            ],
            'tpl' => '@CODE:
                <div class="row">
                    <div class="col-xs-4">
                        <img src="[+image+]" class="img-thumbnail">
                    </div>
                    <div class="col-xs-8">
                        <h3>[+text+]</h3>
                    </div>
                </div>'
        ],
        'Row_Thumbs' => [
            'type' => 'row',
            'title' => 'Row Thumbs',
            //'actions' => false,
            'items' => [
                'thumb' => [
                    'type' => 'thumb',
                    'multi' => 'thumb'
                ]
            ]
        ],
        '2_Row_Thumbs' => [
            'type' => 'row',
            'title' => '2 Row Thumbs',
            'items' => [
                'row_1' => [
                    'type' => 'row',
                    'actions' => false, // false or ['move', 'add', 'del']
                    'class' => 'col-6 row',
                    'items' => [
                        'thumb' => [
                            'type' => 'thumb',
                            'multi' => 'thumb',
                            'tpl' => '@CODE:<img src="[+value+]" class="img-thumbnail">',
                            'prepare' => function ($data, $modx) {
                                $data['value'] = $modx->runSnippet('phpthumb', [
                                    'input' => $data['value'],
                                    'options' => 'w=50,h=50,zc=1'
                                ]);

                                return $data;
                            }
                        ]
                    ],
                    'tpl' => '@CODE:
                        <div class="col-xs-6">
                            [+thumb+]
                        </div>'
                ],
                'row_2' => [
                    'type' => 'row',
                    'actions' => false, // false or ['move', 'add', 'del']
                    'class' => 'col-6 row',
                    'items' => [
                        'thumb' => [
                            'type' => 'thumb',
                            'multi' => 'thumb',
                            'tpl' => '@CODE:<img src="[+value+]" class="img-thumbnail">',
                            'prepare' => function ($data, $modx) {
                                $data['value'] = $modx->runSnippet('phpthumb', [
                                    'input' => $data['value'],
                                    'options' => 'w=50,h=50,zc=1'
                                ]);

                                return $data;
                            }
                        ]
                    ],
                    'tpl' => '@CODE:
                        <div class="col-xs-6">
                            [+thumb+]
                        </div>'
                ]
            ],
            'tpl' => '@CODE:
                <div class="row">
                    [+mf.items+]
                </div>'
        ]
    ]
];

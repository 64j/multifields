<?php

$templates = [
    'Description' => [
        'title' => 'Описание',
        'rows' => [
            0 => [
                'Description' => [
                    'type' => 'text',
                    'placeholder' => 'Описание'
                ]
            ]
        ]
    ],
    'Image' => [
        'title' => 'Картинка',
        'view' => 'float',
        'rows' => [
            0 => [
                'Image' => [
                    'type' => 'thumb',
                    'rows' => [
                        0 => [
                            'image' => [
                                'type' => 'image',
                                'thumb' => 'Thumb',
                                'placeholder' => 'изображение'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'Images' => [
        'title' => 'Изображения',
        'rows' => [
            0 => [
                'items' => [
                    0 => [
                        'Description' => [
                            'type' => 'text',
                            'title' => 'Описание',
                            'placeholder' => 'Описание'
                        ]
                    ],
                    1 => [
                        'Image' => [
                            'type' => 'image',
                            'title' => 'Изображение',
                            'placeholder' => 'Изображение'
                        ]
                    ]
                ]
            ]
        ]
    ],
    'Group' => [
        'title' => 'Группа',
        'group' => [
            'placeholder' => 'Название группы',
            'rows' => []
        ]
    ],
    'RichText' => [
        'rows' => [
            0 => [
                'RichText' => [
                    'type' => 'richtext',
                    'placeholder' => 'RichText'
                ]
            ]
        ]
    ],
    'Slider' => [
        'rows' => [
            0 => [
                'items' => [
                    0 => [
                        'Thumb' => [
                            'type' => 'thumb'
                        ]
                    ],
                    1 => [
                        'rows' => [
                            0 => [
                                'description' => [
                                    'type' => 'text',
                                    'placeholder' => 'описание'
                                ]
                            ],
                            1 => [
                                'image' => [
                                    'type' => 'image',
                                    'thumb' => 'Thumb',
                                    'placeholder' => 'изображение'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'SlideShow' => [
        'view' => 'float',
        'rows' => [
            0 => [
                'Thumb' => [
                    'type' => 'thumb',
                    'value' => '',
                    'rows' => [
                        0 => [
                            'description' => [
                                'type' => 'text',
                                'placeholder' => 'название'
                            ]
                        ],
                        1 => [
                            'image' => [
                                'type' => 'image',
                                'thumb' => 'Thumb',
                                'placeholder' => 'изображение'
                            ]
                        ],
                        2 => [
                            'text' => [
                                'type' => 'textareamini',
                                'placeholder' => 'описание'
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    'Cols2Left' => [
        'rows' => [
            0 => [
                'items' => [
                    0 => [
                        'group' => [
                            'templates' => false,
                            'move' => '0',
                            'title' => 'Col 1',
                            'novalue' => true,
                            'width' => '33%',
                            'rows' => []
                        ]
                    ],
                    1 => [
                        'group' => [
                            'move' => '0',
                            'title' => 'Col 2',
                            'novalue' => true,
                            'width' => '67%',
                            'rows' => []
                        ]
                    ]
                ]
            ]
        ]
    ],
    'Cols2Right' => [
        'rows' => [
            0 => [
                'items' => [
                    0 => [
                        'group' => [
                            'templates' => 'RichText',
                            'move' => '0',
                            'title' => 'Col 1',
                            'novalue' => true,
                            'width' => '67%',
                            'rows' => []
                        ]
                    ],
                    1 => [
                        'group' => [
                            'templates' => 'Image',
                            'move' => '0',
                            'title' => 'Col 2',
                            'novalue' => true,
                            'width' => '33%',
                            'rows' => []
                        ]
                    ]
                ]
            ]
        ]
    ],
    'Cols3' => [
        'rows' => [
            0 => [
                'items' => [
                    0 => [
                        'group' => [
                            'move' => '0',
                            'title' => 'Col 1',
                            'novalue' => true,
                            'width' => '33%',
                            'rows' => []
                        ]
                    ],
                    1 => [
                        'group' => [
                            'move' => '0',
                            'title' => 'Col 2',
                            'novalue' => true,
                            'width' => '34%',
                            'rows' => []
                        ]
                    ],
                    2 => [
                        'group' => [
                            'move' => '0',
                            'title' => 'Col 3',
                            'novalue' => true,
                            'width' => '33%',
                            'rows' => []
                        ]
                    ],
                ]
            ]
        ]
    ],
    'Section' => [
        'section' => [
            'title' => 'Section',
            'rows' => [
                0 => [
                    'items' => [
                        0 => [
                            'Description' => [
                                'type' => 'text',
                                'title' => 'Описание',
                                'placeholder' => 'Описание'
                            ]
                        ],
                        1 => [
                            'Image' => [
                                'type' => 'image',
                                'title' => 'Изображение',
                                'placeholder' => 'Изображение'
                            ]
                        ]
                    ]
                ],
                1 => [
                    'Description' => [
                        'type' => 'text',
                        'title' => 'Описание',
                        'placeholder' => 'Описание'
                    ]
                ],
                2 => [
                    'Image' => [
                        'type' => 'image',
                        'title' => 'Изображение',
                        'placeholder' => 'Изображение'
                    ]
                ],
                3 => [
                    'group' => [
                        'placeholder' => 'Название группы'
                    ]
                ]
            ]
        ]
    ]
];
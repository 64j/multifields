<?php

return [
    'settings' => [],
    'templates' => [
        'Table' => [
            'type' => 'table',
            'placeholder' => 'Table title',
            'value' => true,
            'items' => [
                'thead' => [
                    'type' => 'table:head',
                    'items' => [
                        'row' => [
                            'type' => 'table:row',
                            'items' => [
                                [
                                    'type' => 'table:th',
                                    'items' => [
                                        [
                                            'type' => 'id',
                                            'value' => 'id'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<th>[+mf.items+]</th>'
                                ],
                                [
                                    'type' => 'table:th',
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'value' => 'Title'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<th>[+mf.items+]</th>'
                                ],
                                [
                                    'type' => 'table:th',
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'value' => 'Text'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<th>[+mf.items+]</th>'
                                ],
                                [
                                    'type' => 'table:th',
                                    'items' => [
                                        [
                                            'type' => 'date',
                                            'value' => 'Date'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<th>[+mf.items+]</th>'
                                ],
                                [
                                    'type' => 'table:th',
                                    'items' => [
                                        [
                                            'type' => 'image',
                                            'value' => 'Image'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<th>[+mf.items+]</th>'
                                ]
                            ],
                            'tpl' => '@CODE:<tr>[+mf.items+]</tr>'
                        ]
                    ],
                    'tpl' => '@CODE:<thead class="thead-dark">[+mf.items+]</thead>'
                ],
                'tbody' => [
                    'type' => 'table:body',
                    'items' => [
                        'row' => [
                            'type' => 'table:row',
                            'items' => [
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'id'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'text'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'text'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'date'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'image'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ]
                            ],
                            'tpl' => '@CODE:<tr>[+mf.items+]</tr>'
                        ]
                    ],
                    'tpl' => '@CODE:<tbody>[+mf.items+]</tbody>'
                ]
            ],
            'tpl' => '@CODE:<table class="table table-sm table-hover table-bordered">[+mf.items+]</table>'
        ],
        'Table_1' => [
            'type' => 'table',
            'title' => 'Table 1',
            'placeholder' => 'Table 1 title',
            'value' => false,
            'items' => [
                'tbody' => [
                    'type' => 'table:body',
                    'items' => [
                        'row' => [
                            'type' => 'table:row',
                            'items' => [
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'id',
                                            'placeholder' => 'id'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'placeholder' => 'Title'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'placeholder' => 'Title'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'date',
                                            'placeholder' => 'Date'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ],
                                [
                                    'type' => 'table:td',
                                    'items' => [
                                        [
                                            'type' => 'image',
                                            'placeholder' => 'Image'
                                        ]
                                    ],
                                    'tpl' => '@CODE:<td>[+mf.items+]</td>'
                                ]
                            ],
                            'tpl' => '@CODE:<tr>[+mf.items+]</tr>'
                        ]
                    ],
                    'tpl' => '@CODE:<tbody>[+mf.items+]</tbody>'
                ]
            ],
            'tpl' => '@CODE:<table class="table table-sm table-hover table-bordered">[+mf.items+]</table>'
        ]
    ]
];

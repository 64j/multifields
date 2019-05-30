<?php

return [
    'Table' => [
        'type' => 'table',
        'placeholder' => 'Table title',
        //'value' => false,
        'cols' => [
            'id' => [
                'title' => 'id',
                'attr' => 'style="width: 40px; max-width: 40px"',
            ],
            'title' => [
                'title' => 'Title',
            ],
            'text' => [
                'title' => 'Text',
            ],
            'date' => [
                'title' => 'Date',
            ],
            'image' => [
                'title' => 'Image'
            ]
        ],
        'items' => [
            'Table_Row' => [
                'type' => 'row',
                'items' => [
                    'id' => [
                        'attr' => 'style="width: 40px; max-width: 40px"',
                        'autoincrement' => true
                    ],
                    'title' => [
                        'type' => 'text'
                    ],
                    'text' => [
                        'type' => 'richtext'
                    ],
                    'date' => [
                        'type' => 'date'
                    ],
                    'image' => [
                        'type' => 'image',
                    ]
                ],
                'tpl' => '@CODE:
                    <tr>
                        <td>[+id+]</td>
                        <td>[+title+]</td>
                        <td>[+text+]</td>
                        <td>[+date+]</td>
                        <td>[+image+]</td>
                    </tr>'
            ]
        ],
        'tpl' => '@CODE:
            <h3>[+value+]</h3>
            <table style="width: 100%; border-spacing: 5px; border-collapse: separate; border: 1px solid #000;" border="1">
                [+mf.items+]
            </table>'
    ]
];

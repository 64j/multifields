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

/*
 * Example snippet output
 *
 *


[!multifields?
&tvId=`5`

&tpl_Slider=`@CODE:
<div class="row">
	<div class="col-xs-4">
		<img src="[+image+]" style="display: block; max-width: 100%">
	</div>
	<div class="col-xs-8">
		<p>[+text+]</p>
	</div>
</div>`

&tpl_Slider_group=`@CODE:
<div id="testCarousel" class="carousel slide" data-ride="carousel">
	<div class="carousel-inner">
		[+mf.items+]
	</div>
	<a class="left carousel-control" href="#testCarousel" role="button" data-slide="prev">
		<span class="glyphicon glyphicon-chevron-left"></span>
	</a>
	<a class="right carousel-control" href="#testCarousel" role="button" data-slide="next">
		<span class="glyphicon glyphicon-chevron-right"></span>
	</a>
</div>`
&prepare_Slider_group_item=`mf.prepare.Slider_group_item`
&tpl_Slider_group_item=`@CODE:
<div class="item [+active+]" style="height: 400px; background: url('[+thumb+]') 50% 50% no-repeat; background-size: cover;">
	[+text+]
</div>`
!]



Snippet mf.prepare.Slider_group_item

<?php
$data['active'] = '';
if ($data['mf.iteration'] == 1) {
	$data['active'] = 'active';
}

return $data;


 */

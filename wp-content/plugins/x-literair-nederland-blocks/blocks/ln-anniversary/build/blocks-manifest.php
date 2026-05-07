<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/ln-anniversary',
		'version' => '0.1.0',
		'title' => 'LN Anniversary Post',
		'category' => 'literair-nederland',
		'icon' => 'calendar-alt',
		'description' => 'Shows the post that is closest to exactly 25 (or 10) years ago.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'categories' => array(
				'type' => 'array',
				'items' => array(
					'type' => 'string'
				),
				'default' => array(
					'nieuws',
					'recensies',
					'oogst'
				)
			)
		),
		'textdomain' => 'x-literair-nederland-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);

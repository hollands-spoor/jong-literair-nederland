<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/ln-year-archive',
		'version' => '0.1.0',
		'title' => 'LN Year Archive',
		'category' => 'literair-nederland',
		'icon' => 'calendar',
		'description' => 'Shows a year-based archive with image mode and text mode.',
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
					'recensies',
					'interviews',
					'oogst'
				)
			),
			'defaultMode' => array(
				'type' => 'string',
				'default' => 'image'
			),
			'defaultYear' => array(
				'type' => 'number',
				'default' => 0
			),
			'imageColumnsDesktop' => array(
				'type' => 'number',
				'default' => 8
			),
			'imageColumnsTablet' => array(
				'type' => 'number',
				'default' => 4
			),
			'imageColumnsMobile' => array(
				'type' => 'number',
				'default' => 2
			),
			'postsPerPageTextMode' => array(
				'type' => 'number',
				'default' => 0
			),
			'showFieldBookAuthor' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showFieldBookTitle' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showFieldReviewer' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showFieldDate' => array(
				'type' => 'boolean',
				'default' => true
			),
			'enableCoverFallback' => array(
				'type' => 'boolean',
				'default' => true
			),
			'queryVarYear' => array(
				'type' => 'string',
				'default' => 'ln_year'
			),
			'queryVarMode' => array(
				'type' => 'string',
				'default' => 'ln_mode'
			)
		),
		'textdomain' => 'x-literair-nederland-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php',
		'viewScript' => 'file:./view.js'
	)
);

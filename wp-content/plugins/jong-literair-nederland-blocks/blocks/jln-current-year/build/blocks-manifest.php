<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jln/jln-current-year',
		'version' => '0.1.0',
		'title' => 'LN Current Year',
		'category' => 'literair-nederland',
		'icon' => 'calendar-alt',
		'description' => 'Displays the current year.',
		'supports' => array(
			'html' => false,
			'border' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'attributes' => array(
			'prefix' => array(
				'type' => 'string',
				'default' => '©'
			),
			'siteName' => array(
				'type' => 'string',
				'default' => ''
			),
			'suffix' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'textdomain' => 'jln-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);

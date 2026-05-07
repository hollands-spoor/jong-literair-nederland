<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/ln-ad',
		'version' => '0.1.0',
		'title' => 'LN Ad',
		'category' => 'literair-nederland',
		'icon' => 'megaphone',
		'description' => 'Ad block with date, position, and fallback.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'color' => array(
				'background' => true
			)
		),
		'attributes' => array(
			'position' => array(
				'type' => 'string',
				'default' => ''
			),
			'fallbackType' => array(
				'type' => 'string',
				'default' => 'none'
			),
			'fallbackContents' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'textdomain' => 'x-literair-nederland-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);

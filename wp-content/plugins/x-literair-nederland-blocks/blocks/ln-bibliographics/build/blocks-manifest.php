<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/ln-bibliographics',
		'version' => '0.1.0',
		'title' => 'Bibliographic',
		'category' => 'literair-nederland',
		'icon' => 'smiley',
		'description' => 'Block with title info.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'bibliographic' => array(
				'type' => 'object',
				'default' => array(
					
				)
			),
			'boektitel' => array(
				'type' => 'string',
				'default' => ''
			),
			'isbn' => array(
				'type' => 'string',
				'default' => ''
			),
			'showBuyButton' => array(
				'type' => 'boolean',
				'default' => true
			),
			'isSticky' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'textdomain' => 'x-literair-nederland-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'render' => 'file:./render.php',
		'style' => 'file:./style-index.css'
	)
);

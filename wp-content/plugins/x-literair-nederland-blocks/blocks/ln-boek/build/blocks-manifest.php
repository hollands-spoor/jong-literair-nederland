<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/boek',
		'version' => '0.1.0',
		'textdomain' => 'x-literair-nederland-blocks',
		'title' => 'Book',
		'category' => 'literair-nederland',
		'icon' => 'book',
		'description' => 'Book description with bibliographics information.',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'attributes' => array(
			'align' => array(
				'type' => 'string',
				'default' => 'wide'
			),
			'bibliographics_position' => array(
				'type' => 'string',
				'default' => 'right'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);

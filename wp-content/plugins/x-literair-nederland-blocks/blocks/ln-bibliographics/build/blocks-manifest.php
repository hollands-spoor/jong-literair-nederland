<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'ln/ln-bibliographics',
		'version' => '0.1.0',
		'title' => 'Bibliografisch',
		'category' => 'literair-nederland',
		'icon' => 'smiley',
		'description' => 'Block with title info.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false
		),
		'attributes' => array(
			'boektitel' => array(
				'type' => 'string',
				'default' => ''
			),
			'isbn' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'textdomain' => 'ln-bibliographics',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);

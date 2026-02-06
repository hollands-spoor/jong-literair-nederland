<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jln/jln-floating-text',
		'version' => '0.1.0',
		'title' => 'JLN Floating Text',
		'category' => 'literair-nederland',
		'icon' => 'smiley',
		'description' => 'Floating headline/text block for Jong Literair Nederland.',
		'keywords' => array(
			'curve',
			'text',
			'jong'
		),
		'attributes' => array(
			'text' => array(
				'type' => 'string',
				'default' => 'Floating text'
			),
			'fontFamily' => array(
				'type' => 'string',
				'default' => ''
			),
			'textType' => array(
				'type' => 'string',
				'default' => 'curved'
			),
			'centerX' => array(
				'type' => 'number',
				'default' => 200
			),
			'centerY' => array(
				'type' => 'number',
				'default' => 200
			),
			'radius' => array(
				'type' => 'number',
				'default' => 160
			),
			'angle' => array(
				'type' => 'number',
				'default' => 0
			),
			'fontSize' => array(
				'type' => 'number',
				'default' => 28
			),
			'pathId' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false
		),
		'textdomain' => 'jong-literair-nederland-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	)
);

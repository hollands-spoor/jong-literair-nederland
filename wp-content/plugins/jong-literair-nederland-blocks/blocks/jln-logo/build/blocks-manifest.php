<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jln/jln-logo',
		'version' => '0.1.0',
		'title' => 'LN Logo',
		'category' => 'literair-nederland',
		'icon' => 'smiley',
		'description' => 'Displays the Literair Nederland logo',
		'example' => array(
			
		),
		'supports' => array(
			'html' => false,
			'style' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'attributes' => array(
			'logo_choice' => array(
				'type' => 'number',
				'default' => 0
			),
			'link_url' => array(
				'type' => 'string',
				'default' => ''
			),
			'new_window' => array(
				'type' => 'boolean',
				'default' => false
			),
			'show_jong_text' => array(
				'type' => 'boolean',
				'default' => true
			),
			'fill_font' => array(
				'type' => 'string',
				'default' => '#ffffff'
			),
			'fill_page' => array(
				'type' => 'string',
				'default' => '#cc0000'
			),
			'fill_side' => array(
				'type' => 'string',
				'default' => '#990000'
			),
			'circle_stroke' => array(
				'type' => 'string',
				'default' => '#000000'
			),
			'circle_stroke_width' => array(
				'type' => 'number',
				'default' => 2,
				'minimum' => 0,
				'maximum' => 20
			),
			'circle_fill' => array(
				'type' => 'string',
				'default' => 'transparent'
			)
		),
		'textdomain' => 'jln-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	)
);

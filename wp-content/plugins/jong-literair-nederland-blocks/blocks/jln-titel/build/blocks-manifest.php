<?php
// This file is generated. Do not modify it manually.
return array(
	'build' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'jln/jln-titel',
		'version' => '0.1.0',
		'title' => 'LN Titel',
		'category' => 'literair-nederland',
		'icon' => 'heading',
		'description' => 'Dynamic title block with recensie metadata.',
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true,
				'text' => true
			),
			'border' => true,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'attributes' => array(
			'titleLevel' => array(
				'type' => 'string',
				'default' => 'h1'
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showBoekInfo' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showRecensent' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'usesContext' => array(
			'postId',
			'postType',
			'queryId'
		),
		'textdomain' => 'jln-blocks',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);

<?php
if( ! defined( 'ABSPATH' ) ) {
    exit;
}


function ln_blocks_is_xln_plugin_active() {
    $plugin_basename = 'x-literair-nederland-blocks/x-literair-nederland-blocks.php';

    if ( function_exists( 'is_plugin_active' ) ) {
        return is_plugin_active( $plugin_basename );
    }

    $active_plugins = (array) get_option( 'active_plugins', array() );
    if ( in_array( $plugin_basename, $active_plugins, true ) ) {
        return true;
    }

    if ( is_multisite() ) {
        $sitewide_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
        return isset( $sitewide_plugins[ $plugin_basename ] );
    }

    return false;
}


if ( ! ln_blocks_is_xln_plugin_active() ) {
    return;
}


add_filter( 'xln_options_schema', function( $schema ) {
    $schema['oogst'] = [
        'label' => __( 'Harvest', 'jln-blocks' ),
        'sections' => [
            'oogst_titel' => [
                'label' => __( 'Title block above Harvest', 'jln-blocks' ),
                'fields' => [
                    'oogst-titel-single' => [
                        'type' => 'radio',
                        'label' => __( 'Single: what appears above the title? Authors and titles of reviewed books?', 'jln-blocks' ),
                        'default' => 'none',
                        'options' => [
                            'none' => __( 'None', 'jln-blocks' ),
                            'first' => __( 'Author-title of the first reviewed book', 'jln-blocks' ),
                            'all' => __( 'All authors and titles', 'jln-blocks' ),
                            'custom' => __( 'Custom line', 'jln-blocks' ),
                        ],
                    ],
                    'custom-line-single' => [
                        'type' => 'text',
                        'label' => __( 'Single: custom heading', 'jln-blocks' ),
                        'default' => '',
                        'dependency' => [
                            'option' => 'oogst-titel-single',
                            'value' => 'custom',
                        ],
                    ],
                    'oogst-titel-archive' => [
                        'type' => 'radio',
                        'label' => __( 'Archive/query: what appears above the title? Authors and titles of reviewed books?', 'jln-blocks' ),
                        'default' => 'none',
                        'options' => [
                            'none' => __( 'None', 'jln-blocks' ),
                            'first' => __( 'Author-title of the first reviewed book', 'jln-blocks' ),
                            'all' => __( 'All authors and titles', 'jln-blocks' ),
                            'custom' => __( 'Custom line', 'jln-blocks' ),
                        ],
                    ],
                    'custom-line-archive' => [
                        'type' => 'text',
                        'label' => __( 'Archive/query: custom heading', 'jln-blocks' ),
                        'default' => '',
                        'dependency' => [
                            'option' => 'oogst-titel-archive',
                            'value' => 'custom',
                        ],
                    ],
                ],
            ],
        ],
    ];

    $schema['developers']['sections']['developer_tools']['fields']['enable_jln_tools_page'] = [
			'label'   => __( 'Enable JLN tools page', 'jong-literair-nederland-blocks' ),
			'type'    => 'checkbox',
			'default' => true,
	];

    return $schema;
} );
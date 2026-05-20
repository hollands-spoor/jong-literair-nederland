import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, TextControl } from '@wordpress/components';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		prefix = '©',
		siteName = '',
		suffix = '',
	} = attributes;
	const currentYear = new Date().getFullYear();
	const previewSiteName = siteName || __( 'Site name', 'jln-blocks' );
	const preview = [ prefix, currentYear, previewSiteName, suffix ]
		.filter( ( part ) => String( part ).trim() !== '' )
		.join( ' ' );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Content', 'jln-blocks' ) } initialOpen={ true }>
					<TextControl
						label={ __( 'Prefix', 'jln-blocks' ) }
						value={ prefix }
						onChange={ ( value ) => setAttributes( { prefix: value } ) }
					/>
					<TextControl
						label={ __( 'Site name', 'jln-blocks' ) }
						value={ siteName }
						onChange={ ( value ) => setAttributes( { siteName: value } ) }
						help={ __( 'Leave empty to use the WordPress site title.', 'jln-blocks' ) }
					/>
					<TextControl
						label={ __( 'Suffix', 'jln-blocks' ) }
						value={ suffix }
						onChange={ ( value ) => setAttributes( { suffix: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...useBlockProps() }>{ preview }</div>
		</>
	);
}

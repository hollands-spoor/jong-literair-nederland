import { __ } from '@wordpress/i18n';
import './editor.scss';

import { useEffect } from '@wordpress/element';
import { useBlockProps, InspectorControls, InnerBlocks } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextareaControl, TextControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { serialize } from '@wordpress/blocks';

export default function Edit( { attributes, setAttributes, clientId } ) {
	const { position, fallbackType, fallbackContents } = attributes;
	const blockProps = useBlockProps( { className: 'ln-ad' } );

	const innerBlocks = useSelect( ( select ) => select( 'core/block-editor' ).getBlocks( clientId ), [ clientId ] );

	useEffect( () => {
		if ( fallbackType !== 'block' ) {
			return;
		}

		const serialized = serialize( innerBlocks );
		if ( serialized !== fallbackContents ) {
			setAttributes( { fallbackContents: serialized } );
		}
	}, [ innerBlocks, fallbackType ] );

	return (
		<div { ...blockProps }>
			<InspectorControls>
				<PanelBody title={ __( 'Ad', 'x-literair-nederland-blocks' ) } initialOpen>
					<TextControl
						label={ __( 'Position', 'x-literair-nederland-blocks' ) }
						value={ position }
						onChange={ ( value ) => setAttributes( { position: value } ) }
						placeholder={ __( 'e.g. sidebar-1', 'x-literair-nederland-blocks' ) }
					/>
					<SelectControl
						label={ __( 'Fallback type', 'x-literair-nederland-blocks' ) }
						value={ fallbackType }
						options={ [
							{ label: __( 'None', 'x-literair-nederland-blocks' ), value: 'none' },
							{ label: __( 'HTML', 'x-literair-nederland-blocks' ), value: 'html' },
							{ label: __( 'Block', 'x-literair-nederland-blocks' ), value: 'block' },
						] }
						onChange={ ( value ) => setAttributes( { fallbackType: value } ) }
					/>
					{ fallbackType === 'html' && (
						<TextareaControl
							label={ __( 'Fallback HTML', 'x-literair-nederland-blocks' ) }
							value={ fallbackContents }
							onChange={ ( value ) => setAttributes( { fallbackContents: value } ) }
						/>
					) }
				</PanelBody>
			</InspectorControls>
			<div className="ln-ad__placeholder">
				{ __( 'LN Ad - rendered on the frontend.', 'x-literair-nederland-blocks' ) }
			</div>
			{ fallbackType === 'block' && (
				<div className="ln-ad__fallback-block">
					<InnerBlocks
						templateLock={ false }
						renderAppender={ InnerBlocks.ButtonBlockAppender }
					/>
				</div>
			) }
		</div>
	);
}

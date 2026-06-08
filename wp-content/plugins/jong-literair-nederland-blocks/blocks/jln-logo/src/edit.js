/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps } from '@wordpress/block-editor';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

import { InspectorControls, PanelColorSettings } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, TextControl, ToggleControl } from '@wordpress/components';
import { Fragment } from '@wordpress/element';

import LogoSVG, { getLogoValues } from './get_logo';

function getWordPressHomeUrl() {
	if ( typeof window === 'undefined' ) {
		return '/';
	}

	const apiRoot = window?.wpApiSettings?.root;
	if ( ! apiRoot ) {
		return '/';
	}

	try {
		const url = new URL( apiRoot );
		url.pathname = url.pathname.replace( /\/wp-json\/?$/, '/' );
		url.search = '';
		url.hash = '';
		return url.toString();
	} catch {
		return '/';
	}
}


/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit({ attributes, setAttributes }) {
	const logoValues = getLogoValues( attributes );
	const {
		logo_choice,
		fill_font,
		fill_page,
		fill_side,
		circle_stroke,
		circle_stroke_width,
		circle_fill,
	} = logoValues;
	const linkUrl = ( attributes.link_url || '' ).trim();
	const resolvedLinkUrl = linkUrl === '#home' ? getWordPressHomeUrl() : linkUrl;
	const isNewWindow = !! attributes.new_window;
	const showJongText = attributes.show_jong_text !== false;
	const logoElement = <LogoSVG values={ logoValues } />;
	const linkedLogoElement = resolvedLinkUrl ? (
		<a href={ resolvedLinkUrl } { ...( isNewWindow ? { target: '_blank', rel: 'noopener noreferrer' } : {} ) }>
			{ logoElement }
		</a>
	) : logoElement;
	const jongLogo = <LogoSVG values={ { ...logoValues, logo_choice: 3, fill_font:'#13ccb0' } } />;
	const blockProps = useBlockProps( { className: `has-logo-${ logo_choice }` } );

	return (

		<Fragment>
            <InspectorControls>
				<PanelBody
					title={ __( 'Logo Variant', 'jln-blocks' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Select logo', 'jln-blocks' ) }
						value={ String( logo_choice ) }
						options={ [
							{ label: __( 'Logo', 'jln-blocks' ), value: '0' },
							{ label: __( 'Search', 'jln-blocks' ), value: '1' },
							{ label: __( 'JLN', 'jln-blocks' ), value: '2' },
							{ label: __( 'JONG', 'jln-blocks' ), value: '3' },
							{ label: __( 'Steun Ons', 'jln-blocks' ), value: '4' },
							{ label: __( 'JLN Header', 'jln-blocks' ), value: '5' },
							{ label: __( 'Steun JLN', 'jln-blocks' ), value: '6' }

						] }
						onChange={ ( value ) => setAttributes( { logo_choice: Number.parseInt( value, 10 ) || 0 } ) }
					/>

					<TextControl
						label={ __( 'Link', 'jln-blocks' ) }
						value={ attributes.link_url || '' }
						onChange={ ( value ) => setAttributes( { link_url: value } ) }
					/>

					<ToggleControl
						label={ __( 'New window', 'jln-blocks' ) }
						checked={ isNewWindow }
						onChange={ ( value ) => setAttributes( { new_window: value } ) }
					/>
				</PanelBody>

				{ logo_choice === 0 && (
					<Fragment>
						<PanelBody
							title={ __( 'Circle Stroke', 'jln-blocks' ) }
							initialOpen={ false }
						>
							<RangeControl
								label={ __( 'Stroke width', 'jln-blocks' ) }
								help={ __( 'Adjust the outer circle border thickness.', 'jln-blocks' ) }
								value={ circle_stroke_width }
								onChange={ ( value ) => setAttributes( { circle_stroke_width: value ?? 0 } ) }
								min={ 0 }
								max={ 20 }
								step={ 0.5 }
							/>
						</PanelBody>

						<PanelColorSettings
							title={ __( 'Logo Colors', 'jln-blocks' ) }
							colorSettings={ [
								{
									value: fill_font,
									onChange: ( value ) => setAttributes( { fill_font: value || '#ffffff' } ),
									label: __( 'Font color', 'jln-blocks' )
								},
								{
									value: fill_page,
									onChange: ( value ) => setAttributes( { fill_page: value || '#cc0000' } ),
									label: __( 'Page color', 'jln-blocks' )
								},
								{
									value: fill_side,
									onChange: ( value ) => setAttributes( { fill_side: value || '#990000' } ),
									label: __( 'Side color', 'jln-blocks' )
								},
								{
									value: circle_stroke,
									onChange: ( value ) => setAttributes( { circle_stroke: value || '#000000' } ),
									label: __( 'Circle stroke color', 'jln-blocks' )
								},
								{
									value: circle_fill,
									onChange: ( value ) => setAttributes( { circle_fill: value || 'transparent' } ),
									label: __( 'Circle fill color', 'jln-blocks' )
								}
							] }
						/>
					</Fragment>
				) }
				{ logo_choice === 2 && (
					<Fragment>
						<PanelBody
							title={ __( 'JONG Settings', 'jln-blocks' ) }
							initialOpen={ false }
						>
							<ToggleControl
								label={ __( 'Show JONG texts', 'jln-blocks' ) }
								checked={ showJongText }
								onChange={ ( value ) => setAttributes( { show_jong_text: value } ) }
							/>
						</PanelBody>
						
					</Fragment>
				) }

				{ logo_choice === 5 && (
					<Fragment>

						<PanelColorSettings
							title={ __( 'Logo Colors', 'jln-blocks' ) }
							colorSettings={ [
								{
									value: fill_font,
									onChange: ( value ) => setAttributes( { fill_font: value || '#ffffff' } ),
									label: __( 'Font color', 'jln-blocks' )
								}
							] }
						/>
					</Fragment>
				) }



            </InspectorControls>


			<div { ...blockProps }>
				{ /* Maybe show this tooltip-like div that appears on hover. Only tested for header. */ }
				{ logo_choice === 2 && showJongText ? (
					<div 
						className="ln-logo-jong-wrap"
						style= { { display: 'none', flexDirection: 'column', alignItems: 'center', position: 'absolute' } } >
						<div 
							className="ln-logo-jong-bezoek"
							style= { { display: 'flex', flexDirection: 'row' } } 
						>
							<p className="ln-logo-jong-note-1">
								{ __( 'Visit', 'jln-blocks' ) }
							</p>
						{ jongLogo }
						</div>
						<p className="ln-logo-jong-note-2">
							{ __( 'Jong Literair Nederland', 'jln-blocks' ) }
						</p>
					</div>
				) : null }
				{ linkedLogoElement }
			</div>
		</Fragment>
	);
}

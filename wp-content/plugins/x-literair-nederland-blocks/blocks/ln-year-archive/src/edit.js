import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	SelectControl,
	RangeControl,
	ToggleControl,
	Notice,
} from '@wordpress/components';
import './editor.scss';

function parseCategoryInput( value ) {
	return value
		.split( ',' )
		.map( ( item ) => item.trim().toLowerCase() )
		.filter( Boolean );
}

export default function Edit( { attributes, setAttributes } ) {
	const {
		categories,
		defaultMode,
		defaultYear,
		imageColumnsDesktop,
		imageColumnsTablet,
		imageColumnsMobile,
		postsPerPageTextMode,
		showFieldBookAuthor,
		showFieldBookTitle,
		showFieldReviewer,
		showFieldDate,
		enableCoverFallback,
		queryVarYear,
		queryVarMode,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'ln-year-archive ln-year-archive--editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Source data', 'x-literair-nederland-blocks' ) }
					initialOpen={ true }
				>
					<TextControl
						label={ __( 'Category slugs', 'x-literair-nederland-blocks' ) }
						help={ __( 'Comma-separated, for example: reviews, interviews, harvest', 'x-literair-nederland-blocks' ) }
						value={ categories.join( ', ' ) }
						onChange={ ( value ) => setAttributes( { categories: parseCategoryInput( value ) } ) }
					/>
					<SelectControl
						label={ __( 'Default mode', 'x-literair-nederland-blocks' ) }
						value={ defaultMode }
						options={ [
							{ label: __( 'Image mode', 'x-literair-nederland-blocks' ), value: 'image' },
							{ label: __( 'Text mode', 'x-literair-nederland-blocks' ), value: 'text' },
						] }
						onChange={ ( value ) => setAttributes( { defaultMode: value } ) }
					/>
					<TextControl
						label={ __( 'Default year', 'x-literair-nederland-blocks' ) }
						help={ __( '0 means: current year.', 'x-literair-nederland-blocks' ) }
						type="number"
						min={ 0 }
						value={ defaultYear }
						onChange={ ( value ) => setAttributes( { defaultYear: Number( value ) || 0 } ) }
					/>
					<ToggleControl
						label={ __( 'Allow cover fallback', 'x-literair-nederland-blocks' ) }
						checked={ !! enableCoverFallback }
						onChange={ ( value ) => setAttributes( { enableCoverFallback: value } ) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Grid (image mode)', 'x-literair-nederland-blocks' ) }
					initialOpen={ false }
				>
					<RangeControl
						label={ __( 'Columns desktop', 'x-literair-nederland-blocks' ) }
						value={ imageColumnsDesktop }
						onChange={ ( value ) => setAttributes( { imageColumnsDesktop: value } ) }
						min={ 2 }
						max={ 12 }
					/>
					<RangeControl
						label={ __( 'Columns tablet', 'x-literair-nederland-blocks' ) }
						value={ imageColumnsTablet }
						onChange={ ( value ) => setAttributes( { imageColumnsTablet: value } ) }
						min={ 1 }
						max={ 8 }
					/>
					<RangeControl
						label={ __( 'Columns mobile', 'x-literair-nederland-blocks' ) }
						value={ imageColumnsMobile }
						onChange={ ( value ) => setAttributes( { imageColumnsMobile: value } ) }
						min={ 1 }
						max={ 4 }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Text mode', 'x-literair-nederland-blocks' ) }
					initialOpen={ false }
				>
					<TextControl
						label={ __( 'Max posts (0 = unlimited)', 'x-literair-nederland-blocks' ) }
						type="number"
						min={ 0 }
						value={ postsPerPageTextMode }
						onChange={ ( value ) => setAttributes( { postsPerPageTextMode: Number( value ) || 0 } ) }
					/>
					<ToggleControl
						label={ __( 'Show book author', 'x-literair-nederland-blocks' ) }
						checked={ !! showFieldBookAuthor }
						onChange={ ( value ) => setAttributes( { showFieldBookAuthor: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show book title', 'x-literair-nederland-blocks' ) }
						checked={ !! showFieldBookTitle }
						onChange={ ( value ) => setAttributes( { showFieldBookTitle: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show reviewer', 'x-literair-nederland-blocks' ) }
						checked={ !! showFieldReviewer }
						onChange={ ( value ) => setAttributes( { showFieldReviewer: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show date', 'x-literair-nederland-blocks' ) }
						checked={ !! showFieldDate }
						onChange={ ( value ) => setAttributes( { showFieldDate: value } ) }
					/>
				</PanelBody>

				<PanelBody
					title={ __( 'Advanced', 'x-literair-nederland-blocks' ) }
					initialOpen={ false }
				>
					<TextControl
						label={ __( 'Query variable for year', 'x-literair-nederland-blocks' ) }
						value={ queryVarYear }
						onChange={ ( value ) => setAttributes( { queryVarYear: value || 'ln_year' } ) }
					/>
					<TextControl
						label={ __( 'Query variable for mode', 'x-literair-nederland-blocks' ) }
						value={ queryVarMode }
						onChange={ ( value ) => setAttributes( { queryVarMode: value || 'ln_mode' } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Notice status="info" isDismissible={ false }>
					{ __(
						'Frontend block with year navigation and image/text mode. Only a placeholder is shown in the editor.',
						'x-literair-nederland-blocks'
					) }
				</Notice>
			</div>
		</>
	);
}

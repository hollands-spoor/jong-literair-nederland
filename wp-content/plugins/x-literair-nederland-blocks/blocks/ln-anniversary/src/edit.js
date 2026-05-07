import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, CheckboxControl } from '@wordpress/components';
import './editor.scss';

const AVAILABLE_CATEGORIES = [
	{ label: __( 'News', 'x-literair-nederland-blocks' ), value: 'nieuws' },
	{ label: __( 'Reviews', 'x-literair-nederland-blocks' ), value: 'recensies' },
	{ label: __( 'Harvest', 'x-literair-nederland-blocks' ), value: 'oogst' },
];

export default function Edit( { attributes, setAttributes } ) {
	const { categories } = attributes;
	const blockProps = useBlockProps( { className: 'ln-anniversary ln-anniversary--editor' } );

	function toggleCategory( value, checked ) {
		const next = checked
			? [ ...categories, value ]
			: categories.filter( ( c ) => c !== value );
		setAttributes( { categories: next } );
	}

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Categories', 'x-literair-nederland-blocks' ) }
					initialOpen={ true }
				>
					{ AVAILABLE_CATEGORIES.map( ( { label, value } ) => (
						<CheckboxControl
							key={ value }
							label={ label }
							checked={ categories.includes( value ) }
							onChange={ ( checked ) => toggleCategory( value, checked ) }
						/>
					) ) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<span className="ln-anniversary__editor-label">
					{ __( 'Anniversary post - rendered on the frontend.', 'x-literair-nederland-blocks' ) }
				</span>
			</div>
		</>
	);
}

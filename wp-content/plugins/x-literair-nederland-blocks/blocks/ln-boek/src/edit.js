import { __ } from '@wordpress/i18n';
import { InspectorControls, InnerBlocks, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

const TEMPLATE = [
	[
		'core/columns',
		{
			lock: { move: true, remove: true },
		},
		[
			[
				'core/column',
				{
					className: 'ln-padding',
					width: '66.66%',
					lock: { move: true, remove: true },
				},
				[
					[
						'core/paragraph',
						{ placeholder: __( 'Add text…', 'x-literair-nederland-blocks' ) },
					],
				],
			],
			[
				'core/column',
				{
					className: 'ln-padding',
					width: '33.33%',
					lock: { move: true, remove: true },
				},
				[
					[
						'ln/ln-bibliographics',
						{
							className: 'ln-boek__right',
							lock: { move: true, remove: true },
						},
					],
				],
			],
		],
	],
];

export default function Edit( { attributes, setAttributes } ) {
	const { bibliographics_position = 'right' } = attributes;
	const positionClass =
		bibliographics_position === 'bottom'
			? 'ln-boek--bibliographics-bottom'
			: 'ln-boek--bibliographics-right';
	const blockProps = useBlockProps( {
		className: `ln-boek ${ positionClass }`,
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Bibliographics position', 'x-literair-nederland-blocks' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Place bibliographics at bottom', 'x-literair-nederland-blocks' ) }
						help={
							bibliographics_position === 'bottom'
								? __( 'Currently shown below the text.', 'x-literair-nederland-blocks' )
								: __( 'Currently shown to the right of the text.', 'x-literair-nederland-blocks' )
						}
						checked={ bibliographics_position === 'bottom' }
						onChange={ ( isBottom ) =>
							setAttributes( {
								bibliographics_position: isBottom ? 'bottom' : 'right',
							} )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<InnerBlocks
					template={ TEMPLATE }
					templateLock={ false }
					allowedBlocks={ [
						'core/columns',
						'core/column',
						'core/paragraph',
						'core/heading',
						'core/image',
						'ln/ln-bibliographics',
					] }
				/>
			</div>
		</>
	);
}

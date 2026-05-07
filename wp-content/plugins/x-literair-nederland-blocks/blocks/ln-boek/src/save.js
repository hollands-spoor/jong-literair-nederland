import { InnerBlocks, useBlockProps } from '@wordpress/block-editor';


export default function save( { attributes } ) {
	const { bibliographics_position = 'right' } = attributes;
	const positionClass =
		bibliographics_position === 'bottom'
			? 'ln-boek--bibliographics-bottom'
			: 'ln-boek--bibliographics-right';

	return (
		<div
			{ ...useBlockProps.save( {
				className: `ln-boek ${ positionClass }`,
			} ) }
		>
			<InnerBlocks.Content />
		</div>
	);
}

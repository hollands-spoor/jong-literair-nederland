import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { InnerBlocks, useBlockProps, store as blockEditorStore } from '@wordpress/block-editor';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

const DEFAULT_TEMPLATE = [
	[ 'ln/boek' ],
	[ 'ln/boek' ],
	[ 'ln/boek' ],
];

export default function Edit( { clientId } ) {
	const blockProps = useBlockProps( {
		className: 'ln-oogst',
	} );
	const { insertBlock } = useDispatch( blockEditorStore );

	const handleAddBoek = () => {
		insertBlock( createBlock( 'ln/boek' ), undefined, clientId );
	};

	return (
		<div { ...blockProps }>
			<InnerBlocks
				allowedBlocks={ [ 'ln/boek' ] }
				orientation="vertical"
				template={ DEFAULT_TEMPLATE }
				renderAppender={ () => null }
			/>
			<div className="ln-oogst__footer">
				<Button variant="primary" onClick={ handleAddBoek }>
					{ __( 'Add another book', 'x-literair-nederland-blocks' ) }
				</Button>
			</div>
		</div>
	);
}


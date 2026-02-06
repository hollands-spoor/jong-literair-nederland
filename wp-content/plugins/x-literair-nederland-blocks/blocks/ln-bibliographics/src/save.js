import { useBlockProps, RichText } from '@wordpress/block-editor';

export default function save( { attributes: { boektitel, isbn } } ) {
	const blockProps = useBlockProps.save();

	return (
		<div { ...blockProps }>
			{ boektitel && (
				<RichText.Content
					tagName="p"
					className="ln-bibliographics__title"
					value={ boektitel }
				/>
			) }
			{ isbn && (
				<p className="ln-bibliographics__isbn">
					<span className="ln-bibliographics__isbn-label">ISBN:</span> { isbn }
				</p>
			) }
		</div>
	);
}

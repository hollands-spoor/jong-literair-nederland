import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import './editor.scss';

const TITLE_LEVEL_OPTIONS = [
	{ label: 'H1', value: 'h1' },
	{ label: 'H2', value: 'h2' },
	{ label: 'H3', value: 'h3' },
	{ label: 'H4', value: 'h4' },
	{ label: 'H5', value: 'h5' },
	{ label: 'H6', value: 'h6' },
];

export default function Edit( { attributes, setAttributes, context = {} } ) {
	const {
		titleLevel = 'h1',
		showDate = true,
		showBoekInfo = true,
		showRecensent = true,
	} = attributes;
	const { postId: contextPostId, postType: contextPostType } = context;

	const { postTitle, postDate, auteurRecensie, auteurBoek, boektitel } = useSelect( ( select ) => {
		const editorStore = select( 'core/editor' );
		const coreStore = select( 'core' );
		const hasContextPost = Number( contextPostId ) > 0 && typeof contextPostType === 'string' && contextPostType.length > 0;

		let sourceTitle = '';
		let sourceDate = '';
		let meta = {};

		if ( hasContextPost && coreStore && typeof coreStore.getEntityRecord === 'function' ) {
			const entity = coreStore.getEntityRecord( 'postType', contextPostType, Number( contextPostId ) );

			if ( entity ) {
				if ( typeof entity?.title?.rendered === 'string' ) {
					sourceTitle = decodeEntities( entity.title.rendered.replace( /<[^>]+>/g, '' ).trim() );
				} else if ( typeof entity?.title?.raw === 'string' ) {
					sourceTitle = entity.title.raw;
				} else if ( typeof entity?.title === 'string' ) {
					sourceTitle = entity.title;
				}

				sourceDate = entity?.date || '';
				meta = entity?.meta || {};
			}
		} else if ( editorStore && typeof editorStore.getEditedPostAttribute === 'function' ) {
			sourceTitle = editorStore.getEditedPostAttribute( 'title' ) || '';
			sourceDate = editorStore.getEditedPostAttribute( 'date' ) || '';
			meta = editorStore.getEditedPostAttribute( 'meta' ) || {};
		}

		const auteurRecensieRaw = meta.auteur_recensie || '';
		let auteurRecensieValue = auteurRecensieRaw;

		if ( /^\d+$/.test( String( auteurRecensieRaw ) ) && coreStore && typeof coreStore.getEntityRecord === 'function' ) {
			const auteurRecensieId = Number( auteurRecensieRaw );
			const postTypes = typeof coreStore.getPostTypes === 'function' ? ( coreStore.getPostTypes( { per_page: -1 } ) || [] ) : [];

			for ( const postType of postTypes ) {
				if ( ! postType || ! postType.slug ) {
					continue;
				}

				const post = coreStore.getEntityRecord( 'postType', postType.slug, auteurRecensieId );

				if ( ! post ) {
					continue;
				}

				const renderedTitle = typeof post?.title?.rendered === 'string' ? post.title.rendered : '';
				const plainTitle = renderedTitle.replace( /<[^>]+>/g, '' ).trim();

				if ( plainTitle ) {
					auteurRecensieValue = plainTitle;
					break;
				}

				if ( typeof post?.title === 'string' && post.title.trim() ) {
					auteurRecensieValue = post.title.trim();
					break;
				}
			}
		}

		return {
			postTitle: sourceTitle,
			postDate: sourceDate,
			auteurRecensie: auteurRecensieValue,
			auteurBoek: meta.besproken_boeken_0_auteur_boek || '',
			boektitel: meta.besproken_boeken_0_boektitel || '',
		};
	}, [ contextPostId, contextPostType ] );

	const HeadingTag = titleLevel || 'h1';
	const hasBoekInfo = Boolean( auteurBoek || boektitel );
	const boekInfoValue = [ auteurBoek, boektitel ].filter( Boolean ).join( ' - ' );

	const rows = [];

	if ( showDate && postDate ) {
		rows.push( { label: __( 'Date', 'jln-blocks' ), value: postDate, className: 'ln-titel__post-date' } );
	}

	if ( showBoekInfo && hasBoekInfo ) {
		rows.push( { label: __( 'Book', 'jln-blocks' ), value: boekInfoValue, className: 'ln-titel__boek-info' } );
	}

	if ( showRecensent && auteurRecensie ) {
		rows.push( {
			label: __( 'Reviewer', 'jln-blocks' ),
			value: auteurRecensie,
			className: 'ln-titel__auteur-recensie',
		} );
	}

	const hasVisibleValues = rows.length > 0 || Boolean( postTitle );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Settings', 'jln-blocks' ) } initialOpen={ true }>
					<SelectControl
						label={ __( 'Title level', 'jln-blocks' ) }
						value={ titleLevel }
						options={ TITLE_LEVEL_OPTIONS }
						onChange={ ( value ) => setAttributes( { titleLevel: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show date', 'jln-blocks' ) }
						checked={ showDate }
						onChange={ ( value ) => setAttributes( { showDate: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show book title/book author', 'jln-blocks' ) }
						checked={ showBoekInfo }
						onChange={ ( value ) => setAttributes( { showBoekInfo: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show reviewer', 'jln-blocks' ) }
						checked={ showRecensent }
						onChange={ ( value ) => setAttributes( { showRecensent: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...useBlockProps() }>
				{ hasVisibleValues ? (
					<>
						{ rows.map( ( row ) => (
							<div key={ row.className } className={ `ln-titel__row ${ row.className }` }>
								<span className="ln-titel__label">{ row.label }:</span>
								<span className="ln-titel__value">{ row.value }</span>
							</div>
						) ) }
						{ postTitle && <HeadingTag className="ln-titel__post-title">{ postTitle }</HeadingTag> }
					</>
				) : (
					__( 'No preview values yet. Save post meta values to see the review title preview.', 'jln-blocks' )
				) }
			</div>
		</>
	);
}
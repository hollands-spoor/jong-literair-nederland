import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { cloneBlock, createBlock, rawHandler } from '@wordpress/blocks';
import { dispatch, select, subscribe } from '@wordpress/data';
import {
	getAutoInsertBibliographicsContentType,
	shouldAutoInsertBibliographics,
} from './bibliographics-trigger';

const BIBLIO_BLOCK_NAME = 'ln/ln-bibliographics';
const BOEK_BLOCK_NAME = 'ln/boek';
const OOGST_BLOCK_NAME = 'ln/oogst';
const CLASSIC_BLOCK_NAMES = [ 'core/freeform', 'core/classic' ];
const AUTO_CONVERSION_NOTICE_ID = 'ln-auto-bibliographics-conversion';
const TEMPLATE_BY_CONTENT_TYPE = {
	recensie: 'recensie',
	oogst: 'single-oogst',
	interview: 'single-interview',
};

const debugLog = ( message, context = {} ) => {
	if ( ! window?.lnAutoInsertBibliographicsDebug ) {
		return;
	}

	// eslint-disable-next-line no-console
	console.info( '[LN AutoInsert Bibliographics]', message, context );
};

const flattenBlocks = ( blocks ) => {
	const queue = Array.isArray( blocks ) ? [ ...blocks ] : [];
	const result = [];

	while ( queue.length > 0 ) {
		const current = queue.shift();

		if ( ! current ) {
			continue;
		}

		result.push( current );

		if (
			Array.isArray( current.innerBlocks ) &&
			current.innerBlocks.length > 0
		) {
			queue.unshift( ...current.innerBlocks );
		}
	}

	return result;
};

const hasClassicBlock = ( flatBlocks ) =>
	flatBlocks.some( ( block ) => CLASSIC_BLOCK_NAMES.includes( block.name ) );

const hasBibliographicsBlock = ( flatBlocks ) =>
	flatBlocks.some( ( block ) => block.name === BIBLIO_BLOCK_NAME );

const getEditorPostContext = () => {
	const editorStore = select( 'core/editor' );

	if (
		! editorStore ||
		typeof editorStore.getCurrentPostId !== 'function' ||
		typeof editorStore.getCurrentPostType !== 'function'
	) {
		return null;
	}

	const postId = editorStore.getCurrentPostId();
	const postType = editorStore.getCurrentPostType();
	const meta =
		typeof editorStore.getEditedPostAttribute === 'function'
			? editorStore.getEditedPostAttribute( 'meta' ) || {}
			: {};
	const categoryIds =
		typeof editorStore.getEditedPostAttribute === 'function'
			? editorStore.getEditedPostAttribute( 'categories' ) || []
			: [];
	const rawContent =
		typeof editorStore.getEditedPostAttribute === 'function'
			? editorStore.getEditedPostAttribute( 'content' ) || ''
			: '';

	if ( ! postId || ! postType ) {
		return null;
	}

	return {
		postId,
		postType,
		categoryIds,
		rawContent,
		migrationStatus:
			typeof meta?.ln_bibliographics_migration_status === 'string'
				? meta.ln_bibliographics_migration_status
				: '',
	};
};

const isEmptyLegacyBibliographicsPost = (
	post,
	allBlocks,
	hasBibliographics,
	contentType
) => {
	if ( ! post || hasBibliographics ) {
		return false;
	}

	if ( 'oogst' !== contentType ) {
		return false;
	}

	if ( post.migrationStatus === 'blocktheme' ) {
		return false;
	}

	if ( Array.isArray( allBlocks ) && allBlocks.length > 0 ) {
		return false;
	}

	return ! String( post.rawContent || '' ).trim();
};

const getBibliographicFieldKeys = () => {
	const fieldConfig = window?.lnBibliographicFields?.fields;

	if ( ! fieldConfig || typeof fieldConfig !== 'object' ) {
		return [];
	}

	return Object.keys( fieldConfig );
};

const normalizeBibliographicValues = ( rawFields ) => {
	const keys = getBibliographicFieldKeys();
	const normalized = {};

	keys.forEach( ( key ) => {
		const value = rawFields?.[ key ];
		normalized[ key ] =
			value === null || typeof value === 'undefined' ? '' : value;
	} );

	return normalized;
};

const fetchPostBibliographics = async ( postId ) => {
	const response = await apiFetch( {
		path: `/x-ln/v1/bibliographics-post-meta/${ postId }`,
	} );

	return response?.fields && typeof response.fields === 'object'
		? response.fields
		: {};
};

const fetchOogstEntries = async ( postId ) => {
	const response = await apiFetch( {
		path: `/x-ln/v1/bibliographics-oogst-entries/${ postId }`,
	} );

	return Array.isArray( response?.entries ) ? response.entries : [];
};

const buildColumnsTemplateWithBibliographics = (
	legacyBlocks,
	bibliographic
) => {
	const bibliographicsBlock = createBlock( BIBLIO_BLOCK_NAME, {
		bibliographic,
		boektitel: bibliographic.boektitel || '',
		isbn: bibliographic.isbn || '',
	} );
	const leftColumnBlocks = legacyBlocks.map( ( block ) =>
		cloneBlock( block )
	);

	const leftColumn = createBlock(
		'core/column',
		{ width: '66.66%', className: 'ln-padding' },
		leftColumnBlocks
	);

	const rightColumn = createBlock(
		'core/column',
		{ width: '33.33%', className: 'ln-padding' },
		[ bibliographicsBlock ]
	);

	return createBlock( 'core/columns', {}, [ leftColumn, rightColumn ] );
};

const buildBoekColumnsBlock = ( contentBlocks, bibliographic ) => {
	const leftColumn = createBlock(
		'core/column',
		{
			className: 'ln-padding',
			width: '66.66%',
			lock: { move: true, remove: true },
		},
		contentBlocks
	);

	const rightColumn = createBlock(
		'core/column',
		{
			className: 'ln-padding',
			width: '33.33%',
			lock: { move: true, remove: true },
		},
		[
			createBlock( BIBLIO_BLOCK_NAME, {
				className: 'ln-boek__right',
				lock: { move: true, remove: true },
				bibliographic,
				boektitel: bibliographic.boektitel || '',
				isbn: bibliographic.isbn || '',
			} ),
		]
	);

	return createBlock(
		'core/columns',
		{
			lock: { move: true, remove: true },
		},
		[ leftColumn, rightColumn ]
	);
};

const htmlToContentBlocks = ( content ) => {
	if ( ! content || ! String( content ).trim() ) {
		return [];
	}

	const parsedBlocks = rawHandler( { HTML: String( content ) } );

	if ( Array.isArray( parsedBlocks ) && parsedBlocks.length > 0 ) {
		return parsedBlocks;
	}

	return [ createBlock( 'core/html', { content: String( content ) } ) ];
};

const normalizeOogstEntries = ( entries ) => {
	if ( ! Array.isArray( entries ) ) {
		return [];
	}

	return entries
		.map( ( entry ) => ( {
			bibliographic: normalizeBibliographicValues( entry?.bibliographic ),
			content: typeof entry?.content === 'string' ? entry.content : '',
		} ) )
		.filter( ( entry ) => {
			if ( entry.content.trim() ) {
				return true;
			}

			return Object.values( entry.bibliographic ).some(
				( value ) => value
			);
		} );
};

const buildOogstTitleBlock = ( bibliographic ) => {
	const title = String( bibliographic?.boektitel || '' ).trim();

	if ( ! title ) {
		return null;
	}

	return createBlock( 'core/heading', {
		level: 3,
		content: title,
	} );
};

const buildOogstTemplate = ( entries ) => {
	const boekBlocks = entries.map( ( entry ) => {
		const contentBlocks = htmlToContentBlocks( entry.content );
		const titleBlock = buildOogstTitleBlock( entry.bibliographic );
		const leftColumnBlocks = titleBlock
			? [ titleBlock, ...contentBlocks ]
			: contentBlocks;
		const columnsBlock = buildBoekColumnsBlock(
			leftColumnBlocks,
			entry.bibliographic
		);

		return createBlock( BOEK_BLOCK_NAME, {}, [ columnsBlock ] );
	} );

	return createBlock( OOGST_BLOCK_NAME, {}, boekBlocks );
};

const replaceContentWithBlocks = ( blocks ) => {
	dispatch( 'core/block-editor' ).resetBlocks( blocks );
};

const ensureTemplateForContentType = ( contentType ) => {
	const template = TEMPLATE_BY_CONTENT_TYPE[ contentType ];

	if ( ! template ) {
		return;
	}

	const editorStore = select( 'core/editor' );

	if (
		! editorStore ||
		typeof editorStore.getEditedPostAttribute !== 'function'
	) {
		return;
	}

	const currentTemplate =
		editorStore.getEditedPostAttribute( 'template' ) || '';

	if ( currentTemplate === template ) {
		return;
	}

	dispatch( 'core/editor' ).editPost( { template } );
	debugLog( 'Assigned post template for content type', {
		contentType,
		template,
		currentTemplate,
	} );
};

let isAutoConversionNoticeVisible = false;

const clearAutoConversionNotice = () => {
	if ( ! isAutoConversionNoticeVisible ) {
		return;
	}

	isAutoConversionNoticeVisible = false;

	const noticesStore = dispatch( 'core/notices' );

	if ( noticesStore && typeof noticesStore.removeNotice === 'function' ) {
		noticesStore.removeNotice( AUTO_CONVERSION_NOTICE_ID );
	}
};

const showAutoConversionNotice = () => {
	if ( isAutoConversionNoticeVisible ) {
		return;
	}

	const noticesStore = dispatch( 'core/notices' );

	if ( ! noticesStore || typeof noticesStore.createNotice !== 'function' ) {
		return;
	}

	isAutoConversionNoticeVisible = true;

	noticesStore.createNotice(
		'info',
		__(
			'This post was automatically converted to the LN Block Theme. Click Save to keep this layout.',
			'x-literair-nederland-blocks'
		),
		{
			id: AUTO_CONVERSION_NOTICE_ID,
			isDismissible: true,
			type: 'snackbar',
		}
	);
};

let previousPostId = null;
let previousHadClassic = false;
let insertedForCurrentPost = false;
let isInsertionInProgress = false;

subscribe( async () => {
	const post = getEditorPostContext();

	if ( ! post ) {
		if ( isAutoConversionNoticeVisible ) {
			clearAutoConversionNotice();
		}
		previousPostId = null;
		previousHadClassic = false;
		insertedForCurrentPost = false;
		isInsertionInProgress = false;
		return;
	}

	if (
		post.postType !== 'post' &&
		post.postType !== 'recensie' &&
		post.postType !== 'interview'
	) {
		return;
	}

	if ( previousPostId !== post.postId ) {
		if ( isAutoConversionNoticeVisible ) {
			clearAutoConversionNotice();
		}
		previousPostId = post.postId;
		previousHadClassic = false;
		insertedForCurrentPost = false;
		isInsertionInProgress = false;
		debugLog( 'Reset state for post', {
			postId: post.postId,
			postType: post.postType,
		} );
	}

	const resolvedContentType =
		await getAutoInsertBibliographicsContentType( post );

	const allBlocks = flattenBlocks(
		select( 'core/block-editor' ).getBlocks()
	);
	const currentHasClassic = hasClassicBlock( allBlocks );
	const currentHasBibliographics = hasBibliographicsBlock( allBlocks );
	const emptyLegacyPost = isEmptyLegacyBibliographicsPost(
		post,
		allBlocks,
		currentHasBibliographics,
		resolvedContentType
	);

	if ( currentHasBibliographics ) {
		insertedForCurrentPost = true;
	}

	const justConvertedClassic = previousHadClassic && ! currentHasClassic;
	previousHadClassic = currentHasClassic;

	if ( ! justConvertedClassic && ! emptyLegacyPost ) {
		return;
	}

	if ( justConvertedClassic ) {
		debugLog( 'Detected classic-to-blocks conversion', {
			postId: post.postId,
			hadClassicBefore: true,
			hasClassicNow: currentHasClassic,
		} );
	} else {
		debugLog( 'Detected empty legacy bibliographics post', {
			postId: post.postId,
			postType: post.postType,
			migrationStatus: post.migrationStatus,
		} );
	}

	if (
		insertedForCurrentPost ||
		currentHasBibliographics ||
		isInsertionInProgress
	) {
		debugLog( 'Skipping insertion due to guard', {
			insertedForCurrentPost,
			currentHasBibliographics,
			isInsertionInProgress,
			emptyLegacyPost,
			justConvertedClassic,
		} );
		return;
	}

	isInsertionInProgress = true;

	try {
		const contentType = resolvedContentType;
		const shouldInsert = await shouldAutoInsertBibliographics( post );

		debugLog( 'Evaluated trigger', {
			postId: post.postId,
			postType: post.postType,
			categoryIds: post.categoryIds,
			contentType,
			migrationStatus: post.migrationStatus,
			emptyLegacyPost,
			shouldInsert,
		} );

		if ( ! shouldInsert || ! contentType ) {
			return;
		}

		if ( 'oogst' === contentType ) {
			const oogstEntries = normalizeOogstEntries(
				await fetchOogstEntries( post.postId )
			);

			if ( oogstEntries.length === 0 ) {
				debugLog(
					'Skipping oogst template replace because no entries were found',
					{
						postId: post.postId,
					}
				);
				return;
			}

			const oogstTemplate = buildOogstTemplate( oogstEntries );

			ensureTemplateForContentType( contentType );
			replaceContentWithBlocks( [ oogstTemplate ] );
			insertedForCurrentPost = true;
			showAutoConversionNotice();
			debugLog(
				'Replaced content with oogst template and bibliographics blocks',
				{
					postId: post.postId,
					entryCount: oogstEntries.length,
				}
			);
			return;
		}

		if ( 'interview' === contentType ) {
			const legacyBlocks = select( 'core/block-editor' ).getBlocks();

			if (
				! Array.isArray( legacyBlocks ) ||
				legacyBlocks.length === 0
			) {
				debugLog(
					'Skipping interview template replace because no legacy blocks found',
					{
						postId: post.postId,
					}
				);
				return;
			}

			const hydratedMeta = await fetchPostBibliographics( post.postId );
			const bibliographic = normalizeBibliographicValues( hydratedMeta );
			const columnsTemplate = buildColumnsTemplateWithBibliographics(
				legacyBlocks,
				bibliographic
			);

			ensureTemplateForContentType( contentType );
			replaceContentWithBlocks( [ columnsTemplate ] );
			insertedForCurrentPost = true;
			showAutoConversionNotice();
			debugLog(
				'Replaced content with interview columns template and bibliographics block',
				{
					postId: post.postId,
					legacyBlockCount: legacyBlocks.length,
					fieldCount: Object.keys( bibliographic ).length,
				}
			);
			return;
		}

		const legacyBlocks = select( 'core/block-editor' ).getBlocks();

		if ( ! Array.isArray( legacyBlocks ) || legacyBlocks.length === 0 ) {
			debugLog(
				'Skipping template replace because no legacy blocks found',
				{
					postId: post.postId,
				}
			);
			return;
		}

		const hydratedMeta = await fetchPostBibliographics( post.postId );
		const bibliographic = normalizeBibliographicValues( hydratedMeta );
		const columnsTemplate = buildColumnsTemplateWithBibliographics(
			legacyBlocks,
			bibliographic
		);

		ensureTemplateForContentType( contentType );
		replaceContentWithBlocks( [ columnsTemplate ] );
		insertedForCurrentPost = true;
		showAutoConversionNotice();
		debugLog(
			'Replaced content with recensie columns template and bibliographics block',
			{
				postId: post.postId,
				legacyBlockCount: legacyBlocks.length,
				fieldCount: Object.keys( bibliographic ).length,
			}
		);
	} catch ( error ) {
		insertedForCurrentPost = false;
		debugLog( 'Insertion failed', { postId: post.postId, error } );
	} finally {
		isInsertionInProgress = false;
	}
} );

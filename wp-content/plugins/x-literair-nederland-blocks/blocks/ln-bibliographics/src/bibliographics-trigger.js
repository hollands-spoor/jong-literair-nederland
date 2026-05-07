import apiFetch from '@wordpress/api-fetch';

// Switch this to "postType" when recensies moves from taxonomy to CPT.
const AUTO_INSERT_TRIGGER_MODE = 'category';
const AUTO_INSERT_CATEGORY_SLUGS = {
	recensie: 'recensies',
	oogst: 'oogst',
	interview: 'interview',
};
const AUTO_INSERT_POST_TYPE = 'recensie';

const categoryIdCache = {};
const categoryIdPromises = {};

const getTriggerCategoryId = async ( slug ) => {
	if ( Number.isInteger( categoryIdCache[ slug ] ) ) {
		return categoryIdCache[ slug ];
	}

	if ( ! categoryIdPromises[ slug ] ) {
		categoryIdPromises[ slug ] = apiFetch( {
			path: `/wp/v2/categories?slug=${ encodeURIComponent(
				slug
			) }&_fields=id`,
		} )
			.then( ( terms ) => {
				const resolvedId =
					Array.isArray( terms ) &&
					terms[ 0 ] &&
					Number.isInteger( terms[ 0 ].id )
						? terms[ 0 ].id
						: null;

				categoryIdCache[ slug ] = resolvedId;

				return resolvedId;
			} )
			.catch( () => null )
			.finally( () => {
				categoryIdPromises[ slug ] = null;
			} );
	}

	return categoryIdPromises[ slug ];
};

const getTriggerContentTypeForCategory = async ( post ) => {
	for ( const [ contentType, slug ] of Object.entries(
		AUTO_INSERT_CATEGORY_SLUGS
	) ) {
		const categoryId = await getTriggerCategoryId( slug );

		if ( ! Number.isInteger( categoryId ) ) {
			continue;
		}

		if (
			Array.isArray( post.categoryIds ) &&
			post.categoryIds.includes( categoryId )
		) {
			return contentType;
		}
	}

	return null;
};

const getTriggerContentTypeForPostType = async ( post ) => {
	if ( post.postType === 'interview' ) {
		return 'interview';
	}

	if ( post.postType === AUTO_INSERT_POST_TYPE ) {
		return 'recensie';
	}

	return null;
};

const shouldTriggerForPostType = async ( post ) =>
	null !== ( await getTriggerContentTypeForPostType( post ) );

export const getAutoInsertBibliographicsContentType = async ( post ) => {
	if ( ! post || ! post.postId ) {
		return null;
	}

	if ( AUTO_INSERT_TRIGGER_MODE === 'postType' ) {
		return getTriggerContentTypeForPostType( post );
	}

	return getTriggerContentTypeForCategory( post );
};

const shouldTriggerForCategory = async ( post ) =>
	null !== ( await getTriggerContentTypeForCategory( post ) );

export const shouldAutoInsertBibliographics = async ( post ) => {
	if ( ! post || ! post.postId ) {
		return false;
	}

	// Single decision point for trigger strategy selection.
	// Keep conversion/insertion logic independent from this switch.
	if ( AUTO_INSERT_TRIGGER_MODE === 'postType' ) {
		return shouldTriggerForPostType( post );
	}

	return shouldTriggerForCategory( post );
};

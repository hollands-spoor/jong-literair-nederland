/**
 * Category Reaction Script
 *
 * Watches for category changes in the block editor and reacts by setting
 * the post template and inserting the appropriate block (ln/boek or ln/oogst).
 *
 * Expects a global `xlnCatReact` object (via wp_localize_script) with:
 *   - recensiesId  {number|null}
 *   - oogstId      {number|null}
 *   - interviewId  {number|null}
 *
 * @package
 */
/* global xlnCatReact */
( function () {
	if ( typeof xlnCatReact === 'undefined' ) {
		return;
	}

	const config = xlnCatReact;
	let prevCatStr = null;
	const guardBlocks = [ 'ln/boek', 'ln/oogst' ];

	function toInt( value ) {
		const parsed = Number( value );

		return Number.isInteger( parsed ) ? parsed : null;
	}

	function normalizeIds( values ) {
		if ( ! Array.isArray( values ) ) {
			return [];
		}

		return values.map( toInt ).filter( function ( id ) {
			return id !== null;
		} );
	}

	function hasAnyGuardBlock( blocks ) {
		return blocks.some( function ( block ) {
			return guardBlocks.indexOf( block.name ) !== -1;
		} );
	}

	/**
	 * Defers a block insertion until the editor has finished loading the post
	 * entity (getCurrentPostId() becomes truthy = auto-draft is created and the
	 * block list is stable). Only inserts when no ln/boek or ln/oogst block
	 * already exists.
	 *
	 * @param {string} blockName Block name to insert when ready.
	 */
	function insertWhenReady( blockName ) {
		const unsub = wp.data.subscribe( function () {
			if ( ! wp.data.select( 'core/editor' ).getCurrentPostId() ) {
				return;
			}
			unsub();
			const existing = wp.data.select( 'core/block-editor' ).getBlocks();
			if ( ! hasAnyGuardBlock( existing ) ) {
				wp.data
					.dispatch( 'core/block-editor' )
					.insertBlocks( wp.blocks.createBlock( blockName, {} ) );
			}
		} );
	}

	wp.data.subscribe( function () {
		const postType = wp.data.select( 'core/editor' ).getCurrentPostType();
		if ( ! postType ) {
			return;
		}

		const cats = normalizeIds(
			wp.data
				.select( 'core/editor' )
				.getEditedPostAttribute( 'categories' ) || []
		);
		const catStr = JSON.stringify(
			cats.slice().sort( function ( a, b ) {
				return a - b;
			} )
		);

		// Capture the initial snapshot without reacting to it.
		if ( prevCatStr === null ) {
			prevCatStr = catStr;
			return;
		}

		if ( catStr === prevCatStr ) {
			return;
		}

		const prev = normalizeIds( JSON.parse( prevCatStr ) );
		prevCatStr = catStr;
		const added = cats.filter( function ( id ) {
			return prev.indexOf( id ) === -1;
		} );

		if ( added.length === 0 ) {
			return;
		}

		if (
			toInt( config.recensiesId ) !== null &&
			added.indexOf( toInt( config.recensiesId ) ) !== -1
		) {
			wp.data
				.dispatch( 'core/editor' )
				.editPost( { template: 'recensie' } );
			insertWhenReady( 'ln/boek' );
		}

		if (
			toInt( config.oogstId ) !== null &&
			added.indexOf( toInt( config.oogstId ) ) !== -1
		) {
			wp.data
				.dispatch( 'core/editor' )
				.editPost( { template: 'single-oogst' } );
			insertWhenReady( 'ln/oogst' );
		}

		if (
			toInt( config.interviewId ) !== null &&
			added.indexOf( toInt( config.interviewId ) ) !== -1
		) {
			wp.data
				.dispatch( 'core/editor' )
				.editPost( { template: 'single-interview' } );
			insertWhenReady( 'ln/boek' );
		}
	} );
} )();

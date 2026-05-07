/**
 * Oogst inserter guard.
 *
 * Prevents inserting more than one ln/oogst block per post,
 * regardless of where it is nested.
 *
 * @package XLn
 */
( function() {
	if ( ! window.wp || ! wp.hooks || ! wp.data ) {
		return;
	}

	var noticeId = 'xln-oogst-inserter-guard';
	var lastNoticeMessage = null;
	var lastNoticeTimestamp = 0;
	var seenClientIds = null;
	var isEnforcing = false;

	function getBlockEditorSelect() {
		return wp.data.select( 'core/block-editor' );
	}

	function getBlockEditorDispatch() {
		return wp.data.dispatch( 'core/block-editor' );
	}

	function isInserterOpen() {
		var selector = wp.data.select( 'core/edit-post' );

		if ( ! selector || typeof selector.isInserterOpened !== 'function' ) {
			return false;
		}

		return !! selector.isInserterOpened();
	}

	function showRestrictionNotice( message ) {
		var now = Date.now();

		if ( lastNoticeMessage === message && now - lastNoticeTimestamp < 3000 ) {
			return;
		}

		lastNoticeMessage = message;
		lastNoticeTimestamp = now;

		var noticeStore = wp.data.dispatch( 'core/notices' );
		if ( ! noticeStore || typeof noticeStore.createNotice !== 'function' ) {
			return;
		}

		if ( typeof noticeStore.removeNotice === 'function' ) {
			noticeStore.removeNotice( noticeId );
		}

		noticeStore.createNotice( 'warning', message, {
			id: noticeId,
			type: 'snackbar',
			isDismissible: true,
		} );
	}

	function getRootBlocks() {
		var selector = getBlockEditorSelect();
		if ( ! selector || typeof selector.getBlocks !== 'function' ) {
			return [];
		}

		return selector.getBlocks();
	}

	function collectBlocksRecursively( blocks, out ) {
		blocks.forEach( function( block ) {
			if ( ! block || ! block.clientId ) {
				return;
			}

			out.push( block );

			if ( Array.isArray( block.innerBlocks ) && block.innerBlocks.length ) {
				collectBlocksRecursively( block.innerBlocks, out );
			}
		} );
	}

	function getAllBlocksFlat() {
		var rootBlocks = getRootBlocks();
		var allBlocks = [];

		collectBlocksRecursively( rootBlocks, allBlocks );

		return allBlocks;
	}

	function getCurrentClientIdSet() {
		var set = new Set();

		getAllBlocksFlat().forEach( function( block ) {
			set.add( block.clientId );
		} );

		return set;
	}

	function getOogstCount() {
		return getAllBlocksFlat().filter( function( block ) {
			return block && block.name === 'ln/oogst';
		} ).length;
	}

	function removeBlocksSafely( clientIds ) {
		var selector = getBlockEditorSelect();
		var dispatcher = getBlockEditorDispatch();

		if ( ! selector || ! dispatcher || typeof selector.getBlock !== 'function' ) {
			return;
		}

		var uniqueExistingIds = Array.from( new Set( clientIds ) ).filter( function( clientId ) {
			return !! selector.getBlock( clientId );
		} );

		if ( uniqueExistingIds.length === 0 ) {
			return;
		}

		// Remove one-by-one to avoid occasional Gutenberg bulk-remove race issues.
		uniqueExistingIds.forEach( function( clientId ) {
			if ( typeof dispatcher.removeBlock === 'function' && selector.getBlock( clientId ) ) {
				dispatcher.removeBlock( clientId );
			}
		} );
	}

	function enforceOogstRestrictions() {
		if ( isEnforcing ) {
			return;
		}

		var allBlocks = getAllBlocksFlat();
		var currentSet = new Set();

		allBlocks.forEach( function( block ) {
			currentSet.add( block.clientId );
		} );

		if ( seenClientIds === null ) {
			seenClientIds = currentSet;
			return;
		}

		var newlyAddedOogst = allBlocks.filter( function( block ) {
			return block.name === 'ln/oogst' && ! seenClientIds.has( block.clientId );
		} );

		if ( newlyAddedOogst.length === 0 ) {
			seenClientIds = currentSet;
			return;
		}

		var toRemove = [];

		newlyAddedOogst.forEach( function( block ) {
			if ( getOogstCount() > 1 ) {
				toRemove.push( block.clientId );
				showRestrictionNotice( 'Only one Harvest block is allowed in this post.' );
			}
		} );

		if ( toRemove.length > 0 ) {
			isEnforcing = true;
			try {
				removeBlocksSafely( toRemove );
			} finally {
				isEnforcing = false;
			}
		}

		seenClientIds = getCurrentClientIdSet();
	}

	wp.hooks.addFilter(
		'editor.canInsertBlockType',
		'xln/oogst-can-insert',
		function( canInsert, blockType ) {
			if ( ! canInsert ) {
				return canInsert;
			}

			var blockName = typeof blockType === 'string' ? blockType : ( blockType && blockType.name );
			if ( blockName !== 'ln/oogst' ) {
				return canInsert;
			}

			if ( getOogstCount() >= 1 ) {
				if ( isInserterOpen() ) {
					showRestrictionNotice( 'Only one Harvest block is allowed in this post.' );
				}

				return false;
			}

			return true;
		}
	);

	wp.data.subscribe( enforceOogstRestrictions );
} )();

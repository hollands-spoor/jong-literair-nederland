<?php
/**
 * Dashboard quick-start widget for X LN Blocks.
 *
 * Registers a WordPress dashboard widget when the 'show_quick_start_widget'
 * plugin option is enabled.
 *
 * Quick-action links carry an ?xln_preset_cat=<slug> parameter.
 * xln_store_new_post_preset() saves a short-lived per-user transient on
 * load-post-new.php. xln_apply_preset_to_auto_draft() hooks into
 * wp_after_insert_post which fires inside get_default_post_to_edit() —
 * BEFORE edit-form-blocks.php preloads the REST data for the editor.
 * Patching the post there means the block editor receives block content,
 * category and template from the very first preloaded REST response.
 *
 * When a recensies or oogst category is added manually, the JS subscriber
 * registered by xln_enqueue_category_reaction() handles template + block.
 *
 * @package XLn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_dashboard_setup', 'xln_register_quick_start_widget' );

function xln_register_quick_start_widget(): void {
	$options = xln_get_options();

	if ( empty( $options['show_quick_start_widget'] ) ) {
		return;
	}

	wp_add_dashboard_widget(
		'xln_quick_start',
		__( 'Literair Nederland – Quick Start', 'x-literair-nederland-blocks' ),
		'xln_render_quick_start_widget',
		null,  // control callback
		null,  // callback args
		'normal',
		'high'
	);
}

function xln_render_quick_start_widget(): void {
	$settings_url = admin_url( 'options-general.php?page=xln-settings' );
	$new_recensie = admin_url( 'post-new.php?xln_preset_cat=recensies' );
	$new_oogst    = admin_url( 'post-new.php?xln_preset_cat=oogst' );
	$new_interview = admin_url( 'post-new.php?xln_preset_cat=interview' );
	?>
	<div class="xln-quick-start">
		<p><?php esc_html_e( 'Welcome to Literair Nederland! Here are some quick links to get you started.', 'x-literair-nederland-blocks' ); ?></p>

		<ul>
			<li>
				<strong><?php esc_html_e( 'Add review', 'x-literair-nederland-blocks' ); ?></strong><br />
				<a href="<?php echo esc_url( $new_recensie ); ?>">
					<?php esc_html_e( 'Write a new review', 'x-literair-nederland-blocks' ); ?>
				</a>
			</li>
			<li>
				<strong><?php esc_html_e( 'Add harvest post', 'x-literair-nederland-blocks' ); ?></strong><br />
				<a href="<?php echo esc_url( $new_oogst ); ?>">
					<?php esc_html_e( 'Write a new harvest post', 'x-literair-nederland-blocks' ); ?>
				</a>
			</li>
			<li>
				<strong><?php esc_html_e( 'Add interview', 'x-literair-nederland-blocks' ); ?></strong><br />
				<a href="<?php echo esc_url( $new_interview ); ?>">
					<?php esc_html_e( 'Write a new interview', 'x-literair-nederland-blocks' ); ?>
				</a>
			</li>
		</ul>

		<p class="description">
			<?php
			printf(
				
				wp_kses(
					/* translators: %s: URL to settings page */
					__( 'You can hide this widget in the <a href="%s">LN Blocks settings</a>.', 'x-literair-nederland-blocks' ),
					[ 'a' => [ 'href' => [] ] ]
				),
				esc_url( $settings_url )
			);
			?>
		</p>
		<p>
			<?php
			printf(
				/* translators: %s: URL to documentation */
				wp_kses(
					__( 'For more detailed instructions, please refer to the <a href="%s" target="_blank" rel="noopener">documentation</a>.', 'x-literair-nederland-blocks' ),
					[ 'a' => [ 'href' => [], 'target' => [], 'rel' => [] ] ]
				),
				esc_url( home_url( '/wp-content/uploads/2026/04/LN-Instructie.pdf' ) )
			);
			?>	
		</p>
	</div>
	<?php
}

// -----------------------------------------------------------------------
// Server-side preset for post-new.php via ?xln_preset_cat=<slug>
//
// 1. xln_store_new_post_preset()        — saves transient + registers handler
// 2. xln_apply_preset_to_auto_draft()   — fires in wp_after_insert_post
//                                         (inside get_default_post_to_edit),
//                                         patches content/category/template
//                                         before the block editor preloads REST
// -----------------------------------------------------------------------

/**
 * Map of recognised category slugs to their preset config.
 *
 * 'block' must be the exact serialized block markup that passes block
 * validation: block comment opener + save() HTML output + closer.
 * Both ln/boek and ln/oogst have save() → <div class="wp-block-ln-{name}
 * alignwide ln-{name}"><InnerBlocks.Content /></div>. Default align:'wide'
 * is stripped from the comment attrs by the WP serializer, so no JSON in
 * the comment. If these class names ever change, update accordingly.
 */
function xln_get_new_post_presets(): array {
	return [
		'recensies' => [
			'block'    => "<!-- wp:ln/boek -->\n<div class=\"wp-block-ln-boek alignwide ln-boek  ln-boek--bibliographics-right\"></div>\n<!-- /wp:ln/boek -->",
			'template' => 'recensie',
		],
		'oogst'     => [
			'block'    => "<!-- wp:ln/oogst -->\n<div class=\"wp-block-ln-oogst alignwide ln-oogst\"></div>\n<!-- /wp:ln/oogst -->",
			'template' => 'single-oogst',
		],
		'interview' => [
			'block'    => "<!-- wp:ln/boek -->\n<div class=\"wp-block-ln-boek alignwide ln-boek  ln-boek--bibliographics-right\"></div>\n<!-- /wp:ln/boek -->",
			'template' => 'single-interview',
		],
	];
}

add_action( 'load-post-new.php', 'xln_store_new_post_preset' );

function xln_store_new_post_preset(): void {
	if ( empty( $_GET['xln_preset_cat'] ) || ! current_user_can( 'edit_posts' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		return;
	}

	$slug    = sanitize_key( $_GET['xln_preset_cat'] ); // phpcs:ignore WordPress.Security.NonceVerification
	$presets = xln_get_new_post_presets();

	if ( ! array_key_exists( $slug, $presets ) ) {
		return;
	}

	$term = get_term_by( 'slug', $slug, 'category' );
	if ( ! $term instanceof WP_Term ) {
		return;
	}

	set_transient(
		'xln_new_post_preset_' . get_current_user_id(),
		array_merge( $presets[ $slug ], [ 'term_id' => (int) $term->term_id ] ),
		5 * MINUTE_IN_SECONDS
	);

	// Register the one-shot handler that patches the auto-draft.
	add_action( 'wp_after_insert_post', 'xln_apply_preset_to_auto_draft', 10, 3 );
}

/**
 * Patches the newly created auto-draft with block content, category and template.
 *
 * Fires inside get_default_post_to_edit() → wp_insert_post(), which runs
 * before edit-form-blocks.php preloads the REST data. The transient is deleted
 * before wp_update_post() to prevent any re-entrant trigger.
 *
 * @param int     $post_id Post ID.
 * @param WP_Post $post    Post object.
 * @param bool    $update  Whether this is an update (true) or a new insert (false).
 */
function xln_apply_preset_to_auto_draft( int $post_id, WP_Post $post, bool $update ): void {
	// Only fire for fresh inserts of auto-draft posts.
	if ( $update || $post->post_status !== 'auto-draft' || $post->post_type !== 'post' ) {
		return;
	}

	$key    = 'xln_new_post_preset_' . get_current_user_id();
	$preset = get_transient( $key );
	if ( ! $preset ) {
		return;
	}

	// Delete transient BEFORE wp_update_post to prevent reentrant triggering.
	delete_transient( $key );

	wp_update_post( [
		'ID'           => $post_id,
		'post_content' => $preset['block'],
	] );

	wp_set_post_categories( $post_id, [ $preset['term_id'] ] );

	if ( ! empty( $preset['template'] ) ) {
		update_post_meta( $post_id, '_wp_page_template', $preset['template'] );
	}
}

// -----------------------------------------------------------------------
// React to category changes: set template + insert block
//
// Registered directly on enqueue_block_editor_assets and scoped to post
// editor screens to avoid missing script registration in some admin flows.
// -----------------------------------------------------------------------

add_action( 'enqueue_block_editor_assets', 'xln_enqueue_category_reaction' );

function xln_enqueue_category_reaction(): void {
	if ( ! current_user_can( 'edit_posts' ) ) {
		return;
	}

	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

	if ( ! $screen || ( 'post' !== $screen->base && 'post-new' !== $screen->base ) ) {
		return;
	}

	$recensies = get_term_by( 'slug', 'recensies', 'category' );
	$oogst     = get_term_by( 'slug', 'oogst', 'category' );
	$interview = get_term_by( 'slug', 'interview', 'category' );

	if ( ! $recensies && ! $oogst && ! $interview ) {
		return;
	}

	$asset_url = plugins_url( 'assets/xln-category-reaction.js', __DIR__ );
	$asset_path = dirname( __DIR__ ) . '/assets/xln-category-reaction.js';

	wp_enqueue_script(
		'xln-category-reaction',
		$asset_url,
		[ 'wp-data', 'wp-blocks', 'wp-block-editor' ],
		(string) filemtime( $asset_path ),
		true
	);

	wp_localize_script( 'xln-category-reaction', 'xlnCatReact', [
		'recensiesId' => $recensies instanceof WP_Term ? (int) $recensies->term_id : null,
		'oogstId'     => $oogst instanceof WP_Term ? (int) $oogst->term_id : null,
		'interviewId' => $interview instanceof WP_Term ? (int) $interview->term_id : null,
	] );
}

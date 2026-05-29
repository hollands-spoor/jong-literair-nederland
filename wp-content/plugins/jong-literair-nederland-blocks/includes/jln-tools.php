<?php
/**
 * Admin tools page scaffold for Literair Nederland blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if( ! class_exists( 'Jln_Tools' ) ) {
	class Jln_Tools {
		/**
		 * Tools page hook suffix.
		 *
		 * @var string
		 */
		private $page_hook = '';

		/**
		 * Register WordPress hooks.
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register_tools_page' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
			add_action( 'wp_ajax_ln_convert_users', array( $this, 'ajax_convert_users' ) );
			add_action( 'wp_ajax_ln_add_auteur_recensie', array( $this, 'ajax_add_auteur_recensie' ) );
		}

		/**
		 * Add tools page under Tools menu.
		 */
		public function register_tools_page() {
			$this->page_hook = add_management_page(
				__( 'Jong Literair Nederland Tools', 'jong-literair-nederland-blocks' ),
				__( 'Jong Literair Nederland Tools', 'jong-literair-nederland-blocks' ),
				'manage_options',
				'jong-literair-nederland-tools',
				array( $this, 'render_tools_page' )
			);
		}

		/**
		 * Enqueue assets for tools page only.
		 *
		 * @param string $hook Current admin page hook.
		 */
		public function enqueue_admin_assets( $hook ) {
			if ( $hook !== $this->page_hook ) {
				return;
			}

			wp_register_script( 'literair-nederland-tools-admin', false, array(), '0.1.0', true );
			wp_enqueue_script( 'literair-nederland-tools-admin' );

			$inline_script = sprintf(
				'window.lnTools = { ajaxUrl: %s, nonce: %s };
				document.addEventListener("DOMContentLoaded", function () {
					if (!window.lnTools) {
						return;
					}

					var output = document.getElementById("ln-tools-output");

					function runAction(button, target, payload, runningText) {
						if (!button || !target) {
							return;
						}

						button.disabled = true;
						target.textContent = runningText;

						fetch(window.lnTools.ajaxUrl, {
							method: "POST",
							headers: {
								"Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
							},
							body: new URLSearchParams(payload).toString()
						})
							.then(function (response) {
								return response.json();
							})
							.then(function (data) {
								if (data && data.success) {
									target.innerHTML = (data.data && typeof data.data.message !== "undefined") ? data.data.message : "";
									return;
								}
								target.innerHTML = (data && data.data && data.data.message) ? data.data.message : "An error occurred.";
							})
							.catch(function () {
								target.textContent = "An error occurred.";
							})
							.finally(function () {
								button.disabled = false;
							});
					}

					var convertButton = document.getElementById("ln-convert-users-btn");
					if (convertButton && output) {
						convertButton.addEventListener("click", function () {
							runAction(
								convertButton,
								output,
								{
									action: "ln_convert_users",
									nonce: window.lnTools.nonce
								},
								"Running user conversion..."
							);
						});
					}

					var addAuteurRecensieButton = document.getElementById("ln-add-auteur-recensie-btn");
					if (addAuteurRecensieButton && output) {
						addAuteurRecensieButton.addEventListener("click", function () {
							runAction(
								addAuteurRecensieButton,
								output,
								{
									action: "ln_add_auteur_recensie",
									nonce: window.lnTools.nonce
								},
								"Adding auteur_recensie for 10 posts..."
							);
						});
					}

				});',
				wp_json_encode( admin_url( 'admin-ajax.php' ) ),
				wp_json_encode( wp_create_nonce( 'ln_tools_nonce' ) )
			);

			wp_add_inline_script( 'literair-nederland-tools-admin', $inline_script );
		}

		/**
		 * Render tools page markup.
		 */
		public function render_tools_page() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			?>
			<div class="wrap">
				<h1><?php echo esc_html__( 'Jong Literair Nederland Tools', 'jong-literair-nederland-blocks' ); ?></h1>
				<p>
					<?php echo esc_html__( 'This page provides tools to assist with migrating content for the Jong Literair Nederland block-based theme. Please review each tool\'s description and ensure you have a backup before running migration operations.', 'jong-literair-nederland-blocks' ); ?>
				</p>
				<h2>
					<?php echo esc_html__('Convert Users to Recensenten', 'jong-literair-nederland-blocks' ); ?>

				</h2>
				<p>
					<?php echo esc_html__( 'Convert up to 10 users at a time by creating a recensent post for each user. Run multiple times until no more users are found to convert.', 'jong-literair-nederland-blocks' ); ?>
				</p>
				<p>
					<button id="ln-convert-users-btn" class="button button-primary" type="button">
						<?php echo esc_html__( 'Convert users', 'jong-literair-nederland-blocks' ); ?>
					</button>
				</p>
				<div id="ln-tools-output" style="min-height: 2em;"></div>
				<h2>
					<?php echo esc_html__( 'Add auteur_recensie to Posts', 'jong-literair-nederland-blocks' ); ?>
				</h2>
				<p>
					<?php echo esc_html__( 'Process up to 10 posts per run. For each post without auteur_recensie, this copies the linked recensent ID from the post author user meta (_linked_recensent_id) into post meta auteur_recensie.', 'jong-literair-nederland-blocks' ); ?>
				</p>
				<p>
					<button id="ln-add-auteur-recensie-btn" class="button button-secondary" type="button">
						<?php echo esc_html__( 'Add auteur_recensie (10)', 'jong-literair-nederland-blocks' ); ?>
					</button>
				</p>
				<hr />
			</div>
			<?php
		}

		/**
		 * AJAX handler for convert users tool.
		 */
		public function ajax_convert_users() {
			$converted = '';
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Insufficient permissions.', 'jong-literair-nederland-blocks' ),
					),
					403
				);
			}

			check_ajax_referer( 'ln_tools_nonce', 'nonce' );

			$user_ids = $this->get_users_to_convert();

			if ( empty( $user_ids ) ) {
				wp_send_json_success(
					array(
						'message' => __( 'No users found to convert.', 'jong-literair-nederland-blocks' ),
					)
				);
			}

			$migrated      = 0;
			$failed_ids    = array();
			$failed_errors = array();

			foreach ( $user_ids as $user_id ) {
				update_user_meta( $user_id, 'migration_status', 'classic' );

				$result = $this->migrate_users( (int) $user_id );


				if ( is_wp_error( $result ) ) {
					update_user_meta( $user_id, 'migration_status', 'failed' );
					$failed_ids[]                   = (int) $user_id;
					$failed_errors[ (int) $user_id ] = $result->get_error_message();
					// dont get the name of the post but the nicename of the user.
					// $converted .= sprintf( 'Fail on ID %d, name: %s: %s<br />\n', $user_id, get_the_author_meta( 'user_nicename', $user_id ), $result->get_error_message() );
					continue;
				}

				update_user_meta( $user_id, 'migration_status', 'blocktheme' );

				// get the nicename of the user
				//$converted .= sprintf( 'Success on ID %d, name: %s<br />\n', $user_id, get_the_author_meta( 'display_name', $user_id ) );

				$migrated++;
			}

			$message = $converted;
			$message .= sprintf(
				/* translators: 1: migrated count, 2: attempted count */
				__( 'Migrated %1$d of %2$d users.', 'jong-literair-nederland-blocks' ),
				$migrated,
				count( $user_ids )
			);

			if ( ! empty( $failed_ids ) ) {
				$message .= ' ' . sprintf(
					/* translators: %s: comma-separated user IDs */
					__( 'Failed IDs: %s.', 'jong-literair-nederland-blocks' ),
					implode( ', ', $failed_ids )
				);
			}

			wp_send_json_success(
				array(
					'message' => $message,
					'failed'  => $failed_errors,
				)
			);
		}

		/**
		 * AJAX handler to add auteur_recensie post meta in batches.
		 */
		public function ajax_add_auteur_recensie() {
			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error(
					array(
						'message' => __( 'Insufficient permissions.', 'jong-literair-nederland-blocks' ),
					),
					403
				);
			}

			check_ajax_referer( 'ln_tools_nonce', 'nonce' );

			$post_ids = $this->get_posts_missing_auteur_recensie();

			if ( empty( $post_ids ) ) {
				wp_send_json_success(
					array(
						'message' => __( 'No posts found that are missing auteur_recensie.', 'jong-literair-nederland-blocks' ),
					)
				);
			}

			$updated     = 0;
			$skipped     = array();
			$skipped_msg = array();

			foreach ( $post_ids as $post_id ) {
				$post_author = (int) get_post_field( 'post_author', $post_id );

				if ( $post_author <= 0 ) {
					$skipped[]               = (int) $post_id;
					$skipped_msg[ $post_id ] = __( 'No valid post author.', 'jong-literair-nederland-blocks' );
					continue;
				}

				$linked_recensent_id = (int) get_user_meta( $post_author, '_linked_recensent_id', true );

				if ( $linked_recensent_id <= 0 ) {
					$skipped[]               = (int) $post_id;
					$skipped_msg[ $post_id ] = __( 'Author has no linked recensent ID.', 'jong-literair-nederland-blocks' );
					continue;
				}

				update_post_meta( $post_id, 'auteur_recensie', $linked_recensent_id );
				$updated++;
			}

			$message = sprintf(
				/* translators: 1: updated count, 2: attempted count */
				__( 'Updated auteur_recensie for %1$d of %2$d posts.', 'jong-literair-nederland-blocks' ),
				$updated,
				count( $post_ids )
			);

			if ( ! empty( $skipped ) ) {
				$message .= ' ' . sprintf(
					/* translators: %s: comma-separated post IDs */
					__( 'Skipped post IDs: %s.', 'jong-literair-nederland-blocks' ),
					implode( ', ', $skipped )
				);
			}

			wp_send_json_success(
				array(
					'message' => $message,
					'skipped' => $skipped_msg,
				)
			);
		}

		/**
		 * Fetch up to 10 users that still need conversion.
		 *
		 * @return int[]
		 */
		private function get_users_to_convert() {
			$query = new WP_User_Query(
				array(
					'number'     => 10,
					'orderby'    => 'ID',
					'order'      => 'ASC',
					'fields'     => 'ID',
					'meta_query' => array(
						'relation' => 'OR',
						array(
							'key'     => 'migration_status',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'migration_status',
							'value'   => 'blocktheme',
							'compare' => '!=',
						),
					),
				)
			);

			return array_map( 'intval', (array) $query->get_results() );
		}

		/**
		 * Fetch up to 10 posts that do not yet have auteur_recensie.
		 *
		 * @return int[]
		 */
		private function get_posts_missing_auteur_recensie() {
			$query = new WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => 'publish',
					'posts_per_page' => 100,
					'fields'         => 'ids',
					'orderby'        => 'ID',
					'order'          => 'ASC',
					'no_found_rows'  => true,
					'meta_query'     => array(
						array(
							'key'     => 'auteur_recensie',
							'compare' => 'NOT EXISTS',
						),
					),
				)
			);

			return array_map( 'intval', $query->posts );
		}

		/**
		 * Migrate a user to a recensent post by inserting a recensent cpt 
		 * get the contents of the user_meta with key 'description'
		 * set the featured image to the attachment with the ID in user_meta with key '_wpupa_attachment_id'.
		 *
		 * @param int $user_id User ID.
		 * @return true|WP_Error
		 */
		private function migrate_users( $user_id ) {
			$user = get_userdata( $user_id );

			if ( ! $user ) {
				return new WP_Error( 'invalid_user', __( 'Invalid user.', 'jong-literair-nederland-blocks' ) );
			}

			$post_data = array(
				'post_type'   => 'recensent',
				'post_title'  => $user->display_name,
				'post_status' => 'publish',
				'post_content' => get_user_meta( $user_id, 'description', true ),
			);
			$post_id = wp_insert_post( $post_data );
			if ( is_wp_error( $post_id ) ) {
				return new WP_Error( 'post_creation_failed', __( 'Failed to create recensent post.', 'jong-literair-nederland-blocks' ) );
			}

			$attachment_id = get_user_meta( $user_id, '_wpupa_attachment_id', true );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}

			// now add a post meta to link the user and the recensent post for reference.
			update_post_meta( $post_id, '_linked_user_id', $user_id );

			// and add a link from user to this recensent post as well
			update_user_meta( $user_id, '_linked_recensent_id', $post_id );

			// now give all existing posts a post_meta auteur_recensie
			// that is: set post_meta with key 'auteur_recensie' of all posts to get_user_meta( post_author, '_linked_recensent_id', true ) where post_author is the ID of the author of the post, it is a field in wp_posts.
			




			return true;
		}
	}
}
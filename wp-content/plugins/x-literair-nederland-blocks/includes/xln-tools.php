<?php
/**
 * Admin tools page scaffold for Literair Nederland blocks.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Xln_Tools {
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
		add_action( 'wp_ajax_ln_convert_recensenten', array( $this, 'ajax_convert_recensenten' ) );
		add_action( 'wp_ajax_ln_cleanup_medewerkers', array( $this, 'ajax_cleanup_medewerkers' ) );
	}

	/**
	 * Add tools page under Tools menu.
	 */
	public function register_tools_page() {
		$this->page_hook = add_management_page(
			__( 'Literair Nederland Tools', 'x-literair-nederland-blocks' ),
			__( 'Literair Nederland Tools', 'x-literair-nederland-blocks' ),
			'manage_options',
			'literair-nederland-tools',
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
				var cleanupOutput = document.getElementById("ln-medewerker-cleanup-output");

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

				var convertButton = document.getElementById("ln-convert-recensenten-btn");
				if (convertButton && output) {
					convertButton.addEventListener("click", function () {
						runAction(
							convertButton,
							output,
							{
								action: "ln_convert_recensenten",
								nonce: window.lnTools.nonce
							},
							"Running reviewer conversion..."
						);
					});
				}

				var medewerkerPreviewButton = document.getElementById("ln-medewerker-cleanup-preview-btn");
				if (medewerkerPreviewButton && cleanupOutput) {
					medewerkerPreviewButton.addEventListener("click", function () {
						var dryRunPage = document.getElementById("ln-medewerker-cleanup-dryrun-page");
						var dryRunPerPage = document.getElementById("ln-medewerker-cleanup-dryrun-per-page");
						var pageValue = dryRunPage && dryRunPage.value ? dryRunPage.value : "1";
						var perPageValue = dryRunPerPage && dryRunPerPage.value ? dryRunPerPage.value : "50";

						runAction(
							medewerkerPreviewButton,
							cleanupOutput,
							{
								action: "ln_cleanup_medewerkers",
								run_mode: "dry-run",
								dry_run_page: pageValue,
								dry_run_per_page: perPageValue,
								nonce: window.lnTools.nonce
							},
							"Running cleanup preview..."
						);
					});
				}

				var medewerkerRunButton = document.getElementById("ln-medewerker-cleanup-run-btn");
				if (medewerkerRunButton && cleanupOutput) {
					medewerkerRunButton.addEventListener("click", function () {
						runAction(
							medewerkerRunButton,
							cleanupOutput,
							{
								action: "ln_cleanup_medewerkers",
								run_mode: "execute",
								nonce: window.lnTools.nonce
							},
							"Running medewerker cleanup batch (max 10)..."
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
			<h1><?php echo esc_html__( 'Literair Nederland Tools', 'x-literair-nederland-blocks' ); ?></h1>
			<p>
				<?php echo esc_html__( 'This page provides tools to assist with migrating and cleaning up content for the Literair Nederland block-based theme. Please review each tool\'s description and use with extreme caution, especially the cleanup tool which can delete content. Always ensure you have a backup before running any migration or cleanup operations.', 'x-literair-nederland-blocks' ); ?>
			</p>
			<h2>
				<?php echo esc_html__('Convert Recensenten', 'x-literair-nederland-blocks' ); ?>

			</h2>
			<p>
				<?php echo esc_html__( 'Convert up to 10 recensent posts at a time by moving the first inline image to the featured image. This allows you to migrate reviewers to be compatible with the block theme templates. Run multiple times until no more reviewers are found to convert.', 'x-literair-nederland-blocks' ); ?>
			</p>
			<p>
				<button id="ln-convert-recensenten-btn" class="button button-primary" type="button">
					<?php echo esc_html__( 'Convert reviewers', 'x-literair-nederland-blocks' ); ?>
				</button>
			</p>
			<div id="ln-tools-output" style="min-height: 2em;"></div>
			<hr />
			<h2><?php echo esc_html__( 'Medewerker Duplicate Cleanup', 'x-literair-nederland-blocks' ); ?></h2>
			<p><?php echo esc_html__( 'Preview checks all duplicate medewerker posts. Execute processes up to 10 duplicates per click and applies changes.', 'x-literair-nederland-blocks' ); ?></p>
			<p>
				<label for="ln-medewerker-cleanup-dryrun-page" style="margin-right: 8px;">
					<?php echo esc_html__( 'Dry-run page', 'x-literair-nederland-blocks' ); ?>
				</label>
				<input id="ln-medewerker-cleanup-dryrun-page" type="number" min="1" value="1" style="width: 90px; margin-right: 16px;" />
				<label for="ln-medewerker-cleanup-dryrun-per-page" style="margin-right: 8px;">
					<?php echo esc_html__( 'Dry-run items per page', 'x-literair-nederland-blocks' ); ?>
				</label>
				<input id="ln-medewerker-cleanup-dryrun-per-page" type="number" min="1" max="200" value="50" style="width: 90px;" />
			</p>
			<p>
				<button id="ln-medewerker-cleanup-preview-btn" class="button" type="button">
					<?php echo esc_html__( 'Preview Cleanup (no changes)', 'x-literair-nederland-blocks' ); ?>
				</button>
				<button id="ln-medewerker-cleanup-run-btn" class="button button-primary" type="button">
					<?php echo esc_html__( 'Run Cleanup (max 10 duplicates per click)', 'x-literair-nederland-blocks' ); ?>
				</button>
			</p>
			<div id="ln-medewerker-cleanup-output" style="min-height: 2em;"></div>
		</div>
		<?php
	}

	/**
	 * AJAX handler for medewerker duplicate cleanup tool.
	 */
	public function ajax_cleanup_medewerkers() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'x-literair-nederland-blocks' ),
				),
				403
			);
		}

		check_ajax_referer( 'ln_tools_nonce', 'nonce' );

		$run_mode    = isset( $_POST['run_mode'] ) ? sanitize_key( wp_unslash( $_POST['run_mode'] ) ) : 'dry-run';
		$is_execute  = 'execute' === $run_mode;
		$batch_limit = 10;
		$dry_run_page = isset( $_POST['dry_run_page'] ) ? max( 1, absint( wp_unslash( $_POST['dry_run_page'] ) ) ) : 1;
		$dry_run_per_page = isset( $_POST['dry_run_per_page'] ) ? absint( wp_unslash( $_POST['dry_run_per_page'] ) ) : 50;
		$dry_run_per_page = max( 1, min( 200, $dry_run_per_page ) );

		$candidates      = $this->get_medewerker_duplicate_candidates();
		$total_candidates = count( $candidates );

		if ( 0 === $total_candidates ) {
			wp_send_json_success(
				array(
					'message' => __( 'No duplicate medewerker posts found.', 'x-literair-nederland-blocks' ),
				)
			);
		}

		$to_process = $candidates;
		$offset = 0;
		$total_pages = 1;
		if ( $is_execute ) {
			$to_process = array_slice( $candidates, 0, $batch_limit );
		} else {
			$total_pages = max( 1, (int) ceil( $total_candidates / $dry_run_per_page ) );
			$dry_run_page = min( $dry_run_page, $total_pages );
			$offset = ( $dry_run_page - 1 ) * $dry_run_per_page;
			$to_process = array_slice( $candidates, $offset, $dry_run_per_page );
		}

		$stats = array(
			'scanned_count'   => 0,
			'processed_count' => count( $to_process ),
			'updated_refs'    => 0,
			'deleted_count'   => 0,
			'blocked_count'   => 0,
			'warning_count'   => 0,
		);

		$log_lines = array();
		$title_groups = array();

		foreach ( $to_process as $candidate ) {
			$original_id  = (int) $candidate['original_id'];
			$duplicate_id = (int) $candidate['duplicate_id'];
			$title        = (string) $candidate['title'];
			$group_key    = $title;

			if ( ! isset( $title_groups[ $group_key ] ) ) {
				$title_groups[ $group_key ] = array(
					'title'             => $title,
					'original_id'       => $original_id,
					'duplicate_ids'     => array(),
					'processed_count'   => 0,
					'eligible_count'    => 0,
					'deleted_count'     => 0,
					'blocked_count'     => 0,
					'repointed_refs'    => 0,
					'warning_count'     => 0,
				);
			}

			$title_groups[ $group_key ]['duplicate_ids'][] = $duplicate_id;
			$title_groups[ $group_key ]['processed_count']++;

			$diagnostic = $this->analyze_medewerker_duplicate( $original_id, $duplicate_id, $title );
			$stats['scanned_count']++;

			$updated_refs    = 0;
			$repoint_details = array();
			if ( $is_execute && ! empty( $diagnostic['medewerker_references'] ) ) {
				$repoint_result  = $this->repoint_medewerker_references( $diagnostic['medewerker_references'], $original_id );
				$updated_refs    = isset( $repoint_result['updated'] ) ? (int) $repoint_result['updated'] : 0;
				$repoint_details = isset( $repoint_result['details'] ) && is_array( $repoint_result['details'] ) ? $repoint_result['details'] : array();
			}

			if ( ! $is_execute ) {
				$updated_refs = count( $diagnostic['medewerker_references'] );
			}

			$stats['updated_refs'] += $updated_refs;
			$title_groups[ $group_key ]['repointed_refs'] += $updated_refs;

			if ( ! empty( $diagnostic['blocking_references'] ) ) {
				$stats['warning_count'] += count( $diagnostic['blocking_references'] );
				$title_groups[ $group_key ]['warning_count'] += count( $diagnostic['blocking_references'] );
			}

			$base_line = sprintf(
				'[%s] duplicate #%d (%s) -> original #%d',
				$is_execute ? 'execute' : 'dry-run',
				$duplicate_id,
				esc_html( $title ),
				$original_id
			);

			if ( $is_execute && ! empty( $repoint_details ) ) {
				foreach ( $repoint_details as $repoint_detail ) {
					$log_lines[] = sprintf(
						'repointed medewerker_id: post_id=%1$d, post_title=%2$s, postmeta_id=%3$d, from_duplicate=%4$d, to_original=%5$d',
						isset( $repoint_detail['post_id'] ) ? (int) $repoint_detail['post_id'] : 0,
						esc_html( isset( $repoint_detail['post_title'] ) ? (string) $repoint_detail['post_title'] : '' ),
						isset( $repoint_detail['meta_id'] ) ? (int) $repoint_detail['meta_id'] : 0,
						$duplicate_id,
						$original_id
					);
				}
			}

			if ( ! $diagnostic['is_empty'] ) {
				$stats['blocked_count']++;
				$title_groups[ $group_key ]['blocked_count']++;
				$log_lines[] = $base_line . ': skipped, content or excerpt is not empty.';
				continue;
			}

			if ( $diagnostic['has_media'] ) {
				$stats['blocked_count']++;
				$title_groups[ $group_key ]['blocked_count']++;
				$log_lines[] = $base_line . ': skipped, media linkage detected (' . implode( '; ', array_map( 'esc_html', $diagnostic['media_reasons'] ) ) . ').';
				continue;
			}

			if ( ! empty( $diagnostic['blocking_references'] ) ) {
				$stats['blocked_count']++;
				$title_groups[ $group_key ]['blocked_count']++;
				$log_lines[] = $base_line . ': skipped due to non-medewerker_id references.';

				foreach ( $diagnostic['blocking_references'] as $blocking_ref ) {
					$log_lines[] = sprintf(
						'warning: duplicate_id=%1$d, blocking_postmeta_id=%2$d, blocking_meta_key=%3$s',
						$duplicate_id,
						(int) $blocking_ref['meta_id'],
						esc_html( (string) $blocking_ref['meta_key'] )
					);
				}
				continue;
			}

			if ( $is_execute ) {
				$title_groups[ $group_key ]['eligible_count']++;
				$deleted = wp_delete_post( $duplicate_id, true );

				if ( $deleted ) {
					$stats['deleted_count']++;
					$title_groups[ $group_key ]['deleted_count']++;
					$log_lines[] = $base_line . ': deleted successfully.';
				} else {
					$stats['blocked_count']++;
					$title_groups[ $group_key ]['blocked_count']++;
					$log_lines[] = $base_line . ': failed to delete duplicate post.';
				}
			} else {
				$title_groups[ $group_key ]['eligible_count']++;
				$log_lines[] = $base_line . ': would delete (eligible).';
			}
		}

		$remaining_count = $is_execute ? max( 0, $total_candidates - count( $to_process ) ) : max( 0, $total_candidates - ( $offset + count( $to_process ) ) );

		$group_lines = array();
		foreach ( $title_groups as $title_group ) {
			$group_lines[] = sprintf(
				'title=%1$s, original=%2$d, processed=%3$d, duplicates=[%4$s], repointed_refs=%5$d, eligible=%6$d, deleted=%7$d, blocked=%8$d, warnings=%9$d',
				esc_html( (string) $title_group['title'] ),
				(int) $title_group['original_id'],
				(int) $title_group['processed_count'],
				implode( ',', array_map( 'intval', (array) $title_group['duplicate_ids'] ) ),
				(int) $title_group['repointed_refs'],
				(int) $title_group['eligible_count'],
				(int) $title_group['deleted_count'],
				(int) $title_group['blocked_count'],
				(int) $title_group['warning_count']
			);
		}

		$message_parts = array();
		$message_parts[] = sprintf(
			'mode=%1$s, scanned=%2$d, processed=%3$d, medewerker_refs_updated=%4$d, deleted=%5$d, blocked=%6$d, warnings=%7$d, remaining=%8$d',
			esc_html( $is_execute ? 'execute' : 'dry-run' ),
			(int) $stats['scanned_count'],
			(int) $stats['processed_count'],
			(int) $stats['updated_refs'],
			(int) $stats['deleted_count'],
			(int) $stats['blocked_count'],
			(int) $stats['warning_count'],
			(int) $remaining_count
		);
		if ( ! $is_execute ) {
			$message_parts[] = sprintf(
				'dry_run_page=%1$d/%2$d, dry_run_per_page=%3$d',
				(int) $dry_run_page,
				(int) $total_pages,
				(int) $dry_run_per_page
			);
		}
		$message_parts[] = 'Grouped summary per title:';
		$message_parts[] = implode( '<br />', $group_lines );
		$message_parts[] = '';
		$message_parts[] = implode( '<br />', $log_lines );

		wp_send_json_success(
			array(
				'message'          => implode( '<br />', $message_parts ),
				'run_mode'         => $is_execute ? 'execute' : 'dry-run',
				'processed_count'  => (int) $stats['processed_count'],
				'updated_refs'     => (int) $stats['updated_refs'],
				'deleted_count'    => (int) $stats['deleted_count'],
				'blocked_count'    => (int) $stats['blocked_count'],
				'warning_count'    => (int) $stats['warning_count'],
				'remaining_count'  => (int) $remaining_count,
				'dry_run_page'     => (int) $dry_run_page,
				'dry_run_per_page' => (int) $dry_run_per_page,
				'dry_run_pages'    => (int) $total_pages,
			)
		);
	}

	/**
	 * Build medewerker duplicate list, keeping the lowest ID per title as the original.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private function get_medewerker_duplicate_candidates() {
		global $wpdb;

		$rows = $wpdb->get_results(
			"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'medewerker' AND post_status <> 'trash' ORDER BY ID ASC",
			ARRAY_A
		);

		if ( empty( $rows ) ) {
			return array();
		}

		$candidates = array();
		$seen_titles = array();

		foreach ( $rows as $row ) {
			$title            = (string) $row['post_title'];
			$id               = (int) $row['ID'];
			$comparison_title = $this->normalize_medewerker_title_for_comparison( $title );

			if ( ! isset( $seen_titles[ $comparison_title ] ) ) {
				$seen_titles[ $comparison_title ] = array(
					'original_id' => $id,
					'title'       => $comparison_title,
				);
				continue;
			}

			$candidates[] = array(
				'original_id'  => (int) $seen_titles[ $comparison_title ]['original_id'],
				'duplicate_id' => $id,
				'title'        => (string) $seen_titles[ $comparison_title ]['title'],
			);
		}

		return $candidates;
	}

	/**
	 * Normalize medewerker title before duplicate comparison.
	 *
	 * Treats a leading "door " prefix as non-significant,
	 * so "door Name" and "Name" are compared as the same title.
	 *
	 * @param string $title Medewerker post title.
	 * @return string
	 */
	private function normalize_medewerker_title_for_comparison( $title ) {
		$normalized = trim( (string) $title );
		$normalized = preg_replace( '/^door\s+/iu', '', $normalized );
		$normalized = trim( (string) $normalized );

		return $normalized;
	}

	/**
	 * Analyze whether a medewerker duplicate can be safely removed.
	 *
	 * @param int    $original_id  Original medewerker ID to keep.
	 * @param int    $duplicate_id Duplicate medewerker ID candidate.
	 * @param string $title        Duplicate post title.
	 * @return array<string, mixed>
	 */
	private function analyze_medewerker_duplicate( $original_id, $duplicate_id, $title ) {
		global $wpdb;

		$post = get_post( $duplicate_id );

		$is_empty = false;
		if ( $post instanceof WP_Post ) {
			$content  = trim( (string) $post->post_content );
			$excerpt  = trim( (string) $post->post_excerpt );
			$is_empty = '' === $content && '' === $excerpt;
		}

		$media_analysis = $this->detect_medewerker_media_links( $duplicate_id );

		$reference_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_id, post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_value = %s",
				(string) $duplicate_id
			),
			ARRAY_A
		);

		$medewerker_references = array();
		$blocking_references   = array();

		foreach ( (array) $reference_rows as $reference_row ) {
			$meta_key = isset( $reference_row['meta_key'] ) ? (string) $reference_row['meta_key'] : '';

			if ( 'medewerker_id' === $meta_key ) {
				$medewerker_references[] = array(
					'meta_id' => (int) $reference_row['meta_id'],
					'post_id' => (int) $reference_row['post_id'],
					'meta_key' => $meta_key,
				);
				continue;
			}

			$blocking_references[] = array(
				'meta_id' => (int) $reference_row['meta_id'],
				'post_id' => (int) $reference_row['post_id'],
				'meta_key' => $meta_key,
			);
		}

		return array(
			'original_id'            => (int) $original_id,
			'duplicate_id'           => (int) $duplicate_id,
			'title'                  => (string) $title,
			'is_empty'               => $is_empty,
			'has_media'              => (bool) $media_analysis['has_media'],
			'media_reasons'          => (array) $media_analysis['reasons'],
			'medewerker_references'  => $medewerker_references,
			'blocking_references'    => $blocking_references,
		);
	}

	/**
	 * Update medewerker_id references to point to the original medewerker.
	 *
	 * @param array<int, array<string, mixed>> $references medewerker_id references.
	 * @param int                               $original_id Original medewerker ID.
	 * @return array{updated: int, details: array<int, array<string, mixed>>}
	 */
	private function repoint_medewerker_references( array $references, $original_id ) {
		$updated = 0;
		$details = array();

		foreach ( $references as $reference ) {
			$meta_id = isset( $reference['meta_id'] ) ? (int) $reference['meta_id'] : 0;
			$post_id = isset( $reference['post_id'] ) ? (int) $reference['post_id'] : 0;

			if ( $meta_id <= 0 ) {
				continue;
			}

			$result = update_metadata_by_mid( 'post', $meta_id, (string) $original_id );
			if ( is_wp_error( $result ) || false === $result ) {
				continue;
			}

			$updated++;
			$details[] = array(
				'meta_id'     => $meta_id,
				'post_id'     => $post_id,
				'post_title'  => $post_id > 0 ? (string) get_the_title( $post_id ) : '',
			);

			if ( $post_id > 0 ) {
				clean_post_cache( $post_id );
			}
		}

		return array(
			'updated' => $updated,
			'details' => $details,
		);
	}

	/**
	 * Detect media/file relationships on a duplicate medewerker post.
	 *
	 * @param int $duplicate_id Duplicate medewerker ID.
	 * @return array{has_media: bool, reasons: string[]}
	 */
	private function detect_medewerker_media_links( $duplicate_id ) {
		global $wpdb;

		$reasons      = array();
		$duplicate_id = (int) $duplicate_id;

		$attachment_ids = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_parent'    => $duplicate_id,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $attachment_ids ) ) {
			$reasons[] = 'child attachment found';
		}

		$thumbnail_id = (int) get_post_thumbnail_id( $duplicate_id );
		if ( $thumbnail_id > 0 ) {
			$reasons[] = 'featured image linked';
		}

		$meta_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d",
				$duplicate_id
			),
			ARRAY_A
		);

		foreach ( (array) $meta_rows as $meta_row ) {
			$meta_key   = isset( $meta_row['meta_key'] ) ? (string) $meta_row['meta_key'] : '';
			$meta_value = isset( $meta_row['meta_value'] ) ? (string) $meta_row['meta_value'] : '';

			if ( '' === $meta_value ) {
				continue;
			}

			if ( preg_match( '/(image|img|foto|photo|thumbnail|thumb|avatar|banner|header|cover|logo|file)/i', $meta_key ) ) {
				if ( is_numeric( $meta_value ) ) {
					$candidate_id = (int) $meta_value;
					if ( $candidate_id > 0 && 'attachment' === get_post_type( $candidate_id ) ) {
						$reasons[] = 'media-related meta key points to attachment ID';
						break;
					}
				}

				if ( false !== strpos( $meta_value, '/uploads/' ) || preg_match( '/\.(jpg|jpeg|png|gif|webp|svg|pdf)(\?|$)/i', $meta_value ) ) {
					$reasons[] = 'media-related meta key contains file path/value';
					break;
				}
			}
		}

		return array(
			'has_media' => ! empty( $reasons ),
			'reasons'   => array_values( array_unique( $reasons ) ),
		);
	}

	/**
	 * AJAX handler for convert recensenten tool.
	 */
	public function ajax_convert_recensenten() {
        $converted = '';
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Insufficient permissions.', 'x-literair-nederland-blocks' ),
				),
				403
			);
		}

		check_ajax_referer( 'ln_tools_nonce', 'nonce' );

		$post_ids = $this->get_recensenten_to_convert();

		if ( empty( $post_ids ) ) {
			wp_send_json_success(
				array(
					'message' => __( 'No reviewers found to migrate.', 'x-literair-nederland-blocks' ),
				)
			);
		}

		$migrated      = 0;
		$failed_ids    = array();
		$failed_errors = array();

		foreach ( $post_ids as $post_id ) {
			update_post_meta( $post_id, 'migration_status', 'classic' );

			$result = $this->migrate_recensent( (int) $post_id );


			if ( is_wp_error( $result ) ) {
				update_post_meta( $post_id, 'migration_status', 'failed' );
				$failed_ids[]                   = (int) $post_id;
				$failed_errors[ (int) $post_id ] = $result->get_error_message();
				$converted .= sprintf( 'Fail on ID %d, name: %s: %s<br />\n', $post_id, get_the_title( $post_id ), $result->get_error_message() );
				continue;
			}

			update_post_meta( $post_id, 'migration_status', 'blocktheme' );
            $converted .= sprintf( 'Success on ID %d, name: %s<br />\n', $post_id, get_the_title( $post_id ) );

            $migrated++;
		}

        $message = $converted;
		$message .= sprintf(
			/* translators: 1: migrated count, 2: attempted count */
			__( 'Migrated %1$d of %2$d reviewers.', 'x-literair-nederland-blocks' ),
			$migrated,
			count( $post_ids )
		);

		if ( ! empty( $failed_ids ) ) {
			$message .= ' ' . sprintf(
				/* translators: %s: comma-separated post IDs */
				__( 'Failed IDs: %s.', 'x-literair-nederland-blocks' ),
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
	 * Fetch up to 10 recensent posts that still need migration.
	 *
	 * @return int[]
	 */
	private function get_recensenten_to_convert() {
		$query = new WP_Query(
			array(
				'post_type'      => 'recensent',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_query'     => array(
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

		return array_map( 'intval', $query->posts );
	}

	/**
	 * Migrate a recensent post by moving the first inline image to featured image.
	 *
	 * @param int $post_id Post ID.
	 * @return true|WP_Error
	 */
	private function migrate_recensent( $post_id ) {
		$post = get_post( $post_id );

		if ( ! $post || 'recensent' !== $post->post_type ) {
			return new WP_Error( 'invalid_post', __( 'Invalid reviewer post.', 'x-literair-nederland-blocks' ) );
		}

		$content = (string) $post->post_content;

		if ( ! preg_match( '/<img\\b[^>]*>/i', $content, $img_match ) ) {
			return true;
		}

		$img_tag = $img_match[0];

		if ( ! preg_match( '/\\bsrc\\s*=\\s*(["\'])(.*?)\\1/i', $img_tag, $src_match ) ) {
			return new WP_Error( 'missing_img_src', __( 'Image tag has no src attribute.', 'x-literair-nederland-blocks' ) );
		}


		$image_url = $src_match[2];

		$resolve_attachment_id = static function( $url ) use ( $post_id ) {
			$candidates = array( $url );

			// Some optimizers wrap originals like "image.jpg.webp"; unwrap to the original extension.
			$unwrapped_webp = preg_replace( '/\.(jpe?g|png|gif)\.webp(?=($|[?#]))/i', '.$1', $url );
			if ( is_string( $unwrapped_webp ) && $unwrapped_webp !== $url ) {
				$candidates[] = $unwrapped_webp;
			}

			foreach ( $candidates as $candidate ) {
				// Remove all resize suffixes such as -603x1024 and -219x372.
				$cleaned = preg_replace( '/(?:-\d+x\d+)+(?=\.(jpg|jpeg|png|gif|webp))/i', '', $candidate );
				if ( is_string( $cleaned ) && $cleaned !== $candidate ) {
					$candidates[] = $cleaned;
				}
			}

			// Final pass: unwrap optimizer-style WebP wrappers like image.jpg.webp and image.jpg-219x372.webp.
			$expanded_candidates = $candidates;
			foreach ( $candidates as $candidate ) {
				$webp_unwrapped = preg_replace( '/\.(jpe?g|png|gif)(?:-\d+x\d+)?\.webp(?=($|[?#]))/i', '.$1', $candidate );
				if ( is_string( $webp_unwrapped ) && $webp_unwrapped !== $candidate ) {
					$expanded_candidates[] = $webp_unwrapped;
				}
			}

			$candidates = array_values( array_unique( array_filter( $expanded_candidates, 'is_string' ) ) );

			foreach ( $candidates as $candidate ) {
				$attachment_id = attachment_url_to_postid( $candidate );
				if ( $attachment_id ) {
					return (int) $attachment_id;
				}
			}

			// Final fallback: use the most recently uploaded attachment attached to this recensent post.
			$attached_ids = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_parent'    => (int) $post_id,
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'orderby'        => 'ID',
					'order'          => 'DESC',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $attached_ids ) ) {
				return (int) $attached_ids[0];
			}

			return 0;
		};

		$attachment_id = $resolve_attachment_id( $image_url );

		// If still not found, try resolving relative URLs to absolute and repeat normalization.
		if ( ! $attachment_id && 0 !== strpos( $image_url, 'http' ) ) {
			$uploads = wp_get_upload_dir();
			if ( ! empty( $uploads['baseurl'] ) ) {
				$absolute_url = trailingslashit( $uploads['baseurl'] ) . ltrim( $image_url, '/' );
				$attachment_id = $resolve_attachment_id( $absolute_url );
			}
		}

		if ( ! $attachment_id ) {
			return new WP_Error( 'attachment_not_found', __( 'Could not resolve attachment ID for image.', 'x-literair-nederland-blocks' ) );
		}

		set_post_thumbnail( $post_id, (int) $attachment_id );

		$new_content = preg_replace( '/<img\\b[^>]*>/i', '', $content, 1 );

		if ( null === $new_content ) {
			return new WP_Error( 'content_update_failed', __( 'Failed to process post content.', 'x-literair-nederland-blocks' ) );
		}

		$update_result = wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => $new_content,
			),
			true
		);

		if ( is_wp_error( $update_result ) ) {
			return $update_result;
		}

		return true;
	}
}

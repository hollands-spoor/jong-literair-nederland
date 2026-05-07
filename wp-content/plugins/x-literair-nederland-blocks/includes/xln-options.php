<?php
/**
 * Options page for X LN Blocks plugin.
 *
 * All plugin options are stored in a single serialized associative array
 * under the WP option key 'xln_options'. The schema that defines every
 * option (type, label, default, constraints) lives in xln_get_options_schema()
 * and is exposed through the 'xln_options_schema' filter so other plugins or
 * the active theme can register additional options without touching this file.
 *
 * Schema structure
 * ────────────────
 * tab_id => [
 *   'label'    => string,          // Tab navigation label
 *   'sections' => [
 *     section_id => [
 *       'label'       => string,   // Section heading (WP settings section)
 *       'description' => string,   // (optional) paragraph below heading
 *       'fields'      => [
 *         field_key => [
 *           'label'     => string,
 *           'type'      => 'checkbox' | 'select' | 'radio' | 'text' | 'number',
 *           'default'   => mixed,
 *           // optional conditional visibility:
 *           'dependency' => [
 *             'option' => string, // field key this field depends on
 *             'value'  => mixed,  // required value for visibility
 *           ],
 *           // select only:
 *           'options'   => [ value => label, ... ],
 *           // number only (all optional):
 *           'min'       => int|float,
 *           'max'       => int|float,
 *           'step'      => int|float,
 *           // text only (optional):
 *           'maxlength' => int,
 *         ],
 *       ],
 *     ],
 *   ],
 * ]
 *
 * @package XLn
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -----------------------------------------------------------------------
// Schema
// -----------------------------------------------------------------------

/**
 * Returns the full options schema, filtered so external code can extend it.
 *
 * @return array<string, array>
 */
function xln_get_options_schema(): array {
	$schema = [
		'general'     => [
			'label'    => __( 'General', 'x-literair-nederland-blocks' ),
			'sections' => [
				'general_dashboard' => [
					'label'  => __( 'Dashboard', 'x-literair-nederland-blocks' ),
					'fields' => [
						'show_quick_start_widget' => [
							'label'   => __( 'Show quick start widget', 'x-literair-nederland-blocks' ),
							'type'    => 'checkbox',
							'default' => true,
						],
					],
				],
				'general_cron'      => [
					'label'  => __( 'Cron', 'x-literair-nederland-blocks' ),
					'description' => __('Geautomatiseerde taken', 'x-literair-nederland-blocks' ),
					'fields' => [
						'breaking_news_expires' => [
							'label'   => __( 'Breaking News Expires', 'x-literair-nederland-blocks' ),
							'description' => __( 'Number of days after which a breaking news item expires and moves to rij 1', 'x-literair-nederland-blocks' ),	
							'type'    => 'number',
							'default' => 1,
							'min'     => 0,
							'step'    => 1,
						],
					],
				],
			],
		],
		'ln_donation' => [
			'label'    => __( 'Donation', 'x-literair-nederland-blocks' ),
			'sections' => [
				'donation_general' => [
					'label'  => __( 'Donation settings', 'x-literair-nederland-blocks' ),
					'fields' => [
						'enable_donations' => [
							'label'   => __( 'Enable Donations', 'x-literair-nederland-blocks' ),
							'type'    => 'checkbox',
							'default' => false,
						],
					],
				],
			],
		],
		'developers'  => [
			'label'    => __( 'Developers', 'x-literair-nederland-blocks' ),
			'sections' => [
				'developer_tools' => [
					'label'  => __( 'Tools', 'x-literair-nederland-blocks' ),
					'fields' => [
						'enable_tools_page' => [
							'label'   => __( 'Enable tools page', 'x-literair-nederland-blocks' ),
							'type'    => 'checkbox',
							'default' => true,
						],
					],
				],
				'ads'             => [
					'label'  => __( 'Ads', 'x-literair-nederland-blocks' ),
					'fields' => [
						'show_ads_in_admin' => [
							'label'   => __( 'Show Ads in Admin', 'x-literair-nederland-blocks' ),
							'type'    => 'radio',
							'default' => 'default',
							'options' => [
								'hide-all' => __( 'Hide all', 'x-literair-nederland-blocks' ),
								'default'  => __( 'Default', 'x-literair-nederland-blocks' ),
								'show-all' => __( 'Show all', 'x-literair-nederland-blocks' ),
							],
						],
					],
				],
			],
		],
	];

	/**
	 * Filters the options schema.
	 *
	 * Use this filter to register additional tabs, sections, or fields.
	 * Follow the schema structure documented at the top of this file.
	 *
	 * @param array $schema Full options schema keyed by tab ID.
	 */
	return apply_filters( 'xln_options_schema', $schema );
}

// -----------------------------------------------------------------------
// Public helper – retrieve options merged with defaults
// -----------------------------------------------------------------------

/**
 * Returns saved options with schema defaults as fallback for missing keys.
 *
 * @return array<string, mixed>
 */
function xln_get_options(): array {
	$schema   = xln_get_options_schema();
	$defaults = [];

	foreach ( $schema as $tab ) {
		foreach ( $tab['sections'] as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				$defaults[ $key ] = $field['default'] ?? null;
			}
		}
	}

	$saved = get_option( 'xln_options', [] );
	if ( ! is_array( $saved ) ) {
		$saved = [];
	}

	return array_merge( $defaults, $saved );
}

/**
 * Returns whether the developer tools page is enabled.
 *
 * @return bool
 */
function xln_is_tools_page_enabled(): bool {
	$options = get_option( 'xln_options', [] );

	if ( ! is_array( $options ) ) {
		return true;
	}

	if ( ! array_key_exists( 'enable_tools_page', $options ) ) {
		return true;
	}

	return ! empty( $options['enable_tools_page'] );
}

// -----------------------------------------------------------------------
// Admin menu
// -----------------------------------------------------------------------

add_action( 'admin_menu', 'xln_add_options_page' );

function xln_add_options_page(): void {
	add_options_page(
		__( 'Literair Nederland Settings', 'x-literair-nederland-blocks' ),
		__( 'Literair Nederland', 'x-literair-nederland-blocks' ),
		'manage_options',
		'xln-settings',
		'xln_render_options_page'
	);
}

// -----------------------------------------------------------------------
// Register settings, sections and fields (WP Settings API)
// -----------------------------------------------------------------------

add_action( 'admin_init', 'xln_register_settings' );

function xln_register_settings(): void {
	register_setting(
		'xln_options_group',
		'xln_options',
		[
			'sanitize_callback' => 'xln_sanitize_options',
			'default'           => [],
		]
	);

	$schema = xln_get_options_schema();

	foreach ( $schema as $tab_id => $tab ) {
		// Each tab gets its own Settings API "page" slug so do_settings_sections()
		// renders only the sections belonging to the active tab.
		$page = 'xln_tab_' . $tab_id;

		foreach ( $tab['sections'] as $section_id => $section ) {
			$description_cb = isset( $section['description'] )
				? static function () use ( $section ): void {
					echo '<p>' . esc_html( $section['description'] ) . '</p>';
				}
				: '__return_false';

			add_settings_section( $section_id, $section['label'], $description_cb, $page );

			foreach ( $section['fields'] as $field_key => $field ) {
				add_settings_field(
					$field_key,
					$field['label'],
					'xln_render_field',
					$page,
					$section_id,
					[
					'field_key' => $field_key,
					'field'     => $field,
						'label_for' => 'xln_options_' . $field_key,
					]
				);
			}
		}
	}
}

// -----------------------------------------------------------------------
// Sanitization
// -----------------------------------------------------------------------

/**
 * Sanitizes and validates submitted option values.
 *
 * Because all tabs share a single option key, this callback receives only
 * the fields that were rendered on the active tab. A hidden '_active_tab'
 * input identifies which tab was submitted; fields from other tabs are
 * preserved from the currently saved option value.
 *
 * @param  mixed $input Raw POST data.
 * @return array<string, mixed> Sanitized options.
 */
function xln_sanitize_options( $input ): array {
	$schema = xln_get_options_schema();

	if ( ! is_array( $input ) ) {
		$input = [];
	}

	// Determine which tab was submitted so we handle unchecked checkboxes
	// correctly (browser omits unchecked checkboxes from POST).
	$tab_ids    = array_keys( $schema );
	$active_tab = ( isset( $input['_active_tab'] ) && array_key_exists( $input['_active_tab'], $schema ) )
		? $input['_active_tab']
		: $tab_ids[0];

	// Collect field keys that belong to the active tab.
	$active_keys = [];
	foreach ( $schema[ $active_tab ]['sections'] as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$active_keys[] = $key;
		}
	}

	// Preserve previously saved values for tabs not being submitted right now.
	$existing = get_option( 'xln_options', [] );
	if ( ! is_array( $existing ) ) {
		$existing = [];
	}

	$clean = [];

	foreach ( $schema as $tab ) {
		foreach ( $tab['sections'] as $section ) {
			foreach ( $section['fields'] as $key => $field ) {
				// Field from a non-active tab: keep the saved value unchanged.
				if ( ! in_array( $key, $active_keys, true ) ) {
					$clean[ $key ] = array_key_exists( $key, $existing )
						? $existing[ $key ]
						: ( $field['default'] ?? null );
					continue;
				}

				$type  = $field['type'] ?? 'text';
				$value = $input[ $key ] ?? null;

				switch ( $type ) {
					case 'checkbox':
						$clean[ $key ] = ! empty( $value );
						break;

					case 'number':
						$clean[ $key ] = (float) $value;
						if ( isset( $field['min'] ) && $clean[ $key ] < (float) $field['min'] ) {
							$clean[ $key ] = (float) $field['min'];
						}
						if ( isset( $field['max'] ) && $clean[ $key ] > (float) $field['max'] ) {
							$clean[ $key ] = (float) $field['max'];
						}
						break;

					case 'select':
						case 'radio':
						$allowed        = array_keys( $field['options'] ?? [] );
						$clean[ $key ] = in_array( $value, $allowed, true )
							? $value
							: ( $field['default'] ?? '' );
						break;

					case 'text':
					default:
						$clean[ $key ] = sanitize_text_field( (string) ( $value ?? '' ) );
						if ( isset( $field['maxlength'] ) ) {
							$clean[ $key ] = mb_substr( $clean[ $key ], 0, (int) $field['maxlength'] );
						}
						break;
				}
			}
		}
	}

	return $clean;
}

// -----------------------------------------------------------------------
// Field renderer
// -----------------------------------------------------------------------

/**
 * Renders a single settings field input.
 *
 * Called by the WP Settings API for each registered field.
 *
 * @param array $args Contains 'field_key' and 'field' (schema entry).
 */
function xln_render_field( array $args ): void {
	$options   = xln_get_options();
	$field_key = $args['field_key'];
	$field     = $args['field'];
	$type      = $field['type'] ?? 'text';
	$value     = $options[ $field_key ] ?? ( $field['default'] ?? '' );
	$id        = 'xln_options_' . esc_attr( $field_key );
	$name      = 'xln_options[' . esc_attr( $field_key ) . ']';
	$dependency = ( isset( $field['dependency'] ) && is_array( $field['dependency'] ) ) ? $field['dependency'] : null;
	$dep_option = ( isset( $dependency['option'] ) && is_string( $dependency['option'] ) ) ? $dependency['option'] : '';
	$dep_value  = $dependency['value'] ?? true;
	$wrap_attrs = '';

	if ( '' !== $dep_option ) {
		$wrap_attrs .= ' data-xln-dependency-option="' . esc_attr( $dep_option ) . '"';
		$wrap_attrs .= ' data-xln-dependency-value="' . esc_attr( wp_json_encode( $dep_value ) ) . '"';
	}

	echo '<div class="xln-field-control"' . $wrap_attrs . '>';

	switch ( $type ) {
		case 'checkbox':
			printf(
				'<input type="checkbox" id="%s" name="%s" value="1" %s />',
				$id,
				$name,
				checked( $value, true, false )
			);
			break;

			case 'radio':
				echo '<fieldset id="' . $id . '">';
				foreach ( ( $field['options'] ?? [] ) as $opt_val => $opt_label ) {
					$radio_id = $id . '_' . sanitize_key( (string) $opt_val );
					printf(
						'<label for="%1$s" style="display:block;margin:0 0 6px 0;"><input type="radio" id="%1$s" name="%2$s" value="%3$s" %4$s /> %5$s</label>',
						esc_attr( $radio_id ),
						$name,
						esc_attr( $opt_val ),
						checked( $value, $opt_val, false ),
						esc_html( $opt_label )
					);
				}
				echo '</fieldset>';
				break;

		case 'select':
			echo '<select id="' . $id . '" name="' . $name . '">';
			foreach ( ( $field['options'] ?? [] ) as $opt_val => $opt_label ) {
				printf(
					'<option value="%s" %s>%s</option>',
					esc_attr( $opt_val ),
					selected( $value, $opt_val, false ),
					esc_html( $opt_label )
				);
			}
			echo '</select>';
			break;

		case 'number':
			$attrs = '';
			if ( isset( $field['min'] ) ) {
				$attrs .= ' min="' . esc_attr( (string) $field['min'] ) . '"';
			}
			if ( isset( $field['max'] ) ) {
				$attrs .= ' max="' . esc_attr( (string) $field['max'] ) . '"';
			}
			if ( isset( $field['step'] ) ) {
				$attrs .= ' step="' . esc_attr( (string) $field['step'] ) . '"';
			}
			printf(
				'<input type="number" id="%s" name="%s" value="%s"%s class="small-text" />',
				$id,
				$name,
				esc_attr( (string) $value ),
				$attrs
			);
			break;

		case 'text':
		default:
			$maxlength = isset( $field['maxlength'] ) ? ' maxlength="' . (int) $field['maxlength'] . '"' : '';
			printf(
				'<input type="text" id="%s" name="%s" value="%s"%s class="regular-text" />',
				$id,
				$name,
				esc_attr( (string) $value ),
				$maxlength
			);
			break;
	}

	if ( isset( $field['description'] ) && '' !== trim( (string) $field['description'] ) ) {
		printf(
			'<p class="description" style="display:inline-block;margin:0 0 0 8px;vertical-align:middle;">%s</p>',
			esc_html( (string) $field['description'] )
		);
	}

	echo '</div>';
}

// -----------------------------------------------------------------------
// Options page renderer
// -----------------------------------------------------------------------

function xln_render_options_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$schema      = xln_get_options_schema();
	$tab_ids     = array_keys( $schema );
	$current_tab = ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $schema ) ) // phpcs:ignore WordPress.Security.NonceVerification
		? sanitize_key( $_GET['tab'] ) // phpcs:ignore WordPress.Security.NonceVerification
		: $tab_ids[0];
	$page_slug   = 'xln_tab_' . $current_tab;
	$base_url    = admin_url( 'options-general.php?page=xln-settings' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<nav class="nav-tab-wrapper">
			<?php foreach ( $schema as $tab_id => $tab ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tab', $tab_id, $base_url ) ); ?>"
				   class="nav-tab <?php echo $tab_id === $current_tab ? 'nav-tab-active' : ''; ?>">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="options.php">
			<?php settings_fields( 'xln_options_group' ); ?>
			<input type="hidden" name="xln_options[_active_tab]" value="<?php echo esc_attr( $current_tab ); ?>" />
			<?php
			do_settings_sections( $page_slug );
			submit_button();
			?>
		</form>
	</div>
	<script>
		(function () {
			const form = document.querySelector('.wrap form[action="options.php"]');
			if (!form) {
				return;
			}

			function parseExpected(raw) {
				if (typeof raw !== 'string' || raw === '') {
					return true;
				}
				try {
					return JSON.parse(raw);
				} catch (e) {
					return raw;
				}
			}

			function getControllerValue(key) {
				const fieldName = 'xln_options[' + key + ']';
				const matchingControllers = Array.from(form.elements).filter(function (el) {
					return el && el.name === fieldName;
				});
				const controller = matchingControllers[0];

				if (!controller) {
					return undefined;
				}

				if (controller.type === 'radio') {
					const checkedController = matchingControllers.find(function (el) {
						return !!el.checked;
					});

					return checkedController ? checkedController.value : '';
				}

				if (controller.type === 'checkbox') {
					return !!controller.checked;
				}

				return controller.value;
			}

			function matchesExpected(actual, expected) {
				if (typeof expected === 'boolean') {
					return (!!actual) === expected;
				}

				if (typeof expected === 'number') {
					return Number(actual) === expected;
				}

				return String(actual) === String(expected);
			}

			function applyDependencies() {
				const dependentFields = form.querySelectorAll('[data-xln-dependency-option]');

				dependentFields.forEach(function (node) {
					const optionKey = node.getAttribute('data-xln-dependency-option');
					const expectedValue = parseExpected(node.getAttribute('data-xln-dependency-value'));
					const actualValue = getControllerValue(optionKey);
					const visible = matchesExpected(actualValue, expectedValue);
					const row = node.closest('tr') || node;

					row.style.display = visible ? '' : 'none';
				});
			}

			form.addEventListener('change', applyDependencies);
			applyDependencies();
		})();
	</script>
	<?php
}

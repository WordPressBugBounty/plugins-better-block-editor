<?php
/**
 * Handle plugin settings page.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core;

use BetterBlockEditor\Base\ConfigurableModuleInterface;
use BetterBlockEditor\Plugin;
use Exception;

defined( 'ABSPATH' ) || exit;

class Settings {

	protected static $allowed_breakpoint_units = array( 'px', 'em', 'rem', 'vw', 'vh' );

	// WP permission to open the Settings Page
	const CAPABILITY = 'manage_options';

	// Settings page slug
	const MENU_PAGE_SLUG = 'better-block-editor';

	const TEMPLATES_FOLDER_NAME = WPBBE_DIR . 'admin/templates/settings/';


	/**
	 * Add the Settings Page in the WP admin menu.
	 *
	 * @return void
	 */
	public static function settings_page() {
		add_options_page(
			__( 'Better Block Editor Settings', 'better-block-editor' ),
			__( 'Better Block Editor', 'better-block-editor' ),
			self::CAPABILITY,
			self::MENU_PAGE_SLUG,
			function ( $args ) {
				self::parse_template( 'page', $args );
			}
		);
	}

	/**
	 * Print out Settings page.
	 *
	 * @return void
	 */
	public static function settings_init() {
		// register section
		add_settings_section(
			WPBBE_PLUGIN_ID . '_settings_section',
			'', // no title for section
			null, // no callback for section content at the top of the page
			self::MENU_PAGE_SLUG
		);

		$modules_data = Plugin::instance()->modules_manager->get_managable_modules_data();
		usort(
			$modules_data,
			function ( $a, $b ) {
				return $a['settings_order'] <=> $b['settings_order'];
			}
		);

		foreach ( $modules_data as $module_data ) {
			self::add_module_enable_checkbox(
				$module_data['identifier'],
				$module_data['title'],
				array(
					'label'       => $module_data['label'],
					'description' => $module_data['description'],
					'enabled'     => $module_data['enabled'],
				)
			);
			// add module settings
			if ( $module_data['enabled'] ) {
				$classname = $module_data['classname'] ?? null;
				if ( $classname && is_a( $classname, ConfigurableModuleInterface::class, true ) ) {
					foreach ( $classname::get_settings() as $key => $field ) {
						self::add_module_settigns( $module_data['identifier'], $key, $field );
					}
				}
			}
		}

		self::add_user_defined_breakpoint_options();
	}

	public static function rest_settings_init() {
		$modules_data = Plugin::instance()->modules_manager->get_managable_modules_data();
		foreach ( $modules_data as $module_data ) {
			self::add_module_enable_checkbox(
				$module_data['identifier'],
				$module_data['title'],
				array(
					'label'       => $module_data['label'],
					'description' => $module_data['description'],
					'enabled'     => $module_data['enabled'],
				),
				true
			);
			// add module settings
			if ( $module_data['enabled'] ) {
				$classname = $module_data['classname'] ?? null;
				if ( $classname && is_a( $classname, ConfigurableModuleInterface::class, true ) ) {
					foreach ( $classname::get_settings() as $key => $field ) {
						self::add_module_settigns( $module_data['identifier'], $key, $field, true );
					}
				}
			}
		}
	}

	/**
	 * Checks if a module is enabled using WP options API.
	 *
	 * @param  string $module_identifier Identifier of module to check.
	 * @param  bool   $default            Default value to return if option does not exist.
	 *
	 * @return bool|null value from option, null if option does not exist
	 */
	public static function is_module_enabled( $module_identifier, $default = true ) {
		return get_option( self::build_module_enabled_option_name( $module_identifier ), $default );
	}

	public static function get_active_user_defined_breakpoints() {
		$user_defined_breakpoints = self::get_user_defined_breakpoints();
		return array_filter(
			$user_defined_breakpoints,
			function ( $item ) {
				return $item['active'];
			}
		);
	}

	/**
	 * Retrieves the user-defined breakpoints from the options if set,
	 * otherwise the default user-defined breakpoints.
	 *
	 * @return array
	 */
	public static function get_user_defined_breakpoints() {
		return (array) get_option(
			self::build_user_defined_breakpoints_option_name(),
			self::get_default_user_defined_breakpoints()
		);
	}

	private static function add_user_defined_breakpoint_options() {
		$option_name = self::build_user_defined_breakpoints_option_name();

		register_setting(
			WPBBE_PLUGIN_ID . '_settings',
			$option_name,
			array(
				'default'           => array(),
				'type'              => 'array',
				'sanitize_callback' => array( self::class, 'sanitize_user_defined_breakpoints' ),
			)
		);

		add_settings_field(
			$option_name,
			__( 'Breakpoints', 'better-block-editor' ),
			function () {
				self::parse_template( 'breakpoints', array() );
			},
			self::MENU_PAGE_SLUG,
			WPBBE_PLUGIN_ID . '_settings_section'
		);

		// add js to the page
		$relative_filename = 'admin/js/settings/breakpoints.js';
		$handle            = WPBBE_PLUGIN_ID . '__core-settings__breakpoints-script';
		wp_register_script(
			$handle,
			WPBBE_URL . $relative_filename,
			array(), // no dependencies for this script
			WPBBE_VERSION, // use plugin version as script version
			array(
				'in_footer' => true, // load script in footer as we need to access the DOM elements
			)
		);

		$translations = array(
			'remove_breakpoint_confirm_message' => esc_js( __( 'Do you want to remove this breakpoint?', 'better-block-editor' ) ),
			'remove_breakpoint_button_title'    => esc_js( __( 'Remove breakpoint', 'better-block-editor' ) ),
		);

		$inline_script = 'const WPBBE_RESPONSIVE_BREAKPOINT_SETTINGS = ' . wp_json_encode(
			array(
				'ALLOWED_SIZE_UNITS' => self::$allowed_breakpoint_units,
				'WP_OPTION_NAME'     => self::build_user_defined_breakpoints_option_name(),
				'I18N_TRANSLATIONS'  => $translations,
			)
		) . ';';

		// initialize the Map to store breakpoints
		$inline_script .= 'WPBBE_RESPONSIVE_BREAKPOINT_SETTINGS.BREAKPOINT_LIST = new Map();' . "\n";
		// we use Map to keep breakpoints order (otherwise it will be sorted by keys)
		foreach ( self::get_active_user_defined_breakpoints() as $key => $breakpoint ) {
			$inline_script .= sprintf(
				'WPBBE_RESPONSIVE_BREAKPOINT_SETTINGS.BREAKPOINT_LIST.set(\'%s\', %s);',
				esc_js( (string) $key ),
				wp_json_encode( $breakpoint )
			) . "\n";
		}

		wp_add_inline_script( $handle, $inline_script, 'before' );

		add_action(
			'admin_enqueue_scripts',
			function ( $hook_suffix ) use ( $handle ) {
				// if not on the settings page, do not enqueue the script
				if ( 'settings_page_' . self::MENU_PAGE_SLUG !== $hook_suffix ) {
					return;
				}
				wp_enqueue_script( $handle );
			}
		);
	}

	/**
	 * Sanitizes the user-defined breakpoints.
	 * In case of invalid input just remove invalid option.
	 *
	 * @param array $options
	 * @return array sanitized options.
	 */
	public static function sanitize_user_defined_breakpoints( $options ) {
		$current_breakpoints = self::get_user_defined_breakpoints();

		// if data is not valid
		// in new breakpoints - just ignore
		// in current breakpoints (from DB) - use old values
		foreach ( $options as $key => $data ) {
			$sanitized           = $data;
			$sanitized['name']   = (string) $data['name'];
			$sanitized['value']  = floatval( $data['value'] );
			$sanitized['active'] = true;

			$options[ $key ] = $sanitized;

			if ( ! in_array( $sanitized['unit'], self::$allowed_breakpoint_units )
			|| empty( $sanitized['name'] ) || strlen( $sanitized['name'] ) > 20
			|| empty( $sanitized['value'] ) || $sanitized['value'] < 0 || $sanitized['value'] > 9999
			) {
				if ( array_key_exists( $key, $current_breakpoints ) ) {
					$options[ $key ] = $current_breakpoints[ $key ];
				} else {
					unset( $options[ $key ] );
				}
				continue;
			}
		}

		// removed breakpoints have to be marked as inactive
		foreach ( array_diff_key( $current_breakpoints, $options ) as $key => $data ) {
			$options[ $key ]           = $data;
			$options[ $key ]['active'] = false;
		}

		return $options;
	}

	private static function add_module_enable_checkbox( $module_identifier, $title, $args = array(), $rest = false ) {
		$name = self::build_module_enabled_option_name( $module_identifier );
		register_setting(
			WPBBE_PLUGIN_ID . '_settings',
			$name,
			array(
				'type'              => 'boolean',
				'sanitize_callback' => function ( $value ) {
					return $value === '1' || $value === true ? '1' : '0';
				},
				'show_in_rest' => array(
					'schema' => array(
						'type'        => 'boolean',
						'description' => sprintf(/* translators: %s: Module name */ __( 'Enable/disable module %s', 'better-block-editor'), $module_identifier ),
					),
				),
			)
		);
		if ( $rest ) {
			return;
		}
		$args = array(
			'identifier'  => $name,
			'title'       => $title,
			'label'       => $args['label'] ?? null,
			'description' => $args['description'] ?? null,
			'value'     => $args['enabled'],
		);

		add_settings_field(
			$name,
			$title,
			function ( $args ) {
				self::parse_template( '_checkbox', $args );
			},
			self::MENU_PAGE_SLUG,
			WPBBE_PLUGIN_ID . '_settings_section',
			$args
		);
	}

	/**
	 * Parse a template file based on the provided template name and arguments.
	 * Prints parsed template content.
	 *
	 * @param string $template_name filename of the template (without extension)
	 * @param array  $args variables to be set in template
	 * @throws Exception in case the template file cannot be found or read.
	 *
	 * @return void
	 */
	private static function parse_template( $template_name, $args ) {
		$template_full_name = self::TEMPLATES_FOLDER_NAME . $template_name . '.php';

		if ( ! is_file( $template_full_name ) || ! is_readable( $template_full_name ) ) {
			throw new Exception( 'Can not read template: ' . esc_html( $template_full_name ) );
		}

		include $template_full_name;
	}

	/**
	 * Returns an array containing the default user defined breakpoints.
	 *
	 * @return array
	 */
	private static function get_default_user_defined_breakpoints() {
		return array(
			'mobile' => array(
				'name'   => esc_js( __( 'Mobile', 'better-block-editor' ) ),
				'value'  => '480',
				'unit'   => 'px',
				'active' => true,
			),
			'tablet' => array(
				'name'   => esc_js( __( 'Tablet', 'better-block-editor' ) ),
				'value'  => '960',
				'unit'   => 'px',
				'active' => true,
			),
		);
	}

	/**
	 * Builds the option name used in options API to store the state of a module.
	 *
	 * @param string $module_identifier
	 * @return string
	 */
	private static function build_module_enabled_option_name( $module_identifier ) {
		return WPBBE_PLUGIN_ID . '__module__' . $module_identifier . '__enabled';
	}

	/**
	 * Builds the base option name used to store module-specific settings.
	 *
	 * @param string $module_identifier Module identifier.
	 * @return string Option name used for module settings.
	 */
	public static function build_module_option_name( $module_identifier ) {
		return WPBBE_PLUGIN_ID . '__module__' . $module_identifier;
	}

	/**
	 * Builds the option name used in options API to store the user defined responsiveness breakpoints.
	 *
	 * @return string The option name.
	 */
	private static function build_user_defined_breakpoints_option_name() {
		return WPBBE_PLUGIN_ID . '__user-defined-responsiveness-breakpoints';
	}

	/**
	 * Builds the option name used in options to store the settings of a module.
	 *
	 * @param string $module_identifier
	 * @return string The option name.
	 */
	public static function build_module_settings_name( $module_identifier ) {
		return WPBBE_PLUGIN_ID . '__module__' . $module_identifier . '__settings';
	}

	/* Adds a setting field for a module setting.
	 *
	 * @param string $module_identifier Identifier of the module.
	 * @param string $option_key        Key of the option within the module settings array.
	 * @param array  $field             Field definition array with keys
	 * @param bool   $rest              Whether to register the setting for REST API only (no settings page field).
	 *
	 * @return void
	 */
	private static function add_module_settigns( $module_identifier, $option_key, $field, $rest = false ) {
		$option_name = self::build_module_settings_name( $module_identifier ); // store all sub options as array

		$type = $field['type'] ?? 'boolean';

		register_setting(
			WPBBE_PLUGIN_ID . '_settings',
			$option_name,
			array(
				'type'              => ( $type === 'multicheckbox' ) ? 'array' : $type,
				'sanitize_callback' => function ( $input ) use ( $field, $option_key, $option_name ) {
					$input  = (array) $input;
					$output = $input;

					switch ( $field['type'] ?? 'text' ) {
						case 'multicheckbox':
							$available             = $field['options'] ?? array();
							$output[ $option_key ] = $output[ $option_key ] ?? array();
							foreach ( $available as $key => $_ ) {
								if ( ! empty( $input[ $option_key ][ $key ] ) ) {
									$output[ $option_key ][ $key ] = 1;
								} else {
									unset( $output[ $option_key ][ $key ] );
								}
							}
							break;
						case 'checkbox':
							$output[ $option_key ] = ! empty( $input[ $option_key ] ) ? 1 : 0;
							break;

						case 'text':
						default:
							$output[ $option_key ] = sanitize_text_field(
								$input[ $option_key ] ?? $output[ $option_key ] ?? ''
							);
							break;
					}

					return $output;
				},
				'default'           => $field['default'] ?? array(),
			)
		);

		if ( $rest ) {
			return;
		}
		add_settings_field(
			$option_name . '__' . $option_key,
			$field['label'],
			function () use ( $module_identifier, $option_key, $field, $option_name ) {
				$stored_value = get_option( $option_name, array() );
				$value        = $stored_value[ $option_key ] ?? $field['default'];

				$template_name = '_' . $field['type'];
				self::parse_template(
					$template_name,
					array_merge(
						$field,
						array(
							'identifier' => $option_name . '[' . $option_key . ']',
							'value'      => $value,
						)
					)
				);
			},
			self::MENU_PAGE_SLUG,
			WPBBE_PLUGIN_ID . '_settings_section'
		);
	}
}

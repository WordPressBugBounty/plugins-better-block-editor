<?php
/**
 * Handler base class.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels\Handlers;

use BetterBlockEditor\Core\BlockUtils;

defined( 'ABSPATH' ) || exit;

/**
 * Base class for all tool panel handlers
 */
abstract class HandlerBase {

	/**
	 * Builds CSS variables from styling attributes.
	 *
	 * @param array  $styling_attributes The styling attributes to process.
	 * @param string $prefix The prefix to use for the CSS variable names.
	 * @return array An associative array of CSS variable names and their corresponding values.
	 */
	abstract public function build_css_variables( array $styling_attributes, string $prefix ): array;

	/**
	 * Builds CSS rules from styling attributes.
	 *
	 * @param array $styling_attributes The styling attributes to process.
	 * @return array An associative array of CSS rules.
	 */
	abstract public function build_css_rules( array $styling_attributes ): array;


	/**
	 * Creates CSS variables from a given set of attributes, applying an optional prefix to variable names.
	 *
	 * @param array  $attributes The attributes to convert into CSS variables.
	 * @param string $prefix An optional prefix to prepend to each CSS variable name.
	 * @return array An associative array of CSS variable names and their corresponding values.
	 */
	protected function compile_css_variables( array $attributes, string $prefix = '' ): array {

		$css_variables = array();

		foreach ( $attributes as $key => $value ) {
			if ( $value === null || $value === '' ) {
				continue;
			}

			$css_variable_name  = $prefix . BlockUtils::camel_case_to_kebab_case( $key );
			$css_variable_value = is_string( $value )
				? BlockUtils::css_preset_to_css_variable( $value )
				: (string) $value;

			$css_variables[ $css_variable_name ] = $css_variable_value;
		}

		return $css_variables;
	}

	/**
	 * Creates CSS rules from a given set of attributes.
	 *
	 * @param array $attributes The attributes to convert into CSS rules.
	 * @return array An associative array of CSS property names and their corresponding values.
	 */
	protected function compile_css_rules( array $attributes ): array {

		$css_rules = array();

		foreach ( $attributes as $key => $value ) {
			if ( $value === null || $value === '' ) {
				continue;
			}

			$css_property = BlockUtils::camel_case_to_kebab_case( $key );
			$css_value    = is_string( $value )
				? BlockUtils::css_preset_to_css_variable( $value )
				: (string) $value;

			$css_rules[ $css_property ] = $css_value;
		}

		return $css_rules;
	}
}

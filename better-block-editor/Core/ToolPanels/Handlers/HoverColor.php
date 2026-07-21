<?php
/**
 * Hover color tool panel handler.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels\Handlers;

use BetterBlockEditor\Core\BlockUtils;
use BetterBlockEditor\Core\ColorUtils;

defined( 'ABSPATH' ) || exit;

/**
 * Hover color (text/background/border) tool panel handler.
 */
class HoverColor extends HandlerBase {

	protected const KEYS = array( 'text', 'background', 'border' );

	public function build_css_variables( array $styling_attributes, string $prefix ): array {
		$hover_colors = $styling_attributes['hoverColors'] ?? array();

		if ( empty( $hover_colors ) ) {
			return array();
		}

		$css_variables = array();

		foreach ( self::KEYS as $key ) {
			if ( empty( $hover_colors[ $key ] ) ) {
				continue;
			}

			$var_name                  = $prefix . 'hover-' . BlockUtils::camel_case_to_kebab_case( $key );
			$css_variables[ $var_name ] = ColorUtils::color_attribute_to_css( $hover_colors[ $key ] );
		}

		return $css_variables;
	}

	// Hover colors only ever apply on `:hover` — there's no static rule to emit
	// here. Consuming blocks style `.has-hover-{key}:hover` themselves and read
	// the value from the CSS variable produced by build_css_variables above.
	public function build_css_rules( array $styling_attributes ): array {
		return array();
	}

	/**
	 * Builds the `has-hover-{key}` class list for whichever hover colors are set.
	 *
	 * @param array $hover_colors Hover color attribute values, keyed by text/background/border.
	 * @return string[] Class names to add to the block wrapper.
	 */
	public static function build_class_names( array $hover_colors ): array {
		$classes = array();

		foreach ( self::KEYS as $key ) {
			if ( ! empty( $hover_colors[ $key ] ) ) {
				$classes[] = 'has-hover-' . BlockUtils::camel_case_to_kebab_case( $key );
			}
		}

		return $classes;
	}
}

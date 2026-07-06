<?php
/**
 * Border and shadow tool panel handler.
 * there is a rule in WP core styles like "html :where([style*=border-top-color])"
 * so change the prefix to "brd" to avoid conflicts with core styles
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels\Handlers;

defined( 'ABSPATH' ) || exit;

/**
 * Border and shadow tool panel handler.
 */
class BorderShadow extends HandlerBase {

	protected const BORDER_SIDES = array( 'top', 'right', 'bottom', 'left' );

	public function build_css_variables( array $styling_attributes, string $prefix ): array {

		// clone (copy) attributes.
		$border_attributes = isset( $styling_attributes['border'] )
			? $styling_attributes['border']
			: array();

		$shadow_attribute = $styling_attributes['shadow'] ?? null;

		// early return if no border or shadow attributes are set.
		if ( empty( $border_attributes ) && ! $shadow_attribute ) {
			return array();
		}

		$css_variables = array();

		if ( $shadow_attribute ) {
			$css_variables += $this->compile_css_variables( array( 'shadow' => $shadow_attribute ), $prefix . 'box-' );
		}

		// handle radius (4 corners).
		if ( ! empty( $border_attributes['radius'] ) ) {
			$radius_css_variables = $this->compile_css_variables(
				$border_attributes['radius'],
				$prefix . 'brd-'
			);

			// rename keys -> add "-radius".
			foreach ( array_keys( $radius_css_variables ) as $key ) {
				$css_variables[ $key . '-radius' ] = $radius_css_variables[ $key ];
			}

			// remove radius from further processing.
			unset( $border_attributes['radius'] );
		}

		foreach ( self::BORDER_SIDES as $side ) {
			$side_attributes = $this->has_split_borders( $border_attributes )
				? ( $border_attributes[ $side ] ?? array() )
				: $border_attributes;

			$css_variables += $this->compile_css_variables(
				$side_attributes,
				$prefix . 'brd-' . $side . '-'
			);

		}

		return $css_variables;
	}

	public function build_css_rules( array $styling_attributes ): array {
		// clone (copy) attributes.
		$border_attributes = isset( $styling_attributes['border'] )
			? $styling_attributes['border']
			: array();

		$shadow_attribute = $styling_attributes['shadow'] ?? null;

		// early return if no border or shadow attributes are set.
		if ( empty( $border_attributes ) && ! $shadow_attribute ) {
			return array();
		}

		$css_rules = array();

		if ( $shadow_attribute ) {
			$css_rules += $this->compile_css_rules( array( 'box-shadow' => $shadow_attribute ) );
		}

		// handle border radius (4 corners).
		if ( ! empty( $border_attributes['radius'] ) ) {
			$radius_css_rules = $this->compile_css_rules( $border_attributes['radius'] );

			// rename keys -> add "-radius".
			foreach ( array_keys( $radius_css_rules ) as $key ) {
				$new_key               = 'border-' . $key . '-radius';
				$css_rules[ $new_key ] = $radius_css_rules[ $key ];
			}

			// remove radius from further processing.
			unset( $border_attributes['radius'] );
		}

		// border (with or without split sides).
		$border_rules = array();
		if (! $this->has_split_borders( $border_attributes ) ) {
			// if no split borders, compile all border attributes together (same values for all sides).
			$border_rules = $this->compile_css_rules( $border_attributes );
		} else {	
			// always process all sides.
			foreach ( self::BORDER_SIDES as $side ) {
				$side_attributes = array();
				foreach ( $border_attributes[ $side ] ?? array() as $key => $value ) {
					// add side prefix to attribute key, e.g. "border-top-width".
					$side_attributes[ 'border-' . $side . '-' . $key ] = $value;
				}

				$border_rules += $this->compile_css_rules( $side_attributes );
			}
		}
		// add prefix to border rules
		foreach ( $border_rules as $key => $value ) {
			// add "var(--prefix-)" to each css rule value, e.g. "var(--brd-top-width)".
			$css_rules[ 'border-'.$key ] = $value;
		}	

		return $css_rules;
	}

	/**
	 * Check if the border attributes have split borders (different values for different sides).
	 *
	 * @param array $border The border attributes to check.
	 * @return bool True if split borders are present, false otherwise.
	 */
	private function has_split_borders( array $border ): bool {
		return ! empty( array_intersect( array_keys( $border ), self::BORDER_SIDES ) );
	}
}

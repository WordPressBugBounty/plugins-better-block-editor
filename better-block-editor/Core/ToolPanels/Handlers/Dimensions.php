<?php
/**
 * Dimensions tool panel handler.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels\Handlers;

defined( 'ABSPATH' ) || exit;

/**
 * Dimensions tool panel handler.
 */
class Dimensions extends HandlerBase {

	const SPACING_ATTRIBUTES_MAP = array(
		'blockGap' => 'gap',
		'padding'  => 'padding',
		'margin'   => 'margin',
	);

	public function build_css_variables( array $styling_attributes, string $prefix ): array {
		$spacing_attributes    = $styling_attributes['spacing'] ?? array();
		$dimensions_attributes = $styling_attributes['dimensions'] ?? array();

		// early return if no spacing, dimensions or layout attributes are set.
		if ( empty( $spacing_attributes ) && empty( $dimensions_attributes ) ) {
			return array();
		}

		$css_variables = array();

		if ( ! empty( $spacing_attributes ) ) {
			// some spacing attributes (padding, margin, blockGap) can be arrays with values for different sides.
			foreach ( self::SPACING_ATTRIBUTES_MAP as $attribute_key => $css_variable_prefix ) {
				if ( is_array( $spacing_attributes[ $attribute_key ] ?? false ) ) {
					$css_variables += $this->compile_css_variables(
						$spacing_attributes[ $attribute_key ],
						$prefix . 'spacing-' . $css_variable_prefix . '-'
					);

					unset( $spacing_attributes[ $attribute_key ] );
				}
			}

			$css_variables += $this->compile_css_variables(
				$spacing_attributes,
				$prefix . 'spacing-'
			);
		}

		if ( ! empty( $dimensions_attributes ) ) {
			$css_variables += $this->compile_css_variables(
				$dimensions_attributes,
				$prefix . 'dimensions-'
			);
		}

		return $css_variables;
	}

	public function build_css_rules( array $styling_attributes ): array {
		$spacing_attributes    = $styling_attributes['spacing'] ?? array();
		$dimensions_attributes = $styling_attributes['dimensions'] ?? array();

		// early return if no spacing, dimensions or layout attributes are set.
		if ( empty( $spacing_attributes ) && empty( $dimensions_attributes ) ) {
			return array();
		}

		$css_rules = array();

		if ( ! empty( $spacing_attributes ) ) {
			// some spacing attributes (padding, margin, blockGap) can be arrays with values for different sides.
			foreach ( self::SPACING_ATTRIBUTES_MAP as $attribute_key => $css_rule_prefix ) {
				if ( is_array( $spacing_attributes[ $attribute_key ] ?? false ) ) {
					$css_rules += array_combine(
						array_map(
							function ( $key ) use ( $css_rule_prefix ) {
								return $css_rule_prefix . '-' . $key; },
							array_keys( $spacing_attributes[ $attribute_key ] )
						),
						$spacing_attributes[ $attribute_key ]
					);

					unset( $spacing_attributes[ $attribute_key ] );
				}
			}

			$css_rules += $this->compile_css_rules( $spacing_attributes );
		}

		// blockGap is a special case, because in CSS it is gap, so we need to rename it in the resulting CSS rules.
		if ( isset( $css_rules['block-gap'] ) ) {
			$css_rules['gap'] = $css_rules['block-gap'];
			unset( $css_rules['block-gap'] );
		}

		if ( ! empty( $dimensions_attributes ) ) {
			$css_rules += $this->compile_css_rules( $dimensions_attributes );
		}

		return $css_rules;
	}
}

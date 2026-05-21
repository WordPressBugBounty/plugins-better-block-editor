<?php
/**
 * Typography tool panel handler.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels\Handlers;

defined( 'ABSPATH' ) || exit;

/**
 * Typography tool panel handler.
 */
class Typography extends HandlerBase {

	public function build_css_variables( array $styling_attributes, string $prefix ): array {
		// early return if no typography attributes are set.
		if ( empty( $styling_attributes['typography'] ?? array() ) ) {
			return array();
		}

		return $this->compile_css_variables(
			$styling_attributes['typography'],
			$prefix . 'typography-'
		);
	}

	public function build_css_rules( array $styling_attributes ): array {
		// early return if no typography attributes are set.
		if ( empty( $styling_attributes['typography'] ?? array() ) ) {
			return array();
		}

		return $this->compile_css_rules( $styling_attributes['typography'] );
	}
}

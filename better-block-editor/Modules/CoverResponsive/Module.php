<?php
/**
 * Add responsive settings to Cover Image
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Modules\CoverResponsive;

use BetterBlockEditor\Base\ManagableModuleInterface;
use BetterBlockEditor\Base\ResponsiveBlockModuleBase;
use BetterBlockEditor\Core\BlockUtils;
use BetterBlockEditor\Core\ResponsiveBlockUtils as Responsiveness;
use BetterBlockEditor\Core\ResponsiveBlockUtils;
use BetterBlockEditor\Core\ToolPanels\ToolPanelsCss;

defined( 'ABSPATH' ) || exit;

final class Module extends ResponsiveBlockModuleBase implements ManagableModuleInterface {

	const MODULE_IDENTIFIER = 'cover-responsive';
	const ASSETS_BUILD_PATH = 'editor/blocks/cover/responsiveness/';

	const SETTINGS_ORDER = 1700;

	const BLOCK_NAME = 'core/cover';

	public static function get_title() {
		return __( 'Responsive Cover', 'better-block-editor' );
	}

	public static function get_label() {
		return __( 'Add Responsive Settings to Cover block.', 'better-block-editor' );
	}

	protected function need_to_apply_changes( $block_content, $block, $wp_block_instance ) {
		return true;
	}

	protected function render( $block_content, $block, $wp_block_instance ) {
		$class_id      = BlockUtils::get_unique_class_id( $block_content );
		$block_content = BlockUtils::append_classes( $block_content, $class_id );

		$this->add_styles( $class_id );

		return $block_content;
	}

	private function add_styles( string $class_id ): void {
		$responsive_settings = Responsiveness::get_settings( $this->attributes );
		// gap has to be applied to inner container, and not to the block itself
		$css_rules = ToolPanelsCss::build_rules( $responsive_settings, true );
		$gap_value = $css_rules['gap'] ?? null;
		unset($css_rules['gap']);

		// all other rules (except gap) have to be applied to the block itself 
		ResponsiveBlockUtils::add_style_for_media_query(
			"@media screen and (width <= {$this->switch_width})",
			'.' . $class_id . '.' . $class_id,
			$css_rules
		);

		$inner_container_selector = '.' . $class_id . '.' . $class_id . ' > .wp-block-cover__inner-container';
		// if gap value exists, use it as margin-top for all inner elements 
		// except the first one (logic from core)
		if ( null !== $gap_value ) {
			ResponsiveBlockUtils::add_style_for_media_query(
				"@media screen and (width <= {$this->switch_width})",
				$inner_container_selector . ' > :first-child ',
				array( 'margin-block-start' => '0 !important' ) // reset margin-top for the first child
			);

			ResponsiveBlockUtils::add_style_for_media_query(
				"@media screen and (width <= {$this->switch_width})",
				$inner_container_selector . ' > * + * ',
				array( 'margin-block-start' => $gap_value )
			);
		}
	}
}

<?php
/**
 * Add responsive settings to Grid (block name core/group).
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Modules\GridResponsive;

use BetterBlockEditor\Base\ManagableModuleInterface;
use BetterBlockEditor\Base\ResponsiveBlockModuleBase;
use BetterBlockEditor\Core\BlockUtils;
use BetterBlockEditor\Core\ResponsiveBlockUtils as Responsiveness;
use BetterBlockEditor\Core\ToolPanels\ToolPanelsCss;

defined( 'ABSPATH' ) || exit;

final class Module extends ResponsiveBlockModuleBase implements ManagableModuleInterface {

	const MODULE_IDENTIFIER = 'grid-responsive';
	const ASSETS_BUILD_PATH = 'editor/blocks/grid/responsiveness/';

	const BLOCK_NAME = 'core/group';

	const SETTINGS_ORDER = 250;

	public static function get_title() {
		return __( 'Responsive Grid', 'better-block-editor' );
	}

	public static function get_label() {
		return __( 'Add Responsive Settings to Grid block.', 'better-block-editor' );
	}

	protected function need_to_apply_changes( $block_content, $block, $wp_block_instance ) {
		// if it's not a grid type of block
		return ( $this->attributes['layout']['type'] ?? null ) === 'grid';
	}

	protected function render( $block_content, $block, $wp_block_instance ) {
		$class_id      = BlockUtils::get_unique_class_id( $block_content );
		$block_content = BlockUtils::append_classes( $block_content, array( $class_id ) );

		$css_rules = array();

		if ( Responsiveness::get_setting( $this->attributes, 'stack', false ) ) {
			$css_rules['grid-template-columns'] = 'repeat(1, 1fr)';
		}

		// on FE we use static as default (as it's done in WP core)
		if ( Responsiveness::get_setting( $this->attributes, 'disablePositionSticky', false ) ) {
			$css_rules['position'] = 'static';
		}

		$css_rules = array_merge(
			$css_rules,
			ToolPanelsCss::build_rules( Responsiveness::get_settings( $this->attributes ), true )
		);

		Responsiveness::add_style_for_media_query(
			"@media screen and (width <= {$this->switch_width})",
			".{$class_id}.{$class_id}",
			$css_rules
		);

		return $block_content;
	}
}

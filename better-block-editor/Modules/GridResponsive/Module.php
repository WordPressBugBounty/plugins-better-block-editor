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
use BetterBlockEditor\Core\StylingUtils;

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
		// if it's not a grid type of core/group
		return ( $this->attributes['layout']['type'] ?? null ) === 'grid';
	}

	protected function render( $block_content, $block, $wp_block_instance ) {
		$class_id      = BlockUtils::get_unique_class_id( $block_content, $block );
		$block_content = BlockUtils::append_classes( $block_content, array( $class_id ) );

		$min_column_width = Responsiveness::get_setting( $this->attributes, 'minColumnWidth', null );
		$max_columns      = Responsiveness::get_setting( $this->attributes, 'maxColumns', null );

		// respect old format if it's present (only in case there are no new format values set)
		$stack = Responsiveness::get_setting( $this->attributes, 'stack', null );
		if ( $stack && $min_column_width === null && $max_columns === null ) {
			$max_columns = 1;
		}

		// get the block gap value to use for calculations
		// (if it's set in the responsive settings, use it, otherwise use the block level gap value)
		$responsive_spacing = Responsiveness::get_setting( $this->attributes, 'spacing', array() );
		$block_gap          = $responsive_spacing['blockGap'] ?? $this->attributes['style']['spacing']['blockGap'] ?? null;

		$block_gap_to_use = StylingUtils::get_effective_block_gap( self::BLOCK_NAME, $block_gap );

		$css_rules = array();

		// see editor.js for references
		if ( $min_column_width && ( $max_columns > 0 ) ) {
			$max_value = "max(min($min_column_width, 100%), ( 100% - ($block_gap_to_use * ($max_columns - 1)) ) / $max_columns)";

			$css_rules['grid-template-columns'] = "repeat(auto-fill, minmax( $max_value, 1fr))";
			$css_rules['container-type']        = 'inline-size';
		} elseif ( $max_columns ) {
			$css_rules['grid-template-columns'] = "repeat( $max_columns , minmax(0, 1fr))";
		} else {
			$css_rules['grid-template-columns'] = 'repeat(auto-fill, minmax(min(' . ( $min_column_width ?? '12rem' ) . ', 100%), 1fr))';
			$css_rules['container-type']        = 'inline-size';
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

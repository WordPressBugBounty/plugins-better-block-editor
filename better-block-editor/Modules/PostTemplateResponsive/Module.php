<?php
/**
 * Adds responsiveness settings to Post Template block.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Modules\PostTemplateResponsive;

use BetterBlockEditor\Base\ManagableModuleInterface;
use BetterBlockEditor\Base\ResponsiveBlockModuleBase;
use BetterBlockEditor\Core\BlockUtils;

defined( 'ABSPATH' ) || exit;

class Module extends ResponsiveBlockModuleBase implements ManagableModuleInterface {

	const MODULE_IDENTIFIER = 'post-template-stack-on-responsive';
	const ASSETS_BUILD_PATH = 'editor/blocks/post-template/responsiveness/';

	const SETTINGS_ORDER = 900;

	const BLOCK_NAME = 'core/post-template';

	public static function get_title() {
		return __( 'Responsive Post Template', 'better-block-editor' );
	}

	public static function get_label() {
		return __( 'Add Responsive Settings to Post Template block when used in Grid view.', 'better-block-editor' );
	}

	protected function need_to_apply_changes( $block_content, $block, $wp_block_instance ) {
		// handle only "grid" mode
		if ( ( $this->attributes['layout']['type'] ?? null ) !== 'grid' ) {
			return false;
		}

		return true;
	}

	protected function render( $block_content, $block, $wp_block_instance ) {
		$class_id      = BlockUtils::get_unique_class_id( $block_content, $block );
		$block_content = BlockUtils::append_classes( $block_content, array( $class_id ) );

		// get some block settings
		$min_column_width    = $this->attributes['layout']['minimumColumnWidth'] ?? null;
		$column_count    = $this->attributes['layout']['columnCount'] ?? null;

		// use the global blockGap value as fallback when available.
		// If the gap value has both top and left (separated by space), use the left value for horizontal calculations.
		// get global styles values for block gap
		$global_common_block_gap = wp_get_global_styles(array( 'spacing', 'blockGap' ));
		
		$global_post_template_block_gap = wp_get_global_styles(
			array( 'spacing', 'blockGap' ), 
			array( 'block_name' => 'core/post-template' ) 
		);

		$fallback_gap = $global_post_template_block_gap ?? $global_common_block_gap ?? '1.2rem';
		// if the gap value has both top and left (separated by space), use the left value for horizontal calculations.
		// see editor.js for details on how fallback gap value is calculated.
		if ( strpos( $fallback_gap, ' ' ) !== false ) {
			$fallback_gap_parts = explode( ' ', $fallback_gap );
			$fallback_gap = $fallback_gap_parts[1] ?? $fallback_gap_parts[0];
		}
		
		$block_gap_to_use = $this->attributes['style']['spacing']['blockGap'] ?? $fallback_gap;
		
		$css_rules = array();

		if ( $min_column_width  &&  ($column_count > 0) ) {
			// Ensure 0 values have a unit so they work in calc().
			if ( $block_gap_to_use === '0' || $block_gap_to_use === 0 ) {
				$block_gap_to_use = '0px';
			}

			$max_value = 'max(min('.$min_column_width.', 100%), ( 100% - ('.$block_gap_to_use.'*'.'('.$column_count.' - 1)) ) / '.$column_count.')';
			$css_rules['grid-template-columns'] = "repeat(auto-fill, minmax( $max_value, 1fr))";
			$css_rules['container-type'] = 'inline-size';
		} else if ( $column_count ) {
			$css_rules['grid-template-columns'] = "repeat( $column_count , minmax(0, 1fr))";
		} else {
			$css_rules['grid-template-columns'] ='repeat(auto-fill, minmax(min(' . ($min_column_width ?? '12rem') . ', 100%), 1fr))';
			$css_rules['container-type'] = 'inline-size';
		}

		BlockUtils::add_styles_from_css_rules(
			array(
				array(
					'selector'     => '.wp-block-post-template-is-layout-grid'. str_repeat('.' . $class_id, 4),
					'declarations' => $css_rules,
				),
			)
		);

		// stack on responsive, another set of css rules
		$css_rules = array( 'grid-template-columns' => 'repeat(1, 1fr) !important' );

		$gap = $this->get_responsive_setting( 'gap' );

		// need strict comparison here as gap may be 0
		if ( null !== $gap ) {
			$css_rules['gap'] = $gap . ' !important';
		}

		BlockUtils::add_style_for_media_query(
			"@media screen and (width <= {$this->switch_width})",
			'.wp-block-post-template-is-layout-grid'. str_repeat('.' . $class_id, 4),
			$css_rules
		);

		return $block_content;
	}
}

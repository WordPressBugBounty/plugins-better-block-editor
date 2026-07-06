<?php
/**
 * StylingUtils class provides utility functions for handling styling-related tasks.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core;

defined( 'ABSPATH' ) || exit;

class StylingUtils {
	/**
	 * Get the effective block gap value to use for calculations.
	 * Block level gap value takes precedence over global styles. If neither is set, a default of '1.2rem' is used.
	 * most of this code repeats the logic from node_modules/@wordpress/block-editor/src/layouts/grid.js:144
	 *
	 * @param string                $block_name The name of the block (e.g., 'core/post-template').
	 * @param string|float|int|null $block_level_block_gap The block-level gap value, if set.
	 * @return string The effective block gap value to use.
	 */
	public static function get_effective_block_gap( $block_name, $block_level_block_gap ) {
		// 1.2em is hardcoded in @wordpress/block-editor/src/layouts/grid.js:144
		// BUT actually if $global_styles['spacing']['blockGap'] is not set WP will return 24px from it's core theme.json 
		$global_styles = wp_get_global_styles();  

		$fallback_gap = $global_styles['blocks'][$block_name]['spacing']['blockGap']   
			?? $global_styles['spacing']['blockGap']   
			?? '1.2rem';
		
		// if the gap value has both top and left (separated by space), use the left value for horizontal calculations.
		// see editor.js for details on how fallback gap value is calculated.
		if ( strpos( $fallback_gap, ' ' ) !== false ) {
			$fallback_gap_parts = explode( ' ', $fallback_gap );
			$fallback_gap       = $fallback_gap_parts[1] ?? $fallback_gap_parts[0];
		}

		$block_gap_to_use = $block_level_block_gap ?? $fallback_gap;
				
		// Ensure 0 values have a unit so they work in calc().
		$block_gap_to_use = ( (string) $block_gap_to_use ) === '0' ? '0px' : $block_gap_to_use;

		return $block_gap_to_use;
	}
}

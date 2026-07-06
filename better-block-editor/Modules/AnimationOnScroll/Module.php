<?php
/**
 * Adds Animation on Scroll settings to all blocks.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Modules\AnimationOnScroll;

use BetterBlockEditor\Base\ModuleBase;
use BetterBlockEditor\Base\ManagableModuleInterface;
use BetterBlockEditor\Core\BlockUtils;
use BetterBlockEditor\Core\Settings\Animation\Settings as AnimationSettings;
use BetterBlockEditor\Plugin;

defined( 'ABSPATH' ) || exit;

class Module extends ModuleBase implements ManagableModuleInterface {

	const MODULE_IDENTIFIER        = 'animation-on-scroll';
	const ASSETS_BUILD_PATH        = 'editor/blocks/__all__/animation-on-scroll/';
	const PLUGIN_ASSETS_BUILD_PATH = 'editor/plugins/animation-on-scroll/';

	const SETTINGS_ORDER = 1000;

	const ATTRIBUTE_GROUP = 'wpbbeAnimationOnScroll';

	/**
	 * add animation on scroll plugin assets to editor (displays Play button in top toolbar)
	 */
	public function process_assets() {
		parent::process_assets();

		// in asset bundle mode plugin assets are already registered
		if ( Plugin::instance()->is_asset_bundle_mode() ) {
			return;
		}

		$asset_file = require WPBBE_DIST . $this::PLUGIN_ASSETS_BUILD_PATH . 'editor.asset.php';

		wp_register_script(
			$this->build_script_handle( 'editor-plugin' ),
			WPBBE_URL_DIST . $this::PLUGIN_ASSETS_BUILD_PATH . $this::EDITOR_ASSET_KEY . '.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			array(
				'strategy'  => 'defer',
				'in_footer' => true,
			)
		);

		if ( file_exists( WPBBE_DIST . $this::PLUGIN_ASSETS_BUILD_PATH . $this::EDITOR_ASSET_KEY . '.css' ) ) {
			wp_register_style(
				$this->build_style_handle( 'editor-plugin' ),
				WPBBE_URL_DIST . $this::PLUGIN_ASSETS_BUILD_PATH . $this::EDITOR_ASSET_KEY . '.css',
				array(),
				$asset_file['version']
			);
		}

		add_action(
			'enqueue_block_editor_assets',
			function () {
				$this->enqueue_assets( 'editor-plugin' );
			}
		);
	}

	public function setup_hooks() {
		add_filter( 'render_block', array( $this, 'render' ), 20, 3 );
	}

	private static function normalize_time_value( $value ): string {
		if ( is_numeric( $value ) ) {
			// Old format
			return intval( $value ) . 'ms';
		}
		// New format: "100ms", "2s"
		return $value;
	}

	public function render( $block_content, $block ) {

		$animation_settings = $block['attrs'][ self::ATTRIBUTE_GROUP ] ?? null;

		// if key is present but value is an empty array it's ok - use all default values
		if ( null === $animation_settings || $block_content === '' ) {
			return $block_content;
		}

		// in case some settings are missing, get the default ones
		$animation_settings = $animation_settings + AnimationSettings::get_on_scroll_defaults();

		$data_attributes = array(
			'data-aos'        => $animation_settings['animation'] ?? null,
			'data-aos-easing' => $animation_settings['timingFunction'] ?? null,
		);

		$tag = BlockUtils::get_tag_to_modify( $block_content );
		foreach ( $data_attributes as $key => $value ) {
			$tag->set_attribute( $key, $value );
		}
		$block_content = $tag->get_updated_html();

		$class_id = BlockUtils::get_unique_class_id( $block_content, $block );

		BlockUtils::add_styles_from_css_rules(
			array(
				array(
					'selector'     => '.' . $class_id,
					'declarations' => array(
						'--aos-duration' => ( self::normalize_time_value( $animation_settings['duration'] ?? 0 ) ),
						'--aos-delay'    => ( self::normalize_time_value( $animation_settings['delay'] ?? 0 ) ),
					),
				),
			)
		);

		return BlockUtils::append_classes( $block_content, $class_id );
	}


	public static function get_title() {
		return __( 'Animation on Scroll', 'better-block-editor' );
	}

	public static function get_label() {
		return __( 'Add Animation on Scroll settings to all blocks.', 'better-block-editor' );
	}
}

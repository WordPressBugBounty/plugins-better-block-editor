<?php
/**
 * Handles registration and enqueuing of bundled assets.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core;

defined( 'ABSPATH' ) || exit;

final class BundledAssetsManager {

	const BUNDLE_DIR = 'bundle/';

	const EDITOR_BUNDLE         = 'editor';
	const EDITOR_CONTENT_BUNDLE = 'editor-content';
	const VIEW_BUNDLE           = 'view';

	/**
	 * @var string Plugin ID used for building handles.
	 */
	private $plugin_id;

	/**
	 * @var string URL to the plugin's distribution directory.
	 */
	private $plugin_dist_url;

	/**
	 * @var string File system path to the plugin's distribution directory.
	 */
	private $plugin_dist;

	public function __construct( string $plugin_id, string $plugin_dist, string $plugin_dist_url ) {
		$this->plugin_id       = $plugin_id;
		$this->plugin_dist     = $plugin_dist;
		$this->plugin_dist_url = $plugin_dist_url;
	}

	/**
	 * Register and enqueue editor assets for the block editor interface.
	 *
	 * @return void
	 */
	public function process_editor_assets() {
		$this->register_assets( 'editor' );
		$this->enqueue_assets( 'enqueue_block_editor_assets', 'editor' );
	}

	/**
	 * Register and enqueue editor-content assets for block editor content area.
	 *
	 * @return void
	 */
	public function process_editor_content_assets() {
		$this->register_assets( 'editor-content' );
		$this->enqueue_assets( 'enqueue_block_assets', 'editor-content' );
	}

	/**
	 * Register and enqueue view assets for the frontend.
	 *
	 * @return void
	 */
	public function process_view_assets() {
		$this->register_assets( 'view' );
		$this->enqueue_assets( 'wp_enqueue_scripts', 'view' );
	}

	/**
	 * Add inline JS code just before bundle code (see wp_add_inline_script())
	 *
	 * @param string $bundle_name Bundle name(key) to add code to (see self::*_BUNDLE)
	 * @param string $js JS code to be added as inline script
	 *
	 * @return bool
	 */
	public function add_inline_js_before_bundle( $bundle_name, $js ) {
		return wp_add_inline_script(
			$this->build_script_handle( $bundle_name ),
			$js,
			'before'
		);
	}

	/**
	 * Add inline JS code just after bundle code (see wp_add_inline_script())
	 *
	 * @param string $bundle_name Bundle name(key) to add code to (see self::*_BUNDLE)
	 * @param string $js JS code to be added as inline script
	 *
	 * @return bool
	 */
	public function add_inline_js_after_bundle( $bundle_name, $js ) {
		return wp_add_inline_script(
			$this->build_script_handle( $bundle_name ),
			$js,
			'after'
		);
	}

	/**
	 * Register script and style assets for a given bundle key.
	 *
	 * @param string $key Bundle key (one of 'editor', 'editor-content', 'view').
	 * @param array  $script_register_options Optional script registration options.
	 *
	 * @return void
	 */
	private function register_assets( $key, $script_register_options = array() ) {
		if ( ! file_exists( $this->get_asset_filename( $key ) ) ) {
			return;
		}

		$asset_file = require $this->get_asset_filename( $key );

		// script
		$default_options = array(
			'strategy'  => 'defer',
			'in_footer' => false,
		);
		// it's safe to return here as css is added only using js import construction
		if ( ! file_exists( $this->plugin_dist . self::BUNDLE_DIR . $key . '.js' ) ) {
			return;
		}

		$res = wp_register_script(
			$this->build_script_handle( $key ),
			$this->plugin_dist_url . self::BUNDLE_DIR . $key . '.js',
			$asset_file['dependencies'],
			$asset_file['version'],
			array_merge( $default_options, $script_register_options )
		);

		// style
		if ( ! file_exists( $this->plugin_dist . self::BUNDLE_DIR . $key . '.css' ) ) {
			return;
		}

		wp_register_style(
			$this->build_style_handle( $key ),
			$this->plugin_dist_url . self::BUNDLE_DIR . $key . '.css',
			array(), // no dependencies for styles
			$asset_file['version']
		);
	}

	/**
	 * Enqueue registered script and style assets for a given action and bundle key.
	 *
	 * @param string $action WordPress action hook.
	 * @param string $key    Bundle key (one of 'editor', 'editor-content', 'view').
	 *
	 * @return void
	 */
	private function enqueue_assets( $action, $key ) {
		$script_handle = $this->build_script_handle( $key );
		$style_handle  = $this->build_style_handle( $key );

		add_action(
			$action,
			function () use ( $script_handle, $style_handle ) {
				wp_enqueue_script( $script_handle );
				wp_enqueue_style( $style_handle );
			}
		);
	}

	/**
	 * Get the asset metadata filename for a given bundle key.
	 *
	 * @param string $key Bundle key (one of 'editor', 'editor-content', 'view').
	 *
	 * @return string Path to the asset metadata file.
	 */
	private function get_asset_filename( $key ) {
		return $this->plugin_dist . self::BUNDLE_DIR . $key . '.asset.php';
	}

	/**
	 * Build the script handle name for a given bundle key.
	 *
	 * @param string $key Bundle key (one of 'editor', 'editor-content', 'view').
	 *
	 * @return string Script handle name.
	 */
	private function build_script_handle( $key ) {
		return $this->plugin_id . '__' . 'bundle' . '__' . $key . '-script';
	}

	/**
	 * Build the style handle name for a given bundle key.
	 *
	 * @param string $key Bundle key (one of 'editor', 'editor-content', 'view').
	 *
	 * @return string Style handle name.
	 */
	private function build_style_handle( $key ) {
		return $this->plugin_id . '__' . 'bundle' . '__' . $key . '-style';
	}
}

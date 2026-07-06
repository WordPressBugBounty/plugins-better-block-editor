<?php
/**
 * Animation settings for Better Block Editor.
 * Animation settings are divided into two groups: animation-on-scroll and animation-on-hover.
 * Animation-on-hover is present in pro version only, but settings are still in the same place.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\Settings\Animation;

use BetterBlockEditor\Plugin;
use BetterBlockEditor\Core\Settings as CoreSettings;
use BetterBlockEditor\Core\Settings\Animation\Fields\{FieldInterface, TimingFunction, Effect, Scale, Delay, Duration, Translate};

defined( 'ABSPATH' ) || exit;

class Settings {

	const ANIMATION_ON_HOVER_FEATURE  = 'animation-on-hover';
	const ANIMATION_ON_SCROLL_FEATURE = 'animation-on-scroll';

	public static function get_timing_function_options(): array {
		return array(
			'linear'            => __( 'Linear', 'better-block-editor' ),
			'ease'              => __( 'Ease', 'better-block-editor' ),
			'ease-in'           => __( 'Ease in', 'better-block-editor' ),
			'ease-out'          => __( 'Ease out', 'better-block-editor' ),
			'ease-in-out'       => __( 'Ease in out', 'better-block-editor' ),
			'ease-back'         => __( 'Ease back', 'better-block-editor' ),
			'ease-in-quad'      => __( 'Ease in quad', 'better-block-editor' ),
			'ease-out-quad'     => __( 'Ease out quad', 'better-block-editor' ),
			'ease-in-out-quad'  => __( 'Ease in out quad', 'better-block-editor' ),
			'ease-in-quart'     => __( 'Ease in quart', 'better-block-editor' ),
			'ease-out-quart'    => __( 'Ease out quart', 'better-block-editor' ),
			'ease-in-out-quart' => __( 'Ease in out quart', 'better-block-editor' ),
			'ease-in-expo'      => __( 'Ease in expo', 'better-block-editor' ),
			'ease-out-expo'     => __( 'Ease out expo', 'better-block-editor' ),
			'ease-in-out-expo'  => __( 'Ease in out expo', 'better-block-editor' ),
		);
	}

	public static function get_effect_options(): array {
		return array(
			'fade-in'     => __( 'Fade in', 'better-block-editor' ),
			'fade-out'    => __( 'Fade out', 'better-block-editor' ),
			'slide-up'    => __( 'Slide up', 'better-block-editor' ),
			'slide-down'  => __( 'Slide down', 'better-block-editor' ),
			'slide-left'  => __( 'Slide left', 'better-block-editor' ),
			'slide-right' => __( 'Slide right', 'better-block-editor' ),
			'zoom-in'     => __( 'Zoom in', 'better-block-editor' ),
			'zoom-out'    => __( 'Zoom out', 'better-block-editor' ),
		);
	}

	public static function add_settings(): void {
		if ( Plugin::instance()->is_feature_active( self::ANIMATION_ON_SCROLL_FEATURE ) ) {
			CoreSettings::add_separator( CoreSettings::TAB_ANIMATION, __( 'On Scroll Animations', 'better-block-editor' ) );

			foreach ( self::get_on_scroll_options() as $option ) {
				self::add_option( $option );
			}
		}

		if ( Plugin::instance()->is_feature_active( self::ANIMATION_ON_HOVER_FEATURE ) ) {
			CoreSettings::add_separator( CoreSettings::TAB_ANIMATION, __( 'On Hover Animations', 'better-block-editor' ) );

			foreach ( self::get_on_hover_options() as $option ) {
				self::add_option( $option );
			}
		}
	}

	public static function get_on_hover_defaults(): array {
		$js_export = array();

		foreach ( self::get_on_hover_options() as $key => $option ) {
			$js_export[ $key ] = $option->get_value() . $option->get_unit();
		}

		return $js_export;
	}

	public static function get_on_scroll_defaults(): array {
		$js_export = array();

		foreach ( self::get_on_scroll_options() as $key => $option ) {
			$js_export[ $key ] = $option->get_value() . $option->get_unit();
		}

		return $js_export;
	}

	private static function add_option( FieldInterface $option ): void {
		register_setting(
			WPBBE_PLUGIN_ID . '_settings',
			$option->get_option_name(),
			array(
				'type'              => $option->get_type(),
				'sanitize_callback' => $option->get_sanitize_callback(),
				// do not set default here, otherwise on save behaviour is not solid
				// (default values are not saved to database)
			)
		);

			add_settings_field(
				$option->get_option_name(),
				$option->get_label(),
				function () use ( $option ) {
					CoreSettings::parse_template(
						'_setting_wrapper',
						array(
							'tab'           => CoreSettings::TAB_ANIMATION,
							'template'      => $option->get_template_filename(),
							'template_args' => $option->get_template_args(),
						)
					);
				},
				CoreSettings::MENU_PAGE_SLUG,
				WPBBE_PLUGIN_ID . '_settings_section',
				array( 'class' => 'wpbbe-animation-setting-row' )
			);
	}

	private static function get_on_scroll_options(): array {
		$prefix = WPBBE_PLUGIN_ID . '__animation-on-scroll-default__';

		return array(
			// in js and frontend effect option is called "animation"
			'animation'      => new Effect(
				$prefix . 'effect',
				array_keys( self::get_effect_options() )[0] ?? ''
			),
			'timingFunction' => new TimingFunction(
				$prefix . 'timing-function',
				array_keys( self::get_timing_function_options() )[0] ?? ''
			),
			'duration'       => new Duration( $prefix . 'duration', 300 ),
			'delay'          => new Delay( $prefix . 'delay', 0 ),
		);
	}

	private static function get_on_hover_options(): array {
		$prefix = WPBBE_PLUGIN_ID . '__animation-on-hover-default__';

		return array(

			'translateX'     => new Translate(
				$prefix . 'translate-x',
				0,
				__( 'Translate X', 'better-block-editor' )
			),

			'translateY'     => new Translate(
				$prefix . 'translate-y',
				0,
				__( 'Translate Y', 'better-block-editor' )
			),

			'scale'          => new Scale( $prefix . 'scale', 100 ),

			'timingFunction' => new TimingFunction(
				$prefix . 'timing-function',
				array_keys( self::get_timing_function_options() )[0] ?? ''
			),

			'duration'       => new Duration( $prefix . 'duration', 300 ),
		);
	}
}

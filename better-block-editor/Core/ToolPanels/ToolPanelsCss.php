<?php
/**
 * Utility class to handle tool panel related attributes for all panels.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\ToolPanels;

use WpOrg\Requests\Response\Headers;

defined( 'ABSPATH' ) || exit;

/**
 * Utility class to build CSS for all panels at once.
 */
class ToolPanelsCss {

	const HANDLER_NAMESPACE_BASE = 'BetterBlockEditor\Core\ToolPanels\Handlers\\';

	protected const HANDLER_CLASSES = array(
		self::HANDLER_NAMESPACE_BASE . 'BorderShadow',
		self::HANDLER_NAMESPACE_BASE . 'Dimensions',
		self::HANDLER_NAMESPACE_BASE . 'Typography',
		self::HANDLER_NAMESPACE_BASE . 'HoverColor',
	);

	/**
	 * Builds CSS variables from styling attributes using registered handlers
	 *
	 * @param array  $styling_attributes The styling attributes to process
	 * @param string $prefix The prefix to use for the CSS variable names
	 * @param bool   $add_important Whether to add !important to each CSS variable value
	 * @return array An associative array of CSS variable names and their corresponding values
	 */
	public static function build_variables(
		array $styling_attributes,
		string $prefix,
		bool $add_important = false
	): array {

		// return early if there are no styling attributes to process
		if ( empty( $styling_attributes ) ) {
			return array();
		}

		$css_variables = array();

		foreach ( self::HANDLER_CLASSES as $handler_class ) {
			if ( class_exists( $handler_class ) ) {
				/** @var Handlers\HandlerBase $handler */
				$handler               = new $handler_class();
				$handler_css_variables = $handler->build_css_variables( $styling_attributes, $prefix );

				if ( $add_important ) {
					foreach ( $handler_css_variables as $key => $value ) {
						$handler_css_variables[ $key ] = $value . ' !important';
					}
				}

				$css_variables = array_merge( $css_variables, $handler_css_variables );
			}
		}

		return $css_variables;
	}

	/**
	 * Builds CSS rules from styling attributes using registered handlers
	 *
	 * @param array $styling_attributes The styling attributes to process
	 * @param bool  $add_important Whether to add !important to each CSS rule
	 * @return array An associative array of CSS rules
	 */
	public static function build_rules( array $styling_attributes, bool $add_important = false ): array {

		// return early if there are no styling attributes to process.
		if ( empty( $styling_attributes ) ) {
			return array();
		}

		$css_rules = array();

		foreach ( self::HANDLER_CLASSES as $handler_class ) {
			if ( class_exists( $handler_class ) ) {
				/** @var Handlers\HandlerBase $handler */
				$handler           = new $handler_class();
				$handler_css_rules = $handler->build_css_rules( $styling_attributes );

				if ( $add_important ) {
					foreach ( $handler_css_rules as $key => $value ) {
						$handler_css_rules[ $key ] = $value . ' !important';
					}
				}

				$css_rules = array_merge( $css_rules, $handler_css_rules );
			}
		}

		return $css_rules;
	}
}

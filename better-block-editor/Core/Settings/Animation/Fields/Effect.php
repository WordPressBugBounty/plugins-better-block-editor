<?php
/**
 * Effect field for animation settings.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\Settings\Animation\Fields;

use BetterBlockEditor\Core\Settings\Animation\Settings as AnimationSettings;

defined( 'ABSPATH' ) || exit;

class Effect extends FieldBase implements FieldInterface {

	protected const FIELD_TYPE = 'string';

	public function get_label(): string {
		return $this->label ?? __( 'Effect', 'better-block-editor' );
	}

	public function get_description(): string {
		return $this->description ?? __( '', 'better-block-editor' );
	}

	public function get_template_filename(): string {
		return '_selectbox';
	}

	public function get_sanitize_callback(): callable {
		return function ( $value ) {

			$value = strtolower( trim( $value ) );

			$valid_values = array_keys( AnimationSettings::get_effect_options() );

			return in_array( $value, $valid_values, true ) ? $value : $this->default_value;
		};
	}

	public function get_template_args(): array {
		return parent::get_template_args() + array( 'options' => AnimationSettings::get_effect_options() );
	}
}

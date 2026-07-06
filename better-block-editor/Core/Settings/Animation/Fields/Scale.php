<?php
/**
 * Scale field for animation settings.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\Settings\Animation\Fields;

defined( 'ABSPATH' ) || exit;

class Scale extends FieldBase implements FieldInterface {

	protected const FIELD_TYPE = 'integer';

	protected const UNIT = '%';

	public function get_label(): string {
		return $this->label ?? __( 'Scale', 'better-block-editor' );
	}

	public function get_description(): string {
		return $this->description ?? __( '%', 'better-block-editor' );
	}

	public function get_template_filename(): string {
		return '_number';
	}

	public function get_sanitize_callback(): callable {
		return function ( $value ) {

			$value = intval( $value );

			return $value > 1000 ? 1000 : ( $value < 0 ? 0 : $value );
		};
	}

	public function get_template_args(): array {
		return parent::get_template_args() + array(
			'min'  => 0,
			'max'  => 1000,
			'step' => 1,
		);
	}
}

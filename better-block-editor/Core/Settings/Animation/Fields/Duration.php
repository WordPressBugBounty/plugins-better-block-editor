<?php
/**
 * Duration field for animation settings.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\Settings\Animation\Fields;

defined( 'ABSPATH' ) || exit;

class Duration extends FieldBase implements FieldInterface {

	protected const FIELD_TYPE = 'integer';

	protected const UNIT = 'ms';

	public function get_label(): string {
		return $this->label ?? __( 'Duration', 'better-block-editor' );
	}

	public function get_description(): string {
		return $this->description ?? __( 'milliseconds', 'better-block-editor' );
	}

	public function get_template_filename(): string {
		return '_number';
	}

	public function get_sanitize_callback(): callable {
		return function ( $value ) {
			return abs( intval( $value > 10000 ? 10000 : ( $value < 0 ? 0 : $value ) ) );
		};
	}

	public function get_template_args(): array {
		return parent::get_template_args() + array(
			'min'  => 0,
			'max'  => 10000,
			'step' => 1,
		);
	}
}

<?php
/**
 * Base class for animation settings fields.
 *
 * @package BetterBlockEditor
 */

namespace BetterBlockEditor\Core\Settings\Animation\Fields;

defined( 'ABSPATH' ) || exit;

abstract class FieldBase {


	/** @var string */
	protected string $option_name;

	/** @var ?string */
	protected ?string $label = null;

	/** @var ?string */
	protected ?string $description = null;

	/** @var mixed */
	protected $default_value;

	/**
	 * stub value for field registration type, has to be overridden in subclasses
	 *
	 * @var string
	 */
	protected const FIELD_TYPE = '';

	/**
	 * in case value has a unit (like ms for duration), this constant can be overridden in subclasses
	 *
	 * @var string
	 */
	protected const UNIT = '';

	/**
	 * @param string      $option_name
	 * @param mixed       $default_value
	 * @param string|null $label
	 * @param string|null $description
	 */
	public function __construct( string $option_name, $default_value, ?string $label = null, ?string $description = null ) {
		$this->option_name   = $option_name;
		$this->label         = $label;
		$this->description   = $description;
		$this->default_value = $default_value;
	}

	public function get_option_name(): string {
		return $this->option_name;
	}

	public function get_label(): string {
		return $this->label ?? '';
	}

	public function get_description(): string {
		return $this->description ?? '';
	}

	public function get_value() {
		return get_option( $this->option_name, $this->default_value );
	}

	public function get_unit(): string {
		return $this::UNIT;
	}

	public function get_default_value() {
		return $this->default_value;
	}

	public function get_type(): string {
		return $this::FIELD_TYPE;
	}

	public function get_template_args(): array {
		return array(
			'required'    => true,
			'identifier'  => $this->option_name,
			'value'       => $this->get_value(),
			'description' => $this->get_description(),
		);
	}
}

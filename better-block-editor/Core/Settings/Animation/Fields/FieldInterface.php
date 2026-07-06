<?php
/**
 * Interface for animation settings fields.
 *
 * @package BetterBlockEditor
 */


namespace BetterBlockEditor\Core\Settings\Animation\Fields;

defined( 'ABSPATH' ) || exit;

interface FieldInterface {

	public function get_option_name(): string;

	public function get_label(): string;

	public function get_description(): string;

	public function get_value();

	public function get_unit(): string;

	public function get_default_value();

	public function get_template_filename(): string;

	public function get_type(): string;

	public function get_sanitize_callback(): callable;

	public function get_template_args(): array;
}

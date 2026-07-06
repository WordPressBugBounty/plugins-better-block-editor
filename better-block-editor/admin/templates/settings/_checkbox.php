<?php defined( 'ABSPATH' ) || exit; ?>

<fieldset>
	<label for="<?php echo esc_attr( $args['identifier'] ); ?>">
		<input
			type="checkbox"
			name="<?php echo esc_attr( $args['identifier'] ); ?>"
			id="<?php echo esc_attr( $args['identifier'] ); ?>"
			value="1"
			<?php checked( true, $args['value'] ); ?>
		/>
		<?php echo esc_html( $args['label'] ?? '' ); ?>
	</label>

	<?php if ( ! empty( $args['description'] ) ) : ?>
		<p class="description">
			<?php echo esc_html( $args['description'] ); ?>
		</p>
	<?php endif; ?>
</fieldset>

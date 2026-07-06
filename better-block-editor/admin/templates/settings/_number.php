<?php defined( 'ABSPATH' ) || exit; ?> 

<fieldset>
	<label for="<?php echo esc_attr( $args['identifier'] ); ?>">
		<input
			type="number"
			id="<?php echo esc_attr( $args['identifier'] ); ?>"
			name="<?php echo esc_attr( $args['identifier'] ); ?>"
			class="small-text<?php echo esc_attr( ($args['class'] ?? '' ) !== '' ? (' ' . $args['class']) : '' ); ?>"																							"
			value="<?php echo esc_attr( $args['value'] ?? ($args['required'] ?? false ? 0 : '') ); ?>"

			<?php if ( ($args['step'] ?? '') !== '' ) : ?>
				step="<?php echo esc_attr( $args['step'] ); ?>" 
			<?php endif; ?>

			<?php if ( ($args['min'] ?? '') !== '' ) : ?>
				min="<?php echo esc_attr( $args['min'] ); ?>" 
			<?php endif; ?>

			<?php if ( ($args['max'] ?? '') !== '' ) : ?>
				max="<?php echo esc_attr( $args['max'] ); ?>" 
			<?php endif; ?>
			
			<?php if ( ($args['required'] ?? '') !== '' ) : ?>
				required
			<?php endif; ?>
		/>

		<?php if ( ($args['description'] ?? '') !== '' ) : ?>
			<span class="description"><?php echo esc_html( $args['description'] ); ?></span>
		<?php endif; ?>
	</label>
</fieldset>
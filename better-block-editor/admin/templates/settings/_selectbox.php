<?php defined( 'ABSPATH' ) || exit; ?>

<fieldset>
		<label for="<?php echo esc_attr( $args['identifier'] ); ?>">
			<select 
				id="<?php echo esc_attr( $args['identifier'] ); ?>" 
				name="<?php echo esc_attr( $args['identifier'] ); ?>" 
				<?php if ( ($args['required'] ?? '') !== '' ) : ?>
					required
				<?php endif; ?>
			>
				<?php foreach ( $args['options'] as $key => $label ) : ?>
					<option 
						value="<?php echo esc_attr( $key ); ?>" 
						<?php selected( $key, $args['value'] ); ?>
					>
						<?php echo esc_html( $label ); ?>
					</option>
				<?php endforeach; ?> 
			</select>

			<?php if ( ($args['description'] ?? '') !== '' ) : ?>
				<span class="description"><?php echo esc_html( $args['description'] ); ?></span>
			<?php endif; ?>
		</label>
</fieldset>

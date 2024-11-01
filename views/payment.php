<?php if ( count( $possible_prices ) > 3 ) { ?>
	<div class="younitedpay-checkout-details">
		<select name="maturity">
			<?php
			foreach ( $possible_maturities as $maturity ) {
				if ( array_key_exists( $maturity, $possible_prices ) ) {
					?>
					<option value="<?php echo esc_attr( $maturity ); ?>">
						<?php
							// translators: Placeholder explanation: Maturity.
							echo esc_html( sprintf( esc_html__( 'Pay in %s x', 'wc-younitedpay-gateway' ), esc_html( $maturity ) ) ); 
						?>
					</option>
					<?php
				}
			}
			?>
		</select>
		<?php
		$is_first = true;
		foreach ( $possible_maturities as $maturity ) {
			if ( array_key_exists( $maturity, $possible_prices ) ) {
				?>

				<?php
				$price = $possible_prices[ $maturity ];
				?>

				<p class="younitedpay_checkout_details_maturity" id="younitedpay_checkout_details_maturity_<?php echo esc_attr( $maturity ); ?>" 
					<?php if ( ! $is_first ) { ?>
					style="display:none" 
					<?php } ?>
				>
					<?php include __DIR__ . '/payment_maturity_details.php'; ?>
				</p>
				<?php
			}
			if ( $is_first ) {
				$is_first = false;
			}
		}
		?>
	</div>
<?php } ?>

<?php if ( count( $possible_prices ) <= 3 ) { ?>
	<ul>
		<?php $check = true; ?>

		<?php
		foreach ( $possible_maturities as $maturity ) {
			if ( array_key_exists( $maturity, $possible_prices ) ) {
				?>
				<?php
				$price = $possible_prices[ $maturity ];
				?>

				<li class="younitedpay-checkout-details 
				<?php
				if ( $check ) {
					?>
					checked<?php } ?>" 
					<?php
					if ( ! $check ) {
						?>
					style="margin-top: 0.3rem!important;" <?php } ?>>
					<?php
					if ( $are_many_offers ) {
							$maturity_input_type = 'radio';
					} else {
						$maturity_input_type = 'hidden';
					}
					?>
					<input required="required" id="payment_method_<?php echo esc_attr( $maturity ); ?>" type="<?php echo esc_attr( $maturity_input_type ); ?>" name="maturity" value="<?php echo esc_attr( $maturity ); ?>" data-order_button_text="" 
						<?php
						if ( $check ) {
							?>
												checked="checked" <?php } ?>>
					<label for="payment_method_<?php echo esc_attr( $maturity ); ?>">
						<?php
							// translators: Placeholder explanation: Maturity.
							echo esc_html( sprintf( esc_html__( 'Pay in %s x', 'wc-younitedpay-gateway' ), esc_html( $maturity ) ) ); 
						?>
					</label>
					<p>
						<?php include __DIR__ . '/payment_maturity_details.php'; ?>
					</p>
				</li>
				<?php
				if ( $check ) {
					$check = false;
				}
				?>
				<?php
			}
		}
		?>
	</ul>
<?php } ?>

<style>
	table {
		background-color: white;
	}

	table th,
	td {
		padding-left: 10px ! important;
	}

	<?php if ( ( ! $api_keys_is_defined && ( 'appearance' == $option || 'behaviour' == $option ) ) ) : ?>
	.button-primary.woocommerce-save-button {
		display: none;
	}
	<?php endif; ?>
</style>

<div class="wrap">
	<h1><?php echo esc_html__( 'YounitedPay Payment Gateway', 'wc-younitedpay-gateway' ); ?></h1>

	<div class="wrap">

		<?php require __DIR__ . '/menu.php'; ?>

		<div class="tab-content">

			<?php
			$fields_allowed_html = array(
				'tbody'    => array(),
				'tr'       => array(
					'valign' => array(),
					'class'  => array(),
				),
				'th'       => array(
					'scope' => array(),
					'class' => array(),
				),
				'td'       => array( 'class' => array() ),
				'legend'   => array( 'class' => array() ),
				'span'     => array( 'class' => array() ),
				'label'    => array(
					'for'   => array(),
					'class' => array(),
				),
				'fieldset' => array(),
				'input'    => array(
					'class'   => array(),
					'name'    => array(),
					'type'    => array(),
					'id'      => array(),
					'value'   => array(),
					'checked' => array(),
				),
				'textarea' => array(
					'rows'  => array(),
					'cols'  => array(),
					'class' => array(),
					'type'  => array(),
					'name'  => array(),
					'id'    => array(),
				),
				'p'        => array( 'class' => array() ),
				'br'       => array(),
				'select'   => array(
					'class' => array(),
					'name'  => array(),
					'id'    => array(),
				),
				'option'   => array(
					'value'    => array(),
					'selected' => array(),
				),
			);
			?>

			<?php wp_nonce_field('younited_admin_settings', 'younited_admin_settings_nonce'); ?>
			<table class="form-table" 
			<?php
			if ( 'behaviour' == $option || 'appearance' == $option ) :
				?>
				style="display:none" <?php endif; ?>>
				<?php echo wp_kses( $settings_fields, $fields_allowed_html ); ?>
			</table>

			<?php if ( ! $api_keys_is_defined && ( 'appearance' == $option || 'behaviour' == $option ) ) { ?>
				<table class="form-table">
					<h2> <?php echo esc_html__( "Please enter you API credentials before changing the module's settings", 'wc-younitedpay-gateway' ); ?></h2>
				</table>
			<?php } ?>

			<table class="form-table" 
				<?php if ( ! $api_keys_is_defined || 'settings' == $option || 'appearance' == $option ) : ?>
				style="display:none" 
				<?php endif; ?>
			>

				<?php echo wp_kses( $behaviour_fields, $fields_allowed_html ); ?>
			</table>

			<table class="form-table" 
			<?php
			if ( ! $api_keys_is_defined || 'settings' == $option || 'behaviour' == $option ) :
				?>
				style="display:none" <?php endif; ?>>
				<?php echo wp_kses( $appearance_fields, $fields_allowed_html ); ?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_younitedpay-gateway_monthly_installments_widget">
							<?php echo esc_html__( 'Monthly installments Widget', 'wc-younitedpay-gateway' ); ?>
						</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php echo esc_html__( 'Monthly installments Widget', 'wc-younitedpay-gateway' ); ?></span>
							</legend>
							<input id="shortcode" class="input-text regular-input " type="text" value="[younitedpay]" disabled>
							<button class="button-primary" onclick="(function(){ const shortcode_element = document.getElementById('shortcode'); shortcode_element.select(); shortcode_element.setSelectionRange(0, 99999); navigator.clipboard.writeText(shortcode_element.value); })();">
								<?php echo esc_html__( 'Copy', 'wc-younitedpay-gateway' ); ?>
							</button>
							<p class="description">
								<?php echo esc_html__( 'Alternatively you can copy this code and paste it in any text on the product page.', 'wc-younitedpay-gateway' ); ?>
							</p>
						</fieldset>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>

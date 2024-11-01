<div class="wrap">
	<h1><?php echo esc_html__( 'YounitedPay Payment Gateway', 'wc-younitedpay-gateway' ); ?></h1>

	<div class="wrap">

		<?php require __DIR__ . '/menu.php'; ?>

		<table class="form-table">
			<tr valign="top">
				<td class="forminp">
					<?php echo esc_html__( 'Younited Pay is an instant credit solution designed for large purchases, from €300 to €50,000.', 'wc-younitedpay-gateway' ); ?> <br>
					<?php echo esc_html__( 'Your customers repay at their own pace between 10 and 84 months.', 'wc-younitedpay-gateway' ); ?>
				</td>
			</tr>

			<tr valign="top">
				<td class="forminp">
					<img style="max-width: 400px;" src="<?php echo esc_url( plugins_url( '../assets/img/' . esc_attr( $img_marketing ), __FILE__ ) ); ?>">
				</td>
			</tr>
		</table>
	</div>
</div>

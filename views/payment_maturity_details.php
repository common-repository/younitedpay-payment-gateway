<span><?php echo esc_html__( 'Financing amount', 'wc-younitedpay-gateway' ); ?> : </span><b><?php echo esc_html( $price['requested_amount_html'] ); ?></b>.<br />
<span><?php echo esc_html__( 'Cost of financing', 'wc-younitedpay-gateway' ); ?> : </span><b><?php echo esc_html( $price['interests_total_amount_html'] ); ?></b>
<span>
<?php
// translators: Placeholder explanation: Annual percentage rate and annual debit rate.
echo esc_html( sprintf(	esc_html__( '(i.e. fixed APR of %1$s, fixed lending rate %2$s).', 'wc-younitedpay-gateway' ),
	esc_html( $price['annual_percentage_rate_html'] ),
	esc_html( $price['annual_debit_rate_html'] )
) );
?>
</span><br />
<span><?php echo esc_html__( 'Total amount due', 'wc-younitedpay-gateway' ); ?> : </span><b><?php echo esc_html( $price['credit_total_amount_html'] ); ?></b>.<br />
<span><?php echo esc_html__( 'Your monthly installments will therefore be', 'wc-younitedpay-gateway' ); ?> </span>
<b>
	<?php
	// translators: Placeholder explanation: Monthly installment amount and maturity in months.
	echo esc_html( sprintf(	esc_html__( '%1$s / month during %2$s months', 'wc-younitedpay-gateway' ),
		esc_html( $price['monthly_installment_amount_html'] ),
		esc_html( $price['maturity_in_months'] )
	) );
	?>
</b><br /><br />
<em><?php echo esc_html__( 'A credit commits you and must be repaid. Check your repayment capacity before you commit.', 'wc-younitedpay-gateway' ); ?></em>

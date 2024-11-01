<?php

namespace Younitedpay\WcYounitedpayGateway;

/**
 * Class WcYounitedpayAdminForm {
 */
class WcYounitedpayAdminForm {

	public function __construct() {     }

	public function settings_fields() {
		 $fields           = array();
		$fields['enabled'] = array(
			'title'       => esc_html__( 'Plugin Younitedpay', 'wc-younitedpay-gateway' ),
			'label'       => esc_html__( 'Enable / Disable', 'wc-younitedpay-gateway' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'This enable the YounitedPay gateway which allow to accept payment through YounitedPay.', 'wc-younitedpay-gateway' ),
			'default'     => 'no',

		);

		/*
		//Stand by
		$fields['language_admin'] = [
			'title'       => esc_html__( 'Administrative language', 'wc-younitedpay-gateway' ),
			'type'        => 'select',
			'description' => "",
			'options' => array(
				"" => "Default",
				"fr_FR" => 'Français',
				"es_ES" => 'Español',
				"en_EN" => 'English'
			),
			'default' => get_locale()
		];
		*/

		$fields['title']       = array(
			'title'       => esc_html__( 'Title', 'wc-younitedpay-gateway' ),
			'type'        => 'text',
			'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'wc-younitedpay-gateway' ),
			'default'     => 'Credit Card',
		);
		$fields['description'] = array(
			'title'       => esc_html__( 'Description', 'wc-younitedpay-gateway' ),
			'type'        => 'textarea',
			'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'wc-younitedpay-gateway' ),
			'default'     => '',
		);

		$fields['testmode'] = array(
			'title'       => esc_html__( 'Test mode', 'wc-younitedpay-gateway' ),
			'label'       => esc_html__( 'Enable / Disable', 'wc-younitedpay-gateway' ),
			'type'        => 'checkbox',
			'description' => esc_html__( 'Place the payment gateway in test mode using test API keys.', 'wc-younitedpay-gateway' ),
			'default'     => 'yes',
			'desc_tip'    => true,
		);

		$fields['test_publishable_key'] = array(
			'title' => esc_html__( 'Client ID Test', 'wc-younitedpay-gateway' ),
			'type'  => 'text',
		);
		$fields['test_private_key']     = array(
			'title' => esc_html__( 'Client Secret Test', 'wc-younitedpay-gateway' ),
			'type'  => 'password',
		);

		$fields['test_webhook_key'] = array(
			'title' => esc_html__( 'Webhook Secret Test', 'wc-younitedpay-gateway' ),
			'type'  => 'password',
		);

		$fields['publishable_key'] = array(
			'title' => esc_html__( 'Client ID Production', 'wc-younitedpay-gateway' ),
			'type'  => 'text',
		);

		$fields['private_key'] = array(
			'title' => esc_html__( 'Client Secret Production', 'wc-younitedpay-gateway' ),
			'type'  => 'password',
		);

		$fields['webhook_key'] = array(
			'title' => esc_html__( 'Webhook Secret Production', 'wc-younitedpay-gateway' ),
			'type'  => 'password',
		);

		$fields['whitelist_enable'] = array(
			'title'    => esc_html__( 'Enable Ip Whitelist', 'wc-younitedpay-gateway' ),
			'type'     => 'checkbox',
			'default'  => 'false',
			'desc_tip' => true,
		);

		$fields['whitelist'] = array(
			'title'       => esc_html__( 'Ip Whitelist', 'wc-younitedpay-gateway' ),
			'label'       => esc_html__( 'Ip Whitelist', 'wc-younitedpay-gateway' ),
			'type'        => 'text',
			'description' => esc_html__( 'Separate the different IPs with a comma.', 'wc-younitedpay-gateway' ) . '<br>' .
				esc_html__( "When enable, only the listed IPs will see the module's component on the site", 'wc-younitedpay-gateway' ),
			'default'     => '',
		);

		return $fields;
	}

	public function behaviour_fields( $possible_maturities ) {
		$fields = array();

		$fields['possible_maturities'] = array(
			'title'       => esc_html__( 'Maturities possibles (in months)', 'wc-younitedpay-gateway' ),
			'type'        => 'text',
			'default'     => '10',
			'description' => esc_html__( 'Separate the different numbers with a comma.', 'wc-younitedpay-gateway' ) . ' <br> ' .
				esc_html__( 'To display the new maturities added, save the modifications,', 'wc-younitedpay-gateway' ) . '<br>' .
				esc_html__( 'the new maturities will appear below the existing ones.', 'wc-younitedpay-gateway' ),
		);

		foreach ( $possible_maturities as $maturity ) {
			if ( ! empty( $maturity ) && is_numeric( $maturity ) ) {

				// translators: Placeholder explanation: Maturity.
				$title_min_amount = esc_html( sprintf( esc_html__( 'Maturity %s month - Minimum amount', 'wc-younitedpay-gateway' ), $maturity	) );
				// translators: Placeholder explanation: Maturity.
				$title_max_amount = esc_html( sprintf( esc_html__( 'Maturity %s month - Maximum amount', 'wc-younitedpay-gateway' ), $maturity ) );

				$fields[ 'min_amount_' . $maturity ] = array(
					'title'   => $title_min_amount,
					'type'    => 'number',
					'default' => '0',
				);
				$fields[ 'max_amount_' . $maturity ] = array(
					'title'   => $title_max_amount,
					'type'    => 'number',
					'default' => '0',
				);
			}
		}

		return $fields;
	}

	public function appearance_fields() {
		$fields = array();

		$fields['logo_color'] = array(
			'title'       => esc_html__( 'Logo', 'wc-younitedpay-gateway' ),
			'type'        => 'select',
			'description' => esc_html__( 'Logo color', 'wc-younitedpay-gateway' ),
			'options'     => array(
				'black' => esc_html__( 'Black logo', 'wc-younitedpay-gateway' ),
				'white' => esc_html__( 'White logo', 'wc-younitedpay-gateway' ),
			),
			'default'     => 'black',
		);

		$fields['monthly_installments_enable'] = array(
			'title'   => esc_html__( 'Monthly installments', 'wc-younitedpay-gateway' ),
			'label'   => esc_html__( 'Enable / Disable', 'wc-younitedpay-gateway' ),
			'type'    => 'checkbox',
			'default' => 'no',
		);

		$options_product_hooks = array(
			'woocommerce_before_single_product',
			'woocommerce_before_single_product_summary',
			'woocommerce_single_product_summary',
			'woocommerce_before_add_to_cart_form',
			'woocommerce_before_variations_form',
			'woocommerce_before_add_to_cart_button',
			'woocommerce_before_single_variation',
			'woocommerce_single_variation',
			'woocommerce_before_add_to_cart_quantity',
			'woocommerce_after_single_variation',
			'woocommerce_after_add_to_cart_button',
			'woocommerce_after_variations_form',
			'woocommerce_after_add_to_cart_form',
			'woocommerce_product_meta_start',
			'woocommerce_product_meta_end',
			'woocommerce_share',
			'woocommerce_after_single_product_summary',
			'woocommerce_after_single_productbest_price_on_product_page',
			'woocommerce_after_add_to_cart_quantity',
		);

		$description_monthly_installments_product_hook =
			esc_html__( 'Theses values are located registered by your current theme, you can choose any of them to place the widget where it looks the best.', 'wc-younitedpay-gateway' )
			. '<br><a href="https://www.businessbloomer.com/woocommerce-visual-hook-guide-single-product-page/" target="_blank">' .
			esc_html__( 'Click on this link for more informations', 'wc-younitedpay-gateway' )
			. '</a>';

		$fields['monthly_installments_product_hook'] = array(
			'title'       => esc_html__( 'Monthly installments in product page', 'wc-younitedpay-gateway' ),
			'type'        => 'select',
			'description' => $description_monthly_installments_product_hook,
			'options'     => array_merge( array( '' => esc_html__( 'Nothing', 'wc-younitedpay-gateway' ) ), array_combine( $options_product_hooks, $options_product_hooks ) ),
			'default'     => '',
		);

		/*
		$options_cart_hooks = array(
			'woocommerce_before_cart_table',
			'woocommerce_before_cart',
			'woocommerce_before_cart_contents',
			'woocommerce_cart_contents',
			'woocommerce_cart_coupon',
			'woocommerce_after_cart_contents',
			'woocommerce_after_cart_table',
			'woocommerce_cart_collaterals',
			'woocommerce_before_cart_totals',
			'woocommerce_cart_totals_before_shipping',
			'woocommerce_before_shipping_calculator',
			'woocommerce_after_shipping_calculator',
			'woocommerce_cart_totals_after_shipping',
			'woocommerce_cart_totals_before_order_total',
			'woocommerce_cart_totals_after_order_total',
			'woocommerce_proceed_to_checkout',
			'woocommerce_after_cart_totals',
			'woocommerce_after_cart'
		);

		$description_monthly_installments_cart_hook =
		esc_html__( 'Theses values are located registered by your current theme, you can choose any of them to place the widget where it looks the best.', 'wc-younitedpay-gateway' )
		.'<br><a href="https://www.businessbloomer.com/woocommerce-visual-hook-guide-cart-page/" target="_blank">'.
		esc_html__( 'Click on this link for more informations', 'wc-younitedpay-gateway')
		."</a>";

		$fields['monthly_installments_cart_hook'] = [
			'title'       => esc_html__( 'Monthly installments in cart page', 'wc-younitedpay-gateway' ),
			'type'        => 'select',
			'description' => $description_monthly_installments_cart_hook,
			'options' => array_merge( array("" => esc_html__('Nothing', 'wc-younitedpay-gateway')), array_combine($options_cart_hooks,$options_cart_hooks)),
			'default' => ''
		];
		*/

		return $fields;
	}



	public function form_fields( $possible_maturities ) {
		return array_merge(
			$this->settings_fields(),
			$this->behaviour_fields( $possible_maturities ),
			$this->appearance_fields()
		);
	}
}

<?php

namespace Younitedpay\WcYounitedpayGateway;

/**
 * Class WcYounitedpayPage {
 */
class WcYounitedpayPage {


	public function __construct() {
		 add_action( 'admin_menu', array( $this, 'register_submenu_page_settings' ), 99 );
	}

	public function register_submenu_page_settings() {
		add_submenu_page(
			'woocommerce',
			'Younited Pay Gateway',
			'Younited Pay Gateway',
			'manage_options',
			'younitedpay_settings',
			array( $this, 'younitedpay_settings' )
		);
	}

	public function younitedpay_settings() {

		$default_option = 'home';
		$option         = isset( $_GET['option'] ) ? sanitize_text_field( $_GET['option'] ) : $default_option;
		if ( 'support' != $option && 'faq' != $option ) {
			$option == $default_option;
		}

		$args           = array();
		$args['option'] = $option;

		$customer_id = get_option( 'woocommerce_younitedpay-gateway_customerid' );
		if ( empty( $customer_id ) ) {
			$customer_id = uniqid();
			update_option( 'woocommerce_younitedpay-gateway_customerid', $customer_id );
		}

		if ( 'support' == $option ) {
			$args['sok_lang']        = get_locale();
			$args['sok_config']      = base64_encode( json_encode( get_option( 'woocommerce_younitedpay-gateway_settings' ) ) );
			$args['sok_project']     = base64_encode( 'wc-younitedpay-gateway' );
			$args['sok_log']         = base64_encode( WcYounitedpayLogger::getContent() );
			$args['sok_customer_id'] = $customer_id;
			$args['sok_hmac']        = hash_hmac( 'sha256', 'b3b621a0-0eu9-45ec-b700-677672c8c752', $args['sok_customer_id'] . '|' . $args['sok_lang'] . '|' . $args['sok_project'] . '|' . get_site_url() );
		}

		if ( 'faq' == $option ) {
			$args['faq_array'] = WcYounitedpayFaq::get_list();
		}

		if ( 'home' == $option ) {
			$args['img_marketing'] = 'image_marketing_' . ( 'es_ES' == get_locale() ? 'es' : 'fr' ) . '.png';
		}

		// Render Home, Faq or Support View
		WcYounitedpayUtils::render( $option, $args );
	}
}

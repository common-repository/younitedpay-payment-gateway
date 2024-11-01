<?php

/**
 * Plugin Name: YounitedPay Payment Gateway
 * Plugin URI: https://younited.com/
 * Description: YounitedPay Payment Gateway for WooCommerce
 * Author: Younited
 * Author URI: https://younited.com/
 * Version: 1.7.4
 * Requires at least: 6.0
 * Tested up to: 6.6
 * Text Domain: wc-younitedpay-gateway
 * Domain Path: /languages
 */

use Younitedpay\WcYounitedpayGateway\WcYounitedpayUtils;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayPage;

//si on est pas dans wordpress => exit
if (!defined('ABSPATH')) {
	exit;
}

define('WC_YOUNITEDPAY_PLUGIN_DIRVERSION', '1.7.4' );
define('WC_YOUNITEDPAY_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_YOUNITEDPAY_GATEWAY_CLASS', 'WcYounitedpayGateway');
require WC_YOUNITEDPAY_PLUGIN_DIR . 'vendor/autoload.php';

/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
add_filter('woocommerce_payment_gateways', 'wc_younitedpay_add_gateway');
function wc_younitedpay_add_gateway( $gateways ) {
	$gateways[] = WC_YOUNITEDPAY_GATEWAY_CLASS;
	return $gateways;
}

add_action('plugins_loaded', 'wc_younitedpay_add_plugin');
function wc_younitedpay_add_plugin() {

	if (!class_exists('WC_Payment_Gateway')) {
		return;
	}

	require_once plugin_dir_path(__FILE__) . 'src/' . WC_YOUNITEDPAY_GATEWAY_CLASS . '.php';

	//initialise le module pour ajouter les hooks sur la page produit et la page commande ( mode admin )
	new WcYounitedpayGateway(false);
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature
*/
function declare_cart_checkout_blocks_compatibility() {
    // Check if the required class exists
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility for 'cart_checkout_blocks'
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('cart_checkout_blocks', __FILE__, true);
    }
}
// Hook the custom function to the 'before_woocommerce_init' action
add_action('before_woocommerce_init', 'declare_cart_checkout_blocks_compatibility');

/**
 * Custom function to register a payment method type

 */
function register_younitedpay_blocks() {
    // Check if the required class exists
    if ( ! class_exists( 'Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType' ) ) {
        return;
    }

    // Include the custom Blocks Checkout class
	require_once plugin_dir_path(__FILE__) . 'src/WcYounitedpayBlocks.php';

    // Hook the registration function to the 'woocommerce_blocks_payment_method_type_registration' action
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            // Register an instance of WC_Phonepe_Blocks
            $payment_method_registry->register( new WcYounitedpayBlocks );
        }
    );
}

// Hook the custom function to the 'woocommerce_blocks_loaded' action
add_action( 'woocommerce_blocks_loaded', 'register_younitedpay_blocks' );

//langue du module
WcYounitedpayUtils::load_textdomain();
new WcYounitedpayPage();

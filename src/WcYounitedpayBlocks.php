<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class WcYounitedpayBlocks extends AbstractPaymentMethodType {

    private $gateway;
    protected $name = 'younitedpay-gateway';// your payment gateway name

    public function initialize() {
        $this->settings = get_option( 'woocommerce_'.$this->name.'_settings', [] );
        $this->gateway = new WcYounitedpayGateway(true);
    }

    public function is_active() {
        return $this->gateway->is_available();
    }

    public function get_payment_method_script_handles() {

        wp_register_script(
            $this->name.'-blocks-integration',
            plugin_dir_url(__FILE__) . '../assets/js/younitedpay_blocks_checkout.js',
            [
                'wc-blocks-registry',
                'wc-settings',
                'wp-element',
                'wp-html-entities',
                'wp-i18n',
            ],
            null,
            true
        );
       
		/*if( function_exists( 'wp_set_script_translations' ) ) {            
            wp_set_script_translations( 'wc-younitedpay-blocks-integration', 'wc-younitedpay', SGPPY_PLUGIN_PATH. 'languages/' );
        }*/

        return [ $this->name.'-blocks-integration' ];
    }

    public function get_payment_method_data() {

		ob_start();
		$this->gateway->payment_fields();
		$options = ob_get_clean();

        return [
            'title' => $this->gateway->title,
            'description' => $this->gateway->description,
			'options' => $options
        ];
    }

}
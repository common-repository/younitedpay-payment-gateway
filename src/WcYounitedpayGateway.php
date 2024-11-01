<?php

use WooCommerce\PayPalCommerce\WcGateway\Exception\GatewayGenericException;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayLogger;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayAdminForm;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayApi;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayUtils;

class WcYounitedpayGateway extends WC_Payment_Gateway
{

	/**
	 * Maturities.
	 */
	private $possible_maturities;

	/**
	 * Maturities.
	 */
	private $WcYounitedpayApi;

	/**
	 * Langue module
	 */
	private $lang;

	/**
	 * Préfixe téléphone
	 */
	private $pre_phone;

	/**
	 * Préfixe téléphone
	 */
	private $logo_filename;

	/**
	 * Webhook secure key
	 */
	private $webhook_key;

	/**
	 * Class constructor, more about it in Step 3
	 */
	public function __construct($payment_mode = true)
	{
		// Load the settings.
		$this->id = 'younitedpay-gateway'; // payment gateway plugin ID

		$this->lang		 = WcYounitedpayUtils::get_lang();
		$this->pre_phone = WcYounitedpayUtils::get_phone_prefixe();

		if ('white' == $this->get_option('logo_color')) {
			$this->logo_filename = 'logo-youpay-white.svg';
		} else {
			$this->logo_filename = 'logo-youpay-black.svg';
		}
		$this->icon               = plugins_url('../assets/img/' . $this->logo_filename, __FILE__); // URL of the icon that will be displayed on checkout page near your gateway name
		$this->has_fields         = true; // in case you need a custom credit card form
		$this->method_title       = 'YounitedPay';
		$this->method_description = esc_html__('Offer your customers to pay up to 36 times', 'wc-younitedpay-gateway');


		// gateways can support subscriptions, refunds, saved payment methods,
		// but in this tutorial we begin with simple payments
		$this->supports = array(
			'products',
		);

		// Set settings
		$this->title       = $this->get_option('title');
		$this->description = $this->get_option('description');


		$this->possible_maturities = explode(',', str_replace(' ', '', $this->get_option('possible_maturities')));

		$sandbox                = 'yes' === $this->get_option('testmode');

		$this->WcYounitedpayApi = new WcYounitedpayApi(
			$sandbox,
			$sandbox ? $this->get_option('test_private_key') : $this->get_option('private_key'),
			$sandbox ? $this->get_option('test_publishable_key') : $this->get_option('publishable_key'),
			$this->pre_phone
		);

		$this->enabled     = $this->plugin_is_enabled();
		$this->webhook_key = $sandbox ? $this->get_option('test_webhook_key') : $this->get_option('webhook_key');

		// Method with all the options fields
		$this->init_form_fields();
		$this->init_settings();

		if (false === $payment_mode) {
			$product_page_hook = $this->get_option('monthly_installments_product_hook');
			if (!empty($product_page_hook)) {
				add_action($product_page_hook, array($this, 'render_best_price'));
			}

			add_shortcode('younitedpay', array($this, 'render_best_price'));
			add_action('wp_ajax_nopriv_fetch_shortcode_younitedpay', array($this, 'fetch_shortcode_younitedpay'));
			add_action('wp_ajax_fetch_shortcode_younitedpay', array($this, 'fetch_shortcode_younitedpay'));

			add_action('woocommerce_order_partially_refunded', array($this, 'order_partially_refunded'), 10, 2);
			add_action('admin_footer', array($this, 'order_scripts'));
			add_filter('wc_order_statuses', array($this, 'order_status_filter'));
			add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 3);
			add_action('admin_notices', array($this, 'order_contract_reference_notice'));
		} else {
			// Webhooks - Url must be /wc-api/younited-pay-success for woocommerce_api_younited-pay-success webhook
			add_action('woocommerce_api_younited-pay-success', array($this, 'webhook_younitedpay'));
			add_action('woocommerce_api_younited-pay-fail', array($this, 'webhook_younitedpay'));
			add_action('woocommerce_api_younited-pay-canceled', array($this, 'webhook_younitedpay'));
			add_action('woocommerce_api_younited-pay-withdrawn', array($this, 'webhook_younitedpay'));

			// Action hook to saves the settings
			if (is_admin()) {
				add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
			}

			// We need custom JavaScript and CSS
			add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

			/*
			$cart_page_hook = $this->get_option('monthly_installments_cart_hook');
			if( ! empty($cart_page_hook)){
				add_action($cart_page_hook, array( $this, 'render_best_price' ));
			}
			*/
		}
	}

	public function plugin_is_enabled()
	{
		global $wp;

		$is_enabled = $this->get_option('enabled');

		/*
		if ($is_enabled == 'yes' && !empty($wp)) {
			if (is_checkout()) {
				return $this->is_payment_visible() ? 'yes' : 'no';
			}
		}*/

		return 'yes' === $is_enabled ? 'yes' : 'no';
	}

	/**
	 * Validate max_amount field
	 *
	 * @see validate_settings_fields()
	 */
	public function validate_possible_maturities_field($key)
	{
		if (!isset($_POST['younited_admin_settings_nonce']) || !check_admin_referer('younited_admin_settings', 'younited_admin_settings_nonce')) {
			$this->add_error('Error CSRF');
		}

		if (empty($_POST[$this->plugin_id . $this->id . '_' . $key])) {
			return '';
		};
		$value = sanitize_text_field($_POST[$this->plugin_id . $this->id . '_' . $key]);

		// Contrôle champ échénces multiple
		$possible_maturities       = explode(',', str_replace(' ', '', $value));
		$erreur_format             = false;
		$possible_maturities_valid = array();
		foreach ($possible_maturities as $maturity) {
			if (is_numeric($maturity) && (0 < $maturity) && (0 == ($maturity % 2))) {
				$possible_maturities_valid[] = $maturity;
			} else {
				$erreur_format = true;
			}
		}
		sort($possible_maturities_valid);
		if ($erreur_format) {
			$this->add_error(esc_html__('Multiples maturities : a maturity must be a number and even. Example : 4,8,12,24,36', 'wc-younitedpay-gateway'));
		}

		// Contrôle champ min / max de chaque échéance valide
		foreach ($possible_maturities_valid as $maturity) {

			// translators: Placeholder explanation: Maturity.
			$mes_err_pre = esc_html(sprintf(esc_html__('Maturity %s month : ', 'wc-younitedpay-gateway'), $maturity));

			if (isset($_POST[$this->plugin_id . $this->id . '_' . "min_amount_$maturity"])) {
				$min_amount = sanitize_text_field($_POST[$this->plugin_id . $this->id . '_' . "min_amount_$maturity"]);
			} else {
				$min_amount = $this->get_option("min_amount_$maturity");
			}

			if (isset($_POST[$this->plugin_id . $this->id . '_' . "max_amount_$maturity"])) {
				$max_amount = sanitize_text_field($_POST[$this->plugin_id . $this->id . '_' . "max_amount_$maturity"]);
			} else {
				$max_amount = $this->get_option("max_amount_$maturity");
			}

			// Il faut que le minimum et maximum soit défini pour activer l'échéance
			if (empty($min_amount) || empty($max_amount)) {
				$this->add_error(
					$mes_err_pre .
						esc_html__('The minimum amount and maximum amount must be defined to activate the maturity.', 'wc-younitedpay-gateway')
				);
			}

			if (!empty($min_amount)) {
				if (!is_numeric($min_amount)) {
					$this->add_error(
						$mes_err_pre .
							esc_html__('Minimum amount must be numeric.', 'wc-younitedpay-gateway')
					);
				} elseif ($min_amount < 0) {
					$this->add_error(
						$mes_err_pre .
							esc_html__('The minimum amount must be greater than or equal to 0.', 'wc-younitedpay-gateway')
					);
				}
			}

			if (!empty($max_amount)) {
				if (!is_numeric($max_amount)) {
					$this->add_error(
						$mes_err_pre .
							esc_html__('Maximum amount must be numeric.', 'wc-younitedpay-gateway')
					);
				} elseif ($max_amount <= $min_amount) {
					$this->add_error(
						$mes_err_pre .
							esc_html__('The maximum amount must be greater than the minimum amount.', 'wc-younitedpay-gateway')
					);
				}
			}
		}

		// valeur valide
		return implode(',', $possible_maturities_valid);
	}

	/**
	 * Test Api keys
	 */
	private function check_api_keys()
	{
		if (!$this->enabled) {
			return;
		}

		if ($this->WcYounitedpayApi->is_sandbox()) {
			$env = __('Test mode', 'wc-younitedpay-gateway') . ' -';
		} else {
			$env = '';
		}

		$check_api_keys_msg = '';
		$check_api_keys_msg_type = 'notice-error';
		if ($this->WcYounitedpayApi->api_keys_is_defined()) {
			$status_code = $this->WcYounitedpayApi->get_maturities();
			if ($status_code === 200) {
				$check_api_keys_msg_type = 'notice-success';
				$check_api_keys_msg = __('The API connection is successful. Please proceed to the configurations of maturities you want to offer to your clients.', 'wc-younitedpay-gateway');
			} else if ($status_code === 401) {
				$check_api_keys_msg = __('Connection was not successful. Your API credentials are incorrect, please check them again on the Younited Pay back-office before retrying.', 'wc-younitedpay-gateway');
			} else {
				$check_api_keys_msg = __('Your account has a configuration issue. Please contact your account manager at Younited.', 'wc-younitedpay-gateway');
			}
		} else {
			$check_api_keys_msg_type = 'notice-warning';
			$check_api_keys_msg = __('Your client id and client secret are not recognised', 'wc-younitedpay-gateway');
		}

		if ($check_api_keys_msg != "") {
			echo "<div class='notice $check_api_keys_msg_type is-dismissible'><p>$env $check_api_keys_msg</p></div>";
		}
	}

	/**
	 * View Admin
	 */
	public function admin_options()
	{

		//Test si les clés d'api sont corrects
		$this->check_api_keys();

		$default_option = 'settings';
		$option         = isset($_GET['option']) ? sanitize_text_field($_GET['option']) : $default_option;
		if ('behaviour' != $option && 'appearance' != $option && 'faq' != $option) {
			$option == $default_option;
		}

		$admin_form = new WcYounitedpayAdminForm();
		WcYounitedpayUtils::render(
			'config',
			array(
				'settings_fields'     => $this->generate_settings_html($admin_form->settings_fields(), false),
				'behaviour_fields'    => $this->generate_settings_html($admin_form->behaviour_fields($this->possible_maturities), false),
				'appearance_fields'   => $this->generate_settings_html($admin_form->appearance_fields(), false),
				'option'              => $option,
				'api_keys_is_defined' => $this->WcYounitedpayApi->api_keys_is_defined(),
			)
		);
	}

	/**
	 * Submit Form Admin
	 */
	public function process_admin_options()
	{
		$this->init_settings();
		$post_data = $this->get_post_data();

		foreach ($this->get_form_fields() as $key => $field) {
			if ('title' !== $this->get_field_type($field)) {
				try {
					$this->settings[$key] = $this->get_field_value($key, $field, $post_data);
				} catch (Exception $e) {
					$this->add_error($e->getMessage());
				}
			}
		}

		if (count($this->errors) > 0) {
			$this->display_errors();
		}

		$option_key = $this->get_option_key();
		do_action('woocommerce_update_option', array('id' => $option_key));
		return update_option($option_key, apply_filters('woocommerce_settings_api_sanitized_fields_' . $this->id, $this->settings), 'yes');
	}

	public function maturities_enabled($price)
	{
		if (empty($this->possible_maturities)) {
			return array();
		}

		$maturities_enabled = array();

		foreach ($this->possible_maturities as $maturity) {
			$min_amount = $this->get_option("min_amount_$maturity");
			$max_amount = $this->get_option("max_amount_$maturity");

			if (
				(!empty($min_amount) && is_numeric($min_amount) && $min_amount <= $price) &&
				(!empty($max_amount) && is_numeric($max_amount) && $max_amount >= $price)
			) {
				$maturities_enabled[] = $maturity;
			}
		}

		return $maturities_enabled;
	}

	public function has_maturities_enabled($price)
	{
		return count($this->maturities_enabled($price)) > 0;
	}

	/**
	 * Plugin bestprice or payment visible
	 */
	public function is_module_visible($amount)
	{
		if ('no' === $this->enabled) {
			return false;
		}

		$ip = WcYounitedpayUtils::get_ip();

		/* WHITELIST */
		$whitelist_enable = $this->get_option('whitelist_enable');
		$whitelist        = explode(',', str_replace(' ', '', $this->get_option('whitelist')));
		if ('yes' === $whitelist_enable && (!in_array($ip, $whitelist))) {
			return false;
		}

		return $this->has_maturities_enabled($amount);
	}

	/**
	 * Fetch shortcode
	 */
	public function fetch_shortcode_younitedpay()
	{
		if (!isset($_POST['fetch_shortcode_younitedpay_nonce']) || !check_ajax_referer('fetch_shortcode_younitedpay', 'fetch_shortcode_younitedpay_nonce')) {
			wp_send_json_success('');
			return;
		}

		if (isset($_POST['price'])) {
			$price_att = sanitize_text_field($_POST['price']);
			if (is_numeric($price_att) && filter_var($price_att, FILTER_VALIDATE_FLOAT) !== false) {
				ob_start();
				do_shortcode("[younitedpay price='$price_att' ajax='true']");
				wp_send_json_success(ob_get_clean());
			} else {
				wp_send_json_success('');
			}
		}
	}

	/**
	 * Return best price view
	 */
	public function render_best_price($atts = array(), $content = null, $tag = '')
	{
		$atts = array_change_key_case((array) $atts, CASE_LOWER);
		// override default attributes with user attributes
		$wporg_atts = shortcode_atts(
			array(
				'ajax'  => false,
				'price' => null,
			),
			$atts,
			$tag
		);

		$price = esc_html($wporg_atts['price']);
		$ajax  = esc_html($wporg_atts['ajax']);

		$visible = true;

		if (null != $price) {
			// with defined price
			$amount = $price;
		} elseif (is_cart() && !is_null(WC()->cart)) {
			// Is view cart
			$amount = WC()->cart->total;
		} elseif (is_product()) {
			// is view product
			global $product;
			if (is_null($product)) {
				$visible = false;
			} else {
				$amount = $product->get_price();
			}
		} else {
			$visible = false;
		}

		if ($visible && ('no' === $this->get_option('monthly_installments_enable'))) {
			$visible = false;
		}

		if ($visible && false === $this->is_module_visible($amount)) {
			$visible = false;
		}

		$possible_prices = array();
		$default_price   = null;
		if ($visible) {
			$possible_prices = $this->get_possible_prices($amount);
			if (empty($possible_prices)) {
				$visible = false;
			} else {
				$maturity      = array_key_last($possible_prices);
				$default_price = $possible_prices[$maturity];
			}
		}

		// L'html est généré que si le module est visible ou alors par requete ajax
		// Si ajax, on doit toujours retourner un contenu même si il ne contient pas d'informations.
		if ($visible || $ajax) {
			WcYounitedpayLogger::log('render_best_price(' . $amount . ')');
			WcYounitedpayUtils::render(
				'bestprice',
				array(
					'default_price'   => $default_price,
					'possible_prices' => $possible_prices,
					'logo'            => $this->logo_filename,
					'visible'         => $visible,
					'ajax'            => $ajax,
					'lang'            => $this->lang,
				)
			);
		}

		// On ajoute les scripts que si le module est visible et que ce n'est pas une requête ajax
		// Car si c'est une requete ajax, les scripts sont déjà présents
		if ($visible && !$ajax) {
			wp_enqueue_script('younitedpay_bestprice_js', plugins_url('../assets/js/younitedpay_bestprice.js', __FILE__), array('jquery'), WC_YOUNITEDPAY_PLUGIN_DIRVERSION, true);
			wp_register_style('younitedpay_bestprice', plugins_url('../assets/css/younitedpay_bestprice.css', __FILE__), array(), WC_YOUNITEDPAY_PLUGIN_DIRVERSION);
			wp_enqueue_style('younitedpay_bestprice');
		}
	}

	/**
	 * Plugin options that will be displayed in administration page
	 */
	public function init_form_fields()
	{
		$admin_form        = new WcYounitedpayAdminForm();
		$this->form_fields = $admin_form->form_fields($this->possible_maturities);
	}

	public function is_payment_visible()
	{
		if (is_null(WC()->cart)) {
			return false;
		}

		if (!$this->is_module_visible(WC()->cart->total)) {
			return false;
		}

		// no reason to enqueue JavaScript if API keys are not set
		if (false === $this->WcYounitedpayApi->api_keys_is_defined()) {
			return false;
		}

		// do not work with card detailes without SSL unless your website is in a test mode
		if (!$this->WcYounitedpayApi->is_sandbox() && !is_ssl()) {
			return false;
		}

		return true;
	}

	/**
	 * You will need it if you want your custom credit card form, Step 4 is about it
	 */
	public function payment_fields()
	{
		if (!$this->is_payment_visible()) {
			echo '<div style="padding:5px">' . esc_html__('This payment method is not available at this amount.', 'wc-younitedpay-gateway') . '</div>';
			return;
		}

		$possible_prices = $this->get_possible_prices(WC()->cart->total);
		WcYounitedpayLogger::log('payment_fields(' . json_encode(WC()->cart->total) . ') - $possible_prices : ' . json_encode($possible_prices));

		if ( empty($this->possible_maturities) || count($this->possible_maturities) == 0) {
			echo '<div style="padding:5px">' . esc_html__('This payment method is not available at this amount.', 'wc-younitedpay-gateway') . '</div>';
			return;
		}

		$are_many_offers = (count($this->possible_maturities) > 1);

		echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
		do_action('woocommerce_credit_card_form_start', $this->id);

		WcYounitedpayUtils::render(
			'payment',
			array(
				'possible_maturities' => $this->possible_maturities,
				'possible_prices'     => $possible_prices,
				'are_many_offers'     => $are_many_offers,
			)
		);

		do_action('woocommerce_credit_card_form_end', $this->id);
		echo '<div class="clear"></div></fieldset>';
	}


	/*
	* Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
	*/
	public function payment_scripts()
	{
		wp_register_style('younitedpay_checkout', plugins_url('../assets/css/younitedpay_checkout.css', __FILE__), array(), WC_YOUNITEDPAY_PLUGIN_DIRVERSION);
		wp_enqueue_style('younitedpay_checkout');
		if (!$this->is_payment_visible()) {
			return;
		}
		wp_enqueue_script('younitedpay_js', plugins_url('../assets/js/younitedpay_checkout.js', __FILE__), array('jquery'), WC_YOUNITEDPAY_PLUGIN_DIRVERSION, true);
	}

	/*
	* Get possible prices depending on amount and on $this->possible_maturities
	*/
	public function get_possible_prices($amount)
	{

		$possible_prices = array();

		$best_price = $this->WcYounitedpayApi->get_best_price($amount);
		$maturities = $this->maturities_enabled($amount);

		$possible_prices = WcYounitedpayUtils::get_possible_prices($best_price, $maturities);

		WcYounitedpayLogger::log('get_possible_prices(' . $amount . ') - $possible_month : ' . json_encode(array_keys($possible_prices)));

		return $possible_prices;
	}

	/*
	* Fields validation during checkout
	*/

	public function validate_fields()
	{

		//Si c'est blocks, on check de manière classique
		if (!empty($_POST['wc-younitedpay-gateway-blocks']) && $_POST['wc-younitedpay-gateway-blocks'] == true) {
			return parent::validate_fields();
		}

		if (!isset($_POST['woocommerce-process-checkout-nonce']) || !check_ajax_referer('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce')) {
			wc_add_notice('ERROR CSRF', 'error');
			return false;
		}

		if (empty($_POST['billing_first_name'])) {
			wc_add_notice(__('The first name is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}
		if (empty($_POST['billing_last_name'])) {
			wc_add_notice(__('The name is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}
		if (empty($_POST['billing_phone'])) {
			wc_add_notice(__('The phone is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}

		// Allows 00xx/+xx/0 x xx xx xx xx (with or without spaces)
		if (!preg_match('/^(?:(?:\+|00)' . $this->pre_phone . '|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/', sanitize_text_field($_POST['billing_phone']))) {
			wc_add_notice(
				// translators: Placeholder explanation: prefixe of phone.
				esc_html(
					sprintf(
						esc_html__('The phone is invalid (accepted formats : 0601020304 / 00%1$s601020304 / +%2$s601020304)', 'wc-younitedpay-gateway'),
						$this->pre_phone,
						$this->pre_phone
					)
				),
				'error'
			);
			return false;
		}
		if (empty($_POST['billing_email'])) {
			wc_add_notice(__('The mail is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}
		if (empty($_POST['billing_address_1'])) {
			wc_add_notice(__('The address is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}
		if (empty($_POST['billing_postcode'])) {
			wc_add_notice(__('The postal code is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}
		if (empty($_POST['billing_city'])) {
			wc_add_notice(__('The city is required', 'wc-younitedpay-gateway'), 'error');
			return false;
		}

		return true;
	}

	/*
	* We're processing the payments here => Redirect to YounitedPay website if contract initialisation has succeeded
	*/
	public function process_payment($order_id)
	{

		//Si c'est blocks, on check de manière classique
		if (!empty($_POST['wc-younitedpay-gateway-blocks']) && $_POST['wc-younitedpay-gateway-blocks'] == true) {
			$maturity =	$_POST['wc-younitedpay-gateway-maturity'];
		} else {
			if (
				!isset($_POST['woocommerce-process-checkout-nonce']) ||
				!check_ajax_referer('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce')
			) {
				throw new Exception("ERROR CSRF");
			}
			$maturity =	$_POST['maturity'];
		}


		if (empty($maturity)) {
			throw new Exception(
				__('Please select the number of payments you wish to make with YounitedPay', 'wc-younitedpay-gateway')
			);
		}

		$maturity = sanitize_text_field($maturity);
		if (!is_numeric($maturity)) {
			throw new Exception(
				__('Please select the number of payments you wish to make with YounitedPay', 'wc-younitedpay-gateway')
			);
		}

		$redirect_url = $this->WcYounitedpayApi->initialize_contract($order_id, $maturity);
		WcYounitedpayLogger::log('process_payment(' . $order_id . ') - $redirect_url : ' . json_encode($redirect_url));

		if ($redirect_url) {
			return array(
				'result'   => 'success',
				'redirect' => $redirect_url,
			);
		}
	}

	/*
	* Webhook when redirecting from younitedpay website
	*/
	public function webhook_younitedpay()
	{
		if (false === WcYounitedpayUtils::validate_webhook($this->webhook_key)) {
			exit;
		}

		if (empty($_GET['order-id'])) {
			WcYounitedpayLogger::log('$_GET Order id not found');
			exit;
		}

		$order_id = sanitize_text_field($_GET['order-id']);
		WcYounitedpayLogger::log('webhook_younitedpay() - $order_id : ' . json_encode($order_id));

		if (!$order_id) {
			WcYounitedpayLogger::log('Order id not found');
			exit;
		}

		$order = wc_get_order($order_id);

		global $wp;

		$path = parse_url($wp->request, PHP_URL_PATH);
		WcYounitedpayLogger::log('webhook_younitedpay() - $path : ' . json_encode($path));

		$current_status = $order->get_status();
		$new_status     = null;

		$raw_content = json_decode(file_get_contents('php://input'));
		WcYounitedpayLogger::log('webhook_younitedpay() - raw_content : ' . json_encode($raw_content));
		switch (true) {
			case str_contains($path, 'success'):

				$contract_reference_younited = sanitize_text_field($raw_content->{'contractReference'});

				if ($current_status == 'processing') {
					//On a déjà reçu la notification
				} else {
					// Confirm to YounitedPay
					$order->update_meta_data('_younitedpay_contract_reference', $contract_reference_younited);
					$order->save();
					$confirm = $this->WcYounitedpayApi->confirm_contract($order_id);
					if ($confirm) {
						$new_status = 'processing';
					} else {
						// si il y a une erreur on revient en arrière
						$order->update_meta_data('_younitedpay_contract_reference', '');
						$order->save();
					}
				}

				break;
				// Cancel order
			case str_contains($path, 'fail'):
			case str_contains($path, 'canceled'):
				$new_status = 'cancelled';
				break;
			case str_contains($path, 'withdrawn'):
				$new_status = 'refunded';
				break;
			default:
		}

		if (null != $new_status && $current_status != $new_status) {
			WcYounitedpayLogger::log("webhook_younitedpay($order_id) - Update status - $current_status -> $new_status");
			$order->update_meta_data('_younitedpay_status_changed_disable', true);
			$order->update_status($new_status, esc_html__('Status changed by webhook of Younited Pay', 'wc-younitedpay-gateway'));
			$order->save();
		} else {
			WcYounitedpayLogger::log("webhook_younitedpay($order_id) - No change status - $current_status -> $new_status");
		}
	}

	private function is_not_action_edit()
	{
		return (!isset($_GET['action']) || sanitize_text_field($_GET['action']) !== 'edit');
	}

	public function order_scripts()
	{
		if ($this->is_not_action_edit()) {
			return;
		}

		global $post;

		// si ce n'est pas un post, on filtre pas
		if (is_null($post) || 'shop_order' != $post->post_type) {
			return;
		}

		$order = new WC_Order($post->ID);

		// si ce n'est pas une commande woocommerce, on filtre pas
		if (is_null($order)) {
			return;
		}

		// si la commande n'est pas lié à Younitedpay, on filtre pas
		$contract_reference = $this->WcYounitedpayApi->getContractReferenceOfOrder($order);
		if (empty($contract_reference)) {
			return;
		}

?>
		<script>
			jQuery(function() {
				jQuery('#order_status option[value="wc-checkout-draft"').remove()
			});
		</script>
		<?php

		if ('completed' === $order->get_status()) {
			return;
		}

		// On cache le bouton si c'est un statut autre que complété dans le cas où la commande est lié à younitedpay
		?>
		<script>
			jQuery(function() {
				jQuery('#woocommerce-order-items .refund-items').hide();
			});
		</script>
<?php
	}

	public function order_contract_reference_notice()
	{
		if ($this->is_not_action_edit()) {
			return;
		}

		global $post;

		// si ce n'est pas un post, on filtre pas
		if (is_null($post) || 'shop_order' != $post->post_type) {
			return;
		}

		$order = new WC_Order($post->ID);

		// si ce n'est pas une commande woocommerce, on filtre pas
		if (is_null($order)) {
			return;
		}

		// si la commande n'est pas lié à Younitedpay, on filtre pas
		$contract_reference = $this->WcYounitedpayApi->getContractReferenceOfOrder($order);
		if (empty($contract_reference)) {
			return;
		}

		$status = $this->WcYounitedpayApi->get_contract($order->id);

		$notice_error = false;
		if (
			$status &&
			('WITHDRAWN' == $status && 'refunded' != $order->status) ||
			(('CANCELED' == $status || 'REJECTED' == $status) && ('cancelled' != $order->status && 'failed' != $order->status)) ||
			('FINANCED' == $status && 'completed' != $order->status)
		) {
			$notice_error = true;
		}

		$status_text = '';
		if (!empty($status)) {
			// translators: Placeholder explanation: Status.
			$status_text = sprintf(esc_html__(' - Status %s', 'wc-younitedpay-gateway'), esc_html($status));
		}

		echo "<div class='notice " . ($notice_error ? 'notice-error' : 'notice-info') . " is-dismissible'><p>"
			// translators: Placeholder explanation: Contract reference.
			. sprintf(esc_html__('Younited Pay - Contract reference %s', 'wc-younitedpay-gateway'), esc_html($contract_reference))
			. esc_html($status_text)
			. '</p></div>';

		if ($notice_error) {
			echo "<div class='notice notice-error' is-dismissible'><p>" .
				esc_html__('Warning, you must update the status of your order, it is not synchronized with the status of the Younited Pay contract', 'wc-younitedpay-gateway') .
				'</p></div>';
		}
	}

	public function order_status_filter($order_statuses)
	{
		if ($this->is_not_action_edit()) {
			return $order_statuses;
		}

		global $post;
		// si ce n'est pas un post, on filtre pas
		if (is_null($post) || 'shop_order' != $post->post_type) {
			return $order_statuses;
		}
		$order = new WC_Order($post->ID);

		// si ce n'est pas une commande woocommerce, on filtre pas
		if (is_null($order)) {
			return $order_statuses;
		}

		// si la commande n'est pas lié à Younitedpay, on filtre pas
		$contract_reference = $this->WcYounitedpayApi->getContractReferenceOfOrder($order);
		if (empty($contract_reference)) {
			return $order_statuses;
		}

		$order_status = $order->get_status();

		// ces status sont interdits car le contract younited a été créé.
		// La liste des statuts est réduite.
		$order_statuses_keys_prohibited = array('wc-pending', 'wc-on-hold', 'wc-refunded', 'wc-failed');

		$order_statuses_keys_filter = array();
		if ('completed' == $order_status) {
			$order_statuses_keys_filter = array('wc-completed', 'wc-refunded');
		} elseif ('refunded' == $order_status) {
			$order_statuses_keys_filter = array('wc-refunded');
		} elseif ('cancelled' == $order_status) {
			$order_statuses_keys_filter = array('wc-cancelled');

			// Cela permet de générer des statuts supplémentaires qui ont été rajoutés
		} elseif ('processing' == $order_status || !in_array($order_status, $order_statuses_keys_prohibited)) {
			$order_statuses_filter = array();
			foreach ($order_statuses as $status_key => $status) {
				if (!in_array($status_key, $order_statuses_keys_prohibited)) {
					$order_statuses_filter[$status_key] = $status;
				}
			}
			return $order_statuses_filter;
		} else {
			// Normalement on doit pas arriver içi
			// pour les autres status, on laisse le choix
			return $order_statuses;
		}

		$order_statuses_filter = array();
		foreach ($order_statuses as $status_key => $status) {
			if (in_array($status_key, $order_statuses_keys_filter)) {
				$order_statuses_filter[$status_key] = $status;
			}
		}

		return $order_statuses_filter;
	}

	public function order_status_changed($order_id, $old_status, $new_status)
	{
		WcYounitedpayLogger::log("order_status_changed - $order_id - $old_status -> $new_status");
		$order              = wc_get_order($order_id);
		$contract_reference = $this->WcYounitedpayApi->getContractReferenceOfOrder($order);
		if (empty($contract_reference)) {
			return;
		}

		$status_rollback = $order->get_meta('_younitedpay_status_changed_disable');
		if ($status_rollback) {
			WcYounitedpayLogger::log("order_status_changed - status changed disable - $contract_reference - $order_id - $old_status -> $new_status");
			$order->update_meta_data('_younitedpay_status_changed_disable', false);
			$order->save();
			return;
		}

		WcYounitedpayLogger::log("order_status_changed - $contract_reference - $order_id - $old_status -> $new_status");

		// si c'est les mêmes statuts, on déclenche pas d'évènements vers YounitedPay
		if ($old_status == $new_status) {
			return;
		}

		$order_status_valid = true;
		$msg_error          = '';

		$contract_status = $this->WcYounitedpayApi->get_contract($order->id);

		if ('refunded' == $old_status || 'cancelled' == $old_status) {
			// Quand le statut est remboursée ou annulée, on ne peut plus modifier le statut
			$order_status_valid = false;
			$msg_error          = esc_html__('This status is not editable. - ', 'wc-younitedpay-gateway');
		} elseif ('completed' == $old_status) {
			// Quand le statut est complété, on ne peut que rembourser car le contrat younited est activé
			if ('refunded' == $new_status) {
				// Si le statut du contrat est FINANCED, on rembourse
				// Si le statut du contrat est WITHDRAWN, on ne fait rien
				// Sinon on est en erreur
				if ('FINANCED' == $contract_status) {
					$order_status_valid = $this->WcYounitedpayApi->withdraw_contract($order_id);
					if (!$order_status_valid) {
						$msg_error = esc_html__('Error Call Younited Pay Api - Rollback Status - ', 'wc-younitedpay-gateway');
					}
				} elseif ('WITHDRAWN' != $contract_status) {
					$order_status_valid = false;
					$msg_error          = esc_html__('The status of the Younited Pay contract does not allow this modification - ', 'wc-younitedpay-gateway');
				}
			} else {
				$order_status_valid = false;
				// translators: Placeholder explanation: New status.
				$msg_error = esc_html(sprintf(__('The completed status cannot be changed by the %s status - ', 'wc-younitedpay-gateway'), $new_status));
			}
		} else {
			if ('completed' == $new_status) {
				// On tente d'activer le contrat sur le statut est différent de FINANCED
				if ('FINANCED' != $contract_status) {
					$order_status_valid = $this->WcYounitedpayApi->activate_contract($order_id);
				}
			} elseif ('cancelled' == $new_status || 'failed' == $new_status) {
				// Si le statut du contrat est FINANCED, on rembourse
				// Si le statut du contrat est WITHDRAWN, on change le statut en refunded dans woocommence
				// Si le statut du contrat est CANCELED ou REJECTED, on ne fait rien
				// Sinon on tente d'annuler le contrat

				if ('FINANCED' == $contract_status) {
					$order_status_valid = $this->WcYounitedpayApi->withdraw_contract($order_id);
					if ($order_status_valid) {
						$order->update_meta_data('_younitedpay_status_changed_disable', true);
						$order->update_status('refunded', $msg_error);
						$order->save();
						return;
					}
				} elseif ('WITHDRAWN' == $contract_status) {
					$order->update_meta_data('_younitedpay_status_changed_disable', true);
					$order->update_status('refunded', $msg_error);
					$order->save();
					return;
				} elseif ('CANCELED' != $contract_status && 'REJECTED' != $contract_status) {
					$order_status_valid = $this->WcYounitedpayApi->cancel_contract($order_id);
				}
			}

			if (!$order_status_valid && '' == $msg_error) {
				$msg_error = esc_html__('Error Call Younited Pay Api - Rollback Status - ', 'wc-younitedpay-gateway');
			}

			// Si le nouveau statut fait partie de la liste de statuts interdits, on rollback
			if (in_array($new_status, array('pending', 'on-hold', 'refunded', 'failed'))) {
				$order_status_valid = false;
				$msg_error          = esc_html__('This status is not editable. - ', 'wc-younitedpay-gateway');
			}

			// Si c'est un autre statut, on permet la modification
		}

		if (!$order_status_valid) {
			$order->update_meta_data('_younitedpay_status_changed_disable', true);
			// TODO messages en fct de condition status OLD -> status NEW
			$order->update_status($old_status, $msg_error);
			$order->save();
		}
	}

	public function order_partially_refunded($order_id, $refund_id)
	{
		$contract_reference = $this->WcYounitedpayApi->getContractReference($order_id);
		if (empty($contract_reference)) {
			return;
		}

		$order   = wc_get_order($order_id);
		$refunds = $order->get_refunds();

		$refund = null;
		foreach ($refunds as $r) {
			if ($refund_id == $r->id) {
				$refund = $r;
				break;
			}
		}

		if (empty($refund)) {
			WcYounitedpayLogger::log("order_refunded - orderId: $order_id - RefundId: $refund_id - Refund inconnu");
			return;
		}

		WcYounitedpayLogger::log("order_refunded - orderId: $order_id - RefundId: $refund_id - Total refund : " . $refund->get_amount());
		$this->WcYounitedpayApi->withdraw_contract($order_id, $refund->get_amount());
	}
}

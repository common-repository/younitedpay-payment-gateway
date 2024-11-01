<?php

namespace Younitedpay\WcYounitedpayGateway;

use Younitedpay\WcYounitedpayGateway\WcYounitedpayLogger;

/**
 * Class WcYounitedpayUtils {
 */
class WcYounitedpayUtils {

	public function __construct() { }

	public static function render( $name, array $args = array() ) {
		 extract( $args );
		$file = WC_YOUNITEDPAY_PLUGIN_DIR . "views/$name.php";
		if ( in_array( $name, array( 'bestprice', 'config', 'faq', 'home', 'menu', 'payment', 'support' ) ) ) {
			include $file;
		}
	}

	public static function get_lang(){
		$current_lang = get_locale();
		if ( 'fr_FR' == $current_lang ) {
			$lang      = 'fr';
		} elseif ( 'es_ES' == $current_lang ) {
			$lang      = 'es';
		} else {
			$lang      = 'en';
		}
		return $lang;
	}

	public static function get_phone_prefixe(){
		$current_lang = get_locale();
		if ( 'fr_FR' == $current_lang ) {
			$phone_prefixe =  '33';
		} elseif ( 'es_ES' == $current_lang ) {
			$phone_prefixe =  '34';
		} else {
			$phone_prefixe = '33'; // +33 par défaut pour le moment
		}
		return $phone_prefixe;
	}

	public static function get_ip() {
		$ip = '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
		} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}
		return $ip;
	}

	/*
	* Get possible prices depending on amount and on $this->possible_maturities
	*/
	public static function get_possible_prices( $best_price, $maturities ) {
		$possible_prices = array();

		if ( $best_price ) {
			foreach ( $best_price as $price ) {
				if ( in_array( $price['maturityInMonths'], $maturities ) ) {
					$possible_prices[ $price['maturityInMonths'] ] = array(
						'requested_amount'                => $price['requestedAmount'],
						'requested_amount_html'           => ( number_format( $price['requestedAmount'], '2', ',', ' ' ) . '€' ),
						'annual_percentage_rate'          => $price['annualPercentageRate'],
						'annual_percentage_rate_html'     => ( ( $price['annualPercentageRate'] * 100 ) . '%' ),
						'annual_debit_rate'               => $price['annualDebitRate'],
						'annual_debit_rate_html'          => ( ( $price['annualDebitRate'] * 100 ) . '%' ),
						'monthly_installment_amount'      => $price['monthlyInstallmentAmount'],
						'monthly_installment_amount_html' => ( number_format( $price['monthlyInstallmentAmount'], '2', ',', ' ' ) . '€' ),
						'credit_total_amount'             => $price['creditTotalAmount'],
						'credit_total_amount_html'        => ( number_format( $price['creditTotalAmount'], '2', ',', ' ' ) . '€' ),
						'credit_amount_to_fund'           => $price['creditAmountToFund'],
						'credit_amount_to_fund_html'      => ( number_format( $price['creditAmountToFund'], '2', ',', ' ' ) . '€' ),
						'maturity_in_months'              => $price['maturityInMonths'],
						'maturity_in_months_html'         => $price['maturityInMonths'] . ' mois',
						'interests_total_amount'          => $price['interestsTotalAmount'],
						'interests_total_amount_html'     => ( number_format( $price['interestsTotalAmount'], '2', ',', ' ' ) . '€' ),
					);
				}
			}
		}

		return $possible_prices;
	}

	public static function load_textdomain() {
		// Gestion de la langue depuis l'administration
		// En stand by pour le moment
		/*
		if(is_admin()){
		  $settings = get_option("woocommerce_younitedpay-gateway_settings");
			if($settings && !empty($settings['language_admin'])){
				$current_lang = $settings['language_admin'];
			}
		}*/

		$locale_arr = explode( '_', get_locale() );
		if ( count( $locale_arr ) != 2 ) {
			return;
		}

		$pref_lang = $locale_arr[0];
		if ( 'fr' == $pref_lang ) {
			$current_lang = 'fr_FR';
		} elseif ( 'es' == $pref_lang ) {
			$current_lang = 'es_ES';
		} else {
			return;
		}

		load_textdomain( 'wc-younitedpay-gateway', plugin_dir_path( __FILE__ ) . '/../languages/wc-younitedpay-gateway-' . $current_lang . '.mo', $current_lang );
	}

	public static function validate_webhook( $webhook_secure_key ) {
		try {
			if ( empty( $webhook_secure_key ) ) {
				WcYounitedpayLogger::log( __( 'Webhook Secret is not defined', 'wc-younitedpay-gateway' ) );
				return false;
			}

			$header = array_change_key_case( getallheaders() );
			if ( empty( $header['x-yc-datetime'] ) || empty( $header['x-yc-signature-256'] ) ) {
				WcYounitedpayLogger::log( 'Webhook arguments error' );
				return false;
			}

			$request_uri = '';
			if ( isset( $_SERVER['REQUEST_URI'] ) ) {
				$request_uri = esc_url_raw( $_SERVER['REQUEST_URI'] );
			}
			$webhook_url = esc_url_raw( get_site_url() . $request_uri );
			$raw_content = sanitize_text_field( file_get_contents( 'php://input' ) );

			$yc_date      = sanitize_text_field( $header['x-yc-datetime'] );
			$yc_signature = sanitize_text_field( $header['x-yc-signature-256'] );

			$hmac = hash_hmac( 'sha256', "$webhook_url|$raw_content|$yc_date", $webhook_secure_key );

			if ( $hmac != $yc_signature ) {
				WcYounitedpayLogger::log( "Webhook wrong signature - date: $yc_date - url: $webhook_url - raw: $raw_content - sig: $yc_signature - hmac: $hmac" );
				return false;
			} else {
				WcYounitedpayLogger::log( "Webhook correct signature - date: $yc_date - url: $webhook_url - raw: $raw_content - sig: $yc_signature - hmac: $hmac" );
			}
			return true;
		} catch ( \Exception $ex ) {
			WcYounitedpayLogger::log( 'Validate webhook Exception : ' . json_encode( $ex ) );
			return false;
		}
	}
}

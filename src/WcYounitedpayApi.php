<?php

namespace Younitedpay\WcYounitedpayGateway;

use Exception;
use Younitedpay\WcYounitedpayGateway\WcYounitedpayLogger;

/**
 * Class WcYounitedpayApi {
 */
class WcYounitedpayApi {

	/**
	 * Url of Api
	 */
	private $api;

	/**
	 * Sandbox enabled.
	 */
	private $sandbox;

	/**
	 * Private key.
	 */
	private $private_key;

	/**
	 * Publishable key.
	 */
	private $publishable_key;

	/**
	 * Url bearer token.
	 */
	private $bearer;

	/**
	 * Pre phone
	 */
	private $pre_phone;

	/* Urls */
	private $bearer_sandbox = 'https://login.microsoftonline.com/c9536195-ef3b-4703-9c13-924db8e24486/oauth2/v2.0/token';
	private $bearer_prod    = 'https://login.microsoftonline.com/5fe44fa6-b50a-42d9-a006-199bedeb5bb9/oauth2/v2.0/token';
	private $api_sandbox    = 'https://api.sandbox-younited-pay.com';
	private $api_prod       = 'https://api.younited-pay.com';

	public function __construct( $sandbox, $private_key, $publishable_key, $pre_phone ) {

		$this->sandbox         = $sandbox;
		$this->private_key     = $private_key;
		$this->publishable_key = $publishable_key;
		$this->bearer          = $this->sandbox ? $this->bearer_sandbox : $this->bearer_prod;
		$this->api             = $this->sandbox ? $this->api_sandbox : $this->api_prod;
		$this->pre_phone       = $pre_phone;
	}

	/*
	* Get a Bearer Token
	*/
	public function get_token() {
		$token = null;
		// Get token from session is as not expired (minus 60 seconds in case of...)
		if ( isset( $_SESSION['get_token']['expires_at'] ) && time() < ( sanitize_text_field( $_SESSION['get_token']['expires_at'] ) - 60 ) && isset( $_SESSION['get_token']['access_token'] ) ) {
			$token = sanitize_text_field( $_SESSION['get_token']['access_token'] );
			WcYounitedpayLogger::log( 'get_token() - Get token from SESSION' );
		} else {
			// API REQUEST to get a Bearer Token
			$response = wp_remote_post(
				$this->bearer,
				array(
					'body'      => array(
						'grant_type'    => 'client_credentials',
						'client_id'     => sanitize_text_field( $this->publishable_key ),
						'client_secret' => sanitize_text_field( $this->private_key ),
						'scope'         => 'api://younited-pay/.default',
					),
					'sslverify' => is_ssl() ? true : false,
					'headers'   => array(
						'Content-type: application/x-www-form-urlencoded',
					),
				)
			);

			if ( ! is_wp_error( $response ) ) {
				
				WcYounitedpayLogger::log( 'get_token() - $response_code : ' . wp_remote_retrieve_response_code( $response )  );

				$body = json_decode( $response['body'], true );
				if ( isset( $body['access_token'] ) ) {
					$token      = sanitize_text_field( $body['access_token'] );
					$expires_in = sanitize_text_field( $body['expires_in'] );

					$_SESSION['get_token'] = array(
						'expires_in'   => $expires_in,
						'created'      => time(),
						'expires_at'   => ( time() + $expires_in ),
						'access_token' => $token,
					);
				}
			}
			//WcYounitedpayLogger::log( 'get_token() - $response : ' . json_encode( $response ) );
		}

		// WcYounitedpayLogger::log( 'get_token() - $token : ' . json_encode( $token ) );

		return $token;
	}

	/*
	* Get Contract
	*/
	public function get_contract( $order_id ) {
		$contract_reference = $this->getContractReference( $order_id );
		if ( ! $contract_reference ) {
			return false;
		}

		WcYounitedpayLogger::log( 'get_contract(' . $order_id . ') - $order->_younitedpay_contract_reference : ' . $contract_reference );

		// YOUNITEDPAY API REQUEST -> Confirm Contract
		$response = wp_remote_request(
			$this->api . '/api/1.0/Contract/' . $contract_reference,
			array(
				'method'  => 'GET',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 200 ) {
				$body = json_decode( $response['body'], true );
				WcYounitedpayLogger::log( 'get_contract(' . $order_id . ') - $order->_younitedpay_contract_reference : ' . $contract_reference . ' - status : ' . $body['status'] );
				return $body['status'];
			} else {
				WcYounitedpayLogger::log( 'get_contract(' . $order_id . ') - $response : ' . json_encode( $response ) );
			}
		}
		return false;
	}

	public function get_maturities(){
		$response = wp_remote_request(
			$this->api . '/api/1.0/Maturities',
			array(
				'method'  => 'GET',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(),
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$response_code =  wp_remote_retrieve_response_code($response);
			WcYounitedpayLogger::log( 'get_maturities - response : ' . json_encode($response) );
			WcYounitedpayLogger::log( 'get_maturities - response_code : ' . $response_code );
	
			return $response_code;
		}
		return 500;
	}

	/*
	* Get Best Price
	*/
	public function get_best_price( $price ) {

		if ( is_null( $price ) || $price <= 0 ) {
			return false;
		}

		$data = array( 'borrowedAmount' => number_format( $price, 2, '.', '' ) );
		WcYounitedpayLogger::log( 'get_best_price(' . $price . ') - $data : ' . json_encode( $data ) );

		// YOUNITEDPAY API REQUEST -> get BestPrice
		$response = wp_remote_post(
			$this->api . '/api/1.0/BestPrice',
			array(
				'body'        => wp_json_encode( $data ),
				'data_format' => 'body',
				'sslverify'   => is_ssl() ? true : false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
					'Content-type'  => 'application/json',
				),
			)
		);

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( $response['body'], true );

			$http_response = $response['response'];
			if ( ! isset( $http_response['code'] ) || '200' != $http_response['code'] ) {
				WcYounitedpayLogger::log( 'get_best_price(' . $price . ') - $response : ' . json_encode( $response ) );
			}else{
				//WcYounitedpayLogger::log( 'get_best_price(' . $price . ') - $response : ' . json_encode( $response ) );
			}

			if ( isset( $body['offers'] ) ) {
				return $body['offers'];
			}
		} else {
			WcYounitedpayLogger::log( 'get_best_price(' . $price . ') - $response : ' . json_encode( $response ) );
		}

		return false;
	}

	/*
	* Confirm a YounitedPay Contract
	*/
	public function confirm_contract( $order_id ) {

		$contract_reference = $this->getContractReference( $order_id );
		WcYounitedpayLogger::log( 'confirm_contract(' . $order_id . ') - $order->_younitedpay_contract_reference : ' . $contract_reference );
		if ( ! $contract_reference ) {
			return false;
		}

		// YOUNITEDPAY API REQUEST -> Confirm Contract
		$response = wp_remote_request(
			$this->api . '/api/1.0/Contract/' . $contract_reference . '/confirm',
			array(
				'method'  => 'PATCH',
				'body'    => json_encode( array( 'merchantOrderId' => '' ) ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
					'Content-type'  => 'application/json',
				),
			)
		);

		WcYounitedpayLogger::log( 'confirm_contract(' . $order_id . ') - $response : ' . json_encode( $response ) );

		if ( ! is_wp_error( $response ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 204 ) {
				return true;
			}
		}

		return false;
	}

	/*
	* Cancel a YounitedPay Contract
	*/
	public function cancel_contract( $order_id ) {
		$contract_reference = $this->getContractReference( $order_id );
		WcYounitedpayLogger::log( 'cancel_contract(' . $order_id . ') - $order->_younitedpay_contract_reference : ' . $contract_reference );
		if ( ! $contract_reference ) {
			return false;
		}

		// YOUNITEDPAY API REQUEST -> Confirm Contract
		$response = wp_remote_request(
			$this->api . '/api/1.0/Contract/' . $contract_reference,
			array(
				'method'  => 'DELETE',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
				),
			)
		);

		WcYounitedpayLogger::log( 'cancel_contract(' . $order_id . ') - $response : ' . json_encode( $response ) );

		if ( ! is_wp_error( $response ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 204 ) {
				return true;
			}
		}

		return false;
	}

	/*
	* Withdraw a YounitedPay Contract
	*/
	public function withdraw_contract( $order_id, $amount_withdram = null ) {

		$contract_reference = $this->getContractReference( $order_id );
		WcYounitedpayLogger::log( 'withdraw_contract(' . $order_id . ') - amount ' . $amount_withdram . ' - $order->_younitedpay_contract_reference : ' . $contract_reference );
		if ( ! $contract_reference ) {
			return false;
		}

		// YOUNITEDPAY API REQUEST -> Withdraw Contract
		$response = wp_remote_request(
			$this->api . '/api/1.0/Contract/' . $contract_reference . '/withdraw',
			array(
				'method'  => 'PATCH',
				'body'    => json_encode( array( 'amount' => sanitize_text_field( $amount_withdram ) ) ),
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
					'Content-type'  => 'application/json',
				),
			)
		);

		WcYounitedpayLogger::log( 'withdraw_contract(' . $order_id . ') - $response : ' . json_encode( $response ) );

		if ( ! is_wp_error( $response ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 204 ) {
				return true;
			}
		}

		return false;
	}

	/*
	* Activate a YounitedPay Contract
	*/
	public function activate_contract( $order_id ) {

		$contract_reference = $this->getContractReference( $order_id );
		WcYounitedpayLogger::log( 'activate_contract(' . $order_id . ') - $order->_younitedpay_contract_reference : ' . $contract_reference );
		if ( ! $contract_reference ) {
			return false;
		}

		// YOUNITEDPAY API REQUEST -> Confirm Contract
		$response = wp_remote_request(
			$this->api . '/api/1.0/Contract/' . $contract_reference . '/activate',
			array(
				'method'  => 'PATCH',
				'body'    => '',
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
					'Content-type'  => 'application/json',
				),
			)
		);

		WcYounitedpayLogger::log( 'activate_contract(' . $order_id . ') - $response : ' . json_encode( $response ) );

		if ( ! is_wp_error( $response ) ) {
			if ( wp_remote_retrieve_response_code( $response ) === 204 ) {
				return true;
			}
		}

		return false;
	}

	/*
	* Initialize a YounitedPay Contract
	*/
	public function initialize_contract( $order_id, $maturity ) {

		$cart  = WC()->cart;
		$items = array();
		foreach ( $cart->get_cart() as $item ) {
			$items[] = array(
				'itemName'  => $item['data']->post->post_title,
				'quantity'  => $item['quantity'],
				'unitPrice' => $item['data']->get_price(),
			);
		}

		// Get customer infos
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			throw new Exception("Technical error");
		}

		$site_url = get_site_url();

		// Correct format for phone number => +33xxxxxxxxx or +34xxxxxxxxx
		$phone = str_replace( ' ', '', $order->get_billing_phone() );

		if ( preg_match( '/^0[1-9]\d{8}$/', $phone ) ) {
			$phone = '+' . $this->pre_phone . substr( $phone, 1 );
		} elseif ( preg_match( '/^00' . $this->pre_phone . '[1-9]\d{8}$/', $phone ) ) {
			$phone = '+' . substr( $phone, 2 );
		} elseif ( ! preg_match( '/^\+' . $this->pre_phone . '[1-9]\d{8}$/', $phone ) ) {
			throw new Exception(
				esc_html( sprintf( esc_html__( 'The phone is invalid (accepted formats : 0601020304 / 00%1$s601020304 / +%2$s601020304)', 'wc-younitedpay-gateway' ),
						$this->pre_phone,
						$this->pre_phone
					)
				)
			);
		}

		$data = array(
			'requestedMaturity'    => $maturity,
			'personalInformation'  => array(
				'firstName'       => $order->get_billing_first_name(),
				'lastName'        => $order->get_billing_last_name(),
				'genderCode'      => null, // MALE or FEMALE
				'emailAddress'    => $order->get_billing_email(),
				'cellPhoneNumber' => $phone, // +33 or +34 format
				'birthDate'       => '', // 1987-08-24T14:15:22Z format
				'address'         => array(
					'streetNumber'      => '',
					'streetName'        => substr( $order->get_billing_address_1(), 0, 38 ),
					'additionalAddress' => ( substr( $order->get_billing_address_1(), 38 ) ? substr( $order->get_billing_address_1(), 38 ) : '' ) . $order->get_billing_address_2(),
					'city'              => $order->get_billing_city(),
					'postalCode'        => $order->get_billing_postcode(),
					'countryCode'       => $order->get_billing_country(),
				),
			),
			'basket'               => array(
				'basketAmount' => $cart->total,
				'items'        => $items,
			),
			'merchantUrls'         => array(
				'onGrantedWebhookUrl'               => esc_url_raw( $site_url . '/wc-api/younited-pay-success?order-id=' . $order_id ),
				'onCanceledWebhookUrl'              => esc_url_raw( $site_url . '/wc-api/younited-pay-canceled?order-id=' . $order_id ),
				'onWithdrawnWebhookUrl'             => esc_url_raw( $site_url . '/wc-api/younited-pay-withdrawn?order-id=' . $order_id ),
				'onApplicationSucceededRedirectUrl' => $order->get_checkout_order_received_url(),
				'onApplicationFailedRedirectUrl'    => esc_url_raw( wc_get_checkout_url() . '?younited-msg=' . urlencode( __( 'Contract cancellation', 'wc-younitedpay-gateway' ) ) ),
			),
			'merchantOrderContext' => array(
				'channel'           => 'ONLINE', // ONLINE or PHYSICAL
				'shopCode'          => 'ONLINE',
				'agentEmailAddress' => null,
				'merchantReference' => '',
			),
		);

		WcYounitedpayLogger::log( 'initialize_contract(' . $order_id . ', ' . $maturity . ') - $data : ' . json_encode( $data ) );

		// YOUNITEDPAY API REQUEST -> initialize Contract
		$response = wp_remote_post(
			$this->api . '/api/1.0/Contract',
			array(
				'body'        => wp_json_encode( $data ),
				'data_format' => 'body',
				'sslverify'   => is_ssl() ? true : false,
				'headers'     => array(
					'Authorization' => 'Bearer ' . $this->get_token(), // Get a bearer token
					'Content-type'  => 'application/json',
				),
			)
		);

		WcYounitedpayLogger::log( 'initialize_contract(' . $order_id . ', ' . $maturity . ') - $response : ' . json_encode( $response ) );

		if ( ! is_wp_error( $response ) ) {
			$body = json_decode( $response['body'], true );
			if ( isset( $body['redirectUrl'] ) && isset( $body['contractReference'] ) ) {
				$order->update_status( 'pending' );
				WcYounitedpayLogger::log( 'initialize_contract(' . $order_id . ', ' . $maturity . ') - $order->_younitedpay_contract_reference : ' . $body['contractReference'] );
				$order->save();

				return $body['redirectUrl'];
			} else {
				if ( isset( $body['errors'] ) ) {
					
					$msgs_error = [];

					$msgs_error[] = $body['title'];
					
					foreach ( $body['errors'] as $key_error => $error ) {
						foreach ( $error as $msgError ) {
							$msgs_error[] = $msgError;
						}
					}

					throw new Exception(implode("<br/> ", $msgs_error));
				}
				throw new Exception(esc_html__( 'A technical error has occurred', 'wc-younitedpay-gateway' ));
			}
		}

		$order->update_status( 'failed' );
		$order->save();
		throw new Exception(esc_html__( 'A technical error has occurred', 'wc-younitedpay-gateway' ));
	}

	/*
	* Is Sandbox Api ?
	*/
	public function is_sandbox() {
		return $this->sandbox;
	}

	public function api_keys_is_defined() {
		if ( empty( $this->private_key ) || empty( $this->publishable_key ) ) {
			return false;
		}
		return true;
	}

	/*
	* Get reference of contract
	*/
	public function getContractReference( $order_id ) {
		// Get customer infos
		$order = wc_get_order( $order_id );
		return $this->getContractReferenceOfOrder( $order );
	}

	/*
	* Get reference of contract of order
	*/
	public function getContractReferenceOfOrder( $order ) {
		if ( ! $order ) {
			return false;
		}
		return sanitize_text_field( $order->get_meta( '_younitedpay_contract_reference' ) );
	}
}

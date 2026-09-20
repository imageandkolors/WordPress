<?php
defined( 'ABSPATH' ) || die();

class WLSM_Payu {
	/**
	 * Writes a PayU debug log entry.
	 *
	 * @param string $message Log message.
	 * @param array  $context Optional context.
	 *
	 * @return void
	 */
	public static function log( $message, $context = array() ) {
		$payload = '';

		if ( ! empty( $context ) ) {
			$encoded = wp_json_encode( $context );
			$payload = false !== $encoded ? ' | context=' . $encoded : '';
		}

		error_log( '[WLSM PayU] ' . $message . $payload );
	}

	/**
	 * Masks a secret for safe logging.
	 *
	 * @param string $value Secret value.
	 *
	 * @return string
	 */
	public static function mask_secret( $value ) {
		$value = self::stringify_value( $value );
		$len   = strlen( $value );

		if ( $len <= 4 ) {
			return str_repeat( '*', $len );
		}

		return substr( $value, 0, 2 ) . str_repeat( '*', max( 0, $len - 4 ) ) . substr( $value, -2 );
	}

	/**
	 * Returns a compact hash string preview for logs.
	 *
	 * @param string $value Hash string.
	 *
	 * @return string
	 */
	public static function preview_hash_string( $value ) {
		$value = self::stringify_value( $value );

		if ( strlen( $value ) <= 180 ) {
			return $value;
		}

		return substr( $value, 0, 120 ) . '...[truncated]...' . substr( $value, -40 );
	}

	/**
	 * Field order recommended by PayU for generating request hashes.
	 *
	 * @var array
	 */
	private static $request_sequence = array(
		'key',
		'txnid',
		'amount',
		'productinfo',
		'firstname',
		'email',
		'udf1',
		'udf2',
		'udf3',
		'udf4',
		'udf5',
	);

	/**
	 * PayU response hash field order for reverse-hash verification.
	 *
	 * @var array
	 */
	private static $response_sequence = array(
		'udf5',
		'udf4',
		'udf3',
		'udf2',
		'udf1',
		'email',
		'firstname',
		'productinfo',
		'amount',
		'txnid',
		'key',
	);

	/**
	 * Normalises a value so it is suitable for hashing and output.
	 *
	 * @param mixed $value Value to normalise.
	 *
	 * @return string
	 */
	private static function stringify_value( $value ) {
		if ( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}

		if ( is_scalar( $value ) || null === $value ) {
			return (string) $value;
		}

		return wp_json_encode( $value );
	}

	/**
	 * Ensures every field required for the hash exists and is a string.
	 *
	 * @param array $params Raw parameters.
	 *
	 * @return array
	 */
	private static function normalize_request_params( $params ) {
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		foreach ( self::$request_sequence as $field ) {
			if ( isset( $params[ $field ] ) ) {
				$params[ $field ] = self::stringify_value( $params[ $field ] );
			} else {
				$params[ $field ] = '';
			}
		}

		return $params;
	}

	/**
	 * Returns a normalized PayU mode string.
	 *
	 * @param string $mode Operating mode.
	 *
	 * @return string
	 */
	private static function normalize_mode( $mode ) {
		$mode = strtolower( trim( self::stringify_value( $mode ) ) );

		return ( 'live' === $mode ) ? 'live' : 'test';
	}

	/**
	 * Returns the hosted checkout endpoint for the selected mode.
	 *
	 * @param string $mode Operating mode.
	 *
	 * @return string
	 */
	private static function get_payment_url( $mode = 'test' ) {
		return ( 'live' === self::normalize_mode( $mode ) )
			? 'https://secure.payu.in/_payment'
			: 'https://test.payu.in/_payment';
	}

	/**
	 * Generates a request hash using the official ordering.
	 *
	 * @param array  $params Request parameters.
	 * @param string $salt   Merchant salt.
	 *
	 * @return string
	 */
	public static function generate_hash( $params, $salt ) {
		$params = self::normalize_request_params( $params );

		$hash_parts = array();
		foreach ( self::$request_sequence as $field ) {
			$hash_parts[] = $params[ $field ];
		}

		// PayU hosted checkout hash format appends 6 empty fields before salt:
		// key|txnid|amount|productinfo|firstname|email|udf1|...|udf10||||||SALT
		$hash_string = implode( '|', $hash_parts ) . '||||||' . $salt;

		return strtolower( hash( 'sha512', $hash_string ) );
	}

	/**
	 * Builds the auto-submitting PayU form HTML in plugin code.
	 *
	 * @param array  $fields        Fields expected by PayU.
	 * @param string $merchant_key  Merchant key.
	 * @param string $merchant_salt Merchant salt.
	 * @param string $mode          Operating mode: 'live' or 'test'.
	 *
	 * @return string Rendered HTML form ready to be injected into the page.
	 */
	public static function build_payment_form( $fields, $merchant_key, $merchant_salt, $mode = 'test' ) {
		$fields         = is_array( $fields ) ? $fields : array();
		$fields['key']  = self::stringify_value( $merchant_key );
		$fields['hash'] = self::generate_hash( $fields, $merchant_salt );
		$action_url     = self::get_payment_url( $mode );
		$allowed_fields = array(
			'key',
			'txnid',
			'amount',
			'productinfo',
			'firstname',
			'lastname',
			'email',
			'phone',
			'address1',
			'city',
			'state',
			'country',
			'zipcode',
			'surl',
			'furl',
			'hash',
			'udf1',
			'udf2',
			'udf3',
			'udf4',
			'udf5',
		);

		$html  = '<form action="' . esc_url( $action_url ) . '" id="payment_form_submit" method="post">';
		foreach ( $allowed_fields as $field_name ) {
			$value = isset( $fields[ $field_name ] ) ? self::stringify_value( $fields[ $field_name ] ) : '';
			$html .= '<input type="hidden" name="' . esc_attr( $field_name ) . '" value="' . esc_attr( $value ) . '">';
		}
		$html .= '</form>';
		$html .= '<script type="text/javascript">(function(){var form=document.getElementById("payment_form_submit");if(form){form.submit();}})();</script>';

		return $html;
	}

	/**
	 * Validates the hash in a PayU callback response using plugin-side logic.
	 *
	 * @param array  $posted        Posted data received from PayU.
	 * @param string $merchant_key  Merchant key expected by the plugin.
	 * @param string $merchant_salt Merchant salt expected by the plugin.
	 *
	 * @return bool
	 */
	public static function verify_hash( $posted, $merchant_key, $merchant_salt ) {
		if ( empty( $posted ) || ! is_array( $posted ) ) {
			return false;
		}

		$posted_key = isset( $posted['key'] ) ? self::stringify_value( $posted['key'] ) : '';
		$status     = isset( $posted['status'] ) ? self::stringify_value( $posted['status'] ) : '';
		$resphash   = isset( $posted['hash'] ) ? strtolower( self::stringify_value( $posted['hash'] ) ) : '';

		if ( empty( $posted_key ) || empty( $status ) || empty( $resphash ) || empty( $merchant_key ) || empty( $merchant_salt ) ) {
			return false;
		}

		if ( ! hash_equals( (string) $merchant_key, $posted_key ) ) {
			return false;
		}

		$hash_parts = array();
		foreach ( self::$response_sequence as $field ) {
			$hash_parts[] = isset( $posted[ $field ] ) ? self::stringify_value( $posted[ $field ] ) : '';
		}

		$hash_string = $merchant_salt . '|' . $status . '||||||' . implode( '|', $hash_parts );

		if ( isset( $posted['additionalCharges'] ) && '' !== self::stringify_value( $posted['additionalCharges'] ) ) {
			$hash_string = self::stringify_value( $posted['additionalCharges'] ) . '|' . $hash_string;
		}

		$calculated_hash = strtolower( hash( 'sha512', $hash_string ) );

		return hash_equals( $calculated_hash, $resphash );
	}

	/**
	 * Verifies a PayU transaction directly with PayU's server.
	 *
	 * @param string $txnid         Merchant transaction ID.
	 * @param string $merchant_key  Merchant key.
	 * @param string $merchant_salt Merchant salt.
	 * @param string $mode          Operating mode: 'live' or 'test'.
	 *
	 * @return array|WP_Error
	 */
	public static function verify_payment( $txnid, $merchant_key, $merchant_salt, $mode = 'test' ) {
		$txnid = self::stringify_value( $txnid );

		if ( empty( $txnid ) || empty( $merchant_key ) || empty( $merchant_salt ) ) {
			return new WP_Error( 'wlsm_payu_invalid_verify_args', __( 'Invalid PayU verification arguments.', 'school-management' ) );
		}

		$endpoint = ( 'live' === self::normalize_mode( $mode ) )
			? 'https://info.payu.in/merchant/postservice.php?form=2'
			: 'https://test.payu.in/merchant/postservice.php?form=2';

		$body = array(
			'key'     => $merchant_key,
			'command' => 'verify_payment',
			'var1'    => $txnid,
			'hash'    => strtolower( hash( 'sha512', $merchant_key . '|verify_payment|' . $txnid . '|' . $merchant_salt ) ),
		);

		self::log(
			'Verify payment request',
			array(
				'mode'         => self::normalize_mode( $mode ),
				'endpoint'     => $endpoint,
				'txnid'        => $txnid,
				'merchant_key' => self::mask_secret( $merchant_key ),
				'hash'         => $body['hash'],
			)
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 30,
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			self::log(
				'Verify payment WP_Error',
				array(
					'txnid'   => $txnid,
					'code'    => $response->get_error_code(),
					'message' => $response->get_error_message(),
				)
			);
		}

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		$raw_body    = wp_remote_retrieve_body( $response );
		$data        = json_decode( $raw_body, true );

		self::log(
			'Verify payment raw response',
			array(
				'txnid'       => $txnid,
				'status_code' => $status_code,
				'body'        => $raw_body,
			)
		);

		if ( 200 !== $status_code || ! is_array( $data ) ) {
			return new WP_Error( 'wlsm_payu_verify_failed', __( 'Invalid PayU verification response.', 'school-management' ), array( 'status_code' => $status_code, 'body' => $raw_body ) );
		}

		return $data;
	}
}

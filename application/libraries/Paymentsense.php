<?php
/**
 * Copyright (C) 2019 Paymentsense Ltd.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @author      Paymentsense
 * @copyright   2019 Paymentsense Ltd.
 * @license     https://www.gnu.org/licenses/gpl-3.0.html
 * @link        https://developers.paymentsense.co.uk/
 */

class Paymentsense
{
	/**
	 * Request Types
	 */
	const REQ_NOTIFICATION      = '0';
	const REQ_CUSTOMER_REDIRECT = '1';

	/**
	 * Hosted Payment Form URL
	 */
	const PAYMENT_FORM_URL = 'https://mms.paymentsensegateway.com/Pages/PublicPages/PaymentForm.aspx';

	protected $CI;
	protected $config;
	protected $mms_config;

	function __construct()
	{
		$this->CI =& get_instance();
		$this->config = $this->CI->config;
		$this->mms_config = $this->config->item('mms_config');
	}

	/**
	 * Gets the hash digest
	 *
	 * @param array An associative array of the input data
	 *
	 * @return array An associative array containing the HashDigest element
	 */
	public function get_hash_digest($input_data)
	{
		$fields = array(
			'Amount',
			'CurrencyCode',
			'OrderID',
			'TransactionType',
			'TransactionDateTime',
			'CallbackURL',
			'OrderDescription',
			'CustomerName',
			'Address1',
			'Address2',
			'Address3',
			'Address4',
			'City',
			'State',
			'PostCode',
			'CountryCode',
			'EmailAddress',
			'PhoneNumber',
			'EmailAddressEditable',
			'PhoneNumberEditable',
			'CV2Mandatory',
			'Address1Mandatory',
			'CityMandatory',
			'PostCodeMandatory',
			'StateMandatory',
			'CountryMandatory',
			'ResultDeliveryMethod',
			'ServerResultURL',
			'PaymentFormDisplaysResult',
		);

		$data = 'MerchantID=' . $this->mms_config['MerchantID'];
		$data .= '&Password=' . $this->mms_config['Password'];

		foreach ($fields as $field) {
			if (isset($input_data[$field])) {
				$data .= '&' . $field . '=' . htmlentities($input_data[$field]);
			}
		};

		return array(
			'HashDigest' => $this->calculate_hash_digest(
				$data,
				$this->mms_config['HashMethod'],
				$this->mms_config['PreSharedKey']
			),
		);

	}

	/**
	 * Builds a string containing the variables for calculating the hash digest
	 *
	 * @param string $request_type Type of the request (notification or customer redirect).
	 *
	 * @return bool
	 */
	public function build_variables_string( $request_type ) {
		$result = false;
		$fields = array(
			// Variables for hash digest calculation for notification requests (excluding configuration variables).
			self::REQ_NOTIFICATION      => array(
				'StatusCode',
				'Message',
				'PreviousStatusCode',
				'PreviousMessage',
				'CrossReference',
				'Amount',
				'CurrencyCode',
				'OrderID',
				'TransactionType',
				'TransactionDateTime',
				'OrderDescription',
				'CustomerName',
				'Address1',
				'Address2',
				'Address3',
				'Address4',
				'City',
				'State',
				'PostCode',
				'CountryCode',
				'EmailAddress',
				'PhoneNumber',
			),
			// Variables for hash digest calculation for customer redirects (excluding configuration variables).
			self::REQ_CUSTOMER_REDIRECT => array(
				'CrossReference',
				'OrderID',
			),
		);

		if ( array_key_exists( $request_type, $fields ) ) {
			$result = 'MerchantID=' . $this->mms_config['MerchantID'] .
				'&Password=' . $this->mms_config['Password'];
			foreach ( $fields[ $request_type ] as $field ) {
				$result .= '&' . $field . '=' . $this->get_http_var( $field );
			}
		}
		return $result;
	}

	/**
	 * Calculates the hash digest.
	 * Supported hash methods: MD5, SHA1, HMACMD5, HMACSHA1
	 *
	 * @param string $data Data to be hashed.
	 * @param string $hash_method Hash method.
	 * @param string $key Secret key to use for generating the hash.
	 *
	 * @return string
	 */
	public function calculate_hash_digest( $data, $hash_method, $key ) {
		$result = '';

		$include_key = in_array( $hash_method, array( 'MD5', 'SHA1' ), true );
		if ( $include_key ) {
			$data = 'PreSharedKey=' . $key . '&' . $data;
		}

		switch ( $hash_method ) {
			case 'MD5':
				$result = md5( $data );
				break;
			case 'SHA1':
				$result = sha1( $data );
				break;
			case 'HMACMD5':
				$result = hash_hmac( 'md5', $data, $key );
				break;
			case 'HMACSHA1':
				$result = hash_hmac( 'sha1', $data, $key );
				break;
		}

		return $result;
	}

	/**
	 * Checks whether the hash digest received from the gateway is valid
	 *
	 * @return bool
	 */
	public function is_hash_digest_valid() {
		$result = false;
		$request_type = is_numeric( $this->get_http_var( 'StatusCode' ) )
			? self::REQ_NOTIFICATION
			: self::REQ_CUSTOMER_REDIRECT;

		$data = $this->build_variables_string( $request_type );
		if ( $data ) {
			$hash_digest_received = $this->get_http_var( 'HashDigest' );
			$hash_digest_calculated = $this->calculate_hash_digest(
				$data,
				$this->mms_config['HashMethod'],
				$this->mms_config['PreSharedKey']
			);
			$result = strToUpper( $hash_digest_received ) === strToUpper( $hash_digest_calculated );
		}
		return $result;
	}

	/**
	 * Gets the value of an HTTP variable based on the requested method or
	 * the default value if the variable does not exist
	 *
	 * @param string $field HTTP POST/GET variable.
	 * @param string $default Default value
	 * @return string
	 */
	public function get_http_var( $field, $default = '' ) {
		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				return array_key_exists( $field, $_GET )
					? $_GET[ $field ]
					: $default;
			case 'POST':
				return array_key_exists( $field, $_POST )
					? $_POST[ $field ]
					: $default;
			default:
				return $default;
		}
	}

	/**
	 * Gets the Hosted Payment Form URL
	 *
	 * @return string
	 */
	public function get_payment_form_url() {
		return self::PAYMENT_FORM_URL;
	}
}

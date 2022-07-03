<?php

//use \Firebase\JWT\JWT;

/**
 * Class Connecting Zoom APi V2
 *
 * @since   2.0
 * @author  Deepen
 * @modifiedn
 */
if ( ! class_exists( 'Zoom_Attendance_Api' ) ) {

	class Zoom_Attendance_Api {

		/**
		 * Zoom API KEY
		 *
		 * @var
		 */
		public $zoom_api_key;

		/**
		 * Zoom API Secret
		 *
		 * @var
		 */
		public $zoom_api_secret;

		/**
		 * Hold my instance
		 *
		 * @var
		 */
		protected static $_instance;

		/**
		 * API endpoint base
		 *
		 * @var string
		 */
		private $api_url = 'https://api.zoom.us/v2/';

		/**
		 * Create only one instance so that it may not Repeat
		 *
		 * @since 2.0.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Zoom_Video_Conferencing_Api constructor.
		 *
		 * @param $zoom_api_key
		 * @param $zoom_api_secret
		 */
		public function __construct( $zoom_api_key = 'M5Sli5_fQfWHJyjYK3m4ng', $zoom_api_secret = 'QhfKz2KFHtgt8rqRJrMPbXPNvCBijwqJ9q8h' ) {
			$this->zoom_api_key    = $zoom_api_key;
			$this->zoom_api_secret = $zoom_api_secret;
		}

		/**
		 * Send request to API
		 *
		 * @param        $calledFunction
		 * @param        $data
		 * @param string $request
		 *
		 * @return array|bool|string|WP_Error
		 */
		protected function sendRequest( $calledFunction, $data, $request = "GET" ) {
			$request_url = $this->api_url . $calledFunction;
			$args        = array(
				'headers' => array(
					'Authorization' => 'Bearer eyJhbGciOiJIUzI1NiJ9.eyJhdWQiOm51bGwsImlzcyI6Ik01U2xpNV9mUWZXSEp5allLM200bmciLCJleHAiOjE3NjEzOTIxNjAsImlhdCI6MTYzNTE1NjQ2MX0.6tL3uiwqe8L5mmWk3t8Ek_zQIwZcBJmAkRT0bBtEABg',
					'Content-Type'  => 'application/json'
				)
			);

			if ( $request == "GET" ) {
				$args['body'] = ! empty( $data ) ? $data : array();
				$response     = wp_remote_get( $request_url, $args );
			} else if ( $request == "DELETE" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "DELETE";
				$response       = wp_remote_request( $request_url, $args );
			} else if ( $request == "PATCH" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PATCH";
				$response       = wp_remote_request( $request_url, $args );
			} else if ( $request == "PUT" ) {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "PUT";
				$response       = wp_remote_request( $request_url, $args );
			} else {
				$args['body']   = ! empty( $data ) ? json_encode( $data ) : array();
				$args['method'] = "POST";
				$response       = wp_remote_post( $request_url, $args );
			}

			$response = wp_remote_retrieve_body( $response );
			/*dump($response);
			die;*/

			if ( ! $response ) {
				return false;
			}

			return $response;
		}

		//function to generate JWT
		// private function generateJWTKey() {
		// 	$key    = $this->zoom_api_key;
		// 	$secret = $this->zoom_api_secret;

		// 	$token = array(
		// 		"iss" => $key,
		// 		"exp" => time() + 3600 //60 seconds as suggested
		// 	);

		// 	return JWT::encode( $token, $secret );
		// }

		/**
		 * Creates a User
		 *
		 * @param $postedData
		 *
		 * @return array|bool|string
		 */
		public function getAccountReport( $zoom_account_from, $zoom_account_to ) {
			$getAccountReportArray              = array();
			$getAccountReportArray['from']      = $zoom_account_from;
			$getAccountReportArray['to']        = $zoom_account_to;
			$getAccountReportArray['page_size'] = 300;
			// $getAccountReportArray              = apply_filters( 'vczapi_getAccountReport', $getAccountReportArray );
		
			return $this->sendRequest( 'report/users/info@tritekconsulting.co.uk/meetings', $getAccountReportArray, "GET" );
		}
	
	}

	function zoom_attendance() {
		return Zoom_Attendance_Api::instance();
	}

	zoom_attendance();
}
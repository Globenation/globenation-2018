<?php
class Advanced_Ads_AdSense_MAPI {

	const OPTNAME = 'advanced-ads-adsense-mapi';

	const CID = '400595147946-alk0j13qk563bg94rd4f3ip2t0b2tr5r.apps.googleusercontent.com';

	const CS = '5jecyWgvCszB8UxSM0oS1W22';

	const CALL_PER_24H = 20;
	
	const UNSUPPORTED_TYPE_LINK = 'https://wpadvancedads.com/adsense-ad-type-not-available/';

	private static $instance = null;

	private static $default_options = array();

	private static $empty_account_data = array(
		'default_app' => array(
			'access_token'  => '',
			'refresh_token' => '',
			'expires'       => 0,
			'token_type'    => '',
		),
		'user_app'    => array(
			'access_token'  => '',
			'refresh_token' => '',
			'expires'       => 0,
			'token_type'    => '',
		),
		'ad_units'    => array(),
	);

	private function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

		add_action( 'wp_ajax_advads_gadsense_mapi_confirm_code', array( $this, 'ajax_confirm_code' ) );
		add_action( 'wp_ajax_advads_gadsense_mapi_get_adUnits', array( $this, 'ajax_get_adUnits' ) );
		add_action( 'wp_ajax_advads_mapi_get_adCode', array( $this, 'ajax_get_adCode' ) );
		add_action( 'wp_ajax_advads-mapi-reconstructed-code', array( $this, 'ajax_save_reconstructed_code' ) );
		add_action( 'wp_ajax_advads-mapi-revoke-token', array( $this, 'ajax_revoke_tokken' ) );

		self::$default_options = array(
			'accounts'          => array(),
			'ad_codes'          => array(),
			'unsupported_units' => array(),
			'quota'             => array(
				'count' => self::CALL_PER_24H,
				'ts'    => 0,
			),
		);
	}

	/**
	 * Get available quota and eventual message about remaining call
	 */
	public function get_quota() {
		$options = $this->get_option();
		$now     = time();
		if ( self::use_user_app() ) {
			return array( 'count' => PHP_INT_MAX );
		} else {
			if ( $now > $options['quota']['ts'] + ( 24 * 3600 ) ) {
				return array(
					'count' => self::CALL_PER_24H,
				);
			} else {
				$msg = $this->get_quota_msg();
				return array(
					'count' => $options['quota']['count'],
					'msg'   => $msg,
				);
			}
		}
	}

	/**
	 *  Get the readable quota
	 */
	public function get_quota_msg() {

		$options = $this->get_option();
		$now     = time();
		$secs    = $options['quota']['ts'] + ( 24 * 3600 ) - $now;
		$hours   = floor( $secs / 3600 );
		$mins    = ceil( ( $secs - ( $hours * 3600 ) ) / 60 );

		if ( 60 == $mins ) {
			$hours += 1;
			$mins   = 0;
		}

		if ( 0 == $options['quota']['count'] ) {

			$msg = sprintf(
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				_x( 'No API call left before %1$s %2$s.', 'No call left for the next X hours Y minutes.', 'advanced-ads' ),
				sprintf( _n( '%s hour', '%s hours', $hours, 'advanced-ads' ), $hours ),
				sprintf( _n( '%s minute', '%s minutes', $mins, 'advanced-ads' ), $mins )
				*/
				'No API call left before %1$s %2$s.',
				sprintf( '%s hours', $hours ),
				sprintf( '%s minutes', $mins )
			);

			if ( 0 == $hours ) {
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				$msg = sprintf(
					_x( 'No API call left before %s.', 'No call left for the next X time.', 'advanced-ads' ),
					sprintf( _n( '%s minute', '%s minutes', $mins, 'advanced-ads' ), $mins )
				);
				 */
				 $msg = 'No API call left before.';
			}

			if ( 0 == $mins ) {
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				$msg = sprintf(
					_x( 'No API call left before %s.', 'No call left for the next X time.', 'advanced-ads' ),
					sprintf( _n( '%s hour', '%s hours', $hours, 'advanced-ads' ), $hours )
				);
				 */
				$msg = 'No API call left.';
			}
		} else {

			$msg = sprintf(
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				_x( '%1$s for the next %2$s %3$s.', 'Calls remaining for the next X hours Y minutes.', 'advanced-ads' ),
				sprintf( _n( '%s API call remaining.', '%s API calls remaining.', $options['quota']['count'], 'advanced-ads' ), $options['quota']['count'] ),
				sprintf( _n( '%s hour', '%s hours', $hours, 'advanced-ads' ), $hours ),
				sprintf( _n( '%s minute', '%s minutes', $mins, 'advanced-ads' ), $mins )
				 */
				'%1$s for the next %2$s %3$s',
				sprintf( '%s API calls remaining', $options['quota']['count'] ),
				sprintf( '%s hours', $hours ),
				sprintf( '%s minutes', $mins )
			);

			if ( 0 == $hours ) {
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				$msg = sprintf(
					_x( '%1$s for the next %2$s', 'Calls remaining for the next X time.', 'advanced-ads' ),
					sprintf( _n( '%s API call remaining.', '%s API calls remaining.', $options['quota']['count'], 'advanced-ads' ), $options['quota']['count'] ),
					sprintf( _n( '%s minute', '%s minutes', $mins, 'advanced-ads' ), $mins )
				);
				 */
				$msg = sprintf( '%s API calls remaining.', $options['quota']['count'] );
			}

			if ( 0 == $mins ) {
				/*
				 commented out so that these unused strings don’t show up for translators; using fixed strings instead in case we forget this when we might add the check again later
				$msg = sprintf(
					_x( '%1$s for the next %2$s', 'calls remaining for the next X time', 'advanced-ads' ),
					sprintf( _n( '%s API call remaining', '%s API calls remaining', $options['quota']['count'], 'advanced-ads' ), $options['quota']['count'] ),
					sprintf( _n( '%s hour', '%s hours', $hours, 'advanced-ads' ), $hours )
				);
				 */
				$msg = sprintf(
					'%1$s for the next %2$s',
					sprintf( '%s API calls remaining', $options['quota']['count'] ),
					sprintf( '%s hours', $hours )
				);
			}
		}
		return $msg;
	}

	/**
	 *  Decrement quota by 1, and return message about remaining call
	 */
	public function decrement_quota() {
		$options = $this->get_option();
		if ( 0 < $options['quota']['count'] ) {
			$options['quota']['count']--;
			$now = time();
			if ( $now > $options['quota']['ts'] + ( 24 * 3600 ) ) {
				$options['quota']['ts'] = $now;
			}
			update_option( self::OPTNAME, $options );
			return $this->get_quota_msg();
		}
	}

	/**
	 * Return the ad code for a given client and unit
	 *
	 * @return [str]|[arr] the ad code or info on the error.
	 */
	public function get_ad_code( $adUnit ) {
		$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id    = $gadsense_data->get_adsense_id();
		$options       = self::get_option();

		$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$adsense_id    = $gadsense_data->get_adsense_id();

		$url          = 'https://www.googleapis.com/adsense/v1.4/accounts/' . $adsense_id . '/adclients/ca-' . $adsense_id . '/adunits/' . $adUnit . '/adcode';
		$access_token = $this->get_access_token( $adsense_id );

		if ( ! isset( $access_token['msg'] ) ) {
			$headers  = array(
				'Authorization' => 'Bearer ' . $access_token,
			);
			$response = wp_remote_get( $url, array( 'headers' => $headers ) );
			if ( is_wp_error( $response ) ) {
				return array(
					'status' => false,
					'msg'    => 'error while retrieving adUnits list',
					'raw'    => $response->get_error_message(),
				);
			} else {
				$adCode = json_decode( $response['body'], true );
				if ( null === $adCode || ! isset( $adCode['adCode'] ) ) {
					if (
							$adCode['error'] &&
							$adCode['error']['errors'] &&
							isset( $adCode['error']['errors'][0] ) &&
							isset( $adCode['error']['errors'][0]['reason'] ) &&
							'doesNotSupportAdUnitType' == $adCode['error']['errors'][0]['reason']
					) {
						$options['unsupported_units'][ $adUnit ] = 1;
						update_option( self::OPTNAME, $options );
						return array(
							'status' => false,
							'msg'    => 'doesNotSupportAdUnitType',
						);
					} else {
						return array(
							'status' => false,
							'msg'    => 'invalid response while retrieving adCode for ' . $adUnit,
							'raw'    => $response['body'],
						);
					}
				} else {
					$options['ad_codes'][ $adUnit ] = $adCode['adCode'];
					if ( isset( $options['unsupported_units'][ $adUnit ] ) ) {
						unset( $options['unsupported_units'][ $adUnit ] );
					}
					update_option( self::OPTNAME, $options );
					return $adCode['adCode'];
				}
			}
		} else {
			// return the original error info
			return $access_token;
		}
	}

	/**
	 *  Get/Update ad unit list for a given client
	 *
	 *  @return [bool]|[array] TRUE on success, error info (as array) if an error occurred.
	 */
	public function get_ad_units( $account ) {
		$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
		$url           = 'https://www.googleapis.com/adsense/v1.4/accounts/' . $account . '/adclients/ca-' . $account . '/adunits?includeInactive=true';
		$access_token  = $this->get_access_token( $account );

		$options = self::get_option();

		if ( ! isset( $access_token['msg'] ) ) {
			$headers  = array(
				'Authorization' => 'Bearer ' . $access_token,
			);
			$response = wp_remote_get( $url, array( 'headers' => $headers ) );
			if ( is_wp_error( $response ) ) {
				return array(
					'status' => false,
					'msg'    => 'error while retrieving adUnits list for "' . $account . '"',
					'raw'    => $response->get_error_message(),
				);
			} else {
				$adUnits = json_decode( $response['body'], true );
				if ( null === $adUnits || ! isset( $adUnits['items'] ) ) {
					return array(
						'status' => false,
						'msg'    => 'invalid response while retrieving adUnits list for "' . $account . '"',
						'raw'    => $response['body'],
					);
				} else {
					$new_adUnits = array();
					foreach ( $adUnits['items'] as $item ) {
						$new_adUnits[ $item['id'] ] = $item;
					}
					$options['accounts'][ $account ]['ad_units'] = $new_adUnits;
					update_option( self::OPTNAME, $options );
					return true;
				}
			}
		} else {
			// return the original error info
			return $access_token;
		}
	}

	/**
	 *  Get the appropriate access token (default one or from user's Google app). Update it if needed.
	 *
	 *  @return [str]|[array] the token on success, error info (as array) if an error occurred.
	 */
	public function get_access_token( $account ) {
		$options = self::get_option();
		if ( self::use_user_app() ) {
			if ( time() > $options['accounts'][ $account ]['user_app']['expires'] ) {
				$new_tokens = $this->renew_access_token( $account );
				if ( $new_tokens['status'] ) {
					return $new_tokens['access_token'];
				} else {
					// return all error info [arr]
					return $new_tokens;
				}
			} else {
				return $options['accounts'][ $account ]['user_app']['access_token'];
			}
		} else {
			if ( time() > $options['accounts'][ $account ]['default_app']['expires'] ) {
				$new_tokens = $this->renew_access_token( $account );
				if ( $new_tokens['status'] ) {
					return $new_tokens['access_token'];
				} else {
					// return all error info [arr]
					return $new_tokens;
				}
			} else {
				return $options['accounts'][ $account ]['default_app']['access_token'];
			}
		}
	}

	/**
	 *  Renew the current access token.
	 */
	public function renew_access_token( $account ) {
		$cid           = self::CID;
		$cs            = self::CS;
		$options       = self::get_option();
		$access_token  = $options['accounts'][ $account ]['default_app']['access_token'];
		$refresh_token = $options['accounts'][ $account ]['default_app']['refresh_token'];

		if ( self::use_user_app() ) {
			$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
			$_options      = $gadsense_data->get_options();
			$cid           = ADVANCED_ADS_MAPI_CID;
			$cs            = ADVANCED_ADS_MAPI_CIS;
			$access_token  = $options['accounts'][ $account ]['user_app']['access_token'];
			$refresh_token = $options['accounts'][ $account ]['user_app']['refresh_token'];
		}

		$url  = 'https://www.googleapis.com/oauth2/v4/token';
		$args = array(
			'body' => array(
				'refresh_token' => $refresh_token,
				'client_id'     => $cid,
				'client_secret' => $cs,
				'grant_type'    => 'refresh_token',
			),
		);

		$response = wp_remote_post( $url, $args );
		if ( is_wp_error( $response ) ) {
			return array(
				'status' => false,
				'msg'    => 'error while renewing access token for "' . $account . '"',
				'raw'    => $response->get_error_message(),
			);
		} else {
			$tokens = json_decode( $response['body'], true );
			if ( null !== $tokens ) {
				$expires = time() + absint( $tokens['expires_in'] );
				if ( self::use_user_app() ) {
					$options['accounts'][ $account ]['user_app']['access_token'] = $tokens['access_token'];
					$options['accounts'][ $account ]['user_app']['expires']      = $expires;
				} else {
					$options['accounts'][ $account ]['default_app']['access_token'] = $tokens['access_token'];
					$options['accounts'][ $account ]['default_app']['expires']      = $expires;
				}
				update_option( self::OPTNAME, $options );
				return array(
					'status'       => true,
					'access_token' => $tokens['access_token'],
				);
			} else {
				return array(
					'status' => false,
					'msg'    => 'invalid response received while renewing access token for "' . $account . '"',
					'raw'    => $response['body'],
				);
			}
		}
	}

	/**
	 *  Recoke a refresh token
	 */
	public function ajax_revoke_tokken() {

		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$adsense_id = stripslashes( $_POST['adsenseId'] );
			$options    = self::get_option();
			if ( self::use_user_app() ) {
				$token = $options['accounts'][ $adsense_id ]['user_app']['refresh_token'];
			} else {
				$token = $options['accounts'][ $adsense_id ]['default_app']['refresh_token'];
			}
			$url  = 'https://accounts.google.com/o/oauth2/revoke?token=' . $token;
			$args = array(
				'timeout' => 5,
				'header'  => array( 'Content-type' => 'application/x-www-form-urlencoded' ),
			);

			$response = wp_remote_post( $url, $args );
			if ( is_wp_error( $response ) ) {
				echo json_encode( array( 'status' => false ) );
			} else {
				header( 'Content-Type: application/json' );
				unset( $options['accounts'][ $adsense_id ] );
				update_option( self::OPTNAME, $options );
				echo json_encode( array( 'status' => true ) );
			}
		}
		die;

	}

	/**
	 * Save ad code reconstructed from ad parameters
	 */
	public function ajax_save_reconstructed_code() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$code          = stripslashes( $_POST['code'] );
			$slot          = stripslashes( $_POST['slot'] );
			$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();
			$adsense_id    = $gadsense_data->get_adsense_id();
			$options       = self::get_option();
			$options['ad_codes'][ 'ca-' . $adsense_id . ':' . $slot ] = $code;
			update_option( self::OPTNAME, $options );
			header( 'Content-Type: application/json' );
			echo json_encode( array( 'status' => true ) );
		}
		die;
	}

	/**
	 * Get ad code for a given unit
	 */
	public function ajax_get_adCode() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$unit = stripslashes( $_POST['unit'] );

			if ( ! self::use_user_app() ) {
				$quota = $this->get_quota();

				// No more quota left
				if ( $quota['count'] < 1 ) {
					$quota_msg = $this->get_quota_msg();
					header( 'Content-Type: application/json' );
					$quota_msg = $this->get_quota_msg();
					echo wp_json_encode(
						array(
							'quota'    => 0,
							'quotaMsg' => $quota_msg,
						)
					);
					die;
				}
			}

			$code = $this->get_ad_code( $unit );

			/**
			 * Ad code is returned as string. Otherwise it's an error
			 */
			if ( is_string( $code ) ) {

				$response = array( 'code' => $code );

				/**
				 *  Add quota info for default API creds
				 */
				if ( ! self::use_user_app() ) {
					$quota                = $this->get_quota();
					$quota_msg            = $this->get_quota_msg();
					$response['quota']    = $quota['count'];
					$response['quotaMsg'] = $quota_msg;
				}

				header( 'Content-Type: application/json' );
				echo wp_json_encode( $response );

			} else {

				// return info about the error
				header( 'Content-Type: application/json' );
				echo wp_json_encode( $code );

			}
		}
		die;
	}

	/**
	 * Get / Update the ad unit list for a given ad client. The corresponding <select /> input used in the ad selector is passed as a fied of an array
	 */
	public function ajax_get_adUnits() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$account = stripslashes( $_POST['account'] );
			$units   = $this->get_ad_units( $account );

			if ( true == $units ) {
				$options  = self::get_option();
				$ad_units = $options['accounts'][ $account ]['ad_units'];
				ob_start();

				include_once ADVADS_BASE_PATH . '/modules/gadsense/admin/views/mapi-ad-selector.php';

				$ad_selector = ob_get_clean();

				$response = array(
					'status' => true,
					'html'   => $ad_selector,
				);

				/**
				 *  Add quota info for default API creds
				 */
				if ( ! self::use_user_app() ) {
					$quota                = $this->get_quota();
					$quota_msg            = $this->get_quota_msg();
					$response['quota']    = $quota['count'];
					$response['quotaMsg'] = $quota_msg;
				}
			} else {
				/**
				 *  return the error info [arr]
				 */
				$response = $units;
			}
			header( 'Content-Type: application/json' );
			echo wp_json_encode( $response );
		}
		die;
	}

	/**
	 * Submit Google API confirmation code. Save the token and update ad client list.
	 */
	public function ajax_confirm_code() {
		$nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
		if ( false !== wp_verify_nonce( $nonce, 'advads-mapi' ) ) {
			$code = urldecode( $_POST['code'] );
			$cid  = self::CID;
			$cs   = self::CS;

			$use_user_app = self::use_user_app();

			$gadsense_data = Advanced_Ads_AdSense_Data::get_instance();

			if ( $use_user_app ) {
				$_options = $gadsense_data->get_options();
				$cid      = ADVANCED_ADS_MAPI_CID;
				$cs       = ADVANCED_ADS_MAPI_CIS;
			}

			$code_url     = 'https://www.googleapis.com/oauth2/v4/token';
			$redirect_uri = 'urn:ietf:wg:oauth:2.0:oob';
			$grant_type   = 'authorization_code';

			$args = array(
				'timeout' => 10,
				'body'    => array(
					'code'          => $code,
					'client_id'     => $cid,
					'client_secret' => $cs,
					'redirect_uri'  => $redirect_uri,
					'grant_type'    => $grant_type,
				),
			);

			$response = wp_remote_post( $code_url, $args );

			if ( is_wp_error( $response ) ) {
				return json_encode(
					array(
						'status' => false,
						'msg'    => 'error while submitting code',
						'raw'    => $response->get_error_message(),
					)
				);
			} else {
				$token      = json_decode( $response['body'], true );
				$adsense_id = $gadsense_data->get_adsense_id();

				if ( null !== $token && isset( $token['refresh_token'] ) ) {

					if ( ! empty( $adsense_id ) ) {
						self::save_token_from_code( $token, $adsense_id );

						$gadsense_options                       = $gadsense_data->get_options();
						$gadsense_options['page-level-enabled'] = isset( $_POST['autoads'] );
						update_option( GADSENSE_OPT_NAME, $gadsense_options );

						header( 'Content-Type: application/json' );
						echo json_encode( array( 'status' => true ) );

					} else {
						/**
						 * get AdSense ID first
						 */
						$url = 'https://www.googleapis.com/adsense/v1.4/accounts';

						$headers  = array( 'Authorization' => 'Bearer ' . $token['access_token'] );
						$response = wp_remote_get( $url, array( 'headers' => $headers ) );

						if ( is_wp_error( $response ) ) {

							header( 'Content-Type: application/json' );
							echo json_encode(
								array(
									'status'    => false,
									'error_msg' => $response->get_error_message(),
								)
							);

						} else {

							$accounts   = json_decode( $response['body'], true );
							$adsense_id = $accounts['items'][0]['id'];

							$gadsense_options               = $gadsense_data->get_options();
							$gadsense_options['adsense-id'] = $adsense_id;

							$gadsense_options['page-level-enabled'] = isset( $_POST['autoads'] );

							update_option( GADSENSE_OPT_NAME, $gadsense_options );
							self::save_token_from_code( $token, $adsense_id );

							header( 'Content-Type: application/json' );
							echo json_encode( array( 'status' => true ) );

						}
					}
				} else {
					header( 'Content-Type: application/json' );
					echo json_encode(
						array(
							'status'        => false,
							'response_body' => $response['body'],
						)
					);
				}
			}
		}
		die;
	}

	/**
	 * Enqueue admin scripts
	 */
	public function admin_scripts( $hook ) {
		if ( 'advanced-ads_page_advanced-ads-settings' == $hook ) {
			wp_enqueue_script( 'gasense/mapi/settings', GADSENSE_BASE_URL . 'admin/assets/js/mapi-settings.js', array( 'jquery' ), ADVADS_VERSION );
		}
	}

	/**
	 *  Sort ad units list alphabetically
	 */
	public static function get_sorted_adunits( $adunits ) {
		$units_sorted_by_name = array();
		$units_by_id          = array();
		foreach ( $adunits as $unit ) {
			$units_sorted_by_name[ $unit['name'] ] = $unit['id'];
			$units_by_id[ $unit['id'] ]            = $unit;
		}
		ksort( $units_sorted_by_name );
		$units_sorted_by_name = array_flip( $units_sorted_by_name );
		$results              = array();
		foreach ( $units_sorted_by_name as $id => $name ) {
			$results[ $name ] = $units_by_id[ $id ];
		}
		return $results;
	}

	/**
	 * Format ad type and size strings from Google for display
	 */
	public static function format_ad_data( $str = '', $format = 'type' ) {
		if ( 'type' == $format ) {
			$str = str_replace( '_', ', ', $str );
			$str = strtolower( $str );
			$str = ucwords( $str );
		} else {
			// size.
			$str = str_replace( 'SIZE_', '', $str );
			$str = str_replace( '_', 'x', $str );
			$str = strtolower( $str );
			$str = ucwords( $str );
		}
		return $str;
	}

	/**
	 * Check if the credential are the default ones or from user's app
	 */
	public static function use_user_app() {
		if ( ( defined( 'ADVANCED_ADS_MAPI_CID' ) && '' != ADVANCED_ADS_MAPI_CID ) && ( defined( 'ADVANCED_ADS_MAPI_CIS' ) && '' != ADVANCED_ADS_MAPI_CIS ) ) {
			return true;
		} else {
			return false;
		}
	}

	public static function has_token( $adsense_id = '' ) {
		if ( empty( $adsense_id ) ) {
			return false;
		}

		$has_token = false;
		$options   = self::get_option();
		if ( self::use_user_app() ) {
			if ( isset( $options['accounts'][ $adsense_id ] ) && ! empty( $options['accounts'][ $adsense_id ]['user_app']['refresh_token'] ) ) {
				$has_token = true;
			}
		} else {
			if ( isset( $options['accounts'][ $adsense_id ] ) && ! empty( $options['accounts'][ $adsense_id ]['default_app']['refresh_token'] ) ) {
				$has_token = true;
			}
		}
		return $has_token;

	}

	/**
	 * save token obtained from confirmation code
	 */
	public static function save_token_from_code( $token, $adsense_id ) {

		$options = self::get_option();
		$expires = time() + absint( $token['expires_in'] );
		if ( ! isset( $options['accounts'][ $adsense_id ] ) ) {
			$options['accounts'][ $adsense_id ] = self::$empty_account_data;
		}
		if ( self::use_user_app() ) {
			$options['accounts'][ $adsense_id ]['user_app'] = array(
				'access_token'  => $token['access_token'],
				'refresh_token' => $token['refresh_token'],
				'expires'       => $expires,
				'token_type'    => $token['token_type'],
			);
		} else {
			$options['accounts'][ $adsense_id ]['default_app'] = array(
				'access_token'  => $token['access_token'],
				'refresh_token' => $token['refresh_token'],
				'expires'       => $expires,
				'token_type'    => $token['token_type'],
			);
		}
		update_option( self::OPTNAME, $options );

	}

	/**
	 * Get the class's option
	 */
	public static function get_option() {
		$options = get_option( self::OPTNAME, array() );
		if ( ! is_array( $options ) ) {
			$options = array();
		}
		return $options + self::$default_options;
	}

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

<?php

/**
 * Provide public ajax interface.
 *
 * @since 1.5.0
 */
class Advanced_Ads_Ajax {

	private function __construct()
	{
		add_action( 'wp_ajax_advads_ad_select', array( $this, 'advads_ajax_ad_select' ) );
		add_action( 'wp_ajax_nopriv_advads_ad_select', array( $this, 'advads_ajax_ad_select' ) );
	}

	private static $instance;

	public static function get_instance()
	{
		if ( ! isset(self::$instance) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Simple wp ajax interface for ad selection.
	 */
	public function advads_ajax_ad_select() {
		// set proper header
		header( 'Content-Type: application/json; charset: utf-8' );

		// allow modules / add ons to test (this is rather late but should happen before anything important is called)
		do_action( 'advanced-ads-ajax-ad-select-init' );

		$adIds = isset( $_REQUEST['ad_ids'] ) ? $_REQUEST['ad_ids'] : null;
		if ( is_string( $adIds ) ) {
			$adIds = json_decode( $adIds, true );
		}
		if (is_array($adIds)) { // ads loaded previously and passed by query
			Advanced_Ads::get_instance()->current_ads += $adIds;
		}

		$deferedAds = isset( $_REQUEST['deferedAds'] ) ? $_REQUEST['deferedAds'] : null;
		if ( $deferedAds ) { // load all ajax ads with a single request
			$response = array();

			$requests_by_blog = array();
			foreach ( (array) $deferedAds as $request ) {
				$blog_id = isset( $request['blog_id'] ) ? $request['blog_id'] : get_current_blog_id();
				$requests_by_blog[ $blog_id ][] = $request;
			}
			foreach ( $requests_by_blog as $blog_id => $requests ) {
				if ( $blog_id !== get_current_blog_id() ) { Advanced_Ads::get_instance()->switch_to_blog( $blog_id ); }

				foreach ( $requests as $request ) {
					$result = $this->select_one( $request );
					$result['elementId'] = ! empty( $request['elementId'] ) ? $request['elementId'] : null;
					$response[] = $result;
				}

				if ( $blog_id !== get_current_blog_id() ) { Advanced_Ads::get_instance()->restore_current_blog(); }
			}

			echo json_encode( $response );
			die();
		}

		$response = $this->select_one( $_REQUEST );
		echo json_encode( $response );
		die();
	}

	/**
	 * Provides a single ad (ad, group, placement) given ID and selection method.
	 *
	 * @param $request array
	 */
	private function select_one( $request ) {
		// init handlers
		$selector = Advanced_Ads_Select::get_instance();
		$methods = $selector->get_methods();
		$method = isset( $request['ad_method'] ) ? (string) $request['ad_method'] : null;
		$id = isset( $request['ad_id'] ) ? (string) $request['ad_id'] : null;
		$arguments = isset( $request['ad_args'] ) ? $request['ad_args'] : array();
		if (is_string($arguments)) {
			$arguments = stripslashes($arguments);
			$arguments = json_decode($arguments, true);
		}
		if ( ! empty( $request['elementId'] ) ) {
			$arguments['cache_busting_elementid'] = $request['elementId'];
		}

		$response = array();
		if ( isset( $methods[ $method ] ) && isset( $id ) ) {
			$advads = Advanced_Ads::get_instance();
			$l = count( $advads->current_ads );

			// build content
			$content = $selector->get_ad_by_method( $id, $method, $arguments );
			$adIds = array_slice( $advads->current_ads, $l ); // ads loaded by this request

			return array( 'status' => 'success', 'item' => $content, 'id' => $id, 'method' => $method, 'ads' => $adIds, 'blog_id' => get_current_blog_id() );
		} else {
			// report error
			return array( 'status' => 'error', 'message' => 'No valid ID or METHOD found.' );
		}
	}
}

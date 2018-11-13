<?php

/**
 * Advanced Ads dfp Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2013-2018 Thomas Maier, webgilde GmbH
 *
 * Class containing information about the adsense ad type
 *
 * see also includes/class-ad-type-abstract.php for basic object
 *
 */
class Advanced_Ads_Ad_Type_Adsense extends Advanced_Ads_Ad_Type_Abstract {

	/**
	 * ID - internal type of the ad type
	 *
	 * must be static so set your own ad type ID here
	 * use slug like format, only lower case, underscores and hyphens
	 *
	 * @since 1.4
	 */
	public $ID = 'adsense';

	/**
	 * set basic attributes
	 *
	 * @since 1.4
	 */
	public function __construct() {
		$this->title = __( 'AdSense ad', 'advanced-ads' );
		$this->description = __( 'Use ads from your Google AdSense account', 'advanced-ads' );
		$this->parameters = array(
			'content' => ''
		);
	}

	/**
	 * output for the ad parameters metabox
	 *
	 * this will be loaded using ajax when changing the ad type radio buttons
	 * echo the output right away here
	 * name parameters must be in the "advanced_ads" array
	 *
	 * @param obj $ad ad object
	 * @since 1.4
	 */
	public function render_parameters($ad) {
		$options = $ad->options();

		$content = (string) ( isset( $ad->content ) ? $ad->content : '' );
		$unit_id = '';
		$unit_code = '';
		$unit_type = '';
		$unit_width = 0;
		$unit_height = 0;
		$json_content = '';
		$unit_resize = '';
		$extra_params = array(
			'default_width' => '',
			'default_height' => '',
			'at_media' => array(),
		);

		$db = Advanced_Ads_AdSense_Data::get_instance();
		$pub_id = trim( $db->get_adsense_id() );

		// check pub_id for errors
		$pub_id_errors = false;
		if( $pub_id !== '' && 0 !== strpos( $pub_id, 'pub-' )){
			$pub_id_errors = __( 'The Publisher ID has an incorrect format. (must start with "pub-")', 'advanced-ads' );
		}

		if ( trim($content) !== '' ) {

			$json_content = stripslashes( $content );

			// get json content striped by slashes
			$content = json_decode( stripslashes( $content ) );

			if ( isset($content->unitType) ) {
				$content->json = $json_content;
				$unit_type = $content->unitType;
				$unit_code = $content->slotId;
				$layout = isset( $content->layout ) ? $content->layout : '';
				$layout_key = isset( $content->layout_key ) ? $content->layout_key : '';
				
				if ( 'responsive' != $content->unitType && 'link-responsive' != $content->unitType && 'matched-content' != $content->unitType ) {
					// Normal ad unit
					$unit_width = $ad->width;
					$unit_height = $ad->height;
				} else {
					// Responsive && matched content
					$unit_resize = (isset($content->resize)) ? $content->resize : 'auto';
					if ( 'auto' != $unit_resize ) {
						$extra_params = apply_filters( 'advanced-ads-gadsense-ad-param-data', $extra_params, $content, $ad );
					}
				}
				if ( ! empty($pub_id) ) {
					$unit_id = 'ca-' . $pub_id . ':' . $unit_code;
				}
			}
		}

		if( '' === trim( $pub_id ) && '' !== trim( $unit_code ) ){
			$pub_id_errors = __( 'Your AdSense Publisher ID is missing.', 'advanced-ads' );
		}

		$default_template = GADSENSE_BASE_PATH . 'admin/views/adsense-ad-parameters.php';
		/**
		 * Inclusion of other UI template is done here. The content is passed in order to allow the inclusion of different
		 * templates file, depending of the ad. It's up to the developer to verify that $content is not an empty
		 * variable (which is the case for a new ad).
		 *
		 * Inclusion of .js and .css files for the ad creation/editon page are done by another hook. See
		 * 'advanced-ads-gadsense-ad-param-script' and 'advanced-ads-gadsense-ad-param-style' in "../admin/class-gadsense-admin.php".
		 */
		$template = apply_filters( 'advanced-ads-gadsense-ad-param-template', $default_template, $content );

		require $template;
	}

	/**
	 * sanitize content field on save
	 *
	 * @param str $content ad content
	 * @return str $content sanitized ad content
	 * @since 1.0.0
	 */
	public function sanitize_content($content = '') {
		return $content = wp_unslash( $content );
	}

	/**
	 * prepare the ads frontend output
	 *
	 * @param obj $ad ad object
	 * @return str $content ad content prepared for frontend output
	 * @since 1.0.0
	 */
	public function prepare_output($ad) {
		global $gadsense;

		$content = json_decode( stripslashes( $ad->content ) );
		
		if( isset( $ad->args['wp_the_query']['is_404'] ) 
			&& $ad->args['wp_the_query']['is_404'] 
			&& ! defined( 'ADVADS_ALLOW_ADSENSE_ON_404' ) ){
		    return '';
		}
		
		$output = '';
		$db = Advanced_Ads_AdSense_Data::get_instance();
		$pub_id = $db->get_adsense_id();
		$limit_per_page = $db->get_limit_per_page();

		if ( ! isset($content->unitType) || empty($pub_id) ) {
			return $output; }
		// deprecated since the adsbygoogle.js file is now always loaded
		if ( ! isset($gadsense['google_loaded']) || ! $gadsense['google_loaded'] ) {
			$gadsense['google_loaded'] = true;
		}

		//check if passive cb is used
		if ( isset( $gadsense['adsense_count'] ) ) {
			$gadsense['adsense_count']++;
		} else {
			$gadsense['adsense_count'] = 1;
		}

		if ( $limit_per_page && 3 < $gadsense['adsense_count'] && $ad->global_output ) {
			// The maximum allowed adSense ad per page count is 3 (according to the current Google AdSense TOS).
			return '';
		}

		$output = apply_filters( 'advanced-ads-gadsense-output', false, $ad, $pub_id, $content );
		if ( $output !== false ) {
			return $output;
		} elseif ( advads_is_amp() ) {
			// Prevent output on AMP pages.
			return '';
		}

		$output = '';

		// build static normal content ads first
		if ( ! in_array( $content->unitType, array( 'responsive', 'link-responsive', 'matched-content', 'in-article', 'in-feed' ) ) ) {
			$output .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' . "\n";
			$output .= '<ins class="adsbygoogle" ';
			$output .= 'style="display:inline-block;width:' . $ad->width . 'px;height:' . $ad->height . 'px;" ' . "\n";
			$output .= 'data-ad-client="ca-' . $pub_id . '" ' . "\n";
			$output .= 'data-ad-slot="' . $content->slotId . '"';
			// ad type for static link unit
			if( 'link' == $content->unitType ){
			    $output .= "\n" . 'data-ad-format="link"';
			}
			$output .= '></ins> ' . "\n";
			$output .= '<script> ' . "\n";
			$output .= '(adsbygoogle = window.adsbygoogle || []).push({}); ' . "\n";
			$output .= '</script>' . "\n";
		} else {
			/**
			 * The value of $ad->content->resize should be tested to format the output correctly
			 */
			$unmodified = $output;
			$output = apply_filters( 'advanced-ads-gadsense-responsive-output', $output, $ad, $pub_id );
			if ( $unmodified == $output ) {
				/**
				 * If the output has not been modified, perform a default responsive output.
				 * A simple did_action check isn't sufficient, some hooks may be attached and fired but didn't touch the output
				 */
				$this->append_defaut_responsive_content( $output, $pub_id, $content );

				// Remove float setting if this is a responsive ad unit without custom sizes.
				unset( $ad->wrapper['style']['float'] );
			}

		}


		return $output;
	}

	protected function append_defaut_responsive_content(&$output, $pub_id, $content) {
		$format = '';
		$style = 'display:block;';
		switch( $content->unitType ){
			case 'matched-content' : 
			    $format = 'autorelaxed';
			    break;
			case 'link-responsive' : 
			    $format = 'link';
			    break;
			case 'in-feed' : 
			    $format = 'fluid';
			    $layout = $content->layout;
			    $layout_key = $content->layout_key;
			    break;
			case 'in-article' : 
			    $format = 'fluid';
			    $layout = 'in-article';
			    $style = 'display:block; text-align:center;';
			    break;
			default :
			    $format = 'auto';
		}
			
		$output .= '<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>' . "\n";
		$output .= '<ins class="adsbygoogle" ';
		$output .= 'style="'. $style .'" ';
		$output .= 'data-ad-client="ca-' . $pub_id . '" ' . "\n";
		$output .= 'data-ad-slot="' . $content->slotId . '" ' . "\n";
		$output .= isset( $layout ) ? 'data-ad-layout="' . $layout . '"' . "\n" : '';
		$output .= isset( $layout_key ) ? 'data-ad-layout-key="' . $layout_key . '"' . "\n" : '';
		$output .= 'data-ad-format="';
		$output .= $format;
		$output .= '"></ins>' . "\n";
		$output .= '<script> ' . "\n";
		$output .= apply_filters( 'advanced-ads-gadsense-responsive-adsbygoogle', '(adsbygoogle = window.adsbygoogle || []).push({}); ' . "\n");
		$output .= '</script>' . "\n";
	}

}

<?php
/**
 * Advanced Ads Abstract Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2014 Thomas Maier, webgilde GmbH
 *
 * Class containing information that are defaults for all the other ad types
 *
 * see ad_type_content.php for an example on ad type
 *
 */
class Advanced_Ads_Ad_Type_Abstract {

	/**
	 * ID - internal type of the ad type
	 *
	 * must be static so set your own ad type ID here
	 * use slug like format, only lower case, underscores and hyphens
	 *
	 * @since 1.0.0
	 */
	public $ID = '';

	/**
	 * public title
	 *
	 * will be set within __construct so one can localize it
	 *
	 * @since 1.0.0
	 */
	public $title = '';

	/**
	 * description of the ad type
	 *
	 * will be set within __construct so one can localize it
	 *
	 * @since 1.0.0
	 */
	public $description = '';

	/**
	 * parameters of the ad
	 *
	 * defaults are set in construct
	 */
	public $parameters = array();

	/**
	 * set basic attributes
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// initiall
	}

	/**
	 * output for the ad parameters metabox
	 *
	 * @param obj $ad ad object
	 * @since 1.0.0
	 */
	public function render_parameters($ad){
		/**
		* this will be loaded by default or using ajax when changing the ad type radio buttons
		* echo the output right away here
		* name parameters must be in the "advanced_ads" array
		 */
	}

	/**
	 * sanitize ad parameters on save
	 *
	 * @param arr $parameters array with ad parameters
	 * @return arr $parameters sanitized ad parameters
	 * @since 1.0.0
	 */
	public function sanitize_parameters($parameters = array()){
		// no specific filter for content ad parameters, because there are no
		return $parameters;
	}

	/**
	 * sanitize content field on save
	 *
	 * @param str $content ad content
	 * @return str $content sanitized ad content
	 * @since 1.0.0
	 */
	public function sanitize_content($content = ''){

		// remove slashes from content
		return $content = wp_unslash( $content );
	}

	/**
	 * load content field for the ad
	 *
	 * @param obj $post WP post object
	 * @return str $content ad content
	 * @since 1.0.0
	 */
	public function load_content($post){

		return $post->post_content;
	}

	/**
	 * prepare the ads frontend output
	 *
	 * @param obj $ad ad object
	 * @return str $content ad content prepared for frontend output
	 * @since 1.0.0
	 */
	public function prepare_output($ad){
		return $ad->content;
	}

	/**
	 * Process shortcodes.
	 *
	 * @param str $output Ad content.
	 * @return obj Advanced_Ads_Ad
	 * @return bool force_aa Whether to force Advanced ads shortcodes processing.
	 */
	protected function do_shortcode( $output, Advanced_Ads_Ad $ad ) {
		$ad_options = $ad->options();

		if ( ! isset( $ad_options['output']['has_shortcode'] ) || $ad_options['output']['has_shortcode'] ) {
			// Store arguments so that shortcode hooks can access it.
			$ad_args = $ad->args;
			$ad_args['shortcode_ad_id'] = $ad->id;
			$output = preg_replace( '/\[(the_ad_group|the_ad_placement|the_ad)/', '[$1 ad_args="' . urlencode( json_encode( $ad_args ) )  . '"', $output );
		}

		$output = do_shortcode( $output );
		return $output;
	}

}

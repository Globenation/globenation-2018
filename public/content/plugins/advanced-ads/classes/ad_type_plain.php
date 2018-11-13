<?php
/**
 * Advanced Ads Plain Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2014 Thomas Maier, webgilde GmbH
 *
 * Class containing information about the plain text/code ad type
 *
 * see ad-type-content.php for a better sample on ad type
 *
 */
class Advanced_Ads_Ad_Type_Plain extends Advanced_Ads_Ad_Type_Abstract{

	/**
	 * ID - internal type of the ad type
	 *     *
	 * @since 1.0.0
	 */
	public $ID = 'plain';

	/**
	 * set basic attributes
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->title = __( 'Plain Text and Code', 'advanced-ads' );
		$this->description = __( 'Any ad network, Amazon, customized AdSense codes, shortcodes, and code like JavaScript, HTML or PHP.', 'advanced-ads' );
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
	 * @since 1.0.0
	 */
	public function render_parameters($ad){
		// load content
		$content = (isset($ad->content)) ? $ad->content : '';

		?><p class="description"><?php _e( 'Insert plain text or code into this field.', 'advanced-ads' ); ?></p>
	    <textarea id="advads-ad-content-plain" cols="40" rows="10" name="advanced_ad[content]"><?php echo esc_textarea( $content ); ?></textarea>
		<?php include ADVADS_BASE_PATH . 'admin/views/ad-info-after-textarea.php'; ?>
	    <input type="hidden" name="advanced_ad[output][allow_php]" value="0"/>
	    <?php $this->render_php_allow($ad);
	    $this->render_shortcodes_allow( $ad );
	}
	
	/**
	 * render php output field
	 * 
	 * @param $ad Advanced_Ads_Ad object
	 */
	public function render_php_allow( $ad ){
	    
		if( defined( 'ADVANCED_ADS_DISALLOW_PHP' ) ){
		    return;
		}
		
		$content = (isset($ad->content)) ? $ad->content : '';
	    
		// check if php is allowed
		if ( isset($ad->output['allow_php']) ){
			$allow_php = absint( $ad->output['allow_php'] );
		} else {
			/**
			 * for compatibility for ads with php added prior to 1.3.18
			 *  check if there is php code in the content
			 */
			if ( preg_match( '/\<\?php/', $content ) ){
				$allow_php = 1;
			} else {
				$allow_php = 0;
			}
		}
		?>
	    <label class="label" for="advads-parameters-php"><?php _e( 'Allow PHP', 'advanced-ads' ); ?></label>
	    <div>
	    <input id="advads-parameters-php" type="checkbox" name="advanced_ad[output][allow_php]" value="1" <?php checked( 1, $allow_php ); ?>/><?php _e( 'Execute PHP code (wrapped in <code>&lt;?php ?&gt;</code>)', 'advanced-ads' ); 
	    ?></div><hr/><?php
	    
	}

	/**
	 * Render allow shortcodes field.
	 *
	 * @param $ad Advanced_Ads_Ad object
	 */
	public function render_shortcodes_allow( $ad ){
		$allow_shortcodes = ! empty( $ad->output['allow_shortcodes'] );
		?>
		<label class="label" for="advads-parameters-shortcodes"><?php _e( 'Allow shortcodes', 'advanced-ads' ); ?></label>
		<div>
		<input id="advads-parameters-shortcodes" type="checkbox" name="advanced_ad[output][allow_shortcodes]" value="1" <?php
		checked( 1, $allow_shortcodes ); ?>/><?php _e( 'Execute shortcodes', 'advanced-ads' );
		?></div><hr/><?php
	}

	/**
	 * prepare the ads frontend output
	 *
	 * @param obj $ad ad object
	 * @return str $content ad content prepared for frontend output
	 * @since 1.0.0
	 */
	public function prepare_output($ad){

		// evaluate the code as php if setting was never saved or is allowed
		if ( ! defined( 'ADVANCED_ADS_DISALLOW_PHP' ) && ( ! isset($ad->output['allow_php']) || $ad->output['allow_php'] ) ){
			ob_start();
			eval('?>'.$ad->content);
			$content = ob_get_clean();
		} else {
			$content = $ad->content;
		}

		if ( ! empty( $ad->output['allow_shortcodes'] ) ) {
			$content = $this->do_shortcode( $content, $ad );
		}

		return $content;
	}

}

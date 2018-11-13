<?php
/**
 * Advanced Ads Image Ad Type
 *
 * @package   Advanced_Ads
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2015 Thomas Maier, webgilde GmbH
 * @since     1.6.10
 *
 * Class containing information about the content ad type
 * this should also work as an example for other ad types
 *
 */
class Advanced_Ads_Ad_Type_Image extends Advanced_Ads_Ad_Type_Abstract{

	/**
	 * ID - internal type of the ad type
	 *
	 * must be static so set your own ad type ID here
	 * use slug like format, only lower case, underscores and hyphens
	 *
	 * @since 1.6.10
	 */
	public $ID = 'image';

	/**
	 * set basic attributes
	 *
	 * @since 1.6.10
	 */
	public function __construct() {
		$this->title = __( 'Image Ad', 'advanced-ads' );
		$this->description = __( 'Ads in various image formats.', 'advanced-ads' );
		$this->parameters = array(
			'image_url' => '',
			'image_title' => '',
			'image_alt' => '',
		);
	}

	/**
	 * output for the ad parameters metabox
	 *
	 * @param obj $ad ad object
	 * @since 1.6.10
	 */
	public function render_parameters($ad){
		// load tinymc content exitor
		$id = ( isset( $ad->output['image_id'] ) ) ? $ad->output['image_id'] : '';
		$url =	    ( isset( $ad->url ) ) ? esc_url( $ad->url ) : '';

		?><p><button href="#" class="advads_image_upload button button-secondary" type="button" data-uploader-title="<?php
		    _e( 'Insert File', 'advanced-ads' ); ?>" data-uploader-button-text="<?php _e( 'Insert', 'advanced-ads' ); ?>" onclick="return false;"><?php _e( 'select image', 'advanced-ads' ); ?></button>
		    <a id="advads-image-edit-link" href="<?php if( $id ){ echo get_edit_post_link( $id ); } ?>"><?php _e('edit', 'advanced-ads' ); ?></a>
		</p>
		<input type="hidden" name="advanced_ad[output][image_id]" value="<?php echo $id; ?>" id="advads-image-id"/>
		<div id="advads-image-preview">
		    <?php $this->create_image_tag( $id, $ad ); ?>
		</div>

		<?php // don’t show if tracking plugin enabled
		if( ! defined( 'AAT_VERSION' )) : ?>
			<span class="label"><?php _e( 'URL', 'advanced-ads' ); ?></span>
			<div>
				<input type="url" name="advanced_ad[url]" id="advads-url" class="advads-ad-url" value="<?php echo $url; ?>" placeholder="<?php _e( 'Link to target site', 'advanced-ads' ); ?>" /></p>
			</div><hr/><?php 
		endif;
	}

	/**
	 * render image tag
	 *
	 * @param int $attachment_id	post id of the image
	 * @param obj $ad		ad object, since 1.8.21
	 * @since 1.6.10
	 */
	public function create_image_tag( $attachment_id, $ad ){

		$image = wp_get_attachment_image_src( $attachment_id, 'full' );
		$style = '';
		
		if ( $image ) {
			list( $src, $width, $height ) = $image;
			// override image sizes with the sizes given in ad options, but in frontend only
			if( ! is_admin() || (  // is frontend
				is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) ){ // is AJAX call (cache-busting)
				$width  = isset( $ad->width ) ? absint( $ad->width ) : $width;
				$height = isset( $ad->height ) ? absint( $ad->height ) : $height;
			}
			$hwstring = image_hwstring($width, $height);
			$attachment = get_post($attachment_id);
			$alt = trim(esc_textarea( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ));
			
			global $wp_current_filter;
			
			$more_attributes = $srcset = $sizes = '';
			// create srcset and sizes attributes if we are in the the_content filter and in WordPress 4.4
			if( isset( $wp_current_filter ) 
				&& in_array( 'the_content', $wp_current_filter ) 
				&& ! defined( 'ADVADS_DISABLE_RESPONSIVE_IMAGES' )){
				if( function_exists( 'wp_get_attachment_image_srcset' ) ){
					$srcset = wp_get_attachment_image_srcset( $attachment_id, 'full' );
				}
				if( function_exists( 'wp_get_attachment_image_sizes' ) ){
					$sizes = wp_get_attachment_image_sizes( $attachment_id, 'full' );
				}
				if ( $srcset && $sizes ) {
					$more_attributes .= ' srcset=\'' . $srcset . '\' sizes=\'' . $sizes . '\'';
				}
			}
			
			// add css rule to be able to center the ad
			if( isset( $ad->output['position'] ) && 'center' === $ad->output['position'] ){
			    $style .= 'display: inline-block;';
			}
			
			$style = apply_filters( 'advanced-ads-ad-image-tag-style', $style );
			$style = '' !== $style ? 'style="' . $style . '"' : '';
			
			$more_attributes = apply_filters( 'advanced-ads-ad-image-tag-attributes', $more_attributes );
			
			echo rtrim("<img $hwstring") . " src='$src' alt='$alt' $more_attributes $style/>";
		}
	}
	
	/**
	 * render image icon for overview pages
	 *
	 * @param int $attachment_id post id of the image
	 * @since 1.7.4
	 */
	public function create_image_icon( $attachment_id ){

		$image = wp_get_attachment_image_src( $attachment_id, 'medium', true );
		if ( $image ) {
			list( $src, $width, $height ) = $image;
			
			// scale down width or height to max 100px
			if( $width > $height ){
			    $height = absint( $height / ( $width / 100 ) );
			    $width = 100;
			} else {
			    $width = absint( $width / ( $height / 100 ) );
			    $height = 100;
			}
			
			$hwstring = trim( image_hwstring($width, $height) );
			$attachment = get_post($attachment_id);
			$alt = trim(strip_tags( get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ));
			
			echo "<img $hwstring src='$src' alt='$alt' />";
		}
	}

	/**
	 * prepare the ads frontend output by adding <object> tags
	 *
	 * @param obj $ad ad object
	 * @return str $content ad content prepared for frontend output
	 * @since 1.6.10.
	 */
	public function prepare_output($ad){

		$id = ( isset( $ad->output['image_id'] ) ) ? absint( $ad->output['image_id'] ) : '';
		$url =	    ( isset( $ad->url ) ) ? esc_url( $ad->url ) : '';
		// get general target setting
		$options = Advanced_Ads::get_instance()->options();
		$target_blank =	!empty( $options['target-blank'] ) ? ' target="_blank"' : '';

		ob_start();
		if( ! defined( 'AAT_VERSION' ) && $url ){ echo '<a href="'. $url .'"'.$target_blank.'>'; }
		echo $this->create_image_tag( $id, $ad );
		if( ! defined( 'AAT_VERSION' ) && $url ){ echo '</a>'; }

		return ob_get_clean();
	}
	
	/**
	 * Generate a string with the original image size for output in the backend
	 * Only show, if different from entered image sizes
	 * 
	 * @param   obj	$ad Advanced_Ads_Ad
	 * @return  str	empty, if the entered size is the same as the original size
	 */
	public static function show_original_image_size( $ad ){

		$attachment_id = ( isset( $ad->output['image_id'] ) ) ? absint( $ad->output['image_id'] ) : '';
		
		$image = wp_get_attachment_image_src( $attachment_id, 'full' );
		
		if ( $image ) {
			list( $src, $width, $height ) = $image;
			?><p class="description"><?php if( ( isset( $ad->width ) && $ad->width != $width ) 
				|| ( isset( $ad->height ) && $ad->height != $height ) ) {
			printf( 
				/**
				 * translators: $s is a size string like "728 x 90". 
				 * This string shows up on the ad edit page of image ads if the size entered for the ad is different from the size of the uploaded image.
				 */
				esc_attr__( 'Original size: %s', 'advanced-ads' ), $width . '&nbsp;x&nbsp;' . $height ); ?></p><?php
			}
		}
		
		return '';
		
	}

}

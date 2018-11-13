<?php

/**
 * visitor conditions under which to (not) show an ad
 *
 * @since 1.5.4
 *
 */
class Advanced_Ads_Visitor_Conditions {

	/**
	 *
	 * @var Advanced_Ads_Visitor_Conditions
	 */
	protected static $instance;

	/**
	 * registered visitor conditions
	 */
	public $conditions;

	/**
	 * start of name in form elements
	 */
	const FORM_NAME = 'advanced_ad[visitors]';

	public function __construct() {

	    // register conditions
	    $this->conditions = apply_filters( 'advanced-ads-visitor-conditions', array(
			'mobile' => array( // type of the condition
				'label' => __( 'device', 'advanced-ads' ),
				'description' => __( 'Display ads only on mobile devices or hide them.', 'advanced-ads' ),
				'metabox' => array( 'Advanced_Ads_Visitor_Conditions', 'mobile_is_or_not' ), // callback to generate the metabox
				'check' => array( 'Advanced_Ads_Visitor_Conditions', 'check_mobile' ), // callback for frontend check
				'helplink' => ADVADS_URL . 'manual/display-ads-either-on-mobile-or-desktop/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor-mobile' // link to help section
			),
			'loggedin' => array(
				'label' => __( 'logged in visitor', 'advanced-ads' ),
				'description' => __( 'Whether the visitor has to be logged in or not in order to see the ads.', 'advanced-ads' ),
				'metabox' => array( 'Advanced_Ads_Visitor_Conditions', 'metabox_is_or_not' ), // callback to generate the metabox
				'check' => array( 'Advanced_Ads_Visitor_Conditions', 'check_logged_in' ) // callback for frontend check
			),
	    ));
	}

	/**
	 *
	 * @return Advanced_Ads_Plugin
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
	
	
	/**
	 * get the conditions array alphabetically by label
	 * 
	 * @since 1.8.12
	 */
	public function get_conditions(){
		uasort( $this->conditions, 'Advanced_Ads_Admin::sort_condition_array_by_label' );
		
		return $this->conditions;
	}	

	/**
	 * callback to render the mobile condition using the "is not" condition
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function mobile_is_or_not( $options, $index = 0 ){
	    
	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'is';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		<option value="is" <?php selected( 'is', $operator ); ?>><?php _e( 'Mobile (including tablets)', 'advanced-ads' ); ?></option>
		<option value="is_not" <?php selected( 'is_not', $operator ); ?>><?php _e( 'Desktop', 'advanced-ads' ); ?></option>
	    </select>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; 
	    if( isset( $type_options[ $options['type'] ]['helplink'] ) ) : ?>
	    <a href="<?php echo $type_options[ $options['type'] ]['helplink']; ?>" target="_blank"><?php 
		    _e( 'Manual and Troubleshooting', 'advanced-ads' );
	    ?></a><?php endif; ?></p><?php

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    if( ! defined( 'AAR_SLUG' ) ){
		    echo '<p>' . sprintf(__( 'Display ads by the available space on the device or target tablets with the <a href="%s" target="_blank">Responsive add-on</a>', 'advanced-ads' ), ADVADS_URL . 'add-ons/responsive-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor-responsive') . '</p>';
	    }
	}
	
	/**
	 * callback to display the "is not" condition
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_is_or_not( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'is';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		<option value="is" <?php selected( 'is', $operator ); ?>><?php _e( 'is', 'advanced-ads' ); ?></option>
		<option value="is_not" <?php selected( 'is_not', $operator ); ?>><?php _e( 'is not', 'advanced-ads' ); ?></option>
	    </select>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; 
	    if( isset( $type_options[ $options['type'] ]['helplink'] ) ) : ?>
	    <a href="<?php echo $type_options[ $options['type'] ]['helplink']; ?>" target="_blank"><?php 
		    _e( 'Manual and Troubleshooting', 'advanced-ads' );
	    ?></a><?php endif; ?></p><?php
	}

	/**
	 * callback to display the any condition based on a number
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_number( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $value = isset( $options['value'] ) ? $options['value'] : 0;
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'is_equal';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		    <option value="is_equal" <?php selected( 'is_equal', $operator ); ?>><?php _e( 'equal', 'advanced-ads' ); ?></option>
		    <option value="is_higher" <?php selected( 'is_higher', $operator ); ?>><?php _e( 'equal or higher', 'advanced-ads' ); ?></option>
		    <option value="is_lower" <?php selected( 'is_lower', $operator ); ?>><?php _e( 'equal or lower', 'advanced-ads' ); ?></option>
		</select><input type="number" name="<?php echo $name; ?>[value]" value="<?php echo absint( $value ); ?>"/>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; ?></p><?php
	}

	/**
	 * callback to display the any condition based on a number
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_string( $options, $index = 0 ){

	    if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

	    $type_options = self::get_instance()->conditions;

	    if ( ! isset( $type_options[ $options['type'] ] ) ) {
		    return;
	    }

	    // form name basis
	    $name = self::FORM_NAME . '[' . $index . ']';

	    // options
	    $value = isset( $options['value'] ) ? $options['value'] : '';
	    $operator = isset( $options['operator'] ) ? $options['operator'] : 'contains';

	    ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
                <div class="advads-condition-line-wrap">
                    <?php include( ADVADS_BASE_PATH . 'admin/views/ad-conditions-string-operators.php' ); ?>
		    <input type="text" name="<?php echo $name; ?>[value]" value="<?php echo $value; ?>"/>
                </div>
	    <p class="description"><?php echo $type_options[ $options['type'] ]['description']; ?></p><?php
	}

	/**
	 * controls frontend checks for conditions
	 *
	 * @param arr $options options of the condition
	 * @param ob $ad Advanced_Ads_Ad
	 * @return bool false, if ad can’t be delivered
	 */
	static function frontend_check( $options = array(), $ad = false ){
		$visitor_conditions = Advanced_Ads_Visitor_Conditions::get_instance()->conditions;

		if ( is_array( $options ) && isset( $visitor_conditions[ $options['type'] ]['check'] ) ) {
			$check = $visitor_conditions[ $options['type'] ]['check'];
		} else {
			return true;
		}

		// call frontend check callback
		if ( method_exists( $check[0], $check[1] ) ) {
			return call_user_func( array( $check[0], $check[1] ), $options, $ad );
		}

		return true;
	}
	
	/**
	 * render connector option
	 * 
	 * @since 1.7.0.4
	 * @param int $index
	 */
	static function render_connector_option( $index = 0, $value = 'or' ){

	    $label = ( $value === 'or' ) ? __( 'or', 'advanced-ads' ) : __( 'and', 'advanced-ads' );

	    return '<input type="checkbox" name="' . self::FORM_NAME . '[' . $index . '][connector]' . '" value="or" id="advads-visitor-conditions-' . 
		    $index . '-connector"' .
		    checked( 'or', $value, false ) 
		    .'><label for="advads-visitor-conditions-' . $index . '-connector">' . $label . '</label>';
	}	

	/**
	 * check mobile visitor condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_mobile( $options = array() ){

	    if ( ! isset( $options['operator'] ) ) {
			return true;
	    }

	    switch ( $options['operator'] ){
		    case 'is' :
			    if ( ! wp_is_mobile() ) { return false; }
			    break;
		    case 'is_not' :
			    if ( wp_is_mobile() ) { return false; }
			    break;
	    }

	    return true;
	}

	/**
	 * check mobile visitor condition in frontend
	 *
	 * @since 1.6.3
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_logged_in( $options = array() ){

	    if ( ! isset( $options['operator'] ) ) {
			return true;
	    }

	    switch ( $options['operator'] ){
		    case 'is' :
			    if ( ! is_user_logged_in() ) { return false; }
			    break;
		    case 'is_not' :
			    if ( is_user_logged_in() ) { return false; }
			    break;
	    }

	    return true;
	}

	/**
	 * helper for check with strings
	 *
	 * @since 1.6.3
	 * @param str $string string that is going to be checked
	 * @return bool true if ad can be displayed
	 */
	static function helper_check_string( $string = '', $options = array() ){

		if ( ! isset( $options['operator'] ) || ! isset( $options['value'] ) || '' === $options['value'] ){
			return true;
		}

		$operator = $options['operator'];
		$value = $options['value'];

		// check the condition by mode and bool
		$condition = true;
		switch ( $operator ){
			// referrer contains string on any position
			case 'contain' :
				$condition = stripos( $string, $value ) !== false;
				break;
			// referrer does not contain string on any position
			case 'contain_not' :
				$condition = stripos( $string, $value ) === false;
				break;
			// referrer starts with the string
			case 'start' :
				$condition = stripos( $string, $value ) === 0;
				break;
			// referrer does not start with the string
			case 'start_not' :
				$condition = stripos( $string, $value ) !== 0;
				break;
			// referrer ends with the string
			case 'end' :
				$condition = $value === substr( $string, -strlen( $value ) );
				break;
			// referrer does not end with the string
			case 'end_not' :
				$condition = $value !== substr( $string, -strlen( $value ) );
				break;
			// referrer is equal to the string
			case 'match' :
				// strings do match, but should not or not match but should
				$condition = strcasecmp($value, $string) === 0;
				break;
			// referrer is not equal to the string
			case 'match_not' :
				// strings do match, but should not or not match but should
				$condition = strcasecmp($value, $string) !== 0;
				break;
			// string is a regular expression
			case 'regex' :
				// check regular expression first
				if( @preg_match( $value, null ) === false ){
					Advanced_Ads::log( "Advanced Ads: regular expression '$value' in visitor condition is broken." );
				} else {
					$condition = preg_match( $value, $string );
				}
				break;
			// string is not a regular expression
			case 'regex_not' :
				if( @preg_match( $value, null ) === false ){
					Advanced_Ads::log( "Advanced Ads: regular expression '$value' in visitor condition is broken." );
				} else {
					$condition = ! preg_match( $value, $string );
				}
				break;
		}

		return $condition;
	}
}

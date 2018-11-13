<?php

/**
 * Display Conditions under which to (not) show an ad
 *
 * @since 1.7
 *
 */
class Advanced_Ads_Display_Conditions {

    /**
     *
     * @var Advanced_Ads_Display_Conditions
     */
    protected static $instance;

    /**
     * registered display conditions
     */
    public $conditions;

    /**
     * start of name in form elements
     */
    const FORM_NAME = 'advanced_ad[conditions]';

    protected static $query_var_keys = array(
	// 'is_single',
	'is_archive',
	'is_search',
	'is_home',
	'is_404',
	'is_attachment',
	'is_singular',
	'is_front_page',
	'is_feed'
    );
    
    // this is how the general conditions should look like by default
    protected static $default_general_keys = array(
	'is_front_page',
	'is_singular',
	'is_archive',
	'is_search',
	'is_404',
	'is_attachment',
	'is_main_query',
	'is_feed'
    );

    private function __construct() {

	// register filter
	add_filter('advanced-ads-ad-select-args', array($this, 'ad_select_args_callback'));
	add_filter('advanced-ads-can-display', array($this, 'can_display'), 10, 2);

	// register conditions with init hook, register as late as possible so other plugins can use the same hook to add new taxonomies
	add_action( 'init', array($this, 'register_conditions'), 100 );
    }
    
    /**
     * register display conditions
     * 
     * @since 1.7.1.4
     */
    public function register_conditions(){
	$conditions = array(
	    'posttypes' => array(// post types condition
		'label' => __('post type', 'advanced-ads'),
		'description' => __('Choose the public post types on which to display the ad.', 'advanced-ads'),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_post_type'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_post_type'), // callback for frontend check
	    // 'helplink' => ADVADS_URL . 'manual/display-ads-either-on-mobile-or-desktop/#utm_source=advanced-ads&utm_medium=link&utm_campaign=edit-visitor-mobile' // link to help section
	    ),
	    'postids' => array(// post id condition
		'label' => __('specific pages', 'advanced-ads'),
		'description' => __('Choose on which individual posts, pages and public post type pages you want to display or hide ads.', 'advanced-ads'),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_post_ids'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_post_ids'), // callback for frontend check
	    ),
	    'general' => array(// general conditions
		'label' => __('general conditions', 'advanced-ads'),
		// 'description' => __( 'Choose on which individual posts, pages and public post type pages you want to display or hide ads.', 'advanced-ads' ),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_general'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_general'), // callback for frontend check
	    ),
	    'author' => array(// author conditions
		'label' => __('author', 'advanced-ads'),
		// 'description' => __( 'Choose on which individual posts, pages and public post type pages you want to display or hide ads.', 'advanced-ads' ),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_author'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_author'), // callback for frontend check
	    ),
	    /**
	     * display ads only in content older or younger than a specific age
	     */
	    'content_age' => array(
		    'label' => __( 'content age', 'advanced-ads' ),
		    'description' => __( 'Display ads based on age of the page.', 'advanced-ads' ),
		    'metabox' => array( 'Advanced_Ads_Display_Conditions', 'metabox_content_age' ), // callback to generate the metabox
		    'check' => array( 'Advanced_Ads_Display_Conditions', 'check_content_age' ) // callback for frontend check
	    ),
	    /**
	     * condition for taxonomies in general
	     */
	    'taxonomy' => array(
		    'label' => __( 'taxonomy', 'advanced-ads' ),
		    'description' => __( 'Display ads based on the taxonomy of an archive page.', 'advanced-ads' ),
		    'metabox' => array( 'Advanced_Ads_Display_Conditions', 'metabox_taxonomies' ), // callback to generate the metabox
		    'check' => array( 'Advanced_Ads_Display_Conditions', 'check_taxonomy' ) // callback for frontend check
	    ),
	);
	
	// register a condition for each taxonomy for posts.
	$taxonomies = get_taxonomies(array('public' => true, 'publicly_queryable' => true), 'objects', 'or');

	$tax_label_counts = array();
	
	foreach ($taxonomies as $_tax) :
	    if ( in_array( $_tax->name, array( 'advanced_ads_groups' ) ) ) {
		    continue;
	    }
	    
	    /**
	     * Count names of taxonomies and adjust label if there are duplicates.
	     * we can’t use `array_count_values` here because "label" might not always be a simple string (though it should be)
	     */
	    $tax_label_counts[ $_tax->label ] = isset( $tax_label_counts[ $_tax->label ] ) ? $tax_label_counts[ $_tax->label ] + 1 : $tax_label_counts[ $_tax->label ] = 1;
	    
	    // add tax type to label if we find it multiple times.
	    if ( $tax_label_counts[ $_tax->label ] < 2 ) {
		$label = $_tax->label;
		$archive_label = $_tax->labels->singular_name;
	    } else {
		$label = sprintf( '%s (%s)', $_tax->label, $_tax->name );
		$archive_label = sprintf( '%s (%s)', $_tax->labels->singular_name, $_tax->name );
	    }

	    $conditions['taxonomy_' . $_tax->name] = array(
		'label' => $label,
		// 'description' => sprintf(__( 'Choose terms from the %s taxonomy a post must belong to for showing or hiding ads.', 'advanced-ads' ), $_tax->label ),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_taxonomy_terms'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_taxonomies'), // callback for frontend check
		'taxonomy' => $_tax->name, // unique for this type: the taxonomy name
	    );

	    $conditions['archive_' . $_tax->name] = array(
		'label' => sprintf(__('archive: %s', 'advanced-ads'), $archive_label),
		// 'description' => sprintf(__( 'Choose on which %s archive page ads are hidden or displayeds.', 'advanced-ads' ), $_tax->label ),
		'metabox' => array('Advanced_Ads_Display_Conditions', 'metabox_taxonomy_terms'), // callback to generate the metabox
		'check' => array('Advanced_Ads_Display_Conditions', 'check_taxonomy_archive'), // callback for frontend check
		'taxonomy' => $_tax->name, // unique for this type: the taxonomy name
	    );
	endforeach;
	
	$this->conditions = apply_filters('advanced-ads-display-conditions', $conditions);
    }

    /**
     *
     * @return Advanced_Ads_Plugin
     */
    public static function get_instance() {
	// If the single instance hasn't been set, set it now.
	if (null === self::$instance) {
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
     * controls frontend checks for conditions
     *
     * @param arr $options options of the condition
     * @param ob $ad Advanced_Ads_Ad
     * @return bool false, if ad can’t be delivered
     */
    static function frontend_check($options = array(), $ad = false) {
	$display_conditions = Advanced_Ads_Display_Conditions::get_instance()->conditions;

	if (is_array($options) && isset( $options['type'] ) && isset($display_conditions[$options['type']]['check'])) {
	    $check = $display_conditions[$options['type']]['check'];
	} else {
	    return true;
	}

	// call frontend check callback
	if (method_exists($check[0], $check[1])) {
	    return call_user_func(array($check[0], $check[1]), $options, $ad);
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
	
	return '<input style="display:none;" type="checkbox" name="' . self::FORM_NAME . '[' . $index . '][connector]' . '" value="or" id="advads-conditions-' . 
		$index . '-connector"' .
		checked( 'or', $value, false ) 
		.'><label for="advads-conditions-' . $index . '-connector">' . $label . '</label>';
    }

    /**
     * callback to display the metabox for the post type condition
     *
     * @param arr $options options of the condition
     * @param int $index index of the condition
     */
    static function metabox_post_type($options, $index = 0) {
	if (!isset($options['type']) || '' === $options['type']) {
	    return;
	}

	$type_options = self::get_instance()->conditions;

	if (!isset($type_options[$options['type']])) {
	    return;
	}

	// get values and select operator based on previous settings
	$operator = ( isset($options['operator']) && $options['operator'] === 'is_not' ) ? 'is_not' : 'is';
	$values = ( isset($options['value'] ) && is_array( $options['value'] ) ) ? $options['value'] : array();

	// form name basis
	$name = self::FORM_NAME . '[' . $index . ']';

	// options
	?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
	<select name="<?php echo $name; ?>[operator]">
	    <option value="is" <?php selected('is', $operator); ?>><?php _e('is', 'advanced-ads'); ?></option>
	    <option value="is_not" <?php selected('is_not', $operator); ?>><?php _e('is not', 'advanced-ads' ); ?></option>
	</select><?php

	// set defaults
	$post_types = get_post_types(array('public' => true, 'publicly_queryable' => true), 'object', 'or');
	?><div class="advads-conditions-single advads-buttonset"><?php
	$type_label_counts = array_count_values( wp_list_pluck( $post_types, 'label' ) );

	foreach ($post_types as $_type_id => $_type) {
	    if ( in_array($_type_id, $values)) {
		$_val = 1;
	    } else {
		$_val = 0;
	    }

	    if ( $type_label_counts[ $_type->label ] < 2 ) {
		$_label = $_type->label;
	    } else {
		$_label = sprintf( '%s (%s)', $_type->label, $_type_id );
	    }
	    ?><label class="button" for="advads-conditions-<?php echo $index; ?>-<?php echo $_type_id;
	    ?>"><?php echo $_label ?></label><input type="checkbox" id="advads-conditions-<?php echo $index; ?>-<?php echo $_type_id; ?>" name="<?php echo $name; ?>[value][]" <?php checked($_val, 1); ?> value="<?php echo $_type_id; ?>"><?php
	    }
	    ?><p class="advads-conditions-not-selected advads-error-message"><?php _ex( 'Please select some items.', 'Error message shown when no display condition term is selected', 'advanced-ads' ); ?></p></div><?php
    }

	       /**
		* callback to display the metabox for the author condition
		*
		* @param arr $options options of the condition
		* @param int $index index of the condition
		*/
	       static function metabox_author($options, $index = 0) {

		   if (!isset($options['type']) || '' === $options['type']) {
		       return;
		   }

		   $type_options = self::get_instance()->conditions;

		   if (!isset($type_options[$options['type']])) {
		       return;
		   }

		   // get values and select operator based on previous settings
		   $operator = ( isset($options['operator']) && $options['operator'] === 'is_not' ) ? 'is_not' : 'is';
		   $values = ( isset($options['value']) && is_array($options['value']) ) ? $options['value'] : array();

		   // form name basis
		   $name = self::FORM_NAME . '[' . $index . ']';
		   ?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
	<select name="<?php echo $name; ?>[operator]">
	    <option value="is" <?php selected('is', $operator); ?>><?php _e('is', 'advanced-ads'); ?></option>
	    <option value="is_not" <?php selected('is_not', $operator); ?>><?php _e('is not', 'advanced-ads'); ?></option>
	</select><?php
		    // set defaults
		    $max_authors = absint( apply_filters( 'advanced-ads-admin-max-terms', 50 ) );
		    $authors = get_users(array('who' => 'authors', 'orderby' => 'nicename', 'number' => $max_authors ) );
		   ?><div class="advads-conditions-single advads-buttonset"><?php
	foreach ($authors as $_author) {
	    if ( in_array($_author->ID, $values ) ) {
		$_val = 1;
	    } else {
		$_val = 0;
	    }
	    ?><label class="button ui-button" for="advads-conditions-<?php echo $index; ?>-<?php echo $_author->ID;
	    ?>"><?php echo $_author->display_name; ?></label><input type="checkbox" id="advads-conditions-<?php echo $index; ?>-<?php echo $_author->ID; ?>" name="<?php echo $name; ?>[value][]" <?php checked($_val, 1); ?> value="<?php echo $_author->ID; ?>"><?php
	}
	?><p class="advads-conditions-not-selected advads-error-message"><?php _ex( 'Please select some items.', 'Error message shown when no display condition term is selected', 'advanced-ads' ); ?></p></div>
	<?php if( count( $authors ) >= $max_authors ) : ?><p class="advads-error-message"><?php printf( __( 'Only %d elements are displayed above. Use the <code>advanced-ads-admin-max-terms</code> filter to change this limit according to <a href="%s" target="_blank">this page</a>.', 'advanced-ads' ), $max_authors, ADVADS_URL . 'codex/filter-hooks//#utm_source=advanced-ads&utm_medium=link&utm_campaign=author-term-limit' ); ?></p><?php endif; 
    }

	/**
	 * callback to display the metabox for the taxonomy archive pages
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_taxonomy_terms($options, $index = 0) {

		if (!isset($options['type']) || '' === $options['type']) {
		    return;
		}

		$type_options = self::get_instance()->conditions;

		// don’t use if this is not a taxonomy
		if (!isset($type_options[$options['type']]) || !isset($type_options[$options['type']]['taxonomy'])) {
		    return;
		}

		$taxonomy = get_taxonomy($type_options[$options['type']]['taxonomy']);
		if (false == $taxonomy) {
		    return;
		}

		// get values and select operator based on previous settings
		$operator = ( isset($options['operator']) && $options['operator'] === 'is_not' ) ? 'is_not' : 'is';
		$values = ( isset($options['value']) && is_array($options['value']) ) ? $options['value'] : array();

		// limit the number of terms so many terms don’t break the admin page
		$max_terms = absint(apply_filters('advanced-ads-admin-max-terms', 50));

		// form name basis
		$name = self::FORM_NAME . '[' . $index . ']';
		?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		    <option value="is" <?php selected('is', $operator); ?>><?php _e('is', 'advanced-ads'); ?></option>
		    <option value="is_not" <?php selected('is_not', $operator); ?>><?php _e('is not', 'advanced-ads'); ?></option>
		</select><?php
			   ?><div class="advads-conditions-single advads-buttonset"><?php
		self::display_term_list($taxonomy, $values, $name . '[value][]', $max_terms, $index);
		?></div><?php
	}
	
	/**
	 * callback to display the metabox for the taxonomies
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_taxonomies($options, $index = 0) {

		if (!isset($options['type']) || '' === $options['type']) {
		    return;
		}

		$taxonomies = get_taxonomies( array( 'public' => 1 ), 'objects' );
		
		$name = self::FORM_NAME . '[' . $index . ']';
		
		// get values and select operator based on previous settings
		$operator = ( isset($options['operator']) && $options['operator'] === 'is_not' ) ? 'is_not' : 'is';
		$values = ( isset($options['value']) && is_array($options['value']) ) ? $options['value'] : array();

		?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<div class="advads-conditions-single advads-buttonset"><?php
		$tax_label_counts = array_count_values( wp_list_pluck( $taxonomies, 'label' ) );

		foreach ($taxonomies as $_taxonomy_id => $_taxonomy) :
		    		    
		    if ( in_array($_taxonomy_id, $values)) {
			$_val = 1;
		    } else {
			$_val = 0;
		    }

		    if ( $tax_label_counts[ $_taxonomy->label ] < 2 ) {
			$_label = $_taxonomy->label;
		    } else {
			$_label = sprintf( '%s (%s)', $_taxonomy->label, $_taxonomy_id );
		    }
		    ?><label class="button" for="advads-conditions-<?php echo $index; ?>-<?php echo $_taxonomy_id;
		    ?>"><?php echo $_label ?></label><input type="checkbox" id="advads-conditions-<?php echo $index; ?>-<?php echo $_taxonomy_id; ?>" name="<?php echo $name; ?>[value][]" <?php checked($_val, 1); ?> value="<?php echo $_taxonomy_id; ?>"><?php
		endforeach;
		?><p class="advads-conditions-not-selected advads-error-message"><?php _ex( 'Please select some items.', 'Error message shown when no display condition term is selected', 'advanced-ads' ); ?></p></div><?php
	}

	/**
	 * display terms of a taxonomy for choice
	 * 
	 * @param obj $taxonomy taxonomy object
	 * @param arr $checked ids of checked terms
	 * @param str $inputname name of the input field
	 * @param int $max_terms maximum number of terms to show
	 * @param int $index index of the conditions group
	 */
	public static function display_term_list($taxonomy, $checked = array(), $inputname = '', $max_terms = 50, $index = 0) {

	    $terms = get_terms($taxonomy->name, array('hide_empty' => false, 'number' => $max_terms));

	    if (!empty($terms) && !is_wp_error($terms)):
		// display search field if the term limit is reached
		if (count($terms) == $max_terms) :

		    // query active terms
		    if (is_array($checked) && count($checked)) {
			$args = array('hide_empty' => false);
			$args['include'] = $checked;
			$checked_terms = get_terms($taxonomy->name, $args);
			?><div class="advads-conditions-terms-buttons dynamic-search"><?php
		    foreach ($checked_terms as $_checked_term) :
			?><label class="button ui-state-active"><?php echo $_checked_term->name;
			?><input type="hidden" name="<?php echo $inputname; ?>" value="<?php echo $_checked_term->term_id; ?>"></label><?php
			endforeach;
			?></div><?php
			       } else {
				   ?><div class="advads-conditions-terms-buttons dynamic-search"></div><?php
			}
			?><span class="advads-conditions-terms-show-search button" title="<?php
		    _ex('add more terms', 'display the terms search field on ad edit page', 'advanced-ads');
		    ?>">+</span><br/><input type="text" class="advads-conditions-terms-search" data-tag-name="<?php echo $taxonomy->name;
		    ?>" data-input-name="<?php echo $inputname; ?>" placeholder="<?php _e('term name or id', 'advanced-ads'); ?>"/><?php
		  else :
		      ?><div class="advads-conditions-terms-buttons advads-buttonset"><?php
				   foreach ($terms as $_term) :
				       $field_id = "advads-conditions-$index-$_term->term_id";
				       ?><input type="checkbox" id="<?php echo $field_id; ?>" name="<?php echo $inputname; ?>" value="<?php echo $_term->term_id; ?>" <?php checked(in_array($_term->term_id, $checked), true); ?>><label for="<?php echo $field_id; ?>"><?php echo $_term->name; ?></label><?php
		endforeach;
		?><p class="advads-conditions-not-selected advads-error-message"><?php _ex( 'Please select some items.', 'Error message shown when no display condition term is selected', 'advanced-ads' ); ?></p></div><?php
	    endif;
	endif;
    }

    /**
     * callback to display the metabox for the taxonomy archive pages
     *
     * @param arr $options options of the condition
     * @param int $index index of the condition
     */
    static function metabox_post_ids($options, $index = 0) {

	if (!isset($options['type']) || '' === $options['type']) {
	    return;
	}

	// get values and select operator based on previous settings
	$operator = ( isset($options['operator']) && $options['operator'] === 'is_not' ) ? 'is_not' : 'is';
	$values = ( isset($options['value']) && is_array($options['value']) ) ? $options['value'] : array();

	// form name basis
	$name = self::FORM_NAME . '[' . $index . ']';
	?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
	<select name="<?php echo $name; ?>[operator]">
	    <option value="is" <?php selected('is', $operator); ?>><?php _e('is', 'advanced-ads'); ?></option>
	    <option value="is_not" <?php selected('is_not', $operator); ?>><?php _e('is not', 'advanced-ads'); ?></option>
	</select><?php ?><div class="advads-conditions-single advads-buttonset advads-conditions-postid-buttons"><?php
	// query active post ids
	if ($values != array()) {
	    $args = array(
		'post_type' => 'any',
		// 'post_status' => 'publish',
		'post__in' => $values,
		'posts_per_page' => -1,
		    // 'ignore_sticky_posts' => 1,
	    );

	    $the_query = new WP_Query($args);
	    while ($the_query->have_posts()) {
		$the_query->next_post();
		?><label class="button ui-state-active"><?php echo get_the_title($the_query->post->ID) . ' (' . $the_query->post->post_type . ')';
		?><input type="hidden" name="<?php echo $name; ?>[value][]" value="<?php echo $the_query->post->ID; ?>"></label><?php
	    }
	}
	?><span class="advads-conditions-postids-show-search button" <?php
	if (!count($values)) {
	    echo 'style="display:none;"';
	}
	?>>+</span>
	    <p class="advads-conditions-postids-search-line">
		<input type="text" class="advads-display-conditions-individual-post" <?php if (count($values)) {
	    echo 'style="display:none;"';
	} ?>
		       placeholder="<?php _e('title or id', 'advanced-ads'); ?>"
		       data-field-name="<?php echo $name; ?>"/><?php
	wp_nonce_field('internal-linking', '_ajax_linking_nonce', false);
	?></p></div><?php
    }

    /**
     * callback to display the metabox for the general display conditions
     *
     * @param arr $options options of the condition
     * @param int $index index of the condition
     */
    static function metabox_general($options, $index = 0) {

	// general conditions array
	$conditions = self::get_instance()->general_conditions();
	if (!isset($options['type']) || '' === $options['type']) {
	    return;
	}

	$name = self::FORM_NAME . '[' . $index . ']';
	$values = isset($options['value']) ? $options['value'] : array();
	?><div class="advads-conditions-single advads-buttonset">
	    <input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/><?php
		foreach ($conditions as $_key => $_condition) :

		    // activate by default
		    $value = ( $values === array() || in_array($_key, $values) ) ? 1 : 0;

		    $field_id = "advads-conditions-$index-$_key";
		    ?><input type="checkbox" id="<?php echo $field_id; ?>" name="<?php echo $name; ?>[value][]" value="<?php echo $_key; ?>" <?php checked(1, $value); ?>><label for="<?php echo $field_id; ?>"><?php echo $_condition['label']; ?></label><?php
	    endforeach;
	    ?></div><?php
	    return;
	}	

	/**
	 * retrieve the array with general conditions
	 * 
	 * @return arr $conditions
	 * 
	 */
	static function general_conditions() {
	    return $conditions = apply_filters( 'advanced-ads-display-conditions-general', array(
		'is_front_page' => array(
		    'label' => __('Home Page', 'advanced-ads'),
		    'description' => __('show on Home page', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_singular' => array(
		    'label' => __('Singular Pages', 'advanced-ads'),
		    'description' => __('show on singular pages/posts', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_archive' => array(
		    'label' => __('Archive Pages', 'advanced-ads'),
		    'description' => __('show on any type of archive page (category, tag, author and date)', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_search' => array(
		    'label' => __('Search Results', 'advanced-ads'),
		    'description' => __('show on search result pages', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_404' => array(
		    'label' => __('404 Page', 'advanced-ads'),
		    'description' => __('show on 404 error page', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_attachment' => array(
		    'label' => __('Attachment Pages', 'advanced-ads'),
		    'description' => __('show on attachment pages', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_main_query' => array(
		    'label' => __('Secondary Queries', 'advanced-ads'),
		    'description' => __('allow ads in secondary queries', 'advanced-ads'),
		    'type' => 'radio',
		),
		'is_feed' => array(
		    'label' => __('Feed', 'advanced-ads'),
		    'description' => __('allow ads in Feed', 'advanced-ads'),
		    'type' => 'radio',
		)
	    ) );
	}
	
	/**
	 * Callback to display the 'content age' condition
	 *
	 * @param arr $options options of the condition
	 * @param int $index index of the condition
	 */
	static function metabox_content_age( $options, $index = 0 ){
		if ( ! isset ( $options['type'] ) || '' === $options['type'] ) { return; }

		$type_options = Advanced_Ads_Display_Conditions::get_instance()->conditions;

		if ( ! isset( $type_options[ $options['type'] ] ) ) {
			return;
		}

		// form name basis
		$name = Advanced_Ads_Display_Conditions::FORM_NAME . '[' . $index . ']';

		$operator = isset( $options['operator'] ) ? $options['operator'] : 'older_than';
		$value = ( isset( $options['value'] ) && is_numeric( $options['value'] ) ) ? floatval( $options['value'] ) : 0;
		?><input type="hidden" name="<?php echo $name; ?>[type]" value="<?php echo $options['type']; ?>"/>
		<select name="<?php echo $name; ?>[operator]">
		    <option value="older_than" <?php selected( 'older_than', $operator ); ?>><?php _e( 'older than', 'advanced-ads-pro'); ?></option>
		    <option value="younger_than" <?php selected( 'younger_than', $operator ); ?>><?php _e( 'younger than', 'advanced-ads-pro' ); ?></option>
		</select><input type="text" name="<?php echo $name; ?>[value]" value="<?php echo $value; ?>"/>&nbsp;<?php _e( 'days', 'advanced-ads-pro' );
	}	

	/**
	 * check post type display condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool true if can be displayed
	 */
	static function check_post_type($options = array(), Advanced_Ads_Ad $ad) {
	    if (!isset($options['value']) || !is_array($options['value'])) {
		return false;
	    }

	    if (isset($options['operator']) && $options['operator'] === 'is_not') {
		$operator = 'is_not';
	    } else {
		$operator = 'is';
	    }

	    $ad_options = $ad->options();
	    $query = $ad_options['wp_the_query'];
	    $post = isset($ad_options['post']) ? $ad_options['post'] : null;
	    $post_type = isset($post['post_type']) ? $post['post_type'] : false;

	    if ( ! self::can_display_ids( $post_type, $options['value'], $operator ) ) {
		return false;
	    }

	    return true;
	}

	/**
	 * check author display condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool true if can be displayed
	 */
	static function check_author($options = array(), Advanced_Ads_Ad $ad) {

	    if (!isset($options['value']) || !is_array($options['value'])) {
		return false;
	    }

	    if (isset($options['operator']) && $options['operator'] === 'is_not') {
		$operator = 'is_not';
	    } else {
		$operator = 'is';
	    }

	    $ad_options = $ad->options();
	    $post = isset($ad_options['post']) ? $ad_options['post'] : null;
	    $post_author = isset($post['author']) ? $post['author'] : false;

	    if (!self::can_display_ids($post_author, $options['value'], $operator)) {
		return false;
	    }

	    return true;
	}

	/**
	 * check taxonomies display condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_taxonomies($options = array(), Advanced_Ads_Ad $ad) {

	    if( !isset( $options['value']) ){
		return false;
	    }
	    
	    if (isset($options['operator']) && $options['operator'] === 'is_not') {
		$operator = 'is_not';
	    } else {
		$operator = 'is';
	    }

	    $ad_options = $ad->options();
	    $query = $ad_options['wp_the_query'];
	    $post_id = isset($ad_options['post']['id']) ? $ad_options['post']['id'] : null;

	    // get terms of the current taxonomy
	    $type_options = self::get_instance()->conditions;
	    if (!isset($options['type']) || !isset($type_options[$options['type']]['taxonomy'])) {
		return true;
	    }
	    $taxonomy = $type_options[$options['type']]['taxonomy'];

	    $terms = get_the_terms($post_id, $taxonomy);
	    
	    if ( is_array($terms) ) {
		foreach ($terms as $term) {
		    $term_ids[] = $term->term_id;
		}
	    } elseif( false === $terms && 'is' === $operator ) {
		// don’t show if should show only for a specific tag
		return false;
	    } else {
		return true;
	    }
	    
	    if( 'is' === $operator && ( ! isset($query['is_singular']) || ! $query['is_singular'] ) ){
		return false;
	    } elseif (isset($query['is_singular']) && $query['is_singular'] && !self::can_display_ids($options['value'], $term_ids, $operator) 
	    ) {
		return false;
	    }
	    
	    return true;
	}

	/**
	 * check taxonomy archive display condition in frontend
	 *
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_taxonomy_archive($options = array(), Advanced_Ads_Ad $ad) {

	    if( !isset( $options['value']) ){
		return false;
	    }
	    
	    if (isset($options['operator']) && $options['operator'] === 'is_not') {
		$operator = 'is_not';
	    } else {
		$operator = 'is';
	    }

	    $ad_options = $ad->options();
	    $query = $ad_options['wp_the_query'];
	    
	    // return false if operator is "is", but important query vars are not given
	    if( 'is' === $operator && ( empty( $query['term_id'] ) || empty($query['is_archive']) ) ){
		return false;
	    } elseif ( isset($query['term_id']) && isset($query['is_archive']) && $query['is_archive'] && !self::can_display_ids($query['term_id'], $options['value'], $operator)
	    ) {
		return false;
	    }
	    
	    return true;
	}
	
	/**
	 * check if a specific archive belongs to a taxonomy in general (not a specific term)
	 *
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_taxonomy( $options = array(), Advanced_Ads_Ad $ad ) {

	    if( !isset( $options['value']) ){
		return false;
	    }
	    
	    if (isset($options['operator']) && $options['operator'] === 'is_not') {
		$operator = 'is_not';
	    } else {
		$operator = 'is';
	    }

	    $ad_options = $ad->options();
	    $query = $ad_options['wp_the_query'];
	    
	    // return false if operator is "is", but important query vars are not given
	    if( 'is' === $operator && ( empty( $query['taxonomy'] ) || empty($query['is_archive']) ) ){
		return false;
	    } elseif ( isset($query['taxonomy']) && isset($query['is_archive']) && $query['is_archive'] && !self::can_display_ids($query['taxonomy'], $options['value'], $operator)
	    ) {
		return false;
	    }
	    
	    return true;
	}

	/**
	 * check post ids display condition in frontend
	 * 
	 * @param arr $options options of the condition
	 * @return bool true if can be displayed
	 */
	static function check_post_ids($options = array(), Advanced_Ads_Ad $ad) {

		if (isset($options['operator']) && $options['operator'] === 'is_not') {
		    $operator = 'is_not';
		} else {
		    $operator = 'is';
		}

		$ad_options = $ad->options();
		$query = $ad_options['wp_the_query'];
		$post_id = isset($ad_options['post']['id']) ? $ad_options['post']['id'] : null;
		
                //fixes page id on BuddyPress pages
                if ( $post_id == 0 && class_exists( 'BuddyPress' ) && function_exists( 'bp_current_component' )){
                    $component = bp_current_component();
                    $bp_pages = get_option( 'bp-pages' );
                    if ( isset( $bp_pages[$component] ) ){
                        $post_id = $bp_pages[$component];
                    } 
                }
                
		/**
		 * WooCommerce Store page fix
		 * since WooCommerce changes the post ID of the static page selected to be the product overview page, we need to get the original page id from the WC options
		 */
		if ( function_exists( 'is_shop' ) && is_shop() && isset( $options['value'] ) && is_array( $options['value'] ) ) {
			$post_id = get_option( 'woocommerce_shop_page_id' );
			return self::can_display_ids($post_id, $options['value'], $operator);
		}
				
		if( empty( $ad_options['wp_the_query']['is_singular'] ) ){
		    if( 'is_not' === $operator ){
			return true;
		    } else {
			return false;
		    }
		}

		if ( ! isset( $options['value'] ) || ! is_array( $options['value'] ) || ! $post_id ) {
			return true;
		}

		return self::can_display_ids($post_id, $options['value'], $operator);
	}

	/**
	 * check general display conditions in frontend
	 *
	 * @param arr $options options of the condition
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool true if can be displayed
	 */
	static function check_general($options = array(), Advanced_Ads_Ad $ad) {

	    // display by default
	    if (!isset($options['value']) || !is_array($options['value']) || !count($options['value'])) {
		return true;
	    }

	    // check general conditions added by other add-ons
	    if ( null !== ( $result = apply_filters( 'advanced-ads-display-conditions-check-general', null, $options['value'] ) ) ) {
		return $result;
	    }

	    // skip checks, if general conditions are unchanged
	    if( self::$default_general_keys === $options['value'] ){
		return true;
	    }
	    
	    // get plugin options
	    $plugin_options = Advanced_Ads_Plugin::get_instance()->options();

	    // error_log(print_r($options, true));
	    // error_log(print_r(debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true));

	    $ad_options = $ad->options();
	    $query = $ad_options['wp_the_query'];

	    // check main query
	    if ( isset( $query['is_main_query'] ) && ! $query['is_main_query'] && ! in_array('is_main_query', $options['value'] ) ) {
		    return false;
	    }

	    // check home page
	    if ( ( ( isset($query['is_front_page']) && $query['is_front_page'] ) 
		    || ( isset($query['is_home']) && $query['is_home'] ) )
		    && in_array('is_front_page', $options['value'])
		    ) {
		return true;
	    } elseif (isset($query['is_front_page']) && $query['is_front_page'] && (
		    !in_array('is_front_page', $options['value'])
		    )) {
		return false;
	    }

	    // check common tests
	    foreach (self::$query_var_keys as $_type) {
		if ('is_main_query' !== $_type && isset($query[$_type]) && $query[$_type] &&
			in_array($_type, $options['value'])) {
		    return true;
		}
	    }

	    return false;
	}
	
	/**
	 * Check 'content age' condition in frontend.
	 *
	 * @param arr $options options of the condition
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool true if can be displayed
	 */
	static function check_content_age( $options = array(), Advanced_Ads_Ad $ad ) {
		global $post;
		
		$operator = ( isset($options['operator']) && $options['operator'] === 'younger_than' ) ? 'younger_than' : 'older_than';
		$value = isset( $options['value'] ) ? $options['value'] : '';

		if ( empty( $post->ID ) && empty( $value ) ) {
			return true;
		}
		
		// get post publish date in unix timestamp
		$publish_time = get_the_time( 'U', $post->ID );

		// get difference from now
		$diff_from_now = time() - $publish_time;
		
		// check against entered age
		$value_in_seconds = DAY_IN_SECONDS * $value;

		if( $operator === 'younger_than' ){
			return $diff_from_now < $value_in_seconds;
		} else {
			return $diff_from_now > $value_in_seconds;
		}
	}	

	/**
	 * helper function to check for in array values
	 * 
	 * @param mixed $id  scalar (key) or array of keys as needle
	 * @param array $ids haystack
	 *
	 * @return boolean void if either argument is empty
	 */
	static function in_array($id, $ids) {
	    // empty?
	    if (!isset($id) || $id === array()) {
		return;
	    }

	    // invalid?
	    if (!is_array($ids)) {
		return;
	    }

	    return is_array($id) ? array_intersect($id, $ids) !== array() : in_array($id, $ids);
	}

	/**
	 * helper to compare ids
	 * 
	 * @param arr $needle ids that should be searched for in haystack
	 * @param arr $haystack reference ids
	 * @param str $operator whether it should be included or not
	 * @return boolean
	 */
	static function can_display_ids($needle, $haystack, $operator = 'is') {

	    if ('is' === $operator && self::in_array($needle, $haystack) === false) {
		return false;
	    }

	    if ('is_not' === $operator && self::in_array($needle, $haystack) === true) {
		return false;
	    }

	    return true;
	}

	/**
	 * check display conditions
	 *
	 * @since 1.1.0 moved here from can_display()
	 * @since 1.7.0 moved here from display-by-query module
	 * @return bool $can_display true if can be displayed in frontend
	 */
	public function can_display($can_display, $ad) {
	    if (!$can_display) {
		return false;
	    }

	    $options = $ad->options();
	    if (
	    // test if anything is to be limited at all
		    !isset($options['conditions']) || !is_array($options['conditions'])
		    // query arguments required
		    || !isset($options['wp_the_query'])
	    ) {
		return true;
	    }
	    // get conditions with rebased index keys
	    $conditions = array_values( $options['conditions'] );
	    $query = $options['wp_the_query'];
	    $post = isset($options['post']) ? $options['post'] : null;


	    $last_result = false;
	    $length = count( $conditions );
	    
	    for($i = 0; $i < $length; ++$i) {
		$_condition = current( $conditions );
		$next = next( $conditions );
		$next_key = key( $conditions );
		
		/**
		 * force next condition’s connector to OR if
		 *  not set to OR already
		 *  this condition and the next are from the same taxonomy
		 *  the conditions don’t have the same condition type
		 *  they are both set to SHOW
		 */
		$tax = ( isset( $_condition['type'] ) && isset( $this->conditions[ $_condition['type'] ]['taxonomy'] ) ) ? $this->conditions[ $_condition['type'] ]['taxonomy'] : false;
		$next_tax = ( isset( $next['type'] ) && isset( $this->conditions[ $next['type'] ]['taxonomy'] ) ) ? $this->conditions[ $next['type'] ]['taxonomy'] : false;
		if( $tax && $next_tax && $next_key 
			&& $next_tax === $tax
			&& ( ! isset( $next['connector'] ) || $next['connector'] !== 'or' ) 
			&& 'is' === $_condition['operator'] && 'is' === $next['operator']
			&& $_condition['type'] !== $next['type'] ){
		    // error_log(print_r('force_or', true));
		    $next['connector'] = 'or';
		    $conditions[ $next_key ]['connector'] = 'or';
		}
		
		// ignore OR if last result was true
		if( $last_result && isset( $_condition['connector'] ) && 'or' === $_condition['connector'] ){
		    continue;
		}
		
		$last_result = $result = self::frontend_check($_condition, $ad);
		if( ! $result ) {
		    // return false only, if the next condition doesn’t have an OR operator
		    if( ! isset( $next['connector'] ) || $next['connector'] !== 'or' ) {
			return false;
		    }
		}
	    }

	    return true;
	}

	/**
	 * On demand provide current query arguments to ads.
	 *
	 * Existing arguments must not be overridden.
	 * Some arguments might be cachable.
	 *
	 * @param array $args
	 *
	 * @return array
	 */
	public function ad_select_args_callback($args) {
	    global $post, $wp_the_query, $wp_query, $numpages;

	    if (isset($post)) {
		if (!isset($args['post'])) {
		    $args['post'] = array();
		}
		if (!isset($args['post']['id'])) {

		    // if currently on a single site, use the main query information just in case a custom query is broken
		    if( isset( $wp_the_query->post->ID ) && $wp_the_query->is_single() ){
			$args['post']['id'] = $wp_the_query->post->ID;
		    } else {
			$args['post']['id'] = $post->ID;
		    }
		}
		if (!isset($args['post']['author'])) {
		    // if currently on a single site, use the main query information just in case a custom query is broken
		    if( isset( $wp_the_query->post->post_author ) && $wp_the_query->is_single() ){
			$args['post']['author'] = $wp_the_query->post->post_author;
		    } else {
			$args['post']['author'] = $post->post_author;
		    }
		}
		if (!isset($args['post']['post_type'])) {
		    // if currently on a single site, use the main query information just in case a custom query is broken
		    if( isset( $wp_the_query->post->post_type ) && $wp_the_query->is_single() ){
			$args['post']['post_type'] = $wp_the_query->post->post_type;
		    } else {
			$args['post']['post_type'] = $post->post_type;
		    }
		}
	    }

	    // pass query arguments
	    if (isset($wp_the_query)) {
		if (!isset($args['wp_the_query'])) {
		    $args['wp_the_query'] = array();
		}
		$query = $wp_the_query->get_queried_object();
		// term_id exists only for taxonomy archive pages
		if (!isset($args['wp_the_query']['term_id']) && $query) {
		    $args['wp_the_query']['term_id'] = isset($query->term_id) ? $query->term_id : '';
		}
		// taxonomy
		if (!isset($args['wp_the_query']['taxonomy']) && $query) {
		    $args['wp_the_query']['taxonomy'] = isset($query->taxonomy) ? $query->taxonomy : '';
		}

		// query type/ context
		if (!isset($args['wp_the_query']['is_main_query'])) {
		    $args['wp_the_query']['is_main_query'] = Advanced_Ads::get_instance()->is_main_query();
		}

		// `<!-- nextpage -->` tags
		if ( ! isset( $args['wp_the_query']['page'] ) ) {
		    $args['wp_the_query']['page'] = isset( $wp_the_query->query_vars['page'] ) && $wp_the_query->query_vars['page'] ? $wp_the_query->query_vars['page'] : 1;
		    $args['wp_the_query']['numpages'] = isset( $numpages ) ? $numpages : 1;
		}

		// query vars
		foreach (self::$query_var_keys as $key) {
		    if (!isset($args['wp_the_query'][$key])) {
			$args['wp_the_query'][$key] = $wp_the_query->$key();
		    }
		}
	    }

	    return $args;
	}

	/**
	 * modify post search query to search by post_title or ID
	 *
	 * @param array $query
	 * @return string
	 */
	public static function modify_post_search( $query ) {
	    
		// use ID and not search field if ID given
		if( 0 !== absint( $query['s'] ) && strlen( $query['s'] ) == strlen ( absint( $query['s'] ) ) ){
                    $query['post__in'] = array( absint( $query['s'] ));
		    unset( $query['s'] );
		}
		
		$query['suppress_filters'] = false;
		$query['orderby'] = 'post_title';
		$query['post_status'] = array( 'publish', 'pending', 'draft', 'future' );
		return $query;
	}

	/**
	 * modify post search sql to search only in post title
	 *
	 * @param string $sql
	 * @return string
	 */
	public static function modify_post_search_sql( $sql ) {
		global $wpdb;
		
		// $sql = preg_replace_callback( "/{$wpdb->posts}.post_(content|excerpt)( NOT)? LIKE '%(.*?)%'/", array( 'Advanced_Ads_Display_Conditions', 'modify_post_search_sql_callback' ), $sql );
		
		// removes the search in content and excerpt columns
		$sql = preg_replace( "/OR \({$wpdb->posts}.post_(content|excerpt)( NOT)? LIKE '(.*?)'\)/", '', $sql );
		
		return $sql;
	}

	/**
	 * preg_replace callback used in modify_post_search_sql()
	 *
	 * @param array $matches
	 * @return string
	 * @deprecated since version 1.8.16
	 */
	public static function modify_post_search_sql_callback( $matches ) {
		global $wpdb;
		if ( $matches[1] === 'content' && preg_match( '@^([0-9]+)$@', $matches[3], $matches_id ) ) {
			$equals_op = $matches[2] === ' NOT' ? '!=' : '=';
			return "{$wpdb->posts}.ID$equals_op$matches_id[1]";
		} else if ( $matches[2] === ' NOT' ) {
			return '1=1';
		} else {
			return '1=0';
		}
	}

    }
    
<?php

/**
 * Upgrade logic from older data to new one
 * 
 * the version number itself is changed in /admin/includes/class-notices.php::register_version_notices()
 *
 * @since 1.7
 */
class Advanced_Ads_Upgrades {
    
	public function __construct(){
	    
		$internal_options = Advanced_Ads_Plugin::get_instance()->internal_options();

		// the 'advanced_ads_edit_ads' capability was added to POST_TYPE_SLUG post type in this version
		if ( ! isset( $internal_options['version'] ) || version_compare( $internal_options['version'], '1.7.2', '<' ) ) {
			Advanced_Ads_Plugin::get_instance()->create_capabilities();
		}

		// suppress version update?
		$suppress_version_number_update = false;
		
		// don’t upgrade if no previous version existed
		if( ! empty( $internal_options['version'] ) ) {
			if ( version_compare( $internal_options['version'], '1.7' ) == -1 ) {
				// run with wp_loaded action, because WP_Query is needed and some plugins inject data that is not yet initialized
				add_action( 'wp_loaded', array( $this, 'upgrade_1_7') );
			}

			if ( version_compare( $internal_options['version'], '1.7.4' ) == -1 ) {
				// upgrate version number only after this ran through, because of the used filter only available in admin
				if( ! is_admin() ){
				    $suppress_version_number_update = true;
				    // run with wp_loaded action, because Upgrades are checked in the plugins_loaded hook
				} else {
				    add_action( 'wp_loaded', array( $this, 'upgrade_1_7_4') );
				}
			}
		}

		// update version notices – if this doesn’t happen here, the upgrade might run multiple times and destroy updated data
		if( ! $suppress_version_number_update ){
		    Advanced_Ads_Admin_Notices::get_instance()->update_version_number();
		}
	}
    
	/**
	 * upgrade data to version 1.7
	 * rewrite existing display conditions
	 */
	public function upgrade_1_7(){
	    
		// get all ads, regardless of the publish status
		$args['post_status'] = 'any';
		$args['suppress_filters'] = true; // needed to remove issue with a broken plugin from the repository
		$ads = Advanced_Ads::get_instance()->get_model()->get_ads( $args );
		
		// iterate through ads
		// error_log(print_r($ads, true));
		error_log(print_r('–– STARTING ADVANCED ADS data upgrade to version 1.7 ––', true));
		foreach( $ads as $_ad ){
		    // ad options
		    $option_key = Advanced_Ads_Ad::$options_meta_field;
		    if( !isset( $_ad->ID ) || ! $option_key ){
			continue;
		    }
		    $options = get_post_meta( $_ad->ID, $option_key, true );
		    // rewrite display conditions
		    if( ! isset( $options['conditions'] ) ){
			continue;
		    }
		    
		    error_log(print_r('AD ID: ' . $_ad->ID, true));
		    error_log(print_r('OLD CONDITIONS', true));
		    error_log(print_r($options['conditions'], true));
		    
		    $old_conditions = $options['conditions'];
		    
		    // check if conditions are disabled
		    if( ! isset( $old_conditions['enabled'] ) || ! $old_conditions['enabled'] ){
			$new_conditions = '';
		    } else {
			$new_conditions = array();
			
			// rewrite general conditions
			$old_general_conditions = array(
			    'is_front_page',
			    'is_singular',
			    'is_archive',
			    'is_search',
			    'is_404',
			    'is_attachment',
			    'is_main_query'
			);
			$general = array();
			foreach( $old_general_conditions as $_general_condition ){
			    if( isset( $old_conditions[ $_general_condition ] ) && $old_conditions[ $_general_condition ] ) { 
				$general[] = $_general_condition;
			    }
			}
			// move general conditions into display conditions
			// only, if the number of conditions in the previous setting is lower, because only that means there is an active limitation
			// not sure if allowing an empty array is logical, but some users might have set this up to hide an ad
			if( count( $general ) < count( $old_general_conditions ) ){
			    $new_conditions[] = array(
				'type' => 'general',
				'value' => $general
			    );
			}
			
			// rewrite post types condition
			if( isset( $old_conditions[ 'posttypes' ]['include'] ) 
				&& ( !isset ( $old_conditions[ 'posttypes' ]['all'] ) 
				|| ! $old_conditions[ 'posttypes' ]['all'] ) ) {
				if ( is_string( $old_conditions[ 'posttypes' ]['include']) ) {
				    $old_conditions[ 'posttypes' ]['include'] = explode( ',', $old_conditions[ 'posttypes' ]['include'] );
				}
				$new_conditions[] = array(
				    'type' => 'posttypes',
				    'value' => $old_conditions[ 'posttypes' ]['include']
				);
			}
			
			/**
			 * rewrite category ids and category archive ids
			 * 
			 * the problem is that before there was no connection between term ids and taxonomy, now, each taxonomy has its own condition
			 */
			// check, if there are even such options set
			if( ( isset( $old_conditions[ 'categoryids' ] ) 
				&& ( !isset ( $old_conditions[ 'categoryids' ]['all'] ) 
				|| ! $old_conditions[ 'categoryids' ]['all'] ) )
				|| ( isset( $old_conditions[ 'categoryarchiveids' ] ) 
				&& ( !isset ( $old_conditions[ 'categoryarchiveids' ]['all'] ) 
				|| ! $old_conditions[ 'categoryarchiveids' ]['all'] ) )) 
			    { 
			    
				// get all taxonomies
				$taxonomies = get_taxonomies( array('public' => true, 'publicly_queryable' => true), 'objects', 'or' );
				$taxonomy_terms = array();
				foreach ( $taxonomies as $_tax ) {
				    if( $_tax->name === 'advanced_ads_groups' ){
					continue;
				    }
				    // get all terms
				    $terms = get_terms( $_tax->name, array('hide_empty' => false, 'number' => 0, 'fields' => 'ids' ) );
				    if ( is_wp_error( $terms ) || ! count( $terms ) ){
					continue;
				    } else {
					$taxonomy_terms[ $_tax->name ] = $terms;
				    }
				    
				    // get terms that are in all terms and in active terms
				    if( isset( $old_conditions[ 'categoryids' ] ) 
					&& ( !isset ( $old_conditions[ 'categoryids' ]['all'] ) 
					|| ! $old_conditions[ 'categoryids' ]['all'] ) )
				    {
					// honor "include" option first
					if( isset ( $old_conditions[ 'categoryids' ]['include'] ) && count( $old_conditions[ 'categoryids' ]['include'] ) 
						&& $same_values = array_intersect($terms, $old_conditions[ 'categoryids' ]['include']) ){
						    $new_conditions[] = array(
							'type' => 'taxonomy_' . $_tax->name ,
							'operator' => 'is',
							'value' => $same_values
						    );
					} elseif ( isset ( $old_conditions[ 'categoryids' ]['exclude'] ) && count( $old_conditions[ 'categoryids' ]['exclude'] ) 
						&& $same_values = array_intersect($terms, $old_conditions[ 'categoryids' ]['exclude']) ){
						 $new_conditions[] = array(
						    'type' => 'taxonomy_' . $_tax->name ,
						    'operator' => 'is_not',
						    'value' => $same_values
						);
					}
				    }
				    
				    // get terms that are in all terms and in active terms
				    if( isset( $old_conditions[ 'categoryarchiveids' ] ) 
					&& ( !isset ( $old_conditions[ 'categoryarchiveids' ]['all'] ) 
					|| ! $old_conditions[ 'categoryarchiveids' ]['all'] ) )
				    {
					// honor "include" option first
					if( isset ( $old_conditions[ 'categoryarchiveids' ]['include'] ) && count( $old_conditions[ 'categoryarchiveids' ]['include'] ) 
						&& $same_values = array_intersect($terms, $old_conditions[ 'categoryarchiveids' ]['include']) ){
						    $new_conditions[] = array(
							'type' => 'archive_' . $_tax->name ,
							'operator' => 'is',
							'value' => $same_values
						    );
					} elseif ( isset ( $old_conditions[ 'categoryarchiveids' ]['exclude'] ) && count( $old_conditions[ 'categoryarchiveids' ]['exclude'] ) 
						&& $same_values = array_intersect($terms, $old_conditions[ 'categoryarchiveids' ]['exclude']) ){
						 $new_conditions[] = array(
						    'type' => 'archive_' . $_tax->name ,
						    'operator' => 'is_not',
						    'value' => $same_values
						);
					}
				    }
				}
			}
			
			// rewrite single post ids
			if( isset ( $old_conditions[ 'postids' ]['ids'] )
				&& isset ( $old_conditions[ 'postids' ]['method'] )
				&& $old_conditions[ 'postids' ]['method']
				&& ( !isset ( $old_conditions[ 'postids' ]['all'] ) 
				|| ! $old_conditions[ 'postids' ]['all'] ) ) { 
			    $operator = ( $old_conditions[ 'postids' ]['method'] === 'exclude' ) ? 'is_not' : 'is';
			    if ( is_string( $old_conditions[ 'postids' ]['ids']) ) {
				    $old_conditions[ 'postids' ]['ids'] = explode( ',', $old_conditions[ 'postids' ]['ids'] );
			    }
			    $new_conditions[] = array(
				'type' => 'postids',
				'operator' => $operator,
				'value' => $old_conditions[ 'postids' ]['ids']
			    );
			}			
		    }
		    
		    error_log(print_r('NEW CONDITIONS', true));
		    error_log(print_r($new_conditions, true));
		    
		    $options['conditions'] = $new_conditions;
		    
		    // save conditions
		    update_post_meta( $_ad->ID, $option_key, $options );
		}
		
		error_log(print_r('up to 1.7', true));
	}
	
	/**
	 * upgrades for version 1.7.4
	 * reactivate active add-on licenses, needed only once after upgrade of the plugin shop
	 */
	public function upgrade_1_7_4(){
	    
		// ignore, if not main blog
		if( is_multisite() && ! is_main_site() ){
		    return;
		}
		
		$add_ons = apply_filters( 'advanced-ads-add-ons', array() );
		
		// return if no add-ons found
		if( $add_ons === array() ) {
		    return;
		}

		error_log(print_r('–– STARTING ADVANCED ADS 1.7.4 upgrade ––', true));
		foreach( $add_ons as $_add_on_key => $_add_on ){

			// check status
			if(Advanced_Ads_Admin_Licenses::get_instance()->get_license_status( $_add_on['options_slug'] ) !== 'valid' ) {
				continue;
			}

			// retrieve our license key from the DB
			$licenses = get_option(ADVADS_SLUG . '-licenses', array());
			$license_key = isset($licenses[$_add_on_key]) ? $licenses[$_add_on_key] : '';
			
			$result = Advanced_Ads_Admin::get_instance()->activate_license( $_add_on_key, $_add_on['name'], $_add_on['options_slug'], $license_key );
			error_log( sprintf( 'Register license key for %s: %s', $_add_on['name'], $result ) );
			
		}
	}
    
}
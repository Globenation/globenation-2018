<?php

class Advanced_Ads_Frontend_Checks {
	/**
	 * True if 'the_content' was invoked, false otherwise.
	 *
	 * @var bool
	 */
	private $did_the_content = false;

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$enabled = false;

		if ( ! is_admin()
			&& is_admin_bar_showing()
			&& current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads' ) )
		) {
			add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_menu' ), 1000 );
			add_filter( 'the_content', array( $this, 'set_did_the_content' ) );
			add_filter( 'wp_footer', array( $this, 'footer_checks' ), -101 );
			add_filter( 'advanced-ads-ad-select-args', array( $this, 'ad_select_args_callback' ) );
			$enabled = true;
		}

		if ( $enabled || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			add_filter( 'advanced-ads-ad-output', array( $this, 'after_ad_output' ), 10, 2 );
		}
	}

	/**
	 * Notify ads loaded with AJAX.
	 *
	 * @param array $args
	 * @return array $args
	 */
	public function ad_select_args_callback( $args ) {
		$args['frontend-check'] = true;
		return $args;
	}

	/**
	 * List current ad situation on the page in the admin-bar.
	 *
	 * @param obj $wp_admin_bar WP_Admin_Bar
	 */
	public function add_admin_bar_menu( $wp_admin_bar ) {
		global $wp_the_query, $post, $wp_scripts;

		$options = Advanced_Ads_Plugin::get_instance()->options();
		
		// load AdSense related options
		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();

		// check if jQuery is loaded in the header
		// Hidden, will be shown using js.
		// message removed after we fixed all issues we know of
		/*$wp_admin_bar->add_node( array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_jquery',
			'title' => __( 'jQuery not in header', 'advanced-ads' ),
			'href'  => ADVADS_URL . 'manual/common-issues#frontend-issues-javascript',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning',
				'target' => '_blank'
			)
		) );*/
		
		// check if AdSense loads Auto Ads ads
		// Hidden, will be shown using js.
		if( ! isset( $adsense_options['violation-warnings-disable'] ) ) {
			$nodes[] = array( 'type' => 2, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_autoads_displayed',
				'title' => __( 'Random AdSense ads', 'advanced-ads' ),
				'href'  => ADVADS_URL . 'adsense-in-random-positions-auto-ads/#utm_source=advancedads&utm_medium=link&utm_campaign=frontend-autoads-ads',
				'meta'   => array(
					'class' => 'hidden advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}
		
		// check if current user was identified as a bot
		if( Advanced_Ads::get_instance()->is_bot() ) {
			$nodes[] = array( 'type' => 1, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_user_is_bot',
				'title' => __( 'You look like a bot', 'advanced-ads' ),
				'href'  => ADVADS_URL . 'manual/ad-health/#look-like-bot',
				'meta'   => array(
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}

		// check if an ad blocker is enabled
		// Hidden, will be shown using js.
		$nodes[] = array( 'type' => 2, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'     => 'advanced_ads_ad_health_adblocker_enabled',
			'title'  => __( 'Ad blocker enabled', 'advanced-ads' ),
			// 'href'   => 'https://wpadvancedads.com/support',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning',
				'target' => '_blank'
			)
		) );

		if ( $wp_the_query->is_singular() ) {
			if ( ! $this->did_the_content ) {
				$placements = Advanced_Ads::get_ad_placements_array();
				$placement_types = Advanced_Ads_Placements::get_placement_types();
				// Find a placement that depends on 'the_content' filter.
				foreach ( $placements as $placement ) {
					if ( isset ( $placement['type'] )
					&& ! empty( $placement_types[ $placement['type'] ]['options']['uses_the_content'] ) ) {
						$nodes[] = array( 'type' => 1, 'data' => array(
							'parent' => 'advanced_ads_ad_health',
							'id'    => 'advanced_ads_ad_health_the_content_not_invoked',
							'title' => sprintf( __( '<em>%s</em> filter does not exist', 'advanced-ads' ), 'the_content' ),
							'href'  => ADVADS_URL . 'manual/ads-not-showing-up/#the_content-filter-missing',
							'meta'   => array(
								'class' => 'advanced_ads_ad_health_warning',
								'target' => '_blank'
							)
						) );
						break;
					}
				}
			}
		    
			if ( ! empty( $post->ID ) ) {
				$ad_settings = get_post_meta( $post->ID, '_advads_ad_settings', true );

				if ( ! empty( $ad_settings['disable_ads'] ) ) {
					$nodes[] = array( 'type' => 1, 'data' => array(
						'parent' => 'advanced_ads_ad_health',
						'id'    => 'advanced_ads_ad_health_disabled_on_page',
						'title' => __( 'Ads are disabled on this page', 'advanced-ads' ),
						'href'  => get_edit_post_link( $post->ID ) . '#advads-ad-settings',
						'meta'   => array(
							'class' => 'advanced_ads_ad_health_warning',
							'target' => '_blank'
						)
					) );
				}

				if ( ! empty( $ad_settings['disable_the_content'] ) ) {
					$nodes[] = array( 'type' => 1, 'data' => array(
						'parent' => 'advanced_ads_ad_health',
						'id'    => 'advanced_ads_ad_health_disabled_in_content',
						'title' => __( 'Ads are disabled in the content of this page', 'advanced-ads' ),
						'href'  => get_edit_post_link( $post->ID ) . '#advads-ad-settings',
						'meta'   => array(
							'class' => 'advanced_ads_ad_health_warning',
							'target' => '_blank'
						)
					) );
				}
			} else {
				$nodes[] = array( 'type' => 1, 'data' => array(
					'parent' => 'advanced_ads_ad_health',
					'id'    => 'advanced_ads_ad_health_post_zero',
					'title' => __( 'the current post ID is 0 ', 'advanced-ads' ),
					'href'  => ADVADS_URL . 'manual/ad-health/#post-id-0',
					'meta'   => array(
						'class' => 'advanced_ads_ad_health_warning',
						'target' => '_blank'
					)
				) );
			}
		}

		if ( ! empty( $options['disabled-ads']['all'] ) ) {
			$nodes[] = array( 'type' => 1, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_no_all',
				'title' => __( 'Ads are disabled on all pages', 'advanced-ads' ),
				'href'  => admin_url( 'admin.php?page=advanced-ads-settings' ),
				'meta'   => array(
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}

		if ( $wp_the_query->is_404() && ! empty( $options['disabled-ads']['404'] ) ) {
			$nodes[] = array( 1, array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_no_404',
				'title' => __( 'Ads are disabled on 404 pages', 'advanced-ads' ),
				'href'  => admin_url( 'admin.php?page=advanced-ads-settings' ),
				'meta'   => array(
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}

		if ( ! $wp_the_query->is_singular() && ! empty( $options['disabled-ads']['archives'] ) ){
			$nodes[] = array( 'type' => 1, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_no_archive',
				'title' => __( 'Ads are disabled on non singular pages', 'advanced-ads' ),
				'href'  => admin_url( 'admin.php?page=advanced-ads-settings' ),
				'meta'   => array(
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}

		if ( ! extension_loaded( 'dom' ) ) {
			$nodes[] = array( 'type' => 1, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_no_dom_document',
				'title' => sprintf( __( 'The %s extension(s) is not loaded', 'advanced-ads' ), 'dom' ),
				'href'  => 'http://php.net/manual/en/book.dom.php',
				'meta'   => array(
					'class' => 'advanced_ads_ad_health_warning',
					'target' => '_blank'
				)
			) );
		}

		$nodes[] = array( 'type' => 2, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_has_http',
			'title' => sprintf( '%s %s',
				__( 'Your website is using HTTPS, but the ad code contains HTTP and might not work.', 'advanced-ads' ),
				sprintf( __( 'Ad IDs: %s', 'advanced-ads'  ), '<i></i>' )
			),
			'href'  => 'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-https-ads#https-ads',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_has_http',
				'target' => '_blank'
			)
		) );

		$nodes[] = array( 'type' => 2, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_incorrect_head',
			'title' => sprintf( __( 'Visible ads should not use the Header placement: %s', 'advanced-ads'  ), '<i></i>' ),
			'href'  => 'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-visible-ad-in-header#header-ads',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_incorrect_head',
				'target' => '_blank'
			)
		) );
		
		// warn if an AdSense ad seems to be hidden
		if( ! isset( $adsense_options['violation-warnings-disable'] ) ) {
			$nodes[] = array( 'type' => 2, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_hidden_adsense',
				'title' => sprintf( '%s: %s. %s',
					__( 'AdSense violation', 'advanced-ads' ),
					__( 'Ad is hidden', 'advanced-ads' ),
					sprintf( __( 'IDs: %s', 'advanced-ads'  ), '<i></i>' )
				),
				'href'  => 'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-frontend-adsense-hidden#adsense-hidden',
				'meta'   => array(
					'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_hidden_adsense',
					'target' => '_blank'
				)
			) );
		}

		$nodes[] = array( 'type' => 2, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_floated_responsive_adsense',
			'title' => sprintf( __( 'The following responsive AdSense ads are not showing up: %s', 'advanced-ads'  ), '<i></i>' ),
			'href'	=> 'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-adsense-responsive-not-showing#The_following_responsive_AdSense_ads_arenot_showing_up',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_floated_responsive_adsense',
				'target' => '_blank'
			)
		) );
		
		// warn if consent was not given
		$privacy_state = Advanced_Ads_Privacy::get_instance()->get_state();
		if( 'not_needed' !== $privacy_state ) {
			$nodes[] = array( 'type' => 2, 'data' => array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_consent_missing',
				'title' => __( 'Consent not given', 'advanced-ads' ),
				'href'  => admin_url( 'admin.php?page=advanced-ads-settings#top#privacy' ),
				'meta'   => array(
					'class' => 'hidden advanced_ads_ad_health_warning advanced_ads_ad_health_consent_missing',
					'target' => '_blank'
				)
			) );
		}
		
		$nodes[] = array( 'type' => 3, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_debug_dfp',
			'title' => __( 'debug DFP ads', 'advanced-ads' ),
			'href'  => esc_url( add_query_arg( 'googfc', '' ) ),
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_debug_dfp_link',
				'target' => '_blank',
			)
		) );

		$nodes[] = array( 'type' => 3, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_highlight_ads',
			'title' => sprintf( '<label style="color: inherit;"><input id="advanced_ads_highlight_ads_checkbox" type="checkbox"> %s</label>', __( 'highlight ads', 'advanced-ads' ) )
		) );
		
		// search for AdSense Verification and Auto ads code
		$nodes[] = array( 'type' => 3, 'data' => array(
			'parent' => 'advanced_ads_ad_health',
			'id'    => 'advanced_ads_ad_health_auto_ads_found',
			'title' => __( 'Auto ads code found', 'advanced-ads' ),
			'href'	=> 'https://wpadvancedads.com/manual/ad-health/?utm_source=advanced-ads&utm_medium=link&utm_campaign=adhealth-adsense-auto-ads-found#Auto_ads_code_found',
			'meta'   => array(
				'class' => 'hidden advanced_ads_ad_health_highlight_ads',
				'target' => '_blank',
			)
		) );

		/**
		 * Add new node.
		 *
		 * @param array $node An array that contains: 
		 *      'type' => 1 - warning, 2 - hidden warning that will be shown using JS, 3 - info message
		 *      'data': @see WP_Admin_Bar->add_node 
		 * @param obj  $wp_admin_bar
		 */
		$nodes = apply_filters( 'advanced-ads-ad-health-nodes', $nodes );
		usort( $nodes, array( $this, 'sort_nodes' ) );

		$wp_admin_bar->add_node( array(
			'id'    => 'advanced_ads_ad_health',
			'title' => __( 'Ad Health', 'advanced-ads' ),
		) );

		$display_fine = true;

		foreach ( $nodes as $node ) {
			if ( ! isset( $node['type'] ) || ! isset( $node['data'] ) ) { continue; }
			if ( $node['type'] === 1 ) { $display_fine = false; }
			$wp_admin_bar->add_node( $node['data'] );
		}

		if ( $display_fine ) {
			$wp_admin_bar->add_node( array(
				'parent' => 'advanced_ads_ad_health',
				'id'    => 'advanced_ads_ad_health_fine',
				'title' => __( 'Everything is fine', 'advanced-ads' ),
				'href'  => false,
				'meta'   => array(
					'target' => '_blank',
				)
			) );
		}

	}

	/**
	 * Sort nodes.
	 */
	function sort_nodes( $a, $b ) {
		if ( ! isset( $a['type'] ) || ! isset( $b['type'] ) ) {
			return 0;
		}
		if ( $a['type'] == $b['type'] ) {
			return 0;
		}
		return ( $a['type'] < $b['type'] ) ? -1 : 1;
	}

	/**
	 * Set variable to 'true' when 'the_content' filter is invoked.
	 *
	 * @param string $content
	 * @return string $content
	 */
	public function set_did_the_content( $content ) {
		if ( ! $this->did_the_content ) {
			$this->did_the_content = true;
		}
		return $content;
	}

	/**
	 * Check conditions and display warning. 
	 *	Conditions: 
	 *		AdBlocker enabled,
	 *		jQuery is included in header
	 *		AdSense Quick Start ads are running
	 */
	public function footer_checks() { 
		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		ob_start();
		?><!-- Advanced Ads: <?php _e( 'the following code is used for automatic error detection and only visible to admins', 'advanced-ads' ); ?>-->
		<style>.hidden { display: none; } .advads-adminbar-is-warnings { background: #a54811 ! important; color: #fff !important; }
		#wp-admin-bar-advanced_ads_ad_health-default a:after { content: "\25BA"; margin-left: .5em; font-size: smaller; }
		.advanced-ads-highlight-ads { outline:4px solid blue !important; }</style>
		<script type="text/javascript" src="<?php echo ADVADS_BASE_URL . 'admin/assets/js/advertisement.js' ?>"></script>
		<script>
		var advanced_ads_frontend_checks = {
			showCount: function() {
				try {
					// Count only warnings that have the 'advanced_ads_ad_health_warning' class.
					var warning_count = document.querySelectorAll( '.advanced_ads_ad_health_warning:not(.hidden)' ).length;
					var fine_item = document.getElementById( 'wp-admin-bar-advanced_ads_ad_health_fine' );
				} catch ( e ) { return; }

				if ( warning_count ) {
					var header = document.querySelector( '#wp-admin-bar-advanced_ads_ad_health > div' );

					if ( fine_item ) {
						// Hide 'fine' item.
						fine_item.className += ' hidden';
					}

					if ( header ) {
						header.innerHTML = header.innerHTML.replace(/ <i>(.*?)<\/i>/, '') + ' <i>(' + warning_count + ')</i>';
						header.className += ' advads-adminbar-is-warnings';
					}
				}
			},

			array_unique: function( array ) {
				var r= [];
				for ( var i = 0; i < array.length; i++ ) {
					if ( r.indexOf( array[ i ] ) === -1 ) {
						r.push( array[ i ] );
					}
				}
				return r;
			},

			/**
			 * Add item to Ad Health node.
			 *
			 * @param string selector Selector of the node.
			 * @param string/array Item(s) to add.
			 */
			add_item_to_node: function( selector, item ) {
				if ( typeof item === 'string' ) {
					item = item.split();
				}
				var selector = document.querySelector( selector );
				if ( selector ) {
					selector.className = selector.className.replace( 'hidden', '' );
					selector.innerHTML = selector.innerHTML.replace( /(<i>)(.*?)(<\/i>)/, function( match, p1, p2, p3 ) {
						p2 = ( p2 ) ? p2.split( ', ' ) : [];
						p2 = p2.concat( item );
						p2 = advanced_ads_frontend_checks.array_unique( p2 );
						return p1 + p2.join( ', ' ) + p3;
					} );
					advanced_ads_frontend_checks.showCount();
				}
			},

			/**
			 * Search for hidden AdSense.
			 *
			 * @param string context Context for search.
			 */
			advads_highlight_hidden_adsense: function( context ) {
				if ( ! context ) {
					 context = 'html'
				}
				if ( window.jQuery ) {
					var advads_ad_health_check_adsense_hidden_ids = [];
					var responsive_zero_width = [];
					jQuery( 'ins.adsbygoogle', context ).each( function() {
						// The parent container is invisible.
						if( ! jQuery( this ).parent().is(':visible') ){
						advads_ad_health_check_adsense_hidden_ids.push( this.dataset.adSlot );
						}

						// Zero width, perhaps because a parent container is floated
						if ( jQuery( this ).attr( 'data-ad-format' ) && 0 === jQuery( this ).width() ) {
							responsive_zero_width.push( this.dataset.adSlot );
						}
					});
					if( advads_ad_health_check_adsense_hidden_ids.length ){
						advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_hidden_adsense', advads_ad_health_check_adsense_hidden_ids );
					}
					if ( responsive_zero_width.length ) {
						advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_floated_responsive_adsense', responsive_zero_width );
					}
				}
			}
		};

		(function(d, w) {
				// var not_head_jQuery = typeof jQuery === 'undefined';

				var addEvent = function( obj, type, fn ) {
					if ( obj.addEventListener )
						obj.addEventListener( type, fn, false );
					else if ( obj.attachEvent )
						obj.attachEvent( 'on' + type, function() { return fn.call( obj, window.event ); } );
				};

				function highlight_ads() {
					try {
					    var ad_wrappers = document.querySelectorAll('div[id^="<?php echo Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();?>"]')
					} catch ( e ) { return; }
				    for ( i = 0; i < ad_wrappers.length; i++ ) {
				        if ( this.checked ) {
				            ad_wrappers[i].className += ' advanced-ads-highlight-ads';
				        } else {
				            ad_wrappers[i].className = ad_wrappers[i].className.replace( 'advanced-ads-highlight-ads', '' );
				        }
				    }
				}

				advanced_ads_ready( function() {
					var adblock_item = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_adblocker_enabled' );
					// jQuery_item = d.getElementById( 'wp-admin-bar-advanced_ads_ad_health_jquery' ),

					var highlight_checkbox = d.getElementById( 'advanced_ads_highlight_ads_checkbox' );
					if ( highlight_checkbox ) {
						addEvent( highlight_checkbox, 'change', highlight_ads );
					}

					if ( adblock_item && typeof advanced_ads_adblocker_test === 'undefined' ) {
						// show hidden item
						adblock_item.className = adblock_item.className.replace( /hidden/, '' );
					}

					/* if ( jQuery_item && not_head_jQuery ) {
						// show hidden item
						jQuery_item.className = jQuery_item.className.replace( /hidden/, '' );
					}*/

					advanced_ads_frontend_checks.showCount();
				});
				
				<?php if( ! isset( $adsense_options['violation-warnings-disable'] ) ) : ?>
					// show warning if AdSense ad is hidden
					// show hint if AdSense Auto ads are enabled
					setTimeout( function(){
						advanced_ads_ready( advanced_ads_frontend_checks.advads_highlight_hidden_adsense );
						advads_highlight_adsense_auto_ads();
					}, 2000 );

					// highlight AdSense Auto Ads ads 3 seconds after site loaded
					setTimeout( function(){
						advanced_ads_ready( advads_highlight_adsense_autoads )
					}, 3000 );
					function advads_highlight_adsense_autoads(){
						if ( ! window.jQuery ) {
							window.console && window.console.log( 'Advanced Ads: jQuery not found. Some Ad Health warnings will not be shown' );
							return;
						}
						var autoads_ads = jQuery(document).find('.google-auto-placed');
						jQuery( '<p class="advads-autoads-hint" style="background-color:#0085ba;color:#fff;font-size:0.8em;padding:5px;"><?php 
							printf(__( 'This ad was automatically placed here by AdSense. <a href="%s" target="_blank" style="color:#fff;border-color:#fff;">Click here to learn more</a>.', 'advanced-ads' ), ADVADS_URL . 'adsense-in-random-positions-auto-ads/#utm_source=advanced-ads&utm_medium=link&utm_campaign=frontend-autoads-ads' ); 
							?></p>' ).prependTo( autoads_ads );
						// show Auto Ads warning in Adhealth Bar if relevant
						if( autoads_ads.length ){
							var advads_autoads_link = document.querySelector( '#wp-admin-bar-advanced_ads_autoads_displayed.hidden' );
							if ( advads_autoads_link ) {
								advads_autoads_link.className = advads_autoads_link.className.replace( 'hidden', '' );
							}
							advanced_ads_frontend_checks.showCount();
						}
					}
					
					// inform the user that AdSense Auto ads code was found
					function advads_highlight_adsense_auto_ads(){
						var auto_ads_pattern = /enable_page_level_ads: true/m
						if (auto_ads_pattern.exec( jQuery('head').text() ) ){
						    var advads_autoads_code_link = document.querySelector( '#wp-admin-bar-advanced_ads_ad_health_auto_ads_found' );
						    advads_autoads_code_link.className = advads_autoads_code_link.className.replace( 'hidden', '' );
						}
					}
				<?php endif; 
				/**
				 * code to check if current user gave consent to show ads
				 */
				$privacy_state = Advanced_Ads_Privacy::get_instance()->get_state();
				if( 'not_needed' !== $privacy_state ) :
				?>advanced_ads_ready( function() {
					var state = ( advads.privacy ) ? advads.privacy.get_state() : "";
					var advads_consent_link = document.querySelector( '#wp-admin-bar-advanced_ads_ad_health_consent_missing.hidden' );
					if ( 'unknown' === state && advads_consent_link ) {
						advads_consent_link.className = advads_consent_link.className.replace( 'hidden', '' );
					}

					advanced_ads_frontend_checks.showCount();
				});
				<?php endif; ?>
		})(document, window);
		</script>
		<?php echo Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
	}

	/**
	 * Inject JS after ad content.
	 *
	 * @param str $content ad content
	 * @param obj $ad Advanced_Ads_Ad
	 * @return str $content ad content
	 */
	public function after_ad_output( $content = '', Advanced_Ads_Ad $ad ) {
		if ( ! isset( $ad->args['frontend-check'] ) ) { return $content; }

		// Allow DFP debugging by showing a link that points to the current URL with the 'googfc' parameter.
		if ( $ad->type === 'plain' && preg_match( '/gpt\.js/', $content ) ) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
			var advads_dfp_link = document.querySelector( '.advanced_ads_ad_health_debug_dfp_link.hidden' );
			if ( advads_dfp_link ) {
				advads_dfp_link.className = advads_dfp_link.className.replace( 'hidden', '' );
				advanced_ads_frontend_checks.showCount();
			}
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		if ( Advanced_Ads_Ad_Debug::is_https_and_http( $ad ) ) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
				var ad_id = '<?php echo $ad->id; ?>';
				advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_has_http', ad_id );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		if ( ! $this->can_use_head_placement( $content, $ad ) ) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
			var ad_id = '<?php echo $ad->id; ?>';
			advanced_ads_frontend_checks.add_item_to_node( '.advanced_ads_ad_health_incorrect_head', ad_id );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		$adsense_options = Advanced_Ads_AdSense_Data::get_instance()->get_options();
		if ( 'adsense' === $ad->type
			&& ! empty( $ad->args['cache_busting_elementid'] )
			&& ! isset( $adsense_options['violation-warnings-disable'] )
		) {
			ob_start(); ?>
			<script>advanced_ads_ready( function() {
				var ad_id = '<?php echo $ad->id; ?>';
				var wrapper = '#<?php echo $ad->args['cache_busting_elementid']; ?>';
				advanced_ads_frontend_checks.advads_highlight_hidden_adsense( wrapper );
			});</script>
			<?php
			$content .= Advanced_Ads_Utils::get_inline_asset( ob_get_clean() );
		}

		return $content;
	}


	/**
	 * Check if the 'Header Code' placement can be used to delived the ad.
	 *
	 * @param string $content Ad content.
	 * @param obj $ad Advanced_Ads_Ad
	 * @return bool
	 */
	private function can_use_head_placement( $content, Advanced_Ads_Ad $ad ) {

		if ( ! $ad->is_head_placement ) {
			return true;
		}
		if ( ! $dom = $this->get_ad_dom( $content ) ) {
			return true;
		}

		$body = $dom->getElementsByTagName( 'body' )->item( 0 );

		$count = $body->childNodes->length;
		for ( $i = 0; $i < $count; $i++ ) {
			$node = $body->childNodes->item( $i );

			if ( XML_TEXT_NODE  === $node->nodeType ) {
				return false;
			}

			if ( XML_ELEMENT_NODE === $node->nodeType
				&& ! in_array( $node->nodeName, array( 'meta', 'link', 'title', 'style', 'script', 'noscript', 'base' ) ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Convert ad content to a DOMDocument.
	 *
	 * @param string $content
	 * @return DOMDocument|false
	 */
	private function get_ad_dom( $content ) {
		if ( ! extension_loaded( 'dom' ) ) {
			return false;
		}
		$libxml_previous_state = libxml_use_internal_errors( true );
		$dom = new DOMDocument();
		$result = $dom->loadHTML( '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>' . $content . '</body></html>' );

		libxml_clear_errors();
		libxml_use_internal_errors( $libxml_previous_state );

		if ( ! $result ) {
			return false;
		}

		return $dom;
	}
}

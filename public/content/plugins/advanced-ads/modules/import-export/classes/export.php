<?php
class Advanced_Ads_Export {
    /**
     * @var Advanced_Ads_Export
     */
    private static $instance;

    /**
     * status messages
     */
    private $messages = array();

	private function __construct() {

		$page_hook = 'admin_page_advanced-ads-import-export';
		// execute before headers are sent
		add_action( 'load-' . $page_hook, array( $this, 'download_export_file' ) );
	}

	/**
	 * Return an instance of this class.
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Handle form submissions
	 */
	public function download_export_file() {
		$action = Advanced_Ads_Admin::get_instance()->current_action();

		if ( $action === 'export' ) {
			if ( ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options') ) ) {
				return;
			}

			check_admin_referer( 'advads-export' );

			if ( isset( $_POST['content'] ) ) {
				$this->process( $_POST['content'] );
			}
		}
	}

	/**
	 * Generate XML file
	 */
	private function process( array $content ) {
		global $wpdb;

		@set_time_limit( 0 );
		@ini_set( 'memory_limit', apply_filters( 'admin_memory_limit', WP_MAX_MEMORY_LIMIT ) );

		$export = array();
		$advads_ad_groups = get_option( 'advads-ad-groups', array() );

		if ( in_array( 'ads', $content ) ) {
			$advads_ad_weights =  get_option( 'advads-ad-weights', array() );

			$ads = array();
			$export_fields = implode( ', ', array(
				'ID',
				'post_date',
				'post_date_gmt',
				'post_content',
				'post_title',
				'post_password',
				'post_name',
				'post_status',
				'post_modified',
				'post_modified_gmt',
				'guid'
			) );

			$posts = $wpdb->get_results( $wpdb->prepare( "SELECT $export_fields FROM {$wpdb->posts} where post_type = '%s' and post_status not in ('trash', 'auto-draft')", Advanced_Ads::POST_TYPE_SLUG ), ARRAY_A );

			foreach ( $posts as $k => $post ) {
				if ( ! empty( $post['post_content'] ) ) {
					// wrap images in <advads_import_img></advads_import_img> tags
					$search = '/' . preg_quote( home_url(), '/' ) . '(\S+?)\.(jpg|jpeg|gif|png)/i';
					$post['post_content']  = preg_replace( $search, '<advads_import_img>\\0</advads_import_img>', $post['post_content']  );
				}

			    $ads[$k] = $post;

			    if ( in_array( 'groups', $content ) ) {
				    $terms = wp_get_object_terms( $post['ID'], 'advanced_ads_groups' );

					foreach ( (array) $terms as $term ) {
						$group_info = array(
							'term_id' => $term->term_id,
							'slug' => $term->slug,
							'name' => $term->name,
						);

						if ( isset( $advads_ad_groups[ $term->term_id ] ) ) {
							$group_info += $advads_ad_groups[ $term->term_id ];
						}

						if ( isset( $advads_ad_weights[ $term->term_id ][ $post['ID'] ] ) ) {
							$group_info['weight'] = $advads_ad_weights[ $term->term_id ][ $post['ID'] ];
						}

						$ads[ $k ]['groups'][] = $group_info;
					}
			    }

			    $postmeta = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $post['ID'] ) ) );

				foreach ( $postmeta as $meta ) {
					if ( $meta->meta_key === '_edit_lock' ) {
						continue;
					}
					if ( $meta->meta_key === Advanced_Ads_Ad::$options_meta_field ) {
						$ad_options = maybe_unserialize( $meta->meta_value );
						if ( isset( $ad_options['output']['image_id'] ) ) {
							$image_id = absint( $ad_options['output']['image_id'] );
							if ( $atached_img = wp_get_attachment_url( $image_id) ) {
								$ads[ $k ]['attached_img_url'] = $atached_img;
							}
						}
						$ads[ $k ]['meta_input'][ $meta->meta_key ] = $ad_options;
					} else {
						$ads[ $k ]['meta_input'] [$meta->meta_key ] = $meta->meta_value;
			        }
			    }
			}

			if ( $ads ) {
				$export['ads'] = $ads;
			}
		}

	    if ( in_array( 'groups', $content ) ) {
			$terms = Advanced_Ads::get_instance()->get_model()->get_ad_groups();
			foreach ( $terms as $term ) {
				$group_info = array(
					'term_id' => $term->term_id,
					'slug' => $term->slug,
					'name' => $term->name,
				);

				if ( isset( $advads_ad_groups[ $term->term_id ] ) ) {
					$group_info += $advads_ad_groups[ $term->term_id ];
				}

				$export['groups'][] = $group_info;
			}
	    }

		if ( in_array( 'placements', $content ) ) {
			$placements = Advanced_Ads::get_instance()->get_model()->get_ad_placements_array();

			// prevent nodes starting with number
			foreach ( $placements as $key => &$placement ) {
				$placement['key'] = $key;
			}

			$export['placements'] = array_values( $placements );
		}

		if ( in_array( 'options', $content ) ) {
			$export['options'] = apply_filters( 'advanced-ads-export-options', array (
				ADVADS_SLUG => Advanced_Ads::get_instance()->options(),
				ADVADS_SLUG . '-internal' => Advanced_Ads::get_instance()->internal_options(),
			) );
		}

		do_action_ref_array( 'advanced-ads-export', array( $content, &$export ) );

		if ( $export ) {
			if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
				error_log( print_r( 'Array to decode', true ) );
				error_log( print_r( $export, true) );
			}

			$filename = 'advanced-ads-' . date( 'Y-m-d' ) . '.xml';

			try {
				$encoded = Advanced_Ads_XmlEncoder::get_instance()->encode( $export, array( 'encoding' => get_option( 'blog_charset' ) ) );
				
				header( 'Content-Description: File Transfer' );
				header( 'Content-Disposition: attachment; filename=' . $filename );
				header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
				echo $encoded;

				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( print_r( $encoded, true ) );
					$decoded = Advanced_Ads_XmlEncoder::get_instance()->decode( $encoded );
					error_log( 'result ' . var_export( $export === $decoded , true ) );
				}

				exit();

			} catch ( Exception $e ) {
				$this->messages[] = array( 'error', $e->getMessage() );
			}
		}
	}

	public function get_messages(){
		return $this->messages;
	}
}
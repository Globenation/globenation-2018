<?php

class Advanced_Ads_Gutenberg {

    private static $instance;
	
	private static $css_class;
	
    private function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
    }

	/**
	 * Register blocks
	 */
	public function init() {
		if ( !function_exists( 'register_block_type' ) ) {
			// no Gutenberg, Abort
			return;
		}
		
		register_block_type( 'advads/gblock', array(
			'editor_script' => ADVADS_BASE . '/gutenberg-ad',
			'render_callback' => array( $this, 'render_ad_selector' ),
		) );
	}
	
	/**
	 *  register back end scripts
	 */
	public function register_scripts() {
		if ( !function_exists( 'register_block_type' ) ) {
			// no Gutenberg, Abort
			return;
		}
		
		wp_register_script(
			ADVADS_BASE . '/gutenberg-ad',
			ADVADS_BASE_URL . 'modules/gutenberg/js/advanced-ads.block.js',
			array( 'wp-blocks', 'wp-element' )
		);

		$model = Advanced_Ads::get_instance()->get_model();
		
		$all_ads = Advanced_Ads::get_ads( array( 'post_status' => array( 'publish' ), 'orderby' => 'title', 'order' => 'ASC' ) );
		$all_groups = $model->get_ad_groups();
		$all_placements = Advanced_Ads::get_ad_placements_array();
		
		$ads = array();
		$groups = array();
		$placements = array();
		
		foreach ( $all_ads as $ad ) {
			$ads[] = array( 'id' => $ad->ID, 'title' => $ad->post_title );
		}
		
		foreach ( $all_groups as $gr ) {
			$groups[] = array( 'id' => $gr->term_id, 'name' => $gr->name );
		}
		
		if ( is_array( $all_placements ) ) {
			ksort( $all_placements );
		}
		
		foreach( $all_placements as $key => $value ) {
			if ( 'sidebar_widget' == $value['type'] ) {
				$placements[] = array( 'id' => $key, 'name' => $value['name'] );
			}
		}
		
		if ( empty( $placements ) ) {
			$placements = false;
		}
		
		$i18n = array(
			'--empty--' => __( '--empty--', 'advanced-ads' ),
			'advads' => __( 'Advanced Ads', 'advanced-ads' ),
			'ads' => __( 'Ads', 'advanced-ads' ),
			'adGroups' => __( 'Ad Groups', 'advanced-ads' ),
			'placements' => __( 'Placements', 'advanced-ads' ),
		);
		
		$inline_script = wp_json_encode(
			array(
				'ads' => $ads,
				'groups' => $groups,
				'placements' => $placements,
				'editLinks' => array(
					'group' => admin_url( 'admin.php?page=advanced-ads-groups' ),
					'placement' => admin_url( 'admin.php?page=advanced-ads-placements' ),
					'ad' => admin_url( 'post.php?post=%ID%&action=edit' ),
				),
				'i18n' => $i18n
			)
		);
		
		// put the inline code with the global variable right before the block's JS file
		wp_add_inline_script( ADVADS_BASE . '/gutenberg-ad', 'var advadsGutenberg = ' . $inline_script, 'before' );
		
	}
	
	/**
	 * Server side rendering for single ad block 
	 */
	public static function render_ad_selector( $attr ) {
		ob_start();
		
		if ( !isset( $attr['itemID'] ) ) {
			ob_end_clean();
			return '';
		}
		
		// the item is an ad
		if ( 0 === strpos( $attr['itemID'], 'ad_' ) ) {
			
			$id = substr( $attr['itemID'], 3 );
			
			// add CSS classes to the wrapper via filter
			if ( isset( $attr['className'] ) ) {
				echo get_ad( absint( $id ), array( 'output' => array( 'class' => explode( ' ', $attr['className'] ) ) ) );
			} else {
				the_ad( absint( $id ) );
			}
			
		} elseif ( 0 === strpos( $attr['itemID'], 'group_' ) ) {
			
			$id = substr( $attr['itemID'], 6 );
			
			if ( isset( $attr['className'] ) ) {
				echo get_ad_group( $id, array( 'output' => array( 'class' => explode( ' ', $attr['className'] ) ) ) );
			} else {
				the_ad_group( $id );
			}
			
		} elseif ( 0 === strpos( $attr['itemID'], 'place_' ) ) {
			
			$id = substr( $attr['itemID'], 6 );
			
			if ( isset( $attr['className'] ) ) {
				echo get_ad_placement( $id, array( 'output' => array( 'class' => explode( ' ', $attr['className'] ) ) ) );
			} else {
				the_ad_placement( $id );
			}
			
		}
		
		return ob_get_clean();
	}
	
    /**
     * Return the unique instance 
     */
    public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
    }

}

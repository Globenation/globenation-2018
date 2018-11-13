<?php
defined( 'ABSPATH'  ) || exit;

class Advanced_Ads_Admin_Menu {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the ad group page
	 *
	 * @since    1.0.0
	 * @var      string
	 */
	protected $ad_group_hook_suffix = null;

	private function __construct() {
		// Add menu items
		add_action( 'admin_menu', array($this, 'add_plugin_admin_menu') );
		add_action( 'admin_head', array( $this, 'highlight_menu_item' ) );

		$this->plugin_slug = Advanced_Ads::get_instance()->get_plugin_slug();
		$this->post_type = constant( 'Advanced_Ads::POST_TYPE_SLUG' );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$has_ads = Advanced_Ads::get_number_of_ads();
	    
		// use the overview page only when there is an ad already
		if( $has_ads ){
			add_menu_page(
				__( 'Overview', 'advanced-ads' ), 'Advanced Ads', Advanced_Ads_Plugin::user_cap( 'advanced_ads_see_interface'), $this->plugin_slug, array($this, 'display_overview_page'), 'dashicons-chart-line', '58.74'
			);
		}
		// forward Ads link to new-ad page when there is no ad existing yet.
		// the target to post-new.php needs the extra "new" or any other attribute, since the original add-ad link was removed by CSS using the exact href attribute as a selector
		$target = ( ! $has_ads ) ? 'post-new.php?post_type=' . Advanced_Ads::POST_TYPE_SLUG . '&new=new' : 'edit.php?post_type=' . Advanced_Ads::POST_TYPE_SLUG;
		add_submenu_page(
			$this->plugin_slug, __( 'Ads', 'advanced-ads' ), __( 'Ads', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads'), $target
		);
		
		// display the main overview page as second item when we don’t have ads yet
		if( ! $has_ads ){
			add_menu_page(
				__( 'Overview', 'advanced-ads' ), 'Advanced Ads', Advanced_Ads_Plugin::user_cap( 'advanced_ads_see_interface'), $this->plugin_slug, array($this, 'display_overview_page'), 'dashicons-chart-line', '58.74'
			);

			add_submenu_page(
				$this->plugin_slug, __( 'Overview', 'advanced-ads' ), __( 'Overview', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_see_interface'), $this->plugin_slug, array($this, 'display_overview_page')
			);
		}

		// hidden by css; not placed in 'options.php' in order to highlight the correct item, see the 'highlight_menu_item()'
		if ( ! current_user_can( 'edit_posts' ) ) {
			add_submenu_page(
				$this->plugin_slug, __( 'Add New Ad', 'advanced-ads' ), __( 'New Ad', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads'), 'post-new.php?post_type=' . Advanced_Ads::POST_TYPE_SLUG
			);
		}

		$this->ad_group_hook_suffix = add_submenu_page(
			$this->plugin_slug, __( 'Ad Groups & Rotations', 'advanced-ads' ), __( 'Groups & Rotation', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_edit_ads'), $this->plugin_slug . '-groups', array($this, 'ad_group_admin_page')
		);

		// add placements page
		add_submenu_page(
			$this->plugin_slug, __( 'Ad Placements', 'advanced-ads' ), __( 'Placements', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_placements'), $this->plugin_slug . '-placements', array($this, 'display_placements_page')
		);
		// add settings page
		Advanced_Ads_Admin::get_instance()->plugin_screen_hook_suffix = add_submenu_page(
			$this->plugin_slug, __( 'Advanced Ads Settings', 'advanced-ads' ), __( 'Settings', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options'), $this->plugin_slug . '-settings', array($this, 'display_plugin_settings_page')
		);
		// add support page
		/*add_submenu_page(
			$this->plugin_slug, __( 'Support', 'advanced-ads' ), __( 'Support', 'advanced-ads' ), Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options'), $this->plugin_slug . '-support', array($this, 'display_support_page')
		);*/
		
		/**
		 * since we forward the support link to the settings page, we need to add the menu item manually
		 * could break if WordPress changes the API at one point, but it didn’t do that for many years
		 */
		global $submenu;
		if(current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options') ) ){
			$submenu['advanced-ads'][] = array( 
			    __('Support', 'advanced-ads' ), // title
			    Advanced_Ads_Plugin::user_cap( 'advanced_ads_manage_options'), // capability
			    admin_url( 'admin.php?page=advanced-ads-settings#top#support' ),
			    __('Support', 'advanced-ads' ), // not sure what this is, but it is in the API
			);
		}

		// allows extensions to insert sub menu pages
		do_action( 'advanced-ads-submenu-pages', $this->plugin_slug );
	}

	/**
	 * Highlights the 'Advanced Ads->Ads' item in the menu when an ad edit page is open
	 * @see the 'parent_file' and the 'submenu_file' filters for reference
	 */
	public function highlight_menu_item() {
		global $parent_file, $submenu_file, $post_type;
		if ( $post_type === $this->post_type ) {
			$parent_file = $this->plugin_slug;
			$submenu_file = 'edit.php?post_type=' . $this->post_type;
		}
	}

	/**
	 * Render the overview page
	 *
	 * @since    1.2.2
	 */
	public function display_overview_page() {

		include ADVADS_BASE_PATH . 'admin/views/overview.php';
	}

	/**
	 * Render the settings page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_settings_page() {
		include ADVADS_BASE_PATH . 'admin/views/settings.php';
	}

	/**
	 * Render the placements page
	 *
	 * @since    1.1.0
	 */
	public function display_placements_page() {
		$placement_types = Advanced_Ads_Placements::get_placement_types();
		$placements = Advanced_Ads::get_ad_placements_array(); // -TODO use model
		$items = Advanced_Ads_Placements::items_for_select();
		// load ads and groups for select field

		// display view
		include ADVADS_BASE_PATH . 'admin/views/placements.php';
	}

	/**
	 * Render the support page
	 *
	 * @since    1.6.8.1
	 */
	public function display_support_page() {

		include ADVADS_BASE_PATH . 'admin/views/support.php';
	}

	/**
	 * Render the ad group page
	 *
	 * @since    1.0.0
	 */
	public function ad_group_admin_page() {

		$taxonomy = Advanced_Ads::AD_GROUP_TAXONOMY;
		$post_type = Advanced_Ads::POST_TYPE_SLUG;
		$tax = get_taxonomy( $taxonomy );

		$action = Advanced_Ads_Admin::get_instance()->current_action();

		// handle new and updated groups
		if ( 'editedgroup' == $action ) {
			$group_id = (int) $_POST['group_id'];
			check_admin_referer( 'update-group_' . $group_id );

			if ( ! current_user_can( $tax->cap->edit_terms ) ) {
				wp_die( __( 'Sorry, you are not allowed to access this feature.', 'advanced-ads' ) ); }

			// handle new groups
			if ( 0 == $group_id ) {
				$ret = wp_insert_term( $_POST['name'], $taxonomy, $_POST );
				if ( $ret && ! is_wp_error( $ret ) ) {
					$forced_message = 1; }
				else {
					$forced_message = 4; }
				// handle group updates
			} else {
				$tag = get_term( $group_id, $taxonomy );
				if ( ! $tag ) {
					wp_die( __( 'You attempted to edit an ad group that doesn&#8217;t exist. Perhaps it was deleted?', 'advanced-ads' ) ); }

				$ret = wp_update_term( $group_id, $taxonomy, $_POST );
				if ( $ret && ! is_wp_error( $ret ) ) {
					$forced_message = 3; }
				else {
					$forced_message = 5; }
			}
			// deleting items
		} elseif ( $action == 'delete' ){
			$group_id = (int) $_REQUEST['group_id'];
			check_admin_referer( 'delete-tag_' . $group_id );

			if ( ! current_user_can( $tax->cap->delete_terms ) ) {
				wp_die( __( 'Sorry, you are not allowed to access this feature.', 'advanced-ads' ) ); }

			wp_delete_term( $group_id, $taxonomy );

			$forced_message = 2;
		}

		// handle views
		switch ( $action ) {
			case 'edit' :
				$title = $tax->labels->edit_item;
				if ( isset($_REQUEST['group_id']) ) {
					$group_id = absint( $_REQUEST['group_id'] );
					$tag = get_term( $group_id, $taxonomy, OBJECT, 'edit' );
				} else {
					$group_id = 0;
					$tag = false;
				}

				include ADVADS_BASE_PATH . 'admin/views/ad-group-edit.php';
				break;

			default :
				$title = $tax->labels->name;
				$wp_list_table = _get_list_table( 'WP_Terms_List_Table' );

				// load template
				include ADVADS_BASE_PATH . 'admin/views/ad-group.php';
		}
	}

}
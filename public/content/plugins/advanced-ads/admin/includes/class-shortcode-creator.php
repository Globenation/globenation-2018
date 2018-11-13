<?php
/**
 * shortcode generator for TinyMCE editor
 *
 */
class Advanced_Ads_Shortcode_Creator {
	/**
	 * instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	private function __construct() {
		add_action( 'init', array ( $this, 'init' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function init() {
		$options = Advanced_Ads::get_instance()->options();

		if ( 'true' != get_user_option( 'rich_editing' )
			|| ! current_user_can( Advanced_Ads_Plugin::user_cap( 'advanced_ads_place_ads' ) )
			|| defined( 'ADVANCED_ADS_DISABLE_SHORTCODE_BUTTON' )
			|| ! empty( $options['disable-shortcode-button'] )
		) {
			return;
		}

		add_filter( 'mce_external_plugins', array( $this, 'add_plugin' ) );
		add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );
		add_filter( 'mce_external_languages', array( $this, 'add_l10n' ) );
		add_action( 'wp_ajax_advads_content_for_shortcode_creator', array( $this, 'get_content_for_shortcode_creator' ) );

		add_filter( 'the_editor', array( $this, 'add_addblocker_warning' ) );
		add_action( 'admin_footer', array( $this, 'maybe_show_adblocker_warning' ) );
	}

	/**
	 * add the plugin to array of external TinyMCE plugins
	 *
	 */
	public function add_plugin( $plugin_array ) {
		$plugin_array['advads_shortcode'] = ADVADS_BASE_URL . 'admin/assets/js/shortcode.js';
		return $plugin_array;
	}

	/**
	 * add button to tinyMCE window
	 *
	 */
	public function register_buttons( $buttons ) {
		$buttons[] = 'advads_shortcode_button';
		return $buttons;
	}

	/**
	 * prints html select field for shortcode creator
	 *
	 */
	public function get_content_for_shortcode_creator() {
		if ( ! ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) ) {
			return;
		}

		$items = self::items_for_select(); ?>

		<select id="advads-select-for-shortcode">
			<option value=""><?php _e( '--empty--', 'advanced-ads' );  ?></option>
			<?php if ( isset( $items['ads'] ) ) : ?>
				<optgroup label="<?php _e( 'Ads', 'advanced-ads' ); ?>">
					<?php foreach ( $items['ads'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo $_item_id; ?>"><?php echo $_item_title; ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>
			<?php if ( isset( $items['groups'] ) ) : ?>
				<optgroup label="<?php _e( 'Ad Groups', 'advanced-ads' ); ?>">
					<?php foreach ( $items['groups'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo $_item_id; ?>"><?php echo $_item_title; ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>			
			<?php if ( isset( $items['placements'] ) ) : ?>
				<optgroup label="<?php _e( 'Placements', 'advanced-ads' ); ?>">
					<?php foreach ( $items['placements'] as $_item_id => $_item_title ) : ?>
					<option value="<?php echo $_item_id; ?>"><?php echo $_item_title; ?></option>
					<?php endforeach; ?>
				</optgroup>
			<?php endif; ?>		
		</select><?php
		exit();
	}

	/**
	 * get items for item select field
	 *
	 * @return arr $select items for select field
	 */
	public static function items_for_select(){
		$select = array();
		$model = Advanced_Ads::get_instance()->get_model();

		// load all ads
		$ads = $model->get_ads( array( 'orderby' => 'title', 'order' => 'ASC' ) );
		foreach ( $ads as $_ad ){
			$select['ads']['ad_' . $_ad->ID] = $_ad->post_title;
		}

		// load all ad groups
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ){
			$select['groups']['group_' . $_group->term_id] = $_group->name;
		}

		// load all placements
		$placements = $model->get_ad_placements_array();
		ksort( $placements );
		foreach ( $placements as $key => $_placement ) {
			$select['placements']['placement_' . $key] = $_placement['name'];
		}

		return $select;
	}

	/**
	 * add localisation
	 */
	public function add_l10n( $mce_external_languages ) {
		$mce_external_languages[ 'advads_shortcode' ] = ADVADS_BASE_PATH . 'admin/includes/shortcode-creator-l10n.php';
		return $mce_external_languages;
	}

	/**
	 * Add a warning above TinyMCE editor.
	 *
	 * @param string $output Editor's HTML markup.
	 */
	public function add_addblocker_warning( $output ) {
		ob_start(); ?>
		<div style="display: none; margin: 10px 8px; color: red;" class="advanced-ads-shortcode-button-warning"><?php 
		printf( __ ( 'Please, either switch off your ad blocker or disable the shortcode button in the <a href="%s" target="_blank">settings</a>.', 'advanced-ads' ), 
			admin_url( 'admin.php?page=advanced-ads-settings' ) ); ?>
		</div>
		<?php
		return ob_get_clean() . $output;
	}

	/**
	 * Show a warning above TinyMCE editor when an adblock is enabled.
	 */
	public function maybe_show_adblocker_warning() { ?>
		<script>
		(function(){
			if ( 'undefined' === typeof advanced_ads_adblocker_test ) {
				try {
				    var messages = document.querySelectorAll( '.advanced-ads-shortcode-button-warning' )
				} catch ( e ) { return; }
				for ( var i = 0; i < messages.length; i++ ) {
				    messages[ i ].style.display = 'block';
				}
			}
		})();
		</script>
		<?php
	}

}

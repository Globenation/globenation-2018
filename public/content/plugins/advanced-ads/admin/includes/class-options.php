<?php
defined( 'ABSPATH'  ) || exit;

/**
 * logic to render options for ads, groups and placements
 */

class Advanced_Ads_Admin_Options {
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	private function __construct() {
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
	 * create a wrapper for a single option line
	 * 
	 * @param   str	    $id		internal id of the option wrapper
	 * @param   str	    $title	label of the option
	 * @param   str	    $content	content of the option
	 * @param   str	    $description  description of the option
	 * 
	 */
	public static function render_option( $id, $title, $content, $description = '' ){
		
		/**
		 * this filter allows to extend the class dynamically by add-ons
		 * this would allow add-ons to dynamically hide/show only attributes belonging to them, practically not used now
		 */
		$class = apply_filters( 'advanced-ads-option-class', $id );
		?>
		<div class="advads-option advads-option-<?php echo $class; ?>">
		    <span><?php echo $title ?></span>
		    <div>
			<?php echo $content; ?>
			<?php if( $description ) : echo '<p class="description">'. $description .'</p>'; endif; ?>
		    </div>
		</div><?php
	}

}

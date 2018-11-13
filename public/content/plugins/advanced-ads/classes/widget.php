<?php
/**
 * Advanced Ads Widget
 *
 * @package   Advanced_Ads_Widget
 * @author    Thomas Maier <thomas.maier@webgilde.com>
 * @license   GPL-2.0+
 * @link      http://webgilde.com
 * @copyright 2014 Thomas Maier, webgilde GmbH
 */

/**
 * Ad widget
 *
 */
class Advanced_Ads_Widget extends WP_Widget {

	function __construct() {
		$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		$classname = $prefix . 'widget';

		$widget_ops = array('classname' => $classname, 'description' => __( 'Display Ads and Ad Groups.', 'advanced-ads' ));
		$control_ops = array();
		$base_id = Advanced_Ads_Widget::get_base_id();

		parent::__construct( $base_id,'Advanced Ads', $widget_ops, $control_ops );

		add_filter( 'q2w3-fixed-widgets', array( $this, 'q2w3_replace_frontend_id' ) );
	}

	function widget($args, $instance) {
		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		extract( $args );
		$item_id = empty($instance['item_id']) ? '' : $instance['item_id'];

		$output = self::output( $item_id );
		if( $output == '' ){
		    return;
		}

		$before_widget = $this->maybe_replace_frontend_id( $before_widget, $instance );

		echo $before_widget;
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}
		echo $output;
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = $new_instance['title'];
		$instance['item_id'] = $new_instance['item_id'];

		// Allow to remove/replace id for new widgets and if it was allowed earlier.
		if ( $old_instance === array() || ! empty( $old_instance['remove-widget-id'] ) ) {
			$instance['remove-widget-id'] = true;
		}
		return $instance;
		return $instance;
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array('title' => '', 'item_id' => '') );
		$title = strip_tags( $instance['title'] );
		$elementid = $instance['item_id'];

		?><p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'advanced-ads' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p><?php

			$items = array_merge( self::items_for_select(), self::widget_placements_for_select() );
		?>
        <select id="<?php echo $this->get_field_id( 'item_id' ); ?>" name="<?php echo $this->get_field_name( 'item_id' ); ?>">
            <option value=""><?php _e( '--empty--', 'advanced-ads' );  ?></option>
            <?php if ( isset($items['placements']) ) : ?>
            <optgroup label="<?php _e( 'Placements', 'advanced-ads' ); ?>">
            <?php foreach ( $items['placements'] as $_item_id => $_item_title ) : ?>
            <option value="<?php echo $_item_id; ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo $_item_title; ?></option>
            <?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
            <?php if ( isset($items['groups']) ) : ?>
            <optgroup label="<?php _e( 'Ad Groups', 'advanced-ads' ); ?>">
            <?php foreach ( $items['groups'] as $_item_id => $_item_title ) : ?>
            <option value="<?php echo $_item_id; ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo $_item_title; ?></option>
            <?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
            <?php if ( isset($items['ads']) ) : ?>
            <optgroup label="<?php _e( 'Ads', 'advanced-ads' ); ?>">
            <?php foreach ( $items['ads'] as $_item_id => $_item_title ) : ?>
            <option value="<?php echo $_item_id; ?>" <?php selected( $_item_id, $elementid ); ?>><?php echo $_item_title; ?></option>
            <?php endforeach; ?>
            </optgroup>
            <?php endif; ?>
        </select><?php
	}

	 /**
	 * get items for widget select field
	 *
	 * @since 1.2
	 * @return arr $select items for select field
	 */
	static function items_for_select(){
		$select = array();
		$model = Advanced_Ads::get_instance()->get_model();

		// load all ads
		$ads = $model->get_ads( array('orderby' => 'title', 'order' => 'ASC') );
		foreach ( $ads as $_ad ){
			$select['ads']['ad_' . $_ad->ID] = $_ad->post_title;
		}

		// load all ad groups
		$groups = $model->get_ad_groups();
		foreach ( $groups as $_group ){
			$select['groups']['group_' . $_group->term_id] = $_group->name;
		}

		return $select;
	}
	
	/**
	 * get widget placements for select field
	 * 
	 * @since 1.6.11
	 * @return arr $items for select field
	 */
	public static function widget_placements_for_select(){
		$select = array();
		$placements = Advanced_Ads::get_ad_placements_array();
		
		if ( is_array( $placements ) ) {
			ksort( $placements );
		}
		
		foreach( $placements as $_placement_slug => $_placement ){
			if( isset( $_placement['type'] ) && 'sidebar_widget' === $_placement['type'] ){
				$select['placements']['placement_' . $_placement_slug ] = $_placement['name'];
			}
		}
		
		return $select;
	}
	
	/**
	 * return content of an in a widget
	 *
	 * @since 1.2
	 * @param string $id slug of the display
	 */
	static function output($id = ''){
		// get placement data for the slug
		if ( empty($id) ) { return; }

		$item = explode( '_', $id, 2 );
		
		if ( isset($item[1]) ) {
			$item_id = $item[1];
		} elseif (empty($item_id)) {
			return;
		}

		// return either ad or group content
		if ( $item[0] == 'ad' ){
			return get_ad( absint( $item_id ) );
		} elseif ( $item[0] == 'group' ){
			return get_ad_group( absint( $item_id ) );
		} elseif ( $item[0] == 'placement' ){
			return get_ad_placement( $item_id );
		}

		return;
	}

	/**
	 * get the base id of the widget
	 *
	 * @return string
	 */
	public static function get_base_id() {
		$options = Advanced_Ads_Plugin::get_instance()->options();

		// deprecated to keep previously changed prefixed working
		$prefix2 = ( isset( $options['id-prefix'] ) && $options['id-prefix'] !== '' ) ? $options['id-prefix'] : 'advads_ad_';
		return $prefix2 . 'widget';
	}

	/**
	 * Get frontend widget id.
	 *
	 * @param int $number Unique ID number of the current widget instance.
	 * @return str
	 */
	private function get_frontend_id( $number ) {
		$prefix = Advanced_Ads_Plugin::get_instance()->get_frontend_prefix();
		return $prefix . 'widget-' . $number;
	}

	/**
	 * Make it harder for ad blockers to block the widget.
	 * removes the pre-defined widget ID (e.g., advads_ad_widget-20) and replaces it with one that uses the individual frontend prefix
	 *
	 * @param str $before_widget
	 * @param array $instance Settings for the current widget instance.
	 * @return str $before_widget
	 */
	private function maybe_replace_frontend_id( $before_widget, $instance ) {
		if ( ! empty( $instance['remove-widget-id'] ) 
			|| defined( 'JNEWS_THEME_ID' ) // the JNews theme overrides the widget ID and resets it, so we target this specifically.
			) {
			$pattern = '#\sid=("|\')[^"\']+["\']#';
			if ( ( defined( 'ADVANCED_ADS_SHOW_WIDGET_ID' ) && ADVANCED_ADS_SHOW_WIDGET_ID )
				|| ! empty( $instance['q2w3_fixed_widget'] )
			) {
				// Replace id.
				$number = ! empty( $this->number ) ?  $this->number : '';
				$before_widget = preg_replace( $pattern, ' id=$01' . $this->get_frontend_id( $number ) . '$01', $before_widget );
			} else {
				// Remove id.
				$before_widget = preg_replace( $pattern, '', $before_widget );
			}
		}
		return $before_widget;
	}

	/**
	 * Provide the 'Q2W3 Fixed Widget' plugin with the new frontend widget id.
	 *
	 * @param array $sidebars_widgets
	 * @return array $sidebars_widgets
	 */
	public function q2w3_replace_frontend_id( $sidebars_widgets ) {
		foreach ( $sidebars_widgets as $sidebar => $widgets ) {
			foreach ( $widgets as $k => $widget ) {
				$pos = strrpos( $widget, '-' );
				$option_name = substr( $widget, 0, $pos );
				$number = substr( $widget, $pos + 1 );

				if ( $option_name === self::get_base_id() ) {
					$widget_options = get_option('widget_' . $option_name);
					if ( ! empty( $widget_options[ $number ]['remove-widget-id'] ) ) {
						$sidebars_widgets[ $sidebar ][ $k ] = $this->get_frontend_id( $number );
					}
				}

			}
		}
		return $sidebars_widgets;
	}

}

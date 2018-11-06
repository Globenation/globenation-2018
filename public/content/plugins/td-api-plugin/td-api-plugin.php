<?php
/*
Plugin Name: td-api-plugin
Plugin URI: http://tagdiv.com
Description: tagDiv API plugin
Author: tagDiv
Version: 1.0.2
Author URI: http://tagdiv.com
*/
class td_api_plugin {
    var $plugin_url = '';
    var $plugin_path = '';

    function __construct() {
        $this->plugin_url = plugins_url('', __FILE__); // path used for elements like images, css, etc which are available on end user
        $this->plugin_path = dirname(__FILE__); // used for internal (server side) files

        add_action('td_global_after', array($this, 'hook_td_global_after')); // hook used to add or modify items via Api
        add_action('admin_enqueue_scripts', array('td_api_plugin', 'td_plugin_wpadmin_css')); // hook used to add custom css for wp-admin area
        add_action('wp_enqueue_scripts', array('td_api_plugin', 'td_plugin_frontend_css')); // hook used to add custom css used on frontend area
    }

    static function td_plugin_wpadmin_css() {
        wp_enqueue_style('td-plugin-framework', plugins_url('', __FILE__) . '/wp-admin/style.css'); // backend css (admin_enqueue_scripts)
    }

    static function td_plugin_frontend_css() {
        wp_enqueue_style('td-plugin-framework', plugins_url('', __FILE__) . '/css/style.css'); // frontend css (wp_enqueue_scripts)
    }

    function hook_td_global_after()    { //add the api code inside this function
// Add a new module
        td_api_module::add('td_module_77',
            array(
                'file' => $this->plugin_path . "/modules/td_module_77.php",
                'text' => 'Module 77',
                'img' => $this->plugin_url . '/images/modules/td_module_77.png',
                'used_on_blocks' => array('td_block_77'),
                'excerpt_title' => 12,
                'excerpt_content' => 25,
                'enabled_on_more_articles_box' => true,
                'enabled_on_loops' => true,
                'uses_columns' => true, // if the module uses columns on the page template + loop
                'category_label' => true,
                'class' => 'td_module_wrap td-animation-stack',
            )
        );

// Add a new block
        td_api_block::add('td_block_77',
            array(
                'map_in_visual_composer' => true,
                "name" => 'Block 77',
                "base" => 'td_block_77',
                "class" => 'td_block_77',
                "controls" => "full",
                "category" => 'Blocks',
                'icon' => 'icon-pagebuilder-td_block_77',
                'file' => $this->plugin_path . '/shortcodes/td_block_77.php',
                "params" => array_merge(
                    td_config::get_map_block_general_array(),
                    td_config::get_map_filter_array(),
                    td_config::get_map_block_ajax_filter_array(),
                    td_config::get_map_block_pagination_array()
                )
            )
        );
    }

}
new td_api_plugin();
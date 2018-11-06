<?php
/*
	Plugin Name: tagDiv Speed Booster
	Plugin URI: http://tagdiv.com
	Description: Speed booster for Newspaper 7 and Newsmag 3 themes - It moves the styles and all the scripts of the theme to the bottom when needed. It activates internal theme optimizations for better speed and it adds async js .
	Author: tagDiv
	Version: 4.9.1 | built on 12.10.2018 9:30
	Author URI: http://tagdiv.com
*/

/*
    4.9.3 - added wpml to supported plugins list
    4.9 - tagdiv composer support
        - theme's bbpress style moved at the bottom
    4.8 - child theme support fix
    4.7 - added Newspaper image opacity issue fix
    4.6 - changed the google fonts style
        - added revolution slider to allowed plugins list
    4.5 - enqueue order changed to match the default plugin order and Newsmag 3 new grid
    4.4 - woocommerce prettyPhoto css moved at the bottom
    4.3 - enqueue order fix for bbpress and visual composer
	4.2 - woocommerce support and style.css moved to bottom on Newspaper
    4.1 - buddyPress fix
    4.0 - the plugin was rewritten to better support Newspaper 6
        - if any unsupported plugin is detected, Speed Booster will disable itself
        - the js and css hooks are separated now, it's easier to modify them this way
        - all the code is cemented now
    3.2 - better bbpress support on newsmag
    3.1 - fixed issue with flashing white on load on newspaper theme
        - better compatibility with themes that do not have wp booster framework
        - Newsmag loads fonts in a bundle now
    3.0 - better visual composer support
    2.8 - Newsmag support added :)
    2.7 - fixed ie 9 10 11 window resize bug
    2.6 - fixed rendering bug on the loading of the page
    2.5 - code improvements, newspaper 4 compatibility
        - makes most of the javascript files use defer parsing
        - better compatibility with revolution slider
    2.4 - updated jquery version
        - support for https
    2.3 - fixed warnings when trying to move javascript files that are not registered
 */


define('TD_SPEED_BOOSTER' , 'v4.9.3');


class td_speed_booster {

    private $style_footer_queue = array(); // here we keep all the stylesheets IDs that we want to move to the footer

    private $is_ie = false; // if the browser is detected as IE, treat it differently

    // here we keep the theme information
    private $td_theme_name = '';
    private $td_theme_version = '';
    private $td_deploy_mode = '';


    private $allowed_plugins = array(
        'bbpress/bbpress.php',
        'contact-form-7/wp-contact-form-7.php',
        'jetpack/jetpack.php',
        'js_composer/js_composer.php',
        'td-speed-booster/td-speed-booster.php',
        'font-awesome-4-menus/n9m-font-awesome-4.php',
        'wordpress-seo/wp-seo.php',
        'wp-user-avatar/wp-user-avatar.php',
        'td-social-counter/td-social-counter.php',
        'wp-super-cache/wp-cache.php',
        'woocommerce/woocommerce.php',
        'td-mobile-plugin/td-mobile-plugin.php',
        'revslider/revslider.php',
        'td-composer/td-composer.php',
	    'td-multi-purpose/td-multi-purpose.php',
        'td-newsletter/td-newsletter.php',
        'td-amp/td-amp.php',
        'sitepress-multilingual-cms/sitepress.php',
        'td-cloud-library/td-cloud-library.php',

    );



    // this class is instantiated at the bottom of this page
    function __construct() {

        // disable the plugins if there are incompatible plugins
        $active_plugins = get_option('active_plugins');
        //print_r($active_plugins);
        //die;
        foreach ($active_plugins as $active_plugin) {
            if (!in_array($active_plugin, $this->allowed_plugins)) {
                define('TD_SPEED_BOOSTER_INCOMPATIBLE' , $active_plugin);
                return;
            }
        }

        add_action('td_wp_booster_loaded', array($this, 'start_booster'));
    }



    /**
     * everything starts from here
     */
    function start_booster() {
        // read the theme version and name if defined
        if (defined('TD_THEME_VERSION') and defined('TD_THEME_NAME') and defined('TD_DEPLOY_MODE')) {
            $this->td_theme_version = TD_THEME_VERSION;
            $this->td_theme_name = TD_THEME_NAME;
            $this->td_deploy_mode = TD_DEPLOY_MODE;
        } else {
            return;
        }


        // detect IE 8 9 10 11
        if (
            !empty($_SERVER['HTTP_USER_AGENT'])
            and (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) or (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false))
        ) {
            $this->is_ie = true;
        }

        // add hooks
        add_action('wp_enqueue_scripts', array($this, 'css_mover'), 1002);              // 1002 priority - because visual composer has 1000 and we use 1001 in the wp010 theme
        add_action('wp_enqueue_scripts', array($this, 'js_mover'), 1002);   // 1002 priority - because visual composer has 1000 and we use 1001 in the wp010 theme
        add_action('wp_footer', array($this, 'render_footer_styles'), 15);


        // hide the body only on IE and Newspaper 6+
        if (
            $this->is_ie === false
        ) {
            add_action('wp_head', array($this, 'hide_body_inline_css'), 15);
        }


        /**
         * jetpack is 'special' - it has to be moved like this. It has now only one CSS
         * the two hooks are needed!
         * @since 27.05.2015
         */
        add_action('wp_print_styles', array($this, 'jetpack_mover'), 10);
        add_action('wp_print_footer_scripts', array($this, 'jetpack_mover'), 10);

        /**
         * fix opacity issue
         */
        add_action('wp_head', array($this, 'td_demo_flicker_fix'), 1);
    }


    /**
     * fix opacity issue
     * speedbooster moves css at the end of the page, this causes image opacity to switch from 1 to 0 and back to 1 when all images are loaded
     * @since 08.Mar.2015
     */
    function td_demo_flicker_fix() {
        if ($this->td_theme_name == 'Newspaper' && $this->td_deploy_mode == 'demo') {
            echo '<style type="text/css">
                    body.td-animation-stack-type0 .td-animation-stack .entry-thumb,
                    body.td-animation-stack-type0 .post img {
                        opacity: 0;
                    }
                  </style>';
        }
    }


    /**
     * jetpack is 'special' - it has to be moved like this. It has now only one CSS
     * @since 27.05.2015
     */
    function jetpack_mover() {
        $this->move_style_to_footer_queue('jetpack_css');
    }



    /**
     * here we move the css
     */
    function css_mover() {
        //if ($this->td_theme_name == 'Newspaper') { }
        // wp 011 - Newspaper 6

        $this->move_style_to_footer_queue('bbp-default-bbpress');  //bbpress old
        $this->move_style_to_footer_queue('bbp-default'); // bbpress
        $this->move_style_to_footer_queue('contact-form-7');

        if ($this->is_ie === false) {
            // move style.css theme style
            $this->move_style_to_footer_queue('js_composer_front');
            $this->move_style_to_footer_queue('td-theme');
            $this->move_style_to_footer_queue('td-theme-child');
            $this->move_style_to_footer_queue('td-theme-woo');
            $this->move_style_to_footer_queue('td-theme-demo-style');
            $this->move_style_to_footer_queue('td_live_css_frontend');
            $this->move_style_to_footer_queue('td-theme-bbpress');
        }

        $this->move_style_to_footer_queue('font-awesome-four');

        //@todo test on newsmag
        $this->move_style_to_footer_queue('woocommerce_prettyPhoto_css'); //on product page
        $this->move_style_to_footer_queue('woocommerce_frontend_styles'); //old woocommerce?
        $this->move_style_to_footer_queue('woocommerce-layout');
        $this->move_style_to_footer_queue('woocommerce-smallscreen');
        $this->move_style_to_footer_queue('woocommerce-general');
        //$this->move_style_to_footer_queue('bp-legacy-css');

        //google fonts
        $this->move_style_to_footer_queue('google-fonts-style');

        //move revolution slider css
        $this->move_style_to_footer_queue('rs-plugin-settings');
        $this->move_style_to_footer_queue('genericons'); //still rev slider


        //$this->move_style_to_footer_queue('js_composer_front');

    }



    /**
     * move the js
     */
    function js_mover() {
        global $wp_scripts;


        //detect revmin - revolution slider and do not move jquery, the plugin outputs raw js in the page!
        if( !is_admin() and !isset($wp_scripts->registered['revmin'])){
            if (is_ssl()) {
                $td_protocol = 'https';
            } else {
                $td_protocol = 'http';
            }

            wp_deregister_script('jquery');
            wp_register_script('jquery', ($td_protocol . '://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'), true, '1.11.1', true);
            wp_enqueue_script('jquery');
        }

        /**
         * move javascript to footer
         */
        $this->move_js_to_footer('themepunchtools');
        $this->move_js_to_footer('revmin');
        $this->move_js_to_footer('vc_woocommerce-add-to-cart-js');
        //$this->move_js_to_footer('comment-reply');

        //print_r($wp_scripts);
        // replace comment-reply.min.js with inline version
    }



    /**
     * here we render the footer styles
     * the styles are already deregistered when you call @see move_style_to_footer_queue ($style_id)
     */
    function render_footer_styles() {
        //get the theme version for style
        $current_theme_version = $this->td_theme_version;

        //on demo mode, auto generate version hourly + day - so we get a fresh css hourly
        if ($this->td_deploy_mode == 'demo') {
            $current_theme_version = date('jG');
        }

        //print_r($this->style_footer_queue);

        if (isset($this->style_footer_queue['js_composer_front'])) {
            $td_theme_value = $this->style_footer_queue['js_composer_front'];
            unset($this->style_footer_queue['js_composer_front']);
            $this->style_footer_queue['js_composer_front'] = $td_theme_value;
        }

        if (isset($this->style_footer_queue['td-theme'])) {
            // whatever the order, make damn sure that our td-theme style is LAST - we remove it from the queue and add it again at the end
            $td_theme_value = $this->style_footer_queue['td-theme'];
            unset($this->style_footer_queue['td-theme']);
            $this->style_footer_queue['td-theme'] = $td_theme_value;
        }

        if (isset($this->style_footer_queue['td-theme-child'])) {
            $td_theme_value = $this->style_footer_queue['td-theme-child'];
            unset($this->style_footer_queue['td-theme-child']);
            $this->style_footer_queue['td-theme-child'] = $td_theme_value;
        }

        if (isset($this->style_footer_queue['td-theme-woo'])) {
            // whatever the order, make damn sure that our td-theme style is LAST - we remove it from the queue and add it again at the end
            $td_theme_value = $this->style_footer_queue['td-theme-woo'];
            unset($this->style_footer_queue['td-theme-woo']);
            $this->style_footer_queue['td-theme-woo'] = $td_theme_value;
        }

        if (isset($this->style_footer_queue['td-theme-demo-style'])) {
            // whatever the order, make damn sure that our td-theme style is LAST - we remove it from the queue and add it again at the end
            $td_theme_value = $this->style_footer_queue['td-theme-demo-style'];
            unset($this->style_footer_queue['td-theme-demo-style']);
            $this->style_footer_queue['td-theme-demo-style'] = $td_theme_value;
        }

        // output the new styles
        foreach ($this->style_footer_queue as $style_id => $wp_dependency_object) {

            $script_version = ! empty($wp_dependency_object->ver) ? $wp_dependency_object->ver : $current_theme_version;

            if (strrpos($wp_dependency_object->src, '?') === false) {
                echo "<link rel='stylesheet' id='" . $style_id . "-css'  href='" . $wp_dependency_object->src . "?ver=" . $script_version . "' type='text/css' media='" . $wp_dependency_object->args . "' />\n";
            } else {
                echo "<link rel='stylesheet' id='" . $style_id . "-css'  href='" . $wp_dependency_object->src . "&ver=" . $script_version . "' type='text/css' media='" . $wp_dependency_object->args . "' />\n";
            }


        }
    }



    /**
     * prevent flickering of the image until the style is loaded
     */
    function hide_body_inline_css() {
        echo '<style>body {visibility:hidden;}</style>';
    }



    /**
     * the id of the style can be found in the source of the page, for example:
     * <link rel='stylesheet' id='td-theme-css' => the id is 'td-theme' (note that the '-css' part is missing)
     * using this method you can move other styles if needed
     * Moves a style to the bottom of the page
     * @param $style_id string - the id of the style
     */
    function move_style_to_footer_queue($style_id) {
        global $wp_styles;
        if (!empty($wp_styles->registered[$style_id]) and !empty($wp_styles->registered[$style_id]->src)) {
            //print_r($wp_styles->registered[$style_id]);
            $this->style_footer_queue[$style_id] = $wp_styles->registered[$style_id];
            wp_deregister_style($style_id);
        }
    }



    /**
     * @param $js_id string - the js file id (this is a bit more complex to get) @todo -> add a tutorial about how to get the theme js
     */
    function move_js_to_footer($js_id) {
        global $wp_scripts;
        if (isset($wp_scripts->registered[$js_id])) {
            wp_enqueue_script($js_id, ($wp_scripts->registered[$js_id]->src), ($wp_scripts->registered[$js_id]->deps), $wp_scripts->registered[$js_id]->ver, true);
        }

    }




}

new td_speed_booster();
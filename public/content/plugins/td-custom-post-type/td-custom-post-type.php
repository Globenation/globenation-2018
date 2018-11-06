<?php
/*
	Plugin Name: tagDiv Books custom post types sample
	Plugin URI: http://tagdiv.com
	Description: adds the td_book post type and td_author + td_genre taxonomy
	Author: tagDiv
	Version: 1.0
	Author URI: http://tagdiv.com
*/

// to register Custom Post Types and taxonomies, the use of the init hook is required!
add_action('init', 'td_custom_post_type_init');
function td_custom_post_type_init() {

    /**
     * add the td_book custom post type
     * https://codex.wordpress.org/Function_Reference/register_post_type
     */
    $args = array(
        'public' => true,
        'label'  => 'Books',
        'supports' => array( // here we specify what the taxonomy supports
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'comments'
        )
    );
    register_post_type( 'td_book', $args );





    /**
     * Add new taxonomy, make it hierarchical (like categories) and associate it to the td_books Custom Post Type
     * https://codex.wordpress.org/Function_Reference/register_taxonomy
     */
    $labels = array(
        'name'              => _x( 'Genres', 'taxonomy general name' ),
        'singular_name'     => _x( 'Genre', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Genres' ),
        'all_items'         => __( 'All Genres' ),
        'parent_item'       => __( 'Parent Genre' ),
        'parent_item_colon' => __( 'Parent Genre:' ),
        'edit_item'         => __( 'Edit Genre' ),
        'update_item'       => __( 'Update Genre' ),
        'add_new_item'      => __( 'Add New Genre' ),
        'new_item_name'     => __( 'New Genre Name' ),
        'menu_name'         => __( 'Genre' ),
    );
    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'genre' ),
    );
    register_taxonomy( 'td_genre', array('td_book'), $args );




    /**
     * Add new taxonomy, NOT hierarchical (like tags)  and associate it to the td_books Custom Post Type
     * https://codex.wordpress.org/Function_Reference/register_taxonomy
     */
    $labels = array(
        'name'                       => _x( 'Writers', 'taxonomy general name' ),
        'singular_name'              => _x( 'Writer', 'taxonomy singular name' ),
        'search_items'               => __( 'Search Writers' ),
        'popular_items'              => __( 'Popular Writers' ),
        'all_items'                  => __( 'All Writers' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Writer' ),
        'update_item'                => __( 'Update Writer' ),
        'add_new_item'               => __( 'Add New Writer' ),
        'new_item_name'              => __( 'New Writer Name' ),
        'separate_items_with_commas' => __( 'Separate writers with commas' ),
        'add_or_remove_items'        => __( 'Add or remove writers' ),
        'choose_from_most_used'      => __( 'Choose from the most used writers' ),
        'not_found'                  => __( 'No writers found.' ),
        'menu_name'                  => __( 'Writers' ),
    );
    $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'writer' ),
    );
    register_taxonomy( 'td_writer', 'td_book', $args );


}


/**
 * this hook will regenerate the permalinks when the plugin is activated. I would recommend that you work with the permalinks OFF until you make
 * your custom post types. After you make the final custom post types you can enable the permalinks and hit save in wp-admin -> settings -> permalinks.
 */
function td_regenerate_htaccess() {
    td_custom_post_type_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'td_regenerate_htaccess');
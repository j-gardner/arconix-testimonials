<?php
/**
 * Plugin Name: Arconix Testimonials
 * Plugin URI: http://arconixpc.com/plugins/arconix-testimonials
 * Description: Arconix Testimonials is a plugin which makes it easy for you to display customer feedback on your site
 *
 * Version: 0.5
 *
 * Author: John Gardner
 * Author URI: http://arconixpc.com/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */

class Arconix_Testimonials {

    /**
     * Construct Method
     * 
     * @since 0.5
     */
    function __construct() {
        /* Load the plugin constants */
        add_action( 'plugins_loaded', array( $this, 'constants' ), 1 );
        /* Load the plugin actions and filters */
        $this->hooks();
        /* Run on plugin activation */
        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        /* Run on plugin deactivation */
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );
    }
    
    /**
     * Defines constants used by the plugin.
     *
     * @since 0.5
     */
    function constants() {
        define( 'ACT_VERSION', '0.5');        
        define( 'ACT_URL', plugin_dir_url( __FILE__ ) );
        define( 'ACT_INCLUDES_URL', ACT_URL . 'includes' );
        define( 'ACT_IMAGES_URL', ACT_URL . 'images' );
        define( 'ACT_DIR', plugin_dir_path( __FILE__ ) );
        define( 'ACT_INCLUDES_DIR', ACT_DIR . 'includes' );
    }
    
    /**
     * Run the necessary functions and add them to their respective hooks
     * 
     * @since 0.5
     */
    function hooks() {
        add_action( 'init', 'create_post_type' );
        add_action( 'manage_posts_custom_column', 'column_data' );
        add_action( 'wp_dashboard_setup', 'register_dashboard_widget' );
        add_action( 'right_now_content_table_end', 'right_now' );
        add_action( 'wp_enqueue_scripts', 'load_scripts' );
        add_action( 'widgets_init', 'register_widget' );
        
        add_filter( 'widget_text', 'do_shortcode' );
        add_filter( 'the_content', 'content_filter' );
        add_filter( 'cmb_meta_boxes', 'create_meta_box' );
        add_filter( 'post_updated_messages', 'updated_messages' );
        add_filter( 'manage_edit-testimonials_columns', 'columns_filter' );
        
        add_shortcode( 'testimonials', 'testimonials_shortcode' );
        
        require_once( ACT_INCLUDES_DIR . 'functions.php' );
        require_once( ACT_INCLUDES_DIR . 'post-type.php' );
        require_once( ACT_INCLUDES_DIR . 'widget.php');
        
        if( is_admin() ) {
            require_once( ACT_INCLUDES_DIR . 'admin.php' );
            
            if( !class_exists( 'cmb_Meta_Box' ) )
                require_once( ACT_INCLUDES_DIR . 'metabox/init.php');
        }
    }
    
    /**
     * Runs on plugin activation
     * 
     * @since 0.5
     */
    function activation() {
        // flush_rewrite_rules();
    }

    /**
     * Runs on plugin deactivation
     * 
     * @since 0.5
     */
    function deactivation() {
        flush_rewrite_rules();
    }
    
}

new Arconix_Testimonials;

 register_activation_hook( __FILE__, 'arconix_testimonials_activation' );
/**
 * This function runs on plugin activation. It checks for the existence of the post-type
 * and creates it otherwise.
 *
 * @since 0.9
 */
function arconix_testimonials_activation() {

    if ( ! post_type_exists( 'testimonials' ) ) {
        arconix_testimonials_setup();
        global $_arconix_testimonials;
        $_arconix_testimonials -> create_post_type();
    }
    flush_rewrite_rules();

}

add_action( 'after_setup_theme', 'arconix_testimonials_setup' );
/**
 * Initialize the plugin.
 *
 * Include the libraries, define global variables, instantiate the classes.
 *
 * @since 0.9
 */
function arconix_testimonials_setup() {

    global $_arconix_testimonials;

    define( 'ACT_URL', plugin_dir_url( __FILE__ ) );
    define( 'ACT_VERSION', '0.9');

    /** Includes */
    require_once( dirname( __FILE__ ) . '/includes/class-testimonials.php' );
    require_once( dirname( __FILE__ ) . '/includes/class-testimonials-widget.php' );

    /** Instantiate */
    $_arconix_testimonials = new Arconix_Testimonials;

    /** Register the Widget */
    add_action( 'widgets_init', 'arconix_testimonials_register_widgets' );

}

/**
 * Register Widgets that will be used in the plugin
 *
 * @since 0.9
 */
function arconix_testimonials_register_widgets() {

    register_widget( 'Arconix_Testimonials_Widget' );

}

?>
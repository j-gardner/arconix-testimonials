<?php
/**
 * Plugin Name: Arconix Testimonials
 * Plugin URI: http://arconixpc.com/plugins/arconix-testimonials
 * Description: Arconix Testimonials is a plugin which makes it easy for you to display customer feedback on your site
 *
 * Version: 0.9
 *
 * Author: John Gardner
 * Author URI: http://arconixpc.com/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */


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
<?php
/**
 * Plugin Name: Arconix Testimonials
 * Plugin URI: http://arconixpc.com/plugins/arconix-testimonials
 * Description: Arconix Testimonials is a plugin which makes it easy for you to display customer feedback on your site
 *
 * Version: 1.2
 *
 * Author: John Gardner
 * Author URI: http://arconixpc.com/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */


require_once( plugin_dir_path( __FILE__ ) . 'includes/class-arconix-testimonials.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-arconix-testimonials-admin.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-widgets.php' );

new Arconix_Testimonials_Admin;
<?php
/**
 * Plugin Name: Arconix Testimonials
 * Plugin URI: https://tychesoftwares.com/
 * Description: Arconix Testimonials is a plugin which makes it easy for you to display customer feedback on your site
 *
 * Version: 1.4.2
 *
 * Author: Tyche Softwares
 * Author URI: https://tychesoftwares.com/
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */


require_once plugin_dir_path( __FILE__ ) . 'includes/class-arconix-testimonials.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-arconix-testimonials-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-widgets.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/arconix-testimonials-privacy-export.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/arconix-testimonials-privacy-erase.php';

new Arconix_Testimonials_Admin();

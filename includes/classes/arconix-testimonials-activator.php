<?php

/**
 * Activator class for Testimonials Plugin
 * 
 * @package     WordPress
 * @subpackage  Arconix Testimonials
 * @author      John Gardner
 * @link        http://arconixpc.com/plugins/arconix-testimonials
 * @license     GPL-2.0+
 */
class Arconix_Testimonials_Activator {

	public static function activate( $wp = '4.6', $php = '5.3' ) {

		global $wp_version;

		if( version_compare( PHP_VERSION, $php, '<' ) && version_compare( $wp_version, $wp, '<' ) ) {
			$string = sprintf( __( 'This plugin requires either WordPress 4.6 or PHP 5.3. You are running versions %s and %s, respectively', 
			Arconix_Testimonials_Plugin::$textdomain ),$wp_version , PHP_VERSION );

			deactivate_plugins( basename( __FILE__ ) );

			wp_die( $string, __( 'Plugin Activation Error', Arconix_Testimonials_Plugin::$textdomain ), array( 'response' => 200, 'back_link' => TRUE ) );
		
		}
	}

}

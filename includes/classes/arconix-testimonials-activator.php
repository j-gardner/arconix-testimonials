<?php

/**
 * Activator class for Testimonials Plugin
 */
class Arconix_Testimonials_Activator {

	public static function activate( $wp = '4.6', $php = '5.3' ) {

		global $wp_version;

		if( version_compare( $php_ver, $php, '<' ) && version_compare( $wp_version, $wp, '<' ) ) {
			$string = sprintf( __( 'This plugin requires either WordPress 4.6 or PHP 5.3. You are running versions %s and %s, respectively', 
			'arconix-testimonials' ), PHP_VERSION, $wp_version );

			deactivate_plugins( basename( __FILE__ ) );

			wp_die( $string, 'Plugin Activation Error', array( 'response' => 200, 'back_link' => TRUE ) );
		
		}
	}

}

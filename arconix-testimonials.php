<?php
/**
 * Plugin Name: Arconix Testimonials
 * Plugin URI: http://arconixpc.com/plugins/arconix-testimonials
 * Description: Arconix Testimonials is a plugin which makes it easy for you to display customer feedback on your site
 *
 * Version: 1.2.0
 *
 * Author: John Gardner
 * Author URI: http://arconixpc.com/
 * 
 * Text Domain: arconix-testimonials
 *
 * License: GNU General Public License v2.0
 * License URI: http://www.opensource.org/licenses/gpl-license.php
 */


register_activation_hook( __FILE__, 'activate_arconix_testimonials' );

function activate_arconix_testimonials() {
	require_once plugin_dir_path( __FILE__ ) . '/includes/classes/arconix-testimonials-activator.php';
	Arconix_Testimonials_Activator::activate();
}


spl_autoload_register( 'arconix_testimonials_autoloader' );

/**
 * Class Autoloader
 * 
 * @param	string	$class_name		Class to check to autoload
 * @return	null					Return if it's not a valid class
 */
function arconix_testimonials_autoloader( $class_name ) {
	/**
	 * If the class being requested does not start with our prefix,
	 * we know it's not one in our project
	 */
	if ( 0 !== strpos( $class_name, 'Arconix_' ) ) {
		return;
	}

	$file_name = str_replace(
		array( 'Arconix_', '_' ),	// Prefix | Underscores 
		array( '', '-' ),			// Remove | Replace with hyphens
		strtolower( $class_name )	// lowercase
	);

	// Compile our path from the current location
	$file = dirname( __FILE__ ) . '/includes/classes/' . $file_name . '.php';

	// If a file is found, load it
	if ( file_exists( $file ) ) {
		require_once( $file );
	}
}

/**
 * Arconix Testimonials
 *
 * This is the base class which sets the version, loads dependencies and gets the plugin running
 *
 * @since 1.2.0
 */
final class Arconix_Testimonials_Plugin {

    /**
     * Plugin version.
     *
     * @since	1.2.0
     * @var		string	$version        Plugin version
     */
    public static $version = '1.2.0';
    
    /**
     * Translation Textdomain
     * 
     * @since   1.2.0
     * @var     string  $textdomain     For i18n
     */
    public static $textdomain = 'arconix-testimonials';
	
	/**
	 *
	 * @since	1.2.0
	 * @var		array	$settings       Post Type default settings
	 */
	protected $settings;

    /**
     * Initialize the class and set its properties.
     *
     * @since   1.2.0
     */
    public function __construct() {
        $this->settings = $this->get_settings();
    }
	
	/**
     * Load the plugin instructions
     * 
     * @since   1.2.0
     */
	public function init() {
        $this->register_post_type();
        
        if ( is_admin() ) {
            $this->load_admin();
            $this->load_metaboxes();
        }
	}
    
    /**
     * Set up our Custom Post Type
     * 
     * @since   1.2.0
     */
    private function register_post_type() {
        $t = new Arconix_CPT_Register();
        
        $settings = $this->settings;
        
        $t->add( 'testimonials', $settings['post_type']['args'], self::$textdomain );
    }

    /**
     * Loads the admin functionality
     *
     * @since   1.2.0
     */
    private function load_admin() {
        new Arconix_Testimonials_Admin();
    }
    
    /**
     * Set up the Post Type Metabox
     * 
     * @since   1.2.0
     */
    private function load_metaboxes() {
        new Arconix_Testimonials_Metaboxes();
    }
    
    /**
	 * Get our default Post Type registration settings
	 * 
	 * @since	1.2.0
	 * @return	array				Post Type registration Settings
	 */
	public function get_settings() {
		$settings = array(
            'post_type' => array(
                'args' => array(
                    'public'            => true,
                    'menu_position'     => 20,
                    'menu_icon'         => 'dashicons-testimonial',
                    'has_archive'       => false,
                    'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
                    'rewrite'           => array( 'with_front' => false )
                )
            )
        );

        return apply_filters( 'arconix_testimonials_post_type_settings', $settings );
	}
}

/** Vroom vroom */
add_action( 'plugins_loaded', 'arconix_testimonials_run' );
function arconix_testimonials_run() {
    load_plugin_textdomain( 'arconix-testimonials' );
    $arconix_testimonials = new Arconix_Testimonials_Plugin();
    
    $arconix_testimonials->init();
}
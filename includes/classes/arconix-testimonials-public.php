<?php
/**
 * Public-facing functionality of the plugin
 * 
 * @author      John Gardner
 * @link        http://arconixpc.com/plugins/arconix-testimonials
 * @license     GPLv2 or later
 * @since       1.2.0
 */
class Arconix_Testimonials_Public {

    /**
     * The url path to this plugin.
     *
     * @since   1.2.0
     * @access  private
     * @var     string      $url        The url path to this plugin
     */
    private $url;

    /**
     * Constructor.
     * 
     * Initialize the class and set its properties
     */
    public function __construct() {
        $this->url = trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) );
    }

    /**
     * Get our hooks into WordPress
     * 
     * @since   1.2.0
     */
    public function init() {
        add_action( 'wp_enqueue_scripts',   array( $this, 'register_styles' ), 9 );
        add_action( 'init',                 array( $this, 'shortcodes' ) );
        
        add_filter( 'widget_text',          'do_shortcode' );
        add_filter( 'the_content',          array( $this, 'content_filter' ) );
        add_filter( 'the_posts',            array( $this, 'conditional_styles' ) );
        
        // For use if Arconix Flexslider is active
        add_filter( 'arconix_flexslider_slide_image_return',    array( $this, 'flexslider_image_return' ), 10, 4 );
        add_filter( 'arconix_flexslider_slide_content_return',  array( $this, 'flexslider_content' ), 10, 2 );
    }

    /**
     * Register plugin shortcode.
     *
     * @since   1.0.0
     */
    public function shortcodes() {
        add_shortcode( 'ac-testimonials', array( $this, 'testimonials_shortcode' ) );
    }

    /**
     * Testimonials shortcode.
     *
     * @since   1.0.0
     * @param   array        $atts       Passed attributes
     * @param   string       $content    N/A - self-closing shortcode
     * @return  string                   Result of query
     */
    public function testimonials_shortcode( $atts, $content = null ) {
        $t = new Arconix_Testimonial;

        return $t->loop( $atts );
    }
    
    /**
     * Enqueue the CSS
     * 
     * Checks for theme support and enqueues if there is none.
     * 
     * @since   1.2.0
     */
    public function enqueue_styles() {
        if ( ! current_theme_supports( 'arconix-testimonials', 'css' ) && 
            apply_filters( 'pre_register_arconix_testimonials_css', true ) )
                wp_enqueue_style( 'arconix-testimonials' );
    }

    /**
     * Filter The_Content and add our data to it
     *
     * @since   1.0.0
     * @global  stdObj      $post       Std Post
     * @param   string      $content    Main content
     * @return  string                  Our testimonial content
     */
    public function content_filter( $content ) {
        global $post;

        if( is_single() && $post->post_type == 'testimonial' && is_main_query() ) {

            $t = new Arconix_Testimonial;

            // So we can grab our default gravatar size and allow it to be filtered.
            $defaults = $t->get_defaults();

            $gs = apply_filters( 'arconix_testimonials_content_gravatar_size', $defaults['gravatar']['size'] );

            $gravatar = '<div class="arconix-testimonial-gravatar">' . $t->get_image( $gs ) . '</div>';

            $cite = '<div class="arconix-testimonial-info-wrap">' . $t->get_citation( false ) . '</div>';

            $content = '<div class="arconix-testimonial-content">' . $t->get_content() . '</div>';

            $content = $cite . $gravatar . $content;

        }

        return $content;
    }
    
    /**
     * Conditional load of base CSS
     * 
     * Loops through all the posts and checks for the shortcode, loading the CSS if found
     * 
     * @since   1.2.0
     * @param   array       $posts      List of posts
     * @return  array                   List of posts
     */
    public function conditional_styles( $posts ){
        if ( empty( $posts ) || is_admin() ) return $posts;
        
        $found = false;
        
        foreach ( $posts as $post ) {
            if ( has_shortcode( $post->post_content, 'ac-testimonials' ) ) {
                $found = true;
                break;
            }
        }
        
        if ( $found )
            add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        
        return $posts;
    }

    /**
     * Register required CSS.
     *
     * If the css file is present in the theme directory, it will be loaded instead,
     * allowing for an easy way to override the default template. If you'd like to remove the CSS entirely,
     * such as when building the styles into a theme, simply add support to your theme
     *
     * @example add_theme_support( 'arconix-testimonials', 'css' );
     *
     * @since   1.0.0
     */
    public function register_styles() {
        if ( ! current_theme_supports( 'arconix-testimonials', 'css' ) && 
                apply_filters( 'pre_register_arconix_testimonials_css', true ) ) {
            
            // Checks the child directory and then the parent directory.
            if ( file_exists( get_stylesheet_directory() . '/arconix-testimonials.css' ) )
                wp_register_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', false, Arconix_Testimonials_Plugin::version );
            elseif ( file_exists( get_template_directory() . '/arconix-testimonials.css' ) )
                wp_register_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', false, Arconix_Testimonials_Plugin::version );
            else
                wp_register_style( 'arconix-testimonials', $this->url . 'css/arconix-testimonials.css', false, Arconix_Testimonials_Plugin::version );
        }
    }
    
    /**
     * Modify the Arconix Flexslider content
     *
     * Customizes the flexslider content with our testimonial data
     *
     * @since   1.2.0
     * @global  stdObj      $post           Standard WP Post object
     * @param   string      $content        Incoming content to be modified
     * @param   string      $display        From the Flexslider user, displaying either 'none', 'excerpt' or 'content'
     * @return  string                      Modified return content with our testimonial-customized options
     */
    public function flexslider_content( $content, $display ) {
        global $post;

        // return early if we're not displaying anything or we're not working with a testimonial
        if ( ! $display || $display == 'none' || $post->post_type != 'testimonials' ) return;

        // Initialize our testimonial class
        $t = new Arconix_Testimonial();

        // Get our gravatar size
        $defaults = $t->get_defaults();
        $gs = apply_filters( 'arconix_testimonials_content_gravatar_size', $defaults['gravatar']['size'] );

        $image = '<div class="arconix-testimonial-gravatar">' . $t->get_image( $gs ) . '</div>';

        $cite = '<div class="arconix-testimonial-info-wrap">' . $t->get_citation() . '</div>';

        $display == 'content' ? $display = false : $display = true;
        $content = '<div class="arconix-testimonial-content">' . $t->get_content( $display ) . '</div>';

        $content .= $image . $cite;

        return $content;
    }

    /**
     * Modify the Arconix Flexslider image information
     *
     * Returns an empty string if we're working with a testimonial as the content filter handles the
     * image output
     *
     * @since   1.2.0
     * @global  stdObj      $post           Standard WP Post object
     * @param   string      $content        Existing image data
     * @param   bool        $link_image     Wrap the image in a hyperlink to the permalink (false for basic image slider)
     * @param   string      $image_size     The size of the image to display. Accepts any valid built-in or added WordPress image size
     * @param   string      $caption        Caption to be displayed
     * @return  string                      Empty string if on the testimonial post_type
     */
    public function flexslider_image_return( $content, $link_image, $image_size, $caption ) {
        global $post;

        if ( $post->post_type == 'testimonials' ) $content = '';

        return $content;
    }
}

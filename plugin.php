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
        $this->constants();

        register_activation_hook( __FILE__, array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

        add_action( 'manage_posts_custom_column', array( $this, 'column_action' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'reg_dash_widget' ) );
        add_action( 'right_now_content_table_end', array( $this, 'right_now' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
        add_action( 'widgets_init', array( $this, 'reg_widget' ) );
        add_action( 'init', array( $this, 'define_shortcodes' ) );

        add_filter( 'widget_text', 'do_shortcode' );
        add_filter( 'the_content', array( $this, 'content_filter' ) );
        add_filter( 'cmb_meta_boxes', array( $this, 'metaboxes' ) );
        add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );
        add_filter( 'manage_edit-testimonials_columns', array( $this, 'columns_filter' ) );
        add_filter( 'enter_title_here', array( $this, 'custom_title_text' ) );
    }

    /**
     * Define plugin constants
     *
     * @since 0.5
     */
    function constants() {
        define( 'ACT_VERSION', '0.5');
        define( 'ACT_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
        define( 'ACT_INCLUDES_URL', trailingslashit( ACT_URL . 'includes' ) );
        define( 'ACT_IMAGES_URL', trailingslashit( ACT_URL . 'images' ) );
        define( 'ACT_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        define( 'ACT_INCLUDES_DIR', trailingslashit( ACT_DIR . 'includes' ) );
    }


    /**
     * Runs on plugin activation
     *
     * @since 0.5
     */
    function activation() {
        $this->register_content_types();
        flush_rewrite_rules();
    }

    /**
     * Runs on plugin deactivation
     *
     * @since 0.5
     */
    function deactivation() {
        flush_rewrite_rules();
    }

    /**
     * Set our plugin defaults for post type and metabox registration
     *
     * @since 0.5
     * @return array $defaults
     */
    function defaults() {
        include_once( ACT_INCLUDES_DIR . 'defaults.php' );
        return apply_filters( 'arconix_testimonials_defaults', $defaults );
    }


    function register_content_types() {

    }

    /**
     * Register Plugin Widget(s)
     * 
     * @since 0.5
     */
    function reg_widget() {
        register_widget( 'Arconix_Testimonials_Widget' );
    }


    



    /**
     * Modifies the post save notifications to properly reflect the post-type
     *
     * @global array $post
     * @global int $post_ID
     * @param array $messages
     * @return array $messages
     * @since 0.5
     */
    function act_updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['testimonials'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __( 'Testimonial updated. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            2 => __( 'Custom field updated.' ),
            3 => __( 'Custom field deleted.' ),
            4 => __( 'Testimonial updated.' ),
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
            6 => sprintf( __( 'Testimonial published. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            7 => __( 'Testimonial saved.' ),
            8 => sprintf( __( 'Testimonial submitted. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            9 => sprintf( __( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial</a>' ),
                    // translators: Publish box date format, see http://php.net/date
                    date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
            10 => sprintf( __( 'Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        );

        return $messages;
    }

    /**
     * Choose the specific columns we want to display
     *
     * @param array $columns
     * @return array $columns
     * @since 0.5
     */
    function columns_filter( $columns ) {

        $columns = array(
            "cb" => "<input type=\"checkbox\" />",
            "title" => "Testimonial Author",
            "testimonial_content" => "Testimonial",
            "date" => "Date"
        );

        return $columns;
    }

    /**
     * Filter the data that shows up in the columns we defined above
     *
     * @global array $post
     * @param array $column
     * @since 0.5
     */
    function column_action( $column ) {

        global $post;

        switch( $column ) {
            case "title":
                $custom = get_post_custom();
                $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
                echo '<br />' . $meta_byline;
                break;

            case "testimonial_content":
                the_excerpt();
                break;

            default:
                break;
        }
    }

    /**
     * Customize the "Enter title here" text
     *
     * @param string $title
     * @return $title
     * @since 0.5
     */
    function custom_title_text( $title ) {
        $screen = get_current_screen();

        if( 'testimonials' == $screen->post_type ) {
            $title = __( 'Enter the person\'s name here', 'act' );
        }
        return $title;
    }

    /**
     * Add the Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     * @since 0.5
     */
    function right_now() {
        include_once( ACT_INCLUDES_DIR . 'right-now.php' );
    }

    /**
     * Adds a widget to the dashboard.
     *
     * @since 0.5
     */
    function act_register_dashboard_widget() {
        wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', 'act_dashboard_widget_output' );
    }

    /**
     * Output for the dashboard widget
     *
     * @since 0.5
     */
    function act_dashboard_widget_output() {
        include_once( ACT_INCLUDES_DIR . 'dash-widget.php' );
    }

}

include_once( plugin_dir_path( __FILE__ ) . '/includes/class-widget.php' );

if( ! class_exists( 'cmb_Meta_Box' ) )
    require_once( plugin_dir_path( __FILE__ ) . '/includes/metabox/init.php' );


new Arconix_Testimonials;
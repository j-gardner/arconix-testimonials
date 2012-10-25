<?php
/**
 * This file contains the Arconix_Testimonials class.
 *
 * This class handles the creation of the "testimonial" post type, and creates a
 * UI to display the testimonial-specific data on the admin screens.
 */

 class Arconix_Testimonials {

     /**
     * This var is used to flag the loading of javascript
     *
     * @var type boolean
     */
    static $load_js;


    /**
     * Construct Method.
     */
    function __construct() {

        // Post Type Creation
        add_action( 'init', array( $this, 'create_post_type' ) );
        add_filter( 'post_updated_messages', array( $this, 'updated_messages' ) );

        // Modify the Post Type Admin Screen
        add_filter( 'manage_edit-testimonials_columns', array( $this, 'columns_filter' ) );
        add_action( 'manage_posts_custom_column', array( $this, 'column_data' ) );

        // Register the CSS
        add_action( 'wp_enqueue_scripts', array( $this , 'enqueue_css' ) );

        // Create the shortcode
        add_shortcode( 'testimonials', array( $this, 'testimonials_shortcode' ) );
        add_filter( 'widget_text', 'do_shortcode' );

        // Create/Modify Dashboard widgets
        add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
        add_action( 'right_now_content_table_end', array( $this, 'right_now' ) );

        // Initialize the Post Type Meta Box
        add_filter( 'cmb_meta_boxes', array( $this, 'create_meta_box' ) );
        add_action( 'init', array( $this, 'initialize_cmb_meta_boxes' ), 9999 );

    }


    /**
     * Create the new post-type
     *
     * @since 0.9
     */
    function create_post_type() {

        $args = apply_filters( 'arconix_testimonials_post_type_args',
            array(
                'labels' => array(
                    'name' => __( 'Testimonials', 'act' ),
                    'singular_name' => __( 'Testimonials', 'act' ),
                    'add_new' => __( 'Add New', 'act' ),
                    'add_new_item' => __( 'Add New Testimonial', 'act' ),
                    'edit' => __( 'Edit', 'act' ),
                    'edit_item' => __( 'Edit Testimonial', 'act' ),
                    'new_item' => __( 'New Testimonial', 'act' ),
                    'view' => __( 'View Testimonial', 'act' ),
                    'view_item' => __( 'View Testimonial', 'act' ),
                    'search_items' => __( 'Search Testimonials', 'act' ),
                    'not_found' => __( 'No testimonials found', 'act' ),
                    'not_found_in_trash' => __( 'No testimonials found in Trash', 'act' )
                ),
                'public' => true,
                'query_var' => true,
                'menu_position'	=> 20,
                'menu_icon' => ACT_URL . 'images/act-icon-16x16.png',
                'has_archive' => true,
                'supports' => array( 'title', 'editor' ),
                'rewrite' => array( 'slug' => 'testimonials', 'with_front' => false ),
            )
        );

        register_post_type( 'testimonials', $args );

    }


    /**
     * Modifies the post save notifications to properly reflect the post-type
     *
     * @global type $post
     * @global type $post_ID
     * @param type $messages
     * @return type array
     * @since 0.9
     */
    function updated_messages( $messages ) {
        global $post, $post_ID;

        $messages['testimonials'] = array(
            0 => '', // Unused. Messages start at index 1.
            1 => sprintf( __( 'Testimonial updated. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            2 => __( 'Custom field updated.' ),
            3 => __( 'Custom field deleted.' ),
            4 => __( 'Testimonial updated.' ),
            /* translators: %s: date and time of the revision */
            5 => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6 => sprintf( __('Testimonial published. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            7 => __( 'Testimonial saved.'),
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
     * @return string
     * @since 0.9
     */
    function columns_filter ( $columns ) {

        $columns = array (
            "cb" => "<input type=\"checkbox\" />",
            "title" => "Testimonial Title",
            "testimonial_author" => "Author",
            "testimonial_content" => "Details",
            "date" => "Date"
        );

        return $columns;
    }


    /**
     * Filter the data that shows up in the columns we defined above
     *
     * @global type $post
     * @param type $column
     * @since 0.9
     */
    function column_data( $column ) {

        global $post;

        switch( $column ) {
            case "testimonial_content":
                the_excerpt();
                break;

            case "testimonial_author":
                $output = '';

                $custom = get_post_custom();

                $meta_name = isset( $custom["_act_name"][0] ) ? $custom["_act_name"][0] : null;
                $meta_co = isset( $custom["_act_company_name"][0] ) ? $custom["_act_company_name"][0] : null;


                if( $meta_name ) $output = $meta_name;
                if( $meta_co ) $output .= ' - ' . $meta_co;

                echo $output;
                break;

            default:
                break;

        }
    }


    /**
     * Enqueue the CSS
     *
     * @since 0.9
     */
    function enqueue_css() {

        if( file_exists( get_stylesheet_directory() . "/arconix-testimonials.css" ) ) {
	    wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
	}
	elseif( file_exists( get_template_directory() . "/arconix-testimonials.css" ) ) {
	    wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
	}
	else {
            wp_enqueue_style( 'arconix-testimonials', ACT_URL . 'includes/css/testimonials.css', array(), ACT_VERSION );
	}

    }


    /**
     * Testimonial Shortcode
     *
     * @global type $post
     * @param type $atts
     * @param type $content
     * @since 0.9
     */
    function testimonial_shortcode( $atts, $content = null ) {

        $args = apply_filters( 'arconix_testimonials_shortcode_query_args',
	    array(
		'post_type' => 'testimonials',
		'posts_per_page' => 10,
                'order' => 'DESC',
		'orderby' => 'date'
	    )
	);

        extract( shortcode_atts( $args, $atts ) );

        /** create a new query bsaed on our own arguments */
	$testimonials_query = new WP_Query( $args );

        if( $testimonials_query->have_posts() ) {

            self::$load_js = true; // ensuring the javascript is loaded on the page

            global $post;

            echo '<div class="arconix-testimonials">';
            echo '<div class="arconix-quotes">';

            while( $testimonials_query->have_posts() ) : $testimonials_query->the_post();

                /**
                 * Grab all of our custom post information
                 */
                $custom = get_post_custom();
                $meta_name = $custom["_act_name"][0];
                $meta_title = $custom["_act_title"][0];
                $meta_company = $custom["_act_company_name"][0];
                $meta_city = $custom["_act_city"][0];
                $meta_state = $custom["_act_state"][0];
                $meta_url = $custom["_act_url"][0];


                // Build output based on which variables have been assigned values
                $output = '';

                /**
                 * If the URL is set, then apply it to the either the company name or individual name
                 * before we do anything else
                 */
                if( isset( $meta_url ) ) {
                    if( isset( $meta_company ) ) {
                        $meta_company = '<a href="'. $meta_url .'">'. $meta_company .'</a>';
                    }
                    else {
                        if( isset( $meta_name ) ) {
                            $meta_name = '<a href="'. $meta_url .'">'. $meta_name .'</a>';
                        }
                    }
                }

                // Now we start
                if( isset( $meta_name ) ) $output = $meta_name;

                if( isset( $meta_name ) && isset( $meta_title ) ) $output = $meta_name . ', ' . $meta_title;

                if( isset( $meta_company ) ) $output .= ' - ' . $meta_company;

                if( isset( $meta_city ) && isset( $meta_state ) ) {
                    $output .= ' ' . $meta_city .', '. $meta_state;
                } elseif ( isset( $meta_state ) ) {
                    $output .= ', '. $meta_state;
                }

                /**
                 * Run through the rest of the loop info
                 */
                echo '<div id="post-' . get_the_ID() . '" class="arconix-quote ' . implode( ' ', get_post_class() ) .'">';
                    echo '<blockquote>';
                    the_content();
                    echo '</blockquote>';
                    echo "<cite>$output</cite>";
                echo '</div>';

            endwhile;
        }

    }


    /**
     * Adds a widget to the dashboard.
     *
     * @since 0.9
     */
    function register_dashboard_widget() {
        wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', array( $this, 'dashboard_widget_output' ) );
    }


    /**
     * Output for the dashboard widget
     *
     * @since 0.9
     */
    function dashboard_widget_output() {

        echo '<div class="rss-widget">';

        wp_widget_rss_output( array(
            'url' => 'http://arconixpc.com/tag/arconix-testimonials/feed', // feed url
            'title' => 'Arconix Testimonials Posts', // feed title
            'items' => 3, // how many posts to show
            'show_summary' => 1, // display excerpt
            'show_author' => 0, // display author
            'show_date' => 1 // display post date
        ));

        echo '<div class="act-widget-bottom"><ul>'; ?>
            <li><img src="<?php echo ACT_URL . 'images/page-16x16.png'?>"><a href="http://arcnx.co/atwiki">Wiki Page</a></li>
            <li><img src="<?php echo ACT_URL . 'images/help-16x16.png'?>"><a href="http://arcnx.co/athelp">Support Forum</a></li>
        <?php echo '</ul></div>';
        echo '</div>';

        // handle the styling
        echo '<style type="text/css">
            #ac-testimonials .rsssummary { display: block; }
            #ac-testimonials .act-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
            #ac-testimonials .act-widget-bottom ul { list-style: none; }
            #ac-testimonials .act-widget-bottom ul li { display: inline; padding-right: 9%; }
            #ac-testimonials .act-widget-bottom img { padding-right: 3px; vertical-align: top; }
        </style>';
    }


    /**
     * Add the Portfolio Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     */
    function right_now() {
        include_once( dirname( __FILE__ ) . '/views/right-now.php' );
    }


    /**
     * Register the Post Type metabox
     *
     * @return type array
     * @since 0.9
     */
    function create_meta_box( array $meta_boxes ) {

        include( dirname( __FILE__ ) . '/views/register-metaboxes.php' );

        return $meta_boxes;

    }


    /**
     * Initialize the meta box class
     *
     * @link http://www.billerickson.net/wordpress-metaboxes/
     * @since 0.9
     */
    function initialize_cmb_meta_boxes() {

        if( ! class_exists( 'cmb_Meta_Box' ) ) {

            require_once( dirname( __FILE__ ) . '/metabox/init.php' );

        }
    }

}
?>
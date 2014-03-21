<?php

class Arconix_Testimonials {

    /**
     * Construct Method
     *
     * @since 0.5
     */
    function __construct() {
        $this->constants();

        register_activation_hook( __FILE__,             array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__,           array( $this, 'deactivation' ) );

        add_action( 'init',                             array( $this, 'init'), 9999 );
        add_action( 'init',                             array( $this, 'content_types' ) );
        add_action( 'init',                             array( $this, 'shortcodes' ) );
        add_action( 'widgets_init',                     array( $this, 'widgets' ) );
        add_action( 'wp_enqueue_scripts',               array( $this, 'scripts' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'admin_scripts' ) );
        add_action( 'manage_posts_custom_column',       array( $this, 'column_action' ) ); 
        add_action( 'wp_dashboard_setup',               array( $this, 'dash_widget' ) );
        add_action( 'dashboard_glance_items',           array( $this, 'at_a_glance' ) );
        

        add_filter( 'widget_text',                      'do_shortcode' );
        add_filter( 'the_content',                      array( $this, 'content_filter' ) );
        add_filter( 'enter_title_here',                 array( $this, 'title_text' ) );
        add_filter( 'cmb_meta_boxes',                   array( $this, 'metaboxes' ) );
        add_filter( 'post_updated_messages',            array( $this, 'messages' ) );
        add_filter( 'manage_edit-testimonials_columns', array( $this, 'columns_filter' ) );
    }

    /**
     * Define plugin constants
     *
     * @since 0.5
     */
    function constants() {
        define( 'ACT_VERSION',          '1.0.0' );
        define( 'ACT_URL',              trailingslashit( plugin_dir_url( __FILE__ ) ) );
        define( 'ACT_CSS_URL',          trailingslashit( ACT_URL . 'css' ) );
        define( 'ACT_IMAGES_URL',       trailingslashit( ACT_CSS_URL . 'images' ) );
        define( 'ACT_DIR',              trailingslashit( plugin_dir_path( __FILE__ ) ) );
    }


    /**
     * Runs on plugin activation
     *
     * @since 0.5
     */
    function activation() {
        $this->content_types();
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
     * Set our plugin defaults for post type registration and default query args
     *
     * @since 0.5
     * @return array $defaults
     */
    function defaults() {

        $defaults = array(
            'post_type' => array(
                'slug' => 'testimonials',
                'args' => array(
                    'labels' => array(
                        'name'                  => __( 'Testimonials',                              'act' ),
                        'singular_name'         => __( 'Testimonial',                               'act' ),
                        'add_new'               => __( 'Add New',                                   'act' ),
                        'add_new_item'          => __( 'Add New Testimonial Item',                  'act' ),
                        'edit'                  => __( 'Edit',                                      'act' ),
                        'edit_item'             => __( 'Edit Testimonial Item',                     'act' ),
                        'new_item'              => __( 'New Item',                                  'act' ),
                        'view'                  => __( 'View Testimonial',                          'act' ),
                        'view_item'             => __( 'View Testimonial Item',                     'act' ),
                        'search_items'          => __( 'Search Testimonial',                        'act' ),
                        'not_found'             => __( 'No testimonial items found',                'act' ),
                        'not_found_in_trash'    => __( 'No testimonial items found in the trash',   'act' )
                    ),
                    'public'            => true,
                    'query_var'         => true,
                    'menu_position'     => 20,
                    'menu_icon'         => 'dashicons-testimonial',
                    'has_archive'       => false,
                    'supports'          => array( 'title', 'editor', 'thumbnail' ),
                    'rewrite'           => array( 'with_front' => false )
                )
            ),
            'query' => array(
                'post_type'         => 'testimonials',
                'p'                 => '',
                'posts_per_page'    => -1,
                'orderby'           => 'date',
                'order'             => 'DESC',
            ),
            'gravatar' => array(
                'size' => 32 
            )
        );
        return apply_filters( 'arconix_testimonials_defaults', $defaults );
    }

    function init() {
        if( ! class_exists( 'cmb_Meta_Box' ) )
            require_once( '/metabox/init.php' );

        if ( ! class_exists( 'Gamajo_Dashboard_Glancer' ) )
            require_once( 'class-gamajo-dashboard-glancer.php' );
    }

    /**
     * Register the post_type
     *
     * @since 0.5
     */
    function content_types() {
        $defaults = $this->defaults();
        register_post_type( $defaults['post_type']['slug'], $defaults['post_type']['args'] );
    }

    /**
     * Register Plugin Widget(s)
     * 
     * @since 0.5
     */
    function widgets() {
        register_widget( 'Arconix_Testimonials_Widget' );
    }

    /**
     * Filter The_Content and add our data to it
     * 
     * @param  string       $content 
     * @return null|string  $content return early if not on the correct CPT
     * @since 0.5
     */
    function content_filter( $content ) {
        if( ! 'testimonials' == get_post_type() ) return $content;

        // So we can grab the default gravatar size
        $defaults = $this->defaults();
        $gs = apply_filters( ' arconix_testimonials_content_gravatar_size', $defaults['gravatar']['size'] );

        $gravatar = $this->get_testimonial_gravatar( $gs );

        $content = $gravatar . $content;

        return $content;
    }


    function get_testimonial_gravatar( $size = 32, $echo = false ) {
        // Grab our metadata
        $custom = get_post_custom();

        isset( $custom["_act_email"][0] ) ? $gravatar = get_avatar( $custom["_act_email"][0], $size ) : $gravatar = '';

        if ( $echo )
            echo $gravatar;
        else
            return $gravatar;

    }


    function get_testimonial_citation( $show_author = true, $wrap_url = true, $echo = false ) {
        // Grab our metadata
        $custom = get_post_custom();
        isset( $custom["_act_url"][0] )? $url = esc_url( $custom["_act_url"][0] ) : $url = '';
        isset( $custom["_act_byline"][0] )? $byline = $custom["_act_url"][0] : $byline = '';

        $sep = apply_filters( 'arconix_testimonial_separator', ', ' );

        $before = '';
        $after = '';

        if ( $wrap_url && ! strlen( $url ) == 0 ) {
            $before = '<a href="{$url}">';
            $after = '</a>';
        }

        $author = '';

        if ( $show_author )
            $author = get_the_title();
        else
            $sep = '';

        $r = $before . $author . $sep . $byline . $after;

        if ( $echo )
            echo $r;
        else
            return $r;

    }

    /**
     * Register plugin shortcode(s)
     *
     * @since 0.5
     */
    function shortcodes() {
        add_shortcode( 'ac-testimonials', array( $this, 'testimonials_shortcode' ) );
    }

    /**
     * Testimonials shortcode
     *
     * @param array $atts Passed attributes
     * @param string $content N/A - self-closing shortcode
     * @return string result of query
     * @since  0.5
     */
    function testimonials_shortcode( $atts, $content = null ) {
        return ARCONIX_TESTIMONIALS::get_testimonial_data( $atts );
    }

    /**
     * Returns the testimonial loop results
     *
     * @param array $args Arguments for the query
     * @return string $return Returns the query results
     * @since 0.5
     */
    function get_testimonial_data( $args, $echo = false ) {
        $plugin_defaults = ARCONIX_TESTIMONIALS::defaults();

        $defaults = $plugin_defaults['query'];
        $defaults['gravatar_size'] = $plugin_defaults['gravatar']['size'];

        // Combine the passed args with the function defaults
        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'arconix_get_testimonial_data_args', $args );

        // Data integrity check
        if( ! absint( $args['gravatar_size'] ) ) {
            $args['gravatar_size'] = $plugin_defaults['gravatar']['size'];
        }

        // Extract the avatar size and remove the key from the array
        $gravatar_size = $args['gravatar_size'];
        unset( $args['gravatar_size'] );

        // Run our query
        $tquery = new WP_Query( $args );
        
        // Our string container
        $return = ''; 

        if( $tquery->have_posts() ) {
            $return .= '<div class="arconix-testimonials-wrap">';

            while( $tquery->have_posts() ) : $tquery->the_post();

                

                $return .= '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial-wrap">';
                $return .= $meta_gravatar;
                $return .= '<div class="arconix-testimonial-content">';
                $return .= '<blockquote>' . get_the_content() . '</blockquote>';
                $return .= '<cite>' . $meta_name . $separator . $meta_byline . '</cite>';
                $return .= '</div></div>';

            endwhile;

            $return .= '</div>';
        }
        else {
            $return = '<div class="arconix-testimonials-wrap"><div class="arconix-testimonials-none">' . __( 'No testimonials to display', 'act' ) . '</div></div>';
        }
        wp_reset_postdata();

        if( $echo )
            echo $return;
        else
            return $return;
    }

    /**
     * Load the plugin CSS. If the css file is present in the theme directory, it will be loaded instead,
     * allowing for an easy way to override the default template. If you'd like to remove the CSS entirely,
     * such as when building the styles into a single file, simply reference the filter and return false
     *
     * @example add_filter( 'pre_register_arconix_testimonials_css', '__return_false' );
     *
     * @since 0.5
     */
    function scripts() {
         // If the CSS is not being overridden in a theme folder, allow the user to filter it out entirely (if building into stylesheet or the like)
        if( apply_filters( 'pre_register_arconix_testimonials_css', true ) ) {
            // Checks the child directory and then the parent directory.
            if( file_exists( get_stylesheet_directory() . '/arconix-testimonials.css' ) )
                wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', false, ACT_VERSION );
            elseif( file_exists( get_template_directory() . '/arconix-testimonials.css' ) )
                wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', false, ACT_VERSION );
            else
                wp_enqueue_style( 'arconix-testimonials', ACT_CSS_URL . 'arconix-testimonials.css', false, ACT_VERSION );
        }


    }

    /**
     * Load the admin CSS. If you'd like to remove the CSS entirely, such as when building the styles
     * into a single file, simply reference the filter and return false
     *
     * @example add_filter( 'pre_register_arconix_testimonials_admin_css', '__return_false' );
     *
     * @since 0.5
     */
    function admin_scripts() {
        if( apply_filters( 'pre_register_arconix_testimonials_admin_css', true ) )
            wp_enqueue_style( 'arconix-testimonials-admin', ACT_CSS_URL . 'admin.css', false, ACT_VERSION );
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
    function messages( $messages ) {
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
            "testimonial-gravatar" => "Gravatar",
            "title" => "Author",
            "testimonial-byline" => "Byline",
            "testimonial-content" => "Testimonial",
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
            case "testimonial-gravatar":
                $this->get_testimonial_gravatar( 36, true );
                break;
            case "testimonial-content":
                the_excerpt();                
                break;
            case "testimonial-byline":
                $custom = get_post_custom();
                $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : $meta_byline = '';
                echo '<cite>' . $meta_byline . '</cite>';

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
    function title_text( $title ) {
        $screen = get_current_screen();

        if( 'testimonials' == $screen->post_type )
            $title = __( 'Enter author name here', 'act' );

        return $title;
    }

    /**
     * Add the Post type to the "Right Now" Dashboard Widget
     *
     * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
     * @since 0.5
     */
    function at_a_glance() {
        $glancer = new Gamajo_Dashboard_Glancer;
        $glancer->add( 'testimonials' ); // show only published entries
    }

    /**
     * Adds a widget to the dashboard.
     *
     * @since 0.5
     */
    function dash_widget() {
        wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', array( $this, 'dash_widget_output' ) );
    }

    /**
     * Output for the dashboard widget
     *
     * @since 0.5
     */
    function dash_widget_output() {
        require_once( ACT_DIR . 'dash-widget.php' );
    }

    /**
     * Create the post type metabox
     *
     * @param array $meta_boxes
     * @return array $meta_boxes
     * @since 0.5
     */
    function metaboxes( $meta_boxes ) {
        $metabox = array(
            'id' => 'testimonials-info',
            'title' => 'Testimonial Details',
            'pages' => array( 'testimonials' ), 
            'context' => 'normal',
            'priority' => 'high',
            'show_names' => true, 
            'fields' => array(
                array(
                    'name' => 'E-mail Address',
                    'id' => '_act_email',
                    'desc' => sprintf( __( 'To display the author\'s %sGravatar%s (optional).', 'act' ), '<a href="' . esc_url( 'http://gravatar.com' ) . '" target="_blank">', '</a>' ),
                    'type' => 'text_medium',
                ),
                array(
                    'name' => 'Byline',
                    'id' => '_act_byline',
                    'desc' => __( 'Enter a byline for the author of this testimonial (optional).', 'act' ),
                    'type' => 'text_medium',
                ),
                array(
                    'name' => 'Website',
                    'id' => '_act_url',
                    'desc' => __( 'Enter a URL for the individual or organization (optional).', 'act' ),
                    'type' => 'text_medium',
                )
            )
        );

        $meta_boxes[] = $metabox;

        return $meta_boxes;
    }
}
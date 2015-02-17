<?php

/**
 * Class covers the testimonial admin functionality.
 *
 * @since 1.0.0
 */
class Arconix_Testimonials_Admin {

    /**
     * The version of this plugin.
     *
     * @since   1.0.0
     * @access  private
     * @var     string      $version    The vurrent version of this plugin.
     */
    private $version;

    /**
     * The directory path to this plugin.
     *
     * @since   1.2.0
     * @access  private
     * @var     string      $dir    The directory path to this plugin
     */
    private $dir;

    /**
     * The url path to this plugin.
     *
     * @since   1.2.0
     * @access  private
     * @var     string      $url    The url path to this plugin
     */
    private $url;


    /**
     * Initialize the class and set its properties.
     *
     * @since   1.0.0
     * @version 1.2.0
     * @param   string      $version    The version of this plugin.
     */
    public function __construct( $version ) {
        $this->version = $version;
        $this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->url = trailingslashit( plugin_dir_url( __FILE__ ) );

        register_activation_hook( __FILE__,             array( $this, 'activation' ) );
        register_deactivation_hook( __FILE__,           array( $this, 'deactivation' ) );

        add_action( 'init',                             array( $this, 'content_types' ) );
        add_action( 'init',                             array( $this, 'shortcodes' ) );
        add_action( 'widgets_init',                     array( 'Arconix_Testimonials_Widget', 'register' ) );
        add_action( 'wp_enqueue_scripts',               array( $this, 'scripts' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'admin_scripts' ) );
        add_action( 'manage_posts_custom_column',       array( $this, 'column_action' ) );
        add_action( 'wp_dashboard_setup',               array( $this, 'dash_widget' ) );
        add_action( 'dashboard_glance_items',           array( $this, 'at_a_glance' ) );
        add_action( 'add_meta_boxes',                   array( $this, 'shortcode_metabox' ) );

        add_filter( 'widget_text',                      'do_shortcode' );
        add_filter( 'the_content',                      array( $this, 'content_filter' ) );
        add_filter( 'enter_title_here',                 array( $this, 'title_text' ) );
        add_filter( 'cmb_meta_boxes',                   array( $this, 'metaboxes' ) );
        add_filter( 'post_updated_messages',            array( $this, 'messages' ) );
        add_filter( 'manage_edit-testimonials_columns', array( $this, 'columns_filter' ) );

        // For use if Arconix Flexslider is active
        add_filter( 'arconix_flexslider_slide_image_return',    array( $this, 'flexslider_image_return' ), 10, 4 );
        add_filter( 'arconix_flexslider_slide_content_return',  array( $this, 'flexslider_content' ), 10, 2 );
    }

    /**
     * Runs on plugin activation.
     *
     * @since 1.0.0
     */
    public function activation() {
        $this->content_types();
        flush_rewrite_rules();
    }

    /**
     * Runs on plugin deactivation.
     *
     * @since 1.0.0
     */
    public function deactivation() {
        flush_rewrite_rules();
    }

    /**
     * Set our plugin defaults for post type registration and default query args.
     *
     * @since  1.0.0
     *
     * @return array $defaults
     */
    public function defaults() {
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
                        'search_items'          => __( 'Search Testimonials',                       'act' ),
                        'not_found'             => __( 'No testimonial items found',                'act' ),
                        'not_found_in_trash'    => __( 'No testimonial items found in the trash',   'act' )
                    ),
                    'public'            => true,
                    'query_var'         => true,
                    'menu_position'     => 20,
                    'menu_icon'         => 'dashicons-testimonial',
                    'has_archive'       => false,
                    'supports'          => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
                    'rewrite'           => array( 'with_front' => false )
                )
            )
        );

        return apply_filters( 'arconix_testimonials_admin_defaults', $defaults );
    }

    /**
     * Register the post_type.
     *
     * @since 1.0.0
     */
    public function content_types() {
        $defaults = $this->defaults();
        register_post_type( $defaults['post_type']['slug'], $defaults['post_type']['args'] );
    }

    /**
     * Register plugin shortcode.
     *
     * @since 1.0.0
     */
    public function shortcodes() {
        add_shortcode( 'ac-testimonials', array( $this, 'testimonials_shortcode' ) );
    }

    /**
     * Testimonials shortcode.
     *
     * @since  1.0.0
     *
     * @param  array  $atts    Passed attributes
     * @param  string $content N/A - self-closing shortcode
     *
     * @return string          result of query
     */
    public function testimonials_shortcode( $atts, $content = null ) {
        $t = new Arconix_Testimonial;

        return $t->loop( $atts );
    }


    /**
     * Filter The_Content and add our data to it
     *
     * @since   1.0.0
     * @version 1.2.0
     *
     * @global  stdObj $post    std Post
     * @param   string $content main content
     * @return  string          our testimonial content
     */
    public function content_filter( $content ) {
        global $post;

        if( is_single() && $post->post_type == 'testimonial' && is_main_query() ) {

            $t = new Arconix_Testimonial;

            // So we can grab our default gravatar size and allow it to be filtered.
            $defaults = $t->defaults();

            $gs = apply_filters( 'arconix_testimonials_content_gravatar_size', $defaults['gravatar']['size'] );

            $gravatar = '<div class="arconix-testimonial-gravatar">' . $t->get_image( $gs ) . '</div>';

            $cite = '<div class="arconix-testimonial-info-wrap">' . $t->get_citation( false ) . '</div>';

            $content = '<div class="arconix-testimonial-content">' . $t->get_content() . '</div>';

            $content = $cite . $gravatar . $content;

        }

        return $content;
    }

    /**
     * Load required CSS.
     *
     * Load the plugin CSS. If the css file is present in the theme directory, it will be loaded instead,
     * allowing for an easy way to override the default template. If you'd like to remove the CSS entirely,
     * such as when building the styles into a single file, simply reference the filter and return false
     *
     * @example add_filter( 'pre_register_arconix_testimonials_css', '__return_false' );
     *
     * @since 1.0.0
     */
    public function scripts() {
         // If the CSS is not being overridden in a theme folder, allow the user to filter it out entirely (if building into stylesheet or the like)
        if ( apply_filters( 'pre_register_arconix_testimonials_css', true ) ) {
            // Checks the child directory and then the parent directory.
            if ( file_exists( get_stylesheet_directory() . '/arconix-testimonials.css' ) )
                wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', false, $this->version );
            elseif ( file_exists( get_template_directory() . '/arconix-testimonials.css' ) )
                wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', false, $this->version );
            else
                wp_enqueue_style( 'arconix-testimonials', $this->url . 'css/arconix-testimonials.css', false, $this->version );
        }
    }

    /**
     * Load the Amid-side CSS.
     *
     * Load the admin CSS. If you'd like to remove the CSS entirely, such as when building the styles
     * into a single file, simply reference the filter and return false
     *
     * @example add_filter( 'pre_register_arconix_testimonials_admin_css', '__return_false' );
     *
     * @since 1.0.0
     */
    public function admin_scripts() {
        if( apply_filters( 'pre_register_arconix_testimonials_admin_css', true ) )
            wp_enqueue_style( 'arconix-testimonials-admin', $this->url . 'css/admin.css', false, $this->version );
    }

    /**
     * Modifies the post save notifications to properly reflect the post-type
     *
     * @since  1.0.0
     *
     * @global stdObject $post
     * @global int       $post_ID
     *
     * @param  array     $messages
     *
     * @return array     $messages
     */
    public function messages( $messages ) {
        global $post, $post_ID;
        $post_type = get_post_type( $post_ID );

        $obj = get_post_type_object( $post_type );
        $singular = $obj->labels->singular_name;

        $messages[$post_type] = array(
            0  => '', // Unused. Messages start at index 1.
            1  => sprintf( __( $singular . ' updated. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            2  => __( 'Custom field updated.' ),
            3  => __( 'Custom field deleted.' ),
            4  => __( $singular . ' updated.' ),
            5  => isset( $_GET['revision'] ) ? sprintf( __( $singular . ' restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
            6  => sprintf( __( $singular . ' published. <a href="%s">View ' . strtolower( $singular ) . '</a>' ), esc_url( get_permalink( $post_ID ) ) ),
            7  => __( 'Page saved.' ),
            8  => sprintf( __( $singular . ' submitted. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            9  => sprintf( __( $singular . ' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview ' . strtolower( $singular ) . '</a>' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
            10 => sprintf( __( $singular . ' draft updated. <a target="_blank" href="%s">Preview ' . strtolower( $singular ) . '</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        );

        return $messages;
    }

    /**
     * Choose the specific columns we want to display in the WP Admin Testimonials list.
     *
     * @since   1.0.0
     * @version 1.1.0
     *
     * @param   array $columns
     *
     * @return  array $columns
     */
    public function columns_filter( $columns ) {
        $col_gr = array( 'testimonial-gravatar'     => __( 'Image', 'act' ) );
        $col_ta = array( 'title'                    => __( 'Author', 'act' ) );
        $col_tb = array( 'testimonial-byline'       => __( 'Byline', 'act' ) );
        $col_tc = array( 'testimonial-content'      => __( 'Testimonial', 'act' ) );
        $col_sc = array( 'testimonial-shortcode'    => __( 'Shortcode', 'act' ) );

        unset( $columns['title'] );

        $columns = array_slice( $columns, 0, 1, true ) + $col_gr + array_slice( $columns, 1, NULL, true );
        $columns = array_slice( $columns, 0, 2, true ) + $col_ta + array_slice( $columns, 2, NULL, true );
        $columns = array_slice( $columns, 0, 3, true ) + $col_tb + array_slice( $columns, 3, NULL, true );
        $columns = array_slice( $columns, 0, 4, true ) + $col_tc + array_slice( $columns, 4, NULL, true );
        $columns = array_slice( $columns, 0, 5, true ) + $col_sc + array_slice( $columns, 5, NULL, true );

        return apply_filters( 'arconix_testimonials_admin_column_define', $columns );
    }

    /**
     * Supply the data that shows up in the custom columns we defined.
     *
     * @since 1.0.0
     *
     * @param array $column
     */
    public function column_action( $column ) {
        $t = new Arconix_Testimonial;

        switch( $column ) {
            case "testimonial-gravatar":
                echo $t->get_image( 60 );
                break;
            case "testimonial-content":
                the_excerpt();
                break;
            case "testimonial-byline":
                echo $t->get_citation( false, true );
                break;
            case "testimonial-shortcode":
                printf( '[ac-testimonials p=%d]', get_the_ID() );

            default:
                break;
        }
    }

    /**
     * Customize the "Enter title here" text on the Testimonial creation screen
     *
     * @since  1.0.0
     *
     * @param  string $title
     *
     * @return $title
     */
    public function title_text( $title ) {
        $screen = get_current_screen();

        if( 'testimonials' == $screen->post_type )
            $title = __( 'Enter author name here', 'act' );

        return $title;
    }

    /**
     * Add the Post type to the "At a Glance" Dashboard Widget.
     *
     * @since 1.0.0
     */
    public function at_a_glance() {
        $glancer = new Gamajo_Dashboard_Glancer;
        $glancer->add( 'testimonials' );
    }

    /**
     * Adds a dashboard widget.
     *
     * Adds a widget to the dashboard. Can be overridden completely by a filter, but only shows for users that can
     * manage options (also filterable if desired)
     *
     * @since 1.0.0
     */
    public function dash_widget() {
        if( apply_filters( 'pre_register_arconix_testimonials_dashboard_widget', true ) and
            apply_filters( 'arconix_testimonial_dashboard_widget_security', current_user_can( 'manage_options' ) ) )
                wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', array( $this, 'dash_widget_output' ) );
    }

    /**
     * Output for the dashboard widget.
     *
     * @since 1.0.0
     */
    public function dash_widget_output() {
        echo '<div class="rss-widget">';

            wp_widget_rss_output( array(
                'url' => 'http://arconixpc.com/tag/arconix-testimonials/feed', // feed url
                'title' => 'Arconix Testimonials Posts', // feed title
                'items' => 3, // how many posts to show
                'show_summary' => 1, // display excerpt
                'show_author' => 0, // display author
                'show_date' => 1 // display post date
            ) );

            echo '<div class="act-widget-bottom"><ul>';
            ?>
                <li><a href="http://arcnx.co/atwiki" class="atdocs"><img src="<?php echo $this->url . 'css/images/page-16x16.png' ?>">Documentation</a></li>
                <li><a href="http://arcnx.co/athelp" class="athelp"><img src="<?php echo $this->url . 'css/images/help-16x16.png' ?>">Support Forum</a></li>
                <li><a href="http://arcnx.co/attrello" class="atdev"><img src="<?php echo $this->url . 'css/images/trello-16x16.png' ?>">Dev Board</a></li>
                <li><a href="http://arcnx.co/atsource" class="atsource"><img src="<?php echo $this->url . 'css/images/github-16x16.png'; ?>">Source Code</a></li>
            <?php
            echo '</ul></div>';
        echo '</div>';
    }

    /**
     * Create the post type metabox.
     *
     * @since  1.0.0
     *
     * @param  array $meta_boxes
     *
     * @return array $meta_boxes
     */
    public function metaboxes( $meta_boxes ) {
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

    /**
     * Adds another metabox to the testimonial creation screen.
     *
     * This metabox shows the shortcode with the post_id for users to display
     * just that testimonial on a post, page or other applicable location
     *
     * @since 1.1.0
     */
    public function shortcode_metabox() {
        add_meta_box( 'ac-shortcode-box', __( 'Testimonial Shortcode', 'act' ), array( $this, 'shortcode_box' ), 'testimonials', 'side' );
    }

    /**
     * Output for the testimonial shortcode metabox. Creates a readonly inputbox that outputs the testimonial shortcode
     * plus the $post_id
     *
     * @since 1.1.0
     *
     * @global int $post_ID ID of the current post
     */
    public function shortcode_box() {
        global $post_ID;
        ?>
        <p class="howto">
            <?php _e( 'To display this testimonial, copy the code below and paste it into your post, page, text widget or other content area.', 'act' ); ?>
        </p>
        <p><input type="text" value="[ac-testimonials p=<?php echo $post_ID; ?>]" readonly="readonly" class="widefat wp-ui-text-highlight code"></p>
        <?php
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
     * @return  string      $content        Modified return content with our testimonial-customized options
     */
    public function flexslider_content( $content, $display ) {
        global $post;

        // return early if we're not displaying anything or we're not working with a testimonial
        if ( ! $display || $display == 'none' || $post->post_type != 'testimonials' ) return;

        // Initialize our testimonial class
        $t = new Arconix_Testimonial();

        // Get our gravatar size
        $defaults = $t->defaults();
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
     * @return  string      $s              Empty string if on the testimonial post_type
     */
    public function flexslider_image_return( $content, $link_image, $image_size, $caption ) {
        global $post;

        if ( $post->post_type == 'testimonials' ) $content = '';

        return $content;
    }



}
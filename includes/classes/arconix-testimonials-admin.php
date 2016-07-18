<?php

/**
 * Class covers the testimonial admin functionality.
 *
 * @since 1.0.0
 */
class Arconix_Testimonials_Admin extends Arconix_CPT_Admin {

    /**
     * The directory path to this plugin.
     *
     * @since   1.2.0
     * @access  private
     * @var     string      $dir        The directory path to this plugin
     */
    private $dir;

    /**
     * The url path to this plugin.
     *
     * @since   1.2.0
     * @access  private
     * @var     string      $url        The url path to this plugin
     */
    private $url;

    /**
     * Initialize the class and set its properties.
     *
     * @since   1.0.0
     * @version 1.2.0
     * @param   string      $version    The version of this plugin.
     */
    public function __construct() {
        $this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
        $this->url = trailingslashit( plugin_dir_url( __FILE__ ) );

        add_action( 'init',                             array( $this, 'shortcodes' ) );
        add_action( 'wp_enqueue_scripts',               array( $this, 'scripts' ) );
        add_action( 'admin_enqueue_scripts',            array( $this, 'admin_scripts' ) );
        add_action( 'wp_dashboard_setup',               array( $this, 'dash_widget' ) );

        add_filter( 'widget_text',                      'do_shortcode' );
        add_filter( 'the_content',                      array( $this, 'content_filter' ) );
        add_filter( 'enter_title_here',                 array( $this, 'title_text' ) );
        add_filter( 'cmb_meta_boxes',                   array( $this, 'metaboxes' ) );

        // For use if Arconix Flexslider is active
        add_filter( 'arconix_flexslider_slide_image_return',    array( $this, 'flexslider_image_return' ), 10, 4 );
        add_filter( 'arconix_flexslider_slide_content_return',  array( $this, 'flexslider_content' ), 10, 2 );
        
        parent::__construct( 'testimonials', Arconix_Testimonials_Plugin::$textdomain );
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
     *
     * @param   array        $atts       Passed attributes
     * @param   string       $content    N/A - self-closing shortcode
     * @return  string                   Result of query
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
     * @global  stdObj      $post       Std Post
     * @param   string      $content    Main content
     * @return  string                  Our testimonial content
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
     * @since   1.0.0
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
     * @since   1.0.0
     */
    public function admin_scripts() {
        if( apply_filters( 'pre_register_arconix_testimonials_admin_css', true ) )
            wp_enqueue_style( 'arconix-testimonials-admin', $this->url . 'css/admin.css', false, $this->version );
    }

   

    /**
     * Choose the specific columns we want to display in the WP Admin Testimonials list.
     *
     * @since   1.0.0
     * @version 1.1.0
     * @param   array       $columns        Existing column structure
     * @return  array       $columns        New column structure
     */
    public function columns_define( $columns ) {
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
     * @since   1.0.0
     * @param   array       $column
     */
    public function column_value( $column ) {
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
     * @since   1.0.0
     * @param   string      $title      Existing Title Text
     * @return                          New Title Text
     */
    public function title_text( $title ) {
        $screen = get_current_screen();

        if( 'testimonials' == $screen->post_type )
            $title = __( 'Enter author name here', Arconix_Testimonials_Plugin::$textdomain );

        return $title;
    }


    /**
     * Adds a dashboard widget.
     *
     * Adds a widget to the dashboard. Can be overridden completely by a filter, but only shows for users that can
     * manage options (also filterable if desired)
     *
     * @since   1.0.0
     */
    public function dash_widget() {
        if( apply_filters( 'pre_register_arconix_testimonials_dashboard_widget', true ) and
            apply_filters( 'arconix_testimonial_dashboard_widget_security', current_user_can( 'manage_options' ) ) )
                wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', array( $this, 'dash_widget_output' ) );
    }

    /**
     * Output for the dashboard widget.
     *
     * @since   1.0.0
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
     * @return  string      $s              Empty string if on the testimonial post_type
     */
    public function flexslider_image_return( $content, $link_image, $image_size, $caption ) {
        global $post;

        if ( $post->post_type == 'testimonials' ) $content = '';

        return $content;
    }



}
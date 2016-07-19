<?php
/**
 * Create the metaboxes for the Testimonial Creation Screen
 * 
 * @since 1.2.0
 */
class Arconix_Testimonials_Metaboxes {
    
    /**
     * Translation Textdomain.
     * 
     * @since   1.2.0
     * @var     string  $textdomain     For i18n
     */
    protected $textdomain;
    
    /**
     * Initialize the class.
     *
     * @since   1.2.0
     */
    public function __construct() {
        $this->textdomain = Arconix_Testimonials_Plugin::$textdomain;
    }
    
    /**
     * Get our hooks into WordPress
     * 
     * @since   1.2.0
     */
    public function init() {
        add_action( 'cmb2_admin_init',    array( $this, 'cmb2') );        
        add_action( 'add_meta_boxes',     array( $this, 'shortcode_metabox' ) );
    }

    /**
     * Define the Metabox and its fields.
     *
     * @since   1.2.0
     */
    public function cmb2() {
        // Initiate the metabox
        $cmb = new_cmb2_box( array(
            'id'            => 'arconix-testimonials-details',
            'title'         => __( 'Testimonial Details', $this->textdomain ),
            'object_types'  => array( 'testimonials' ),
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true
        ) );

        // Add the Link Type field
        $cmb->add_field( array(
            'id'    => '_act_email',
            'name'  => __( 'E-mail Addres', $this->textdomain ),
            'desc'  => sprintf( __( 'To display the author\'s %sGravatar%s (optional).', $this->textdomain ), '<a href="' . esc_url( 'http://gravatar.com' ) . '" target="_blank">', '</a>' ),
            'type'  => 'text_email'
        ) );
        
        $cmb->add_field( array(
            'id'    => '_act_byline',
            'name'  => __( 'Byline', $this->textdomain ),
            'desc'  => __( 'Enter a byline for the author of this testimonial (optional).', $this->textdomain ),
            'type'  => 'text'
        ) );
        
        $cmb->add_field( array(
            'id'    => '_act_url',
            'name'  => __( 'Website', $this->textdomain ),
            'desc'  => __( 'Enter a URL for the individual or organization (optional).', $this->textdomain ),
            'type'  => 'text',
        ) );

    }
        
    /**
     * Adds another metabox to the testimonial creation screen.
     *
     * This metabox shows the shortcode with the post_id for users to display
     * just that testimonial on a post, page or other applicable location
     *
     * @since   1.2.0
     */
    public function shortcode_metabox() {
        add_meta_box( 'ac-shortcode-box', __( 'Testimonial Shortcode', $this->textdomain ), array( $this, 'shortcode_box' ), 'testimonials', 'side' );
    }

    /**
     * Output for the testimonial shortcode metabox. 
     * 
     * Creates a readonly inputbox that outputs the testimonial shortcode
     * plus the $post_id
     *
     * @since   1.2.0
     * @global  int     $post           ID of the current post
     */
    public function shortcode_box() {
        global $post_ID;
        ?>
        <p class="howto">
            <?php _e( 'To display this testimonial, copy the code below and paste it into your post, page, text widget or other content area.', $this->textdomain ); ?>
        </p>
        <p><input type="text" value="[ac-testimonials p=<?php echo $post_ID; ?>]" readonly="readonly" class="widefat wp-ui-text-highlight code"></p>
        <?php
    }
}
<?php
/**
 * Create the metaboxes for the Testimonial Creation Screen
 * 
 * @author      John Gardner
 * @link        http://arconixpc.com/plugins/arconix-testimonials
 * @license     GPLv2 or later
 * @since       1.2.0
 */
class Arconix_Testimonials_Metaboxes {
    
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
            'title'         => __( 'Testimonial Details', 'arconix-testimonials' ),
            'object_types'  => array( 'testimonials' ),
            'context'       => 'normal',
            'priority'      => 'high',
            'show_names'    => true
        ) );

        // Add the Link Type field
        $cmb->add_field( array(
            'id'    => '_act_email',
            'name'  => __( 'E-mail Addres', 'arconix-testimonials' ),
            'desc'  => sprintf( __( 'To display the author\'s %sGravatar%s (optional).', 'arconix-testimonials' ), '<a href="' . esc_url( 'http://gravatar.com' ) . '" target="_blank">', '</a>' ),
            'type'  => 'text_email'
        ) );
        
        $cmb->add_field( array(
            'id'    => '_act_byline',
            'name'  => __( 'Byline', 'arconix-testimonials' ),
            'desc'  => __( 'Enter a byline for the author of this testimonial (optional).', 'arconix-testimonials' ),
            'type'  => 'text'
        ) );
        
        $cmb->add_field( array(
            'id'    => '_act_url',
            'name'  => __( 'Website', 'arconix-testimonials' ),
            'desc'  => __( 'Enter a URL for the individual or organization (optional).', 'arconix-testimonials' ),
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
        add_meta_box( 'ac-shortcode-box', __( 'Testimonial Shortcode', 'arconix-testimonials' ), array( $this, 'shortcode_box' ), 'testimonials', 'side' );
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
            <?php _e( 'To display this testimonial, copy the code below and paste it into your post, page, text widget or other content area.', 'arconix-testimonials' ); ?>
        </p>
        <p><input type="text" value="[ac-testimonials p=<?php echo $post_ID; ?>]" readonly="readonly" class="widefat wp-ui-text-highlight code"></p>
        <?php
    }
}
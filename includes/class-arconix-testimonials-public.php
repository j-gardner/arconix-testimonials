<?php

/**
 * Class covers the testimonial loop itself and associated functions
 *
 * @since 1.2.0
 */
class Arconix_Testimonial {

    /**
     * Holds class defaults, populated in constructor.
     *
     * @since   1.2.0
     * @access  protected
     * @var     array       $defaults   default args
     */
    protected $defaults;

    /**
     * Constructor
     *
     * Populates the $defaults var
     *
     * @since   1.2.0
     */
    function __construct() {
        $this->defaults = array(
            'query'     => array(
                'post_type'         => 'testimonials',
                'p'                 => '',
                'posts_per_page'    => -1,
                'orderby'           => 'date',
                'order'             => 'DESC',
            ),
            'gravatar'  => array(
                'size'              => 60
            ),
            'content'   => 'full'
        );
    }

    /**
     * Return our default values for the query and gravatar
     *
     * @since   1.0.0
     * @version 1.2.0
     *
     * @return  array       $defaults
     */
    function defaults() {
        return apply_filters( 'arconix_testimonials_defaults', $this->defaults );
    }

    /**
     * Finds and returns the image associated with the Testimonial.
     * Checks for a post_thumbnail, then a gravatar and if neither exist
     * return false
     *
     * @since   1.0.0
     * @version 1.2.0
     * @param   int     $size   size of the image to return
     * @return  string  $image  string containing the image or false
     */
    function get_image( $size = 60 ) {
        // Get the post metadata
        $meta = get_post_meta( get_the_id(), '_act_email', true );

        if ( has_post_thumbnail() )
            $image = get_the_post_thumbnail( null, array( $size, $size ) );
        elseif ( isset ( $meta ) )
            $image = get_avatar( $meta, $size );
        else
            return false;

        return $image;
    }

    /**
     * Get the testimonial citation information.
     *
     * @since  1.0.0
     * @param  bool     $show_author    show the author with the citation
     * @param  bool     $wrap_url       wrap the URL around the byline
     * @return string                   text of citation
     */
    function get_citation( $show_author = true, $wrap_url = true ) {
        // Grab our metadata
        $custom = get_post_custom();
        isset( $custom["_act_byline"][0] ) ? $byline = $custom["_act_byline"][0] : $byline = '';
        isset( $custom["_act_url"][0] ) ? $url = esc_url( $custom["_act_url"][0] ) : $url = '';

        // Author
        if ( $show_author )
            $author = '<div class="arconix-testimonial-author">' . get_the_title() . '</div>';
        else
            $author = '';

        // Separator
        if ( ! $show_author || strlen( $byline ) == 0 )
            $sep = '';
        else
            $sep = apply_filters( 'arconix_testimonial_separator', ', ' );

        // Byline
        if ( strlen( $byline ) != 0 ) {
            $before = '<div class="arconix-testimonial-byline">';
            $after = '</div>';

            // URL
            if ( $wrap_url && ! strlen( $url ) == 0 ) {
                $before .= '<a href="' . $url . '">';
                $after = '</a>' . $after;
            }

        }
        else {
            $before = '';
            $after = '';
        }

        return $author . $sep . $before . $byline . $after;
    }

    /**
     * Output Testimonial Content
     *
     * Return the content or the excerpt depending on the $content param
     *
     * @since   1.2.0
     * @param   bool    $content    full | excerpt - display all the testimonial or the excerpt
     * @return  string              Testimonial content
     */
    function get_content( $content = 'full' ) {

        if ( $content == 'excerpt' )
            $s = get_the_excerpt();
        else
            $s = apply_filters( 'the_content', get_the_content() );

        return $s;
    }

    /**
     * Returns the testimonial loop results
     *
     * @since   1.0.0
     * @version 1.1.1
     *
     * @param   array   $args       Query arguments
     * @param   bool    $echo       Echo or return results
     * @return  string              The query results
     */
    function loop( $args, $echo = false ) {
        $plugin_defaults = $this->defaults();

        $defaults = $plugin_defaults['query'];
        $defaults['gravatar_size'] = $plugin_defaults['gravatar']['size'];
        $defaults['content'] = $plugin_defaults['content'];

        // Combine the passed args with the function defaults
        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'arconix_get_testimonial_data_args', $args );

        // Extract the avatar size and remove the key from the array
        $gravatar_size = $args['gravatar_size'];
        unset( $args['gravatar_size'] );

        // Extract the content value and remove the key from the array
        $content = $args['content'];
        unset( $args['content'] );

        // Run our query
        $tquery = new WP_Query( $args );

        $r = '';

        if( $tquery->have_posts() ) {

            $r .= '<div class="arconix-testimonials-wrap">';

            while( $tquery->have_posts() ) : $tquery->the_post();

                $r .= '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial-wrap">';
                $r .= '<div class="arconix-testimonial-content">' . $this->get_content( $content ) . '</div>';
                $r .= '<div class="arconix-testimonial-info-wrap">';
                $r .= '<div class="arconix-testimonial-gravatar">' . $this->get_image( $gravatar_size ) . '</div>';
                $r .= '<div class="arconix-testimonial-cite">' . $this->get_citation() . '</div>';
                $r .= '</div></div>';

            endwhile;

            $r .= '</div>';
        }
        else {
            $r .= '<div class="arconix-testimonials-wrap"><div class="arconix-testimonials-none">' . __( 'No testimonials to display', 'act' ) . '</div></div>';
        }
        wp_reset_postdata();

        if( $echo === true )
            echo $r;
        else
            return $r;
    }

}
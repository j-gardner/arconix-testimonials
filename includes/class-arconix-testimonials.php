<?php

/**
 * Class covers the testimonial loop itself and associated functions
 *
 * @since 1.0.0
 */
class Arconix_Testimonials {

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    function __construct() {

    }

    /**
     * Set our default values for the query and gravatar
     *
     * @since  1.0.0
     * @version 1.2.0
     *
     * @return array $defaults
     */
    function defaults() {

        $defaults = array(
            'query' => array(
                'post_type'         => 'testimonials',
                'p'                 => '',
                'posts_per_page'    => -1,
                'orderby'           => 'date',
                'order'             => 'DESC',
            ),
            'gravatar' => array(
                'size' => 60
            )
        );

        return apply_filters( 'arconix_testimonials_defaults', $defaults );

    }

    /**
     * Finds and returns the image associated with the Testimonial.
     * Checks for a post_thumbnail, then a gravatar and if neither exist
     * return false
    *
     * @since  1.0.0
     * @version 1.2.0
     *
     * @param  integer $size size of the image to return
     * @param  boolean $echo echo or return the data
     *
     * @return mixed         string containing the image or false
     */
    function get_image( $size = 60, $echo = false ) {
        // Get the post metadata
        $custom = get_post_custom();

        if ( has_post_thumbnail() )
            $image = get_the_post_thumbnail( null, array( $size, $size ) );
        elseif ( isset ( $custom["_act_email"][0] ) )
            $image = get_avatar( $custom["_act_email"][0], $size );
        else
            return false;

        if ( $echo === true && $image != false )
            echo $image;
        else
            return $image;
    }

    /**
     * Get the testimonial citation information.
     *
     * @since  1.0.0
     *
     * @param  boolean $show_author show the author with the citation
     * @param  boolean $wrap_url    wrap the URL around the byline
     * @param  boolean $echo        echo or return the citation
     *
     * @return string               text of citation
     */
    function get_citation( $show_author = true, $wrap_url = true, $echo = false ) {
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

        $r = $author . $sep . $before . $byline . $after;

        if ( $echo )
            echo $r;
        else
            return $r;
    }



    /**
     * Returns the testimonial loop results
     *
     * @since   1.0.0
     * @version 1.1.1
     *
     * @param  array   $args   query arguments
     * @param  boolean $echo   echo or return results
     *
     * @return string  $return returns the query results
     */
    function loop( $args, $echo = false ) {
        $plugin_defaults = $this->defaults();

        $defaults = $plugin_defaults['query'];
        $defaults['gravatar_size'] = $plugin_defaults['gravatar']['size'];

        // Combine the passed args with the function defaults
        $args = wp_parse_args( $args, $defaults );
        $args = apply_filters( 'arconix_get_testimonial_data_args', $args );

        // Extract the avatar size and remove the key from the array
        $gravatar_size = $args['gravatar_size'];
        unset( $args['gravatar_size'] );

        // Run our query
        $tquery = new WP_Query( $args );

        ob_start();

        if( $tquery->have_posts() ) {

            echo '<div class="arconix-testimonials-wrap">';

            while( $tquery->have_posts() ) : $tquery->the_post();

                echo '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial-wrap">';
                echo '<div class="arconix-testimonial-content">' . apply_filters( 'the_content', get_the_content() ) . '</div>';
                echo '<div class="arconix-testimonial-info-wrap">';
                echo '<div class="arconix-testimonial-gravatar">' . $this->get_image( $gravatar_size ) . '</div>';
                echo '<div class="arconix-testimonial-cite">' . $this->get_citation() . '</div>';
                echo '</div></div>';

            endwhile;

            echo '</div>';
        }
        else {
            echo '<div class="arconix-testimonials-wrap"><div class="arconix-testimonials-none">' . __( 'No testimonials to display', 'act' ) . '</div></div>';
        }
        wp_reset_postdata();

        if( $echo )
            echo ob_get_clean();
        else
            return ob_get_clean();
    }

}
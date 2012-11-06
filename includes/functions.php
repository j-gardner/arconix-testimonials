<?php
/**
 * Register plugin shortcode(s)
 *
 * @since 0.5
 */
function register_shortcodes() {
    add_shortcode( 'ac-testimonials', 'testimonials_shortcode' );
}

/**
 * Testimonials shortcode
 *
 * @param array $atts Passed attributes
 * @param string $content N/A as this is a self-closing shortcode
 * @return string result of query
 */
function testimonials_shortcode( $atts, $content = null ) {
    $defaults = array(
        'posts_per_page' => '1',
        'orderby' => 'rand',
        'order' => 'DESC',
        'gravatar_size' => '32'
    );

    $args = shortcode_atts( $defaults, $atts );

    return get_testimonial_data( $args );
}

/**
 * Returns the testimonial loop results
 *
 * @param array $args Arguments for the query
 * @return string $return Returns the query results
 * @since 0.5
 */
function get_testimonial_data( $args = '' ) {
    $defaults = array(
        'post_type' => 'testimonials',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'order' => 'DESC',
        'gravatar_size' => '32'
    );

    /* Combine the passed args with the function defaults */
    $args = wp_parse_args( $args, $defaults );

    /* Allow filtering of the array */
    $args = apply_filters( 'arconix_get_testimonials_args', $args );

    /* Data integrity checks */
    if( ! in_array( $args['orderby'], array( 'none', 'ID', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num' ) ) )
        $args['orderby'] = 'rand';
    if( ! in_array( $args['order'], array( 'ASC', 'DESC' ) ) )
        $args['order'] = 'DESC';
    if( ! in_array( $args['post_type'], get_post_types() ) )
        $args['post_type'] = 'testimonials';
    if( !absint( $args['gravatar_size'] ) )
        $args['gravatar_size'] = 32;

    /* Extract the avatar size and remove the key from the array */
    $gravatar_size = $args['gravatar_size'];
    unset( $args['gravatar_size'] );


    $tquery = new WP_Query( $args );

    $return = ''; // our string container

    if( $tquery->have_posts() ) {
        $return .= '<div class="arconix-testimonials-wrap">';

        while( $tquery->have_posts() ) : $tquery->the_post();

        /* Grab all of our custom post information */
        $custom = get_post_custom();
        $meta_email = isset( $custom["_act_email"][0] ) ? $custom["_act_email"][0] : null;
        $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
        $meta_url = isset( $custom["_act_url"][0] ) ? $custom["_act_url"][0] : null;
        $met_name = get_the_title();
        $meta_details = '';
        $meta_gravatar = '';

        /* If there's an e-mail address, return a gravatar */
        if( isset( $meta_email) ) $meta_gravatar = get_avatar( $meta_email, $gravatar_size );

        /* If the url has a value, then wrap it around the name and/or gravatar */
        if( isset( $meta_url ) ) {
            $meta_name = '<a href="' . esc_url( $meta_url ) . '">' . $meta_name . '</a>';
            if( isset( $meta_email ) )
                $meta_gravatar = '<a href="' . esc_url( $meta_url ) . '">' . $meta_gravatar . '</a>';
        }

        $meta_details .= $meta_name;
        if( isset( $meta_byline ) ) $meta_details .= '- ' . $meta_byline;

        $return .= '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial-wrap">';
        $return .= $meta_gravatar;
        $return .= '<div class="arconix-testimonial-content">';
        $return .= '<blockquote>' . get_the_content() . '</blockquote>';
        $return .= '<cite>' . $meta_details . '</cite>';
        $return .= '</div></div>';

        endwhile;

        $return .= '</div>';
    }

    return $return;
}

/**
 * Display testimonial loop results
 *
 * @param array $args Function arguments
 * @since 0.5
 */
function testimonial_data( $args = '' ) {
    $return = get_testimonial_data( $args );
    echo $return;
}

/**
 * Load the CSS if it exists
 *
 * @since 0.5
 */
function load_css() {
    /* Checks the child directory and then the parent directory */
    if( file_exists( get_stylesheet_directory() . '/arconix-testimonials.css' ) ) {
	wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
    }
    elseif( file_exists( get_template_directory() . '/arconix-testimonials.css' ) ) {
	wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
    }
}

/**
 * Register the Post Type Meta Box
 *
 * @param array $meta_boxes
 * @return array $meta_boxes
 * @since 0.5
 */
function register_meta_box( array $meta_boxes ) {
    $prefix = '_act_';

    $meta_boxes[] = array(
        'id' => 'testimonial',
        'title' => 'Testimonial Information',
        'pages' => array( 'testimonials' ), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names left of input
        'fields' => array(
            array(
                'name' => 'E-mail Address',
                'desc' => sprintf( __( 'To display the individual\'s %sGravatar%s (optional).', 'act' ), '<a href="' . esc_url( 'http://gravatar.com/' ) . '" target="_blank">', '</a>' ),
                'id' => $prefix . 'email',
                'type' => 'text'
            ),
            array(
                'name' => 'Byline',
                'desc' => 'Enter a byline for the person giving this testimonial (optional).',
                'id' => $prefix . 'byline',
                'type' => 'text'
            ),
            array(
                'name' => 'Website',
                'desc' => 'Enter a URL for the individual or organization (optional).',
                'id' => $prefix . 'url',
                'type' => 'text'
            )
        )
    );

    return $meta_boxes;
}
?>
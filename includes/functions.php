<?php
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
                'name' => 'Name',
                'desc' => 'Enter the individual\'s name.',
                'id' => $prefix . 'name',
                'type' => 'text'
            ),
            array(
                'name' => 'E-mail Address',
                'desc' => 'To display individual\'s <a href="http://gravatar.com">gravatar</a> (optional).',
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
                'desc' => 'Enter a URL for the individual or company (optional).',
                'id' => $prefix . 'url',
                'type' => 'text'
            )
        )
    );

    return $meta_boxes;
}

/**
 * Load the CSS if it exists
 *
 * @since 0.5
 */
function load_scripts() {
    /* Load CSS file (if it exists) */
    if( file_exists( get_stylesheet_directory() . "/arconix-testimonials.css" ) ) {
	wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
    }
    elseif( file_exists( get_template_directory() . "/arconix-flexslider.css" ) ) {
	wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', array(), ACT_VERSION );
    }
}

/**
 * Returns the testimonial loop results
 *
 * @param type $params Function paramaters not related to the query itself (like gravatar size)
 * @param type $query_args Arguments for the query
 * @return string
 * @since 0.5
 */
function get_testimonial_data( $params = '', $query_args = '' ) {
    $query_defaults = array(
        'post_type' => 'testimonials',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'order' => 'DESC'
    );

    $param_defaults = array(
        'gravatar_size' => '32'
    );

    /* Combine the passed params with the function defaults */
    $params = wp_parse_args( $params, $param_defaults );

    /* Do the same with the query args */
    $args = wp_parse_args( $args, $query_defaults );

    /* Allow filtering of those arrays */
    $params = apply_filters( 'arconix_get_testimonial_params', $params );
    $args = apply_filters( 'arconix_get_testimonials_args', $args );

    /* Data integrity checks */
    if ( ! in_array( $args['orderby'], array( 'none', 'ID', 'author', 'title', 'date', 'modified', 'parent', 'rand', 'comment_count', 'menu_order', 'meta_value', 'meta_value_num' ) ) )
            $args['orderby'] = 'rand';

    if ( ! in_array( $args['order'], array( 'ASC', 'DESC' ) ) )
            $args['order'] = 'DESC';

    if ( ! in_array( $args['post_type'], get_post_types() ) )
            $args['post_type'] = 'testimonials';

    $tquery = new WP_Query( $args );

    $return = ''; // our string container

    if( $tquery->have_posts() ) {
        $return .= '<div class="arconix-testimonials-wrap">';

        while( $tquery->have_posts() ) : $tquery->the_post();

        /* Grab all of our custom post information */
        $custom = get_post_custom();
        $meta_name = isset( $custom["_act_name"][0] ) ? $custom["_act_name"][0] : null;
        $meta_email = isset( $custom["_act_email"][0] ) ? $custom["_act_email"][0] : null;
        $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
        $meta_url = isset( $custom["_act_url"][0] ) ? $custom["_act_url"][0] : null;
        $meta_details = '';
        $meta_gravatar = '';

        /* If there's an e-mail address, return a gravatar */
        if( isset( $meta_email) ) $meta_gravatar = get_avatar( $meta_email, $params->gravatar_size );

        /* If the url has a value, then wrap it around the name and/or gravatar */
        if( isset( $meta_url ) ) {
            if( isset( $meta_name) )
                $meta_name = '<a href="' . esc_url( $meta_url ) . '">' . $meta_name . '</a>';
            if( isset( $meta_email ) )
                $meta_gravatar = '<a href="' . esc_url( $meta_url ) . '">' . $meta_gravatar . '</a>';
        }
        if( isset( $meta_name ) ) $meta_details .= $meta_name;
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
 * @param type $params
 * @param type $query_args
 * @since 0.5
 */
function testimonial_data( $params = '', $query_args = '' ) {
    $return = get_testimonial_data( $params, $query_args );

    echo $return;
}
?>
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
            $args['orderby'] = 'date';

    if ( ! in_array( $args['order'], array( 'ASC', 'DESC' ) ) )
            $args['order'] = 'DESC';

    if ( ! in_array( $args['post_type'], get_post_types() ) )
            $args['post_type'] = 'testimonials';

    $query = new WP_Query( $args );

    $return = ''; // our string container

    if( $query->have_posts() ) {
        $return .= '<div class="arconix-testimonials-list">';

        while( $query->have_posts() ) : $query->the_post();

        /* Grab all of our custom post information */
        $custom = get_post_custom();
        $_meta_details = '';
        $_meta_name = isset( $custom["_act_name"][0] ) ? $custom["_act_name"][0] : null;
        $_meta_email = isset( $custom["_act_email"][0] ) ? $custom["_act_email"][0] : null;
        $_meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
        $_meta_url = isset( $custom["_act_url"][0] ) ? $custom["_act_url"][0] : null;

        /* If the url has a value, then apply it to the name before we go any farther */
        if( isset( $_meta_url ) ) {
            if( isset( $_meta_name) )
                $_meta_name = '<a href="'. esc_url( $_meta_url ) .'">'. $_meta_name .'</a>';
        }

        if( isset( $_meta_email) ) $_meta_email .= get_avatar( $_meta_email, $params->gravatar_size );
        if( isset( $_meta_name ) ) $_meta_details .= $meta_name;
        if( isset( $_meta_byline ) ) $_meta_details .= '- ' . $_meta_byline;

        $return .= '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial">';
            $return .= $_meta_email;
            $return .= '<blockquote>';
                get_the_content();
            $return .= '</blockquote>';
            $return .= '<cite>' . $_meta_details . '</cite>';
        $return .= '</div>';

        endwhile;

        $return .= '</div>';
    }

    return $return;

}
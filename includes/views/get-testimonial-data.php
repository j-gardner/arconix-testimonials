<?php
// Data integrity check
if( ! absint( $args['gravatar_size'] ) )
    $args['gravatar_size'] = 32;

// Extract the avatar size and remove the key from the array
$gravatar_size = $args['gravatar_size'];
unset( $args['gravatar_size'] );

$tquery = new WP_Query( $args );

$return = ''; // our string container

if( $tquery->have_posts() ) {
    $return .= '<div class="arconix-testimonials-wrap">';

    while( $tquery->have_posts() ) : $tquery->the_post();

    // Grab all of our custom post information
    $custom = get_post_custom();
    $meta_email = isset( $custom["_act_email"][0] ) ? $custom["_act_email"][0] : null;
    $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
    $meta_url = isset( $custom["_act_url"][0] ) ? $custom["_act_url"][0] : null;
    $meta_name = get_the_title();
    $meta_details = '';
    $meta_gravatar = '';

    // If there's an e-mail address, return a gravatar
    if( isset( $meta_email) ) $meta_gravatar = get_avatar( $meta_email, $gravatar_size );

    // If the url has a value, then wrap it around the name and/or gravatar
    if( isset( $meta_url ) ) {
        $meta_name = '<a href="' . esc_url( $meta_url ) . '">' . $meta_name . '</a>';
        if( isset( $meta_email ) )
            $meta_gravatar = '<a href="' . esc_url( $meta_url ) . '">' . $meta_gravatar . '</a>';
    }

    $meta_details .= $meta_name;
    if( isset( $meta_byline ) ) $meta_details .= ' - ' . $meta_byline;

    $return .= '<div id="arconix-testimonial-' . get_the_ID() . '" class="arconix-testimonial-wrap">';
    $return .= $meta_gravatar;
    $return .= '<div class="arconix-testimonial-content">';
    $return .= '<blockquote>' . get_the_content() . '</blockquote>';
    $return .= '<cite>' . $meta_details . '</cite>';
    $return .= '</div></div>';

    endwhile;

    $return .= '</div>';
}
else {
    $return = '<div class="arconix-testimonials-wrap"><p class="arconix-testimonials-none">' . __( 'No testimonials to display', 'act' ) . '</p></div>';
}
wp_reset_postdata();
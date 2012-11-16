<?php

/**
 * Create the new post-type
 *
 * @since 0.5
 */
function act_post_type() {

    $args = apply_filters( 'arconix_testimonials_post_type_args', array(
        'labels' => array(
            'name' => __( 'Testimonials', 'act' ),
            'singular_name' => __( 'Testimonials', 'act' ),
            'add_new' => __( 'Add New', 'act' ),
            'add_new_item' => __( 'Add New Testimonial', 'act' ),
            'edit' => __( 'Edit', 'act' ),
            'edit_item' => __( 'Edit Testimonial', 'act' ),
            'new_item' => __( 'New Testimonial', 'act' ),
            'view' => __( 'View Testimonial', 'act' ),
            'view_item' => __( 'View Testimonial', 'act' ),
            'search_items' => __( 'Search Testimonials', 'act' ),
            'not_found' => __( 'No testimonials found', 'act' ),
            'not_found_in_trash' => __( 'No testimonials found in Trash', 'act' )
        ),
        'public' => true,
        'query_var' => true,
        'menu_position' => 20,
        'menu_icon' => ACT_IMAGES_URL . 'act-icon-16x16.png',
        'has_archive' => true,
        'supports' => array( 'title', 'editor' ),
        'rewrite' => array( 'slug' => 'testimonials', 'with_front' => false ),
    ) );

    register_post_type( 'testimonials', $args );
}
?>
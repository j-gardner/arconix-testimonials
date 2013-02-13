<?php
$prefix = '_act_'; // for use in the metabox id

$defaults = array(
    'post_type' => array(
        'slug' => 'testimonials',
        'args' => array(
            'labels' => array(
                'name'                  => __( 'Testimonials',                              'act' ),
                'singular_name'         => __( 'Testimonial',                               'act' ),
                'add_new'               => __( 'Add New',                                   'act' ),
                'add_new_item'          => __( 'Add New Testimonial Item',                  'act' ),
                'edit'                  => __( 'Edit',                                      'act' ),
                'edit_item'             => __( 'Edit Testimonial Item',                     'act' ),
                'new_item'              => __( 'New Item',                                  'act' ),
                'view'                  => __( 'View Testimonial',                          'act' ),
                'view_item'             => __( 'View Testimonial Item',                     'act' ),
                'search_items'          => __( 'Search Testimonial',                        'act' ),
                'not_found'             => __( 'No testimonial items found',                'act' ),
                'not_found_in_trash'    => __( 'No testimonial items found in the trash',   'act' )
            ),
            'public'            => true,
            'query_var'         => true,
            'menu_position'     => 20,
            'menu_icon'         => ACT_IMAGES_URL . 'testimonials-icon-16x16.png',
            'has_archive'       => false,
            'supports'          => array( 'title', 'editor', 'thumbnail', 'revisions' ),
            'rewrite'           => array( 'slug' => 'testimonials', 'with_front' => false )
        )
    ),
    'meta_box' => array(
        'id' => 'testimonials-information',
        'title' => 'Testimonial Information',
        'pages' => array( 'testimonials' ), // post type
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true, // Show field names left of input
        'fields' => array(
            array(
                'name' => 'E-mail Address',
                'id' => $prefix . 'email',
                'desc' => sprintf( __( 'To display the author\'s %sGravatar%s (optional).', 'act' ), '<a href="' . esc_url( 'http://gravatar.com' ) . '" target="_blank">', '</a>' ),
                'type' => 'text',
            ),
            array(
                'name' => 'Byline',
                'id' => $prefix . 'byline',
                'desc' => __( 'Enter a byline for the author of this testimonial (optional).', 'act' ),
                'type' => 'text',
            ),
            array(
                'name' => 'Website',
                'id' => $prefix . 'url',
                'desc' => __( 'Enter a URL for the individual or organization (optional).', 'act' ),
                'type' => 'text'
            )
        )
    )
);
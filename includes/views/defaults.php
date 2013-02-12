<?php
$defaults = array(
    'post_type' => array(
        'slug' => 'testimonials',
        'args' => array(
            'labels' => array(
                'name'                  => __( 'Testimonial',                               'act' ),
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
    'metabox' => array(
        'id'            => 'testimonial',
        'title'         => 'Testimonial Information',
        'pages'         => array( 'testimonials' ), // post type
        'context'       => 'normal',
        'priority'      => 'high',
        'show_names'    => true, // Show field names left of input
        'fields' => array(
            array(
                'name'  => 'E-mail Address',
                'desc'  => sprintf( __( 'To display the individual\'s %sGravatar%s (optional).', 'act' ), '<a href="' . esc_url( 'http://gravatar.com/' ) . '" target="_blank">', '</a>' ),
                'id'    => $prefix . 'email',
                'type'  => 'text'
            ),
            array(
                'name'  => 'Byline',
                'desc'  => __( 'Enter a byline for the individual giving this testimonial (optional).', 'act' ),
                'id'    => $prefix . 'byline',
                'type'  => 'text'
            ),
            array(
                'name'  => 'Website',
                'desc'  => __( 'Enter a URL for the individual or organization (optional).', 'act' ),
                'id'    => $prefix . 'url',
                'type'  => 'text'
            )
        )
    )
);
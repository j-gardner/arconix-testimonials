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
                'name' => 'Title',
                'desc' => 'Title of the indidivual (optional).',
                'id' => $prefix . 'title',
                'type' => 'text'
            ),
            array(
                'name' => 'Company Name',
                'desc' => 'Enter the company\'s name (optional).',
                'id' => $prefix . 'company_name',
                'type' => 'text',
            ),
            array(
                'name' => 'City',
                'desc' => '(optional).',
                'id' => $prefix . 'city',
                'type' => 'text'
            ),
            array(
                'name' => 'State',
                'desc' => '(optional).',
                'id' => $prefix . 'state',
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


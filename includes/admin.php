<?php
/**
 * Register the Post Type Meta Box
 *
 * @param array $meta_boxes
 * @return array $meta_boxes
 * @since 0.5
 */
function act_create_meta_box( array $meta_boxes ) {
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
                'desc' => __( 'Enter a byline for the individual giving this testimonial (optional).', 'act' ),
                'id' => $prefix . 'byline',
                'type' => 'text'
            ),
            array(
                'name' => 'Website',
                'desc' => __( 'Enter a URL for the individual or organization (optional).', 'act' ),
                'id' => $prefix . 'url',
                'type' => 'text'
            )
        )
    );

    return $meta_boxes;
}

/**
 * Modifies the post save notifications to properly reflect the post-type
 *
 * @global array $post
 * @global int $post_ID
 * @param array $messages
 * @return array $messages
 * @since 0.5
 */
function act_updated_messages( $messages ) {
    global $post, $post_ID;

    $messages['testimonials'] = array(
        0 => '', // Unused. Messages start at index 1.
        1 => sprintf( __( 'Testimonial updated. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
        2 => __( 'Custom field updated.' ),
        3 => __( 'Custom field deleted.' ),
        4 => __( 'Testimonial updated.' ),
        /* translators: %s: date and time of the revision */
        5 => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s' ), wp_post_revision_title( ( int ) $_GET['revision'], false ) ) : false,
        6 => sprintf( __( 'Testimonial published. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
        7 => __( 'Testimonial saved.' ),
        8 => sprintf( __( 'Testimonial submitted. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
        9 => sprintf( __( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial</a>' ),
                // translators: Publish box date format, see http://php.net/date
                date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
        10 => sprintf( __( 'Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
    );

    return $messages;
}

/**
 * Choose the specific columns we want to display
 *
 * @param array $columns
 * @return array $columns
 * @since 0.5
 */
function act_columns_filter( $columns ) {

    $columns = array(
        "cb" => "<input type=\"checkbox\" />",
        "title" => "Testimonial Author",
        "testimonial_content" => "Testimonial",
        "date" => "Date"
    );

    return $columns;
}

/**
 * Filter the data that shows up in the columns we defined above
 *
 * @global array $post
 * @param array $column
 * @since 0.5
 */
function act_column_data( $column ) {

    global $post;

    switch( $column ) {
        case "title":
            $custom = get_post_custom();
            $meta_byline = isset( $custom["_act_byline"][0] ) ? $custom["_act_byline"][0] : null;
            echo '<br />' . $meta_byline;
            break;

        case "testimonial_content":
            the_excerpt();
            break;

        default:
            break;
    }
}

/**
 * Customize the "Enter title here" text
 *
 * @param string $title
 * @return $title
 * @since 0.5
 */
function act_custom_title_text( $title ) {
    $screen = get_current_screen();

    if( 'testimonials' == $screen->post_type ) {
        $title = __( 'Enter the person\'s name here', 'act' );
    }
    return $title;
}

/**
 * Add the Post type to the "Right Now" Dashboard Widget
 *
 * @link http://bajada.net/2010/06/08/how-to-add-custom-post-types-and-taxonomies-to-the-wordpress-right-now-dashboard-widget
 * @version 0.5
 */
function act_right_now() {
    // Define the post type text here, allowing us to quickly re-use this code in other projects
    $ac_pt = 'testimonials';
    $ac_pt_p = 'Testimonials';
    $ac_pt_s = 'Testimonial';

    // No need to modify these 2
    $ac_pt_pp = $ac_pt_p . ' Pending';
    $ac_pt_sp = $ac_pt_s . ' Pending';


    $args = array(
        'public' => true,
        '_builtin' => false
    );
    $output = 'object';
    $operator = 'and';

    $num_posts = wp_count_posts( $ac_pt );
    $num = number_format_i18n( $num_posts->publish );
    $text = _n( $ac_pt_s, $ac_pt_p, intval( $num_posts->publish ) );

    if( current_user_can( 'edit_posts' ) ) {

        $num = "<a href='edit.php?post_type=$ac_pt'>$num</a>";
        $text = "<a href='edit.php?post_type=$ac_pt'>$text</a>";
    }

    echo '<td class="first b b-' . $ac_pt . '">' . $num . '</td>';
    echo '<td class="t ' . $ac_pt . '">' . $text . '</td>';
    echo '</tr>';

    if( $num_posts->pending > 0 ) {
        $num = number_format_i18n( $num_posts->pending );
        $text = _n( $ac_pt_sp, $ac_pt_pp, intval( $num_posts->pending ) );

        if( current_user_can( 'edit_posts' ) ) {

            $num = "<a href='edit.php?post_status=pending&post_type='$ac_pt'>$num</a>";
            $text = "<a href='edit.php?post_status=pending&post_type=$ac_pt'>$text</a>";
        }

        echo '<td class="first b b-' . $ac_pt . '">' . $num . '</td>';
        echo '<td class="t ' . $ac_pt . '">' . $text . '</td>';
        echo '</tr>';
    }
}

/**
 * Adds a widget to the dashboard.
 *
 * @since 0.5
 */
function act_register_dashboard_widget() {
    wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', 'act_dashboard_widget_output' );
}

/**
 * Output for the dashboard widget
 *
 * @since 0.5
 */
function act_dashboard_widget_output() {

    echo '<div class="rss-widget">';

    wp_widget_rss_output( array(
        'url' => 'http://arconixpc.com/tag/arconix-testimonials/feed', // feed url
        'title' => 'Arconix Testimonials Posts', // feed title
        'items' => 3, // how many posts to show
        'show_summary' => 1, // display excerpt
        'show_author' => 0, // display author
        'show_date' => 1 // display post date
    ) );

    echo '<div class="act-widget-bottom"><ul>';
    ?>
        <li><a href="http://arcnx.co/atwiki"><img src="<?php echo ACT_IMAGES_URL . 'page-16x16.png' ?>">Documentation</a></li>
        <li><a href="http://arcnx.co/athelp"><img src="<?php echo ACT_IMAGES_URL . 'help-16x16.png' ?>">Support Forum</a></li>
        <li><a href="http://arcnx.co/attrello"><img src="<?php echo ACT_IMAGES_URL . 'trello-16x16.png' ?>">Dev Board</a></li>
        <li><a href="http://arcnx.co/atsource"><img src="<?php echo ACT_IMAGES_URL . 'github-16x16.png'; ?>">Source Code</a></li>
    <?php
    echo '</ul></div>';
    echo '</div>';

    // handle the styling
    echo '<style type="text/css">
            #ac-testimonials .rsssummary { display: block; }
            #ac-testimonials .act-widget-bottom { border-top: 1px solid #ddd; padding-top: 10px; text-align: center; }
            #ac-testimonials .act-widget-bottom ul { list-style: none; }
            #ac-testimonials .act-widget-bottom ul li { display: inline; padding-right: 20px; }
            #ac-testimonials .act-widget-bottom img { padding-right: 3px; vertical-align: top; }
        </style>';
}
?>
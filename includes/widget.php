<?php
/**
 * Register the plugin widget
 * 
 * @since 0.5
 */
function register_act_widget() {
    register_widget( 'Arconix_Testimonials_Widget' );
}


/**
 * Testimonials Widget
 *
 * @since 0.5
 */
class Arconix_Testimonials_Widget extends WP_Widget {
    
    /**
     * @todo refactor Widget (defaults, external function call)
     */

    /**
     * Constructor. Set the default widget options and create widget.
     *
     * @since 0.9
     */
    function __construct() {

        /** Widget Settings */
        $widget_ops = array(
            'classname' => 'testimonials_widget',
            'description' => __( 'Display client testimonials', 'act' ),
        );

        /** Widget Control Settings */
        $control_ops = array( 'id_base' => 'arconix-testimonials-widget' );

        /** Create the widget */
        $this->WP_Widget( 'arconix-testimonials-widget', 'Arconix - Testimonials', $widget_ops, $control_ops );

    }

    /**
     * Widget Display
     *
     * @param type $args
     * @param type $instance
     * @since 0.9
     */
    function widget( $args, $instance ) {

        extract( $args );

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( !empty( $instance['title'] ) ) {
            echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
        }

        /* Widget output */
        $w_args = apply_filters( 'arconix-testimonials-query-args' , array(
            'post_type' => 'testimonials',
            'posts_per_page' => 1,
            'orderby' => 'rand'
	) );


        /* create a new query bsaed on our own arguments */
	$testimonials_query = new WP_Query( $w_args );

        if( $testimonials_query->have_posts() ) {

            global $post;

            echo '<div class="arconix-testimonials">';
            echo '<div class="arconix-quotes">';

            while( $testimonials_query->have_posts() ) : $testimonials_query->the_post();

                /* Grab all of our custom post information */
                $custom = get_post_custom();
                if( isset( $custom["_act_name"][0] ) ) $meta_name = $custom["_act_name"][0];
                if( isset( $custom["_act_title"][0] ) ) $meta_title = $custom["_act_title"][0];
                if( isset( $custom["_act_company_name"][0] ) )  $meta_company = $custom["_act_company_name"][0];
                if( isset( $custom["_act_city"][0] ) ) $meta_city = $custom["_act_city"][0];
                if( isset( $custom["_act_state"][0] ) )  $meta_state = $custom["_act_state"][0];
                if( isset( $custom["_act_url"][0] ) )  $meta_url = $custom["_act_url"][0];


                /* Build output based on which variables have been assigned values */
                $output = '';

                /**
                 * If the URL is set, then apply it to the either the company name or individual name
                 * before we do anything else
                 */
                if( isset( $meta_url ) ) {
                    if( isset( $meta_company ) ) {
			$meta_company = '<a href="'. $meta_url .'">'. $meta_company .'</a>';
                    }
                    else {
                        if( isset( $meta_name ) )
                            $meta_name = '<a href="'. $meta_url .'">'. $meta_name .'</a>';
                    }
                }

                /* Now we start */
                if( isset( $meta_name ) ) $output = $meta_name;

                if( isset( $meta_name ) && isset( $meta_title ) ) $output = $meta_name . ', ' . $meta_title;

                if( isset( $meta_company ) ) $output .= ' - ' . $meta_company;

                if( isset( $meta_city ) && isset( $meta_state ) ) {
                    $output .= ' ' . $meta_city .', '. $meta_state;
                } elseif ( isset( $meta_state ) ) {
                    $output .= ', '. $meta_state;
                }

                /* Run through the rest of the loop info */

                echo '<div id="post-' . get_the_ID() . '" class="arconix-quote ' . implode( ' ', get_post_class() ) .'">';
                    echo '<blockquote>';
                    the_content();
                    echo '</blockquote>';
                    echo "<cite>$output</cite>";
                echo '</div>';

            endwhile;

	    echo '</div>'; /* .arconix-quotes */

            if( !empty( $instance['more'] ) )
                echo '<a class="arconix-testimonials-more" href="/testimonials">' . $instance['more'] . '</a>';

	    echo '</div>'; /* .arconix-testimonials */
        }

        /* After widget (defined by themes). */
        echo $after_widget;

    }


    /**
     * Update the widget settings
     *
     * @param type $new_instance
     * @param type $old_instance
     * @return type array
     * @since 0.9
     */
    function update( $new_instance, $old_instance ) {

       $instance = $old_instance;

        /* Strip tags for title and name to remove HTML (important for text inputs). */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['more'] = strip_tags( $new_instance['more'] );

        return $instance;
   }


   /**
    * Widget form
    *
    * @param type $instance
    * @since 0.9
    */
   function form( $instance ) {

        /* Let's set up some widget defaults. */
        $defaults = array(
            'title' => 'Testimonials',
            'more' => 'Read More Testimonials'
        );
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>Use the Testimonials custom post type to add content to this widget.</p>

        <!-- Title: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'act' ); ?></label>
            <br />
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
        </p>

        <!-- Read More: Text Input -->
        <p>
            <label for="<?php echo $this->get_field_id( 'more' ); ?>"><?php _e( 'Read More text:', 'act' ); ?></label>
            <br />
            <input class="widefat" id="<?php echo $this->get_field_id( 'more' ); ?>" name="<?php echo $this->get_field_name( 'more' ); ?>" value="<?php echo $instance['more']; ?>" />
        </p>

        <?php   $title = esc_attr( $instance['title'] );
                $more = esc_attr( $instance['more'] );
   }

}

?>
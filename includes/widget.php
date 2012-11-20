<?php
/**
 * Register the plugin widget
 *
 * @since 0.5
 */
function act_register_widget() {
    register_widget( 'Arconix_Testimonials_Widget' );
}

/**
 * Testimonials Widget
 *
 * @since 0.5
 */
class Arconix_Testimonials_Widget extends WP_Widget {

    /**
     * Holds widget settings defaults, populated in constructor.
     *
     * @var array defaults
     * @since 0.5
     */
    protected $defaults;

    /**
     * Constructor. Set the default widget options and create widget.
     *
     * @since 0.9
     */
    function __construct() {
        $this->defaults = array(
            'posts_per_page' => 1,
            'orderby' => 'rand',
            'order' => 'DESC',
            'gravatar_size' => 32
        );

        /* Widget Settings */
        $widget_ops = array(
            'classname' => 'testimonials_widget',
            'description' => __( 'Display client testimonials', 'act' ),
        );

        /* Widget Control Settings */
        $control_ops = array( 'id_base' => 'arconix-testimonials-widget' );

        /* Create the widget */
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

        extract( $args, EXTR_SKIP );

        /* Merge with defaults */
	$instance = wp_parse_args( (array) $instance, $this->defaults );

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( !empty( $instance['title'] ) )
            echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;

        testimonial_data( $instance );

        /* After widget (defined by themes). */
        echo $after_widget;

    }

    /**
     * Update a particular instance.
     *
     * @param array $new_instance New settings for this instance as input by the user via form()
     * @param array $old_instance Old settings for this instance
     * @return array Settings to save or bool false to cancel saving
     * @since 0.5
     */
    function update( $new_instance, $old_instance ) {
       $instance = $old_instance;

        /* Strip tags for title and name to remove HTML (important for text inputs). */
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['posts_per_page'] = absint( $new_instance['posts_per_page'] );

        return $instance;
   }

   /**
    * Widget form
    *
    * @param array $instance Current Settings
    * @since 0.5
    */
   function form( $instance ) {

        /* Merge with defaults */
        $instance = wp_parse_args( (array) $instance, $defaults ); ?>

        <p>Use the Testimonials custom post type to add content to this widget.</p>

        <!-- Title: Text Input -->
	<p>
	    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'act' ); ?>:</label>
	    <input class="widefat" type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
	</p>
        <!-- Posts Number: Input Box -->
	<p>
	    <label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php _e( 'Number of items to show:', 'act' ); ?></label>
	    <input id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" type="text" value="<?php echo $instance['posts_per_page']; ?>" size="3" /></p>
	</p>
        <!-- Orderby: Select Box -->
	<p>
	    <label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Select Orderby', 'act' ); ?></label>
	    <select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
		<?php
		$orderby_items = array( 'ID', 'author', 'title', 'name', 'date', 'modified', 'rand', 'comment_count', 'menu_order' );
		foreach( $orderby_items as $orderby_item )
		    echo '<option value="' . $orderby_item . '" ' . selected( $orderby_item, $instance['orderby'], FALSE ) . '>' . $orderby_item . '</option>';
		?>
	    </select>
	</p>
        <!-- Order: Select Box -->
	<p>
	    <label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Select Order', 'act' ); ?></label>
	    <select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
		<?php
		$order_items = array( 'ASC', 'DESC' );
		foreach( $order_items as $order_item )
		    echo '<option value="' . $order_item . '" ' . selected( $order_item, $instance['order'], FALSE ) . '>' . $order_item . '</option>';
		?>
	    </select>
	</p>
        <!-- Gravatar Size: Select Box -->
        <p>
            <label for="<?php echo $this->get_field_id( 'gravatar_size' ); ?>"><?php _e( 'Gravatar Size', 'act' ); ?>:</label>
            <select id="<?php echo $this->get_field_id( 'gravatar_size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>">
                <?php
                $sizes = array( __( 'Small', 'act' ) => 32, __( 'Medium', 'act' ) => 48, __( 'Large', 'act' ) => 64, __( 'X-Large', 'act' ) => 80 );
                /* Allow the gravatar sizes to be filtered */
                $sizes = apply_filters( 'arconix_testimonials_gravatar_sizes', $sizes );

                foreach( (array) $sizes as $label => $size ) {
                    echo '<option value="' . absint( $size ) . '" ' . selected( $size, $instance['gravatar_size'], FALSE ) . '>'. printf( '%s (%spx)', $label, $size ) . '</option>';
                } ?>
            </select>
        </p>
        <?php
    }
}

?>
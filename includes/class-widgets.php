<?php
/**
 * Testimonials Widget
 *
 * @since 1.0.0
 * @package arconix-testimonials/widgets
 */

/**
 * Create a testimonial widget
 */
class Arconix_Testimonials_Widget extends WP_Widget {
	/**
	 * Holds widget settings defaults, populated in constructor.
	 *
	 * @since 1.0.0
	 *
	 * @var array defaults
	 */
	protected $defaults = array();

	/**
	 * Registers the widget with the WordPress Widget API.
	 *
	 * @since 1.1.0
	 */
	public static function register() {
		register_widget( __CLASS__ );
	}

	/**
	 * Constructor. Set the default widget options and create widget.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->defaults = array(
			'title'          => '',
			'content'        => 'full',
			'posts_per_page' => 1,
			'p'              => '',
			'orderby'        => 'rand',
			'order'          => 'ASC',
			'gravatar_size'  => 32,
		);

		$widget_ops = array(
			'classname'   => 'arconix_testimonials_widget',
			'description' => __( 'Display client testimonials', 'act' ),
		);
		parent::__construct( 'arconix-testimonials', __( 'Arconix Testimonials', 'act' ), $widget_ops );
	}

	/**
	 * Widget Display.
	 *
	 * Loops through available testimonials as dictated by user and outputs
	 * them to the screen
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		extract( $args, EXTR_SKIP );

		// Merge with defaults.
		$instance = wp_parse_args( $instance, $this->defaults );

		// Before widget (defined by themes).
		echo $before_widget;

		// Title of widget (before and after defined by themes).
		if ( ! empty( $instance['title'] ) ) {
			echo $before_title . apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base ) . $after_title;
		}

		$t = new Arconix_Testimonials();
		$t->loop( $instance, true );

		// After widget (defined by themes).
		echo $after_widget;
	}

	/**
	 * Update a particular instance.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $new_instance New settings for this instance as input by the user via form().
	 * @param  array $old_instance Old settings for this instance.
	 *
	 * @return array Settings to save or bool false to cancel saving
	 */
	public function update( $new_instance, $old_instance ) {
		$instance                   = $old_instance;
		$instance['title']          = strip_tags( $new_instance['title'] );
		$instance['p']              = strip_tags( $new_instance['p'] );
		$instance['posts_per_page'] = strip_tags( $new_instance['posts_per_page'] );

		return $new_instance;
	}

	/**
	 * Widget form
	 *
	 * @since 1.0.0
	 *
	 * @param array $instance Current Settings.
	 */
	public function form( $instance ) {

		/* Merge with defaults */
		$instance = wp_parse_args( $instance, $this->defaults ); ?>

		<p>Use the Testimonials custom post type to add content to this widget.</p>

		<!-- Title: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'act' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<!-- Specific Post ID: Input Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'p' ); ?>"><?php _e( 'Specific ID', 'act' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'p' ); ?>" name="<?php echo $this->get_field_name( 'p' ); ?>" type="text" value="<?php echo esc_attr( $instance['p'] ); ?>" size="4" />
			</p>
		</p>
		<!-- Content: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'content' ); ?>"><?php _e( 'Content', 'act' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'content' ); ?>" name="<?php echo $this->get_field_name( 'content' ); ?>">
			<?php
			$contents = array( 'full', 'excerpt' );
			foreach ( $contents as $content ) {
				echo '<option value="' . $content . '" ' . selected( $content, $instance['content'] ) . '>' . $content . '</option>';
			}
			?>
			</select>
		</p>
		<!-- Content Limit: Text Input -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'act' ); ?>:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat" />
		</p>
		<!-- Posts Number: Input Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'posts_per_page' ); ?>"><?php _e( 'Number of items to show:', 'act' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'posts_per_page' ); ?>" name="<?php echo $this->get_field_name( 'posts_per_page' ); ?>" type="text" value="<?php echo esc_attr( $instance['posts_per_page'] ); ?>" size="2" />
			</p>
		</p>
		<!-- Orderby: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ); ?>"><?php _e( 'Select Orderby', 'act' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'orderby' ); ?>" name="<?php echo $this->get_field_name( 'orderby' ); ?>">
			<?php
			$orderby_items = array( 'ID', 'author', 'title', 'name', 'date', 'modified', 'rand', 'comment_count', 'menu_order' );
			foreach ( $orderby_items as $orderby_item ) {
				echo '<option value="' . $orderby_item . '" ' . selected( $orderby_item, $instance['orderby'] ) . '>' . $orderby_item . '</option>';
			}
			?>
			</select>
		</p>
		<!-- Order: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'order' ); ?>"><?php _e( 'Select Order', 'act' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'order' ); ?>" name="<?php echo $this->get_field_name( 'order' ); ?>">
			<?php
			$order_items = array( 'ASC', 'DESC' );
			foreach ( $order_items as $order_item ) {
				echo '<option value="' . $order_item . '" ' . selected( $order_item, $instance['order'] ) . '>' . $order_item . '</option>';
			}
			?>
			</select>
		</p>
		<!-- Gravatar Size: Select Box -->
		<p>
			<label for="<?php echo $this->get_field_id( 'gravatar_size' ); ?>"><?php _e( 'Gravatar Size', 'act' ); ?>:</label>
			<select id="<?php echo $this->get_field_id( 'gravatar_size' ); ?>" name="<?php echo $this->get_field_name( 'gravatar_size' ); ?>">
				<?php
				$sizes = array(
					__( 'Small', 'act' )    => 32,
					__( 'Medium', 'act' )   => 48,
					__( 'Large', 'act' )    => 64,
					__( 'X-Large', 'act' )  => 80,
					__( 'XX-Large', 'act' ) => 96,
				);
				$sizes = apply_filters( 'arconix_testimonials_widget_gravatar_sizes', $sizes );
				foreach ( $sizes as $label => $size ) {
					?>
					<option value="<?php echo absint( $size ); ?>" <?php selected( $size, $instance['gravatar_size'] ); ?>><?php printf( '%s (%spx)', $label, $size ); ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}

}

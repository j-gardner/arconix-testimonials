<?php
/**
 * Class covers the testimonial admin functionality.
 *
 * @since 1.0.0
 * @package arconix-testimonials/admin
 */

if ( file_exists( dirname( __FILE__ ) . '/metabox/init.php' ) ) {
	require_once dirname( __FILE__ ) . '/metabox/init.php';
}

/**
 * Define the admin side functionality for testimonials.
 */
class Arconix_Testimonials_Admin {

	/**
	 * Plugin version.
	 *
	 * @since 1.1.0
	 *
	 * @var string plugin version
	 */
	public static $version = '1.1.1';


	/**
	 * Construct Method.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->constants();

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		add_action( 'init', array( $this, 'init' ), 9999 );
		add_action( 'init', array( $this, 'content_types' ) );
		add_action( 'init', array( $this, 'shortcodes' ) );
		add_action( 'widgets_init', array( 'Arconix_Testimonials_Widget', 'register' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'column_action' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'dash_widget' ) );
		add_action( 'dashboard_glance_items', array( $this, 'at_a_glance' ) );
		add_action( 'add_meta_boxes', array( $this, 'shortcode_metabox' ) );

		add_filter( 'widget_text', 'do_shortcode' );
		add_filter( 'the_content', array( $this, 'content_filter' ) );
		add_filter( 'enter_title_here', array( $this, 'title_text' ) );
		add_filter( 'cmb2_admin_init', array( $this, 'act_register_metaboxes' ) );
		add_filter( 'post_updated_messages', array( $this, 'messages' ) );
		add_filter( 'manage_edit-testimonials_columns', array( $this, 'columns_filter' ) );
	}

	/**
	 * Define plugin constants.
	 *
	 * @since 1.0.0
	 */
	public function constants() {
		define( 'ACT_VERSION', self::$version );
		define( 'ACT_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
		define( 'ACT_CSS_URL', trailingslashit( ACT_URL . 'css' ) );
		define( 'ACT_IMAGES_URL', trailingslashit( ACT_CSS_URL . 'images' ) );
		define( 'ACT_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
	}


	/**
	 * Runs on plugin activation.
	 *
	 * @since 1.0.0
	 */
	public function activation() {
		$this->content_types();
		flush_rewrite_rules();
	}

	/**
	 * Runs on plugin deactivation.
	 *
	 * @since 1.0.0
	 */
	public function deactivation() {
		flush_rewrite_rules();
	}

	/**
	 * Set our plugin defaults for post type registration and default query args.
	 *
	 * @since  1.0.0
	 *
	 * @return array $defaults
	 */
	public function defaults() {
		$defaults = array(
			'post_type' => array(
				'slug' => 'testimonials',
				'args' => array(
					'labels'        => array(
						'name'               => __( 'Testimonials', 'act' ),
						'singular_name'      => __( 'Testimonial', 'act' ),
						'add_new'            => __( 'Add New', 'act' ),
						'add_new_item'       => __( 'Add New Testimonial Item', 'act' ),
						'edit'               => __( 'Edit', 'act' ),
						'edit_item'          => __( 'Edit Testimonial Item', 'act' ),
						'new_item'           => __( 'New Item', 'act' ),
						'view'               => __( 'View Testimonial', 'act' ),
						'view_item'          => __( 'View Testimonial Item', 'act' ),
						'search_items'       => __( 'Search Testimonials', 'act' ),
						'not_found'          => __( 'No testimonial items found', 'act' ),
						'not_found_in_trash' => __( 'No testimonial items found in the trash', 'act' ),
					),
					'public'        => true,
					'query_var'     => true,
					'menu_position' => 20,
					'menu_icon'     => 'dashicons-testimonial',
					'has_archive'   => false,
					'supports'      => array( 'title', 'editor' ),
					'rewrite'       => array( 'with_front' => false ),
				),
			),
		);

		return apply_filters( 'arconix_testimonials_admin_defaults', $defaults );
	}

	/**
	 * Load our Meta Box and At a Glance helper classes.
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 */
	public function init() {
		if ( ! class_exists( 'Gamajo_Dashboard_Glancer' ) ) {
			require_once ACT_DIR . 'class-gamajo-dashboard-glancer.php';
		}
	}

	/**
	 * Register the post_type.
	 *
	 * @since 1.0.0
	 */
	public function content_types() {
		$defaults = $this->defaults();
		register_post_type( $defaults['post_type']['slug'], $defaults['post_type']['args'] );
	}

	/**
	 * Register plugin shortcode.
	 *
	 * @since 1.0.0
	 */
	public function shortcodes() {
		add_shortcode( 'ac-testimonials', array( $this, 'testimonials_shortcode' ) );
	}

	/**
	 * Testimonials shortcode.
	 *
	 * @since  1.0.0
	 *
	 * @param  array  $atts    Passed attributes.
	 * @param  string $content N/A - self-closing shortcode.
	 *
	 * @return string          result of query
	 */
	public function testimonials_shortcode( $atts, $content = null ) {
		$t = new Arconix_Testimonials();

		return $t->loop( $atts );
	}


	/**
	 * Filter The_Content and add our data to it
	 *
	 * @since   1.0.0
	 * @version 1.0.1
	 *
	 * @global  stdObj $post    std Post
	 * @param   string $content main content.
	 * @return  string          our testimonial content
	 */
	public function content_filter( $content ) {
		global $post;

		if ( is_single() && 'testimonial' === $post->post_type && is_main_query() ) {

			$t = new Arconix_Testimonials();

			// So we can grab our default gravatar size and allow it to be filtered.
			$defaults = $t->defaults();

			$gs = apply_filters( 'arconix_testimonials_content_gravatar_size', $defaults['gravatar']['size'] );

			$gravatar = '<div class="arconix-testimonial-gravatar">' . $t->get_gravatar( $gs ) . '</div>';

			$cite = '<div class="arconix-testimonial-info-wrap">' . $t->get_citation( false ) . '</div>';

			$content = '<div class="arconix-testimonial-content">' . $content . '</div>';

			$content = $cite . $gravatar . $content;

		}

		return $content;
	}


	/**
	 * Load required CSS.
	 *
	 * Load the plugin CSS. If the css file is present in the theme directory, it will be loaded instead,
	 * allowing for an easy way to override the default template. If you'd like to remove the CSS entirely,
	 * such as when building the styles into a single file, simply reference the filter and return false
	 *
	 * @example add_filter( 'pre_register_arconix_testimonials_css', '__return_false' );
	 *
	 * @since 1.0.0
	 */
	public function scripts() {
		// If the CSS is not being overridden in a theme folder, allow the user to filter it out entirely (if building into stylesheet or the like).
		if ( apply_filters( 'pre_register_arconix_testimonials_css', true ) ) {
			// Checks the child directory and then the parent directory.
			if ( file_exists( get_stylesheet_directory() . '/arconix-testimonials.css' ) ) {
				wp_enqueue_style( 'arconix-testimonials', get_stylesheet_directory_uri() . '/arconix-testimonials.css', false, ACT_VERSION );
			} elseif ( file_exists( get_template_directory() . '/arconix-testimonials.css' ) ) {
				wp_enqueue_style( 'arconix-testimonials', get_template_directory_uri() . '/arconix-testimonials.css', false, ACT_VERSION );
			} else {
				wp_enqueue_style( 'arconix-testimonials', ACT_CSS_URL . 'arconix-testimonials.css', false, ACT_VERSION );
			}
		}
	}

	/**
	 * Load the Amid-side CSS.
	 *
	 * Load the admin CSS. If you'd like to remove the CSS entirely, such as when building the styles
	 * into a single file, simply reference the filter and return false
	 *
	 * @example add_filter( 'pre_register_arconix_testimonials_admin_css', '__return_false' );
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		if ( apply_filters( 'pre_register_arconix_testimonials_admin_css', true ) ) {
			wp_enqueue_style( 'arconix-testimonials-admin', ACT_CSS_URL . 'admin.css', false, ACT_VERSION );
		}
	}

	/**
	 * Modifies the post save notifications to properly reflect the post-type
	 *
	 * @since  1.0.0
	 *
	 * @global stdObject $post
	 * @global int       $post_ID
	 *
	 * @param  array $messages Save notification messages.
	 *
	 * @return array $messages
	 */
	public function messages( $messages ) {
		global $post, $post_ID;

		$messages['testimonials'] = array(
			0  => '', // Unused. Messages start at index 1.
			/* translators: %s: Testimonial link */
			1  => sprintf( __( 'Testimonial updated. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			2  => __( 'Custom field updated.' ),
			3  => __( 'Custom field deleted.' ),
			4  => __( 'Testimonial updated.' ),
			/* translators: %s: date and time of the revision */
			5  => isset( $_GET['revision'] ) ? sprintf( __( 'Testimonial restored to revision from %s' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore
			/* translators: %s: Testimonial link */
			6  => sprintf( __( 'Testimonial published. <a href="%s">View testimonial</a>' ), esc_url( get_permalink( $post_ID ) ) ),
			7  => __( 'Testimonial saved.' ),
			/* translators: %s: Preview link */
			8  => sprintf( __( 'Testimonial submitted. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
			/* translators: %1$s: Scheduled date, %2$s: Preview link */
			9  => sprintf( __( 'Testimonial scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview testimonial</a>' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ) ),
			/* translators: %s: Preview link */
			10 => sprintf( __( 'Testimonial draft updated. <a target="_blank" href="%s">Preview testimonial</a>' ), esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
		);

		return $messages;
	}

	/**
	 * Choose the specific columns we want to display in the WP Admin Testimonials list.
	 *
	 * @since   1.0.0
	 * @version 1.1.0
	 *
	 * @param   array $columns Column names.
	 *
	 * @return  array $columns
	 */
	public function columns_filter( $columns ) {
		$col_gr = array( 'testimonial-gravatar' => __( 'Gravatar', 'act' ) );
		$col_ta = array( 'title' => __( 'Author', 'act' ) );
		$col_tb = array( 'testimonial-byline' => __( 'Byline', 'act' ) );
		$col_tc = array( 'testimonial-content' => __( 'Testimonial', 'act' ) );

		unset( $columns['title'] );

		$columns = array_slice( $columns, 0, 1, true ) + $col_gr + array_slice( $columns, 1, null, true );
		$columns = array_slice( $columns, 0, 2, true ) + $col_ta + array_slice( $columns, 2, null, true );
		$columns = array_slice( $columns, 0, 3, true ) + $col_tb + array_slice( $columns, 3, null, true );
		$columns = array_slice( $columns, 0, 4, true ) + $col_tc + array_slice( $columns, 4, null, true );

		return apply_filters( 'arconix_testimonials_admin_column_define', $columns );
	}

	/**
	 * Supply the data that shows up in the custom columns we defined.
	 *
	 * @since 1.0.0
	 *
	 * @param array $column Column name.
	 */
	public function column_action( $column ) {
		$t = new Arconix_Testimonials();

		switch ( $column ) {
			case 'testimonial-gravatar':
				$t->get_gravatar( 60, true );
				break;
			case 'testimonial-content':
				the_excerpt();
				break;
			case 'testimonial-byline':
				$t->get_citation( false, true, true );
			default:
				break;
		}
	}

	/**
	 * Customize the "Enter title here" text on the Testimonial creation screen
	 *
	 * @since  1.0.0
	 *
	 * @param  string $title Placeholder text.
	 *
	 * @return $title
	 */
	public function title_text( $title ) {
		$screen = get_current_screen();

		if ( 'testimonials' === $screen->post_type ) {
			$title = __( 'Enter author name here', 'act' );
		}

		return $title;
	}

	/**
	 * Add the Post type to the "At a Glance" Dashboard Widget.
	 *
	 * @since 1.0.0
	 */
	public function at_a_glance() {
		$glancer = new Gamajo_Dashboard_Glancer();
		$glancer->add( 'testimonials' );
	}

	/**
	 * Adds a dashboard widget.
	 *
	 * Adds a widget to the dashboard. Can be overridden completely by a filter, but only shows for users that can
	 * manage options (also filterable if desired)
	 *
	 * @since 1.0.0
	 */
	public function dash_widget() {
		if ( apply_filters( 'pre_register_arconix_testimonials_dashboard_widget', true ) &&
			apply_filters( 'arconix_testimonial_dashboard_widget_security', current_user_can( 'manage_options' ) ) ) {
				wp_add_dashboard_widget( 'ac-testimonials', 'Arconix Testimonials', array( $this, 'dash_widget_output' ) );
		}
	}

	/**
	 * Output for the dashboard widget.
	 *
	 * @since 1.0.0
	 */
	public function dash_widget_output() {
		echo '<div class="rss-widget">';

			wp_widget_rss_output(
				array(
					'url'          => 'http://arconixpc.com/tag/arconix-testimonials/feed', // feed url.
					'title'        => 'Arconix Testimonials Posts', // feed title.
					'items'        => 3, // how many posts to show.
					'show_summary' => 1, // display excerpt.
					'show_author'  => 0, // display author.
					'show_date'    => 1, // display post date.
				)
			);

			echo '<div class="act-widget-bottom"><ul>';
		?>
				<li><a href="http://arcnx.co/atwiki" class="atdocs"><img src="<?php echo ACT_IMAGES_URL . 'page-16x16.png'; ?>">Documentation</a></li>
				<li><a href="http://arcnx.co/athelp" class="athelp"><img src="<?php echo ACT_IMAGES_URL . 'help-16x16.png'; ?>">Support Forum</a></li>
				<li><a href="http://arcnx.co/attrello" class="atdev"><img src="<?php echo ACT_IMAGES_URL . 'trello-16x16.png'; ?>">Dev Board</a></li>
				<li><a href="http://arcnx.co/atsource" class="atsource"><img src="<?php echo ACT_IMAGES_URL . 'github-16x16.png'; ?>">Source Code</a></li>
			<?php
			echo '</ul></div>';
			echo '</div>';
	}

	/**
	 * Create the post type metabox.
	 *
	 * @since  1.0.0
	 */
	public function act_register_metaboxes() {
		/**
		 * Sample metabox to demonstrate each field type included
		 */
		$act_metabox = new_cmb2_box(
			array(
				'id'           => 'testimonials-info',
				'title'        => esc_html__( 'Testimonial Details', 'act' ),
				'object_types' => array( 'testimonials' ), // Post type.
			)
		);

		$act_metabox->add_field(
			array(
				'name' => esc_html__( 'E-mail Address', 'act' ),
				/* translators: %1$s: link to gravatar */
				'desc' => sprintf( __( 'To display the author\'s %1$sGravatar%2$s (optional).', 'act' ), '<a href="' . esc_url( 'http://gravatar.com' ) . '" target="_blank">', '</a>' ),
				'id'   => '_act_email',
				'type' => 'text_email',
			)
		);

		$act_metabox->add_field(
			array(
				'name' => esc_html__( 'Byline', 'act' ),
				'desc' => esc_html__( 'Enter a byline for the author of this testimonial (optional).', 'act' ),
				'id'   => '_act_byline',
				'type' => 'text',
			)
		);

		$act_metabox->add_field(
			array(
				'name' => esc_html__( 'Website', 'act' ),
				'desc' => esc_html__( 'Enter a URL for the individual or organization (optional).', 'act' ),
				'id'   => '_act_url',
				'type' => 'text_url',
			)
		);

	}

	/**
	 * Adds another metabox to the testimonial creation screen.
	 *
	 * This metabox shows the shortcode with the post_id for users to display
	 * just that testimonial on a post, page or other applicable location
	 *
	 * @since 1.1.0
	 */
	public function shortcode_metabox() {
		add_meta_box( 'ac-shortcode-box', __( 'Testimonial Shortcode', 'act' ), array( $this, 'shortcode_box' ), 'testimonials', 'side' );
	}

	/**
	 * Output for the testimonial shortcode metabox. Creates a readonly inputbox that outputs the testimonial shortcode
	 * plus the $post_id
	 *
	 * @since 1.1.0
	 *
	 * @global int $post_ID ID of the current post
	 */
	public function shortcode_box() {
		global $post_ID;
		?>
		<p class="howto">
			<?php _e( 'To display this testimonial, copy the code below and paste it into your post, page, text widget or other content area.', 'act' ); ?>
		</p>
		<p><input type="text" value="[ac-testimonials p=<?php echo $post_ID; ?>]" readonly="readonly" class="widefat wp-ui-text-highlight code"></p>
		<?php
	}

}

<?php
/**
 * Export Arconix textimonial data in
 * Dashboard->Tools->Export Personal Data
 *
 * @since 1.3
 * @package arconix-testimonials/privacy
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Arconix_Testimonials_Privacy_Export' ) ) {

	/**
	 * Export Arconix textimonial data in
	 * Dashboard->Tools->Export Personal Data
	 */
	class Arconix_Testimonials_Privacy_Export {

		/**
		 * Construct
		 *
		 * @since 1.3
		 */
		public function __construct() {
			// Hook into the WP export process.
			add_filter( 'wp_privacy_personal_data_exporters', array( &$this, 'act_exporter_array' ), 6 );
		}

		/**
		 * Add our export and it's callback function
		 *
		 * @param array $exporters - Any exportes that need to be added by 3rd party plugins.
		 *
		 * @since 1.3
		 */
		public static function act_exporter_array( $exporters = array() ) {

			$exporter_list = array();
			// Add our export and it's callback function.
			$exporter_list['arconix_testimonial'] = array(
				'exporter_friendly_name' => __( 'Arconix Testimonial', 'act' ),
				'callback'               => array( 'Arconix_Testimonials_Privacy_Export', 'act_data_exporter' ),
			);

			$exporters = array_merge( $exporters, $exporter_list );

			return $exporters;

		}

		/**
		 * Returns data to be displayed for exporting the
		 * cart details
		 *
		 * @param string  $email_address - Email Address for which personal data is being exported.
		 * @param integer $page - The Export page number.
		 * @return array $data_to_export - Data to be exported.
		 *
		 * @hook wp_privacy_personal_data_exporters
		 * @global $wpdb
		 * @since 1.3
		 */
		public static function act_data_exporter( $email_address, $page ) {

			global $wpdb;

			$done           = false;
			$page           = (int) $page;
			$data_to_export = array();

			$testimonail_query   = 'SELECT * FROM `' . $wpdb->prefix . 'postmeta`
                            WHERE meta_value = %s AND meta_key = %s ';
			$testimonial_details = $wpdb->get_results( $wpdb->prepare( $testimonail_query, $email_address, '_act_email' ) );
			$custom_data         = array();
			$done                = true;
			if ( count( $testimonial_details ) > 0 ) {
				foreach ( $testimonial_details as $testimonial_details_key => $testimonial_details_value ) {
					if ( isset( $testimonial_details_value->post_id ) && '' !== $testimonial_details_value->post_id ) {
						$testi_custom_data = get_post_custom( $testimonial_details_value->post_id );
						$post_data         = get_post( $testimonial_details_value->post_id, ARRAY_A );
						$custom_data []    = array_merge( $testi_custom_data, $post_data );
					}
				}
			}
			foreach ( $custom_data as $custom_data_key ) {
				$data_to_export[] = array(
					'group_id'    => 'arconix_testimonial',
					'group_label' => __( 'Arconix Testimonial', 'act' ),
					'item_id'     => 'testimonial-' . $email_address,
					'data'        => self::get_arconix_testimonial_data( $email_address, $custom_data_key ),
				);
			}
			return array(
				'data' => $data_to_export,
				'done' => $done,
			);

		}

		/**
		 * Returns the personal data for each Testimonial
		 *
		 * @param integer $email_address - Email address.
		 * @param array   $custom_data Post details.
		 * @return array $personal_data - Personal data to be displayed
		 * @global $wpdb
		 * @since 1.3
		 */
		public static function get_arconix_testimonial_data( $email_address, $custom_data ) {
			$personal_data = array();

			global $wpdb;
			$act_details_to_export = apply_filters(
				'act_personal_export_testimonial_details_prop',
				array(
					'act_email'   => __( 'Email Address', 'act' ),
					'act_byline'  => __( 'Byline', 'act' ),
					'act_website' => __( 'Website', 'act' ),
					'act_title'   => __( 'Title', 'act' ),
					'act_content' => __( 'Content', 'act' ),
				),
				$email_address
			);

			foreach ( $act_details_to_export as $prop => $name ) {

				switch ( $prop ) {
					case 'act_email':
						$value = $email_address;
						break;
					case 'act_byline':
						$value = $custom_data['_act_byline'][0];
						break;
					case 'act_website':
						$value = $custom_data['_act_url'][0];
						break;
					case 'act_title':
						$value = $custom_data['post_title'];
						break;
					case 'act_content':
						$value = $custom_data['post_content'];

						break;
					default:
						$value = ( isset( $custom_data->$prop ) ) ? $custom_data->$prop : '';
						break;
				}

				$value = apply_filters( 'act_personal_export_cart_details_prop_value', $value, $prop, $custom_data );

				$personal_data[] = array(
					'name'  => $name,
					'value' => $value,
				);

			}
			$personal_data = apply_filters( 'act_personal_data_cart_details_export', $personal_data, $custom_data );

			return $personal_data;
		}
	} // end of class
	$arconix_testimonials_privacy_export = new Arconix_Testimonials_Privacy_Export();
} // end if


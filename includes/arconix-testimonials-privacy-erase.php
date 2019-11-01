<?php
/**
 * Erase testimonial data in
 * Dashboard->Tools->Erase Personal Data
 *
 * @since 1.3
 * @package arconix-testimonials/privacy
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Arconix_Testimonials_Privacy_Erase' ) ) {

	/**
	 * Erase testimonial data in
	 * Dashboard->Tools->Erase Personal Data
	 */
	class Arconix_Testimonials_Privacy_Erase {

		/**
		 * Construct
		 *
		 * @since 1.3
		 */
		public function __construct() {
			// Hook into the WP erase process.
			add_filter( 'wp_privacy_personal_data_erasers', array( &$this, 'act_eraser_array' ), 6 );
		}

		/**
		 * Add our eraser and it's callback function
		 *
		 * @param array $erasers - Any erasers that need to be added by 3rd party plugins.
		 *
		 * @since 7.8
		 */
		public static function act_eraser_array( $erasers = array() ) {

			$eraser_list = array();
			// Add our eraser and it's callback function.
			$eraser_list['arconix_testimonial'] = array(
				'eraser_friendly_name' => __( 'Arconix Testimonial', 'act' ),
				'callback'             => array( 'Arconix_Testimonials_Privacy_Erase', 'act_data_eraser' ),
			);

			$erasers = array_merge( $erasers, $eraser_list );

			return $erasers;

		}

		/**
		 * Erases personal data for Testimonial carts.
		 *
		 * @param string  $email_address - EMail Address for which personal data is being exported.
		 * @param integer $page - The Eraser page number.
		 * @return array $reponse - Whether the process was successful or no
		 *
		 * @hook wp_privacy_personal_data_erasers
		 * @global $wpdb
		 * @since 7.8
		 */
		public static function act_data_eraser( $email_address, $page ) {

			global $wpdb;

			$page = (int) $page;
			$user = get_user_by( 'email', $email_address ); // Check if user has an ID in the DB to load stored personal data.

			$response = array(
				'items_removed'  => false,
				'items_retained' => false,
				'messages'       => array(),
				'done'           => true,
			);

			$testimonail_query   = 'SELECT * FROM `' . $wpdb->prefix . 'postmeta`
                            WHERE meta_value = %s AND meta_key = %s ';
			$testimonial_details = $wpdb->get_results( $wpdb->prepare( $testimonail_query, $email_address, '_act_email' ) );
			$custom_data         = array();

			if ( count( $testimonial_details ) > 0 ) {
				foreach ( $testimonial_details as $testimonial_details_key => $testimonial_details_value ) {
					if ( isset( $testimonial_details_value->post_id ) && '' !== $testimonial_details_value->post_id ) {
						$testi_custom_data = get_post_custom( $testimonial_details_value->post_id );
						$post_data         = get_post( $testimonial_details_value->post_id, ARRAY_A );
						$custom_data []    = array_merge( $testi_custom_data, $post_data );
					}
				}

				foreach ( $custom_data as $custom_data_key ) {

					self::remove_testimonial_personal_data( $email_address, $custom_data_key );
					/* translators: %s: Email address */
					$response['messages'][]    = sprintf( __( 'Removed personal data from testimonial written by %s.', 'act' ), $email_address );
					$response['items_removed'] = true;
				}
			}
			return $response;

		}

		/**
		 * Erases the personal data for each testimonial data
		 *
		 * @param string $email_address - Email address.
		 * @param array  $custom_data_key - Testimonial information.
		 * @global $wpdb
		 * @since 7.8
		 */
		public static function remove_testimonial_personal_data( $email_address, $custom_data_key ) {
			global $wpdb;

			$anonymized_cart        = array();
			$anonymized_testimonial = array();

			$post_id = $custom_data_key['ID'];
			do_action( 'act_privacy_before_remove_personal_data', $email_address );

			// list the props we'll be anonymizing for guest cart history table.
			$props_to_remove_testimonial = apply_filters(
				'act_privacy_remove_personal_data_props',
				array(
					'_act_url'   => 'text',
					'_act_email' => 'email',
				),
				$email_address
			);

			if ( ! empty( $props_to_remove_testimonial ) && is_array( $props_to_remove_testimonial ) ) {

				foreach ( $props_to_remove_testimonial as $prop => $data_type ) {

					$value = $custom_data_key[ $prop ];

					if ( empty( $value ) || empty( $data_type ) ) {
						continue;
					}

					if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
						if ( isset( $value[0] ) && '' !== $value [0] ) {
							$anon_value = wp_privacy_anonymize_data( $data_type, $value[0] );
						} else {
							$anon_value = wp_privacy_anonymize_data( $data_type, $value );
						}
					} else {
						$anon_value = '';
					}

					$set_prop_value = apply_filters( 'act_privacy_remove_testimonial_personal_data_prop_value', $anon_value, $prop, $value, $data_type, $email_address );

					$act_update                     = 'UPDATE `' . $wpdb->prefix . 'postmeta` SET `meta_value` = %s WHERE `post_id` = %d AND `meta_key`= %s';
					$act_update_testimonial_details = $wpdb->query( $wpdb->prepare( $act_update, $set_prop_value, $post_id, $prop ) );
				}
			}

		}

	} // end of class.
	$arconix_testimonials_privacy_erase = new Arconix_Testimonials_Privacy_Erase();
} // end if.


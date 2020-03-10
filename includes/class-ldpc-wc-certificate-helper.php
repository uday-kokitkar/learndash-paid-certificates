<?php
/**
 * LearnDash Paid Certificates.
 *
 * @package LDPC
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce product and LD certificate helper.
 *
 * @since 1.0.0
 */
class LDPC_Wc_Certificate_Helper {

	/**
	 * __constructor.
	 */
	public function __construct() {
		// Nothing here.
	} // fn __construct


	/**
	 * Returns a product ID mapped to the course certificate.
	 *
	 * @static
	 * @param  integer $course_id Course ID.
	 * @return integer            A WC product ID.
	 */
	public static function get_certificate_product_id( $course_id ) {

		$learndash_post_settings = (array) learndash_get_setting( $course_id, null );

		$product_id = 0;

		if ( isset( $learndash_post_settings['ldpc_certi_product'] ) ) {
			if ( ! empty( $learndash_post_settings['ldpc_certi_product'] ) ) {
				$product_id = $learndash_post_settings['ldpc_certi_product'];
			}
		}

		return $product_id;
	}

	/**
	 * A function to check product purchase status.
	 *
	 * @static
	 * @param  integer $product_id Certificate product ID.
	 * @param  integer $user_id    User ID.
	 * @return boolean             True if purchased.
	 */
	public static function is_product_purchased( $product_id, $user_id ) {
		if ( empty( $product_id ) || empty( $user_id ) ) {
			return false;
		}

		$user_info = get_userdata( $user_id );
		$customer_email = $user_info->user_email;

		return wc_customer_bought_product( $customer_email, $user_id, $product_id );
	}

	/**
	 * Returns a product ID if we want user to purchase the product.
	 *
	 * @static
	 * @param  integer $course_id    Course ID.
	 * @param  integer $user_id      User ID.
	 * @return integer Product ID.
	 */
	public static function should_be_changed( $course_id, $user_id = 0 ) {

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		$product_id = self::get_certificate_product_id( $course_id );

		if ( $product_id ) {
			$is_purchased = self::is_product_purchased( $product_id, $user_id );

			if ( ! $is_purchased ) {
				return $product_id;
			}
		}

		return 0;
	}

} // class LDPC_Public_Certificate_Purchase_Link



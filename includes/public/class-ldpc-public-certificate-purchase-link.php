<?php
/**
 * LearnDash Paid Certificates.
 *
 * @package LDPC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Links certificate button to product URL if the product is not purchased.
 *
 * @since 1.0.0
 */
class LDPC_Public_Certificate_Purchase_Link {

	/**
	 * Class static instance
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initiate class.
	 */
	public function __construct() {
		add_filter( 'ld_certificate_link_label', array( $this, 'change_ld_certificate_link_label' ), 10, 3 );
		add_filter( 'learndash_course_certificate_link', array( $this, 'change_learndash_course_certificate_link' ), 10, 3 );

	} // fn __construct

	/**
	 * Instantiates class if not initialized.
	 *
	 * @return Object
	 */
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	} // fn get_instance

	/**
	 * Changes 'Print Your Certificate' text.
	 *
	 * @param  string  $text         Default button text.
	 * @param  integer $user_id      Current User.
	 * @param  integer $course_id    Course ID.
	 * @return string                Modified button text.
	 */
	public function change_ld_certificate_link_label( $text, $user_id, $course_id ) {

		if ( LDPC_Wc_Certificate_Helper::should_be_changed( $course_id, $user_id ) ) {
			$text = apply_filters( 'ldpc_purchase_button_text', __( 'Purchase Certificate', 'learndash-paid-certificates' ), $text, $course_id, $user_id );
		}
		return $text;
	}

	/**
	 * Changes LearnDash course certificate link if the respective product is not purchased.
	 *
	 * @param  string  $url          Default certificate URL.
	 * @param  integer $course_id    Course ID.
	 * @param  integer $cert_user_id User ID.
	 * @return string                A product URL or a certificate URL.
	 */
	public function change_learndash_course_certificate_link( $url, $course_id, $cert_user_id ) {

		$product_id = LDPC_Wc_Certificate_Helper::should_be_changed( $course_id, $cert_user_id );
		if ( $product_id && apply_filters( 'ldpc_certificate_link_check', true, $product_id, $url, $course_id, $cert_user_id ) ) {
			$url = get_permalink( $product_id );
		}

		return $url;
	}
} // class LDPC_Public_Certificate_Purchase_Link

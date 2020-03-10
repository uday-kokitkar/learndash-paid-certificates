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
class LDPC_Public_Ld30_Certificate {

	/**
	 * Class static instance
	 *
	 * @var object
	 */
	protected static $instance = null;


	/**
	 * Value 'true' if the current alert template is for download certificate. This variable is instroduced because there is no value to differentiate which alert message is called.
	 *
	 * @static
	 * @var boolean
	 */
	protected static $is_certificate_alert = false;

	/**
	 * Initiate class.
	 */
	public function __construct() {

		add_filter( 'ld-alert-class', array( $this, 'ld_alert_class_certificate' ) );
		add_filter( 'ld-alert-button', array( $this, 'ld_alert_button_certificate' ) );

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
	 * Checks if current alert is for download certificate.
	 *
	 * @param  string $class Multiple classes space separated.
	 * @return string        Multiple classes space separated.
	 */
	public function ld_alert_class_certificate( $class ) {

		if ( strpos( $class, 'ld-alert-certificate' ) !== false ) {
			self::$is_certificate_alert = true;
			apply_filters( 'ldpc_download_certificate_class', $class );
		}

		return $class;
	}

	/**
	 * Changes certificate button text and URL, conditionally.
	 *
	 * @since  1.0.0
	 * @param  array $button Button contains url, label, etc values.
	 * @return array         Array of button values.
	 */
	public function ld_alert_button_certificate( $button ) {

		// This is to validate we are changing URL of 'ld certificate' only and not of other alerts.
		if ( self::$is_certificate_alert ) {

			global $post;

			if ( ! empty( $post ) && 'sfwd-courses' == $post->post_type ) {

				$product_id = LDPC_Wc_Certificate_Helper::should_be_changed( $post->ID, get_current_user_id() );

				if ( $product_id && apply_filters( 'ldpc_certificate_link_check_ld30', true, $product_id, $post->ID, get_current_user_id() ) ) {

					$button['label'] = apply_filters( 'ldpc_purchase_button_text_ld30', __( 'Purchase Certificate', 'learndash-paid-certificates' ), $post->ID, get_current_user_id() );
					$button['url'] = get_permalink( $product_id );

				}
			}
			self::$is_certificate_alert = false;
		}

		return $button;
	}

} // class LDPC_Public_Certificate_Purchase_Link

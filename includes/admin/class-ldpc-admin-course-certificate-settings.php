<?php
/**
 * LearnDash Paid Certificates.
 *
 * @package LDPC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Course level settings to assign products.
 *
 * @since 1.0.0
 */
class LDPC_Admin_Course_Certificate_Settings {

	/**
	 * Class static instance
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * LD's course settings meta-key.
	 *
	 * @var string
	 */
	public static $settings_metabox_key = 'learndash-course-display-content-settings';

	/**
	 * Initiate class.
	 */
	private function __construct() {
		add_filter( 'learndash_settings_fields', array( $this, 'learndash_settings_fields_cb' ), 10, 2 );
		add_filter( 'learndash_settings_save_values', array( $this, 'learndash_settings_save_values_cb' ), 10, 2 );

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
	 * Adds setting fields to edit course page.
	 *
	 * @param  array  $fields       All the setting fields.
	 * @param  string $metabox_key  Metabox key.
	 * @return array                Modified setting fields.
	 */
	public function learndash_settings_fields_cb( $fields, $metabox_key ) {

		if ( self::$settings_metabox_key === $metabox_key ) {

			$modified_fields = array();

			global $post;

			$learndash_post_settings = (array) learndash_get_setting( $post, null );

			// A product must be purchased to access the certificate. That is, 'certi product'.
			$cp_value                   = '';
			if ( isset( $learndash_post_settings['ldpc_certi_product'] ) ) {
				if ( ! empty( $learndash_post_settings['ldpc_certi_product'] ) ) {
					$cp_value = $learndash_post_settings['ldpc_certi_product'];
				}
			}

			// Retrieve WC products. We are retrieving all the products in one go because I assume that e-learning sites do not have many products that will impact on site loading speed.
			$args = array(
				'status'    => array( 'private', 'publish' ),
				'orderby'   => 'name',
				'return'    => 'ids',
				'limit'     => 50, // Limit to 50, if the site has more than that.
			);

			$product_ids = wc_get_products( apply_filters( 'ldpc_admin_get_wc_products', $args, $post ) );

			$products = array();

			if ( ! empty( $product_ids ) ) {
				$products[0] = __( 'Select the product', 'learndash-paid-certificates' );
				foreach ( $product_ids as $prod_id ) {
					$products[ $prod_id ] = get_the_title( $prod_id );
				}
			} else {
				// We load only private and published products. Please refer 'wc_get_products' call above.
				$products[0] = __( 'No private or published product found.', 'learndash-paid-certificates' );
			}

			// We are looping through because we want to show our setting immediately after 'certificate' setting. Otherwise, LD shows in the sequence it is added in the array.
			foreach ( $fields as $key => $value ) {

				if ( 'certificate' === $key ) {

					if ( empty( $_GET['post'] ) ) {
						return $fields;
					}

					// By default, show child elements by setting it to 'open'.
					$fields['certificate']['child_section_state'] = 'open';

					$modified_fields['certificate'] = $fields['certificate'];

					// New child setting for certificate. This is a WC product to directly map product to certificate.
					$modified_fields['ldpc_certi_product'] = array(
						'name' => 'ldpc_certi_product',
						'type' => 'select',
						'class' => 'small-text',
						'label' => 'Certificate Product',
						'input_label' => '<p>' . __( 'This product needs to be purchased to access the certificate.', 'learndash-paid-certificates' ) . '</p>',
						'value' => $cp_value,
						'default' => '',
						'options' => $products,
						'parent_setting' => 'certificate',
					);
					continue;
				}
				$modified_fields[ $key ] = $value;
			}
		} else {
			$modified_fields = $fields;
		}

		return $modified_fields;
	} // fn learndash_settings_fields_cb

	/**
	 * A callback function to save setting field values for Course edit page.
	 *
	 * @param  array  $values       Input fields and values by LearnDash.
	 * @param  string $metabox_key  Metabox key.
	 * @return array                Modified input fields and values.
	 */
	public function learndash_settings_save_values_cb( $values, $metabox_key ) {

		global $post;

		if ( self::$settings_metabox_key === $metabox_key ) {
			// Update the post's metadata. Nonce already verified by LearnDash.
			// Certi Product.
			if (
				isset( $_POST[ self::$settings_metabox_key ] ) &&
				isset( $_POST[ self::$settings_metabox_key ]['ldpc_certi_product'] )
			) {
				$certi_product_id = (int) sanitize_text_field( $_POST[ self::$settings_metabox_key ]['ldpc_certi_product'] );
				learndash_update_setting( $post, 'ldpc_certi_product', $certi_product_id );
			}
		}

		return $values;
	} // fn learndash_settings_save_values_cb

} // class LDPC_Admin_Course_Certificate_Settings

<?php
/**
 * Paid Certificates
 *
 * @package LDPC
 */

defined( 'ABSPATH' ) || exit;

/**
 * Main LDPC Class.
 *
 * @class Learndash_Paid_Certificates
 */
final class Learndash_Paid_Certificates {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Plugin's name in the dashboard.
	 *
	 * @var string
	 */
	public $plugin_name = 'LearnDash Paid Certificates';

	/**
	 * The single instance of the class.
	 *
	 * @var LDPC
	 */
	protected static $_instance = null;

	/**
	 * Main LDPC Instance.
	 *
	 * Ensures only one instance of LDPC is loaded or can be loaded.
	 *
	 * @static
	 * @see LDPC()
	 * @return LDPC - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cloning is forbidden.', 'learndash-paid-certificates' ) ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Unserializing instances of this class is forbidden.', 'learndash-paid-certificates' ) ), '1.0.0' );
	}

	/**
	 * LDPC Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * When WP has loaded all plugins, trigger the `ldpc_loaded` hook.
	 *
	 * This ensures `ldpc_loaded` is called only after all other plugins
	 * are loaded.
	 */
	public function on_plugins_loaded() {
		do_action( 'ldpc_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0.0
	 */
	private function init_hooks() {

		add_action( 'plugins_loaded', array( $this, 'on_plugins_loaded' ), -1 );
		add_action( 'admin_notices', array( $this, 'build_dependencies_notice' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

	}

	/**
	 * Show admin notices for dependencies.
	 *
	 * @return void.
	 */
	public function build_dependencies_notice() {

		if ( ! version_compare( PHP_VERSION, LDPC_NOTICE_MIN_PHP_VERSION, '>=' ) ) {
			// Show notice if php version is less than required.
			$current = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION;
			?>
			<div class="notice notice-error">
				<h3>
				<?php
				printf(
					__(
						'The <strong>%1$s</strong> requires PHP version %2$s or higher. Because you are using an unsupported version of PHP (%3$s), the <strong>%4$s</strong> plugin will not initialize. Please contact your hosting company to upgrade to PHP.'
					),
					$this->plugin_name,
					LDPC_NOTICE_MIN_PHP_VERSION,
					$current,
					$this->plugin_name
				);
				?>
				</h3>
			</div>
			<?php
		} else if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			// Show notice if LearnDash is not active.
			?>
			<div class="notice notice-error">
				<p>
				<?php
				printf(
					__(
						'The <strong>%1$s</strong> requires LearnDash LMS plugin to be activated !'
					),
					$this->plugin_name
				);
				?>
				</p>
			</div>
			<?php

		} else if ( ! version_compare( LEARNDASH_VERSION, LDPC_NOTICE_MIN_LD_VERSION, '>=' ) ) {
			// Show notice if LD's version is less than required.
			?>
			<div class="notice notice-error">
				<p>
				<?php
				printf(
					__(
						'The <strong>%1$s</strong> requires LearnDash v%2$s or greater!'
					),
					$this->plugin_name,
					LDPC_NOTICE_MIN_LD_VERSION
				);
				?>
				</p>
			</div>
			<?php
		} else if ( ! defined( 'WC_PLUGIN_FILE' ) ) {
			// Show notice if WooCommerce is not active.
			?>
			<div class="notice notice-error">
				<p>
				<?php
				$install_wc_url = admin_url( 'plugin-install.php?s=woocommerce&tab=search' );
				printf(
					__(
						'The <strong>%1$s</strong> requires WooCommerce to be activated ! <a href="%2$s">Install / Activate WooCommerce</a>'
					),
					$this->plugin_name,
					$install_wc_url
				);
				?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Define Constants.
	 */
	private function define_constants() {

		$this->define( 'LDPC_ABSPATH', dirname( LDPC_PLUGIN_FILE ) . '/' );
		$this->define( 'LDPC_PLUGIN_BASENAME', plugin_basename( LDPC_PLUGIN_FILE ) );
		$this->define( 'LDPC_VERSION', $this->version );
		$this->define( 'LDPC_NOTICE_MIN_PHP_VERSION', '5.6.20' );
		$this->define( 'LDPC_NOTICE_MIN_WP_VERSION', '5.0' );
		$this->define( 'LDPC_NOTICE_MIN_LD_VERSION', '3.0' );
		$this->define( 'LDPC_PLUGIN_URL', plugin_dir_url( LDPC_PLUGIN_FILE ) );
		$this->define( 'LDPC_LANG_DIR', LDPC_ABSPATH . '/languages/' );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @param string      $name  Constant name.
	 * @param string|bool $value Constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 * @return bool
	 */
	private function is_request( $type ) {

		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
			// case 'frontend_only':
			// return ( ! is_admin() && ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_CRON' )  && ! $this->is_rest_api_request() );
			// case 'frontend_and_ajax':
			// return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! $this->is_rest_api_request();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		/**
		 * Class autoloader.
		 */
		include_once LDPC_ABSPATH . 'includes/class-ldpc-autoloader.php';
	}

	/**
	 * Initiate classes required for dashboard.
	 */
	public function admin_init() {
		LDPC_Admin_Course_Certificate_Settings::get_instance();
	}

	/**
	 * Initiate classes required for frontend and ajax.
	 */
	public function frontend_init() {
		LDPC_Public_Certificate_Purchase_Link::get_instance();
		LDPC_Public_Ld30_Certificate::get_instance();
	}

	/**
	 * Init LDPC when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_ldpc_init' );

		// Set up localisation.
		$this->load_plugin_textdomain();

		// We are dependent on LearnDash and WooCommerce. If either of them is not active, do not run our code.
		if ( ! defined( 'WC_PLUGIN_FILE' ) || ! defined( 'LEARNDASH_VERSION' ) ) {
			return;
		}

		// Classes/actions loaded for the dashboard only.
		if ( $this->is_request( 'admin' ) ) {
			$this->admin_init();
		}

		// Classes/actions loaded for the frontend and for ajax requests.
		if ( $this->is_request( 'frontend' ) ) {
			$this->frontend_init();
		}

		// Init action.
		do_action( 'ldpc_init' );
	}

	/**
	 * Load Localisation files.
	 * Locales found in:
	 *      - LDPC_LANG_DIR/learndash-paid-certificates-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'learndash-paid-certificates', false, plugin_basename( dirname( LDPC_PLUGIN_FILE ) ) . '/languages' );
	}

	/**
	 * Returns true if the request is a REST API request.
	 *
	 * @return bool
	 */
	public function is_rest_api_request() {

		// Considering this discussion, using constant here. We are also keeping further logic to check REST request in case REST_REQUEST is not defined.
		// https://github.com/WP-API/WP-API/issues/926
		// if ( ! defined( 'REST_REQUEST' ) ) {
		// return false;
		// }

		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$r_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $r_prefix ) );

		return $is_rest_api_request;
	}
}

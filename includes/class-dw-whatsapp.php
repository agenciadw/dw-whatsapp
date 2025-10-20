<?php
/**
 * Main plugin class
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp
	 */
	private static $instance = null;

	/**
	 * Plugin settings
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->load_dependencies();
		$this->init_hooks();
	}

	/**
	 * Load dependencies
	 */
	private function load_dependencies() {
		require_once DW_WHATSAPP_PATH . 'includes/class-dw-whatsapp-settings.php';
		require_once DW_WHATSAPP_PATH . 'includes/class-dw-whatsapp-frontend.php';
		require_once DW_WHATSAPP_PATH . 'admin/class-dw-whatsapp-admin.php';
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		add_action( 'plugins_loaded', array( $this, 'check_requirements' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'before_woocommerce_init', array( $this, 'declare_hpos_compatibility' ) );

		if ( $this->is_woocommerce_active() ) {
			DW_WhatsApp_Settings::instance();
			DW_WhatsApp_Frontend::instance();
			
			if ( is_admin() ) {
				DW_WhatsApp_Admin::instance();
			}
		}
	}

	/**
	 * Check requirements
	 */
	public function check_requirements() {
		if ( ! $this->is_woocommerce_active() ) {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * WooCommerce missing notice
	 */
	public function woocommerce_missing_notice() {
		?>
		<div class="notice notice-error">
			<p><strong>DW WhatsApp para WooCommerce</strong> requer o WooCommerce para funcionar. Por favor, instale e ative o WooCommerce.</p>
		</div>
		<?php
	}

	/**
	 * Load text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'dw-whatsapp', false, dirname( DW_WHATSAPP_BASENAME ) . '/languages' );
	}

	/**
	 * Declare HPOS compatibility
	 */
	public function declare_hpos_compatibility() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', DW_WHATSAPP_FILE, true );
		}
	}
}


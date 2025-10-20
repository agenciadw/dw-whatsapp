<?php
/**
 * Admin panel
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Admin {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp_Admin
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp_Admin
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			'DW WhatsApp',
			'DW WhatsApp',
			'manage_options',
			'dw-whatsapp',
			array( $this, 'render_settings_page' ),
			'dashicons-whatsapp',
			56
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		register_setting(
			'dw_whatsapp_settings_group',
			'dw_whatsapp_settings',
			array( 'DW_WhatsApp_Settings', 'update' )
		);
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_dw-whatsapp' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_add_inline_script( 'wp-color-picker', '
			jQuery(document).ready(function($) {
				$(".dw-color-picker").wpColorPicker();
			});
		' );
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'dw-whatsapp' ) );
		}

		if ( isset( $_POST['dw_whatsapp_settings_submit'] ) ) {
			check_admin_referer( 'dw_whatsapp_settings_action', 'dw_whatsapp_settings_nonce' );
			DW_WhatsApp_Settings::update( $_POST['dw_whatsapp_settings'] );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configurações salvas com sucesso!', 'dw-whatsapp' ) . '</p></div>';
		}

		require_once DW_WHATSAPP_PATH . 'admin/views/settings-page.php';
	}
}


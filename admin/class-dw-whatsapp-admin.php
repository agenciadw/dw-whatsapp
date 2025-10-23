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
		// Não registrar callback para evitar loop infinito
		// O salvamento é feito manualmente no render_settings_page
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
			
			// Verificar se os dados foram enviados
			if ( isset( $_POST['dw_whatsapp_settings'] ) && is_array( $_POST['dw_whatsapp_settings'] ) ) {
				// Limitar o número de usuários para evitar problemas de performance
				if ( isset( $_POST['dw_whatsapp_settings']['multi_users'] ) && is_array( $_POST['dw_whatsapp_settings']['multi_users'] ) ) {
					// Limitar a 10 usuários para evitar problemas
					$_POST['dw_whatsapp_settings']['multi_users'] = array_slice( $_POST['dw_whatsapp_settings']['multi_users'], 0, 10 );
				}
				
				// Tentar salvar com tratamento de erro
				try {
					$result = DW_WhatsApp_Settings::update( $_POST['dw_whatsapp_settings'] );
					
					if ( $result ) {
						echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configurações salvas com sucesso!', 'dw-whatsapp' ) . '</p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erro ao salvar configurações. Tente novamente.', 'dw-whatsapp' ) . '</p></div>';
					}
				} catch ( Exception $e ) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erro crítico: ', 'dw-whatsapp' ) . esc_html( $e->getMessage() ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Dados inválidos recebidos.', 'dw-whatsapp' ) . '</p></div>';
			}
		}

		require_once DW_WHATSAPP_PATH . 'admin/views/settings-page.php';
	}
}



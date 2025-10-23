<?php
/**
 * Plugin Name: DW WhatsApp para WooCommerce
 * Plugin URI: https://github.com/agenciadw/dw-whatsapp
 * Description: Adiciona botões de WhatsApp ao WooCommerce com múltiplos atendentes, horário automático, atribuição por produto e interface drag & drop.
 * Version: 0.2.0
 * Author: David William da Costa
 * Author URI: https://github.com/agenciadw
 * Text Domain: dw-whatsapp
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * HPOS: yes
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants
define( 'DW_WHATSAPP_VERSION', '0.2.0' );
define( 'DW_WHATSAPP_FILE', __FILE__ );
define( 'DW_WHATSAPP_PATH', plugin_dir_path( __FILE__ ) );
define( 'DW_WHATSAPP_URL', plugin_dir_url( __FILE__ ) );
define( 'DW_WHATSAPP_BASENAME', plugin_basename( __FILE__ ) );

// Autoload classes
spl_autoload_register( 'dw_whatsapp_autoload' );

function dw_whatsapp_autoload( $class ) {
	$prefix = 'DW_WhatsApp_';
	$len = strlen( $prefix );

	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$relative_class = substr( $class, $len );
	$file = DW_WHATSAPP_PATH . 'includes/class-dw-whatsapp-' . strtolower( str_replace( '_', '-', $relative_class ) ) . '.php';

	if ( file_exists( $file ) ) {
		require $file;
	}
}

// Initialize plugin
function dw_whatsapp_init() {
	require_once DW_WHATSAPP_PATH . 'includes/class-dw-whatsapp.php';
	return DW_WhatsApp::instance();
}

add_action( 'plugins_loaded', 'dw_whatsapp_init' );

// Activation hook
register_activation_hook( __FILE__, 'dw_whatsapp_activate' );

function dw_whatsapp_activate() {
	$default_options = array(
		'phone_number'               => '',
		'show_on_product_page'       => 'yes',
		'show_on_product_loop'       => 'yes',
		'show_floating_button'       => 'yes',
		'floating_button_position'   => 'bottom-right',
		'floating_button_hide_pages' => array( 'cart', 'checkout', 'my-account' ),
		'message_with_price'         => 'Olá! Gostaria de comprar o produto: {product_name}',
		'message_without_price'      => 'Olá! Gostaria de solicitar um orçamento para o produto: {product_name}',
		'button_text_with_price'     => 'Comprar via WhatsApp',
		'button_text_without_price'  => 'Solicitar Orçamento',
		'floating_button_text'       => 'Fale Conosco',
		'floating_button_message'    => 'Olá! Vim pelo site e gostaria de mais informações.',
		'include_product_link'       => 'yes',
		'include_variations'         => 'yes',
		'button_color'               => '#25d366',
		'multi_users_enabled'        => 'no',
		'multi_users'                => array(),
		'chat_widget_title'          => 'Iniciar Conversa',
		'chat_widget_subtitle'       => 'Olá! Clique em um dos nossos membros abaixo para conversar no WhatsApp ;)',
		'chat_widget_availability'   => 'A equipe normalmente responde em alguns minutos.',
	);

	add_option( 'dw_whatsapp_settings', $default_options );
}

// Deactivation hook
register_deactivation_hook( __FILE__, 'dw_whatsapp_deactivate' );

function dw_whatsapp_deactivate() {
	// Keep settings for reactivation
}

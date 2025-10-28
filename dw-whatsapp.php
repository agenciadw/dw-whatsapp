<?php
/**
 * Plugin Name: DW WhatsApp para WooCommerce
 * Plugin URI: https://github.com/agenciadw/dw-whatsapp
 * Description: Plugin para integração do WhatsApp com WooCommerce. Adiciona botões de WhatsApp em produtos e botão flutuante em todas as páginas. Funciona com ou sem WooCommerce.
 * Version: 1.0.0
 * Author: David William da Costa
 * Author URI: https://dwdigital.com.br
 * Text Domain: dw-whatsapp
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Network: false
 * 
 * @package DW_WhatsApp
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
if ( ! defined( 'DW_WHATSAPP_VERSION' ) ) {
    define( 'DW_WHATSAPP_VERSION', '1.0.0' );
}

if ( ! defined( 'DW_WHATSAPP_FILE' ) ) {
    define( 'DW_WHATSAPP_FILE', __FILE__ );
}

if ( ! defined( 'DW_WHATSAPP_PATH' ) ) {
    define( 'DW_WHATSAPP_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'DW_WHATSAPP_URL' ) ) {
    define( 'DW_WHATSAPP_URL', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'DW_WHATSAPP_BASENAME' ) ) {
    define( 'DW_WHATSAPP_BASENAME', plugin_basename( __FILE__ ) );
}

// Initialize the plugin
require_once DW_WHATSAPP_PATH . 'includes/class-dw-whatsapp.php';

// Initialize the main plugin class
function dw_whatsapp_run() {
    DW_WhatsApp::instance();
}
add_action( 'plugins_loaded', 'dw_whatsapp_run' );

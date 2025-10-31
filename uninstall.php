<?php
/**
 * Uninstall DW WhatsApp
 * 
 * Remove todas as configurações do plugin quando for desinstalado
 * 
 * @package DW_WhatsApp
 */

// Se o arquivo não foi chamado pelo WordPress, aborta
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove as opções do banco de dados
delete_option( 'dw_whatsapp_settings' );

// Para sites multisite, remove de todos os sites
if ( is_multisite() ) {
    global $wpdb;
    
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    
    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        delete_option( 'dw_whatsapp_settings' );
        restore_current_blog();
    }
}





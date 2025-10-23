<?php
/**
 * Settings management
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Settings {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp_Settings
	 */
	private static $instance = null;

	/**
	 * Settings option name
	 *
	 * @var string
	 */
	const OPTION_NAME = 'dw_whatsapp_settings';

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp_Settings
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
		// Settings are loaded on demand
	}

	/**
	 * Get all settings
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = self::get_defaults();
		$settings = get_option( self::OPTION_NAME, array() );
		
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get single setting
	 *
	 * @param string $key Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public static function get( $key, $default = '' ) {
		$settings = self::get_settings();
		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}

	/**
	 * Update settings - usando padrão WordPress/WooCommerce
	 *
	 * @param array $settings Settings array.
	 * @return bool
	 */
	public static function update( $settings ) {
		if ( ! is_array( $settings ) ) {
			return false;
		}
		
		$sanitized = self::sanitize( $settings );
		
		return update_option( self::OPTION_NAME, $sanitized );
	}

	/**
	 * Get default settings
	 *
	 * @return array
	 */
	private static function get_defaults() {
		return array(
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
	}

	/**
	 * Sanitize settings - Padrão WordPress/WooCommerce
	 *
	 * @param array $input Input array.
	 * @return array
	 */
	private static function sanitize( $input ) {
		$sanitized = array();

		// Phone number
		$sanitized['phone_number'] = isset( $input['phone_number'] ) ? preg_replace( '/[^0-9]/', '', $input['phone_number'] ) : '';

		// Checkboxes
		$sanitized['show_on_product_page']   = ! empty( $input['show_on_product_page'] ) ? 'yes' : 'no';
		$sanitized['show_on_product_loop']   = ! empty( $input['show_on_product_loop'] ) ? 'yes' : 'no';
		$sanitized['show_floating_button']   = ! empty( $input['show_floating_button'] ) ? 'yes' : 'no';
		$sanitized['include_product_link']   = ! empty( $input['include_product_link'] ) ? 'yes' : 'no';
		$sanitized['include_variations']     = ! empty( $input['include_variations'] ) ? 'yes' : 'no';
		$sanitized['multi_users_enabled']    = ! empty( $input['multi_users_enabled'] ) ? 'yes' : 'no';

		// Position
		$allowed_positions = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
		$sanitized['floating_button_position'] = isset( $input['floating_button_position'] ) && in_array( $input['floating_button_position'], $allowed_positions ) ? $input['floating_button_position'] : 'bottom-right';

		// Hidden pages
		$sanitized['floating_button_hide_pages'] = array();
		if ( isset( $input['floating_button_hide_pages'] ) && is_array( $input['floating_button_hide_pages'] ) ) {
			$sanitized['floating_button_hide_pages'] = array_map( 'sanitize_key', $input['floating_button_hide_pages'] );
		}

		// Text fields
		$sanitized['message_with_price']         = isset( $input['message_with_price'] ) ? wp_kses_post( $input['message_with_price'] ) : '';
		$sanitized['message_without_price']      = isset( $input['message_without_price'] ) ? wp_kses_post( $input['message_without_price'] ) : '';
		$sanitized['button_text_with_price']     = isset( $input['button_text_with_price'] ) ? sanitize_text_field( $input['button_text_with_price'] ) : '';
		$sanitized['button_text_without_price']  = isset( $input['button_text_without_price'] ) ? sanitize_text_field( $input['button_text_without_price'] ) : '';
		$sanitized['floating_button_text']       = isset( $input['floating_button_text'] ) ? sanitize_text_field( $input['floating_button_text'] ) : '';
		$sanitized['floating_button_message']    = isset( $input['floating_button_message'] ) ? sanitize_text_field( $input['floating_button_message'] ) : '';
		$sanitized['chat_widget_title']          = isset( $input['chat_widget_title'] ) ? sanitize_text_field( $input['chat_widget_title'] ) : '';
		$sanitized['chat_widget_subtitle']       = isset( $input['chat_widget_subtitle'] ) ? sanitize_text_field( $input['chat_widget_subtitle'] ) : '';
		$sanitized['chat_widget_availability']   = isset( $input['chat_widget_availability'] ) ? sanitize_text_field( $input['chat_widget_availability'] ) : '';

		// Color
		$sanitized['button_color'] = isset( $input['button_color'] ) ? sanitize_hex_color( $input['button_color'] ) : '#25d366';

		// Multi users
		$sanitized['multi_users'] = array();
		if ( isset( $input['multi_users'] ) && is_array( $input['multi_users'] ) ) {
			foreach ( $input['multi_users'] as $user ) {
				if ( empty( $user['name'] ) || empty( $user['phone'] ) ) {
					continue;
				}

				$sanitized_user = array(
					'name'           => sanitize_text_field( $user['name'] ),
					'phone'          => preg_replace( '/[^0-9]/', '', $user['phone'] ),
					'department'     => isset( $user['department'] ) ? sanitize_text_field( $user['department'] ) : '',
					'avatar'         => isset( $user['avatar'] ) ? esc_url_raw( $user['avatar'] ) : '',
					'status'         => isset( $user['status'] ) && in_array( $user['status'], array( 'online', 'away', 'offline' ) ) ? $user['status'] : 'online',
					'status_message' => isset( $user['status_message'] ) ? sanitize_text_field( $user['status_message'] ) : '',
					'working_hours'  => isset( $user['working_hours'] ) ? sanitize_text_field( $user['working_hours'] ) : '',
					'auto_status'    => ! empty( $user['auto_status'] ) ? 'yes' : 'no',
				);

				// Campos de horário automático
				if ( $sanitized_user['auto_status'] === 'yes' ) {
					$sanitized_user['working_days'] = isset( $user['working_days'] ) && is_array( $user['working_days'] ) ? array_map( 'sanitize_key', $user['working_days'] ) : array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
					$sanitized_user['work_start']   = isset( $user['work_start'] ) ? sanitize_text_field( $user['work_start'] ) : '09:00';
					$sanitized_user['work_end']     = isset( $user['work_end'] ) ? sanitize_text_field( $user['work_end'] ) : '18:00';
					$sanitized_user['timezone']     = isset( $user['timezone'] ) ? sanitize_text_field( $user['timezone'] ) : 'America/Sao_Paulo';
				}

				$sanitized['multi_users'][] = $sanitized_user;

				// Limitar a 10 usuários
				if ( count( $sanitized['multi_users'] ) >= 10 ) {
					break;
				}
			}
		}

		return $sanitized;
	}
}

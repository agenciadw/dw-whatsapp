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
			'floating_button_position'   => 'bottom-right', // Mantido para compatibilidade
			'floating_button_position_desktop' => 'bottom-right',
			'floating_button_position_mobile' => 'bottom-right',
			'floating_button_offset_x_desktop' => '0',
			'floating_button_offset_y_desktop' => '0',
			'floating_button_offset_x_mobile' => '0',
			'floating_button_offset_y_mobile' => '0',
			'floating_button_style'      => 'rectangular',
			'floating_button_size'        => 'medium',
			'floating_button_hide_pages' => array( 'cart', 'checkout', 'my-account' ),
			'message_with_price'         => 'Olá! Gostaria de comprar o produto: {product_name}',
			'message_without_price'      => 'Olá! Gostaria de solicitar um orçamento para o produto: {product_name}',
			'button_text_with_price'     => 'Comprar via WhatsApp',
			'button_text_without_price'  => 'Solicitar Orçamento',
			'floating_button_text'       => 'Fale Conosco',
			'floating_button_message'    => 'Olá! Vim pelo site e gostaria de mais informações.',
			'floating_button_message_product' => 'Olá! Gostaria de saber mais sobre {product_name}.',
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
		
		// Desktop Position
		$sanitized['floating_button_position_desktop'] = isset( $input['floating_button_position_desktop'] ) && in_array( $input['floating_button_position_desktop'], $allowed_positions ) ? $input['floating_button_position_desktop'] : 'bottom-right';
		
		// Mobile Position
		$sanitized['floating_button_position_mobile'] = isset( $input['floating_button_position_mobile'] ) && in_array( $input['floating_button_position_mobile'], $allowed_positions ) ? $input['floating_button_position_mobile'] : 'bottom-right';

		// Style
		$allowed_styles = array( 'rectangular', 'circular' );
		$sanitized['floating_button_style'] = isset( $input['floating_button_style'] ) && in_array( $input['floating_button_style'], $allowed_styles ) ? $input['floating_button_style'] : 'rectangular';

		// Desktop Offsets
		$sanitized['floating_button_offset_x_desktop'] = isset( $input['floating_button_offset_x_desktop'] ) ? intval( $input['floating_button_offset_x_desktop'] ) : 0;
		$sanitized['floating_button_offset_y_desktop'] = isset( $input['floating_button_offset_y_desktop'] ) ? intval( $input['floating_button_offset_y_desktop'] ) : 0;
		
		// Mobile Offsets
		$sanitized['floating_button_offset_x_mobile'] = isset( $input['floating_button_offset_x_mobile'] ) ? intval( $input['floating_button_offset_x_mobile'] ) : 0;
		$sanitized['floating_button_offset_y_mobile'] = isset( $input['floating_button_offset_y_mobile'] ) ? intval( $input['floating_button_offset_y_mobile'] ) : 0;

		// Size
		$allowed_sizes = array( 'small', 'medium', 'large' );
		$sanitized['floating_button_size'] = isset( $input['floating_button_size'] ) && in_array( $input['floating_button_size'], $allowed_sizes ) ? $input['floating_button_size'] : 'medium';

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
		$sanitized['floating_button_message_product'] = isset( $input['floating_button_message_product'] ) ? sanitize_text_field( $input['floating_button_message_product'] ) : '';
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
					$sanitized_user['timezone'] = isset( $user['timezone'] ) ? sanitize_text_field( $user['timezone'] ) : 'America/Sao_Paulo';
					
					// Processar horários por dia
					$sanitized_user['day_hours'] = array();
					$days = array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' );
					
					foreach ( $days as $day ) {
						$sanitized_user['day_hours'][ $day ] = array(
							'enabled' => isset( $user['day_hours'][ $day ]['enabled'] ) ? '1' : '0',
							'start'   => isset( $user['day_hours'][ $day ]['start'] ) ? sanitize_text_field( $user['day_hours'][ $day ]['start'] ) : (in_array($day, ['saturday', 'sunday']) ? '08:00' : '09:00'),
							'end'     => isset( $user['day_hours'][ $day ]['end'] ) ? sanitize_text_field( $user['day_hours'][ $day ]['end'] ) : (in_array($day, ['saturday', 'sunday']) ? '12:00' : '18:00'),
						);
					}
					
					// Manter compatibilidade com sistema antigo
					$sanitized_user['working_days'] = array();
					foreach ( $sanitized_user['day_hours'] as $day => $hours ) {
						if ( $hours['enabled'] === '1' ) {
							$sanitized_user['working_days'][] = $day;
						}
					}
					
					// Horários padrão para compatibilidade
					$sanitized_user['work_start'] = $sanitized_user['day_hours']['monday']['start'];
					$sanitized_user['work_end'] = $sanitized_user['day_hours']['monday']['end'];
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

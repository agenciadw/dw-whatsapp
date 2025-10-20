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
	 * Update settings
	 *
	 * @param array $settings Settings array.
	 * @return bool
	 */
	public static function update( $settings ) {
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
		);
	}

	/**
	 * Sanitize settings
	 *
	 * @param array $input Input array.
	 * @return array
	 */
	private static function sanitize( $input ) {
		$sanitized = array();

		// Phone number
		if ( isset( $input['phone_number'] ) ) {
			$sanitized['phone_number'] = preg_replace( '/[^0-9]/', '', $input['phone_number'] );
		}

		// Checkboxes
		$checkboxes = array(
			'show_on_product_page',
			'show_on_product_loop',
			'show_floating_button',
			'include_product_link',
			'include_variations',
		);

		foreach ( $checkboxes as $checkbox ) {
			$sanitized[ $checkbox ] = isset( $input[ $checkbox ] ) ? 'yes' : 'no';
		}

		// Position
		if ( isset( $input['floating_button_position'] ) ) {
			$allowed = array( 'bottom-right', 'bottom-left', 'top-right', 'top-left' );
			$sanitized['floating_button_position'] = in_array( $input['floating_button_position'], $allowed, true ) ? $input['floating_button_position'] : 'bottom-right';
		}

		// Hidden pages
		if ( isset( $input['floating_button_hide_pages'] ) && is_array( $input['floating_button_hide_pages'] ) ) {
			$allowed_pages = array(
				'home', 'page', 'post', 'category', 'tag', 'archive', 'search', '404',
				'cart', 'checkout', 'my-account', 'shop', 'product', 'product-category', 'product-tag', 'other',
			);
			
			$sanitized['floating_button_hide_pages'] = array();
			
			foreach ( $input['floating_button_hide_pages'] as $page ) {
				if ( in_array( $page, $allowed_pages, true ) ) {
					$sanitized['floating_button_hide_pages'][] = sanitize_text_field( $page );
				}
			}
		}

		// Text fields
		$text_fields = array(
			'message_with_price',
			'message_without_price',
			'button_text_with_price',
			'button_text_without_price',
			'floating_button_text',
			'floating_button_message',
		);

		foreach ( $text_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = sanitize_text_field( $input[ $field ] );
			}
		}

		// Color
		if ( isset( $input['button_color'] ) ) {
			$sanitized['button_color'] = sanitize_hex_color( $input['button_color'] );
		}

		return $sanitized;
	}
}


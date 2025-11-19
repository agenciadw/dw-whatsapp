<?php
/**
 * Custom fields management
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Custom_Fields {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private static $table_name = 'dw_whatsapp_custom_fields';

	/**
	 * Get table name with prefix
	 *
	 * @return string
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . self::$table_name;
	}

	/**
	 * Create table
	 */
	public static function create_table() {
		global $wpdb;
		$table_name = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			field_key varchar(100) NOT NULL,
			field_label varchar(255) NOT NULL,
			field_type varchar(50) NOT NULL,
			field_options longtext DEFAULT NULL,
			is_required tinyint(1) DEFAULT 0,
			show_in_whatsapp tinyint(1) DEFAULT 1,
			field_order int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			UNIQUE KEY field_key (field_key),
			KEY field_order (field_order)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Get all custom fields
	 *
	 * @return array
	 */
	public static function get_all_fields() {
		global $wpdb;
		$table_name = self::get_table_name();

		$results = $wpdb->get_results(
			"SELECT * FROM $table_name ORDER BY field_order ASC, id ASC",
			ARRAY_A
		);

		return $results ? $results : array();
	}

	/**
	 * Get single field by key
	 *
	 * @param string $field_key Field key.
	 * @return array|false
	 */
	public static function get_field( $field_key ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$result = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM $table_name WHERE field_key = %s", $field_key ),
			ARRAY_A
		);

		return $result ? $result : false;
	}

	/**
	 * Save custom field
	 *
	 * @param array $data Field data.
	 * @return int|false Field ID or false on failure.
	 */
	public static function save_field( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'field_key'        => '',
			'field_label'      => '',
			'field_type'       => 'text',
			'field_options'    => '',
			'is_required'      => 0,
			'show_in_whatsapp' => 1,
			'field_order'      => 0,
		);

		$data = wp_parse_args( $data, $defaults );

		// Sanitize
		$data['field_key'] = sanitize_key( $data['field_key'] );
		$data['field_label'] = sanitize_text_field( $data['field_label'] );
		$allowed_types = array( 'text', 'textarea', 'email', 'tel', 'date', 'number', 'password', 'select' );
		$data['field_type'] = in_array( $data['field_type'], $allowed_types ) ? $data['field_type'] : 'text';
		$data['field_options'] = is_array( $data['field_options'] ) ? json_encode( $data['field_options'] ) : sanitize_textarea_field( $data['field_options'] );
		$data['is_required'] = ! empty( $data['is_required'] ) ? 1 : 0;
		$data['show_in_whatsapp'] = ! empty( $data['show_in_whatsapp'] ) ? 1 : 0;
		$data['field_order'] = absint( $data['field_order'] );

		// Check if field exists
		$existing = self::get_field( $data['field_key'] );
		
		if ( $existing ) {
			// Update
			$result = $wpdb->update(
				$table_name,
				$data,
				array( 'field_key' => $data['field_key'] ),
				array( '%s', '%s', '%s', '%s', '%d', '%d', '%d' ),
				array( '%s' )
			);

			return $result !== false ? $existing['id'] : false;
		} else {
			// Insert
			$result = $wpdb->insert(
				$table_name,
				$data,
				array( '%s', '%s', '%s', '%s', '%d', '%d', '%d' )
			);

			if ( $result ) {
				return $wpdb->insert_id;
			}
		}

		return false;
	}

	/**
	 * Delete custom field
	 *
	 * @param string $field_key Field key.
	 * @return bool
	 */
	public static function delete_field( $field_key ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return (bool) $wpdb->delete(
			$table_name,
			array( 'field_key' => $field_key ),
			array( '%s' )
		);
	}

	/**
	 * Update field order
	 *
	 * @param array $field_orders Array of field_key => order.
	 * @return bool
	 */
	public static function update_field_order( $field_orders ) {
		global $wpdb;
		$table_name = self::get_table_name();

		foreach ( $field_orders as $field_key => $order ) {
			$wpdb->update(
				$table_name,
				array( 'field_order' => absint( $order ) ),
				array( 'field_key' => sanitize_key( $field_key ) ),
				array( '%d' ),
				array( '%s' )
			);
		}

		return true;
	}

	/**
	 * Create table for lead custom field values
	 */
	public static function create_lead_fields_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dw_whatsapp_lead_fields';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			lead_id bigint(20) NOT NULL,
			field_key varchar(100) NOT NULL,
			field_value longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY lead_id (lead_id),
			KEY field_key (field_key),
			UNIQUE KEY lead_field (lead_id, field_key)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Save lead custom field values
	 *
	 * @param int   $lead_id Lead ID.
	 * @param array $field_values Array of field_key => value.
	 * @return bool
	 */
	public static function save_lead_fields( $lead_id, $field_values ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dw_whatsapp_lead_fields';

		// Delete existing values for this lead
		$wpdb->delete(
			$table_name,
			array( 'lead_id' => absint( $lead_id ) ),
			array( '%d' )
		);

		// Insert new values
		foreach ( $field_values as $field_key => $value ) {
			$wpdb->insert(
				$table_name,
				array(
					'lead_id'     => absint( $lead_id ),
					'field_key'   => sanitize_key( $field_key ),
					'field_value' => sanitize_textarea_field( $value ),
				),
				array( '%d', '%s', '%s' )
			);
		}

		return true;
	}

	/**
	 * Get lead custom field values
	 *
	 * @param int $lead_id Lead ID.
	 * @return array
	 */
	public static function get_lead_fields( $lead_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dw_whatsapp_lead_fields';

		$results = $wpdb->get_results(
			$wpdb->prepare( "SELECT field_key, field_value FROM $table_name WHERE lead_id = %d", absint( $lead_id ) ),
			ARRAY_A
		);

		$fields = array();
		foreach ( $results as $result ) {
			$fields[ $result['field_key'] ] = $result['field_value'];
		}

		return $fields;
	}

	/**
	 * Get custom field values for a group of leads (by email or phone)
	 *
	 * @param string $email Email address.
	 * @param string $phone Phone number.
	 * @return array
	 */
	public static function get_lead_fields_by_contact( $email = '', $phone = '' ) {
		global $wpdb;
		$leads_table = $wpdb->prefix . 'dw_whatsapp_leads';

		// Find the most recent lead ID for this contact
		$where_parts = array();
		
		if ( ! empty( $email ) ) {
			$where_parts[] = $wpdb->prepare( 'email = %s', $email );
		}
		
		if ( ! empty( $phone ) ) {
			$phone_clean = preg_replace( '/[^0-9]/', '', $phone );
			$where_parts[] = $wpdb->prepare( 'phone = %s', $phone_clean );
		}
		
		if ( empty( $where_parts ) ) {
			return array();
		}
		
		$where_clause = '(' . implode( ' OR ', $where_parts ) . ')';
		
		// Get the most recent lead ID
		$query = "SELECT id FROM $leads_table WHERE $where_clause ORDER BY created_at DESC LIMIT 1";
		$lead_id = $wpdb->get_var( $query );
		
		if ( ! $lead_id ) {
			return array();
		}
		
		return self::get_lead_fields( $lead_id );
	}
}


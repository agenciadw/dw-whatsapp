<?php
/**
 * Leads management
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Leads {

	/**
	 * Table name
	 *
	 * @var string
	 */
	private static $table_name = 'dw_whatsapp_leads';

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
			name varchar(255) DEFAULT NULL,
			email varchar(255) DEFAULT NULL,
			phone varchar(50) DEFAULT NULL,
			is_customer tinyint(1) DEFAULT 0,
			customer_id bigint(20) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY email (email),
			KEY phone (phone),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	/**
	 * Save lead
	 *
	 * @param array $data Lead data.
	 * @return int|false Lead ID or false on failure.
	 */
	public static function save_lead( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'name'         => '',
			'email'        => '',
			'phone'        => '',
			'is_customer'  => 0,
			'customer_id'  => null,
			'created_at'   => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		// Sanitize
		$data['name'] = sanitize_text_field( $data['name'] );
		$data['email'] = sanitize_email( $data['email'] );
		$data['phone'] = sanitize_text_field( $data['phone'] );
		$data['is_customer'] = (int) $data['is_customer'];
		$data['customer_id'] = ! empty( $data['customer_id'] ) ? absint( $data['customer_id'] ) : null;

		$result = $wpdb->insert(
			$table_name,
			$data,
			array( '%s', '%s', '%s', '%d', '%d', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get leads
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public static function get_leads( $args = array() ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'orderby'  => 'last_contact',
			'order'    => 'DESC',
			'search'   => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$limit = absint( $args['per_page'] );

		$where = '1=1';
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where .= $wpdb->prepare( " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s)", $search, $search, $search );
		}

		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		if ( ! $orderby ) {
			$orderby = 'last_contact DESC';
		}

		// Agrupar por email ou telefone e contar contatos
		$group_by = "COALESCE(NULLIF(email, ''), phone)";
		$query = "SELECT 
			MIN(id) as id,
			MAX(name) as name,
			MAX(email) as email,
			MAX(phone) as phone,
			MAX(is_customer) as is_customer,
			MAX(customer_id) as customer_id,
			MAX(created_at) as last_contact,
			MIN(created_at) as first_contact,
			COUNT(*) as contact_count
		FROM $table_name 
		WHERE $where 
		GROUP BY $group_by
		ORDER BY $orderby 
		LIMIT $limit OFFSET $offset";
		
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results ? $results : array();
	}

	/**
	 * Get total leads count
	 *
	 * @param string $search Search term.
	 * @return int
	 */
	public static function get_total_leads( $search = '' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$where = '1=1';
		if ( ! empty( $search ) ) {
			$search = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s)", $search, $search, $search );
		}

		// Contar grupos únicos (email ou telefone)
		$count = $wpdb->get_var( "SELECT COUNT(DISTINCT COALESCE(NULLIF(email, ''), phone)) FROM $table_name WHERE $where" );
		return absint( $count );
	}

	/**
	 * Delete lead
	 *
	 * @param int $lead_id Lead ID.
	 * @return bool
	 */
	public static function delete_lead( $lead_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return (bool) $wpdb->delete(
			$table_name,
			array( 'id' => absint( $lead_id ) ),
			array( '%d' )
		);
	}

	/**
	 * Check if email is customer
	 *
	 * @param string $email Email address.
	 * @return array|false Customer data or false.
	 */
	public static function check_is_customer( $email ) {
		if ( empty( $email ) ) {
			return false;
		}

		// Verificar se o e-mail tem cadastro no WordPress (usuário registrado)
		$user = get_user_by( 'email', $email );
		if ( $user ) {
			return array(
				'is_customer' => 1,
				'customer_id' => $user->ID,
			);
		}

		// Se WooCommerce estiver ativo, também verificar por e-mail nos pedidos (clientes sem conta WordPress)
		if ( class_exists( 'WooCommerce' ) ) {
			$orders_by_email = wc_get_orders( array(
				'billing_email' => $email,
				'limit'         => 1,
				'return'        => 'ids',
			) );

			if ( ! empty( $orders_by_email ) ) {
				return array(
					'is_customer' => 1,
					'customer_id' => null, // Cliente sem conta WordPress mas com pedidos
				);
			}
		}

		return false;
	}

	/**
	 * Get all leads for export
	 *
	 * @param string $search Search term.
	 * @return array
	 */
	public static function get_all_leads_for_export( $search = '' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$where = '1=1';
		if ( ! empty( $search ) ) {
			$search = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare( " AND (name LIKE %s OR email LIKE %s OR phone LIKE %s)", $search, $search, $search );
		}

		// Agrupar por email ou telefone e contar contatos
		$group_by = "COALESCE(NULLIF(email, ''), phone)";
		$query = "SELECT 
			MIN(id) as id,
			MAX(name) as name,
			MAX(email) as email,
			MAX(phone) as phone,
			MAX(is_customer) as is_customer,
			MAX(customer_id) as customer_id,
			MAX(created_at) as last_contact,
			MIN(created_at) as first_contact,
			COUNT(*) as contact_count
		FROM $table_name 
		WHERE $where 
		GROUP BY $group_by
		ORDER BY last_contact DESC";
		
		$results = $wpdb->get_results( $query, ARRAY_A );

		return $results ? $results : array();
	}
}


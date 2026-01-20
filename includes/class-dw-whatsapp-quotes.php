<?php
/**
 * Quotes (cart checkout via WhatsApp) management
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Quotes {

	/**
	 * Table name (without prefix)
	 *
	 * @var string
	 */
	private static $table_name = 'dw_whatsapp_quotes';

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
			user_id bigint(20) DEFAULT NULL,
			customer_name varchar(255) DEFAULT NULL,
			customer_email varchar(255) DEFAULT NULL,
			customer_phone varchar(50) DEFAULT NULL,
			cart_contents longtext DEFAULT NULL,
			totals longtext DEFAULT NULL,
			currency varchar(10) DEFAULT NULL,
			ip varchar(45) DEFAULT NULL,
			user_agent text DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY created_at (created_at),
			KEY user_id (user_id),
			KEY customer_email (customer_email)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Save quote
	 *
	 * @param array $data Quote data.
	 * @return int|false Quote ID or false on failure.
	 */
	public static function save_quote( $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'user_id'        => null,
			'customer_name'  => '',
			'customer_email' => '',
			'customer_phone' => '',
			'cart_contents'  => '',
			'totals'         => '',
			'currency'       => '',
			'ip'             => '',
			'user_agent'     => '',
			'created_at'     => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		$data['user_id'] = ! empty( $data['user_id'] ) ? absint( $data['user_id'] ) : null;
		$data['customer_name'] = sanitize_text_field( $data['customer_name'] );
		$data['customer_email'] = sanitize_email( $data['customer_email'] );
		$data['customer_phone'] = sanitize_text_field( $data['customer_phone'] );
		$data['cart_contents'] = is_string( $data['cart_contents'] ) ? $data['cart_contents'] : wp_json_encode( $data['cart_contents'] );
		$data['totals'] = is_string( $data['totals'] ) ? $data['totals'] : wp_json_encode( $data['totals'] );
		$data['currency'] = sanitize_text_field( $data['currency'] );
		$data['ip'] = sanitize_text_field( $data['ip'] );
		$data['user_agent'] = sanitize_text_field( $data['user_agent'] );

		$result = $wpdb->insert(
			$table_name,
			$data,
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get quotes
	 *
	 * @param array $args Query args.
	 * @return array
	 */
	public static function get_quotes( $args = array() ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'per_page' => 20,
			'page'     => 1,
			'search'   => '',
			'orderby'  => 'created_at',
			'order'    => 'DESC',
		);
		$args = wp_parse_args( $args, $defaults );

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$limit = absint( $args['per_page'] );

		$where = '1=1';
		if ( ! empty( $args['search'] ) ) {
			$search = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where .= $wpdb->prepare(
				' AND (customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)',
				$search,
				$search,
				$search
			);
		}

		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );
		if ( ! $orderby ) {
			$orderby = 'created_at DESC';
		}

		$query = $wpdb->prepare(
			"SELECT * FROM $table_name WHERE $where ORDER BY $orderby LIMIT %d OFFSET %d",
			$limit,
			$offset
		);

		$results = $wpdb->get_results( $query, ARRAY_A );
		return $results ? $results : array();
	}

	/**
	 * Get total quotes
	 *
	 * @param string $search Search term.
	 * @return int
	 */
	public static function get_total_quotes( $search = '' ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$where = '1=1';
		if ( ! empty( $search ) ) {
			$search = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare(
				' AND (customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s)',
				$search,
				$search,
				$search
			);
		}

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE $where" );
		return absint( $count );
	}

	/**
	 * Get quote by ID
	 *
	 * @param int $quote_id Quote ID.
	 * @return array|null
	 */
	public static function get_quote( $quote_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$quote_id = absint( $quote_id );
		if ( ! $quote_id ) {
			return null;
		}

		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $quote_id ), ARRAY_A );
		return $row ? $row : null;
	}

	/**
	 * Delete quote
	 *
	 * @param int $quote_id Quote ID.
	 * @return bool
	 */
	public static function delete_quote( $quote_id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		return (bool) $wpdb->delete(
			$table_name,
			array( 'id' => absint( $quote_id ) ),
			array( '%d' )
		);
	}
}


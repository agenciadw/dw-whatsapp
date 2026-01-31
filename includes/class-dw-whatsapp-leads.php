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
	 * Cache da verificação de coluna origin
	 *
	 * @var bool|null
	 */
	private static $has_origin_column = null;

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
			origin varchar(50) DEFAULT NULL,
			origin_source varchar(100) DEFAULT NULL,
			origin_campaign varchar(150) DEFAULT NULL,
			origin_campaign_id varchar(50) DEFAULT NULL,
			is_customer tinyint(1) DEFAULT 0,
			customer_id bigint(20) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY email (email),
			KEY phone (phone),
			KEY origin (origin),
			KEY origin_source (origin_source),
			KEY origin_campaign (origin_campaign),
			KEY origin_campaign_id (origin_campaign_id),
			KEY created_at (created_at)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Garantir compatibilidade em upgrades: dbDelta pode falhar em adicionar coluna em alguns ambientes.
		self::ensure_origin_column_exists();
	}

	/**
	 * Verifica se a coluna existe na tabela.
	 *
	 * @param string $column Column name.
	 * @return bool
	 */
	private static function column_exists( $column ) {
		global $wpdb;
		$table_name = self::get_table_name();
		$column = sanitize_key( $column );

		$col = $wpdb->get_var( $wpdb->prepare( "SHOW COLUMNS FROM {$table_name} LIKE %s", $column ) );
		return ! empty( $col );
	}

	/**
	 * Garante que a coluna origin existe (e cria índice quando possível).
	 *
	 * @return void
	 */
	private static function ensure_origin_column_exists() {
		global $wpdb;
		$table_name = self::get_table_name();

		// Reset cache para revalidar após possíveis alterações.
		self::$has_origin_column = null;

		if ( self::origin_column_exists() ) {
			// Continua para garantir também origin_source
		} else {
			// Adicionar coluna origin
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN origin varchar(50) DEFAULT NULL" );
			// Tentar adicionar índice (se já existir, o MySQL pode retornar erro; ignoramos)
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY origin (origin)" );
		}

		// Garantir origin_source
		if ( ! self::column_exists( 'origin_source' ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN origin_source varchar(100) DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY origin_source (origin_source)" );
		}

		// Garantir origin_campaign
		if ( ! self::column_exists( 'origin_campaign' ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN origin_campaign varchar(150) DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY origin_campaign (origin_campaign)" );
		}

		// Garantir origin_campaign_id
		if ( ! self::column_exists( 'origin_campaign_id' ) ) {
			$wpdb->query( "ALTER TABLE {$table_name} ADD COLUMN origin_campaign_id varchar(50) DEFAULT NULL" );
			$wpdb->query( "ALTER TABLE {$table_name} ADD KEY origin_campaign_id (origin_campaign_id)" );
		}

		// Atualizar cache
		self::$has_origin_column = null;
	}

	/**
	 * Indica se a coluna origin existe (com cache).
	 *
	 * @return bool
	 */
	public static function origin_column_exists() {
		if ( self::$has_origin_column === null ) {
			self::$has_origin_column = self::column_exists( 'origin' );
		}
		return (bool) self::$has_origin_column;
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
			'origin'       => '',
			'origin_source'=> '',
			'origin_campaign' => '',
			'origin_campaign_id' => '',
			'is_customer'  => 0,
			'customer_id'  => null,
			'created_at'   => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $data, $defaults );

		// Sanitize
		$data['name'] = sanitize_text_field( $data['name'] );
		$data['email'] = sanitize_email( $data['email'] );
		$data['phone'] = sanitize_text_field( $data['phone'] );
		$data['origin'] = sanitize_text_field( $data['origin'] );
		$data['origin_source'] = sanitize_text_field( $data['origin_source'] );
		$data['origin_campaign'] = sanitize_text_field( $data['origin_campaign'] );
		$data['origin_campaign_id'] = sanitize_text_field( $data['origin_campaign_id'] );
		$data['is_customer'] = (int) $data['is_customer'];
		$data['customer_id'] = ! empty( $data['customer_id'] ) ? absint( $data['customer_id'] ) : null;

		// Inserção robusta: se a coluna origin ainda não existir, não quebrar a captura.
		$formats_map = array(
			'name'        => '%s',
			'email'       => '%s',
			'phone'       => '%s',
			'origin'      => '%s',
			'origin_source' => '%s',
			'origin_campaign' => '%s',
			'origin_campaign_id' => '%s',
			'is_customer' => '%d',
			'customer_id' => '%d',
			'created_at'  => '%s',
		);

		$insert_data = $data;
		$formats = array();

		// Se a coluna não existir, remove origin do insert.
		if ( ! self::origin_column_exists() ) {
			unset( $insert_data['origin'] );
		}
		if ( ! self::column_exists( 'origin_source' ) ) {
			unset( $insert_data['origin_source'] );
		}
		if ( ! self::column_exists( 'origin_campaign' ) ) {
			unset( $insert_data['origin_campaign'] );
		}
		if ( ! self::column_exists( 'origin_campaign_id' ) ) {
			unset( $insert_data['origin_campaign_id'] );
		}

		// Montar formatos na mesma ordem do array de dados
		foreach ( $insert_data as $key => $_value ) {
			$formats[] = $formats_map[ $key ] ?? '%s';
		}

		$result = $wpdb->insert( $table_name, $insert_data, $formats );

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
		$origin_select = self::origin_column_exists()
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin,"
			: "'' as origin,";
		$origin_source_select = self::column_exists( 'origin_source' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_source ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_source,"
			: "'' as origin_source,";
		$origin_campaign_select = self::column_exists( 'origin_campaign' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_campaign ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_campaign,"
			: "'' as origin_campaign,";
		$origin_campaign_id_select = self::column_exists( 'origin_campaign_id' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_campaign_id ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_campaign_id,"
			: "'' as origin_campaign_id,";
		$query = "SELECT 
			MIN(id) as id,
			MAX(name) as name,
			MAX(email) as email,
			MAX(phone) as phone,
			$origin_select
			$origin_source_select
			$origin_campaign_select
			$origin_campaign_id_select
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
		$origin_select = self::origin_column_exists()
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin,"
			: "'' as origin,";
		$origin_source_select = self::column_exists( 'origin_source' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_source ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_source,"
			: "'' as origin_source,";
		$origin_campaign_select = self::column_exists( 'origin_campaign' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_campaign ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_campaign,"
			: "'' as origin_campaign,";
		$origin_campaign_id_select = self::column_exists( 'origin_campaign_id' )
			? "SUBSTRING_INDEX(GROUP_CONCAT(origin_campaign_id ORDER BY created_at ASC SEPARATOR '||'), '||', 1) as origin_campaign_id,"
			: "'' as origin_campaign_id,";
		$query = "SELECT 
			MIN(id) as id,
			MAX(name) as name,
			MAX(email) as email,
			MAX(phone) as phone,
			$origin_select
			$origin_source_select
			$origin_campaign_select
			$origin_campaign_id_select
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


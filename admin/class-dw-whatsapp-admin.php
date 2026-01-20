<?php
/**
 * Admin panel
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Admin {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp_Admin
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp_Admin
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'handle_export' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_ajax_dw_whatsapp_save_lead', array( $this, 'ajax_save_lead' ) );
		add_action( 'wp_ajax_nopriv_dw_whatsapp_save_lead', array( $this, 'ajax_save_lead' ) );
		add_action( 'wp_ajax_dw_whatsapp_get_lead_details', array( $this, 'ajax_get_lead_details' ) );
	}

	/**
	 * Add admin menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			'DW WhatsApp',
			'DW WhatsApp',
			'manage_options',
			'dw-whatsapp',
			array( $this, 'render_settings_page' ),
			'dashicons-whatsapp',
			56
		);
		
		add_submenu_page(
			'dw-whatsapp',
			'Leads',
			'Leads',
			'manage_options',
			'dw-whatsapp-leads',
			array( $this, 'render_leads_page' )
		);

		add_submenu_page(
			'dw-whatsapp',
			'Cotações',
			'Cotações',
			'manage_options',
			'dw-whatsapp-quotes',
			array( $this, 'render_quotes_page' )
		);
		
		add_submenu_page(
			'dw-whatsapp',
			'Campos Customizados',
			'Campos Customizados',
			'manage_options',
			'dw-whatsapp-custom-fields',
			array( $this, 'render_custom_fields_page' )
		);
	}

	/**
	 * Register settings
	 */
	public function register_settings() {
		// Não registrar callback para evitar loop infinito
		// O salvamento é feito manualmente no render_settings_page
	}

	/**
	 * Handle export requests early
	 */
	public function handle_export() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'dw-whatsapp-leads' ) {
			if ( isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'export_csv', 'export_excel' ) ) ) {
				check_admin_referer( 'export_leads' );
				$this->export_leads( $_GET['action'] );
				exit; // Exit here to prevent any further rendering
			}
		}
		
		// Handle custom fields actions
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'dw-whatsapp-custom-fields' ) {
			if ( isset( $_POST['save_custom_field'] ) ) {
				check_admin_referer( 'dw_whatsapp_custom_field_action', 'dw_whatsapp_custom_field_nonce' );
				$this->save_custom_field();
			}
			
			if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['field_key'] ) ) {
				check_admin_referer( 'delete_custom_field_' . $_GET['field_key'] );
				$this->delete_custom_field( sanitize_key( $_GET['field_key'] ) );
				wp_redirect( admin_url( 'admin.php?page=dw-whatsapp-custom-fields&deleted=1' ) );
				exit;
			}
		}
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		// Enqueue scripts for settings page
		if ( 'toplevel_page_dw-whatsapp' === $hook ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );

			wp_add_inline_script( 'wp-color-picker', '
				jQuery(document).ready(function($) {
					$(".dw-color-picker").wpColorPicker();
				});
			' );
		}
		
		// Enqueue jQuery for leads page (jQuery is already included in WordPress admin, but we ensure it's available)
		if ( 'dw-whatsapp_page_dw-whatsapp-leads' === $hook ) {
			wp_enqueue_script( 'jquery' );
		}
	}

	/**
	 * Render settings page
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'dw-whatsapp' ) );
		}

		if ( isset( $_POST['dw_whatsapp_settings_submit'] ) ) {
			check_admin_referer( 'dw_whatsapp_settings_action', 'dw_whatsapp_settings_nonce' );
			
			// Verificar se os dados foram enviados
			if ( isset( $_POST['dw_whatsapp_settings'] ) && is_array( $_POST['dw_whatsapp_settings'] ) ) {
				// Limitar o número de usuários para evitar problemas de performance
				if ( isset( $_POST['dw_whatsapp_settings']['multi_users'] ) && is_array( $_POST['dw_whatsapp_settings']['multi_users'] ) ) {
					// Limitar a 10 usuários para evitar problemas
					$_POST['dw_whatsapp_settings']['multi_users'] = array_slice( $_POST['dw_whatsapp_settings']['multi_users'], 0, 10 );
				}
				
				// Tentar salvar com tratamento de erro
				try {
					$result = DW_WhatsApp_Settings::update( $_POST['dw_whatsapp_settings'] );
					
					if ( $result ) {
						echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configurações salvas com sucesso!', 'dw-whatsapp' ) . '</p></div>';
					} else {
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erro ao salvar configurações. Tente novamente.', 'dw-whatsapp' ) . '</p></div>';
					}
				} catch ( Exception $e ) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erro crítico: ', 'dw-whatsapp' ) . esc_html( $e->getMessage() ) . '</p></div>';
				}
			} else {
				echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Dados inválidos recebidos.', 'dw-whatsapp' ) . '</p></div>';
			}
		}

		require_once DW_WHATSAPP_PATH . 'admin/views/settings-page.php';
	}

	/**
	 * AJAX handler to save lead
	 */
	public function ajax_save_lead() {
		check_ajax_referer( 'dw_whatsapp_save_lead', 'nonce' );

		$name = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';

		// Verificar se é cliente WooCommerce
		$is_customer = 0;
		$customer_id = null;
		
		if ( ! empty( $email ) && class_exists( 'WooCommerce' ) ) {
			$customer_data = DW_WhatsApp_Leads::check_is_customer( $email );
			if ( $customer_data ) {
				$is_customer = $customer_data['is_customer'];
				$customer_id = $customer_data['customer_id'];
			}
		}

		$lead_data = array(
			'name'         => $name,
			'email'        => $email,
			'phone'        => $phone,
			'is_customer'  => $is_customer,
			'customer_id'  => $customer_id,
		);

		$lead_id = DW_WhatsApp_Leads::save_lead( $lead_data );

		// Save custom fields
		if ( $lead_id ) {
			$custom_fields = array();
			$all_fields = DW_WhatsApp_Custom_Fields::get_all_fields();
			
			foreach ( $all_fields as $field ) {
				$field_key = $field['field_key'];
				if ( isset( $_POST[ $field_key ] ) ) {
					$value = sanitize_textarea_field( $_POST[ $field_key ] );
					if ( ! empty( $value ) ) {
						$custom_fields[ $field_key ] = $value;
					}
				}
			}
			
			if ( ! empty( $custom_fields ) ) {
				DW_WhatsApp_Custom_Fields::save_lead_fields( $lead_id, $custom_fields );
			}
		}

		if ( $lead_id ) {
			wp_send_json_success( array(
				'lead_id' => $lead_id,
				'message' => 'Lead salvo com sucesso',
			) );
		} else {
			wp_send_json_error( array(
				'message' => 'Erro ao salvar lead',
			) );
		}
	}

	/**
	 * Format phone number
	 *
	 * @param string $phone Phone number.
	 * @return string
	 */
	private function format_phone( $phone ) {
		if ( empty( $phone ) ) {
			return '-';
		}
		
		$phone = preg_replace( '/\D/', '', $phone );
		
		if ( strlen( $phone ) === 11 ) {
			// Celular (99) 99999-9999
			return '(' . substr( $phone, 0, 2 ) . ') ' . substr( $phone, 2, 5 ) . '-' . substr( $phone, 7 );
		} elseif ( strlen( $phone ) === 10 ) {
			// Fixo (99) 9999-9999
			return '(' . substr( $phone, 0, 2 ) . ') ' . substr( $phone, 2, 4 ) . '-' . substr( $phone, 6 );
		}
		
		return $phone;
	}

	/**
	 * Render leads page
	 */
	public function render_leads_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'dw-whatsapp' ) );
		}

		// Export is now handled in handle_export() method via admin_init hook

		// Handle delete
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['lead_id'] ) ) {
			check_admin_referer( 'delete_lead_' . $_GET['lead_id'] );
			DW_WhatsApp_Leads::delete_lead( absint( $_GET['lead_id'] ) );
			echo '<div class="notice notice-success is-dismissible"><p>Lead excluído com sucesso!</p></div>';
		}

		// Pagination
		$per_page = 20;
		$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

		$args = array(
			'per_page' => $per_page,
			'page'     => $current_page,
			'search'   => $search,
		);

		$leads = DW_WhatsApp_Leads::get_leads( $args );
		$total = DW_WhatsApp_Leads::get_total_leads( $search );
		$total_pages = ceil( $total / $per_page );

		?>
		<div class="wrap">
			<h1>Leads do WhatsApp</h1>
			
			<div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
				<form method="get" style="flex: 1; min-width: 300px;">
					<input type="hidden" name="page" value="dw-whatsapp-leads">
					<p class="search-box" style="margin: 0;">
						<label class="screen-reader-text" for="lead-search-input">Buscar leads:</label>
						<input type="search" id="lead-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nome, e-mail ou telefone..." style="width: 300px;">
						<input type="submit" id="search-submit" class="button" value="Buscar">
						<?php if ( $search ) : ?>
							<a href="<?php echo admin_url( 'admin.php?page=dw-whatsapp-leads' ); ?>" class="button">Limpar</a>
						<?php endif; ?>
					</p>
				</form>
				<div style="display: flex; gap: 10px;">
					<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-leads&action=export_csv&s=' . urlencode( $search ) ), 'export_leads' ); ?>" class="button">
						📥 Exportar CSV
					</a>
					<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-leads&action=export_excel&s=' . urlencode( $search ) ), 'export_leads' ); ?>" class="button button-primary">
						📊 Exportar Excel
					</a>
				</div>
			</div>

			<div class="tablenav top">
				<div class="alignleft actions">
					<span class="displaying-num"><?php echo esc_html( $total ); ?> lead(s)</span>
				</div>
				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav-pages">
						<?php
						$page_links = paginate_links( array(
							'base'    => add_query_arg( 'paged', '%#%' ),
							'format'  => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'   => $total_pages,
							'current' => $current_page,
						) );
						echo $page_links;
						?>
					</div>
				<?php endif; ?>
			</div>

			<div style="overflow-x: auto;">
				<table class="wp-list-table widefat fixed striped dw-leads-table" id="dw-leads-table">
					<thead>
						<tr>
							<th style="width: 60px; position: relative;">
								ID
								<span class="dw-resize-handle"></span>
							</th>
							<th style="position: relative;">
								Nome
								<span class="dw-resize-handle"></span>
							</th>
							<th style="position: relative;">
								E-mail
								<span class="dw-resize-handle"></span>
							</th>
							<th style="position: relative;">
								Telefone
								<span class="dw-resize-handle"></span>
							</th>
							<th style="width: 100px; position: relative;">
								Contatos
								<span class="dw-resize-handle"></span>
							</th>
							<th style="width: 120px; position: relative;">
								É Cliente
								<span class="dw-resize-handle"></span>
							</th>
							<th style="width: 150px; position: relative;">
								Último Contato
								<span class="dw-resize-handle"></span>
							</th>
							<th style="width: 150px; position: relative;">
								Ações
								<span class="dw-resize-handle"></span>
							</th>
						</tr>
					</thead>
				<tbody>
					<?php if ( empty( $leads ) ) : ?>
						<tr>
							<td colspan="8" style="text-align: center; padding: 40px;">
								<p>Nenhum lead encontrado.</p>
							</td>
						</tr>
					<?php else : ?>
						<?php foreach ( $leads as $lead ) : ?>
							<tr>
								<td data-label="ID"><?php echo esc_html( $lead['id'] ); ?></td>
								<td data-label="Nome"><strong><?php echo esc_html( $lead['name'] ?: '-' ); ?></strong></td>
								<td data-label="E-mail"><?php echo esc_html( $lead['email'] ?: '-' ); ?></td>
								<td data-label="Telefone"><?php echo esc_html( $this->format_phone( $lead['phone'] ?? '' ) ); ?></td>
								<td data-label="Contatos">
									<span style="background: #25d366; color: white; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 13px;">
										<?php echo esc_html( $lead['contact_count'] ?? 1 ); ?>
									</span>
								</td>
								<td data-label="É Cliente">
									<?php if ( $lead['is_customer'] ) : ?>
										<span style="color: #25d366; font-weight: bold;">✓ Sim</span>
										<?php if ( $lead['customer_id'] ) : ?>
											<br><small><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $lead['customer_id'] ); ?>">Ver cliente</a></small>
										<?php endif; ?>
									<?php else : ?>
										<span style="color: #999;">Não</span>
									<?php endif; ?>
								</td>
								<td data-label="Último Contato">
									<?php
									$date_field = isset( $lead['last_contact'] ) ? $lead['last_contact'] : ( $lead['created_at'] ?? '' );
									if ( $date_field ) {
										$date = new DateTime( $date_field );
										echo esc_html( $date->format( 'd/m/Y H:i' ) );
									} else {
										echo '-';
									}
									?>
								</td>
								<td data-label="Ações">
									<button type="button" 
											class="button button-small dw-view-lead-btn" 
											data-lead-id="<?php echo esc_attr( $lead['id'] ); ?>"
											data-email="<?php echo esc_attr( $lead['email'] ?? '' ); ?>"
											data-phone="<?php echo esc_attr( $lead['phone'] ?? '' ); ?>">
										Ver Lead
									</button>
									<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-leads&action=delete&lead_id=' . $lead['id'] ), 'delete_lead_' . $lead['id'] ); ?>" 
									   class="button button-small" 
									   onclick="return confirm('Tem certeza que deseja excluir este lead?');">Excluir</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
			</div>

			<div class="tablenav bottom">
				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav-pages">
						<?php echo $page_links; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Modal para visualizar detalhes do lead -->
		<div id="dw-lead-modal-overlay" class="dw-lead-modal-overlay" style="display: none;">
			<div class="dw-lead-modal">
				<div class="dw-lead-modal-header">
					<h2>Detalhes do Lead</h2>
					<button type="button" class="dw-lead-modal-close" id="dw-lead-modal-close">&times;</button>
				</div>
				<div class="dw-lead-modal-body" id="dw-lead-modal-body">
					<div style="text-align: center; padding: 40px;">
						<span class="spinner is-active" style="float: none; margin: 0;"></span>
						<p>Carregando dados do lead...</p>
					</div>
				</div>
			</div>
		</div>
		
		<style>
		.dw-lead-modal-overlay {
			position: fixed;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.7);
			z-index: 100000;
			display: flex;
			align-items: center;
			justify-content: center;
			padding: 20px;
		}
		
		.dw-lead-modal {
			background: #fff;
			border-radius: 8px;
			max-width: 800px;
			width: 100%;
			max-height: 90vh;
			overflow: hidden;
			display: flex;
			flex-direction: column;
			box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
		}
		
		.dw-lead-modal-header {
			padding: 20px 25px;
			border-bottom: 1px solid #ddd;
			display: flex;
			justify-content: space-between;
			align-items: center;
			background: #25d366;
			color: white;
		}
		
		.dw-lead-modal-header h2 {
			margin: 0;
			font-size: 20px;
			font-weight: 600;
		}
		
		.dw-lead-modal-close {
			background: none;
			border: none;
			color: white;
			font-size: 28px;
			cursor: pointer;
			padding: 0;
			width: 30px;
			height: 30px;
			line-height: 1;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		
		.dw-lead-modal-close:hover {
			opacity: 0.8;
		}
		
		.dw-lead-modal-body {
			padding: 25px;
			overflow-y: auto;
			flex: 1;
		}
		
		.dw-lead-detail-row {
			display: grid;
			grid-template-columns: 200px 1fr;
			gap: 15px;
			padding: 15px 0;
			border-bottom: 1px solid #f0f0f0;
		}
		
		.dw-lead-detail-row:last-child {
			border-bottom: none;
		}
		
		.dw-lead-detail-label {
			font-weight: 600;
			color: #555;
		}
		
		.dw-lead-detail-value {
			color: #333;
		}
		
		.dw-lead-custom-fields {
			margin-top: 20px;
			padding-top: 20px;
			border-top: 2px solid #25d366;
		}
		
		.dw-lead-custom-fields h3 {
			margin: 0 0 15px 0;
			color: #25d366;
			font-size: 16px;
		}
		
		/* Tabela responsiva e redimensionável */
		.dw-leads-table {
			table-layout: fixed;
			min-width: 100%;
		}
		
		.dw-leads-table th {
			position: relative;
			user-select: none;
		}
		
		.dw-resize-handle {
			position: absolute;
			top: 0;
			right: -2px;
			width: 8px;
			height: 100%;
			cursor: col-resize;
			background: transparent;
			z-index: 10;
			transition: background 0.2s;
		}
		
		.dw-resize-handle:hover {
			background: rgba(37, 211, 102, 0.3);
		}
		
		.dw-resize-handle.active {
			background: #25d366;
		}
		
		.dw-leads-table th:hover .dw-resize-handle {
			background: rgba(37, 211, 102, 0.2);
		}
		
		.dw-leads-table th:last-child .dw-resize-handle {
			display: none;
		}
		
		@media (max-width: 782px) {
			.dw-leads-table {
				display: block;
				overflow-x: auto;
				-webkit-overflow-scrolling: touch;
			}
			
			.dw-leads-table thead {
				display: none;
			}
			
			.dw-leads-table tbody {
				display: block;
			}
			
			.dw-leads-table tr {
				display: block;
				border: 1px solid #ddd;
				margin-bottom: 15px;
				padding: 15px;
				background: #fff;
				border-radius: 4px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			
			.dw-leads-table td {
				display: block;
				border: none;
				position: relative;
				padding: 10px 0 10px 40% !important;
				text-align: left;
				border-bottom: 1px solid #f0f0f0;
			}
			
			.dw-leads-table td:last-child {
				border-bottom: none;
			}
			
			.dw-leads-table td:before {
				content: attr(data-label);
				position: absolute;
				left: 0;
				width: 35%;
				padding-right: 10px;
				white-space: nowrap;
				font-weight: 600;
				color: #555;
			}
			
			.dw-resize-handle {
				display: none;
			}
			
			.dw-leads-table td[data-label="Ações"] {
				padding-left: 0 !important;
				text-align: center;
			}
			
			.dw-leads-table td[data-label="Ações"]:before {
				display: none;
			}
			
			.dw-leads-table td[data-label="Ações"] .button {
				margin: 5px;
				display: inline-block;
			}
		}
		</style>
		
		<script>
		jQuery(document).ready(function($) {
			// Ensure ajaxurl is available
			if (typeof ajaxurl === 'undefined') {
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			}
			
			// Funcionalidade de redimensionar colunas
			var $table = $('#dw-leads-table');
			var $resizeHandles = $table.find('.dw-resize-handle');
			var isResizing = false;
			var currentColumn = null;
			var columnIndex = 0;
			var startX = 0;
			var startWidth = 0;
			
			$resizeHandles.on('mousedown', function(e) {
				e.preventDefault();
				e.stopPropagation();
				
				isResizing = true;
				currentColumn = $(this).closest('th');
				columnIndex = currentColumn.index();
				startX = e.pageX;
				startWidth = currentColumn.outerWidth();
				
				$(this).addClass('active');
				$('body').css('cursor', 'col-resize').css('user-select', 'none');
				
				$(document).on('mousemove.dwResize', function(e) {
					if (!isResizing) return;
					
					var diff = e.pageX - startX;
					var newWidth = startWidth + diff;
					
					if (newWidth > 50) { // Largura mínima
						// Aplicar largura no cabeçalho
						currentColumn.css('width', newWidth + 'px');
						
						// Aplicar largura nas células correspondentes
						$table.find('tbody tr').each(function() {
							$(this).find('td').eq(columnIndex).css('width', newWidth + 'px');
						});
					}
				});
				
				$(document).on('mouseup.dwResize', function() {
					if (isResizing) {
						isResizing = false;
						$resizeHandles.removeClass('active');
						$('body').css('cursor', '').css('user-select', '');
						$(document).off('mousemove.dwResize mouseup.dwResize');
					}
				});
			});
			
			$('.dw-view-lead-btn').on('click', function() {
				const leadId = $(this).data('lead-id');
				const email = $(this).data('email');
				const phone = $(this).data('phone');
				
				$('#dw-lead-modal-overlay').fadeIn(200);
				$('#dw-lead-modal-body').html('<div style="text-align: center; padding: 40px;"><span class="spinner is-active" style="float: none; margin: 0;"></span><p>Carregando dados do lead...</p></div>');
				
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'dw_whatsapp_get_lead_details',
						lead_id: leadId,
						email: email,
						phone: phone,
						nonce: '<?php echo wp_create_nonce( 'dw_whatsapp_get_lead_details' ); ?>'
					},
					success: function(response) {
						if (response.success && response.data) {
							const lead = response.data;
							let html = '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">ID:</div>';
							html += '<div class="dw-lead-detail-value">' + (lead.id || '-') + '</div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">Nome:</div>';
							html += '<div class="dw-lead-detail-value"><strong>' + (lead.name || '-') + '</strong></div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">E-mail:</div>';
							html += '<div class="dw-lead-detail-value">' + (lead.email || '-') + '</div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">Telefone:</div>';
							html += '<div class="dw-lead-detail-value">' + (lead.phone || '-') + '</div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">Total de Contatos:</div>';
							html += '<div class="dw-lead-detail-value"><span style="background: #25d366; color: white; padding: 4px 10px; border-radius: 12px; font-weight: bold;">' + (lead.contact_count || 1) + '</span></div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">É Cliente:</div>';
							html += '<div class="dw-lead-detail-value">' + (lead.is_customer ? '<span style="color: #25d366; font-weight: bold;">✓ Sim</span>' : '<span style="color: #999;">Não</span>') + '</div>';
							html += '</div>';
							
							if (lead.customer_id) {
								html += '<div class="dw-lead-detail-row">';
								html += '<div class="dw-lead-detail-label">ID do Cliente:</div>';
								html += '<div class="dw-lead-detail-value"><a href="' + lead.customer_url + '" target="_blank">' + lead.customer_id + '</a></div>';
								html += '</div>';
							}
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">Primeiro Contato:</div>';
							html += '<div class="dw-lead-detail-value">' + (lead.first_contact || '-') + '</div>';
							html += '</div>';
							
							html += '<div class="dw-lead-detail-row">';
							html += '<div class="dw-lead-detail-label">Último Contato:</div>';
							html += '<div class="dw-lead-detail-value"><strong>' + (lead.last_contact || '-') + '</strong></div>';
							html += '</div>';
							
							// Campos customizados
							if (lead.custom_fields && Object.keys(lead.custom_fields).length > 0) {
								html += '<div class="dw-lead-custom-fields">';
								html += '<h3>📋 Campos Personalizados</h3>';
								for (const [key, value] of Object.entries(lead.custom_fields)) {
									html += '<div class="dw-lead-detail-row">';
									html += '<div class="dw-lead-detail-label">' + (lead.custom_fields_labels[key] || key) + ':</div>';
									html += '<div class="dw-lead-detail-value">' + (value || '-') + '</div>';
									html += '</div>';
								}
								html += '</div>';
							}
							
							$('#dw-lead-modal-body').html(html);
						} else {
							$('#dw-lead-modal-body').html('<div style="text-align: center; padding: 40px; color: #d63638;"><p>Erro ao carregar dados do lead.</p></div>');
						}
					},
					error: function() {
						$('#dw-lead-modal-body').html('<div style="text-align: center; padding: 40px; color: #d63638;"><p>Erro ao carregar dados do lead. Tente novamente.</p></div>');
					}
				});
			});
			
			$('#dw-lead-modal-close, #dw-lead-modal-overlay').on('click', function(e) {
				if (e.target === this) {
					$('#dw-lead-modal-overlay').fadeOut(200);
				}
			});
			
			$(document).on('keydown', function(e) {
				if (e.key === 'Escape' && $('#dw-lead-modal-overlay').is(':visible')) {
					$('#dw-lead-modal-overlay').fadeOut(200);
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Render quotes page
	 */
	public function render_quotes_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'dw-whatsapp' ) );
		}

		// Handle delete
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['quote_id'] ) ) {
			check_admin_referer( 'delete_quote_' . $_GET['quote_id'] );
			DW_WhatsApp_Quotes::delete_quote( absint( $_GET['quote_id'] ) );
			echo '<div class="notice notice-success is-dismissible"><p>Cotação excluída com sucesso!</p></div>';
		}

		$per_page = 20;
		$current_page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

		$args = array(
			'per_page' => $per_page,
			'page'     => $current_page,
			'search'   => $search,
		);

		$quotes = DW_WhatsApp_Quotes::get_quotes( $args );
		$total = DW_WhatsApp_Quotes::get_total_quotes( $search );
		$total_pages = ceil( $total / $per_page );

		// View single quote
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'view' && isset( $_GET['quote_id'] ) ) {
			$quote = DW_WhatsApp_Quotes::get_quote( absint( $_GET['quote_id'] ) );
			?>
			<div class="wrap">
				<h1>Cotação #<?php echo esc_html( absint( $_GET['quote_id'] ) ); ?></h1>
				<p><a href="<?php echo esc_url( admin_url( 'admin.php?page=dw-whatsapp-quotes' ) ); ?>" class="button">&larr; Voltar</a></p>
				<?php if ( empty( $quote ) ) : ?>
					<div class="notice notice-error"><p>Cotação não encontrada.</p></div>
				<?php else : ?>
					<?php
					$cart = json_decode( $quote['cart_contents'] ?? '', true );
					$totals = json_decode( $quote['totals'] ?? '', true );
					?>
					<table class="widefat striped" style="max-width: 900px;">
						<tbody>
							<tr><th style="width: 220px;">Data</th><td><?php echo esc_html( $quote['created_at'] ?? '-' ); ?></td></tr>
							<tr><th>Cliente</th><td><?php echo esc_html( $quote['customer_name'] ?: '-' ); ?></td></tr>
							<tr><th>E-mail</th><td><?php echo esc_html( $quote['customer_email'] ?: '-' ); ?></td></tr>
							<tr><th>Telefone</th><td><?php echo esc_html( $quote['customer_phone'] ?: '-' ); ?></td></tr>
							<tr><th>Moeda</th><td><?php echo esc_html( $quote['currency'] ?: '-' ); ?></td></tr>
							<tr><th>IP</th><td><?php echo esc_html( $quote['ip'] ?: '-' ); ?></td></tr>
						</tbody>
					</table>

					<h2 style="margin-top: 25px;">Itens</h2>
					<table class="widefat striped" style="max-width: 900px;">
						<thead>
							<tr>
								<th>Produto</th>
								<th style="width: 90px;">Qtd</th>
								<th style="width: 140px;">Total</th>
							</tr>
						</thead>
						<tbody>
							<?php if ( empty( $cart['items'] ) ) : ?>
								<tr><td colspan="3">—</td></tr>
							<?php else : ?>
								<?php foreach ( $cart['items'] as $item ) : ?>
									<tr>
										<td><?php echo esc_html( $item['name'] ?? '-' ); ?></td>
										<td><?php echo esc_html( $item['qty'] ?? 0 ); ?></td>
										<td><?php echo esc_html( $item['line_total'] ?? '-' ); ?></td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>

					<h2 style="margin-top: 25px;">Totais</h2>
					<table class="widefat striped" style="max-width: 900px;">
						<tbody>
							<tr><th style="width: 220px;">Subtotal</th><td><?php echo esc_html( $totals['subtotal'] ?? '-' ); ?></td></tr>
							<tr><th>Frete</th><td><?php echo esc_html( $totals['shipping'] ?? '-' ); ?></td></tr>
							<tr><th>Descontos</th><td><?php echo esc_html( $totals['discount'] ?? '-' ); ?></td></tr>
							<tr><th>Total</th><td><strong><?php echo esc_html( $totals['total'] ?? '-' ); ?></strong></td></tr>
						</tbody>
					</table>
				<?php endif; ?>
			</div>
			<?php
			return;
		}

		?>
		<div class="wrap">
			<h1>Cotações (Carrinho via WhatsApp)</h1>

			<div style="margin: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
				<form method="get" style="flex: 1; min-width: 300px;">
					<input type="hidden" name="page" value="dw-whatsapp-quotes">
					<p class="search-box" style="margin: 0;">
						<label class="screen-reader-text" for="quote-search-input">Buscar cotações:</label>
						<input type="search" id="quote-search-input" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Buscar por nome, e-mail ou telefone..." style="width: 300px;">
						<input type="submit" class="button" value="Buscar">
						<?php if ( $search ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=dw-whatsapp-quotes' ) ); ?>" class="button">Limpar</a>
						<?php endif; ?>
					</p>
				</form>
				<div class="alignleft actions">
					<span class="displaying-num"><?php echo esc_html( $total ); ?> cotação(ões)</span>
				</div>
			</div>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav top">
					<div class="tablenav-pages">
						<?php
						echo paginate_links( array(
							'base'      => add_query_arg( 'paged', '%#%' ),
							'format'    => '',
							'prev_text' => '&laquo;',
							'next_text' => '&raquo;',
							'total'     => $total_pages,
							'current'   => $current_page,
						) );
						?>
					</div>
				</div>
			<?php endif; ?>

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 70px;">ID</th>
						<th>Cliente</th>
						<th>E-mail</th>
						<th>Telefone</th>
						<th style="width: 170px;">Data</th>
						<th style="width: 140px;">Total</th>
						<th style="width: 170px;">Ações</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $quotes ) ) : ?>
						<tr><td colspan="7" style="text-align:center; padding: 30px;">Nenhuma cotação encontrada.</td></tr>
					<?php else : ?>
						<?php foreach ( $quotes as $q ) : ?>
							<?php
							$totals = json_decode( $q['totals'] ?? '', true );
							$total_txt = is_array( $totals ) && ! empty( $totals['total'] ) ? $totals['total'] : '-';
							?>
							<tr>
								<td><?php echo esc_html( $q['id'] ); ?></td>
								<td><strong><?php echo esc_html( $q['customer_name'] ?: '-' ); ?></strong></td>
								<td><?php echo esc_html( $q['customer_email'] ?: '-' ); ?></td>
								<td><?php echo esc_html( $q['customer_phone'] ?: '-' ); ?></td>
								<td><?php echo esc_html( $q['created_at'] ?? '-' ); ?></td>
								<td><?php echo esc_html( $total_txt ); ?></td>
								<td>
									<a class="button button-small" href="<?php echo esc_url( admin_url( 'admin.php?page=dw-whatsapp-quotes&action=view&quote_id=' . absint( $q['id'] ) ) ); ?>">Ver</a>
									<a class="button button-small" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-quotes&action=delete&quote_id=' . absint( $q['id'] ) ), 'delete_quote_' . absint( $q['id'] ) ) ); ?>" onclick="return confirm('Tem certeza que deseja excluir esta cotação?');">Excluir</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Export leads to CSV or Excel
	 *
	 * @param string $format Export format (export_csv or export_excel).
	 */
	private function export_leads( $format ) {
		// Limpar todos os buffers de saída
		while ( ob_get_level() ) {
			ob_end_clean();
		}
		
		// Desabilitar qualquer cache
		nocache_headers();
		
		$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
		$leads = DW_WhatsApp_Leads::get_all_leads_for_export( $search );

		if ( $format === 'export_excel' ) {
			header( 'Content-Type: application/vnd.ms-excel; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="leads-whatsapp-' . date( 'Y-m-d' ) . '.xls"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
		} else {
			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="leads-whatsapp-' . date( 'Y-m-d' ) . '.csv"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			
			echo "\xEF\xBB\xBF"; // UTF-8 BOM para CSV
		}

		// Get custom fields
		$custom_fields = DW_WhatsApp_Custom_Fields::get_all_fields();
		
		// Headers
		$headers = array(
			'ID',
			'Nome',
			'E-mail',
			'Telefone',
			'Contatos',
			'É Cliente',
			'ID Cliente',
			'Primeiro Contato',
			'Último Contato',
		);
		
		// Add custom fields to headers
		foreach ( $custom_fields as $field ) {
			$headers[] = $field['field_label'];
		}

		if ( $format === 'export_excel' ) {
			// Gerar XML SpreadsheetML (formato Excel 2003+)
			echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
			echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
			echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
			echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
			echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
			echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
			echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
			echo '<Worksheet ss:Name="Leads">' . "\n";
			echo '<Table>' . "\n";
			
			// Cabeçalho
			echo '<Row ss:StyleID="Header">' . "\n";
			foreach ( $headers as $header ) {
				echo '<Cell><Data ss:Type="String">' . esc_html( $header ) . '</Data></Cell>' . "\n";
			}
			echo '</Row>' . "\n";

			// Dados
			foreach ( $leads as $lead ) {
				echo '<Row>' . "\n";
				
				$first_date = isset( $lead['first_contact'] ) ? $lead['first_contact'] : ( $lead['created_at'] ?? '' );
				$first_formatted = $first_date ? ( new DateTime( $first_date ) )->format( 'd/m/Y H:i' ) : '-';
				
				$last_date = isset( $lead['last_contact'] ) ? $lead['last_contact'] : ( $lead['created_at'] ?? '' );
				$last_formatted = $last_date ? ( new DateTime( $last_date ) )->format( 'd/m/Y H:i' ) : '-';
				
				// Get custom field values for this lead (use email or phone to find most recent)
				$lead_custom_fields = DW_WhatsApp_Custom_Fields::get_lead_fields_by_contact( $lead['email'] ?? '', $lead['phone'] ?? '' );
				
				// ID
				echo '<Cell><Data ss:Type="Number">' . esc_html( $lead['id'] ) . '</Data></Cell>' . "\n";
				// Nome
				echo '<Cell><Data ss:Type="String">' . esc_html( $lead['name'] ?: '-' ) . '</Data></Cell>' . "\n";
				// E-mail
				echo '<Cell><Data ss:Type="String">' . esc_html( $lead['email'] ?: '-' ) . '</Data></Cell>' . "\n";
				// Telefone
				echo '<Cell><Data ss:Type="String">' . esc_html( $this->format_phone( $lead['phone'] ?? '' ) ) . '</Data></Cell>' . "\n";
				// Contatos
				echo '<Cell><Data ss:Type="Number">' . esc_html( $lead['contact_count'] ?? 1 ) . '</Data></Cell>' . "\n";
				// É Cliente
				echo '<Cell><Data ss:Type="String">' . ( $lead['is_customer'] ? 'Sim' : 'Não' ) . '</Data></Cell>' . "\n";
				// ID Cliente
				echo '<Cell><Data ss:Type="String">' . ( $lead['customer_id'] ?: '-' ) . '</Data></Cell>' . "\n";
				// Primeiro Contato
				echo '<Cell><Data ss:Type="String">' . esc_html( $first_formatted ) . '</Data></Cell>' . "\n";
				// Último Contato
				echo '<Cell><Data ss:Type="String">' . esc_html( $last_formatted ) . '</Data></Cell>' . "\n";
				
				// Custom fields
				foreach ( $custom_fields as $field ) {
					$field_value = isset( $lead_custom_fields[ $field['field_key'] ] ) ? $lead_custom_fields[ $field['field_key'] ] : '-';
					echo '<Cell><Data ss:Type="String">' . esc_html( $field_value ) . '</Data></Cell>' . "\n";
				}
				
				echo '</Row>' . "\n";
			}
			
			echo '</Table>' . "\n";
			
			// Estilos
			echo '<Styles>' . "\n";
			echo '<Style ss:ID="Header">' . "\n";
			echo '<Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
			echo '<Interior ss:Color="#25d366" ss:Pattern="Solid"/>' . "\n";
			echo '</Style>' . "\n";
			echo '</Styles>' . "\n";
			
			echo '</Worksheet>' . "\n";
			echo '</Workbook>';
		} else {
			$output = fopen( 'php://output', 'w' );
			fputcsv( $output, $headers, ';' );

			foreach ( $leads as $lead ) {
				$first_date = isset( $lead['first_contact'] ) ? $lead['first_contact'] : ( $lead['created_at'] ?? '' );
				$first_formatted = $first_date ? ( new DateTime( $first_date ) )->format( 'd/m/Y H:i' ) : '-';
				
				$last_date = isset( $lead['last_contact'] ) ? $lead['last_contact'] : ( $lead['created_at'] ?? '' );
				$last_formatted = $last_date ? ( new DateTime( $last_date ) )->format( 'd/m/Y H:i' ) : '-';

				// Get custom field values for this lead (use email or phone to find most recent)
				$lead_custom_fields = DW_WhatsApp_Custom_Fields::get_lead_fields_by_contact( $lead['email'] ?? '', $lead['phone'] ?? '' );
				
				$row_data = array(
					$lead['id'],
					$lead['name'] ?: '-',
					$lead['email'] ?: '-',
					$this->format_phone( $lead['phone'] ?? '' ),
					$lead['contact_count'] ?? 1,
					$lead['is_customer'] ? 'Sim' : 'Não',
					$lead['customer_id'] ?: '-',
					$first_formatted,
					$last_formatted,
				);
				
				// Add custom fields values
				foreach ( $custom_fields as $field ) {
					$field_value = isset( $lead_custom_fields[ $field['field_key'] ] ) ? $lead_custom_fields[ $field['field_key'] ] : '-';
					$row_data[] = $field_value;
				}
				
				fputcsv( $output, $row_data, ';' );
			}

			fclose( $output );
		}

		exit;
	}

	/**
	 * Save custom field
	 */
	private function save_custom_field() {
		$field_key = isset( $_POST['field_key'] ) ? sanitize_key( $_POST['field_key'] ) : '';
		$field_label = isset( $_POST['field_label'] ) ? sanitize_text_field( $_POST['field_label'] ) : '';
		$field_type = isset( $_POST['field_type'] ) ? sanitize_text_field( $_POST['field_type'] ) : 'text';
		$field_options = isset( $_POST['field_options'] ) ? sanitize_textarea_field( $_POST['field_options'] ) : '';
		$is_required = isset( $_POST['is_required'] ) ? 1 : 0;
		$show_in_whatsapp = isset( $_POST['show_in_whatsapp'] ) ? 1 : 0;
		$field_order = isset( $_POST['field_order'] ) ? absint( $_POST['field_order'] ) : 0;

		// Generate field_key from label if not provided
		if ( empty( $field_key ) && ! empty( $field_label ) ) {
			$field_key = sanitize_key( $field_label );
		}

		// Parse options for select field
		$options_array = array();
		if ( $field_type === 'select' && ! empty( $field_options ) ) {
			$options_lines = explode( "\n", $field_options );
			foreach ( $options_lines as $line ) {
				$line = trim( $line );
				if ( ! empty( $line ) ) {
					$options_array[] = $line;
				}
			}
		}

		$data = array(
			'field_key'        => $field_key,
			'field_label'      => $field_label,
			'field_type'       => $field_type,
			'field_options'    => $field_type === 'select' ? $options_array : $field_options,
			'is_required'      => $is_required,
			'show_in_whatsapp' => $show_in_whatsapp,
			'field_order'      => $field_order,
		);

		$result = DW_WhatsApp_Custom_Fields::save_field( $data );

		if ( $result ) {
			wp_redirect( admin_url( 'admin.php?page=dw-whatsapp-custom-fields&saved=1' ) );
			exit;
		} else {
			wp_redirect( admin_url( 'admin.php?page=dw-whatsapp-custom-fields&error=1' ) );
			exit;
		}
	}

	/**
	 * Delete custom field
	 *
	 * @param string $field_key Field key.
	 */
	private function delete_custom_field( $field_key ) {
		DW_WhatsApp_Custom_Fields::delete_field( $field_key );
	}

	/**
	 * AJAX handler to get lead details
	 */
	public function ajax_get_lead_details() {
		check_ajax_referer( 'dw_whatsapp_get_lead_details', 'nonce' );

		$lead_id = isset( $_POST['lead_id'] ) ? absint( $_POST['lead_id'] ) : 0;
		$email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';

		if ( ! $lead_id && empty( $email ) && empty( $phone ) ) {
			wp_send_json_error( array( 'message' => 'Dados inválidos' ) );
		}

		// Get lead data - always use grouped data for consistency
		$leads = DW_WhatsApp_Leads::get_all_leads_for_export( '' );
		$lead_data = array();
		
		foreach ( $leads as $lead ) {
			$match = false;
			
			if ( $lead_id && $lead['id'] == $lead_id ) {
				$match = true;
			} elseif ( ! empty( $email ) && ! empty( $lead['email'] ) && $lead['email'] === $email ) {
				$match = true;
			} elseif ( ! empty( $phone ) && ! empty( $lead['phone'] ) ) {
				$lead_phone_clean = preg_replace( '/[^0-9]/', '', $lead['phone'] );
				$phone_clean = preg_replace( '/[^0-9]/', '', $phone );
				if ( $lead_phone_clean === $phone_clean ) {
					$match = true;
				}
			}
			
			if ( $match ) {
				$lead_data = $lead;
				break;
			}
		}

		if ( empty( $lead_data ) ) {
			wp_send_json_error( array( 'message' => 'Lead não encontrado' ) );
		}

		// Format dates
		$first_date = isset( $lead_data['first_contact'] ) ? $lead_data['first_contact'] : ( $lead_data['created_at'] ?? '' );
		$first_formatted = $first_date ? ( new DateTime( $first_date ) )->format( 'd/m/Y H:i' ) : '-';
		
		$last_date = isset( $lead_data['last_contact'] ) ? $lead_data['last_contact'] : ( $lead_data['created_at'] ?? '' );
		$last_formatted = $last_date ? ( new DateTime( $last_date ) )->format( 'd/m/Y H:i' ) : '-';

		// Get custom fields
		$custom_fields = DW_WhatsApp_Custom_Fields::get_lead_fields_by_contact( $lead_data['email'] ?? '', $lead_data['phone'] ?? '' );
		$custom_fields_labels = array();
		
		if ( ! empty( $custom_fields ) ) {
			$all_fields = DW_WhatsApp_Custom_Fields::get_all_fields();
			foreach ( $all_fields as $field ) {
				if ( isset( $custom_fields[ $field['field_key'] ] ) ) {
					$custom_fields_labels[ $field['field_key'] ] = $field['field_label'];
				}
			}
		}

		$response = array(
			'id' => $lead_data['id'] ?? 0,
			'name' => $lead_data['name'] ?? '-',
			'email' => $lead_data['email'] ?? '-',
			'phone' => $this->format_phone( $lead_data['phone'] ?? '' ),
			'contact_count' => $lead_data['contact_count'] ?? 1,
			'is_customer' => ! empty( $lead_data['is_customer'] ),
			'customer_id' => $lead_data['customer_id'] ?? null,
			'customer_url' => ! empty( $lead_data['customer_id'] ) ? admin_url( 'user-edit.php?user_id=' . $lead_data['customer_id'] ) : '',
			'first_contact' => $first_formatted,
			'last_contact' => $last_formatted,
			'custom_fields' => $custom_fields,
			'custom_fields_labels' => $custom_fields_labels,
		);

		wp_send_json_success( $response );
	}

	/**
	 * Render custom fields page
	 */
	public function render_custom_fields_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Você não tem permissão para acessar esta página.', 'dw-whatsapp' ) );
		}

		$fields = DW_WhatsApp_Custom_Fields::get_all_fields();
		$editing_field = null;
		
		if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['field_key'] ) ) {
			$editing_field = DW_WhatsApp_Custom_Fields::get_field( sanitize_key( $_GET['field_key'] ) );
		}

		include DW_WHATSAPP_PATH . 'admin/views/custom-fields-page.php';
	}
}



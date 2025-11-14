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
	}

	/**
	 * Enqueue admin scripts
	 *
	 * @param string $hook Page hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'toplevel_page_dw-whatsapp' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_add_inline_script( 'wp-color-picker', '
			jQuery(document).ready(function($) {
				$(".dw-color-picker").wpColorPicker();
			});
		' );
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

			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th style="width: 60px;">ID</th>
						<th>Nome</th>
						<th>E-mail</th>
						<th>Telefone</th>
						<th style="width: 100px;">Contatos</th>
						<th style="width: 120px;">É Cliente</th>
						<th style="width: 150px;">Último Contato</th>
						<th style="width: 100px;">Ações</th>
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
								<td><?php echo esc_html( $lead['id'] ); ?></td>
								<td><strong><?php echo esc_html( $lead['name'] ?: '-' ); ?></strong></td>
								<td><?php echo esc_html( $lead['email'] ?: '-' ); ?></td>
								<td><?php echo esc_html( $this->format_phone( $lead['phone'] ?? '' ) ); ?></td>
								<td>
									<span style="background: #25d366; color: white; padding: 4px 10px; border-radius: 12px; font-weight: bold; font-size: 13px;">
										<?php echo esc_html( $lead['contact_count'] ?? 1 ); ?>
									</span>
								</td>
								<td>
									<?php if ( $lead['is_customer'] ) : ?>
										<span style="color: #25d366; font-weight: bold;">✓ Sim</span>
										<?php if ( $lead['customer_id'] ) : ?>
											<br><small><a href="<?php echo admin_url( 'user-edit.php?user_id=' . $lead['customer_id'] ); ?>">Ver cliente</a></small>
										<?php endif; ?>
									<?php else : ?>
										<span style="color: #999;">Não</span>
									<?php endif; ?>
								</td>
								<td>
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
								<td>
									<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-leads&action=delete&lead_id=' . $lead['id'] ), 'delete_lead_' . $lead['id'] ); ?>" 
									   class="button button-small" 
									   onclick="return confirm('Tem certeza que deseja excluir este lead?');">Excluir</a>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>

			<div class="tablenav bottom">
				<?php if ( $total_pages > 1 ) : ?>
					<div class="tablenav-pages">
						<?php echo $page_links; ?>
					</div>
				<?php endif; ?>
			</div>
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

				fputcsv( $output, array(
					$lead['id'],
					$lead['name'] ?: '-',
					$lead['email'] ?: '-',
					$this->format_phone( $lead['phone'] ?? '' ),
					$lead['contact_count'] ?? 1,
					$lead['is_customer'] ? 'Sim' : 'Não',
					$lead['customer_id'] ?: '-',
					$first_formatted,
					$last_formatted,
				), ';' );
			}

			fclose( $output );
		}

		exit;
	}
}



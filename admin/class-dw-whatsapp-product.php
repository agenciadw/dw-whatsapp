<?php
/**
 * Product admin functionality
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Product {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp_Product
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp_Product
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
		// Adicionar metabox
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		
		// Salvar metabox
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_metabox' ) );
		
		// Adicionar coluna na lista de produtos
		add_filter( 'manage_product_posts_columns', array( $this, 'add_product_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_product_column' ), 10, 2 );
	}

	/**
	 * Add metabox
	 */
	public function add_metabox() {
		add_meta_box(
			'dw_whatsapp_product_attendant',
			'WhatsApp - Atendente Responsável',
			array( $this, 'render_metabox' ),
			'product',
			'side',
			'default'
		);
	}

	/**
	 * Render metabox
	 */
	public function render_metabox( $post ) {
		// Verificar se múltiplos usuários está ativo
		$multi_users_enabled = DW_WhatsApp_Settings::get( 'multi_users_enabled' );
		$users = DW_WhatsApp_Settings::get( 'multi_users', array() );
		
		// Valor atual
		$selected_attendant = get_post_meta( $post->ID, '_dw_whatsapp_attendant', true );
		
		// Nonce
		wp_nonce_field( 'dw_whatsapp_product_nonce', 'dw_whatsapp_product_nonce' );
		
		if ( $multi_users_enabled === 'yes' && ! empty( $users ) ) {
			?>
			<div style="margin-bottom: 15px;">
				<label for="dw_whatsapp_attendant" style="display: block; margin-bottom: 8px; font-weight: 600;">
					Selecione o atendente:
				</label>
				<select name="dw_whatsapp_attendant" id="dw_whatsapp_attendant" style="width: 100%;">
					<option value="">Usar número padrão</option>
					<?php foreach ( $users as $user ) : ?>
						<?php
						$phone = preg_replace( '/[^0-9]/', '', $user['phone'] ?? '' );
						$name = esc_html( $user['name'] );
						$department = ! empty( $user['department'] ) ? ' - ' . esc_html( $user['department'] ) : '';
						
						// Verificar status baseado em horário (se auto_status ativo)
						$current_status = DW_WhatsApp_Schedule::get_current_status( $user );
						
						$status_icon = '';
						if ( $current_status === 'online' ) {
							$status_icon = ' ✓';
						} elseif ( $current_status === 'away' ) {
							$status_icon = ' ⏰';
						} else {
							$status_icon = ' ⭕';
						}
						?>
						<option value="<?php echo esc_attr( $phone ); ?>" <?php selected( $selected_attendant, $phone ); ?>>
							<?php echo $name . $department . $status_icon; ?>
						</option>
					<?php endforeach; ?>
				</select>
				<p class="description" style="margin-top: 8px;">
					Este produto será direcionado para o atendente selecionado. Se nenhum for selecionado, usará o número principal.
				</p>
			</div>
			
			<?php if ( ! empty( $selected_attendant ) ) : ?>
				<div style="padding: 10px; background: #e7f7ed; border-left: 3px solid #25d366; margin-top: 10px;">
					<strong>✓ Atendente Configurado</strong><br>
					<small>Telefone: <?php echo esc_html( $selected_attendant ); ?></small>
				</div>
			<?php endif; ?>
			<?php
		} else {
			?>
			<div style="padding: 15px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 3px;">
				<p style="margin: 0;">
					<strong>⚠️ Sistema de múltiplos usuários desativado</strong>
				</p>
				<p style="margin: 10px 0 0 0; font-size: 12px;">
					Para atribuir atendentes específicos aos produtos, ative o sistema de múltiplos usuários em 
					<a href="<?php echo admin_url( 'admin.php?page=dw-whatsapp' ); ?>">Configurações do DW WhatsApp</a>.
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Save metabox
	 */
	public function save_metabox( $post_id ) {
		// Verificar nonce
		if ( ! isset( $_POST['dw_whatsapp_product_nonce'] ) || ! wp_verify_nonce( $_POST['dw_whatsapp_product_nonce'], 'dw_whatsapp_product_nonce' ) ) {
			return;
		}

		// Verificar autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Verificar permissões
		if ( ! current_user_can( 'edit_product', $post_id ) ) {
			return;
		}

		// Salvar ou deletar meta
		if ( isset( $_POST['dw_whatsapp_attendant'] ) ) {
			$attendant = sanitize_text_field( $_POST['dw_whatsapp_attendant'] );
			
			if ( ! empty( $attendant ) ) {
				update_post_meta( $post_id, '_dw_whatsapp_attendant', $attendant );
			} else {
				delete_post_meta( $post_id, '_dw_whatsapp_attendant' );
			}
		}
	}

	/**
	 * Add column to products list
	 */
	public function add_product_column( $columns ) {
		// Adicionar coluna antes da coluna de data
		$new_columns = array();
		
		foreach ( $columns as $key => $value ) {
			if ( $key === 'date' ) {
				$new_columns['dw_whatsapp'] = '<span class="dashicons dashicons-whatsapp" title="Atendente WhatsApp"></span>';
			}
			$new_columns[ $key ] = $value;
		}
		
		return $new_columns;
	}

	/**
	 * Render column content
	 */
	public function render_product_column( $column, $post_id ) {
		if ( $column === 'dw_whatsapp' ) {
			$attendant_phone = get_post_meta( $post_id, '_dw_whatsapp_attendant', true );
			
			if ( ! empty( $attendant_phone ) ) {
				// Buscar nome do atendente
				$users = DW_WhatsApp_Settings::get( 'multi_users', array() );
				$attendant_name = '';
				
				foreach ( $users as $user ) {
					$phone = preg_replace( '/[^0-9]/', '', $user['phone'] ?? '' );
					if ( $phone === $attendant_phone ) {
						$attendant_name = $user['name'];
						break;
					}
				}
				
				if ( $attendant_name ) {
					echo '<span style="color: #25d366; font-weight: 600;" title="Atendente: ' . esc_attr( $attendant_name ) . '">';
					echo esc_html( $attendant_name );
					echo '</span>';
				} else {
					echo '<span style="color: #999;" title="Telefone: ' . esc_attr( $attendant_phone ) . '">Custom</span>';
				}
			} else {
				echo '<span style="color: #999;" title="Usando número padrão">—</span>';
			}
		}
	}
}


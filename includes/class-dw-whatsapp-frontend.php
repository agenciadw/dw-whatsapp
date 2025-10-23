<?php
/**
 * Frontend functionality
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DW_WhatsApp_Frontend {

	/**
	 * Single instance
	 *
	 * @var DW_WhatsApp_Frontend
	 */
	private static $instance = null;

	/**
	 * Get instance
	 *
	 * @return DW_WhatsApp_Frontend
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
		$this->init_hooks();
	}

	/**
	 * Initialize hooks
	 */
	private function init_hooks() {
		// Product page
		if ( DW_WhatsApp_Settings::get( 'show_on_product_page' ) === 'yes' ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_product_button' ), 999 );
		}

		// Product loop
		if ( DW_WhatsApp_Settings::get( 'show_on_product_loop' ) === 'yes' ) {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'render_loop_button' ), 999, 2 );
		}

		// Floating button
		if ( DW_WhatsApp_Settings::get( 'show_floating_button' ) === 'yes' ) {
			add_action( 'wp_footer', array( $this, 'render_floating_button' ) );
		}

		// Product hooks
		add_filter( 'woocommerce_is_purchasable', array( $this, 'set_unpurchasable_if_no_price' ), 1000, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'modify_price_html' ), 1000, 2 );
		add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'modify_price_html' ), 1000, 2 );
		add_filter( 'woocommerce_variable_price_html', array( $this, 'modify_price_html' ), 1000, 2 );

		// Scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		// Enqueue CSS
		wp_enqueue_style(
			'dw-whatsapp-frontend',
			DW_WHATSAPP_URL . 'assets/css/frontend.css',
			array(),
			DW_WHATSAPP_VERSION
		);

		// Enqueue JS apenas em páginas de produto
		if ( ! is_product() || DW_WhatsApp_Settings::get( 'include_variations' ) !== 'yes' ) {
			return;
		}

		wp_enqueue_script(
			'dw-whatsapp-variations',
			DW_WHATSAPP_URL . 'assets/js/variations.js',
			array( 'jquery' ),
			DW_WHATSAPP_VERSION,
			true
		);

		wp_localize_script( 'dw-whatsapp-variations', 'dwWhatsApp', array(
			'phone'                   => preg_replace( '/[^0-9]/', '', DW_WhatsApp_Settings::get( 'phone_number' ) ),
			'messageTemplate'         => DW_WhatsApp_Settings::get( 'message_with_price' ),
			'messageTemplateNoPrice'  => DW_WhatsApp_Settings::get( 'message_without_price' ),
			'includeLink'             => DW_WhatsApp_Settings::get( 'include_product_link' ) === 'yes',
		) );
	}

	/**
	 * Render product button
	 */
	public function render_product_button() {
		global $product;

		if ( ! $product ) {
			return;
		}

		if ( $this->is_product_without_price( $product ) ) {
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
		}

		// Buscar atendente do produto
		$attendant = $this->get_product_attendant( $product );
		
		// Gerar link com o atendente específico ou número padrão
		if ( ! empty( $attendant['phone'] ) ) {
			$link = $this->generate_whatsapp_link_with_phone( $product, $attendant['phone'] );
		} else {
			$link = $this->generate_whatsapp_link( $product );
		}

		// Texto do botão com nome do atendente (se houver)
		$text = $this->is_product_without_price( $product ) 
			? DW_WhatsApp_Settings::get( 'button_text_without_price', 'Solicitar Orçamento' )
			: DW_WhatsApp_Settings::get( 'button_text_with_price', 'Comprar via WhatsApp' );
		
		// Adicionar nome do atendente ao texto
		if ( ! empty( $attendant['name'] ) ) {
			$text .= ' com ' . $attendant['name'];
		}

		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );

		echo '<div class="dw-whatsapp-wrapper" style="margin-top: 20px;">';
		echo '<a href="' . esc_url( $link ) . '" target="_blank" class="dw-whatsapp-button single_add_to_cart_button button alt" data-product-id="' . esc_attr( $product->get_id() ) . '" data-product-name="' . esc_attr( $product->get_name() ) . '" data-product-link="' . esc_url( get_permalink() ) . '" data-is-variable="' . ( $product->is_type( 'variable' ) ? '1' : '0' ) . '" style="display: inline-flex !important; align-items: center; justify-content: center; width: 100%; padding: 15px !important; font-size: 16px !important; background-color: ' . $color . ' !important; color: white !important; text-decoration: none; border-radius: 5px; gap: 10px;">';
		echo $this->get_whatsapp_icon();
		echo esc_html( $text );
		echo '</a>';
		echo '</div>';
	}

	/**
	 * Generate WhatsApp link with specific phone
	 */
	private function generate_whatsapp_link_with_phone( $product, $phone ) {
		$template = $this->is_product_without_price( $product )
			? DW_WhatsApp_Settings::get( 'message_without_price' )
			: DW_WhatsApp_Settings::get( 'message_with_price' );

		$message = str_replace( '{product_name}', $product->get_name(), $template );

		if ( DW_WhatsApp_Settings::get( 'include_product_link' ) === 'yes' ) {
			$message .= ' - Link: ' . get_permalink( $product->get_id() );
		}

		return 'https://wa.me/' . $phone . '?text=' . rawurlencode( $message );
	}

	/**
	 * Get attendant name for product
	 *
	 * @param WC_Product $product Product object.
	 * @return array Array with 'phone' and 'name' keys.
	 */
	private function get_product_attendant( $product ) {
		$assigned_attendant = get_post_meta( $product->get_id(), '_dw_whatsapp_attendant', true );
		$attendant_name = '';
		
		if ( ! empty( $assigned_attendant ) ) {
			// Buscar nome do atendente
			$users = DW_WhatsApp_Settings::get( 'multi_users', array() );
			foreach ( $users as $user ) {
				$phone = preg_replace( '/[^0-9]/', '', $user['phone'] ?? '' );
				if ( $phone === $assigned_attendant ) {
					$attendant_name = $user['name'];
					break;
				}
			}
		}
		
		return array(
			'phone' => $assigned_attendant,
			'name'  => $attendant_name,
		);
	}

	/**
	 * Render loop button
	 *
	 * @param string     $html Button HTML.
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function render_loop_button( $html, $product ) {
		// Buscar atendente do produto
		$attendant = $this->get_product_attendant( $product );
		
		// Gerar link com o atendente específico ou número padrão
		if ( ! empty( $attendant['phone'] ) ) {
			$link = $this->generate_whatsapp_link_with_phone( $product, $attendant['phone'] );
		} else {
			$link = $this->generate_whatsapp_link( $product );
		}

		// Texto do botão com nome do atendente (se houver)
		$text = $this->is_product_without_price( $product )
			? DW_WhatsApp_Settings::get( 'button_text_without_price', 'Solicitar Orçamento' )
			: DW_WhatsApp_Settings::get( 'button_text_with_price', 'Comprar via WhatsApp' );
		
		// Adicionar nome do atendente ao texto
		if ( ! empty( $attendant['name'] ) ) {
			$text .= ' com ' . $attendant['name'];
		}

		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );

		$button = '<a href="' . esc_url( $link ) . '" target="_blank" class="dw-whatsapp-button-loop button" style="background-color: ' . $color . '; color: white; width: 100%; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: 8px; border-color: ' . $color . ';">';
		$button .= $this->get_whatsapp_icon( '15px' );
		$button .= esc_html( $text );
		$button .= '</a>';

		return $this->is_product_without_price( $product ) ? $button : $html . $button;
	}

	/**
	 * Render floating button
	 */
	public function render_floating_button() {
		if ( ! $this->should_show_floating_button() ) {
			return;
		}

		$multi_users_enabled = DW_WhatsApp_Settings::get( 'multi_users_enabled' );
		
		if ( $multi_users_enabled === 'yes' ) {
			$this->render_multi_users_widget();
		} else {
			$this->render_single_user_button();
		}
	}

	/**
	 * Render single user button (original behavior)
	 */
	private function render_single_user_button() {
		$phone = preg_replace( '/[^0-9]/', '', DW_WhatsApp_Settings::get( 'phone_number' ) );
		$text = DW_WhatsApp_Settings::get( 'floating_button_text', 'Fale Conosco' );
		$message = DW_WhatsApp_Settings::get( 'floating_button_message', 'Olá! Vim pelo site e gostaria de mais informações.' );
		$position = DW_WhatsApp_Settings::get( 'floating_button_position', 'bottom-right' );
		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );

		$link = 'https://wa.me/' . $phone . '?text=' . rawurlencode( $message );

		$positions = array(
			'bottom-right' => 'bottom: 20px; right: 20px;',
			'bottom-left'  => 'bottom: 20px; left: 20px;',
			'top-right'    => 'top: 80px; right: 20px;',
			'top-left'     => 'top: 80px; left: 20px;',
		);

		$style = isset( $positions[ $position ] ) ? $positions[ $position ] : $positions['bottom-right'];

		echo '<div id="dw-whatsapp-floating" style="position: fixed; ' . esc_attr( $style ) . ' z-index: 99999;">';
		echo '<a href="' . esc_url( $link ) . '" target="_blank" class="dw-whatsapp-floating-button" style="display: flex; align-items: center; gap: 10px; background-color: ' . $color . '; color: white; padding: 12px 20px; border-radius: 50px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500;">';
		echo $this->get_whatsapp_icon( '24px' );
		echo '<span class="dw-floating-text" style="font-size: 14px;">' . esc_html( $text ) . '</span>';
		echo '</a></div>';

		$this->render_floating_button_styles( $position );
	}

	/**
	 * Render multi users widget
	 */
	private function render_multi_users_widget() {
		$position = DW_WhatsApp_Settings::get( 'floating_button_position', 'bottom-right' );
		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );
		$users = DW_WhatsApp_Settings::get( 'multi_users', array() );
		$title = DW_WhatsApp_Settings::get( 'chat_widget_title', 'Iniciar Conversa' );
		$subtitle = DW_WhatsApp_Settings::get( 'chat_widget_subtitle', 'Olá! Clique em um dos nossos membros abaixo para conversar no WhatsApp ;)' );
		$availability = DW_WhatsApp_Settings::get( 'chat_widget_availability', 'A equipe normalmente responde em alguns minutos.' );

		$positions = array(
			'bottom-right' => 'bottom: 20px; right: 20px;',
			'bottom-left'  => 'bottom: 20px; left: 20px;',
			'top-right'    => 'top: 80px; right: 20px;',
			'top-left'     => 'top: 80px; left: 20px;',
		);

		$style = isset( $positions[ $position ] ) ? $positions[ $position ] : $positions['bottom-right'];

		echo '<div id="dw-whatsapp-floating" style="position: fixed; ' . esc_attr( $style ) . ' z-index: 99999;">';
		
		// Floating button
		echo '<div id="dw-whatsapp-trigger" class="dw-whatsapp-trigger" style="display: flex; align-items: center; gap: 10px; background-color: ' . $color . '; color: white; padding: 12px 20px; border-radius: 50px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500; cursor: pointer;">';
		echo $this->get_whatsapp_icon( '24px' );
		echo '<span class="dw-floating-text" style="font-size: 14px;">' . esc_html( $title ) . '</span>';
		echo '</div>';

		// Chat widget
		echo '<div id="dw-whatsapp-widget" class="dw-whatsapp-widget" style="display: none; position: absolute; bottom: 80px; right: 0; width: 320px; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); overflow: hidden; animation: dwSlideUp 0.3s ease;">';
		
		// Header
		echo '<div class="dw-widget-header" style="background: ' . $color . '; color: white; padding: 20px; text-align: center;">';
		echo '<div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: 8px;">';
		echo $this->get_whatsapp_icon( '24px' );
		echo '<h3 style="margin: 0; font-size: 18px; font-weight: 600;">' . esc_html( $title ) . '</h3>';
		echo '</div>';
		echo '<p style="margin: 0; font-size: 14px; opacity: 0.9;">' . esc_html( $subtitle ) . '</p>';
		echo '</div>';

		// Availability message
		echo '<div style="padding: 12px 20px; background: #f8f9fa; border-bottom: 1px solid #e9ecef; font-size: 13px; color: #6c757d; text-align: center;">';
		echo esc_html( $availability );
		echo '</div>';

		// Users list
		echo '<div class="dw-users-list" style="max-height: 300px; overflow-y: auto;">';
		foreach ( $users as $user ) {
			$this->render_user_item( $user );
		}
		echo '</div>';

		// Close button
		echo '<div style="position: absolute; top: 15px; right: 15px;">';
		echo '<button id="dw-whatsapp-close" style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; padding: 5px; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">&times;</button>';
		echo '</div>';

		echo '</div>'; // End widget
		echo '</div>'; // End floating

		$this->render_multi_users_styles( $position );
		$this->render_multi_users_scripts();
	}

	/**
	 * Render floating button styles
	 *
	 * @param string $position Position.
	 */
	private function render_floating_button_styles( $position ) {
		?>
		<style>
			.dw-whatsapp-floating-button:hover {
				transform: scale(1.05);
				box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
			}
			@media (max-width: 768px) {
				#dw-whatsapp-floating {
					<?php echo strpos( $position, 'right' ) !== false ? 'right: 15px !important;' : 'left: 15px !important;'; ?>
					<?php echo strpos( $position, 'bottom' ) !== false ? 'bottom: 15px !important;' : 'top: 70px !important;'; ?>
				}
				.dw-floating-text { display: none; }
				.dw-whatsapp-floating-button { padding: 15px !important; border-radius: 50% !important; }
			}
		</style>
		<?php
	}

	/**
	 * Set product unpurchasable if no price
	 *
	 * @param bool       $purchasable Purchasable status.
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	public function set_unpurchasable_if_no_price( $purchasable, $product ) {
		return $this->is_product_without_price( $product ) ? false : $purchasable;
	}

	/**
	 * Modify price HTML
	 *
	 * @param string     $price Price HTML.
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function modify_price_html( $price, $product ) {
		if ( $this->is_product_without_price( $product ) ) {
			return '<span class="dw-price-request-quote" style="color: #d63638; font-weight: bold; font-size: 16px;">Solicite um orçamento</span>';
		}
		return $price;
	}

	/**
	 * Check if product has no price
	 *
	 * @param WC_Product $product Product object.
	 * @return bool
	 */
	private function is_product_without_price( $product ) {
		if ( ! is_object( $product ) ) {
			return false;
		}

		$price = $product->get_price();

		return ( '' === $price || null === $price || 0 == $price || '0' === $price );
	}

	/**
	 * Generate WhatsApp link
	 *
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	private function generate_whatsapp_link( $product ) {
		$phone = preg_replace( '/[^0-9]/', '', DW_WhatsApp_Settings::get( 'phone_number' ) );
		$template = $this->is_product_without_price( $product )
			? DW_WhatsApp_Settings::get( 'message_without_price' )
			: DW_WhatsApp_Settings::get( 'message_with_price' );

		$message = str_replace( '{product_name}', $product->get_name(), $template );

		if ( DW_WhatsApp_Settings::get( 'include_product_link' ) === 'yes' ) {
			$message .= ' - Link: ' . get_permalink( $product->get_id() );
		}

		return 'https://wa.me/' . $phone . '?text=' . rawurlencode( $message );
	}

	/**
	 * Should show floating button
	 *
	 * @return bool
	 */
	private function should_show_floating_button() {
		if ( DW_WhatsApp_Settings::get( 'show_floating_button' ) !== 'yes' ) {
			return false;
		}

		if ( empty( DW_WhatsApp_Settings::get( 'phone_number' ) ) ) {
			return false;
		}

		$hidden_pages = DW_WhatsApp_Settings::get( 'floating_button_hide_pages', array() );
		
		if ( empty( $hidden_pages ) ) {
			return true;
		}

		$current_page = $this->get_current_page_type();

		return ! in_array( $current_page, $hidden_pages, true );
	}

	/**
	 * Get current page type
	 *
	 * @return string
	 */
	private function get_current_page_type() {
		// WooCommerce
		if ( is_cart() ) {
			return 'cart';
		}
		if ( is_checkout() ) {
			return 'checkout';
		}
		if ( is_account_page() ) {
			return 'my-account';
		}
		if ( is_shop() ) {
			return 'shop';
		}
		if ( is_product_category() ) {
			return 'product-category';
		}
		if ( is_product_tag() ) {
			return 'product-tag';
		}
		if ( is_product() ) {
			return 'product';
		}

		// WordPress
		if ( is_home() || is_front_page() ) {
			return 'home';
		}
		if ( is_page() ) {
			return 'page';
		}
		if ( is_single() ) {
			return 'post';
		}
		if ( is_category() ) {
			return 'category';
		}
		if ( is_tag() ) {
			return 'tag';
		}
		if ( is_archive() ) {
			return 'archive';
		}
		if ( is_search() ) {
			return 'search';
		}
		if ( is_404() ) {
			return '404';
		}

		return 'other';
	}

	/**
	 * Render user item
	 *
	 * @param array $user User data.
	 */
	private function render_user_item( $user ) {
		$name = esc_html( $user['name'] ?? '' );
		$department = esc_html( $user['department'] ?? '' );
		$phone = preg_replace( '/[^0-9]/', '', $user['phone'] ?? '' );
		$avatar = esc_url( $user['avatar'] ?? '' );
		$status_message = esc_html( $user['status_message'] ?? '' );
		$message = DW_WhatsApp_Settings::get( 'floating_button_message', 'Olá! Vim pelo site e gostaria de mais informações.' );

		// Usar status automático baseado em horário (se configurado)
		$status = DW_WhatsApp_Schedule::get_current_status( $user );
		
		// Obter horário formatado
		$working_hours = DW_WhatsApp_Schedule::get_formatted_hours( $user );
		if ( empty( $working_hours ) ) {
			$working_hours = esc_html( $user['working_hours'] ?? '' );
		}

		$status_colors = array(
			'online' => '#25d366',
			'away' => '#ffa500',
			'offline' => '#999',
		);

		$status_color = $status_colors[ $status ] ?? '#999';
		$is_available = $status === 'online';

		echo '<div class="dw-user-item" style="padding: 15px 20px; border-bottom: 1px solid #f0f0f0; display: flex; align-items: center; gap: 12px; transition: background-color 0.2s ease; cursor: ' . ( $is_available ? 'pointer' : 'default' ) . ';" data-phone="' . esc_attr( $phone ) . '" data-message="' . esc_attr( $message ) . '" data-available="' . ( $is_available ? '1' : '0' ) . '">';
		
		// Avatar
		echo '<div style="position: relative; flex-shrink: 0;">';
		if ( $avatar ) {
			echo '<img src="' . $avatar . '" alt="' . $name . '" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">';
		} else {
			echo '<div style="width: 50px; height: 50px; border-radius: 50%; background: #e9ecef; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #6c757d;">' . strtoupper( substr( $name, 0, 1 ) ) . '</div>';
		}
		// Status indicator
		echo '<div style="position: absolute; bottom: 2px; right: 2px; width: 14px; height: 14px; border-radius: 50%; background: ' . $status_color . '; border: 2px solid white;"></div>';
		echo '</div>';

		// User info
		echo '<div style="flex: 1; min-width: 0;">';
		echo '<div style="font-weight: 600; font-size: 15px; color: #333; margin-bottom: 2px;">' . $name . '</div>';
		if ( $department ) {
			echo '<div style="font-size: 13px; color: #666; margin-bottom: 2px;">' . $department . '</div>';
		}
		if ( $working_hours ) {
			echo '<div style="font-size: 12px; color: #888;">' . $working_hours . '</div>';
		}
		
		// Mostrar próximo horário disponível se offline e tiver horário automático
		if ( ! $is_available && ! empty( $user['auto_status'] ) && $user['auto_status'] === 'yes' ) {
			$next_available = DW_WhatsApp_Schedule::get_next_available( $user );
			if ( ! empty( $next_available ) ) {
				echo '<div style="font-size: 12px; color: #ff6b6b; font-style: italic;">' . esc_html( $next_available ) . '</div>';
			}
		} elseif ( $status_message && ! $is_available ) {
			echo '<div style="font-size: 12px; color: #ff6b6b; font-style: italic;">' . $status_message . '</div>';
		}
		echo '</div>';

		// WhatsApp icon
		echo '<div style="flex-shrink: 0; opacity: ' . ( $is_available ? '1' : '0.3' ) . ';">';
		echo $this->get_whatsapp_icon( '24px' );
		echo '</div>';

		echo '</div>';
	}

	/**
	 * Render multi users styles
	 *
	 * @param string $position Position.
	 */
	private function render_multi_users_styles( $position ) {
		?>
		<style>
			.dw-whatsapp-trigger:hover {
				transform: scale(1.05);
				box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
			}
			
			.dw-user-item:hover {
				background-color: #f8f9fa !important;
			}
			
			.dw-user-item[data-available="0"] {
				opacity: 0.6;
			}
			
			@keyframes dwSlideUp {
				from {
					opacity: 0;
					transform: translateY(20px);
				}
				to {
					opacity: 1;
					transform: translateY(0);
				}
			}
			
			@media (max-width: 768px) {
				.dw-whatsapp-widget {
					width: 280px !important;
					right: -10px !important;
				}
				
				.dw-floating-text { 
					display: none; 
				}
				
				.dw-whatsapp-trigger { 
					padding: 15px !important; 
					border-radius: 50% !important; 
				}
				
				#dw-whatsapp-floating {
					<?php echo strpos( $position, 'right' ) !== false ? 'right: 15px !important;' : 'left: 15px !important;'; ?>
					<?php echo strpos( $position, 'bottom' ) !== false ? 'bottom: 15px !important;' : 'top: 70px !important;'; ?>
				}
			}
		</style>
		<?php
	}

	/**
	 * Render multi users scripts
	 */
	private function render_multi_users_scripts() {
		?>
		<script>
		document.addEventListener('DOMContentLoaded', function() {
			const trigger = document.getElementById('dw-whatsapp-trigger');
			const widget = document.getElementById('dw-whatsapp-widget');
			const closeBtn = document.getElementById('dw-whatsapp-close');
			const userItems = document.querySelectorAll('.dw-user-item');
			
			// Toggle widget
			trigger.addEventListener('click', function() {
				widget.style.display = widget.style.display === 'none' ? 'block' : 'none';
			});
			
			// Close widget
			closeBtn.addEventListener('click', function() {
				widget.style.display = 'none';
			});
			
			// User item click
			userItems.forEach(function(item) {
				item.addEventListener('click', function() {
					const phone = this.getAttribute('data-phone');
					const message = this.getAttribute('data-message');
					const available = this.getAttribute('data-available');
					
					if (available === '1' && phone) {
						const url = 'https://wa.me/' + phone + '?text=' + encodeURIComponent(message);
						window.open(url, '_blank');
						widget.style.display = 'none';
					}
				});
			});
			
			// Close on outside click
			document.addEventListener('click', function(e) {
				if (!trigger.contains(e.target) && !widget.contains(e.target)) {
					widget.style.display = 'none';
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * Get WhatsApp icon SVG
	 *
	 * @param string $size Icon size.
	 * @return string
	 */
	private function get_whatsapp_icon( $size = '18px' ) {
		return '<svg style="width: ' . esc_attr( $size ) . '; height: ' . esc_attr( $size ) . '; flex-shrink: 0;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 509 511.514"><path fill="#fff" d="M434.762 74.334C387.553 26.81 323.245 0 256.236 0h-.768C115.795.001 2.121 113.696 2.121 253.456l.001.015a253.516 253.516 0 0033.942 126.671L0 511.514l134.373-35.269a253.416 253.416 0 00121.052 30.9h.003.053C395.472 507.145 509 393.616 509 253.626c0-67.225-26.742-131.727-74.252-179.237l.014-.055zM255.555 464.453c-37.753 0-74.861-10.22-107.293-29.479l-7.72-4.602-79.741 20.889 21.207-77.726-4.984-7.975c-21.147-33.606-32.415-72.584-32.415-112.308 0-116.371 94.372-210.743 210.741-210.743 56.011 0 109.758 22.307 149.277 61.98a210.93 210.93 0 0161.744 149.095c0 116.44-94.403 210.869-210.844 210.869h.028zm115.583-157.914c-6.363-3.202-37.474-18.472-43.243-20.593-5.769-2.121-10.01-3.202-14.315 3.203-4.305 6.404-16.373 20.593-20.063 24.855-3.69 4.263-7.401 4.815-13.679 1.612-6.278-3.202-26.786-9.883-50.899-31.472a192.748 192.748 0 01-35.411-43.867c-3.712-6.363-.404-9.777 2.82-12.873 3.224-3.096 6.363-7.381 9.48-11.092a41.58 41.58 0 006.357-10.597 11.678 11.678 0 00-.508-11.09c-1.718-3.18-14.444-34.357-19.534-47.06-5.09-12.703-10.37-10.603-14.272-10.901-3.902-.297-7.911-.19-12.089-.19a23.322 23.322 0 00-16.964 7.911c-5.707 6.298-22.1 21.673-22.1 52.849s22.671 61.249 25.852 65.532c3.182 4.284 44.663 68.227 108.288 95.649 15.099 6.489 26.891 10.392 36.053 13.403a87.504 87.504 0 0025.216 3.718c4.905 0 9.82-.416 14.65-1.237 12.174-1.782 37.453-15.291 42.776-30.073s5.303-27.57 3.711-30.093c-1.591-2.524-5.704-4.369-12.088-7.615l-.038.021z"/></svg>';
	}
}



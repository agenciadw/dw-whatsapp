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
		// Floating button - sempre funciona (com ou sem WooCommerce)
		if ( DW_WhatsApp_Settings::get( 'show_floating_button' ) === 'yes' ) {
			add_action( 'wp_footer', array( $this, 'render_floating_button' ) );
		}

		// Scripts - sempre carregar CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Hooks específicos do WooCommerce (só funcionam se WooCommerce estiver ativo)
		if ( $this->is_woocommerce_active() ) {
			$this->init_woocommerce_hooks();
		}
	}

	/**
	 * Initialize WooCommerce specific hooks
	 */
	private function init_woocommerce_hooks() {
		// Product page
		if ( DW_WhatsApp_Settings::get( 'show_on_product_page' ) === 'yes' ) {
			add_action( 'woocommerce_single_product_summary', array( $this, 'render_product_button' ), 999 );
		}

		// Product loop
		if ( DW_WhatsApp_Settings::get( 'show_on_product_loop' ) === 'yes' ) {
			add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'render_loop_button' ), 999, 2 );
		}

		// Product hooks
		add_filter( 'woocommerce_is_purchasable', array( $this, 'set_unpurchasable_if_no_price' ), 1000, 2 );
		add_filter( 'woocommerce_get_price_html', array( $this, 'modify_price_html' ), 1000, 2 );
		add_filter( 'woocommerce_variable_sale_price_html', array( $this, 'modify_price_html' ), 1000, 2 );
		add_filter( 'woocommerce_variable_price_html', array( $this, 'modify_price_html' ), 1000, 2 );
	}

	/**
	 * Check if WooCommerce is active
	 *
	 * @return bool
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {
		// Enqueue CSS - sempre carregar
		wp_enqueue_style(
			'dw-whatsapp-frontend',
			DW_WHATSAPP_URL . 'assets/css/frontend.css',
			array(),
			DW_WHATSAPP_VERSION
		);

		// Enqueue JS apenas em páginas de produto do WooCommerce (se WooCommerce estiver ativo)
		if ( $this->is_woocommerce_active() && is_product() && DW_WhatsApp_Settings::get( 'include_variations' ) === 'yes' ) {
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
		
		// Configurações separadas para desktop e mobile
		$position_desktop = DW_WhatsApp_Settings::get( 'floating_button_position_desktop', 'bottom-right' );
		$position_mobile = DW_WhatsApp_Settings::get( 'floating_button_position_mobile', 'bottom-right' );
		$offset_x_desktop = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_x_desktop', '0' ) );
		$offset_y_desktop = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_y_desktop', '0' ) );
		$offset_x_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_x_mobile', '0' ) );
		$offset_y_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_y_mobile', '0' ) );
		
		$style = DW_WhatsApp_Settings::get( 'floating_button_style', 'rectangular' );
		$size = DW_WhatsApp_Settings::get( 'floating_button_size', 'medium' );
		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );

		$link = 'https://wa.me/' . $phone . '?text=' . rawurlencode( $message );

		// Posições para desktop
		$positions_desktop = array(
			'bottom-right' => 'bottom: ' . (20 + $offset_y_desktop) . 'px; right: ' . (20 + $offset_x_desktop) . 'px;',
			'bottom-left'  => 'bottom: ' . (20 + $offset_y_desktop) . 'px; left: ' . (20 + $offset_x_desktop) . 'px;',
			'top-right'    => 'top: ' . (80 + $offset_y_desktop) . 'px; right: ' . (20 + $offset_x_desktop) . 'px;',
			'top-left'     => 'top: ' . (80 + $offset_y_desktop) . 'px; left: ' . (20 + $offset_x_desktop) . 'px;',
		);

		// Posições para mobile
		$positions_mobile = array(
			'bottom-right' => 'bottom: ' . (15 + $offset_y_mobile) . 'px; right: ' . (15 + $offset_x_mobile) . 'px;',
			'bottom-left'  => 'bottom: ' . (15 + $offset_y_mobile) . 'px; left: ' . (15 + $offset_x_mobile) . 'px;',
			'top-right'    => 'top: ' . (70 + $offset_y_mobile) . 'px; right: ' . (15 + $offset_x_mobile) . 'px;',
			'top-left'     => 'top: ' . (70 + $offset_y_mobile) . 'px; left: ' . (15 + $offset_x_mobile) . 'px;',
		);

		$position_style_desktop = isset( $positions_desktop[ $position_desktop ] ) ? $positions_desktop[ $position_desktop ] : $positions_desktop['bottom-right'];
		$position_style_mobile = isset( $positions_mobile[ $position_mobile ] ) ? $positions_mobile[ $position_mobile ] : $positions_mobile['bottom-right'];

		// Tamanhos
		$sizes = array(
			'small' => array( 'padding' => '8px 12px', 'font-size' => '12px', 'icon-size' => '18px' ),
			'medium' => array( 'padding' => '12px 20px', 'font-size' => '14px', 'icon-size' => '24px' ),
			'large' => array( 'padding' => '16px 24px', 'font-size' => '16px', 'icon-size' => '28px' ),
		);

		$size_config = isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['medium'];

		echo '<div id="dw-whatsapp-floating" class="dw-whatsapp-floating-container" style="position: fixed; ' . esc_attr( $position_style_desktop ) . ' z-index: 99999;">';
		
		if ( $style === 'circular' ) {
			// Estilo circular com hover
			echo '<div class="dw-whatsapp-circular-container">';
			echo '<a href="' . esc_url( $link ) . '" target="_blank" class="dw-whatsapp-floating-button dw-circular" style="display: flex; align-items: center; justify-content: center; background-color: ' . $color . '; color: white; padding: ' . $size_config['padding'] . '; border-radius: 50%; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500; width: 60px; height: 60px;">';
			echo $this->get_whatsapp_icon( $size_config['icon-size'] );
			echo '</a>';
			echo '<div class="dw-whatsapp-tooltip" style="position: absolute; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 6px; font-size: ' . $size_config['font-size'] . '; white-space: nowrap; opacity: 0; visibility: hidden; transition: all 0.3s ease; pointer-events: none;">';
			echo esc_html( $text );
			echo '</div>';
			echo '</div>';
		} else {
			// Estilo retangular (atual)
			echo '<a href="' . esc_url( $link ) . '" target="_blank" class="dw-whatsapp-floating-button dw-rectangular" style="display: flex; align-items: center; gap: 10px; background-color: ' . $color . '; color: white; padding: ' . $size_config['padding'] . '; border-radius: 50px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500;">';
			echo $this->get_whatsapp_icon( $size_config['icon-size'] );
			echo '<span class="dw-floating-text" style="font-size: ' . $size_config['font-size'] . ';">' . esc_html( $text ) . '</span>';
			echo '</a>';
		}
		
		echo '</div>';

		$this->render_floating_button_styles( $position_desktop, $position_mobile, $style, $size );
	}

	/**
	 * Render multi users widget
	 */
	private function render_multi_users_widget() {
		// Configurações separadas para desktop e mobile
		$position_desktop = DW_WhatsApp_Settings::get( 'floating_button_position_desktop', 'bottom-right' );
		$position_mobile = DW_WhatsApp_Settings::get( 'floating_button_position_mobile', 'bottom-right' );
		$offset_x_desktop = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_x_desktop', '0' ) );
		$offset_y_desktop = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_y_desktop', '0' ) );
		$offset_x_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_x_mobile', '0' ) );
		$offset_y_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_y_mobile', '0' ) );
		
		$style = DW_WhatsApp_Settings::get( 'floating_button_style', 'rectangular' );
		$size = DW_WhatsApp_Settings::get( 'floating_button_size', 'medium' );
		$color = esc_attr( DW_WhatsApp_Settings::get( 'button_color', '#25d366' ) );
		$users = DW_WhatsApp_Settings::get( 'multi_users', array() );
		$title = DW_WhatsApp_Settings::get( 'chat_widget_title', 'Iniciar Conversa' );
		$subtitle = DW_WhatsApp_Settings::get( 'chat_widget_subtitle', 'Olá! Clique em um dos nossos membros abaixo para conversar no WhatsApp ;)' );
		$availability = DW_WhatsApp_Settings::get( 'chat_widget_availability', 'A equipe normalmente responde em alguns minutos.' );

		// Posições para desktop
		$positions_desktop = array(
			'bottom-right' => 'bottom: ' . (20 + $offset_y_desktop) . 'px; right: ' . (20 + $offset_x_desktop) . 'px;',
			'bottom-left'  => 'bottom: ' . (20 + $offset_y_desktop) . 'px; left: ' . (20 + $offset_x_desktop) . 'px;',
			'top-right'    => 'top: ' . (80 + $offset_y_desktop) . 'px; right: ' . (20 + $offset_x_desktop) . 'px;',
			'top-left'     => 'top: ' . (80 + $offset_y_desktop) . 'px; left: ' . (20 + $offset_x_desktop) . 'px;',
		);

		$position_style_desktop = isset( $positions_desktop[ $position_desktop ] ) ? $positions_desktop[ $position_desktop ] : $positions_desktop['bottom-right'];

		// Tamanhos
		$sizes = array(
			'small' => array( 'padding' => '8px 12px', 'font-size' => '12px', 'icon-size' => '18px' ),
			'medium' => array( 'padding' => '12px 20px', 'font-size' => '14px', 'icon-size' => '24px' ),
			'large' => array( 'padding' => '16px 24px', 'font-size' => '16px', 'icon-size' => '28px' ),
		);

		$size_config = isset( $sizes[ $size ] ) ? $sizes[ $size ] : $sizes['medium'];

		echo '<div id="dw-whatsapp-floating" class="dw-whatsapp-floating-container" style="position: fixed; ' . esc_attr( $position_style_desktop ) . ' z-index: 99999;">';
		
		if ( $style === 'circular' ) {
			// Estilo circular com hover
			echo '<div class="dw-whatsapp-circular-container">';
			echo '<div id="dw-whatsapp-trigger" class="dw-whatsapp-trigger dw-circular" style="display: flex; align-items: center; justify-content: center; background-color: ' . $color . '; color: white; padding: ' . $size_config['padding'] . '; border-radius: 50%; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500; width: 60px; height: 60px; cursor: pointer;">';
			echo $this->get_whatsapp_icon( $size_config['icon-size'] );
			echo '</div>';
			echo '<div class="dw-whatsapp-tooltip" style="position: absolute; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 6px; font-size: ' . $size_config['font-size'] . '; white-space: nowrap; opacity: 0; visibility: hidden; transition: all 0.3s ease; pointer-events: none;">';
			echo esc_html( $title );
			echo '</div>';
			echo '</div>';
		} else {
			// Estilo retangular (atual)
			echo '<div id="dw-whatsapp-trigger" class="dw-whatsapp-trigger dw-rectangular" style="display: flex; align-items: center; gap: 10px; background-color: ' . $color . '; color: white; padding: ' . $size_config['padding'] . '; border-radius: 50px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.15); transition: all 0.3s ease; font-weight: 500; cursor: pointer;">';
			echo $this->get_whatsapp_icon( $size_config['icon-size'] );
			echo '<span class="dw-floating-text" style="font-size: ' . $size_config['font-size'] . ';">' . esc_html( $title ) . '</span>';
			echo '</div>';
		}

		// Chat widget - posicionamento dinâmico baseado na posição do botão
		$widget_position = $this->get_widget_position( $position );
		echo '<div id="dw-whatsapp-widget" class="dw-whatsapp-widget" style="display: none; position: absolute; ' . $widget_position . ' width: 320px; background: white; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,0.2); overflow: hidden; animation: dwSlideUp 0.3s ease;">';
		
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

		$this->render_multi_users_styles( $position_desktop, $position_mobile );
		$this->render_multi_users_scripts();
	}

	/**
	 * Render floating button styles
	 *
	 * @param string $position_desktop Desktop position.
	 * @param string $position_mobile Mobile position.
	 * @param string $style Style (rectangular or circular).
	 * @param string $size Size (small, medium, large).
	 */
	private function render_floating_button_styles( $position_desktop, $position_mobile, $style = 'rectangular', $size = 'medium' ) {
		?>
		<style>
			.dw-whatsapp-floating-button:hover {
				transform: scale(1.05);
				box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
			}

			/* Estilo circular com tooltip */
			.dw-whatsapp-circular-container {
				position: relative;
			}

			.dw-whatsapp-circular-container:hover .dw-whatsapp-tooltip {
				opacity: 1 !important;
				visibility: visible !important;
			}

			/* Posicionamento do tooltip baseado na posição do botão (desktop) */
			<?php if ( strpos( $position_desktop, 'right' ) !== false ) : ?>
			/* Botão à direita - tooltip aparece à esquerda */
			.dw-whatsapp-tooltip {
				right: 70px;
				top: 50%;
				transform: translateY(-50%);
			}
			.dw-whatsapp-tooltip::after {
				content: '';
				position: absolute;
				right: -5px;
				top: 50%;
				transform: translateY(-50%);
				border: 5px solid transparent;
				border-left-color: rgba(0,0,0,0.8);
			}
			<?php else : ?>
			/* Botão à esquerda - tooltip aparece à direita */
			.dw-whatsapp-tooltip {
				left: 70px;
				top: 50%;
				transform: translateY(-50%);
			}
			.dw-whatsapp-tooltip::after {
				content: '';
				position: absolute;
				left: -5px;
				top: 50%;
				transform: translateY(-50%);
				border: 5px solid transparent;
				border-right-color: rgba(0,0,0,0.8);
			}
			<?php endif; ?>

			/* Responsividade - Posicionamento específico para mobile */
			@media (max-width: 768px) {
				#dw-whatsapp-floating {
					/* Aplicar posição mobile com offsets */
					<?php if ( strpos( $position_mobile, 'right' ) !== false ) : ?>
						right: <?php echo (15 + $offset_x_mobile); ?>px !important;
						left: auto !important;
					<?php else : ?>
						left: <?php echo (15 + $offset_x_mobile); ?>px !important;
						right: auto !important;
					<?php endif; ?>
					
					<?php if ( strpos( $position_mobile, 'bottom' ) !== false ) : ?>
						bottom: <?php echo (15 + $offset_y_mobile); ?>px !important;
						top: auto !important;
					<?php else : ?>
						top: <?php echo (70 + $offset_y_mobile); ?>px !important;
						bottom: auto !important;
					<?php endif; ?>
				}
				
				/* Em mobile, sempre usar estilo circular */
				.dw-whatsapp-floating-button {
					padding: 15px !important;
					border-radius: 50% !important;
					width: 60px !important;
					height: 60px !important;
					justify-content: center !important;
				}
				
				.dw-floating-text { 
					display: none !important; 
				}
				
				.dw-whatsapp-tooltip {
					display: none !important;
				}
			}

			/* Tamanhos específicos */
			<?php if ( $size === 'small' ) : ?>
			.dw-whatsapp-floating-button {
				min-width: 50px;
			}
			.dw-circular {
				width: 50px !important;
				height: 50px !important;
			}
			<?php elseif ( $size === 'large' ) : ?>
			.dw-whatsapp-floating-button {
				min-width: 80px;
			}
			.dw-circular {
				width: 70px !important;
				height: 70px !important;
			}
			<?php endif; ?>
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
		// WooCommerce (só funciona se WooCommerce estiver ativo)
		if ( $this->is_woocommerce_active() ) {
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
		}

		// WordPress (sempre funciona)
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
	 * @param string $position_desktop Desktop position.
	 * @param string $position_mobile Mobile position.
	 */
	private function render_multi_users_styles( $position_desktop, $position_mobile ) {
		$style = DW_WhatsApp_Settings::get( 'floating_button_style', 'rectangular' );
		$size = DW_WhatsApp_Settings::get( 'floating_button_size', 'medium' );
		
		// Obter offsets para mobile
		$offset_x_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_x_mobile', '0' ) );
		$offset_y_mobile = intval( DW_WhatsApp_Settings::get( 'floating_button_offset_y_mobile', '0' ) );
		?>
		<style>
			.dw-whatsapp-trigger:hover {
				transform: scale(1.05);
				box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
			}

			/* Estilo circular com tooltip para múltiplos usuários */
			.dw-whatsapp-circular-container {
				position: relative;
			}

			.dw-whatsapp-circular-container:hover .dw-whatsapp-tooltip {
				opacity: 1 !important;
				visibility: visible !important;
			}

			/* Posicionamento do tooltip baseado na posição do botão (desktop) */
			<?php if ( strpos( $position_desktop, 'right' ) !== false ) : ?>
			/* Botão à direita - tooltip aparece à esquerda */
			.dw-whatsapp-tooltip {
				right: 70px;
				top: 50%;
				transform: translateY(-50%);
			}
			.dw-whatsapp-tooltip::after {
				content: '';
				position: absolute;
				right: -5px;
				top: 50%;
				transform: translateY(-50%);
				border: 5px solid transparent;
				border-left-color: rgba(0,0,0,0.8);
			}
			<?php else : ?>
			/* Botão à esquerda - tooltip aparece à direita */
			.dw-whatsapp-tooltip {
				left: 70px;
				top: 50%;
				transform: translateY(-50%);
			}
			.dw-whatsapp-tooltip::after {
				content: '';
				position: absolute;
				left: -5px;
				top: 50%;
				transform: translateY(-50%);
				border: 5px solid transparent;
				border-right-color: rgba(0,0,0,0.8);
			}
			<?php endif; ?>
			
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
			
			/* Ponteiro da caixa de diálogo */
			.dw-whatsapp-widget::before {
				content: '';
				position: absolute;
				width: 0;
				height: 0;
				border-style: solid;
			}
			
			/* Posicionamento do ponteiro baseado na posição do botão (desktop) */
			<?php if ( strpos( $position_desktop, 'left' ) !== false ) : ?>
			/* Botão à esquerda - ponteiro aponta da esquerda para a direita */
			.dw-whatsapp-widget::before {
				left: -10px;
				top: 50%;
				transform: translateY(-50%);
				border-width: 10px 10px 10px 0;
				border-color: transparent white transparent transparent;
			}
			<?php else : ?>
			/* Botão à direita - ponteiro aponta da direita para a esquerda */
			.dw-whatsapp-widget::before {
				right: -10px;
				top: 50%;
				transform: translateY(-50%);
				border-width: 10px 0 10px 10px;
				border-color: transparent transparent transparent white;
			}
			<?php endif; ?>

			/* Tamanhos específicos */
			<?php if ( $size === 'small' ) : ?>
			.dw-whatsapp-trigger {
				min-width: 50px;
			}
			.dw-circular {
				width: 50px !important;
				height: 50px !important;
			}
			<?php elseif ( $size === 'large' ) : ?>
			.dw-whatsapp-trigger {
				min-width: 80px;
			}
			.dw-circular {
				width: 70px !important;
				height: 70px !important;
			}
			<?php endif; ?>
			
			/* Responsividade - Posicionamento específico para mobile */
			@media (max-width: 768px) {
				#dw-whatsapp-floating {
					/* Aplicar posição mobile com offsets */
					<?php if ( strpos( $position_mobile, 'right' ) !== false ) : ?>
						right: <?php echo (15 + $offset_x_mobile); ?>px !important;
						left: auto !important;
					<?php else : ?>
						left: <?php echo (15 + $offset_x_mobile); ?>px !important;
						right: auto !important;
					<?php endif; ?>
					
					<?php if ( strpos( $position_mobile, 'bottom' ) !== false ) : ?>
						bottom: <?php echo (15 + $offset_y_mobile); ?>px !important;
						top: auto !important;
					<?php else : ?>
						top: <?php echo (70 + $offset_y_mobile); ?>px !important;
						bottom: auto !important;
					<?php endif; ?>
				}
				
				.dw-whatsapp-widget {
					width: 280px !important;
					<?php if ( strpos( $position_mobile, 'left' ) !== false ) : ?>
					left: -10px !important;
					right: auto !important;
					<?php else : ?>
					right: -10px !important;
					left: auto !important;
					<?php endif; ?>
				}
				
				.dw-floating-text { 
					display: none; 
				}
				
				.dw-whatsapp-tooltip {
					display: none !important;
				}
				
				.dw-whatsapp-trigger { 
					padding: 15px !important; 
					border-radius: 50% !important; 
					width: 60px !important;
					height: 60px !important;
					justify-content: center !important;
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
	 * Get widget position based on button position
	 *
	 * @param string $position Button position.
	 * @return string
	 */
	private function get_widget_position( $position ) {
		$positions = array(
			'bottom-right' => 'bottom: 80px; right: 0;',
			'bottom-left'  => 'bottom: 80px; left: 0;',
			'top-right'    => 'top: 80px; right: 0;',
			'top-left'     => 'top: 80px; left: 0;',
		);
		
		return isset( $positions[ $position ] ) ? $positions[ $position ] : $positions['bottom-right'];
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



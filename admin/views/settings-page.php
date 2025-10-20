<?php
/**
 * Settings page template
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$settings = DW_WhatsApp_Settings::get_settings();
$hidden_pages = isset( $settings['floating_button_hide_pages'] ) ? $settings['floating_button_hide_pages'] : array();
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<form method="post" action="">
		<?php wp_nonce_field( 'dw_whatsapp_settings_action', 'dw_whatsapp_settings_nonce' ); ?>
		
		<table class="form-table">
			<!-- General Settings -->
			<tr>
				<th colspan="2"><h2>⚙️ Configurações Gerais</h2></th>
			</tr>
			
			<tr>
				<th scope="row"><label for="phone_number">Número do WhatsApp</label></th>
				<td>
					<input type="text" id="phone_number" name="dw_whatsapp_settings[phone_number]" value="<?php echo esc_attr( $settings['phone_number'] ); ?>" class="regular-text" placeholder="5519999999999" required>
					<p class="description">Digite o número com código do país e DDD (ex: 5519999999999)</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label for="button_color">Cor dos Botões</label></th>
				<td>
					<input type="text" id="button_color" name="dw_whatsapp_settings[button_color]" value="<?php echo esc_attr( $settings['button_color'] ); ?>" class="dw-color-picker">
					<p class="description">Escolha a cor dos botões do WhatsApp</p>
				</td>
			</tr>
			
			<!-- Display Options -->
			<tr>
				<th colspan="2"><h2>📍 Onde Exibir</h2></th>
			</tr>
			
			<tr>
				<th scope="row">Exibir Botões</th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="dw_whatsapp_settings[show_on_product_page]" value="yes" <?php checked( $settings['show_on_product_page'], 'yes' ); ?>> Na página do produto</label><br>
						<label><input type="checkbox" name="dw_whatsapp_settings[show_on_product_loop]" value="yes" <?php checked( $settings['show_on_product_loop'], 'yes' ); ?>> Na listagem de produtos</label><br>
						<label><input type="checkbox" name="dw_whatsapp_settings[show_floating_button]" value="yes" <?php checked( $settings['show_floating_button'], 'yes' ); ?>> Botão flutuante em todas as páginas</label>
					</fieldset>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label for="floating_button_position">Posição do Botão Flutuante</label></th>
				<td>
					<select id="floating_button_position" name="dw_whatsapp_settings[floating_button_position]">
						<option value="bottom-right" <?php selected( $settings['floating_button_position'], 'bottom-right' ); ?>>Inferior Direito</option>
						<option value="bottom-left" <?php selected( $settings['floating_button_position'], 'bottom-left' ); ?>>Inferior Esquerdo</option>
						<option value="top-right" <?php selected( $settings['floating_button_position'], 'top-right' ); ?>>Superior Direito</option>
						<option value="top-left" <?php selected( $settings['floating_button_position'], 'top-left' ); ?>>Superior Esquerdo</option>
					</select>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label>Ocultar Botão Flutuante em</label></th>
				<td>
					<fieldset>
						<?php
						$page_options = array(
							'cart' => 'Carrinho',
							'checkout' => 'Checkout',
							'my-account' => 'Minha Conta',
							'shop' => 'Loja',
							'product' => 'Páginas de Produto',
							'product-category' => 'Categorias de Produto',
							'home' => 'Página Inicial',
							'page' => 'Páginas',
							'post' => 'Posts/Blog',
						);
						foreach ( $page_options as $value => $label ) :
							?>
							<label><input type="checkbox" name="dw_whatsapp_settings[floating_button_hide_pages][]" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $hidden_pages, true ) ); ?>> <?php echo esc_html( $label ); ?></label><br>
						<?php endforeach; ?>
						<p class="description">Selecione as páginas onde o botão flutuante NÃO deve aparecer</p>
					</fieldset>
				</td>
			</tr>
			
			<!-- Messages -->
			<tr>
				<th colspan="2"><h2>💬 Mensagens do WhatsApp</h2></th>
			</tr>
			
			<tr>
				<th scope="row"><label for="message_with_price">Mensagem para Produtos com Preço</label></th>
				<td>
					<textarea id="message_with_price" name="dw_whatsapp_settings[message_with_price]" rows="3" class="large-text"><?php echo esc_textarea( $settings['message_with_price'] ); ?></textarea>
					<p class="description">Use {product_name} para inserir o nome do produto</p>
				</td>
			</tr>
			
			<tr>
				<th scope="row"><label for="message_without_price">Mensagem para Produtos sem Preço</label></th>
				<td>
					<textarea id="message_without_price" name="dw_whatsapp_settings[message_without_price]" rows="3" class="large-text"><?php echo esc_textarea( $settings['message_without_price'] ); ?></textarea>
				</td>
			</tr>
			
			<!-- Button Texts -->
			<tr>
				<th colspan="2"><h2>🔘 Textos dos Botões</h2></th>
			</tr>
			
			<tr>
				<th scope="row"><label for="button_text_with_price">Texto do Botão (com preço)</label></th>
				<td><input type="text" id="button_text_with_price" name="dw_whatsapp_settings[button_text_with_price]" value="<?php echo esc_attr( $settings['button_text_with_price'] ); ?>" class="regular-text"></td>
			</tr>
			
			<tr>
				<th scope="row"><label for="button_text_without_price">Texto do Botão (sem preço)</label></th>
				<td><input type="text" id="button_text_without_price" name="dw_whatsapp_settings[button_text_without_price]" value="<?php echo esc_attr( $settings['button_text_without_price'] ); ?>" class="regular-text"></td>
			</tr>
			
			<tr>
				<th scope="row"><label for="floating_button_text">Texto do Botão Flutuante</label></th>
				<td><input type="text" id="floating_button_text" name="dw_whatsapp_settings[floating_button_text]" value="<?php echo esc_attr( $settings['floating_button_text'] ); ?>" class="regular-text"></td>
			</tr>
			
			<tr>
				<th scope="row"><label for="floating_button_message">Mensagem do Botão Flutuante</label></th>
				<td><textarea id="floating_button_message" name="dw_whatsapp_settings[floating_button_message]" rows="3" class="large-text"><?php echo esc_textarea( $settings['floating_button_message'] ); ?></textarea></td>
			</tr>
			
			<!-- Advanced Options -->
			<tr>
				<th colspan="2"><h2>🔧 Opções Avançadas</h2></th>
			</tr>
			
			<tr>
				<th scope="row">Incluir na Mensagem</th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="dw_whatsapp_settings[include_product_link]" value="yes" <?php checked( $settings['include_product_link'], 'yes' ); ?>> Link do produto</label><br>
						<label><input type="checkbox" name="dw_whatsapp_settings[include_variations]" value="yes" <?php checked( $settings['include_variations'], 'yes' ); ?>> Variações selecionadas (cor, tamanho, etc.)</label>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<?php submit_button( 'Salvar Configurações', 'primary', 'dw_whatsapp_settings_submit' ); ?>
	</form>
	
	<hr>
	
	<div style="background: #f0f0f1; padding: 20px; border-radius: 5px; margin-top: 30px;">
		<h3>ℹ️ Informações do Plugin</h3>
		<p><strong>Versão:</strong> <?php echo esc_html( DW_WHATSAPP_VERSION ); ?></p>
		<p><strong>Desenvolvido por:</strong> David William da Costa</p>
		<p><strong>GitHub:</strong> <a href="https://github.com/agenciadw/dw-whatsapp" target="_blank">@agenciadw/dw-whatsapp</a></p>
	</div>
</div>


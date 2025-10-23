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
			
			<!-- Multi Users -->
			<tr>
				<th colspan="2"><h2>👥 Múltiplos Usuários</h2></th>
			</tr>
			
			<tr>
				<th scope="row">Ativar Múltiplos Usuários</th>
				<td>
					<fieldset>
						<label><input type="checkbox" name="dw_whatsapp_settings[multi_users_enabled]" value="yes" <?php checked( $settings['multi_users_enabled'], 'yes' ); ?>> Ativar sistema de múltiplos usuários no botão flutuante</label>
						<p class="description">Quando ativado, o botão flutuante mostrará uma lista de usuários para o cliente escolher</p>
					</fieldset>
				</td>
			</tr>
			
			<tr id="multi-users-section" style="<?php echo $settings['multi_users_enabled'] === 'yes' ? '' : 'display: none;'; ?>">
				<th scope="row">Configurações do Chat</th>
				<td>
					<table class="form-table">
						<tr>
							<th scope="row"><label for="chat_widget_title">Título do Chat</label></th>
							<td><input type="text" id="chat_widget_title" name="dw_whatsapp_settings[chat_widget_title]" value="<?php echo esc_attr( $settings['chat_widget_title'] ); ?>" class="regular-text"></td>
						</tr>
						<tr>
							<th scope="row"><label for="chat_widget_subtitle">Subtítulo do Chat</label></th>
							<td><textarea id="chat_widget_subtitle" name="dw_whatsapp_settings[chat_widget_subtitle]" rows="2" class="large-text"><?php echo esc_textarea( $settings['chat_widget_subtitle'] ); ?></textarea></td>
						</tr>
						<tr>
							<th scope="row"><label for="chat_widget_availability">Mensagem de Disponibilidade</label></th>
							<td><input type="text" id="chat_widget_availability" name="dw_whatsapp_settings[chat_widget_availability]" value="<?php echo esc_attr( $settings['chat_widget_availability'] ); ?>" class="regular-text"></td>
						</tr>
					</table>
				</td>
			</tr>
			
			<tr id="users-list-section" style="<?php echo $settings['multi_users_enabled'] === 'yes' ? '' : 'display: none;'; ?>">
				<th scope="row">Usuários</th>
				<td>
					<div style="margin-bottom: 15px; padding: 12px; background: #e7f7ed; border-left: 4px solid #25d366; border-radius: 4px;">
						<strong>💡 Dica:</strong> Arraste e solte os usuários para alterar a ordem de exibição no widget do WhatsApp
					</div>
					<div id="users-container">
						<?php
						$users = isset( $settings['multi_users'] ) ? $settings['multi_users'] : array();
						if ( empty( $users ) ) {
							$users = array( array( 'name' => '', 'phone' => '', 'department' => '', 'avatar' => '', 'status' => 'online', 'status_message' => '', 'working_hours' => '' ) );
						}
						foreach ( $users as $index => $user ) :
							?>
							<div class="user-item" style="border: 1px solid #ddd; padding: 15px 15px 15px 40px; margin-bottom: 15px; border-radius: 5px; background: #f9f9f9; position: relative; cursor: move;" draggable="true">
								<div class="drag-handle" style="position: absolute; left: -5px; top: 50%; transform: translateY(-50%); cursor: grab; padding: 10px; font-size: 20px; color: #999;" title="Arrastar para reordenar">
									<span style="display: flex; flex-direction: column; gap: 2px;">
										<span style="width: 20px; height: 3px; background: #999; border-radius: 2px;"></span>
										<span style="width: 20px; height: 3px; background: #999; border-radius: 2px;"></span>
										<span style="width: 20px; height: 3px; background: #999; border-radius: 2px;"></span>
									</span>
								</div>
								<h4 class="user-title">Usuário #<?php echo $index + 1; ?> <button type="button" class="button remove-user" style="float: right; color: #d63638;">Remover</button></h4>
								<table class="form-table">
									<tr>
										<th scope="row"><label>Nome</label></th>
										<td><input type="text" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][name]" value="<?php echo esc_attr( $user['name'] ); ?>" class="regular-text" placeholder="Ex: João Silva"></td>
									</tr>
									<tr>
										<th scope="row"><label>Telefone</label></th>
										<td><input type="text" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][phone]" value="<?php echo esc_attr( $user['phone'] ); ?>" class="regular-text" placeholder="5519999999999"></td>
									</tr>
									<tr>
										<th scope="row"><label>Departamento</label></th>
										<td><input type="text" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][department]" value="<?php echo esc_attr( $user['department'] ); ?>" class="regular-text" placeholder="Ex: Suporte, Vendas, Financeiro"></td>
									</tr>
									<tr>
										<th scope="row"><label>Avatar (URL)</label></th>
										<td><input type="url" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][avatar]" value="<?php echo esc_attr( $user['avatar'] ?? '' ); ?>" class="regular-text" placeholder="https://exemplo.com/avatar.jpg"></td>
									</tr>
									<tr>
										<th scope="row"><label>Status</label></th>
										<td>
											<select name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][status]">
												<option value="online" <?php selected( $user['status'], 'online' ); ?>>Online</option>
												<option value="away" <?php selected( $user['status'], 'away' ); ?>>Ausente</option>
												<option value="offline" <?php selected( $user['status'], 'offline' ); ?>>Offline</option>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label>Mensagem de Status</label></th>
										<td><input type="text" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][status_message]" value="<?php echo esc_attr( $user['status_message'] ?? '' ); ?>" class="regular-text" placeholder="Ex: Volto em breve"></td>
									</tr>
									<tr>
										<th scope="row"><label>Horário de Trabalho</label></th>
										<td>
											<div style="margin-bottom: 8px;">
												<label style="display: inline-block; width: 100px;">
													<input type="checkbox" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][auto_status]" value="yes" <?php checked( $user['auto_status'] ?? 'no', 'yes' ); ?>>
													<strong>Automático</strong>
												</label>
												<small style="color: #666;">Status muda automaticamente baseado no horário</small>
											</div>
											
											<div class="working-hours-fields" style="<?php echo ( isset( $user['auto_status'] ) && $user['auto_status'] === 'yes' ) ? '' : 'display: none;'; ?>">
												<div style="margin-bottom: 10px;">
													<label style="display: block; margin-bottom: 5px; font-weight: 600;">Dias da Semana:</label>
													<?php
													$days = array(
														'monday' => 'Segunda',
														'tuesday' => 'Terça',
														'wednesday' => 'Quarta',
														'thursday' => 'Quinta',
														'friday' => 'Sexta',
														'saturday' => 'Sábado',
														'sunday' => 'Domingo'
													);
													$active_days = $user['working_days'] ?? array( 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
													foreach ( $days as $day_key => $day_name ) :
													?>
														<label style="display: inline-block; margin-right: 12px;">
															<input type="checkbox" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][working_days][]" value="<?php echo $day_key; ?>" <?php checked( in_array( $day_key, $active_days ) ); ?>>
															<?php echo $day_name; ?>
														</label>
													<?php endforeach; ?>
												</div>
												
												<div style="display: flex; gap: 15px; align-items: center;">
													<div>
														<label style="display: block; margin-bottom: 3px;">Início:</label>
														<input type="time" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][work_start]" value="<?php echo esc_attr( $user['work_start'] ?? '09:00' ); ?>" style="padding: 5px;">
													</div>
													<div>
														<label style="display: block; margin-bottom: 3px;">Fim:</label>
														<input type="time" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][work_end]" value="<?php echo esc_attr( $user['work_end'] ?? '18:00' ); ?>" style="padding: 5px;">
													</div>
													<div style="flex: 1;">
														<label style="display: block; margin-bottom: 3px;">Fuso Horário:</label>
														<select name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][timezone]" style="width: 100%;">
															<?php
															$current_timezone = $user['timezone'] ?? 'America/Sao_Paulo';
															$timezones = array(
																'America/Sao_Paulo' => 'Brasília (GMT-3)',
																'America/Fortaleza' => 'Fortaleza (GMT-3)',
																'America/Recife' => 'Recife (GMT-3)',
																'America/Manaus' => 'Manaus (GMT-4)',
																'America/Rio_Branco' => 'Rio Branco (GMT-5)',
																'America/Noronha' => 'Fernando de Noronha (GMT-2)',
															);
															foreach ( $timezones as $tz => $label ) :
															?>
																<option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $current_timezone, $tz ); ?>><?php echo esc_html( $label ); ?></option>
															<?php endforeach; ?>
														</select>
													</div>
												</div>
												
												<div style="margin-top: 8px;">
													<small style="color: #666;">
														💡 O status mudará automaticamente para Online/Offline baseado neste horário
													</small>
												</div>
											</div>
											
											<div class="manual-hours-field" style="margin-top: 8px; <?php echo ( isset( $user['auto_status'] ) && $user['auto_status'] === 'yes' ) ? 'display: none;' : ''; ?>">
												<input type="text" name="dw_whatsapp_settings[multi_users][<?php echo $index; ?>][working_hours]" value="<?php echo esc_attr( $user['working_hours'] ?? '' ); ?>" class="regular-text" placeholder="Ex: 9:00 às 18:00 (apenas informativo)">
												<p class="description">Horário apenas informativo (não altera status automaticamente)</p>
											</div>
										</td>
									</tr>
								</table>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" id="add-user" class="button button-secondary">+ Adicionar Usuário</button>
				</td>
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

<script>
jQuery(document).ready(function($) {
	let userIndex = <?php echo count( $users ); ?>;
	const MAX_USERS = 10; // Limite de usuários
	
	// Toggle multi-users sections
	$('input[name="dw_whatsapp_settings[multi_users_enabled]"]').change(function() {
		if ($(this).is(':checked')) {
			$('#multi-users-section, #users-list-section').show();
		} else {
			$('#multi-users-section, #users-list-section').hide();
		}
	});
	
	// Add new user
	$('#add-user').click(function() {
		// Verificar limite de usuários
		if ($('.user-item').length >= MAX_USERS) {
			alert('Máximo de ' + MAX_USERS + ' usuários permitidos.');
			return;
		}
		
		const userHtml = `
			<div class="user-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 5px; background: #f9f9f9;">
				<h4>Usuário #${userIndex + 1} <button type="button" class="button remove-user" style="float: right; color: #d63638;">Remover</button></h4>
				<table class="form-table">
					<tr>
						<th scope="row"><label>Nome</label></th>
						<td><input type="text" name="dw_whatsapp_settings[multi_users][${userIndex}][name]" value="" class="regular-text" placeholder="Ex: João Silva" required></td>
					</tr>
					<tr>
						<th scope="row"><label>Telefone</label></th>
						<td><input type="text" name="dw_whatsapp_settings[multi_users][${userIndex}][phone]" value="" class="regular-text" placeholder="5519999999999" required></td>
					</tr>
					<tr>
						<th scope="row"><label>Departamento</label></th>
						<td><input type="text" name="dw_whatsapp_settings[multi_users][${userIndex}][department]" value="" class="regular-text" placeholder="Ex: Suporte, Vendas, Financeiro"></td>
					</tr>
					<tr>
						<th scope="row"><label>Avatar (URL)</label></th>
						<td><input type="url" name="dw_whatsapp_settings[multi_users][${userIndex}][avatar]" value="" class="regular-text" placeholder="https://exemplo.com/avatar.jpg"></td>
					</tr>
					<tr>
						<th scope="row"><label>Status</label></th>
						<td>
							<select name="dw_whatsapp_settings[multi_users][${userIndex}][status]">
								<option value="online">Online</option>
								<option value="away">Ausente</option>
								<option value="offline">Offline</option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row"><label>Mensagem de Status</label></th>
						<td><input type="text" name="dw_whatsapp_settings[multi_users][${userIndex}][status_message]" value="" class="regular-text" placeholder="Ex: Volto em breve"></td>
					</tr>
					<tr>
						<th scope="row"><label>Horário de Trabalho</label></th>
						<td><input type="text" name="dw_whatsapp_settings[multi_users][${userIndex}][working_hours]" value="" class="regular-text" placeholder="Ex: 9:00 às 18:00"></td>
					</tr>
				</table>
			</div>
		`;
		$('#users-container').append(userHtml);
		userIndex++;
		updateUserNumbers();
		updateAddButton();
	});
	
	// Remove user
	$(document).on('click', '.remove-user', function() {
		$(this).closest('.user-item').remove();
		updateUserNumbers();
		updateAddButton();
	});
	
	// Update user numbers
	function updateUserNumbers() {
		$('.user-item').each(function(index) {
			$(this).find('h4').html(`Usuário #${index + 1} <button type="button" class="button remove-user" style="float: right; color: #d63638;">Remover</button>`);
		});
	}
	
	// Update add button state
	function updateAddButton() {
		if ($('.user-item').length >= MAX_USERS) {
			$('#add-user').prop('disabled', true).text('Máximo de usuários atingido');
		} else {
			$('#add-user').prop('disabled', false).text('+ Adicionar Usuário');
		}
	}
	
	// Initial state
	updateAddButton();
	
	// Form validation
	$('form').on('submit', function(e) {
		let hasErrors = false;
		
		$('.user-item').each(function() {
			const name = $(this).find('input[name*="[name]"]').val().trim();
			const phone = $(this).find('input[name*="[phone]"]').val().trim();
			
			if (name && !phone) {
				alert('Usuário "' + name + '" precisa ter um telefone preenchido.');
				hasErrors = true;
				return false;
			}
			
			if (phone && !name) {
				alert('Usuário com telefone "' + phone + '" precisa ter um nome preenchido.');
				hasErrors = true;
				return false;
			}
		});
		
		if (hasErrors) {
			e.preventDefault();
			return false;
		}
	});
	
	// Toggle working hours fields
	$(document).on('change', 'input[name*="[auto_status]"]', function() {
		const $container = $(this).closest('td');
		const $workingHoursFields = $container.find('.working-hours-fields');
		const $manualHoursField = $container.find('.manual-hours-field');
		
		if ($(this).is(':checked')) {
			$workingHoursFields.slideDown();
			$manualHoursField.slideUp();
		} else {
			$workingHoursFields.slideUp();
			$manualHoursField.slideDown();
		}
	});
	
	// Drag and Drop para reordenar usuários
	let draggedElement = null;
	let draggedIndex = null;
	
	// Eventos de drag
	$(document).on('dragstart', '.user-item', function(e) {
		draggedElement = this;
		draggedIndex = $(this).index();
		$(this).css('opacity', '0.5');
		e.originalEvent.dataTransfer.effectAllowed = 'move';
		e.originalEvent.dataTransfer.setData('text/html', this.innerHTML);
	});
	
	$(document).on('dragend', '.user-item', function(e) {
		$(this).css('opacity', '1');
		$('.user-item').removeClass('drag-over');
	});
	
	$(document).on('dragover', '.user-item', function(e) {
		if (e.preventDefault) {
			e.preventDefault();
		}
		e.originalEvent.dataTransfer.dropEffect = 'move';
		
		// Visual feedback
		$('.user-item').removeClass('drag-over');
		if (this !== draggedElement) {
			$(this).addClass('drag-over');
		}
		
		return false;
	});
	
	$(document).on('dragenter', '.user-item', function(e) {
		if (this !== draggedElement) {
			$(this).css('border-color', '#25d366');
		}
	});
	
	$(document).on('dragleave', '.user-item', function(e) {
		$(this).css('border-color', '#ddd');
	});
	
	$(document).on('drop', '.user-item', function(e) {
		if (e.stopPropagation) {
			e.stopPropagation();
		}
		
		if (draggedElement !== this) {
			const dropIndex = $(this).index();
			
			// Reorganizar elementos
			if (draggedIndex < dropIndex) {
				$(this).after($(draggedElement));
			} else {
				$(this).before($(draggedElement));
			}
			
			// Atualizar índices dos inputs
			reindexUsers();
			updateUserNumbers();
		}
		
		$(this).css('border-color', '#ddd');
		return false;
	});
	
	// Função para reindexar os inputs após reordenação
	function reindexUsers() {
		$('#users-container .user-item').each(function(newIndex) {
			// Atualizar todos os inputs com o novo índice
			$(this).find('input, select, textarea').each(function() {
				const name = $(this).attr('name');
				if (name) {
					// Substituir o índice antigo pelo novo
					const newName = name.replace(/\[multi_users\]\[\d+\]/, '[multi_users][' + newIndex + ']');
					$(this).attr('name', newName);
				}
			});
		});
	}
	
	// CSS para o efeito drag over
	$('<style>')
		.text('.user-item.drag-over { border: 2px dashed #25d366 !important; background: #f0fff0 !important; }')
		.appendTo('head');
});
</script>



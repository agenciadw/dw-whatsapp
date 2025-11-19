<?php
/**
 * Custom fields page template
 *
 * @package DW_WhatsApp
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	
	<?php if ( isset( $_GET['saved'] ) && $_GET['saved'] == '1' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>Campo salvo com sucesso!</p>
		</div>
	<?php endif; ?>
	
	<?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == '1' ) : ?>
		<div class="notice notice-success is-dismissible">
			<p>Campo excluído com sucesso!</p>
		</div>
	<?php endif; ?>
	
	<?php if ( isset( $_GET['error'] ) && $_GET['error'] == '1' ) : ?>
		<div class="notice notice-error is-dismissible">
			<p>Erro ao salvar campo. Verifique os dados e tente novamente.</p>
		</div>
	<?php endif; ?>

	<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
		<!-- Formulário de Adicionar/Editar Campo -->
		<div>
			<h2><?php echo $editing_field ? 'Editar Campo' : 'Adicionar Novo Campo'; ?></h2>
			<form method="post" action="">
				<?php wp_nonce_field( 'dw_whatsapp_custom_field_action', 'dw_whatsapp_custom_field_nonce' ); ?>
				
				<table class="form-table">
					<tr>
						<th scope="row"><label for="field_label">Nome do Campo *</label></th>
						<td>
							<input type="text" id="field_label" name="field_label" value="<?php echo $editing_field ? esc_attr( $editing_field['field_label'] ) : ''; ?>" class="regular-text" required>
							<p class="description">Nome que será exibido no formulário</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="field_key">Chave do Campo</label></th>
						<td>
							<input type="text" id="field_key" name="field_key" value="<?php echo $editing_field ? esc_attr( $editing_field['field_key'] ) : ''; ?>" class="regular-text" <?php echo $editing_field ? 'readonly' : ''; ?>>
							<p class="description">Identificador único (gerado automaticamente se vazio)</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="field_type">Tipo de Campo *</label></th>
						<td>
							<select id="field_type" name="field_type" required>
								<option value="text" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'text' ); ?>>Texto Curto</option>
								<option value="textarea" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'textarea' ); ?>>Texto Longo</option>
								<option value="email" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'email' ); ?>>E-mail</option>
								<option value="tel" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'tel' ); ?>>Telefone</option>
								<option value="date" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'date' ); ?>>Data</option>
								<option value="number" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'number' ); ?>>Número</option>
								<option value="password" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'password' ); ?>>Senha</option>
								<option value="select" <?php selected( $editing_field ? $editing_field['field_type'] : '', 'select' ); ?>>Seleção</option>
							</select>
						</td>
					</tr>
					
					<tr id="field_options_row" style="<?php echo ( $editing_field && $editing_field['field_type'] === 'select' ) || ! $editing_field ? '' : 'display:none;'; ?>">
						<th scope="row"><label for="field_options">Opções (Seleção)</label></th>
						<td>
							<textarea id="field_options" name="field_options" rows="5" class="large-text"><?php 
								if ( $editing_field && $editing_field['field_type'] === 'select' ) {
									$options = json_decode( $editing_field['field_options'], true );
									if ( is_array( $options ) ) {
										echo esc_textarea( implode( "\n", $options ) );
									} else {
										echo esc_textarea( $editing_field['field_options'] );
									}
								}
							?></textarea>
							<p class="description">Uma opção por linha</p>
						</td>
					</tr>
					
					<tr>
						<th scope="row">Configurações</th>
						<td>
							<fieldset>
								<label>
									<input type="checkbox" name="is_required" value="1" <?php checked( $editing_field ? $editing_field['is_required'] : 0, 1 ); ?>>
									Campo obrigatório
								</label><br>
								<label>
									<input type="checkbox" name="show_in_whatsapp" value="1" <?php checked( $editing_field ? $editing_field['show_in_whatsapp'] : 1, 1 ); ?>>
									Exibir na mensagem do WhatsApp
								</label>
							</fieldset>
						</td>
					</tr>
					
					<tr>
						<th scope="row"><label for="field_order">Ordem</label></th>
						<td>
							<input type="number" id="field_order" name="field_order" value="<?php echo $editing_field ? esc_attr( $editing_field['field_order'] ) : '0'; ?>" min="0" style="width: 80px;">
							<p class="description">Ordem de exibição (menor número aparece primeiro)</p>
						</td>
					</tr>
				</table>
				
				<?php submit_button( $editing_field ? 'Atualizar Campo' : 'Adicionar Campo', 'primary', 'save_custom_field' ); ?>
				
				<?php if ( $editing_field ) : ?>
					<a href="<?php echo admin_url( 'admin.php?page=dw-whatsapp-custom-fields' ); ?>" class="button">Cancelar</a>
				<?php endif; ?>
			</form>
		</div>
		
		<!-- Lista de Campos -->
		<div>
			<h2>Campos Cadastrados</h2>
			<?php if ( empty( $fields ) ) : ?>
				<p>Nenhum campo customizado cadastrado ainda.</p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th>Nome</th>
							<th>Tipo</th>
							<th>Obrigatório</th>
							<th>No WhatsApp</th>
							<th>Ordem</th>
							<th>Ações</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $fields as $field ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $field['field_label'] ); ?></strong><br><small><?php echo esc_html( $field['field_key'] ); ?></small></td>
								<td><?php 
									$types = array(
										'text' => 'Texto Curto',
										'textarea' => 'Texto Longo',
										'email' => 'E-mail',
										'tel' => 'Telefone',
										'date' => 'Data',
										'number' => 'Número',
										'password' => 'Senha',
										'select' => 'Seleção',
									);
									echo esc_html( $types[ $field['field_type'] ] ?? $field['field_type'] );
								?></td>
								<td><?php echo $field['is_required'] ? '✓' : '-'; ?></td>
								<td><?php echo $field['show_in_whatsapp'] ? '✓' : '-'; ?></td>
								<td><?php echo esc_html( $field['field_order'] ); ?></td>
								<td>
									<a href="<?php echo admin_url( 'admin.php?page=dw-whatsapp-custom-fields&action=edit&field_key=' . esc_attr( $field['field_key'] ) ); ?>" class="button button-small">Editar</a>
									<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=dw-whatsapp-custom-fields&action=delete&field_key=' . esc_attr( $field['field_key'] ) ), 'delete_custom_field_' . $field['field_key'] ); ?>" class="button button-small" onclick="return confirm('Tem certeza que deseja excluir este campo?');">Excluir</a>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

<script>
jQuery(document).ready(function($) {
	$('#field_type').on('change', function() {
		if ($(this).val() === 'select') {
			$('#field_options_row').show();
		} else {
			$('#field_options_row').hide();
		}
	});
	
	$('#field_label').on('blur', function() {
		if (!$('#field_key').attr('readonly') && !$('#field_key').val()) {
			var key = $(this).val().toLowerCase()
				.replace(/[^a-z0-9\s-]/g, '')
				.replace(/\s+/g, '_')
				.replace(/-+/g, '_')
				.replace(/^_+|_+$/g, '');
			$('#field_key').val(key);
		}
	});
});
</script>


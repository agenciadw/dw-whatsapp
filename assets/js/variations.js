/**
 * DW WhatsApp - Manipulação de Variações de Produtos
 * 
 * Este script captura as variações selecionadas pelo usuário
 * e atualiza o link do WhatsApp dinamicamente
 */

(function($) {
    'use strict';
    
    // Espera o DOM estar pronto
    $(document).ready(function() {
        
        // Verifica se existem variações no produto
        var variationForm = $('form.variations_form');
        
        if (variationForm.length === 0) {
            return; // Não é um produto variável
        }
        
        // Função para obter variações selecionadas
        function getSelectedVariations() {
            var variations = {};
            var hasSelection = false;
            
            // Percorre todos os selects de variação
            $('table.variations select').each(function() {
                var $select = $(this);
                var value = $select.val();
                var label = $select.closest('tr').find('label').text().replace(':', '').trim();
                
                if (value && value !== '') {
                    // Tenta pegar o texto da opção selecionada
                    var selectedText = $select.find('option:selected').text();
                    variations[label] = selectedText;
                    hasSelection = true;
                }
            });
            
            return hasSelection ? variations : null;
        }
        
        // Função para formatar as variações para a mensagem
        function formatVariationsForMessage(variations) {
            if (!variations) {
                return '';
            }
            
            var text = '\n\nVariações selecionadas:';
            
            for (var key in variations) {
                if (variations.hasOwnProperty(key)) {
                    text += '\n• ' + key + ': ' + variations[key];
                }
            }
            
            return text;
        }
        
        // Função para atualizar o link do WhatsApp
        function updateWhatsAppLink() {
            var button = $('.dw-whatsapp-button[data-is-variable="1"]');
            
            if (button.length === 0) {
                return;
            }
            
            var productName = button.data('product-name');
            var productLink = button.data('product-link');
            var hasPrice = !$('.price-solicite-orcamento').length;
            
            // Determina qual template de mensagem usar
            var messageTemplate = hasPrice ? dwWhatsApp.messageTemplate : dwWhatsApp.messageTemplateNoPrice;
            
            // Substitui o nome do produto
            var message = messageTemplate.replace('{product_name}', productName);
            
            // Adiciona link do produto se configurado
            if (dwWhatsApp.includeLink) {
                message += ' - Link: ' + productLink;
            }
            
            // Adiciona variações selecionadas
            var variations = getSelectedVariations();
            if (variations) {
                message += formatVariationsForMessage(variations);
            }
            
            // Gera novo link
            var newLink = 'https://wa.me/' + dwWhatsApp.phone + '?text=' + encodeURIComponent(message);
            
            // Atualiza o href do botão
            button.attr('href', newLink);
        }
        
        // Atualiza quando uma variação é selecionada
        $('table.variations select').on('change', function() {
            // Pequeno delay para garantir que a variação foi processada
            setTimeout(updateWhatsAppLink, 100);
        });
        
        // Atualiza quando o formulário de variações é alterado
        variationForm.on('found_variation', function() {
            setTimeout(updateWhatsAppLink, 100);
        });
        
        // Atualiza quando as variações são resetadas
        variationForm.on('reset_data', function() {
            setTimeout(updateWhatsAppLink, 100);
        });
        
        // Suporte para plugins de swatch/variações visuais
        $(document).on('click', '.variations .swatch-wrapper, .variations_form .swatch', function() {
            setTimeout(updateWhatsAppLink, 200);
        });
        
        // Suporte para variações de rádio button
        $('input[name^="attribute_"]').on('change', function() {
            setTimeout(updateWhatsAppLink, 100);
        });
        
        // Atualização inicial
        updateWhatsAppLink();
    });
    
})(jQuery);

















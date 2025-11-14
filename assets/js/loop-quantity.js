/**
 * DW WhatsApp - Suporte para Quantidade no Loop/Catálogo
 * 
 * Este script captura a quantidade selecionada no catálogo
 * e atualiza o link do WhatsApp dinamicamente
 */

(function($) {
    'use strict';
    
    // Espera o DOM estar pronto
    $(document).ready(function() {
        
        // Função para atualizar o link do WhatsApp com a quantidade
        function updateWhatsAppLinkWithQuantity(productContainer) {
            var $container = $(productContainer);
            var $whatsappWrapper = $container.find('.dw-whatsapp-wrapper-loop');
            var $whatsappButton = $whatsappWrapper.find('.dw-whatsapp-button-loop');
            var $quantityInput = $container.find('input.qty');
            
            if ($whatsappButton.length === 0 || $quantityInput.length === 0) {
                return;
            }
            
            var currentHref = $whatsappButton.attr('href');
            if (!currentHref) {
                return;
            }
            
            // Extrai a mensagem atual do link
            var urlParts = currentHref.split('?text=');
            if (urlParts.length < 2) {
                return;
            }
            
            var baseUrl = urlParts[0];
            var message = decodeURIComponent(urlParts[1]);
            
            // Remove informação de quantidade anterior se existir
            message = message.replace(/\n\nQuantidade:\s*\d+/g, '');
            message = message.replace(/\s*-\s*Quantidade:\s*\d+/g, '');
            
            // Adiciona a quantidade atual
            var quantity = parseInt($quantityInput.val()) || 1;
            if (quantity > 1) {
                message += '\n\nQuantidade: ' + quantity;
            }
            
            // Atualiza o link
            var newLink = baseUrl + '?text=' + encodeURIComponent(message);
            $whatsappButton.attr('href', newLink);
        }
        
        // Monitora mudanças nos inputs de quantidade
        $(document).on('change', '.quantity input.qty', function() {
            var $productContainer = $(this).closest('.product, .product-grid-item, .product-list-item, .wd-product');
            updateWhatsAppLinkWithQuantity($productContainer);
        });
        
        // Monitora cliques nos botões + e -
        $(document).on('click', '.quantity .plus, .quantity .minus', function() {
            var $button = $(this);
            var $productContainer = $button.closest('.product, .product-grid-item, .product-list-item, .wd-product');
            
            // Pequeno delay para garantir que o valor foi atualizado
            setTimeout(function() {
                updateWhatsAppLinkWithQuantity($productContainer);
            }, 100);
        });
        
        // Suporte específico para Woodmart - atualiza quando o input perde o foco
        $(document).on('blur', '.quantity input.qty', function() {
            var $productContainer = $(this).closest('.product, .product-grid-item, .product-list-item, .wd-product');
            updateWhatsAppLinkWithQuantity($productContainer);
        });
        
        // Suporte para AJAX do WooCommerce (quando produtos são carregados dinamicamente)
        $(document.body).on('updated_wc_div', function() {
            $('.product, .product-grid-item, .product-list-item, .wd-product').each(function() {
                updateWhatsAppLinkWithQuantity(this);
            });
        });
        
        // Inicialização para todos os produtos visíveis
        $('.product, .product-grid-item, .product-list-item, .wd-product').each(function() {
            updateWhatsAppLinkWithQuantity(this);
        });
        
    });
    
})(jQuery);


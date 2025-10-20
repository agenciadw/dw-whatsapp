# DW WhatsApp para WooCommerce

[![Versão](https://img.shields.io/badge/versão-0.1.0-blue.svg)](https://github.com/agenciadw/dw-whatsapp)
[![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-brightgreen.svg)](https://wordpress.org/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple.svg)](https://woocommerce.com/)
[![Licença](https://img.shields.io/badge/licença-GPL%20v2-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Plugin profissional para adicionar botões de WhatsApp ao WooCommerce com suporte a variações de produtos, botão flutuante e sistema de orçamentos.

## 🎯 Funcionalidades

### Botões de WhatsApp
- ✅ Botão na página individual do produto
- ✅ Botão na listagem de produtos
- ✅ Botão flutuante configurável

### Produtos Variáveis
- ✅ Captura automática de variações (cor, tamanho, etc.)
- ✅ Atualização dinâmica da mensagem
- ✅ Compatível com plugins de variação visual

### Sistema de Orçamentos
- ✅ Detecta produtos sem preço automaticamente
- ✅ Remove botão "Adicionar ao Carrinho"
- ✅ Botão de solicitar orçamento via WhatsApp

### Painel de Configurações
- ✅ Interface intuitiva no WordPress Admin
- ✅ Mensagens personalizáveis
- ✅ Controle de exibição por página
- ✅ Seletor de cores integrado
- ✅ 4 posições para botão flutuante

## 📋 Requisitos

- WordPress 5.8 ou superior
- WooCommerce 5.0 ou superior
- PHP 7.4 ou superior
- ✅ Compatível com HPOS (High-Performance Order Storage)

## 🚀 Instalação

1. Faça upload da pasta `dw-whatsapp` para `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. Configure o plugin em **DW WhatsApp** no menu lateral

## ⚙️ Configuração

### Passo 1: Número do WhatsApp
Digite seu número com código do país (ex: 5519999999999)

### Passo 2: Configure Exibição
- Marque onde os botões devem aparecer
- Escolha páginas para ocultar o botão flutuante
- Defina a posição do botão flutuante

### Passo 3: Personalize Mensagens
Use `{product_name}` para inserir o nome do produto automaticamente

### Passo 4: Ajuste Estilos
Escolha a cor dos botões (padrão: verde WhatsApp #25d366)

## 🔧 Estrutura do Código

```
dw-whatsapp/
├── dw-whatsapp.php                    # Bootstrap do plugin
├── uninstall.php                      # Limpeza na desinstalação
├── includes/
│   ├── class-dw-whatsapp.php          # Classe principal
│   ├── class-dw-whatsapp-settings.php # Gerenciamento de configurações
│   └── class-dw-whatsapp-frontend.php # Funcionalidades do frontend
├── admin/
│   ├── class-dw-whatsapp-admin.php    # Painel administrativo
│   └── views/
│       └── settings-page.php          # Template da página de configurações
└── assets/
    └── js/
        └── variations.js              # JavaScript para variações
```

## 🎨 Customização

### Classes CSS Disponíveis
- `.dw-whatsapp-button` - Botão na página do produto
- `.dw-whatsapp-button-loop` - Botão no loop de produtos
- `.dw-whatsapp-floating-button` - Botão flutuante

### Exemplo de CSS Personalizado
```css
.dw-whatsapp-button {
    border-radius: 10px !important;
}
```

## 📱 Compatibilidade

- ✅ Totalmente responsivo
- ✅ Compatível com HPOS
- ✅ Multisite
- ✅ Temas responsivos
- ✅ Plugins de variação visual
- ✅ Plugins de cache

## 🔒 Segurança

- ✅ Sanitização completa de inputs
- ✅ Escape de todos os outputs
- ✅ Nonces em formulários
- ✅ Verificação de permissões
- ✅ Validação de dados
- ✅ Proteção contra XSS e CSRF

## 🆘 Suporte

- **GitHub:** [github.com/agenciadw/dw-whatsapp](https://github.com/agenciadw/dw-whatsapp)
- **Issues:** [github.com/agenciadw/dw-whatsapp/issues](https://github.com/agenciadw/dw-whatsapp/issues)
- **Email:** david@dwdigital.com.br

## 📝 Changelog

### 0.1.0 (2025-10-18)
- Lançamento inicial
- Botões de WhatsApp em produtos
- Botão flutuante configurável
- Suporte a variações de produtos
- Sistema de orçamentos
- Compatibilidade HPOS
- Controle de páginas para ocultar botão flutuante

## 📄 Licença

GPL v2 ou posterior

## 👨‍💻 Autor

**David William da Costa**
- GitHub: [@agenciadw](https://github.com/agenciadw)
- Website: [DW Digital](https://dwdigital.com.br)

---

Desenvolvido com ❤️ por [David William da Costa](https://github.com/agenciadw)

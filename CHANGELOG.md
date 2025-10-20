# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.1.0] - 2025-10-18

### Added
- Plugin inicial com estrutura orientada a objetos
- Botão de WhatsApp na página individual do produto
- Botão de WhatsApp na listagem de produtos (loop)
- Botão flutuante configurável em todas as páginas
- Suporte completo a produtos variáveis
- Captura automática de variações selecionadas (cor, tamanho, etc.)
- Sistema de orçamento para produtos sem preço
- Painel de configurações completo no admin
- Compatibilidade com HPOS (High-Performance Order Storage)
- Controle de páginas onde ocultar o botão flutuante
- Seletor de cores integrado (WordPress Color Picker)
- Mensagens personalizáveis com variável {product_name}
- 4 posições configuráveis para botão flutuante
- Sanitização completa de inputs
- Escape de todos os outputs
- Nonces para proteção CSRF
- Verificação de permissões
- Autoloader para classes
- Estrutura modular e extensível

### Security
- Sanitização de número de telefone
- Validação de cores hexadecimais
- Whitelist de páginas permitidas
- Proteção contra XSS
- Proteção contra CSRF
- Verificação de capacidades do usuário

### Technical
- WordPress 5.8+ compatibility
- WooCommerce 5.0+ compatibility
- PHP 7.4+ requirement
- HPOS (High-Performance Order Storage) compatible
- Object-oriented architecture
- PSR-4 autoloading
- Separation of concerns (MVC pattern)
- Clean code principles
- Well-documented codebase

---

## Links

- [GitHub Repository](https://github.com/agenciadw/dw-whatsapp)
- [Report Issues](https://github.com/agenciadw/dw-whatsapp/issues)
- [Documentation](README.md)

---

**Mantido por David William da Costa**  
GitHub: [@agenciadw](https://github.com/agenciadw)

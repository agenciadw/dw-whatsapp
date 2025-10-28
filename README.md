# DW WhatsApp para WooCommerce

Plugin WordPress profissional para integração completa do WhatsApp com WooCommerce. Adiciona botões de WhatsApp em produtos e botão flutuante em todas as páginas. **Funciona perfeitamente com ou sem WooCommerce instalado!**

[![WordPress](https://img.shields.io/badge/WordPress-5.0+-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.4+-green.svg)](https://php.net/)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-Optional-orange.svg)](https://woocommerce.com/)
[![License](https://img.shields.io/badge/License-GPL%20v2+-red.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 🚀 Funcionalidades Principais

### ✨ **Botão Flutuante Avançado**
- **4 posições**: Inferior Direito, Inferior Esquerdo, Superior Direito, Superior Esquerdo
- **Ajuste fino**: Offset horizontal e vertical (-100px a +100px)
- **3 tamanhos**: Pequeno, Médio, Grande
- **2 estilos**: Retangular com texto ou Circular com hover
- **Tooltip inteligente**: Posicionamento automático baseado na posição
- **Design responsivo**: Adaptação automática para mobile

### 🎭 **Dois Estilos de Botão**
- **Estilo Retangular**: Botão com texto dentro (padrão)
- **Estilo Circular**: Ícone circular com texto no hover
- **Transições suaves**: Animações CSS profissionais
- **Hover interativo**: Tooltip aparece ao passar o mouse

### ⏰ **Sistema de Horários Diferenciados**
- **Horários por dia da semana**: Configure horários diferentes para cada dia
- **Exemplos práticos**: Segunda a Sexta 08:00-17:00, Sábado 08:00-12:00
- **Status automático**: Online/Offline baseado nos horários configurados
- **Fusos horários brasileiros**: Suporte completo aos fusos do Brasil
- **Formatação inteligente**: Agrupa dias com horários iguais

### 👥 **Sistema de Múltiplos Usuários**
- **Até 10 usuários**: Configure múltiplos atendentes
- **Status individual**: Cada usuário pode ter status diferente
- **Avatars personalizados**: Upload de fotos para cada usuário
- **Horários individuais**: Cada usuário pode ter horários diferentes
- **Mensagens personalizadas**: Texto específico para cada usuário
- **Widget de chat**: Interface moderna para escolher o atendente

### 🛒 **Integração Completa com WooCommerce**
- **Botões em páginas de produto**: Integração nativa com WooCommerce
- **Botões em listagem de produtos**: Aparece na loja e categorias
- **Integração com variações**: Suporte completo a produtos variáveis
- **Links de produtos**: Inclui link do produto nas mensagens
- **Controle de produtos sem preço**: Botão especial para produtos sem preço
- **Compatibilidade HPOS**: Suporte ao High-Performance Order Storage

### 🎨 **Interface e Design**
- **Design responsivo**: Funciona perfeitamente em mobile
- **Ícone SVG do WhatsApp**: Ícone vetorial de alta qualidade
- **Animações suaves**: Transições e efeitos hover
- **Cores personalizáveis**: Escolha a cor dos botões
- **Mensagens personalizáveis**: Configure textos para cada situação

## 📦 Instalação

### Método 1: Upload via WordPress Admin
1. Faça upload do arquivo `dw-whatsapp.zip` através do painel administrativo do WordPress
2. Ative o plugin
3. Configure o número do WhatsApp nas configurações
4. Personalize as mensagens e aparência

### Método 2: Upload via FTP
1. Extraia o arquivo `dw-whatsapp.zip`
2. Faça upload da pasta `dw-whatsapp` para `/wp-content/plugins/`
3. Ative o plugin através do painel administrativo

## ⚙️ Configuração

### 🔧 Configurações Básicas
- **Número do WhatsApp**: Digite com código do país e DDD (ex: 5519999999999)
- **Cor dos Botões**: Escolha a cor dos botões
- **Posição do Botão Flutuante**: 4 posições disponíveis

### 🎨 Configurações Avançadas do Botão
- **Estilo do Botão**: Retangular com texto ou Circular com hover
- **Posição Horizontal**: Ajuste fino (-100px a +100px)
- **Posição Vertical**: Ajuste fino (-100px a +100px)
- **Tamanho do Botão**: Pequeno, Médio ou Grande

### 💬 Mensagens Personalizáveis
- **Mensagem para Produtos com Preço**: Use `{product_name}` para inserir o nome do produto
- **Mensagem para Produtos sem Preço**: Mensagem para solicitação de orçamento
- **Mensagem do Botão Flutuante**: Mensagem padrão para contato geral
- **Textos dos Botões**: Personalize os textos dos botões

### 👥 Sistema de Múltiplos Usuários
- **Ative o sistema**: Marque "Habilitar múltiplos usuários"
- **Configure usuários**: Nome, telefone, departamento e avatar
- **Horários individuais**: Configure horários diferentes para cada dia da semana
- **Status automático**: Online/Offline baseado nos horários
- **Fusos horários**: Escolha o fuso horário de cada usuário

## 🎨 Personalização Avançada

### 📍 Posicionamento do Botão
- **Inferior Direito**: Padrão, ideal para a maioria dos sites
- **Inferior Esquerdo**: Alternativa para layouts específicos
- **Superior Direito**: Para sites com muito conteúdo inferior
- **Superior Esquerdo**: Para layouts não convencionais
- **Ajuste fino**: Offset horizontal e vertical para posicionamento perfeito

### 🎭 Estilos de Botão
- **Retangular**: Botão com texto dentro, ideal para desktop
- **Circular**: Ícone circular com tooltip, ideal para designs minimalistas
- **Tooltip inteligente**: Aparece automaticamente no lado correto

### 📱 Responsividade
- **Mobile-first**: Design otimizado para dispositivos móveis
- **Adaptação automática**: Botão se adapta ao tamanho da tela
- **Tooltip desabilitado em mobile**: Melhor experiência em touch
- **Posicionamento otimizado**: Posições ajustadas para mobile

## 🔧 Compatibilidade

- **WordPress**: 5.0 ou superior
- **PHP**: 7.4 ou superior
- **WooCommerce**: Opcional (funciona perfeitamente sem)
- **Testado até**: WordPress 6.4
- **Navegadores**: Chrome, Firefox, Safari, Edge

## 📱 Funcionalidades Mobile

- **Botão flutuante otimizado**: Para touch e gestos
- **Texto oculto em telas pequenas**: Interface limpa
- **Ícone do WhatsApp sempre visível**: Reconhecimento imediato
- **Abertura direta no aplicativo**: WhatsApp nativo
- **Posicionamento inteligente**: Evita conflitos com outros elementos

## 🌐 Internacionalização

- **Texto em português brasileiro**: Interface completamente em português
- **Suporte a múltiplos idiomas**: Estrutura preparada para tradução
- **Fusos horários brasileiros**: Todos os fusos do Brasil configurados
- **Formatação brasileira**: Horários e datas no formato brasileiro

## 🔒 Segurança

- **Verificação de acesso direto**: Proteção contra acesso não autorizado
- **Sanitização completa**: Todos os inputs são sanitizados
- **Validação de dados**: Validação rigorosa de todos os dados
- **Nonce para formulários**: Proteção contra CSRF
- **Escape de output**: Proteção contra XSS

## 📞 Suporte e Desenvolvimento

- **Desenvolvedor**: David William da Costa
- **Website**: https://dwdigital.com.br
- **GitHub**: https://github.com/agenciadw/dw-whatsapp
- **Email**: david@dwdigital.com.br

## 📄 Licença

GPL v2 ou posterior - https://www.gnu.org/licenses/gpl-2.0.html

## 🔄 Changelog

### Versão 1.0.0 - 28-10-2025
- 🎉 **Lançamento inicial** com funcionalidades completas
- ✨ **Sistema de posicionamento avançado** com ajuste fino
- 🎭 **Dois estilos de botão** (retangular e circular)
- ⏰ **Sistema de horários diferenciados** por dia da semana
- 👥 **Sistema de múltiplos usuários** com status automático
- 🛒 **Integração completa com WooCommerce** (opcional)
- 🎨 **Design responsivo** e interface moderna
- 🔒 **Segurança completa** e código limpo

---

**Desenvolvido com ❤️ por David William da Costa**

*Plugin profissional para integração WhatsApp com WordPress/WooCommerce*
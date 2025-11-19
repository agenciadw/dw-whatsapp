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
- **Seletor de quantidade no loop**: Suporte nativo para seletores de quantidade no catálogo
- **Compatibilidade Woodmart**: Integração completa com o tema Woodmart
- **Links de produtos**: Inclui link do produto nas mensagens
- **Controle de produtos sem preço**: Botão especial para produtos sem preço
- **Compatibilidade HPOS**: Suporte ao High-Performance Order Storage

### 📋 **Sistema de Captura de Leads**
- **Captura de dados antes do WhatsApp**: Modal elegante para capturar nome, e-mail e telefone
- **Campos configuráveis**: Escolha quais campos exibir e quais são obrigatórios
- **Campos customizados**: Crie campos personalizados (texto, e-mail, telefone, data, número, senha, seleção)
- **Máscara de telefone brasileiro**: Formatação automática (99) 99999-9999
- **Validação em tempo real**: Validação de campos obrigatórios e formato de e-mail
- **Design moderno**: Modal com gradiente verde WhatsApp e animações suaves
- **Cache busting**: CSS versionado automaticamente para evitar problemas de cache
- **Integração Google Tag Manager**: Envio automático de dados para dataLayer do GTM

### 📊 **Gerenciamento de Leads**
- **Base de dados dedicada**: Tabela customizada para armazenar todos os leads
- **Detecção de clientes WooCommerce**: Identifica automaticamente se o lead é cliente
- **Agrupamento inteligente**: Contatos duplicados são agrupados com contador de contatos
- **Histórico completo**: Primeiro e último contato registrados
- **Visualização detalhada**: Modal popup para ver todos os dados do lead incluindo campos customizados
- **Exportação completa**: Exporte leads em CSV ou Excel incluindo campos customizados
- **Busca avançada**: Busque leads por nome, e-mail ou telefone
- **Paginação**: Navegação fácil através de grandes volumes de leads
- **Tabela responsiva**: Layout adaptável com redimensionamento de colunas por arraste

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

### 📋 Captura de Dados de Contato
- **Habilitar captura**: Ative ou desative a captura de dados antes de enviar para WhatsApp
- **Campos a exibir**: Escolha quais campos mostrar (Nome, E-mail, Telefone)
- **Campos obrigatórios**: Defina quais campos são obrigatórios
- **Título e subtítulo**: Personalize os textos do modal de captura
- **Máscara automática**: Telefone formatado automaticamente no padrão brasileiro

### 🎨 **Campos Customizados**
- **Crie campos personalizados**: Adicione quantos campos quiser ao formulário
- **Tipos de campo**: Texto curto, texto longo, e-mail, telefone, data, número, senha, seleção
- **Campos obrigatórios**: Defina quais campos são obrigatórios
- **Exibir no WhatsApp**: Escolha se o campo aparece na mensagem do WhatsApp ou só no banco
- **Opções de seleção**: Configure opções para campos do tipo seleção
- **Ordem personalizada**: Defina a ordem de exibição dos campos
- **Gerenciamento completo**: Interface admin para criar, editar e excluir campos

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
- **Testado até**: WordPress 6.8
- **Temas compatíveis**: Woodmart e outros temas WooCommerce
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

### 🔗 **Integração Google Tag Manager**
- **Evento automático**: Envio de evento `whatsapp_lead_capture` para dataLayer
- **Dados completos**: Nome, e-mail, telefone e todos os campos customizados
- **Estrutura organizada**: Dados agrupados em `lead_data` e `custom_fields`
- **Timestamp automático**: Data/hora de captura incluída automaticamente
- **Versão do widget**: Identificação da versão do plugin nos dados

## 📄 Licença

GPL v2 ou posterior - https://www.gnu.org/licenses/gpl-2.0.html

## 🔄 Changelog

### Versão 2.0.1 - 18-11-2025
- 🎨 **Sistema de Campos Customizados**: Crie campos personalizados no formulário de captura
- 📋 **Tipos de campo**: Texto curto, texto longo, e-mail, telefone, data, número, senha, seleção
- ⚙️ **Configurações avançadas**: Defina se o campo é obrigatório e se aparece no WhatsApp
- 🔗 **Integração Google Tag Manager**: Envio automático de dados para dataLayer do GTM
- 👁️ **Visualização de Leads**: Modal popup para ver todos os dados do lead
- 📊 **Exportação melhorada**: Campos customizados incluídos na exportação CSV/Excel
- 📱 **Tabela responsiva**: Layout adaptável com redimensionamento de colunas por arraste
- 🎯 **Melhorias na interface**: Botão "Ver Lead" e melhor organização dos dados

### Versão 2.0.0 - 13-11-2025
- 🎉 **Lançamento da versão 2.0** com sistema completo de captura e gerenciamento de leads
- 📋 **Sistema de Captura de Dados**: Modal elegante para capturar nome, e-mail e telefone antes de enviar para WhatsApp
- 🎨 **Design do Modal**: Interface moderna com gradiente verde WhatsApp, animações suaves e botão de fechar posicionado
- ✅ **Validação em Tempo Real**: Validação de campos obrigatórios e formato de e-mail antes de enviar
- 📱 **Máscara de Telefone Brasileiro**: Formatação automática (99) 99999-9999 para celular e (99) 9999-9999 para fixo
- 💾 **Base de Dados de Leads**: Tabela customizada para armazenar todos os leads capturados
- 🔍 **Detecção de Clientes WooCommerce**: Identifica automaticamente se o lead é cliente pelo e-mail
- 📊 **Gerenciamento de Leads**: Página dedicada no admin para visualizar, buscar e gerenciar leads
- 🔄 **Agrupamento Inteligente**: Contatos duplicados são agrupados com contador de quantas vezes entraram em contato
- 📥 **Exportação de Dados**: Exporte leads em CSV ou Excel com formatação profissional
- 🎯 **Busca e Paginação**: Sistema completo de busca e paginação para grandes volumes de dados
- 🚀 **Performance**: Otimizações de código e limpeza de buffers para exportação limpa
- 🧹 **Clean Code**: Código limpo, organizado e pronto para produção

### Versão 1.0.2 - 11-11-2025
- 🎯 **Suporte para seletor de quantidade no loop/catálogo**: Integração completa com tema Woodmart
- 🔄 **Sincronização automática**: Quantidade selecionada é incluída automaticamente na mensagem do WhatsApp
- 📊 **Monitoramento inteligente**: Detecta mudanças em botões +/-, digitação direta e perda de foco
- 🎨 **Estilos otimizados**: CSS específico para melhor integração visual com Woodmart
- ⚡ **Suporte AJAX**: Funciona com carregamento dinâmico de produtos
- 📱 **Totalmente responsivo**: Funciona perfeitamente em mobile e desktop
- 🎭 **Animação no hover**: Efeito suave de elevação ao passar o mouse no botão WhatsApp

### Versão 1.0.1 - 30-10-2025
- 🔧 Mensagens do botão flutuante separadas por contexto:
  - Nova: "Mensagem do Botão Flutuante (páginas de produto)" com suporte a `{product_name}`
  - Mantida: "Mensagem do Botão Flutuante" para páginas comuns
- 🧠 Substituição automática de `{product_name}` nas páginas de produto (botão flutuante e widget de múltiplos usuários)
- 🔗 Inclusão opcional do link do produto nas mensagens do botão flutuante em páginas de produto, respeitando "Incluir na Mensagem > Link do produto"
- ✂️ Uso de shortlink do WordPress (`wp_get_shortlink`) quando disponível; fallback para permalink

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
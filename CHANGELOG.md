# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [1.0.1] - 2025-10-30

### Adicionado
- Mensagem específica para páginas de produto no botão flutuante, com suporte a `{product_name}`
- Substituição automática de `{product_name}` nas mensagens do botão flutuante (single user e multi users) em páginas de produto
- Inclusão opcional do link do produto na mensagem do botão flutuante em páginas de produto, respeitando a configuração "Link do produto"

### Alterado
- Uso de `wp_get_shortlink` para encurtar a URL do produto quando disponível; fallback para `get_permalink`

## [1.0.0] - 2024-12-19

### 🎉 Lançamento Inicial

#### ✨ Funcionalidades Principais
- **Botão flutuante do WhatsApp** em todas as páginas
- **Integração completa com WooCommerce** (opcional)
- **Sistema de múltiplos usuários** (até 10 usuários)
- **Status automático** baseado em horários de trabalho
- **Funciona com ou sem WooCommerce** instalado

#### 🎨 Sistema de Posicionamento Avançado
- **4 posições básicas**: Inferior Direito, Inferior Esquerdo, Superior Direito, Superior Esquerdo
- **Ajuste fino de posição**: Offset horizontal e vertical (-100px a +100px)
- **3 tamanhos**: Pequeno, Médio, Grande
- **Posicionamento inteligente** do widget de chat

#### 🎭 Dois Estilos de Botão
- **Estilo Retangular**: Botão com texto dentro (padrão)
- **Estilo Circular**: Ícone circular com texto no hover
- **Tooltip inteligente** com posicionamento automático
- **Transições suaves** e animações CSS

#### ⏰ Sistema de Horários Diferenciados
- **Horários por dia da semana**: Configure horários diferentes para cada dia
- **Exemplos práticos**: Segunda a Sexta 08:00-17:00, Sábado 08:00-12:00
- **Status automático**: Online/Offline baseado nos horários configurados
- **Fusos horários brasileiros**: Suporte completo aos fusos do Brasil
- **Formatação inteligente**: Agrupa dias com horários iguais

#### 🛒 Funcionalidades do WooCommerce
- **Botões em páginas de produto**: Integração nativa com WooCommerce
- **Botões em listagem de produtos**: Aparece na loja e categorias
- **Integração com variações**: Suporte completo a produtos variáveis
- **Links de produtos**: Inclui link do produto nas mensagens
- **Controle de produtos sem preço**: Botão especial para produtos sem preço
- **Compatibilidade HPOS**: Suporte ao High-Performance Order Storage

#### 👥 Sistema de Múltiplos Usuários
- **Até 10 usuários**: Configure múltiplos atendentes
- **Status individual**: Cada usuário pode ter status diferente
- **Avatars personalizados**: Upload de fotos para cada usuário
- **Horários individuais**: Cada usuário pode ter horários diferentes
- **Mensagens personalizadas**: Texto específico para cada usuário
- **Widget de chat**: Interface moderna para escolher o atendente

#### 🎨 Interface e Design
- **Design responsivo**: Funciona perfeitamente em mobile
- **Ícone SVG do WhatsApp**: Ícone vetorial de alta qualidade
- **Animações suaves**: Transições e efeitos hover
- **Cores personalizáveis**: Escolha a cor dos botões
- **Mensagens personalizáveis**: Configure textos para cada situação

#### ⚙️ Configurações Avançadas
- **Página de configurações intuitiva**: Interface amigável no admin
- **Drag & drop**: Reordene usuários facilmente
- **Validação de formulários**: Feedback em tempo real
- **Indicadores visuais**: Status Online/Offline claros
- **Configurações condicionais**: Opções aparecem conforme necessário

#### 🔧 Arquitetura Técnica
- **Orientação a objetos**: Código limpo e organizado
- **Hooks do WordPress**: Integração nativa com WordPress/WooCommerce
- **Carregamento condicional**: Funcionalidades carregam conforme necessário
- **Detecção automática**: Detecta WooCommerce automaticamente
- **Constantes definidas**: Estrutura profissional do plugin

#### 🔒 Segurança
- **Verificação de acesso direto**: Proteção contra acesso não autorizado
- **Sanitização completa**: Todos os inputs são sanitizados
- **Validação de dados**: Validação rigorosa de todos os dados
- **Nonce para formulários**: Proteção contra CSRF
- **Escape de output**: Proteção contra XSS

#### 📱 Responsividade
- **Mobile-first**: Design otimizado para dispositivos móveis
- **Adaptação automática**: Botão se adapta ao tamanho da tela
- **Tooltip desabilitado em mobile**: Melhor experiência em touch
- **Posicionamento otimizado**: Posições ajustadas para mobile

#### 🌍 Internacionalização
- **Suporte a fusos horários**: Todos os fusos brasileiros
- **Formatação de horários**: Formato brasileiro de horários
- **Mensagens em português**: Interface completamente em português
- **Timezone automático**: Detecta fuso horário automaticamente

---

**🎯 Este é o lançamento inicial do plugin DW WhatsApp para WooCommerce. Todas as funcionalidades foram desenvolvidas, testadas e otimizadas para funcionar perfeitamente tanto com quanto sem o WooCommerce instalado, oferecendo máxima flexibilidade e facilidade de uso.**
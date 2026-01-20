# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [2.1.0] - 2026-01-20

### Adicionado
- **Finalizar venda via WhatsApp (Carrinho)**: botão no carrinho para enviar os itens e totais para o WhatsApp e registrar uma cotação no site.
- **Cotações no admin**: nova página “Cotações” para listar e visualizar cotações geradas pelo carrinho via WhatsApp.
- **Opções de checkout**: configuração para manter/ocultar o botão padrão de checkout e bloquear a página de checkout (opcional).

### Melhorado
- **Mensagem do carrinho**: formatação com quebras de linha e valores sem HTML/entities, com fallback para garantir legibilidade no WhatsApp.

## [2.0.2] - 2026-01-17

### Corrigido
- **Compatibilidade com WooCommerce Bookings**: produtos do tipo Booking não são mais tratados como “sem preço”, evitando ocultar o calendário e o cálculo por pessoas/participantes.

### Técnico
- Atualizado `DW_WhatsApp_Frontend` para detectar produto `booking` e não aplicar as regras de “sem preço” (ex.: `woocommerce_is_purchasable`, `woocommerce_get_price_html`, remoção do add-to-cart).

## [2.0.1] - 2025-11-18

### Adicionado
- **Sistema de Campos Customizados**: Crie campos personalizados no formulário de captura de leads
- **Interface de gerenciamento**: Página admin dedicada para criar, editar e excluir campos customizados
- **Tipos de campo suportados**: Texto curto, texto longo, e-mail, telefone, data, número, senha, seleção
- **Configurações por campo**: Defina se o campo é obrigatório e se aparece na mensagem do WhatsApp
- **Opções de seleção**: Configure múltiplas opções para campos do tipo seleção (uma por linha)
- **Ordem personalizada**: Defina a ordem de exibição dos campos no formulário
- **Integração Google Tag Manager**: Envio automático de dados para dataLayer do GTM
- **Evento GTM**: Evento `whatsapp_lead_capture` com todos os dados do lead
- **Modal de visualização**: Botão "Ver Lead" que abre popup com todos os dados do lead
- **Campos customizados na exportação**: Campos personalizados incluídos como colunas na exportação CSV/Excel
- **Tabela responsiva**: Layout adaptável para dispositivos móveis
- **Redimensionamento de colunas**: Arraste e redimensione colunas da tabela de leads
- **Layout mobile**: Tabela se transforma em cards em telas pequenas

### Melhorado
- **Exportação de leads**: Agora inclui todos os campos customizados como colunas adicionais
- **DataLayer do GTM**: Estrutura melhorada com campos customizados agrupados
- **Interface de leads**: Tabela mais funcional e responsiva
- **Experiência do usuário**: Visualização rápida de todos os dados do lead sem sair da página
- **Organização de dados**: Campos customizados exibidos em seção separada no modal

### Técnico
- Criada classe `DW_WhatsApp_Custom_Fields` para gerenciamento de campos customizados
- Tabela `wp_dw_whatsapp_custom_fields` para armazenar configurações dos campos
- Tabela `wp_dw_whatsapp_lead_fields` para armazenar valores dos campos por lead
- Método `get_lead_fields_by_contact()` para buscar campos do lead mais recente
- AJAX handler `ajax_get_lead_details()` para buscar dados completos do lead
- Função JavaScript `enviarParaDataLayer()` para integração com GTM
- CSS responsivo para tabela com media queries
- JavaScript para redimensionamento de colunas com drag & drop
- Atributos `data-label` nos `<td>` para layout mobile

## [2.0.0] - 2025-11-13

### Adicionado
- **Sistema completo de captura de leads**: Modal elegante para capturar nome, e-mail e telefone antes de enviar para WhatsApp
- **Configurações de captura**: Opções para escolher quais campos exibir e quais são obrigatórios
- **Máscara de telefone brasileiro**: Formatação automática (99) 99999-9999 para celular e (99) 9999-9999 para fixo
- **Validação em tempo real**: Validação de campos obrigatórios e formato de e-mail antes de enviar
- **Base de dados de leads**: Tabela customizada `wp_dw_whatsapp_leads` para armazenar todos os leads
- **Detecção automática de clientes**: Identifica se o lead é cliente WooCommerce pelo e-mail
- **Página de gerenciamento de leads**: Interface completa no admin para visualizar, buscar e gerenciar leads
- **Agrupamento inteligente**: Contatos duplicados são agrupados com contador de quantas vezes entraram em contato
- **Exportação CSV e Excel**: Exporte leads em formato CSV ou Excel com formatação profissional
- **Busca avançada**: Busque leads por nome, e-mail ou telefone
- **Paginação**: Sistema de paginação para grandes volumes de dados
- **Histórico de contatos**: Primeiro e último contato registrados para cada lead

### Melhorado
- **Design do modal**: Interface moderna com gradiente verde WhatsApp, animações suaves e botão de fechar posicionado
- **Cache busting**: CSS versionado automaticamente usando `filemtime()` para evitar problemas de cache
- **Performance de exportação**: Limpeza de buffers e headers corretos para exportação limpa sem HTML
- **Formato Excel**: Geração de arquivo Excel em formato XML SpreadsheetML compatível com Excel 2003+
- **Formatação de telefone**: Telefones exibidos e exportados com máscara formatada
- **Experiência do usuário**: Botão "Ir para o WhatsApp" quando nenhum campo é obrigatório

### Corrigido
- **Exportação limpa**: Arquivos CSV e Excel agora são exportados sem HTML da página admin
- **Posicionamento do botão fechar**: Botão de fechar corretamente posicionado no canto superior direito
- **Detecção de clientes**: Verificação melhorada para identificar clientes WooCommerce pelo e-mail
- **Agrupamento de contatos**: Query SQL otimizada para agrupar contatos duplicados corretamente

### Técnico
- Criada classe `DW_WhatsApp_Leads` para gerenciamento completo de leads
- Método `create_table()` para criar tabela de leads na ativação do plugin
- Método `save_lead()` para salvar leads via AJAX
- Método `get_leads()` com suporte a paginação e busca
- Método `get_all_leads_for_export()` para exportação com agrupamento
- Método `check_is_customer()` para detectar clientes WooCommerce
- AJAX handler `ajax_save_lead()` para processar salvamento de leads
- Função `export_leads()` com suporte a CSV e Excel
- Função `format_phone()` para formatação de telefones
- Hook `admin_init` para processar exportações antes de qualquer renderização
- Limpeza de buffers de saída para exportação limpa
- Uso de `nocache_headers()` para evitar cache em exportações

## [1.0.2] - 2025-11-11

### Adicionado
- **Suporte para seletor de quantidade no loop/catálogo do Woodmart**: Novo script JavaScript (`loop-quantity.js`) que captura a quantidade selecionada no catálogo e atualiza automaticamente o link do WhatsApp
- **Monitoramento de eventos de quantidade**: Detecta mudanças nos inputs de quantidade, cliques nos botões + e -, e perda de foco nos campos
- **Suporte a AJAX do WooCommerce**: Atualiza links quando produtos são carregados dinamicamente
- **Estilos CSS específicos para Woodmart**: Melhor visualização e integração com o tema Woodmart
- **Animação no hover do botão do WhatsApp**: Efeito suave de elevação ao passar o mouse
- **Wrapper dedicado para botão do WhatsApp**: Novo container `.dw-whatsapp-wrapper-loop` para melhor isolamento e controle de layout

### Melhorado
- **Compatibilidade com tema Woodmart**: Integração completa com os seletores de quantidade do tema
- **Experiência do usuário no catálogo**: Quantidade selecionada é enviada automaticamente na mensagem do WhatsApp
- **Performance**: Carregamento condicional do script apenas quando o botão do WhatsApp está ativo no loop
- **Posicionamento do botão**: Botão agora é inserido em wrapper separado, evitando sobreposição com seletor de quantidade

### Corrigido
- **Problema de sobreposição no Woodmart**: Botão do WhatsApp não sobrepõe mais o seletor de quantidade
- **Hook correto**: Alterado de `woocommerce_loop_add_to_cart_link` (filtro) para `woocommerce_after_shop_loop_item` (ação) para melhor compatibilidade

### Técnico
- Criado novo arquivo `assets/js/loop-quantity.js` para gerenciar quantidade no loop
- Atualizado `class-dw-whatsapp-frontend.php`:
  - Alterado hook de filtro para ação (`woocommerce_after_shop_loop_item`)
  - Método `render_loop_button()` agora usa `echo` ao invés de `return`
  - Adicionado wrapper `.dw-whatsapp-wrapper-loop` ao redor do botão
- Atualizado `assets/css/frontend.css`:
  - Estilos específicos para Woodmart
  - CSS para `.dw-whatsapp-wrapper-loop`
  - Propriedade `clear: both` para garantir posicionamento correto
- Atualizado `assets/js/loop-quantity.js`:
  - Busca botão dentro do wrapper específico
  - Melhor isolamento e prevenção de conflitos
- Suporte a múltiplos containers de produto: `.product`, `.product-grid-item`, `.product-list-item`, `.wd-product`

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
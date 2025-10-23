# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

---

## [0.2.0] - 2024-10-23

### ✨ Adicionado

#### Atribuição de Atendente por Produto
- Metabox "WhatsApp - Atendente Responsável" no editor de produtos
- Seleção de atendente específico para cada produto
- Nome do atendente exibido no botão ("Comprar via WhatsApp com João")
- Nova coluna na lista de produtos (admin) mostrando atendente atribuído
- Fallback automático para número padrão quando não há atendente selecionado
- Classe `DW_WhatsApp_Product` para gerenciar metabox e coluna
- Método `get_product_attendant()` para buscar atendente do produto

#### Horário de Trabalho Automático
- Sistema de status automático baseado em horário configurado
- Configuração de dias da semana individuais (Segunda a Domingo)
- Campos de horário de início e fim (input type="time")
- Seletor de fuso horário (6 regiões do Brasil)
- Classe `DW_WhatsApp_Schedule` para gerenciar horários
- Método `is_available()` para verificar se está no horário
- Método `get_current_status()` para obter status em tempo real
- Método `get_next_available()` para mostrar próximo horário
- Indicador visual de próximo horário disponível quando offline
- Toggle automático/manual por atendente

#### Formatação Inteligente de Horário
- Detecção automática de padrões comuns:
  - "Segunda a Sexta" (seg-sex)
  - "Segunda a Sábado" (seg-sáb)
  - "Todos os dias" (7 dias)
  - "Finais de Semana" (sáb+dom)
- Criação de intervalos para dias consecutivos
- Formatação natural para dias não consecutivos:
  - 2 dias: "Segunda e Quarta"
  - 3+ dias: "Segunda, Quarta e Sexta"
- Método `format_days_range()` para formatação inteligente
- Método `is_consecutive()` para detectar sequências

#### Interface Drag & Drop
- Sistema completo de arrastar e soltar para reordenar atendentes
- Ícone visual de arrasto (≡ três linhas) à esquerda de cada card
- Feedback visual durante arrasto:
  - Opacidade 50% no elemento arrastado
  - Borda verde tracejada na área de destino
  - Fundo verde claro ao passar sobre
- Renumeração automática dos títulos após reordenação
- Reindexação automática de todos os campos HTML
- Eventos HTML5 Drag & Drop: dragstart, dragover, drop, dragend
- Função `reindexUsers()` para atualizar índices
- Função `updateUserNumbers()` para renumerar títulos
- Dica contextual verde sempre visível
- Cursor "grab" e "move" apropriados

### 🔧 Modificado

#### Interface de Usuário
- Padding ajustado para `15px 15px 15px 40px` (melhor espaçamento com ícone de drag)
- Atributo `draggable="true"` adicionado aos cards de usuário
- Campo de horário reorganizado com toggle automático/manual
- Campos de horário automático ocultam/mostram dinamicamente
- Descrição do plugin atualizada com novos recursos

#### Backend
- Sanitização expandida para novos campos (auto_status, working_days, work_start, work_end, timezone)
- Método `render_user_item()` atualizado para usar `DW_WhatsApp_Schedule`
- Método `render_product_button()` verifica atendente específico do produto
- Método `render_loop_button()` atualizado para usar atendente específico
- Widget flutuante usa status automático em tempo real
- Constante `DW_WHATSAPP_VERSION` atualizada para '0.2.0'

#### Arquitetura
- Nova classe `DW_WhatsApp_Schedule` em `includes/class-dw-whatsapp-schedule.php`
- Nova classe `DW_WhatsApp_Product` em `admin/class-dw-whatsapp-product.php`
- Registro de novas classes no `class-dw-whatsapp.php`
- Método helper `get_product_attendant()` no frontend para evitar duplicação

### 📚 Documentação
- README.md completamente reescrito e expandido
- Seção "Novidades v0.2.0" adicionada
- Índice e navegação melhorados
- Screenshots e exemplos visuais
- FAQ expandido com novas perguntas
- Roadmap para futuras versões
- Badges de versão e compatibilidade

### 🗑️ Removido
- Arquivos de documentação técnica interna (consolidados no README):
  - `DRAG-DROP.md`
  - `EXEMPLOS-FORMATACAO.md`
  - `TESTE-HORARIO.md`
  - `HORARIO-AUTOMATICO.md`
  - `ATRIBUICAO-ATENDENTE.md`
  - `SOLUCAO-FINAL.md`
  - `MULTI-USERS.md`

### 🐛 Corrigido
- Warnings de undefined array keys usando operador `??`
- Status do atendente agora respeita horário automático
- Formatação de horário mais legível e profissional
- Espaçamento entre ícone de drag e conteúdo do card

---

## [0.1.0] - 2024-10-01

### ✨ Adicionado

#### Funcionalidades Base
- Botão de WhatsApp na página individual do produto
- Botão de WhatsApp na listagem de produtos (loop)
- Botão flutuante configurável em 4 posições (cantos da tela)
- Suporte a produtos variáveis (captura variações selecionadas)
- Sistema de orçamento para produtos sem preço
- Remoção do botão "Adicionar ao carrinho" em produtos sem preço

#### Sistema de Múltiplos Atendentes
- Ativação/desativação do sistema multi-usuários
- Cadastro de até 10 atendentes
- Campos por atendente:
  - Nome (obrigatório)
  - Telefone (obrigatório)
  - Departamento (opcional)
  - Avatar URL (opcional)
  - Status (Online/Away/Offline)
  - Mensagem de status (opcional)
  - Horário de trabalho (texto livre)
- Widget flutuante de chat com lista de atendentes
- Configurações do widget (título, subtítulo, mensagem de disponibilidade)

#### Personalização
- Mensagens customizáveis para produtos com/sem preço
- Textos dos botões editáveis
- Seletor de cor para botões
- Opção de incluir/excluir link do produto
- Opção de incluir/excluir variações
- Ocultar botão flutuante em páginas específicas

#### Painel Admin
- Página de configurações em WooCommerce > DW WhatsApp
- Interface organizada por seções
- Validação de campos obrigatórios
- Limite de 10 usuários (performance)
- Botão para adicionar/remover usuários dinamicamente

#### Técnico
- Compatibilidade com HPOS (High-Performance Order Storage)
- WordPress Coding Standards
- Sanitização e validação de dados
- Escape de saída (esc_html, esc_attr, esc_url)
- JavaScript modular (variations.js)
- CSS inline otimizado
- Sem dependências externas
- Autoload de classes PSR-4

#### Arquivos Principais
- `dw-whatsapp.php` - Arquivo principal do plugin
- `includes/class-dw-whatsapp.php` - Classe principal
- `includes/class-dw-whatsapp-settings.php` - Gerenciamento de configurações
- `includes/class-dw-whatsapp-frontend.php` - Renderização frontend
- `admin/class-dw-whatsapp-admin.php` - Painel administrativo
- `admin/views/settings-page.php` - Template da página de configurações
- `assets/js/variations.js` - JavaScript para produtos variáveis
- `uninstall.php` - Limpeza ao desinstalar

### 📚 Documentação
- README.md com instruções completas
- CHANGELOG.md para histórico de versões
- STRUCTURE.md com estrutura do código
- Comentários PHPDoc em todas as classes

### 🎨 Interface
- Design moderno e responsivo
- Ícone SVG do WhatsApp
- Animações CSS suaves
- Feedback visual em hover
- Mobile-friendly

---

## Tipos de Mudanças

- `✨ Adicionado` - Novas funcionalidades
- `🔧 Modificado` - Mudanças em funcionalidades existentes
- `🗑️ Removido` - Funcionalidades removidas
- `🐛 Corrigido` - Correção de bugs
- `🔒 Segurança` - Correções de vulnerabilidades
- `📚 Documentação` - Mudanças na documentação
- `⚡ Performance` - Melhorias de performance
- `♻️ Refatoração` - Mudanças que não afetam funcionalidade

---

**Nota:** Versões futuras seguirão o padrão [Semantic Versioning](https://semver.org/lang/pt-BR/):
- **MAJOR** (X.0.0) - Mudanças incompatíveis com versões anteriores
- **MINOR** (0.X.0) - Novas funcionalidades compatíveis
- **PATCH** (0.0.X) - Correções de bugs compatíveis

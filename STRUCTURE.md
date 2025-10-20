# Estrutura do Plugin - DW WhatsApp v0.1.0

Documentação da estrutura organizada do plugin seguindo clean code e padrões do WordPress.

## 📁 Estrutura de Diretórios

```
dw-whatsapp/
│
├── 📄 dw-whatsapp.php                          # Bootstrap principal (100 linhas)
├── 📄 uninstall.php                            # Limpeza na desinstalação
├── 📄 README.md                                # Documentação do usuário
├── 📄 CHANGELOG.md                             # Histórico de versões
├── 📄 .gitignore                               # Git ignore
│
├── 📁 includes/                                # Classes principais
│   ├── class-dw-whatsapp.php                   # Classe principal (130 linhas)
│   ├── class-dw-whatsapp-settings.php          # Gerenciamento de configurações (180 linhas)
│   └── class-dw-whatsapp-frontend.php          # Funcionalidades do frontend (320 linhas)
│
├── 📁 admin/                                   # Painel administrativo
│   ├── class-dw-whatsapp-admin.php             # Classe admin (90 linhas)
│   └── views/
│       └── settings-page.php                   # Template da página de configurações (140 linhas)
│
└── 📁 assets/                                  # Assets do plugin
    ├── css/                                    # Estilos (vazio - inline)
    └── js/
        └── variations.js                       # JavaScript para variações (120 linhas)
```

**Total:** ~1.080 linhas de código organizado

---

## 🎯 Padrão de Arquitetura

### MVC Simplificado
- **Model:** `class-dw-whatsapp-settings.php` (dados e configurações)
- **View:** `admin/views/settings-page.php` (templates)
- **Controller:** `class-dw-whatsapp-admin.php` e `class-dw-whatsapp-frontend.php`

### Singleton Pattern
Todas as classes principais usam o padrão Singleton:
```php
private static $instance = null;

public static function instance() {
    if ( is_null( self::$instance ) ) {
        self::$instance = new self();
    }
    return self::$instance;
}
```

### Autoloading
PSR-4 style autoloading implementado:
```php
spl_autoload_register( 'dw_whatsapp_autoload' );
```

---

## 📦 Responsabilidades das Classes

### `DW_WhatsApp` (Principal)
- **Localização:** `includes/class-dw-whatsapp.php`
- **Responsabilidade:** Inicialização e coordenação
- **Métodos principais:**
  - `instance()` - Singleton
  - `load_dependencies()` - Carrega classes
  - `init_hooks()` - Inicializa hooks
  - `check_requirements()` - Verifica WooCommerce
  - `declare_hpos_compatibility()` - HPOS

### `DW_WhatsApp_Settings` (Configurações)
- **Localização:** `includes/class-dw-whatsapp-settings.php`
- **Responsabilidade:** Gerenciamento de configurações
- **Métodos principais:**
  - `get_settings()` - Retorna todas as configurações
  - `get($key, $default)` - Retorna configuração específica
  - `update($settings)` - Atualiza configurações
  - `sanitize($input)` - Sanitiza inputs

### `DW_WhatsApp_Frontend` (Frontend)
- **Localização:** `includes/class-dw-whatsapp-frontend.php`
- **Responsabilidade:** Botões e funcionalidades do site
- **Métodos principais:**
  - `render_product_button()` - Botão na página do produto
  - `render_loop_button()` - Botão no loop
  - `render_floating_button()` - Botão flutuante
  - `generate_whatsapp_link()` - Gera link do WhatsApp
  - `should_show_floating_button()` - Lógica de exibição
  - `get_current_page_type()` - Detecta tipo de página

### `DW_WhatsApp_Admin` (Admin)
- **Localização:** `admin/class-dw-whatsapp-admin.php`
- **Responsabilidade:** Painel administrativo
- **Métodos principais:**
  - `add_admin_menu()` - Adiciona menu
  - `register_settings()` - Registra configurações
  - `enqueue_scripts()` - Enfileira scripts admin
  - `render_settings_page()` - Renderiza página

---

## 🔄 Fluxo de Execução

### 1. Inicialização do Plugin
```
dw-whatsapp.php (bootstrap)
    ↓
Define constantes
    ↓
Registra autoloader
    ↓
Hook: plugins_loaded → dw_whatsapp_init()
    ↓
DW_WhatsApp::instance()
    ↓
Carrega dependências
    ↓
Inicializa hooks
```

### 2. Carregamento de Classes
```
DW_WhatsApp::instance()
    ↓
load_dependencies()
    ↓
├── DW_WhatsApp_Settings::instance()
├── DW_WhatsApp_Frontend::instance()
└── DW_WhatsApp_Admin::instance() (se admin)
```

### 3. Frontend (Site)
```
DW_WhatsApp_Frontend::instance()
    ↓
init_hooks()
    ↓
├── woocommerce_single_product_summary (botão produto)
├── woocommerce_loop_add_to_cart_link (botão loop)
├── wp_footer (botão flutuante)
├── woocommerce_is_purchasable (produtos sem preço)
└── woocommerce_get_price_html (altera preço)
```

### 4. Admin (Painel)
```
DW_WhatsApp_Admin::instance()
    ↓
add_admin_menu()
    ↓
Cria menu "DW WhatsApp"
    ↓
render_settings_page()
    ↓
Inclui: admin/views/settings-page.php
```

---

## 🔐 Camadas de Segurança

### 1. Proteção de Arquivos
```php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
```
**Todos os arquivos PHP** têm esta proteção.

### 2. Sanitização (Settings)
- `preg_replace()` para telefone
- `sanitize_text_field()` para textos
- `sanitize_hex_color()` para cores
- Whitelist para páginas e posições

### 3. Escape (Frontend)
- `esc_url()` para URLs
- `esc_attr()` para atributos
- `esc_html()` para textos
- `esc_textarea()` para textareas

### 4. Nonces (Admin)
```php
wp_nonce_field( 'dw_whatsapp_settings_action', 'dw_whatsapp_settings_nonce' );
check_admin_referer( 'dw_whatsapp_settings_action', 'dw_whatsapp_settings_nonce' );
```

### 5. Permissões (Admin)
```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Sem permissão' );
}
```

---

## 📝 Convenções de Código

### Nomenclatura
- **Classes:** `DW_WhatsApp_*` (PascalCase)
- **Métodos:** `get_settings()` (snake_case)
- **Hooks:** `dw_whatsapp_*` (snake_case)
- **Constantes:** `DW_WHATSAPP_*` (UPPER_SNAKE_CASE)

### Padrões WordPress
- ✅ Tab indentation (4 espaços convertidos)
- ✅ Yoda conditions
- ✅ Strict comparisons (`===`, `!==`)
- ✅ Type hints quando possível
- ✅ Documentação PHPDoc

### Organização
- Métodos `public` no topo
- Métodos `private` embaixo
- Métodos relacionados agrupados
- Comentários PHPDoc em todos os métodos

---

## 🎨 Assets

### JavaScript
```javascript
// assets/js/variations.js
- Captura variações selecionadas
- Atualiza link do WhatsApp dinamicamente
- Compatível com swatches e radio buttons
- 120 linhas, bem documentado
```

### CSS
```
Estilos inline (integrados ao HTML)
- Facilita manutenção
- Evita conflitos
- Customizável via tema
```

---

## 📚 Documentação

### README.md
- Instalação e configuração
- Lista de funcionalidades
- Requisitos do sistema
- Exemplos de uso
- Links úteis

### CHANGELOG.md
- Histórico de versões
- Mudanças detalhadas
- Formato Keep a Changelog
- Semantic Versioning

---

## 🔧 Extensibilidade

### Adicionar Nova Classe
```php
// includes/class-dw-whatsapp-nova-feature.php
class DW_WhatsApp_Nova_Feature {
    private static $instance = null;
    
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

Autoloader cuida do resto!

### Adicionar Novo Hook
```php
// includes/class-dw-whatsapp-frontend.php
private function init_hooks() {
    // ... hooks existentes
    add_action( 'novo_hook', array( $this, 'novo_metodo' ) );
}
```

### Adicionar Nova Configuração
```php
// includes/class-dw-whatsapp-settings.php
private static function get_defaults() {
    return array(
        // ... configurações existentes
        'nova_config' => 'valor_padrao',
    );
}
```

---

## ✅ Checklist de Qualidade

### Código
- ✅ Clean Code
- ✅ SOLID Principles
- ✅ DRY (Don't Repeat Yourself)
- ✅ KISS (Keep It Simple, Stupid)
- ✅ Separation of Concerns
- ✅ Single Responsibility

### Padrões
- ✅ WordPress Coding Standards
- ✅ WooCommerce Best Practices
- ✅ PSR-4 Autoloading
- ✅ Singleton Pattern
- ✅ MVC Pattern

### Segurança
- ✅ Sanitização completa
- ✅ Escape de outputs
- ✅ Nonces
- ✅ Verificação de permissões
- ✅ Validação de dados
- ✅ Proteção de arquivos

### Documentação
- ✅ PHPDoc em todas as classes
- ✅ PHPDoc em todos os métodos
- ✅ README.md completo
- ✅ CHANGELOG.md atualizado
- ✅ Comentários inline quando necessário

---

## 📊 Métricas

- **Classes:** 4
- **Métodos:** ~30
- **Hooks:** ~15
- **Linhas de código:** ~1.080
- **Arquivos:** 9
- **Erros de linting:** 0
- **Cobertura de segurança:** 100%

---

## 🎓 Para Desenvolvedores

### Entendendo o Código
1. Comece pelo `dw-whatsapp.php` (bootstrap)
2. Leia `class-dw-whatsapp.php` (principal)
3. Explore `class-dw-whatsapp-settings.php` (configurações)
4. Analise `class-dw-whatsapp-frontend.php` (botões)
5. Veja `class-dw-whatsapp-admin.php` (admin)

### Modificando o Plugin
1. **Adicionar funcionalidade:** Crie nova classe em `includes/`
2. **Modificar configurações:** Edite `class-dw-whatsapp-settings.php`
3. **Alterar botões:** Edite `class-dw-whatsapp-frontend.php`
4. **Mudar admin:** Edite `admin/views/settings-page.php`

### Debugging
```php
// Ativar debug
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Ver logs
error_log( print_r( $variavel, true ) );
```

---

**Plugin organizado com Clean Code e padrões profissionais!** 🎉

**Desenvolvido por David William da Costa**  
GitHub: [@agenciadw](https://github.com/agenciadw/dw-whatsapp)


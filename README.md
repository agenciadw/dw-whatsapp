# 📱 DW WhatsApp para WooCommerce

Plugin profissional de integração WhatsApp para WooCommerce com sistema de múltiplos atendentes, horário automático e gestão avançada de atendimento.

![Versão](https://img.shields.io/badge/vers%C3%A3o-0.2.0-green)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-blue)
![WooCommerce](https://img.shields.io/badge/WooCommerce-5.0%2B-purple)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)
![HPOS](https://img.shields.io/badge/HPOS-Compatible-success)

---

## 📋 Índice

- [Recursos](#-recursos)
- [Novidades v0.2.0](#-novidades-v020)
- [Instalação](#-instalação)
- [Configuração](#-configuração)
- [Recursos Detalhados](#-recursos-detalhados)
- [Screenshots](#-screenshots)
- [Requisitos](#-requisitos)
- [FAQ](#-faq)
- [Changelog](#-changelog)
- [Suporte](#-suporte)

---

## ✨ Recursos

### Botões de WhatsApp
- ✅ Botão na página individual do produto
- ✅ Botão na listagem de produtos
- ✅ Botão flutuante configurável em 4 posições

### 👥 Sistema de Múltiplos Atendentes
- ✅ **Atribuição de atendente por produto** (configuração no admin)
- ✅ **Nome do atendente no botão** ("Comprar com João")
- ✅ Widget de chat flutuante com lista de atendentes
- ✅ Status em tempo real (Online/Ausente/Offline)
- ✅ Avatares personalizados para cada atendente
- ✅ Departamentos e horários de trabalho
- ✅ Metabox no editor de produtos para seleção
- ✅ Coluna na lista de produtos mostrando atendente
- ✅ Fallback automático para número padrão

### ⏰ Horário de Trabalho Automático
- ✅ **Status automático baseado em horário**
- ✅ Configuração de dias da semana (seg-dom)
- ✅ Horário de início e fim personalizável
- ✅ Múltiplos fusos horários do Brasil
- ✅ Formatação inteligente ("Segunda a Sexta")
- ✅ Indicação de próximo horário disponível
- ✅ Modo manual ou automático por atendente

### 🎨 Interface de Gestão
- ✅ **Drag & Drop para reordenar atendentes**
- ✅ Interface intuitiva e visual
- ✅ Feedback visual durante arrasto
- ✅ Reorganização instantânea
- ✅ Até 10 atendentes simultâneos

### Produtos Variáveis
- ✅ Captura automática de variações (cor, tamanho, etc.)
- ✅ Atualização dinâmica da mensagem
- ✅ Compatível com plugins de variação visual

### Produtos Sem Preço
- ✅ Botão de "Solicitar Orçamento"
- ✅ Remove botão "Adicionar ao carrinho"
- ✅ Mensagem personalizada para orçamentos
- ✅ Badge visual "Solicite um orçamento"

### Personalização
- ✅ Mensagens customizáveis
- ✅ Textos dos botões editáveis
- ✅ Cor do botão personalizável
- ✅ Posicionamento flexível
- ✅ Ocultar em páginas específicas

### Técnico
- ✅ Compatível com HPOS (High-Performance Order Storage)
- ✅ Código limpo e otimizado (WordPress Coding Standards)
- ✅ JavaScript moderno (ES6+)
- ✅ Sem dependências externas
- ✅ Performance otimizada
- ✅ Seguro (sanitização e validação)

---

## 🆕 Novidades v0.2.0

### 🎯 Atribuição de Atendente por Produto

Agora o **gestor de produto** pode escolher qual atendente receberá as mensagens de cada produto!

**Como funciona:**
1. Edite qualquer produto no WooCommerce
2. Veja a metabox "WhatsApp - Atendente Responsável"
3. Selecione o atendente desejado
4. Se não selecionar, usa o número padrão
5. **Botão mostra nome:** "Comprar via WhatsApp com João"

**Vantagens:**
- ✅ Cliente vê com quem vai falar ("com João")
- ✅ Atendimento especializado por produto
- ✅ Distribuição de carga entre equipe
- ✅ Produtos por departamento/expertise
- ✅ Visível na lista de produtos (coluna admin)

---

### ⏰ Horário de Trabalho Automático

Sistema inteligente que **controla automaticamente** quando o atendente fica online/offline!

**Recursos:**
- 📅 **Dias da semana:** Escolha seg-dom individualmente
- 🕐 **Horário:** Defina início e fim (ex: 09:00-18:00)
- 🌎 **Fuso horário:** Brasil (6 regiões)
- 🤖 **Automático:** Status muda sozinho
- 📊 **Formatação inteligente:** "Segunda a Sexta - 09:00 às 18:00"
- ⏳ **Próximo horário:** Mostra quando volta (ex: "Disponível amanhã às 09:00")

**Padrões detectados:**
- Segunda a Sexta → "Segunda a Sexta"
- Segunda a Sábado → "Segunda a Sábado"
- Todos os dias → "Todos os dias"
- Sáb + Dom → "Finais de Semana"
- Dias não consecutivos → "Segunda, Quarta e Sexta"

---

### 🎨 Interface Drag & Drop

Reorganize atendentes **arrastando e soltando**!

**Como usar:**
1. Vá em WooCommerce > DW WhatsApp > Múltiplos Usuários
2. Clique e segure no card do atendente
3. Arraste para nova posição
4. Solte e salve

**Efeitos visuais:**
- ≡ Ícone de arrasto à esquerda
- 🟢 Borda verde na área de destino
- 👻 Opacidade 50% ao arrastar
- 💡 Dica contextual sempre visível

**Por que usar:**
- Priorizar vendedor principal
- Agrupar por departamento
- Ordem aparece igual no widget
- Interface intuitiva e rápida

---

### 📝 Formatação Inteligente de Horário

O sistema agora formata horários de forma **natural e legível**:

| Seleção | Exibição |
|---------|----------|
| Seg-Sex | Segunda a Sexta - 09:00 às 18:00 |
| Seg-Sáb | Segunda a Sábado - 10:00 às 19:00 |
| Todos | Todos os dias - 08:00 às 20:00 |
| Sáb-Dom | Finais de Semana - 10:00 às 16:00 |
| Seg+Qua+Sex | Segunda, Quarta e Sexta - 09:00 às 17:00 |

---

## 📥 Instalação

### Via WordPress Admin

1. Baixe o arquivo ZIP do plugin
2. Acesse **Plugins > Adicionar Novo**
3. Clique em **Enviar Plugin**
4. Selecione o arquivo ZIP
5. Clique em **Instalar Agora**
6. Ative o plugin

### Via FTP

1. Descompacte o arquivo ZIP
2. Envie a pasta `dw-whatsapp` para `/wp-content/plugins/`
3. Acesse **Plugins** no WordPress
4. Ative o **DW WhatsApp para WooCommerce**

### Via WP-CLI

```bash
wp plugin install dw-whatsapp.zip --activate
```

---

## ⚙️ Configuração

### Configuração Básica

1. Acesse **WooCommerce > DW WhatsApp**
2. Configure o **número principal** (com código do país)
   - Exemplo: `5519999999999`
3. Personalize as **mensagens**
4. Escolha a **posição do botão flutuante**
5. Clique em **Salvar Alterações**

### Configuração de Múltiplos Atendentes

1. Marque **"Ativar sistema de múltiplos usuários"**
2. Configure os textos do widget (título, subtítulo)
3. Clique em **"+ Adicionar Usuário"**
4. Preencha:
   - **Nome:** Ex: João Silva
   - **Telefone:** 5519999999999 (com código do país)
   - **Departamento:** Ex: Suporte, Vendas
   - **Avatar:** URL da imagem (opcional)
   - **Status:** Online/Ausente/Offline (se manual)

### Configuração de Horário Automático

1. No usuário, marque **☑️ Automático**
2. Selecione os **dias da semana**
3. Configure **horário de início e fim**
4. Escolha o **fuso horário**
5. Salve - o status mudará automaticamente!

### Atribuir Atendente a Produto

1. Edite qualquer produto
2. Veja a metabox **"WhatsApp - Atendente Responsável"**
3. Selecione o atendente desejado
4. Ou deixe em branco para usar número padrão
5. Publique/Atualize o produto

### Reordenar Atendentes

1. Vá em **WooCommerce > DW WhatsApp**
2. Na seção "Usuários", **arraste** os cards
3. **Solte** na nova posição
4. **Salve** as configurações

---

## 🎯 Recursos Detalhados

### Sistema de Múltiplos Atendentes

**Metabox no Produto:**
```
┌─────────────────────────────────────┐
│ WhatsApp - Atendente Responsável    │
├─────────────────────────────────────┤
│ Selecione o atendente:              │
│ ▼ João Silva - Suporte ✓           │
│   Maria Santos - Vendas ⭕          │
│   Pedro Costa - Financ. ✓          │
└─────────────────────────────────────┘
```

**Widget Flutuante:**
```
┌─────────────────────────────────────┐
│ 📱 Iniciar Conversa                 │
│ Olá! Clique em um dos membros...   │
├─────────────────────────────────────┤
│ 👤 João Silva                       │
│    Suporte                          │
│    Segunda a Sexta - 09:00-18:00   │
│    ● ✓ Online                       │
├─────────────────────────────────────┤
│ 👤 Maria Santos                     │
│    Vendas                           │
│    Segunda a Sábado - 10:00-20:00  │
│    ● ⭕ Offline                      │
│    💬 Disponível amanhã às 10:00    │
└─────────────────────────────────────┘
```

**Botão no Produto:**
```
Com atendente:
┌─────────────────────────────────────┐
│ 📱 Comprar via WhatsApp com João    │
└─────────────────────────────────────┘

Sem atendente:
┌─────────────────────────────────────┐
│ 📱 Comprar via WhatsApp             │
└─────────────────────────────────────┘
```

### Horário Automático

**Status em Tempo Real:**
- ✅ **Online:** Dentro do horário configurado
- ⏰ **Away:** Status manual (ausente)
- ⭕ **Offline:** Fora do horário ou dia não trabalhado

**Exemplo de Configuração:**
```
Atendente: João Silva
☑️ Automático
Dias: ☑️ Seg ☑️ Ter ☑️ Qua ☑️ Qui ☑️ Sex
Início: 09:00
Fim: 18:00
Fuso: Brasília (GMT-3)

Resultado:
- Segunda às 10:00 → ✓ Online
- Segunda às 20:00 → ⭕ Offline  
- Sábado às 10:00 → ⭕ Offline (não trabalha)
```

### Drag & Drop

**Interface:**
```
💡 Dica: Arraste e solte os usuários...

┌─────────────────────────────────────┐
│ ≡  Usuário #1 - João Silva          │
│    Vendas                           │
└─────────────────────────────────────┘
        ↕ Arrastar
┌─────────────────────────────────────┐
│ ≡  Usuário #2 - Maria Santos        │
│    Suporte                          │
└─────────────────────────────────────┘
```

---

## 📸 Screenshots

1. **Botão no Produto** - Botão personalizado com nome do atendente
2. **Widget Flutuante** - Lista de atendentes com status
3. **Painel Admin** - Interface drag & drop
4. **Metabox Produto** - Seleção de atendente
5. **Horário Automático** - Configuração de horário

---

## 📋 Requisitos

- **WordPress:** 5.8 ou superior
- **WooCommerce:** 5.0 ou superior
- **PHP:** 7.4 ou superior
- **Navegador:** Chrome, Firefox, Safari, Edge (últimas versões)

---

## ❓ FAQ

### Como adicionar múltiplos atendentes?

1. Ative "Sistema de múltiplos usuários"
2. Clique em "+ Adicionar Usuário"
3. Preencha nome e telefone (obrigatórios)
4. Preencha departamento, avatar, etc. (opcionais)
5. Salve as configurações

### Como atribuir atendente a um produto?

1. Edite o produto no WooCommerce
2. Veja a metabox "WhatsApp - Atendente Responsável"
3. Selecione o atendente desejado
4. Publique/Atualize o produto
5. O botão mostrará: "Comprar com [Nome]"

### Como configurar horário automático?

1. No atendente, marque "☑️ Automático"
2. Selecione os dias da semana
3. Configure horário (início e fim)
4. Escolha o fuso horário
5. Salve - o status mudará automaticamente!

### Como reordenar atendentes?

1. Vá em WooCommerce > DW WhatsApp
2. Clique e segure no card do atendente
3. Arraste para cima ou para baixo
4. Solte na nova posição
5. Salve as configurações

### O horário não está mudando automaticamente

Verifique:
- ☑️ "Automático" está marcado?
- Dia de hoje está selecionado?
- Horário configurado está correto?
- Fuso horário é o correto?
- Salvou as configurações?

### Posso usar sem múltiplos atendentes?

Sim! O plugin funciona perfeitamente com um único número. Apenas não ative o "Sistema de múltiplos usuários".

### Quantos atendentes posso adicionar?

Até **10 atendentes** por questões de performance e UX.

### O plugin funciona com produtos variáveis?

Sim! Captura automaticamente as variações selecionadas e inclui na mensagem.

### Posso personalizar as cores?

Sim! Há um seletor de cores para o botão no painel de configurações.

### É compatível com HPOS?

Sim! 100% compatível com High-Performance Order Storage do WooCommerce.

---

## 📝 Changelog

### 0.2.0 - 2024-10-23

#### ✨ Novidades

**Atribuição de Atendente por Produto:**
- Metabox no editor de produtos
- Seleção de atendente específico
- Nome do atendente no botão ("Comprar com João")
- Coluna na lista de produtos mostrando atendente
- Fallback automático para número padrão

**Horário de Trabalho Automático:**
- Status automático baseado em horário
- Configuração de dias da semana (seg-dom)
- Horário de início e fim personalizável
- Múltiplos fusos horários do Brasil
- Indicação de próximo horário disponível
- Modo manual ou automático por atendente

**Formatação Inteligente:**
- "Segunda a Sexta" (ao invés de "Seg, Ter, Qua...")
- "Todos os dias" para 7 dias
- "Finais de Semana" para Sáb+Dom
- Intervalos consecutivos detectados
- Dias não consecutivos com vírgula e "e"

**Interface Drag & Drop:**
- Arrastar e soltar para reordenar atendentes
- Ícone visual de arrasto (≡)
- Feedback visual durante arrasto
- Borda verde na área de destino
- Renumeração automática
- Reindexação de campos

#### 🔧 Melhorias
- Padding ajustado (15px 40px) para melhor espaçamento
- Clean code em todos os arquivos
- Documentação consolidada no README
- Remoção de arquivos .md temporários
- Versão atualizada para 0.2.0

#### 📚 Arquivos
- Mantidos: README.md, CHANGELOG.md, STRUCTURE.md
- Removidos: arquivos de documentação técnica interna

### 0.1.0 - 2024-10-01

#### ✨ Lançamento Inicial
- Botões de WhatsApp em produtos
- Botão flutuante configurável
- Sistema de múltiplos atendentes
- Widget de chat
- Suporte a variações de produtos
- Produtos sem preço (orçamento)
- Compatibilidade HPOS

---

## 🤝 Suporte

### Documentação
- **README.md** - Documentação principal
- **CHANGELOG.md** - Histórico de versões
- **STRUCTURE.md** - Estrutura do código

### Contato
- **Desenvolvedor:** David William da Costa
- **GitHub:** [@agenciadw](https://github.com/agenciadw)
- **Plugin URI:** [dw-whatsapp](https://github.com/agenciadw/dw-whatsapp)

### Reportar Bugs
Abra uma issue no GitHub com:
- Versão do WordPress
- Versão do WooCommerce
- Versão do PHP
- Descrição detalhada do problema
- Screenshots (se aplicável)

---

## 📄 Licença

Este plugin é licenciado sob GPLv2 ou posterior.

```
Copyright (C) 2024 David William da Costa

Este programa é software livre; você pode redistribuí-lo e/ou
modificá-lo sob os termos da GNU General Public License conforme
publicada pela Free Software Foundation; na versão 2 da Licença,
ou (a seu critério) qualquer versão posterior.
```

---

## 🎉 Créditos

**Desenvolvido com ❤️ por:**
- David William da Costa

**Tecnologias:**
- WordPress
- WooCommerce
- PHP 7.4+
- JavaScript (ES6+)
- HTML5 Drag & Drop API
- CSS3

---

## 🚀 Roadmap

### Próximas Versões

**v0.3.0 (Planejado)**
- [ ] Horário de almoço (pausa)
- [ ] Múltiplos períodos no dia
- [ ] Feriados personalizáveis
- [ ] Mensagem automática quando offline
- [ ] Relatórios de atendimento

**v0.4.0 (Futuro)**
- [ ] Integração com API oficial do WhatsApp
- [ ] Histórico de conversas
- [ ] Analytics de atendimento
- [ ] Templates de mensagens
- [ ] Respostas rápidas

---

**⭐ Se gostou do plugin, deixe sua avaliação!**

**🐛 Encontrou um bug? Reporte!**

**💡 Tem uma ideia? Compartilhe!**

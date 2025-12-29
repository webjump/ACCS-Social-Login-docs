# Adobe Commerce Social Login - Exemplos de Integração

Este diretório contém exemplos práticos para integrar o Social Login Widget em diferentes ambientes Adobe Commerce.

## 📁 Estrutura dos Exemplos

```
examples/
├── README.md                           # Este arquivo
├── aem-edge-integration/               # 🎯 AEM Edge Delivery Services
│   ├── blocks/social-login/           # Block Franklin/Helix
│   ├── configs-example.js             # Configurações
│   ├── QUICK-START.md                 # Setup em 5 minutos
│   └── scripts-requirements.md        # Dependências
├── magento-module/                     # 🏪 Módulo Adobe Commerce completo
│   ├── app/code/Webjump/SocialLogin/  # Código do módulo
│   └── README.md                      # Guia de instalação
└── integration-examples/               # 🔧 Integrações simples
    ├── basic-integration.html         # HTML básico
    ├── advanced-integration.html      # Configuração dinâmica
    └── pwa-studio-example.jsx         # PWA Studio (referência)
```

## 🎯 Qual Exemplo Usar?

### **AEM Edge Delivery Services** ⭐ RECOMENDADO
```
📁 aem-edge-integration/
```
**Use se você tem:**
- ✅ AEM Edge como Storefront
- ✅ Adobe Commerce como backend
- ✅ Franklin/Helix blocks
- ✅ aem-boilerplate-commerce base

**Vantagens:**
- ⚡ Performance otimizada
- 🎨 Design system nativo
- 📱 Mobile-first
- 🚀 Deploy automático

---

### **Módulo Adobe Commerce Completo**
```
📁 magento-module/
```
**Use se você tem:**
- ✅ Adobe Commerce tradicional (Luma/Blank theme)
- ✅ Acesso ao admin backend
- ✅ Capacidade de instalar módulos
- ✅ Controle total sobre configurações

**Vantagens:**
- 🎛️ Configuração via admin panel
- 🔧 Escolha de providers via backoffice
- 🎨 Personalização de tema/posição
- 📊 Configurações avançadas

---

### **Integrações Simples**
```
📁 integration-examples/
```
**Use se você tem:**
- ✅ Qualquer frontend (HTML/JS/React/Vue)
- ✅ Acesso limitado ao backend
- ✅ Necessidade de implementação rápida
- ✅ Flexibilidade total de customização

**Vantagens:**
- 🚀 Setup em minutos
- 🛠️ Plug-and-play
- 💡 Exemplos educativos
- 🔄 Facilmente adaptável

## 🚀 Quick Start por Plataforma

### **AEM Edge** (5 minutos)
```bash
# 1. Copiar arquivos
cp aem-edge-integration/blocks/social-login/* your-site/blocks/social-login/

# 2. Configurar
# Adicionar configs ao scripts/configs.js

# 3. Deploy
hlx deploy && hlx publish
```

### **Adobe Commerce Module** (15 minutos)
```bash
# 1. Copiar módulo
cp -r magento-module/app/code/Webjump/SocialLogin app/code/Webjump/SocialLogin

# 2. Instalar
php bin/magento module:enable Webjump_SocialLogin
php bin/magento setup:upgrade

# 3. Configurar via admin
# Stores > Configuration > Webjump > Social Login
```

### **HTML Simples** (2 minutos)
```html
<!-- 1. Copiar HTML -->
<!-- Ver basic-integration.html -->

<!-- 2. Configurar API endpoint -->
<script>
new SocialLoginWidget({
    apiEndpoint: 'https://your-domain.adobe.io/api/v1/social-login'
});
</script>
```

## 🔧 Configuração Comum

Todos os exemplos requerem:

### **1. Adobe App Builder Social Login**
```bash
# Deploy das ações OAuth
aio app deploy
```

### **2. Environment Variables**
```bash
# Configurar credenciais OAuth
GOOGLE_CLIENT_ID=your-google-client-id
META_APP_ID=your-meta-app-id
LINKEDIN_CLIENT_ID=your-linkedin-client-id
# etc...
```

### **3. Adobe Commerce GraphQL**
- ✅ GraphQL endpoint habilitado
- ✅ Customer tokens funcionando
- ✅ CORS configurado corretamente

## 📊 Comparação de Implementações

| Característica | AEM Edge | Módulo Commerce | HTML Simples |
|---|---|---|---|
| **Tempo Setup** | ⚡ 5min | 🕐 15min | ⚡ 2min |
| **Configuração Admin** | ❌ Via código | ✅ Via painel | ❌ Via código |
| **Performance** | ⚡⚡⚡ | ⚡⚡ | ⚡⚡⚡ |
| **Flexibilidade** | ⚡⚡ | ⚡ | ⚡⚡⚡ |
| **Manutenção** | ⚡⚡⚡ | ⚡⚡ | ⚡⚡ |
| **Recursos** | ⚡⚡ | ⚡⚡⚡ | ⚡ |

## 🆘 Suporte

### **Para Problemas Específicos:**
- **AEM Edge**: Consulte `aem-edge-integration/scripts-requirements.md`
- **Módulo Adobe Commerce**: Consulte `magento-module/README.md`
- **Integrações Simples**: Consulte comentários nos arquivos HTML

### **Para Problemas Gerais:**
- Verificar configuração do Adobe App Builder
- Confirmar credenciais OAuth dos providers
- Testar endpoints GraphQL do Adobe Commerce
- Verificar CORS e políticas de segurança

### **Debug:**
```javascript
// Ativar modo debug em qualquer implementação
'social-login-debug': 'true'
```

---

**Escolha o exemplo que melhor se adapta ao seu ambiente e siga o guia específico! 🚀**
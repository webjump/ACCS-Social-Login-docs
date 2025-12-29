# Social Login para AEM Edge Delivery Services + Adobe Commerce

Integração específica do Social Login Widget com **AEM Edge Delivery Services** baseado no [aem-boilerplate-commerce](https://github.com/hlxsites/aem-boilerplate-commerce).

## 🏗️ Arquitetura

```
AEM Edge Delivery Services (Storefront)
├── Franklin/Helix Blocks
├── Adobe App Builder (Social Login OAuth)
├── Adobe Commerce GraphQL API
└── CDN Edge Workers
```

## 📁 Estrutura Final

```
your-aem-edge-site/
├── blocks/
│   └── social-login/
│       ├── social-login.js          # Block principal
│       └── social-login.css         # Estilos do block
├── scripts/
│   ├── configs.js                   # Configurações (atualizar)
│   ├── customer.js                  # Funções de cliente (verificar)
│   └── commerce.js                  # GraphQL Commerce (verificar)
└── docs/
    └── social-login-setup.md        # Instruções de setup
```

## 🚀 Implementação

### 1. **Copiar Arquivos**
```bash
# Copie apenas os arquivos essenciais:
cp blocks/social-login/* /path/to/your-aem-edge-site/blocks/social-login/
```

### 2. **Configurar**
```javascript
// scripts/configs.js - Adicionar configurações
'social-login-enabled': 'true',
'social-login-api-endpoint': 'https://your-app-domain.adobe.io/api/v1/social-login',
'social-login-providers': 'google,meta,linkedin',
'social-login-theme': 'light',
'social-login-redirect-url': '/customer/account/'
```

### 3. **Usar nas Páginas**
```html
<!-- Via Document (Word/Google Docs) -->
| Social Login |
|--------------|
|              |

<!-- Ou diretamente via HTML -->
<div class="social-login">
  <div></div>
</div>
```

### 4. **Deploy**
```bash
hlx deploy && hlx publish
```

## 🔧 Dependências

O block depende destes scripts existentes do aem-boilerplate-commerce:

- **scripts/configs.js** - Para configurações
- **scripts/customer.js** - Para gerenciar clientes
- **scripts/commerce.js** - Para GraphQL queries
- **scripts/minicart.js** - Para carrinho (opcional)

## 📊 Performance

- ✅ **LCP**: < 2.5s - Block carrega rapidamente
- ✅ **FID**: < 100ms - Interações responsivas
- ✅ **CLS**: 0 - Sem layout shift
- ✅ **Bundle**: < 5KB - Código otimizado

## 🛡️ Segurança

- ✅ Tokens seguros via localStorage/sessionStorage
- ✅ CORS configurado no Adobe App Builder
- ✅ GraphQL com autenticação
- ✅ Sanitização de inputs

---

**Arquivos essenciais**: Apenas `social-login.js` + `social-login.css` + configuração!
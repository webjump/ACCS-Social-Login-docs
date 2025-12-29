# Módulo Adobe Commerce - Webjump Social Login

Este módulo completo para Adobe Commerce permite configurar e gerenciar o Social Login Widget diretamente através do painel administrativo.

## 🚀 Funcionalidades

### ✅ Configuração via Admin Panel
- **Habilitar/Desabilitar** o Social Login globalmente
- **Seleção de Providers**: Escolha quais provedores OAuth habilitar:
  - Google
  - Meta/Facebook
  - LinkedIn
  - PayPal
  - Apple
  - Twitter
  - Pinterest
  - Instagram

### 🎨 Personalização Visual
- **Temas**: Light, Dark ou Default
- **Tamanho dos Botões**: Small, Medium ou Large
- **Labels**: Mostrar/Ocultar nomes dos provedores
- **Posicionamento**: Antes, após ou substituindo o formulário de login

### ⚙️ Configurações Avançadas
- URL de redirecionamento personalizada
- Modo debug para desenvolvimento
- Criação automática de clientes
- Cache inteligente para performance

## 📁 Estrutura do Módulo

```
app/code/Webjump/SocialLogin/
├── registration.php                    # Registro do módulo
├── etc/
│   ├── module.xml                     # Definição do módulo
│   ├── config.xml                     # Configurações padrão
│   ├── acl.xml                        # Permissões de acesso
│   └── adminhtml/
│       └── system.xml                 # Interface de configuração admin
├── Helper/
│   └── Data.php                       # Helper principal com getters de configuração
├── Block/
│   └── Login.php                      # Block para renderização frontend
├── Model/Config/Source/
│   ├── Theme.php                      # Opções de tema
│   ├── ButtonSize.php                 # Opções de tamanho
│   └── Position.php                   # Opções de posicionamento
└── view/frontend/
    ├── layout/
    │   └── customer_account_login.xml # Layout da página de login
    └── templates/
        └── login.phtml                # Template principal
```

## 📦 Instalação

### 1. Copiar Arquivos
```bash
# Copie todo o diretório para seu Adobe Commerce
cp -r app/code/Webjump/SocialLogin /path/to/your/magento/app/code/Webjump/SocialLogin
```

### 2. Ativar Módulo
```bash
# Via linha de comando
php bin/magento module:enable Webjump_SocialLogin
php bin/magento setup:upgrade
php bin/magento cache:clean
```

### 3. Verificar Instalação
```bash
# Confirmar que o módulo está ativo
php bin/magento module:status Webjump_SocialLogin
```

## ⚙️ Configuração

### 1. Acessar Configurações
Vá para: **Admin Panel > Stores > Configuration > Webjump > Social Login**

### 2. Configurações Básicas
- **Habilitar Social Login**: Ative o módulo
- **API Endpoint**: Configure a URL do seu Adobe App Builder (ex: `https://your-domain.adobe.io/api/v1/social-login`)

### 3. Selecionar Providers
Marque os provedores OAuth que deseja habilitar:
- ✅ Google (recomendado)
- ✅ Meta/Facebook
- ✅ LinkedIn
- ⚠️ PayPal (requer configuração adicional)
- ⚠️ Apple (requer Apple Developer Program)
- ⚠️ Twitter (requer configuração OAuth 2.0)
- ⚠️ Pinterest (requer Pinterest Developer Account)
- ⚠️ Instagram (via Meta Developer)

### 4. Personalizar Aparência
- **Tema**: Escolha entre Light, Dark ou Default
- **Tamanho**: Small, Medium ou Large
- **Labels**: Mostrar/ocultar nomes dos provedores
- **Posição**: Onde exibir os botões na página de login

### 5. Configurações Avançadas
- **URL de Redirecionamento**: Para onde enviar após login (padrão: conta do cliente)
- **Modo Debug**: Ativar apenas durante desenvolvimento
- **Criação Automática**: Criar cliente automaticamente no primeiro login

## 🆕 Novidades da Versão

### ✅ Carregamento Inteligente de Scripts
- **Auto-detecção**: Detecta automaticamente arquivos JavaScript com hash do build
- **Fallbacks**: Sistema de fallback tenta múltiplas URLs se uma falhar
- **Proteção contra Undefined**: Proteção defensiva contra dados indefinidos dos providers OAuth
- **Aguarda Carregamento**: Aguarda o SocialLoginWidget estar disponível antes de inicializar

### ✅ Todos os 8 Providers OAuth
- Suporte completo para todos os 8 provedores OAuth
- Seleção granular no admin panel
- Configuração otimizada para produção

## 🔧 Personalização

### Custom CSS
Adicione estilos personalizados no seu tema:

```css
.webjump-social-login-container {
    /* Seus estilos personalizados */
}

.webjump-social-login-container.theme-dark {
    /* Estilos para tema escuro */
}
```

### Layout Personalizado
Crie um layout personalizado em seu tema:

```xml
<!-- app/design/frontend/YourVendor/YourTheme/Webjump_SocialLogin/layout/customer_account_login.xml -->
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <move element="webjump.social.login" destination="form.additional.info" after="-"/>
    </body>
</page>
```

### Template Personalizado
Substitua o template padrão:

```php
<!-- app/design/frontend/YourVendor/YourTheme/Webjump_SocialLogin/templates/custom-login.phtml -->
<?php /** @var \Webjump\SocialLogin\Block\Login $block */ ?>
<!-- Seu template personalizado -->
```

## 🛠️ Desenvolvimento

### Configuração de Desenvolvimento
```php
// app/etc/env.php - adicione para desenvolvimento
'webjump_social_login' => [
    'debug' => true,
    'api_endpoint' => 'http://localhost:9080/api/v1/social-login'
]
```

### Debug e Logs
```bash
# Acompanhar logs do módulo
tail -f var/log/system.log | grep "social.login"
```

### Extensões
O módulo pode ser estendido através de:
- **Observers**: Para interceptar eventos de login
- **Plugins**: Para modificar comportamentos
- **Preferences**: Para substituir classes

## 🔍 Troubleshooting

### Módulo Não Aparece
```bash
# Verificar se o módulo está registrado
php bin/magento module:status | grep Webjump

# Forçar reindexação
php bin/magento indexer:reindex
```

### Widget Não Carrega
1. **API Endpoint**: Verifique se o API Endpoint está correto no admin
2. **Adobe App Builder**: Confirme que o App Builder está funcionando
3. **Console**: Verifique console do navegador para erros JavaScript
4. **Script Hash**: O módulo agora detecta automaticamente arquivos JavaScript com hash do build
5. **Fallbacks**: Sistema de fallback tenta múltiplas URLs automaticamente

### Providers Não Funcionam
1. Confirme credenciais OAuth nos arquivos de environment
2. Verifique se os providers estão habilitados no admin
3. Teste isoladamente cada provider

## 📋 Requisitos

### Adobe Commerce
- Adobe Commerce 2.4.x ou superior
- PHP 7.4 ou superior
- Extensões PHP: json, curl, openssl

### Adobe App Builder
- Social Login App implantado e funcionando
- Credenciais OAuth configuradas
- Endpoints acessíveis publicamente

## 🆘 Suporte

### Problemas Comuns
- **Cache**: Sempre limpe cache após alterações de configuração
- **CORS**: Configure CORS_ORIGINS no Adobe App Builder
- **SSL**: Use HTTPS em produção para todos os providers

### Logs Úteis
- `var/log/system.log` - Logs gerais do Magento
- `var/log/exception.log` - Erros PHP
- Browser Console - Erros JavaScript

### Documentação Adicional
- [Adobe Commerce DevDocs](https://devdocs.magento.com/)
- [Adobe App Builder Docs](https://developer.adobe.com/app-builder/)
- [OAuth Provider Documentation](../PROVIDER_EXAMPLES.md)

---

**Desenvolvido por Webjump** - Tecnologia que transforma negócios
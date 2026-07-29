# Adobe Commerce Social Login - Integration Examples

This directory contains practical examples for integrating the Social Login Widget into different Adobe Commerce environments.

## 📁 Examples Structure

```
examples/
├── README.md                           # This file
├── aem-edge-integration/               # 🎯 AEM Edge Delivery Services
│   ├── blocks/social-login/           # Franklin/Helix Block
│   ├── configs-example.js             # Configuration
│   ├── QUICK-START.md                 # 5-minute setup
│   └── scripts-requirements.md        # Dependencies
├── magento-module/                     # 🏪 Complete Adobe Commerce module
│   ├── app/code/Webjump/SocialLogin/  # Module code
│   └── README.md                      # Installation guide
└── integration-examples/               # 🔧 Simple integrations
    ├── basic-integration.html         # Basic HTML
    ├── advanced-integration.html      # Dynamic configuration
    └── pwa-studio-example.jsx         # PWA Studio (reference)
```

## 🎯 Which Example Should I Use?

### **AEM Edge Delivery Services** ⭐ RECOMMENDED
```
📁 aem-edge-integration/
```
**Use this if you have:**
- ✅ AEM Edge as Storefront
- ✅ Adobe Commerce as backend
- ✅ Franklin/Helix blocks
- ✅ aem-boilerplate-commerce base

**Advantages:**
- ⚡ Optimized performance
- 🎨 Native design system
- 📱 Mobile-first
- 🚀 Automatic deploy

---

### **Complete Adobe Commerce Module**
```
📁 magento-module/
```
**Use this if you have:**
- ✅ Traditional Adobe Commerce (Luma/Blank theme)
- ✅ Access to the admin backend
- ✅ Ability to install modules
- ✅ Full control over configuration

**Advantages:**
- 🎛️ Configuration via admin panel
- 🔧 Provider selection via backoffice
- 🎨 Theme/position customization
- 📊 Advanced configuration

---

### **Simple Integrations**
```
📁 integration-examples/
```
**Use this if you have:**
- ✅ Any frontend (HTML/JS/React/Vue)
- ✅ Limited backend access
- ✅ Need for a quick implementation
- ✅ Full customization flexibility

**Advantages:**
- 🚀 Setup in minutes
- 🛠️ Plug-and-play
- 💡 Educational examples
- 🔄 Easily adaptable

## 🚀 Quick Start by Platform

### **AEM Edge** (5 minutes)
```bash
# 1. Copy files
cp aem-edge-integration/blocks/social-login/* your-site/blocks/social-login/

# 2. Configure
# Add configs to scripts/configs.js

# 3. Deploy
hlx deploy && hlx publish
```

### **Adobe Commerce Module** (15 minutes)
```bash
# 1. Copy module
cp -r magento-module/app/code/Webjump/SocialLogin app/code/Webjump/SocialLogin

# 2. Install
php bin/magento module:enable Webjump_SocialLogin
php bin/magento setup:upgrade

# 3. Configure via admin
# Stores > Configuration > Webjump > Social Login
```

### **Simple HTML** (2 minutes)
```html
<!-- 1. Copy HTML -->
<!-- See basic-integration.html -->

<!-- 2. Configure API endpoint -->
<script>
new SocialLoginWidget({
    apiEndpoint: 'https://your-domain.adobe.io/api/v1/social-login'
});
</script>
```

## 🔧 Common Configuration

All examples require:

### **1. Adobe App Builder Social Login**
```bash
# Deploy the OAuth actions
aio app deploy
```

### **2. Environment Variables**
```bash
# Configure OAuth credentials
GOOGLE_CLIENT_ID=your-google-client-id
META_APP_ID=your-meta-app-id
LINKEDIN_CLIENT_ID=your-linkedin-client-id
# etc...
```

### **3. Adobe Commerce GraphQL**
- ✅ GraphQL endpoint enabled
- ✅ Customer tokens working
- ✅ CORS configured correctly

## 📊 Implementation Comparison

| Feature | AEM Edge | Commerce Module | Simple HTML |
|---|---|---|---|
| **Setup Time** | ⚡ 5min | 🕐 15min | ⚡ 2min |
| **Admin Configuration** | ❌ Via code | ✅ Via panel | ❌ Via code |
| **Performance** | ⚡⚡⚡ | ⚡⚡ | ⚡⚡⚡ |
| **Flexibility** | ⚡⚡ | ⚡ | ⚡⚡⚡ |
| **Maintenance** | ⚡⚡⚡ | ⚡⚡ | ⚡⚡ |
| **Features** | ⚡⚡ | ⚡⚡⚡ | ⚡ |

## 🆘 Support

### **For Specific Issues:**
- **AEM Edge**: See `aem-edge-integration/scripts-requirements.md`
- **Adobe Commerce Module**: See `magento-module/README.md`
- **Simple Integrations**: See the comments in the HTML files

### **For General Issues:**
- Verify the Adobe App Builder configuration
- Confirm the OAuth provider credentials
- Test the Adobe Commerce GraphQL endpoints
- Check CORS and security policies

### **Debug:**
```javascript
// Enable debug mode in any implementation
'social-login-debug': 'true'
```

---

**Choose the example that best fits your environment and follow the specific guide! 🚀**

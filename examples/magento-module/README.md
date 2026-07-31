# Adobe Commerce Module - Webjump Social Login

This complete module for Adobe Commerce lets you configure and manage the Social Login Widget directly from the admin panel.

## 🚀 Features

### ✅ Configuration via Admin Panel
- **Enable/Disable** Social Login globally
- **Provider Selection**: Choose which OAuth providers to enable:
  - Google
  - Meta/Facebook

  These are the two providers the Social Login extension implements. No other
  provider is available: the extension has OAuth actions for Google and Meta
  only, so enabling anything else would render a button that cannot complete a
  login.

### 🎨 Visual Customization
- **Themes**: Light, Dark, or Default
- **Button Size**: Small, Medium, or Large
- **Labels**: Show/hide provider names
- **Positioning**: Before, after, or replacing the login form

### ⚙️ Advanced Configuration
- Custom redirect URL
- Debug mode for development
- Smart caching for performance

### 🔐 How the session is opened
- The App Builder application hands the storefront a **real Adobe Commerce customer token**
- The module validates that token against Commerce's own token storage and opens the session for the customer it belongs to
- It accepts nothing else — no email, no profile data, no signed payload from the browser

## 📁 Module Structure

```
app/code/Webjump/SocialLogin/
├── registration.php                    # Module registration
├── etc/
│   ├── module.xml                     # Module definition
│   ├── config.xml                     # Default configuration
│   ├── acl.xml                        # Access permissions
│   └── adminhtml/
│       └── system.xml                 # Admin configuration interface
├── Helper/
│   └── Data.php                       # Main helper with configuration getters
├── Block/
│   └── Login.php                      # Block for frontend rendering
├── Api/
│   └── CustomerTokenAuthenticationInterface.php  # Authentication contract
├── Controller/Auth/
│   └── Callback.php                   # Receives the Commerce customer token
├── Model/
│   └── CustomerTokenAuthentication.php # Token validation + session creation
├── Model/Config/Source/
│   ├── Theme.php                      # Theme options
│   ├── ButtonSize.php                 # Size options
│   └── Position.php                   # Positioning options
└── view/frontend/
    ├── layout/
    │   └── customer_account_login.xml # Login page layout
    ├── templates/
    │   └── login.phtml                # Main template
    └── web/js/
        └── social-login-auth.js       # Posts the token to the controller
```

## 📦 Installation

### 1. Copy Files
```bash
# Copy the entire directory into your Adobe Commerce install
cp -r app/code/Webjump/SocialLogin /path/to/your/magento/app/code/Webjump/SocialLogin
```

### 2. Enable the Module
```bash
# Via command line
php bin/magento module:enable Webjump_SocialLogin
php bin/magento setup:upgrade
php bin/magento cache:clean
```

### 3. Verify Installation
```bash
# Confirm the module is active
php bin/magento module:status Webjump_SocialLogin
```

## ⚙️ Configuration

### 1. Access Settings
Go to: **Admin Panel > Stores > Configuration > Webjump > Social Login**

### 2. Basic Settings
- **Enable Social Login**: Activate the module
- **API Endpoint**: Configure your Adobe App Builder URL (e.g., `https://your-domain.adobe.io/api/v1/social-login`)

### 3. Select Providers
Check the OAuth providers you want to enable:
- ✅ Google
- ✅ Meta/Facebook

The extension supports these two providers and no others.

### 4. Customize Appearance
- **Theme**: Choose between Light, Dark, or Default
- **Size**: Small, Medium, or Large
- **Labels**: Show/hide provider names
- **Position**: Where to display the buttons on the login page

### 5. Advanced Settings
- **Redirect URL**: Where to send the customer after login (default: customer account)
- **Debug Mode**: Enable only during development

Creating the customer account is the App Builder application's job, not the
module's — there is no "create customer" setting here on purpose. See
[Authentication](#-authentication) below.

## 🆕 What's New in This Version

### ✅ Smart Script Loading
- **Auto-detection**: Automatically detects JavaScript files with the build hash
- **Fallbacks**: Fallback system tries multiple URLs if one fails
- **Undefined Protection**: Defensive protection against undefined data from OAuth providers
- **Waits for Load**: Waits for SocialLoginWidget to be available before initializing

### ✅ All 8 OAuth Providers
- Full support for all 8 OAuth providers
- Granular selection in the admin panel
- Configuration optimized for production

## 🔐 Authentication

### The flow

1. The shopper completes the OAuth flow in the widget.
2. The App Builder application finds or creates the customer in Adobe Commerce and
   returns a **genuine Commerce customer access token** — the same kind
   `POST /V1/integration/customer/token` issues.
3. `social-login-auth.js` posts that token to `sociallogin/auth/callback`.
4. `CustomerTokenAuthentication` looks the token up in Commerce's token storage
   (the same source `Magento\Webapi\Model\Authorization\TokenUserContext` uses for
   REST requests), checks that it is not revoked or expired and that it belongs to
   a **customer** rather than an admin or integration, and opens the session for
   that customer id.

### Why it is built this way

The token is the only thing that establishes identity, and it can't be forged: a
made-up token has no matching row in Commerce, so it is refused. The customer id
comes from that row — never from the request.

This is the reason the module deliberately does **not**:

- accept an email address, profile payload, or any self-describing token from the
  browser — that would move the trust decision to whoever calls the endpoint;
- create or update customer accounts — the App Builder application already did
  that, with the provider-verified identity, before any token existed;
- attempt client-side validation of the token — a check in the browser is
  advisory at best, and implies a guarantee it can't make.

If you adapt this module, keep those three properties. An endpoint that logs a
shopper in based on data the browser supplied is an account takeover, regardless
of how the data is encoded.

### Session handling

The session id is regenerated **before** the customer is logged in, so a session
id planted in the browser beforehand cannot survive into the authenticated
session (session fixation).

## 🔧 Customization

### Custom CSS
Add custom styles in your theme:

```css
.webjump-social-login-container {
    /* Your custom styles */
}

.webjump-social-login-container.theme-dark {
    /* Dark theme styles */
}
```

### Custom Layout
Create a custom layout in your theme:

```xml
<!-- app/design/frontend/YourVendor/YourTheme/Webjump_SocialLogin/layout/customer_account_login.xml -->
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <body>
        <move element="webjump.social.login" destination="form.additional.info" after="-"/>
    </body>
</page>
```

### Custom Template
Replace the default template:

```php
<!-- app/design/frontend/YourVendor/YourTheme/Webjump_SocialLogin/templates/custom-login.phtml -->
<?php /** @var \Webjump\SocialLogin\Block\Login $block */ ?>
<!-- Your custom template -->
```

## 🛠️ Development

### Development Configuration
```php
// app/etc/env.php - add for development
'webjump_social_login' => [
    'debug' => true,
    'api_endpoint' => 'http://localhost:9080/api/v1/social-login'
]
```

### Debug and Logs
```bash
# Follow module logs
tail -f var/log/system.log | grep "social.login"
```

### Extensions
The module can be extended through:
- **Observers**: To intercept login events
- **Plugins**: To modify behaviors
- **Preferences**: To replace classes

## 🔍 Troubleshooting

### Module Doesn't Appear
```bash
# Check if the module is registered
php bin/magento module:status | grep Webjump

# Force reindex
php bin/magento indexer:reindex
```

### Widget Doesn't Load
1. **API Endpoint**: Check whether the API Endpoint is correct in the admin
2. **Adobe App Builder**: Confirm the App Builder is working
3. **Console**: Check the browser console for JavaScript errors
4. **Script Hash**: The module now automatically detects JavaScript files with the build hash
5. **Fallbacks**: The fallback system automatically tries multiple URLs

### Providers Don't Work
1. Confirm OAuth credentials in the environment files
2. Verify the providers are enabled in the admin
3. Test each provider in isolation

## 📋 Requirements

### Adobe Commerce
- Adobe Commerce 2.4.x or higher
- PHP 7.4 or higher
- PHP extensions: json, curl, openssl

### Adobe App Builder
- Social Login App deployed and working
- OAuth credentials configured
- Publicly accessible endpoints

## 🆘 Support

### Common Issues
- **Cache**: Always clear the cache after configuration changes
- **CORS**: Configure CORS_ORIGINS in Adobe App Builder
- **SSL**: Use HTTPS in production for all providers

### Useful Logs
- `var/log/system.log` - General Magento logs
- `var/log/exception.log` - PHP errors
- Browser Console - JavaScript errors

### Additional Documentation
- [Adobe Commerce DevDocs](https://devdocs.magento.com/)
- [Adobe App Builder Docs](https://developer.adobe.com/app-builder/)
- [OAuth Provider Documentation](../PROVIDER_EXAMPLES.md)

---

**Developed by Webjump** - Technology that transforms business

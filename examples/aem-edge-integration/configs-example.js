/**
 * Exemplo de configuração para Social Login no AEM Edge
 * Adicione estas configurações ao seu scripts/configs.js
 */

// Configurações a serem adicionadas ao seu configs existente
export const socialLoginConfigs = {
  // === SOCIAL LOGIN CONFIGURATION ===
  'social-login-enabled': 'true',
  'social-login-api-endpoint': 'https://612360-752yellowcrawdad-stage.adobeio-static.net/api/v1/social-login',
  'social-login-providers': 'google,meta,linkedin',
  'social-login-theme': 'light',
  'social-login-button-size': 'medium',
  'social-login-show-labels': 'true',
  'social-login-redirect-url': '/customer/account/',

  // Opcional: Debug mode
  'social-login-debug': 'false',

  // Opcional: Configurações específicas para checkout
  'social-login-checkout-enabled': 'true',
  'social-login-checkout-providers': 'google,meta',
};

// Exemplo completo de como deve ficar seu scripts/configs.js:
/*
const configs = {
  // Suas configurações existentes do aem-boilerplate-commerce
  'commerce-endpoint': 'https://your-commerce-domain.com',
  'commerce-store-view-code': 'default',

  // Adicionar configurações do Social Login
  ...socialLoginConfigs
};

export function getConfigValue(configKey) {
  return configs[configKey];
}
*/
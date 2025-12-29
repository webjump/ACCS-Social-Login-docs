# ACCS Social Login docs

# Installation Guide - Social Login for Adobe Commerce

This guide was created to help you install and configure the Social Login extension in your Adobe App Builder project through Adobe Exchange in a simple and straightforward way, without requiring advanced technical knowledge.

## 📋 What You Need to Know

This guide assumes that you:
- Have access to Adobe Exchange
- Have an Adobe Developer account
- Have access to the Adobe Commerce administrative panel
- Have developer accounts with social login providers (Google and/or Facebook)

## What does this extension do?

The Social Login extension allows your customers to log in to your site using their Google or Facebook accounts, instead of creating a new account. This makes the registration and login process faster and more convenient.

### Business Benefits:

- **Reduced cart abandonment**: Faster login = more conversions
- **Better customer experience**: Fewer forms to fill out
- **Increased registrations**: Customers prefer social login
- **Reliable data**: Information validated by providers

## 🔧 Step 1: Configure OAuth Credentials

Before installing the extension, you need to create applications with social login providers (Google and/or Facebook) to obtain the necessary credentials. These credentials will be used during the extension configuration in Adobe Exchange.

### 1.1 Configure Google OAuth

#### Create Project in Google Cloud Console

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Click **Select a project** at the top
3. Click **New Project**
4. Give the project a name (e.g., "My E-commerce Social Login")
5. Click **Create**

#### Create OAuth Credentials

1. In the side menu, go to **APIs & Services** > **Credentials**
2. Click **+ Create Credentials** > **OAuth client ID**
3. If prompted, configure the OAuth consent screen:
   - Choose **External**
   - Fill in the basic information
   - Add your email as a test user
4. Configure the OAuth client:
   - **Application type**: Web application
   - **Name**: Social Login - My E-commerce
   - **Authorized redirect URIs**: 
     ```
     https://your-app.adobeio-static.net/api/v1/web/social-login/google-callback-page
     ```
     > **Note**: You will need to update this URL after installing the extension in Adobe Exchange. The URL will be provided during installation.

5. Click **Create**
6. **Copy and save** in a secure location:
   - **Client ID** - something like: `123456789-abc.apps.googleusercontent.com`
   - **Client Secret** - a long string

### 1.2 Configure Facebook/Meta OAuth

#### Create App in Meta for Developers

1. Go to [Meta for Developers](https://developers.facebook.com/)
2. Click **My Apps** > **Create App**
3. Choose **Consumer** as the app type
4. Fill in:
   - **App Name**: My E-commerce Social Login
   - **Contact Email**: Your email
5. Click **Create App**

#### Configure Facebook Login

1. In the app dashboard, find **Facebook Login** and click **Set Up**
2. Choose **Web** as the platform
3. Configure:
   - **Site URL**: `https://your-site.com`
   - **Valid OAuth Redirect URIs**:
     ```
     https://your-app.adobeio-static.net/api/v1/web/social-login/meta-callback-page
     ```
     > **Note**: You will need to update this URL after installing the extension in Adobe Exchange. The URL will be provided during installation.

4. In the side menu, go to **Settings** > **Basic**
5. **Copy and save** in a secure location:
   - **App ID** - a long number
   - **App Secret** - click **Show** to view

## 🚀 Step 2: Install the Extension via Adobe Exchange

### 2.1 Access Adobe Exchange

1. Go to [Adobe Exchange](https://exchange.adobe.com/)
2. Log in with your Adobe Developer account
3. Navigate to the Adobe App Builder extensions section
4. Search for **"Social Login"** or **"ACCS Social Login"**

### 2.2 Install the Extension

1. Click on the Social Login extension
2. Review the extension information
3. Click **Install** or **Add to Project**
4. Select the Adobe App Builder project where you want to install
5. Confirm the installation

The extension will be installed automatically. You will receive a URL for your app, something like:
```
https://your-app.adobeio-static.net
```

**Save this URL** - you will need it in the next steps!

## ⚙️ Step 3: Configure the Extension

After installation, you will need to configure the extension with your credentials and Adobe Commerce information.

### 3.1 Access Settings

1. In Adobe Exchange, go to **My Apps** or **My Projects**
2. Find the project where you installed the Social Login extension
3. Click **Configure** or **Settings**

### 3.2 Configure Adobe Commerce

Fill in your Adobe Commerce information:

- **Adobe Commerce Base URL**: 
  - Example: `https://your-ecommerce.adobecommerce.com`
  - This is the base URL of your Adobe Commerce (without `/graphql` or `/rest`)

- **Adobe Commerce API Token**: 
  - See how to obtain it in [Step 3.3](#33-obtain-adobe-commerce-api-token)

- **Adobe Commerce Store Code**: 
  - Usually `default`
  - If you use multiple stores, use the main store code

### 3.3 Obtain Adobe Commerce API Token

To obtain the Adobe Commerce API token:

1. Access the Adobe Commerce administrative panel
2. Go to **System** > **Extensions** > **Integrations**
3. Click **Add New Integration**
4. Fill in:
   - **Name**: Social Login Integration
   - **Email**: Your email
   - **Password**: Create a secure password
5. In **API Resources**, select:
   - ✅ **Customer**
   - ✅ **Shopping Cart**
6. Click **Save**
7. After saving, click **Activate** in the Actions column
8. On the next screen, **copy the Access Token**
9. Paste this token in the **Adobe Commerce API Token** field in the extension settings

### 3.4 Configure Google OAuth

Fill in the Google credentials you obtained in Step 1.1:

- **Google Client ID**: Paste the Client ID you copied
- **Google Client Secret**: Paste the Client Secret

### 3.5 Configure Meta/Facebook OAuth

Fill in the Meta/Facebook credentials you obtained in Step 1.2:

- **Meta App ID**: Paste the App ID you copied
- **Meta App Secret**: Paste the App Secret

### 3.6 Configure Widget Domain

- **Widget Domain**: Use the app URL you received after installation
  - Example: `https://your-app.adobeio-static.net`
  - This URL should already be automatically filled in

### 3.7 Configure CORS Origins

- **CORS Origins**: Add your site domains, separated by commas
  - Example: `https://your-site.com,https://www.your-site.com`
  - Add all domains where the widget will be used

### 3.8 Save Settings

1. Review all settings
2. Click **Save** or **Apply**
3. Wait for confirmation that the settings have been saved

## 🔄 Step 4: Update Redirect URLs

Now that you have the app URL (provided by Adobe Exchange), update the redirect URLs in the OAuth providers.

### 4.1 Update Google OAuth

1. Go back to [Google Cloud Console](https://console.cloud.google.com/)
2. Go to **APIs & Services** > **Credentials**
3. Click on your **OAuth Client ID**
4. In **Authorized redirect URIs**, add or update:
   ```
   https://your-app.adobeio-static.net/api/v1/web/social-login/google-callback-page
   ```
   (Replace `your-app.adobeio-static.net` with your actual app URL)
5. Click **Save**

### 4.2 Update Meta/Facebook OAuth

1. Go back to [Meta for Developers](https://developers.facebook.com/)
2. Select your app
3. Go to **Facebook Login** > **Settings**
4. In **Valid OAuth Redirect URIs**, add or update:
   ```
   https://your-app.adobeio-static.net/api/v1/web/social-login/meta-callback-page
   ```
   (Replace `your-app.adobeio-static.net` with your actual app URL)
5. Click **Save Changes**

## ✅ Step 5: Verify Installation

### 5.1 Test the Installation

1. Access your app URL: `https://your-app.adobeio-static.net/index.html`
   (Use the URL provided by Adobe Exchange)
2. You should see a page with social login buttons
3. Click **"Login with Google"** or **"Login with Facebook"**
4. Complete the login in the popup that opens
5. Verify that the login was successful
6. You should see a success message or be redirected

### 5.2 Verify in Adobe Commerce

1. Access the Adobe Commerce administrative panel
2. Go to **Customers** > **All Customers**
3. Verify that the customer who logged in with social login appears in the list
4. If it appears, the integration is working correctly!

## 🎨 Step 6: Integrate into Your Site

Now that the extension is installed and working, you need to integrate it into your site. There are different ways to do this depending on your platform.

### Option 1: Simple HTML

If you have access to your site's HTML code:

1. Add the widget to the login page:
   ```html
   <div id="social-login-widget"></div>
   
   <script src="https://your-app.adobeio-static.net/SocialLoginWidget.js"></script>
   <script>
       new SocialLoginWidget({
           containerId: 'social-login-widget',
           apiEndpoint: 'https://your-app.adobeio-static.net/api/v1/web/social-login',
           providers: ['google', 'meta'],
           theme: 'light',
           showLabels: true,
           buttonSize: 'medium',
           onSuccess: function(data) {
               console.log('Login successful:', data);
               // Redirect or update UI
               window.location.href = '/customer/account/';
           },
           onError: function(error) {
               console.error('Login failed:', error);
               alert('Login error. Please try again.');
           }
       });
   </script>
   ```
   > **Remember**: Replace `your-app.adobeio-static.net` with your actual app URL

### Option 2: Adobe Commerce Module

If you use traditional Adobe Commerce, see the guide at:
- `examples/magento-module/README.md`

This module allows you to configure Social Login directly from the Adobe Commerce administrative panel.

### Option 3: AEM Edge Delivery

If you use AEM Edge Delivery Services, see the guide at:
- `examples/aem-edge-integration/README.md`

This guide shows how to integrate Social Login into AEM Edge blocks.

## 🔍 Common Troubleshooting

### Problem: "Failed to get authorization URL"

**Possible causes and solutions**:
- **Incorrect OAuth credentials**: Verify that Google or Meta credentials are correct in the extension settings
- **Extension not configured**: Make sure you saved all settings in Adobe Exchange
- **Incorrect app URL**: Verify that the Widget Domain URL is correct

**How to verify**:
1. Access the extension settings in Adobe Exchange
2. Review all credentials
3. Save the settings again

### Problem: Popup closes but login doesn't complete

**Possible causes and solutions**:
- **Incorrect redirect URLs**: Verify that URLs in OAuth providers are correct
- **CORS not configured**: Verify that your site domain is in CORS Origins
- **Console error**: Open the browser console (F12) and check for errors

**How to verify**:
1. Open the browser console (F12)
2. Go to the Console tab
3. Try logging in again
4. See if there are error messages

### Problem: Customer doesn't appear in Adobe Commerce

**Possible causes and solutions**:
- **Incorrect Commerce URL**: Verify that `COMMERCE_CLOUD_BASE_URL` is correct
- **Invalid API token**: Verify that the token has the correct permissions
- **Incorrect Store Code**: Verify that the store code is correct

**How to verify**:
1. Access the Adobe Commerce panel
2. Go to **System** > **Extensions** > **Integrations**
3. Verify that the Social Login integration is active
4. Test the API token by making a manual request

### Problem: CORS Error

**Possible causes and solutions**:
- **Domain not configured**: Add your site domain to CORS Origins
- **Incorrect protocol**: Make sure to use `https://` in domains

**How to resolve**:
1. Access the extension settings in Adobe Exchange
2. In **CORS Origins**, add all domains where the widget will be used
3. Use the format: `https://your-site.com,https://www.your-site.com`
4. Save the settings

### Problem: Login buttons don't appear

**Possible causes and solutions**:
- **JavaScript not loaded**: Verify that the `SocialLoginWidget.js` script is being loaded
- **Container doesn't exist**: Verify that the element with `id="social-login-widget"` exists on the page
- **Console error**: Open the browser console to see errors

**How to verify**:
1. Open the browser console (F12)
2. Go to the Network tab
3. Reload the page
4. Verify that `SocialLoginWidget.js` was loaded successfully (status 200)

## 📞 Need Help?

If you encounter problems during installation:

1. **Consult the technical documentation**:
   - `README.md` - Complete technical documentation

2. **Check the examples**:
   - `examples/README.md` - Integration examples guide

3. **Adobe Exchange Support**:
   - Access support through Adobe Exchange
   - Check if there is additional documentation on the extension page

4. **Check the logs**:
   - In Adobe Exchange, you can access application logs
   - Look for errors related to Social Login actions
  

## 🎉 Done!

If all checklist items are checked, your Social Login extension is installed and working! Your customers can now log in using Google or Facebook.

---

**Tip**: Keep this guide saved for future reference. If you need to make changes or updates, you can access the extension settings in Adobe Exchange at any time.


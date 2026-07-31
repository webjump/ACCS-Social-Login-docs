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

This extension supports **both Adobe Commerce PaaS** (Cloud/on-premises) **and Adobe Commerce SaaS** (Adobe Commerce as a Cloud Service). **PaaS and SaaS use structurally different API endpoints, not just different credentials** — fill in **only one** of the two credential blocks below, matching your Commerce flavor. If both credential types are filled in, the OAuth 1.0a credentials take priority.

Two separate things are being configured here, and the extension treats them independently:

- **Which Commerce flavor you have** comes from the endpoint you fill in — an **Adobe Commerce Base URL** means PaaS, a **Commerce as a Cloud Service API URL** plus **Instance ID** means SaaS. This determines the API paths used and which Commerce APIs are available at all.
- **How the extension authenticates** comes from the credentials you fill in — OAuth 1.0a integration credentials, or an IMS technical account. IMS is also valid on **PaaS from Adobe Commerce 2.4.7+**; in that case the endpoints stay in the PaaS format.

### 3.3-PaaS: Obtain Commerce OAuth credentials (Adobe Commerce Cloud / on-premises)

- **Adobe Commerce Base URL**:
  - Example: `https://your-ecommerce.adobecommerce.com`
  - This is the base URL of your Adobe Commerce (without `/graphql` or `/rest`)
  - Requests go to `{baseUrl}/rest/{storeCode}/V1/...`

- **Adobe Commerce Store Code**:
  - Usually `default`
  - If you use multiple stores, use the main store code

1. Access the Adobe Commerce administrative panel
2. Go to **System** > **Extensions** > **Integrations**
3. Click **Add New Integration**
4. Fill in:
   - **Name**: Social Login Integration
   - **Email**: Your email
   - **Password**: Create a secure password
5. In **API Resources**, select at least:
   - ✅ **Customer** (the extension searches, creates, and generates session tokens for customers)
6. Click **Save**
7. After saving, click **Activate** in the Actions column
8. On the next screen, copy **Consumer Key**, **Consumer Secret**, **Access Token**, and **Access Token Secret**
9. Paste these into the matching fields in the extension settings:
   - **Adobe Commerce base URL (PaaS)**
   - **Adobe Commerce Store Code (PaaS)**
   - **Commerce OAuth Consumer Key (PaaS)**
   - **Commerce OAuth Consumer Secret (PaaS)**
   - **Commerce OAuth Access Token (PaaS)**
   - **Commerce OAuth Access Token Secret (PaaS)**

### 3.3-SaaS: Obtain Commerce IMS credentials (Adobe Commerce as a Cloud Service)

- **Commerce as a Cloud Service API URL**: the API Mesh gateway, e.g. `https://na1-sandbox.api.commerce.adobe.com`, **without** the instance id
- **Instance ID**: your Commerce as a Cloud Service tenant id
  - Requests go to `{apiUrl}/{instanceId}/V1/...` — no `/rest/{store}` segment, unlike PaaS. Do not reuse the PaaS base URL here, it's a different gateway with a different path structure
  - Commerce as a Cloud Service also doesn't expose the customer/guest REST APIs available on PaaS — this extension uses the `generateCustomerToken` GraphQL mutation for SaaS customer token generation instead of the REST endpoint used on PaaS

1. In the [Adobe Developer Console](https://developer.adobe.com/console), create (or reuse) a project/workspace and add an **OAuth Server-to-Server** credential — this creates a technical account with a `Client ID`, `Client Secret(s)`, `Technical Account ID`, `Technical Account Email`, and `Org ID`, all shown in the credential details
2. In the **Commerce Admin**, the Technical Account Email must exist as an **Admin User** (*System > Permissions > All Users*), assigned a **Role** (*System > Permissions > User Roles*) with the **Customers** resource enabled — Commerce has no separate Integrations screen for IMS credentials like it does for OAuth 1.0a, so this is how it maps the token's identity to ACL permissions. Without this step, requests fail with `The consumer isn't authorized to access %resources.` even though the IMS token itself is valid
3. Paste the values into the matching fields in the extension settings:
   - **Commerce as a Cloud Service API URL (SaaS)**
   - **Commerce as a Cloud Service Instance ID (SaaS)**
   - **Commerce IMS Client ID (SaaS)**
   - **Commerce IMS Client Secrets (SaaS)** — single value, or JSON array for secret rotation
   - **Commerce IMS Technical Account ID (SaaS)**
   - **Commerce IMS Technical Account Email (SaaS)**
   - **Commerce IMS Organization ID (SaaS)**
   - **Commerce IMS API Key (SaaS)** — optional, defaults to the Client ID
   - **Commerce IMS Scopes (SaaS)** — JSON array. **Must include `commerce.accs`**, or Commerce as a Cloud Service rejects every call even though IMS issued a valid token. Copy the scope list shown on the OAuth Server-to-Server credential in the Adobe Developer Console; it is normally `["openid","AdobeID","email","profile","additional_info.roles","additional_info.projectedProductContext","commerce.accs"]`
   - **Commerce IMS Environment (SaaS)** — typically `prod`

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

- **CORS Origins**: the storefront origins allowed to call this extension. Strongly recommended for production
  - Comma-separated list of origins — scheme and host only, no path: `https://www.example.com,https://example.com`
  - `https://www.example.com` and `https://example.com` are different origins. List every domain your storefront is reachable on
  - When set, browser requests from any other origin are refused with `403 Origin not allowed`, and the login result — which carries a real Adobe Commerce customer session token — is delivered only to these origins
  - The **Widget Domain** is always allowed automatically; you don't need to list it
  - Leaving this empty accepts requests from any origin and delivers the login result to whichever page opened the login popup

### 3.8 Configure the internal secrets (required)

These two are **required** — without them every login fails with `500 Commerce configuration missing`. The extension doesn't generate them for you.

Generate a different random value for each. On any machine with Node.js installed:

```bash
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
```

- **Internal Authentication Secret**: signs the internal token that proves a customer request came from a completed OAuth flow, rather than someone calling the action directly
- **Customer Password Encryption Key**: encrypts (AES-256-GCM) the password generated for each social-login customer, so a real Commerce customer token can be issued for them later

Both must stay **stable** in production:
- Rotating the **Customer Password Encryption Key** invalidates every stored customer credential. Affected shoppers get `409 ACCOUNT_LINK_REQUIRED` on their next social login and have to sign in with their password once so a new credential is issued
- Rotating the **Internal Authentication Secret** only invalidates logins already in flight (those tokens live 2 minutes)

### 3.9 Configure Log Level

- **Log Level**: leave this at `info` for production
  - Use `debug` only while troubleshooting — it writes considerably more request detail to the activation logs
  - Set it back to `info` when you're done
  - Tokens and passwords are never logged at any level

### 3.10 Save Settings

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

This module allows you to configure Social Login directly from the Adobe Commerce administrative panel, and opens the customer session from the Commerce customer token the extension issues — validating it against Commerce's own token storage. If you adapt it, read the Authentication section of its README first: an endpoint that logs a shopper in based on data the browser supplied is an account takeover.

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
- **Incorrect Widget Domain**: Verify the Widget Domain setting matches your app's actual installed URL
- **Console error**: Open the browser console (F12) and check for errors

**How to verify**:
1. Open the browser console (F12)
2. Go to the Console tab
3. Try logging in again
4. See if there are error messages

### Problem: "This email already has an Adobe Commerce account"

This is expected behaviour, not a misconfiguration. It happens when the shopper already had a **regular** Adobe Commerce account — created with an email and password — using the same email address as their social account.

To hand the shopper a real Commerce session, the extension exchanges the password it generated when it created their account. An account it didn't create has no such credential on file, so no session token can be issued for it. The extension answers with `409 ACCOUNT_LINK_REQUIRED` and the shopper sees the message above.

**What the shopper should do**: sign in once with their existing email and password.

**Known limitation**: the extension does not automatically link a pre-existing password account to a social identity. Doing so would mean resetting the shopper's password without their consent, which it deliberately does not do.

### Problem: Customer doesn't appear in Adobe Commerce

**Possible causes and solutions**:
- **Incorrect Commerce URL**: PaaS - verify the Adobe Commerce Base URL/Store Code; SaaS - verify the Commerce as a Cloud Service API URL/Instance ID (don't mix the two, they're different gateways with different path structures)
- **Missing permissions**: Verify that your Integration (PaaS) or IMS Technical Account (SaaS) has the **Customers** ACL resource enabled
- **`The consumer isn't authorized to access %resources.` error** (not SaaS-specific - can happen on PaaS too): SaaS - the Technical Account Email isn't set up as an Admin User with a Role that has **Customers** enabled (see [Step 3.3-SaaS](#33-saas-obtain-commerce-ims-credentials-adobe-commerce-as-a-cloud-service)); PaaS - the Integration's API Resource Access doesn't include **Customers**
- **404 `Request does not match any route` when generating a token (SaaS)**: expected if the Commerce as a Cloud Service API URL/Instance ID aren't set - SaaS doesn't expose the REST customer-token endpoint used on PaaS, the extension uses a GraphQL mutation there instead
- **`The account sign-in was incorrect or your account is disabled temporarily`**: Commerce's standard customer account lockout after repeated failed sign-in attempts, not a bug - unlock the customer in Commerce Admin (**Customers > All Customers**, mass action "Unlock") or wait for the lockout period to expire

**How to verify**:
1. Access the Adobe Commerce panel
2. PaaS: go to **System** > **Extensions** > **Integrations** and verify the Social Login integration is active. SaaS: go to **System** > **Permissions** > **All Users** and verify the Technical Account Email exists with a Role that has the Customers permission

## 🔐 Security and Token System

### How the Security System Works

The Social Login extension always issues a **real Adobe Commerce customer session token** — the same kind of token your storefront already knows how to use. There is no fabricated or backup token format; if a real Commerce token can't be issued, login fails with a clear error instead of silently falling back to something else.

#### Complete Adobe Commerce Integration Process

When a customer logs in with a social provider (Google or Facebook), the system goes through several steps:

**Step 1: Customer Search in Adobe Commerce**
- The system searches for the customer in your Adobe Commerce database using their email
- Uses official Adobe Commerce authentication (OAuth 1.0a for PaaS, or IMS for SaaS — see Step 3.3)
- If the customer doesn't exist, creates a new account automatically
- If the customer exists, proceeds with that account

**Step 2: Real Commerce Token Generation**
- For a new customer, the extension generates a random password at account-creation time and stores it encrypted (AES-256-GCM) — this is what lets it request a real token later, since Adobe Commerce's token endpoint requires an actual username/password pair; there is no way to mint a customer token without one
- The extension then exchanges that stored password for a genuine customer session token via Adobe Commerce's own token endpoint — REST `POST /V1/integration/customer/token` on PaaS, or the `generateCustomerToken` GraphQL mutation on SaaS (Commerce as a Cloud Service doesn't expose the REST customer/guest APIs available on PaaS). Which one is used depends on your **Commerce flavor**, not on which credentials authenticate the call
- If no stored credential is on file for a customer — typically an account that already existed in Commerce before this extension was installed — the extension answers with a specific, actionable result (`409 ACCOUNT_LINK_REQUIRED`) asking the shopper to sign in with their password once, instead of a generic failure. See [this account already exists](#problem-this-email-already-has-an-adobe-commerce-account) in the troubleshooting section

### Security Layers Implemented

**1. Adobe Commerce Native Authentication**
- Uses official Adobe Commerce authentication libraries (OAuth 1.0a for PaaS, IMS for SaaS)
- Secure customer search with specific filters
- Real token generation via Adobe Commerce's own token endpoint
- No locally-generated substitute token of any kind

**2. Encrypted Credential Storage**
- The password generated for each social-login customer is encrypted (AES-256-GCM) before being stored, and only ever used server-side to request a Commerce token
- Refreshed automatically on every successful login so active accounts don't lose access

**3. Multi-Layer Validation**
- CSRF protection via a cryptographically random OAuth `state`, validated once and discarded
- A one-time-use authentication token (5-minute expiration) links the OAuth callback to the token-generation step
- An internally signed token (HMAC) proves a customer-creation request actually came from a completed OAuth flow, not a direct call

**4. Origin Allowlist**
- With **CORS Origins** configured (Step 3.7), browser requests from any other origin are refused with `403`, and `Access-Control-Allow-Origin` is echoed back only for allowed origins
- The login result is delivered only to those origins, instead of to whichever page opened the login popup
- The **Widget Domain** is allowed implicitly, since the OAuth callback page is served from there and calls these same actions

**5. The session token stays out of the return channel**

Google's sign-in pages sever the popup's link back to your storefront (`Cross-Origin-Opener-Policy`), so the extension also parks the login outcome server-side under the OAuth `state` and the storefront polls for it. That `state` is not a secret — it passes through the social provider and appears in URLs, browser history and the provider's logs — so the channel is built on the assumption that it leaks:

- What gets parked is the **one-time authentication token** (5-minute expiration, single use, tied to one email address), never a Commerce customer session token. Your storefront redeems it for the session token in a separate, origin-checked call. Someone holding the `state` can at most consume that single redemption — they never get a reusable session
- Parking a **successful** outcome requires the internally signed token, which only the extension's own OAuth callback can produce, and only for the exact `state` it was issued against. So an attacker can't complete a login of their own and park its result under someone else's `state` to log that shopper into an account the attacker controls
- Error messages come from a fixed list inside the extension, never from whoever made the request
- The outcome is written once and read once, then deleted, and expires on its own after 10 minutes

**6. Identity Binding**

The extension's actions are public web endpoints — they have to be, because they're called by your storefront and by the OAuth providers' redirects. What protects them is that every step is bound to the identity the provider actually verified:

- The internally signed token carries the verified email, provider and provider id. If the data submitted alongside it disagrees in any way, the request is rejected — the email can't be swapped in the browser
- Accounts are matched by email address, so an email the social provider hasn't verified is refused outright
- The one-time authentication token is bound to the email it was issued for. It cannot be used to request a session for a different account, and every lookup afterwards uses the email from the validated token rather than whatever the request body says
- Each token is consumed on first use, so a captured token can't be replayed

### What This Means for You

**Data Security**: Customer data and the session token both come directly from your Adobe Commerce instance — nothing is fabricated locally.

**Predictable failure**: if Adobe Commerce can't issue a token (misconfigured permissions, unreachable instance), login fails visibly with an error, rather than silently degrading to a token your storefront might not fully trust. The one case that isn't a failure at all — a pre-existing account with no stored credential — gets its own actionable response so your storefront can tell the shopper exactly what to do.

**Monitoring**: The system logs authentication attempts for security monitoring, without logging tokens or passwords at any log level.

### Authentication Response Information

When a customer successfully logs in, the system provides:

```json
{
  "success": true,
  "customer": {
    "id": 12345,                        // Real ID from Adobe Commerce
    "email": "customer@example.com",    // Email from Adobe Commerce
    "firstName": "John",                // Name from Adobe Commerce
    "lastName": "Doe"                   // Last name from Adobe Commerce
  },
  "token": "<real Adobe Commerce customer token>",
  "tokenInfo": {
    "type": "Commerce API Token",
    "source": "adobe_commerce_api"
  }
}
```

### Production Security Configuration

For production, make sure to:

**Adobe Commerce Integration:**
- Secure connections (HTTPS only)
- Official API authentication (OAuth 1.0a for PaaS, IMS for SaaS)
- **CORS Origins** filled in with every storefront domain that hosts the widget (Step 3.7) — this is what restricts who can call the actions and who receives the session token
- The **Customers** ACL resource enabled on the integration/technical account used
- Keep the extension's internal secrets (Internal Authentication Secret, Customer Password Encryption Key) stable — rotating the Customer Password Encryption Key invalidates every stored customer credential, requiring affected customers to log in again to get a new one issued
- Keep **Log Level** at `info` — `debug` is for troubleshooting only and writes far more request detail to the activation logs

### Best Practices for Security

✅ **Always use HTTPS** in production
✅ **Monitor authentication logs** regularly
✅ **Keep Adobe Commerce credentials secure**
✅ **Update OAuth credentials periodically**
✅ **Use Commerce credentials with the minimum permissions required**

### Common Error Codes

**401**: Authentication failed — the authentication token is invalid, expired, already used, or was issued for a different email address; or the customer wasn't found in Commerce
**403 `EMAIL_NOT_VERIFIED`**: the social provider hasn't verified the email address on that account, so it can't be matched to a Commerce customer
**403 Origin not allowed**: the request came from a browser origin that isn't in **CORS Origins** — see [CORS Error](#problem-cors-error)
**409 `ACCOUNT_LINK_REQUIRED`**: the email already has a regular Adobe Commerce account that wasn't created by this extension — see [this account already exists](#problem-this-email-already-has-an-adobe-commerce-account)
**500**: Commerce configuration missing (base URL, credentials, or internal secrets not set)

### Problem: CORS Error

**Possible causes and solutions**:
- **Origin missing from the allowlist**: if you filled in **CORS Origins**, every storefront domain that hosts the widget must be listed there, or its requests are refused with `403 Origin not allowed`. Add the missing origin (scheme and host, e.g. `https://www.example.com`) and save. Leaving the field empty accepts any origin
- **Incorrect protocol**: Make sure to use `https://` in domains you reference from the widget, and that the origin you list matches exactly (`https://www.example.com` and `https://example.com` are different origins)
- **Stale deployment**: Confirm the extension is up to date and reinstall/reconfigure if needed
- **Browser extension or proxy interfering**: Test in a clean browser profile

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


# Integration Setup Guide

Complete guide for setting up all integrations in LifeHub.

## Table of Contents
1. [SMTP Email Configuration](#smtp-email-configuration)
2. [Telegram Bot Setup](#telegram-bot-setup)
3. [Google OAuth Setup](#google-oauth-setup)
4. [CoinGecko API (Crypto Prices)](#coingecko-api)
5. [Database Configuration](#database-configuration)

---

## SMTP Email Configuration

### Step 1: Get SMTP Credentials

#### Option A: Gmail
1. Enable 2-Factor Authentication on your Google account
2. Go to https://myaccount.google.com/security
3. Click "App passwords"
4. Select "Mail" and your device
5. Copy the generated 16-character password

#### Option B: SendGrid
1. Sign up at https://sendgrid.com
2. Go to Settings → API Keys
3. Create a new API key with "Mail Send" permissions
4. Copy the API key

#### Option C: Mailgun
1. Sign up at https://mailgun.com
2. Go to Sending → Domain Settings
3. Note your SMTP credentials

### Step 2: Configure Environment Variables

For Replit:
1. Click on "Tools" → "Secrets"
2. Add the following secrets:
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM_EMAIL=your-email@gmail.com
```

For Hostinger/cPanel:
1. Edit your `.env` file or set via cPanel environment variables
2. Add the same variables as above

### Step 3: Test Email

1. Go to System Management → Configuration → Email
2. Enter your SMTP settings
3. Click "Send Test Email"
4. Check your inbox

---

## Telegram Bot Setup

### Step 1: Create Telegram Bot

1. Open Telegram and search for "@BotFather"
2. Send `/newbot` command
3. Follow prompts to name your bot
4. Copy the Bot Token (looks like: `123456789:ABCdefGHIjklMNOpqrsTUVwxyz`)

### Step 2: Get Your Chat ID

1. Send a message to your bot
2. Open: `https://api.telegram.org/bot<YOUR_BOT_TOKEN>/getUpdates`
3. Look for `"chat":{"id":123456789}` in the response
4. Copy your chat ID

### Step 3: Configure Environment Variables

Add to Replit Secrets or `.env` file:
```
TELEGRAM_BOT_TOKEN=123456789:ABCdefGHIjklMNOpqrsTUVwxyz
```

### Step 4: Set Chat ID in Profile

1. Go to Profile → Settings
2. Enter your Telegram Chat ID
3. Save settings

### Step 5: Test Telegram Notifications

1. Go to System Management → Configuration → Telegram
2. Enter your bot token
3. Click "Send Test Message"
4. Check your Telegram app

---

## Google OAuth Setup

### Step 1: Create Google Cloud Project

1. Go to https://console.cloud.google.com
2. Create a new project or select existing one
3. Enable Google+ API:
   - Go to "APIs & Services" → "Library"
   - Search for "Google+ API"
   - Click "Enable"

### Step 2: Create OAuth Credentials

1. Go to "APIs & Services" → "Credentials"
2. Click "Create Credentials" → "OAuth client ID"
3. Select "Web application"
4. Add Authorized Redirect URIs:
   - For Replit: `https://your-repl-name.your-username.repl.co/oauth_callback.php`
   - For Hostinger: `https://yourdomain.com/oauth_callback.php`
5. Click "Create"
6. Copy Client ID and Client Secret

### Step 3: Configure Environment Variables

Add to Replit Secrets or `.env` file:
```
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
```

### Step 4: Test Google Sign-In

1. Go to the login page
2. You should see "Continue with Google" button
3. Click it to test OAuth flow

---

## CoinGecko API

### Note
CoinGecko's free API doesn't require authentication! The system is already configured.

### Rate Limits
- Free tier: 10-30 calls/minute
- Demo/Pro tier: Higher limits available

### Optional: Get API Key (For Higher Limits)

1. Sign up at https://www.coingecko.com/api
2. Go to Dashboard → API Keys
3. Copy your API key
4. Add to environment variables:
```
COINGECKO_API_KEY=your-api-key
```

---

## Database Configuration

### PostgreSQL (Replit)

Database is automatically configured! The following environment variables are set:
- `DATABASE_URL`
- `PGHOST`
- `PGPORT`
- `PGUSER`
- `PGPASSWORD`
- `PGDATABASE`

### PostgreSQL (Hostinger/External)

1. Create a PostgreSQL database in cPanel
2. Note the credentials
3. Import the schema:
```bash
psql -h hostname -U username -d database_name < database.sql
```

4. Update environment variables or `includes/config.php`:
```php
define('DB_HOST', 'your-db-host');
define('DB_PORT', '5432');
define('DB_NAME', 'your-db-name');
define('DB_USER', 'your-db-user');
define('DB_PASS', 'your-db-password');
```

---

## Verification Checklist

After setting up all integrations, verify:

- [ ] Email notifications working (test via System Management)
- [ ] Telegram notifications working (test via System Management)
- [ ] Google OAuth login working (test on login page)
- [ ] Crypto prices updating (check crypto portfolio page)
- [ ] Database connection successful (dashboard loads)
- [ ] Cron jobs configured (see DEPLOYMENT.md)

---

## Troubleshooting

### SMTP Not Working
- Check firewall allows port 587
- Verify app password is correct (not regular password)
- Try port 465 with SSL instead of TLS

### Telegram Not Receiving Messages
- Verify bot token is correct
- Make sure you've sent at least one message to the bot
- Check chat ID is correct number format

### Google OAuth Errors
- Verify redirect URI matches exactly (including https://)
- Check OAuth consent screen is configured
- Make sure Google+ API is enabled

### Crypto Prices Not Updating
- Check cron job is running (System Management → Cron Jobs)
- Verify internet connection allows API calls
- Check CoinGecko API status

---

## Security Best Practices

1. **Never commit secrets to Git**
   - Use `.gitignore` for `.env` files
   - Use Replit Secrets for sensitive data

2. **Use environment variables**
   - All API keys should be in environment variables
   - Never hardcode credentials

3. **Enable HTTPS**
   - Required for OAuth
   - Protects data in transit

4. **Rotate keys regularly**
   - Change API keys every 90 days
   - Update passwords periodically

---

## Support

For issues or questions:
1. Check the main [README.md](README.md)
2. Review [DEPLOYMENT.md](DEPLOYMENT.md)
3. Check System Management → System Diagnostics
4. Review job logs in System Management → System Logs

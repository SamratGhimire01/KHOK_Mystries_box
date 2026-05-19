<?php
// ─────────────────────────────────────────
//  K HO K — Payment Configuration
//  config/payment.php
// ─────────────────────────────────────────

// ══════════════════════════════════════
//  eSEWA CONFIGURATION
// ══════════════════════════════════════

// ── TEST MODE (active) ──
define('ESEWA_MERCHANT_ID',  'EPAYTEST');
define('ESEWA_SECRET_KEY',   '8gBm/:&EnhH.1/q');
define('ESEWA_GATEWAY_URL',  'https://rc-epay.esewa.com.np/api/epay/main/v2/form');
define('ESEWA_VERIFY_URL',   'https://rc-epay.esewa.com.np/api/epay/transaction/statuscheck');
define('ESEWA_MODE',         'test');

// ── REAL / PRODUCTION (uncomment when going live, comment test above) ──
// define('ESEWA_MERCHANT_ID',  'YOUR_REAL_ESEWA_MERCHANT_ID');
// define('ESEWA_SECRET_KEY',   'YOUR_REAL_ESEWA_SECRET_KEY');
// define('ESEWA_GATEWAY_URL',  'https://epay.esewa.com.np/api/epay/main/v2/form');
// define('ESEWA_VERIFY_URL',   'https://epay.esewa.com.np/api/epay/transaction/statuscheck');
// define('ESEWA_MODE',         'live');

// ══════════════════════════════════════
//  FONEPAY CONFIGURATION
// ══════════════════════════════════════

// ── TEST MODE (active) ──
define('FONEPAY_MERCHANT_ID',     'TEST_MERCHANT');
define('FONEPAY_SECRET_KEY',      'TEST_SECRET_KEY_FONEPAY');
define('FONEPAY_GATEWAY_URL',     'https://dev-clientapi.fonepay.com/api/merchant/merchantDetailsForThirdParty');
define('FONEPAY_VERIFY_URL',      'https://dev-clientapi.fonepay.com/api/merchant/merchantDetailsForThirdParty');
define('FONEPAY_MODE',            'test');

// ── REAL / PRODUCTION (uncomment when going live, comment test above) ──
// define('FONEPAY_MERCHANT_ID',     'YOUR_REAL_FONEPAY_MERCHANT_ID');
// define('FONEPAY_SECRET_KEY',      'YOUR_REAL_FONEPAY_SECRET_KEY');
// define('FONEPAY_GATEWAY_URL',     'https://clientapi.fonepay.com/api/merchant/merchantDetailsForThirdParty');
// define('FONEPAY_VERIFY_URL',      'https://clientapi.fonepay.com/api/merchant/merchantDetailsForThirdParty');
// define('FONEPAY_MODE',            'live');

// ══════════════════════════════════════
//  SHARED PAYMENT SETTINGS
// ══════════════════════════════════════
define('PAYMENT_SUCCESS_URL', APP_URL . '/payment/success');
define('PAYMENT_FAILURE_URL', APP_URL . '/payment/failed');
define('WHATSAPP_BUSINESS_NUMBER', '9779823045928'); // Replace with your WhatsApp business number
<?php
// ─────────────────────────────────────────
//  K HO K — App Configuration
//  config/app.php
// ─────────────────────────────────────────
define('APP_NAME',    'K HO K');
define('APP_URL',     'http://localhost/khok');
define('APP_VERSION', '1.0.0');
define('CURRENCY',    'Rs.');

// Session
define('SESSION_NAME',     'khok_session');
define('SESSION_LIFETIME', 86400); // 24 hours

// Upload paths
define('UPLOAD_DIR',       __DIR__ . '/../uploads/delivery_proof/');
define('UPLOAD_URL',       APP_URL . '/uploads/delivery_proof/');

// Box definitions
define('BOXES', [
    'shadow'  => ['name' => 'Shadow Box',  'tagline' => 'रहस्यको सुरुवात',      'price' => 999,   'min' => 1, 'max' => 2],
    'core'    => ['name' => 'Core Box',    'tagline' => 'असली खेल सुरु हुन्छ', 'price' => 2999,  'min' => 1, 'max' => 3],
    'pulse'   => ['name' => 'Pulse Box',   'tagline' => 'धड्कन बढ्छ',           'price' => 4999,  'min' => 2, 'max' => 4],
    'elite'   => ['name' => 'Elite Box',   'tagline' => 'छनोट भएकाहरूको लागि', 'price' => 9999,  'min' => 2, 'max' => 5],
    'phantom' => ['name' => 'Phantom Box', 'tagline' => 'सपनाको छेउमा',         'price' => 24999, 'min' => 3, 'max' => 6],
    'god'     => ['name' => 'GOD BOX',     'tagline' => 'देवताको छनोट',         'price' => 99999, 'min' => 5, 'max' => 10],
]);

<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/router.php';

startSession();
route();

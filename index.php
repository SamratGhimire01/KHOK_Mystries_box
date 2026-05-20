<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/payment.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/router.php';

startSession();
route();

// This file serves as the entry point for the application. It loads all necessary configurations and dependencies, starts the session, and then routes the incoming request to the appropriate handler based on the defined routes in the router configuration.
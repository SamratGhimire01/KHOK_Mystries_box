<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Checkout';
$pageCSS   = 'checkout.css';
$pageJS    = 'checkout.js';

require_once __DIR__ . '/../components/header.php';
?>

<main class="page-checkout">
    <div class="container">
        <h1>Checkout — coming in Phase 2</h1>
    </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>

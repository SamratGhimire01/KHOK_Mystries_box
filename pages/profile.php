<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Profile';
$pageCSS   = 'profile.css';
$pageJS    = 'profile.js';

require_once __DIR__ . '/../components/header.php';
?>

<main class="page-profile">
    <div class="container">
        <h1>Profile — coming in Phase 2</h1>
    </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>

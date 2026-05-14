<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();

$pageTitle = 'Track';
$pageCSS   = 'track.css';
$pageJS    = 'track.js';

require_once __DIR__ . '/../components/header.php';
?>

<main class="page-track">
    <div class="container">
        <h1>Track — coming in Phase 2</h1>
    </div>
</main>

<?php require_once __DIR__ . '/../components/footer.php'; ?>

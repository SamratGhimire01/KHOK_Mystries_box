<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../core/helpers.php';

startSession();
$pageTitle = '404 — Not Found';
require_once __DIR__ . '/../components/header.php';
?>
<main class="page-404">
    <div class="container" style="text-align:center;padding:6rem 1rem;">
        <h1 style="font-size:5rem;color:var(--accent)">404</h1>
        <p>This page doesn't exist.</p>
        <a href="<?= APP_URL ?>/" class="btn-primary">Go Home</a>
    </div>
</main>
<?php require_once __DIR__ . '/../components/footer.php'; ?>

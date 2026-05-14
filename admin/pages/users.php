<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../core/helpers.php';

startSession();
requireAdmin();

$pageTitle = 'Admin — Users';
require_once __DIR__ . '/../../components/header.php';
?>
<main class="admin-page">
    <div class="container">
        <h1>Admin: Users — coming in Phase 4</h1>
    </div>
</main>
<?php require_once __DIR__ . '/../../components/footer.php'; ?>

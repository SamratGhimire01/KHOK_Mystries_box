<?php
// components/footer.php — shared site footer
?>
<footer class="footer">
    <div class="footer-inner">
        <p class="footer-brand">K HO K</p>
        <p class="footer-tagline">Mystery. Technology. Nepal.</p>
        <p class="footer-copy">&copy; <?= date('Y') ?> K HO K. All rights reserved.</p>
    </div>
</footer>

<script src="<?= APP_URL ?>/public/js/main.js"></script>
<script>
document.querySelectorAll('a[href*="/logout"]').forEach(function(link) {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to logout?')) {
            window.location.href = this.href;
        }
    });
});
</script>
<?php if (isset($pageJS)): ?>
<script src="<?= APP_URL ?>/public/js/<?= e($pageJS) ?>"></script>
<?php endif; ?>
</body>
</html>

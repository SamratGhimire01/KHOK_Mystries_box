<?php // components/admin_footer.php ?>
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
</body>
</html>
</main>
<footer class="container pb-4">
    <div class="card bg-body-tertiary border-0 shadow-sm">
        <div class="card-body text-center small">
            <strong>VideoShare</strong> — <?= date('Y') ?> · <?= esc(t('footer_text', 'Built with PHP, MySQL, AJAX and Bootstrap.')) ?>
        </div>
    </div>
</footer>
<script>
window.VideoShare = {
    csrfToken: "<?= esc(csrf_token()) ?>",
    baseUrl: "<?= esc(base_url()) ?>"
};
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= esc(base_url('assets/js/app.js')) ?>"></script>
</body>
</html>

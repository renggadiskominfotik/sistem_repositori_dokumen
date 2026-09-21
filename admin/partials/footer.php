<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Auto cleanup sisa backdrop offcanvas bootstrap saat navigasi antar halaman PHP
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll('.offcanvas-backdrop, .modal-backdrop').forEach(function(el) {
        el.remove();
    });
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';
});
</script>
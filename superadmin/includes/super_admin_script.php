<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle sidebar on mobile
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebar');

        if (window.innerWidth < 992 &&
            !sidebar.contains(event.target) &&
            !toggleBtn.contains(event.target) &&
            sidebar.classList.contains('active')) {
            sidebar.classList.remove('active');
        }
    });

    // Responsive adjustments
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            document.getElementById('sidebar').classList.remove('active');
        }
    });

    document.querySelectorAll('.view-office-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('modalOfficeName').innerText = this.getAttribute('data-office-name');
                document.getElementById('modalAdminName').innerText = this.getAttribute('data-admin-name') || 'No Admin';
                document.getElementById('modalTotalAssets').innerText = this.getAttribute('data-total-assets') || '0';
            });
        });
</script>
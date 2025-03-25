<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#assetsTable').DataTable({
            order: [
                [0, 'desc']
            ]
        });

        // Handle edit button clicks
        $('.edit-btn').click(function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const category = $(this).data('category');
            const status = $(this).data('status');
            const description = $(this).data('description');
            const acquired = $(this).data('acquired');
            const value = $(this).data('value');
            const quantity = $(this).data('quantity');
            const unit = $(this).data('unit');
            const location = $(this).data('location');

            $('#edit_asset_id').val(id);
            $('#edit_asset_name').val(name);
            $('#edit_asset_category').val(category);
            $('#edit_asset_status').val(status);
            $('#edit_asset_description').val(description);
            $('#edit_date_acquired').val(acquired);
            $('#edit_asset_value').val(value);
            $('#edit_quantity').val(quantity || 1);
            $('#edit_unit').val(unit);
            $('#edit_location').val(location);
        });


        // Auto dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').alert('close');
        }, 5000);

        // Toggle sidebar on mobile
        $('#sidebarToggle').on('click', function() {
            $('#sidebar').toggleClass('collapsed');
            $('.main-content').toggleClass('expanded');
        });

        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
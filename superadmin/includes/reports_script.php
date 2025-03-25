<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize DataTables only if the table exists
        let tables = ['#assetStatusTable', '#maintenanceTable'];
        tables.forEach(function(table) {
            if ($(table).length) {
                $(table).DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
                    ],
                    destroy: true // Prevent duplicate initialization
                });
            }
        });

        // Print report functionality
        $('#printReport').click(function(e) {
            e.preventDefault();
            window.print();
        });

        // Initialize charts based on report type
        <?php if ($report_type == 'asset_summary' && !empty($chart_data['status']) && !empty($chart_data['category'])): ?>
            // Asset Status Chart
            const statusChartData = <?php echo $chart_data['status']; ?>;
            if (document.getElementById('statusChart')) {
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: statusChartData.map(item => item.label),
                        datasets: [{
                            data: statusChartData.map(item => item.value),
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545', '#007bff']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }

            // Asset Category Chart
            const categoryChartData = <?php echo $chart_data['category']; ?>;
            if (document.getElementById('categoryChart')) {
                const categoryCtx = document.getElementById('categoryChart').getContext('2d');
                new Chart(categoryCtx, {
                    type: 'bar',
                    data: {
                        labels: categoryChartData.map(item => item.label),
                        datasets: [{
                            data: categoryChartData.map(item => item.value),
                            backgroundColor: '#007bff'
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

        <?php if ($report_type == 'asset_acquisition' && !empty($chart_data['acquisition'])): ?>
            // Asset Acquisition Chart
            const acquisitionChartData = <?php echo $chart_data['acquisition']; ?>;
            if (document.getElementById('acquisitionChart')) {
                const acquisitionCtx = document.getElementById('acquisitionChart').getContext('2d');
                new Chart(acquisitionCtx, {
                    type: 'line',
                    data: {
                        labels: acquisitionChartData.map(item => item.label),
                        datasets: [{
                            label: 'Assets Acquired',
                            data: acquisitionChartData.map(item => item.value),
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.2)',
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        <?php endif; ?>

        <?php if ($report_type == 'value_report' && !empty($chart_data['value'])): ?>
            // Asset Value Report Chart
            const valueChartData = <?php echo $chart_data['value']; ?>;
            if (document.getElementById('valueChart')) {
                const valueCtx = document.getElementById('valueChart').getContext('2d');
                new Chart(valueCtx, {
                    type: 'doughnut',
                    data: {
                        labels: valueChartData.map(item => item.label),
                        datasets: [{
                            data: valueChartData.map(item => item.value),
                            backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true
                    }
                });
            }
        <?php endif; ?>
    });
</script>
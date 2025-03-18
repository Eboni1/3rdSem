<?php
session_start();
include "../connect.php"; // Include database connection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

// Initialize variables
$report_type = isset($_GET['type']) ? $_GET['type'] : 'asset_summary';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // First day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Today
$category = isset($_GET['category']) ? $_GET['category'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$export_format = isset($_GET['export']) ? $_GET['export'] : '';

// Fetch asset categories for filter dropdown
$categories_query = "SELECT * FROM asset_categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $categories_query);
$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

// Generate report data based on selected report type
$report_data = [];
$chart_data = [];

switch ($report_type) {
    case 'asset_summary':
        // Get total count of assets
        $total_query = "SELECT COUNT(*) as total FROM assets";
        $total_result = mysqli_query($conn, $total_query);
        $total_row = mysqli_fetch_assoc($total_result);
        $total_assets = $total_row['total'];
        
        // Get count by status
        $status_query = "SELECT status, COUNT(*) as count FROM assets GROUP BY status";
        $status_result = mysqli_query($conn, $status_query);
        $status_data = mysqli_fetch_all($status_result, MYSQLI_ASSOC);
        
        // Get count by category
        $category_query = "SELECT category, COUNT(*) as count FROM assets GROUP BY category ORDER BY count DESC";
        $category_result = mysqli_query($conn, $category_query);
        $category_data = mysqli_fetch_all($category_result, MYSQLI_ASSOC);
        
        // Prepare chart data for status
        $status_chart = [];
        foreach ($status_data as $item) {
            $status_chart[] = [
                'label' => $item['status'],
                'value' => $item['count']
            ];
        }
        $chart_data['status'] = json_encode($status_chart);
        
        // Prepare chart data for categories
        $category_chart = [];
        foreach ($category_data as $item) {
            $category_chart[] = [
                'label' => $item['category'],
                'value' => $item['count']
            ];
        }
        $chart_data['category'] = json_encode($category_chart);
        
        $report_data = [
            'total' => $total_assets,
            'status' => $status_data,
            'category' => $category_data
        ];
        break;
        
    case 'asset_acquisition':
        // Build query with filters
        $query = "SELECT DATE_FORMAT(date_acquired, '%Y-%m') as month, COUNT(*) as count 
                 FROM assets 
                 WHERE date_acquired BETWEEN '$start_date' AND '$end_date'";
        
        if (!empty($category)) {
            $query .= " AND category = '$category'";
        }
        
        if (!empty($status)) {
            $query .= " AND status = '$status'";
        }
        
        $query .= " GROUP BY DATE_FORMAT(date_acquired, '%Y-%m') 
                   ORDER BY DATE_FORMAT(date_acquired, '%Y-%m')";
        
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        // Prepare chart data
        $acquisition_chart = [];
        foreach ($report_data as $item) {
            $month_name = date('M Y', strtotime($item['month'] . '-01'));
            $acquisition_chart[] = [
                'label' => $month_name,
                'value' => $item['count']
            ];
        }
        $chart_data['acquisition'] = json_encode($acquisition_chart);
        break;
        
    case 'asset_status':
        // Build query with filters
        $query = "SELECT a.*, c.category_name 
                 FROM assets a
                 LEFT JOIN asset_categories c ON a.category = c.category_name
                 WHERE 1=1";
        
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND a.date_acquired BETWEEN '$start_date' AND '$end_date'";
        }
        
        if (!empty($category)) {
            $query .= " AND a.category = '$category'";
        }
        
        if (!empty($status)) {
            $query .= " AND a.status = '$status'";
        }
        
        $query .= " ORDER BY a.id DESC";
        
        $result = mysqli_query($conn, $query);
        $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        break;
        
    case 'maintenance_history':
        // For this example, we'll assume there's a maintenance_logs table
        // If it doesn't exist, you would need to create it
        
        // Check if maintenance_logs table exists
        $table_check = mysqli_query($conn, "SHOW TABLES LIKE 'maintenance_logs'");
        
        if (mysqli_num_rows($table_check) == 0) {
            // Create maintenance_logs table if it doesn't exist
            $create_table_query = "CREATE TABLE IF NOT EXISTS `maintenance_logs` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `asset_id` int(11) NOT NULL,
                `maintenance_date` date NOT NULL,
                `description` text NOT NULL,
                `cost` decimal(10,2) DEFAULT NULL,
                `performed_by` varchar(100) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `asset_id` (`asset_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            
            mysqli_query($conn, $create_table_query);
        }
        
        // Build query with filters
        $query = "SELECT m.*, a.asset_name, a.category 
                 FROM maintenance_logs m
                 JOIN assets a ON m.asset_id = a.id
                 WHERE 1=1";
        
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND m.maintenance_date BETWEEN '$start_date' AND '$end_date'";
        }
        
        if (!empty($category)) {
            $query .= " AND a.category = '$category'";
        }
        
        $query .= " ORDER BY m.maintenance_date DESC";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $report_data = [];
        }
        break;
        
    case 'value_report':
        // For this example, we'll assume there's an asset_value field in the assets table
        // If it doesn't exist, you would need to add it
        
        // Check if asset_value column exists
        $column_check = mysqli_query($conn, "SHOW COLUMNS FROM assets LIKE 'asset_value'");
        
        if (mysqli_num_rows($column_check) == 0) {
            // Add asset_value column if it doesn't exist
            mysqli_query($conn, "ALTER TABLE assets ADD COLUMN asset_value DECIMAL(10,2) DEFAULT NULL");
        }
        
        // Build query with filters
        $query = "SELECT category, SUM(asset_value) as total_value, COUNT(*) as asset_count 
                 FROM assets 
                 WHERE 1=1";
        
        if (!empty($start_date) && !empty($end_date)) {
            $query .= " AND date_acquired BETWEEN '$start_date' AND '$end_date'";
        }
        
        if (!empty($category)) {
            $query .= " AND category = '$category'";
        }
        
        if (!empty($status)) {
            $query .= " AND status = '$status'";
        }
        
        $query .= " GROUP BY category ORDER BY total_value DESC";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $report_data = mysqli_fetch_all($result, MYSQLI_ASSOC);
            
            // Prepare chart data
            $value_chart = [];
            foreach ($report_data as $item) {
                $value_chart[] = [
                    'label' => $item['category'],
                    'value' => $item['total_value'] ? $item['total_value'] : 0
                ];
            }
            $chart_data['value'] = json_encode($value_chart);
        } else {
            $report_data = [];
        }
        break;
}

// Handle export functionality
if (!empty($export_format)) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="asset_report_' . $report_type . '_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers based on report type
    switch ($report_type) {
        case 'asset_summary':
            fputcsv($output, ['Category', 'Asset Count']);
            foreach ($report_data['category'] as $row) {
                fputcsv($output, [$row['category'], $row['count']]);
            }
            break;
            
        case 'asset_acquisition':
            fputcsv($output, ['Month', 'Assets Acquired']);
            foreach ($report_data as $row) {
                $month_name = date('M Y', strtotime($row['month'] . '-01'));
                fputcsv($output, [$month_name, $row['count']]);
            }
            break;
            
        case 'asset_status':
            fputcsv($output, ['ID', 'Asset Name', 'Category', 'Status', 'Date Acquired', 'Description']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['id'], 
                    $row['asset_name'], 
                    $row['category'], 
                    $row['status'], 
                    $row['date_acquired'], 
                    $row['description']
                ]);
            }
            break;
            
        case 'maintenance_history':
            fputcsv($output, ['Asset ID', 'Asset Name', 'Category', 'Maintenance Date', 'Description', 'Cost', 'Performed By']);
            foreach ($report_data as $row) {
                fputcsv($output, [
                    $row['asset_id'], 
                    $row['asset_name'], 
                    $row['category'], 
                    $row['maintenance_date'], 
                    $row['description'], 
                    $row['cost'], 
                    $row['performed_by']
                ]);
            }
            break;
            
        case 'value_report':
            fputcsv($output, ['Category', 'Total Value', 'Asset Count', 'Average Value']);
            foreach ($report_data as $row) {
                $avg_value = $row['asset_count'] > 0 ? $row['total_value'] / $row['asset_count'] : 0;
                fputcsv($output, [
                    $row['category'], 
                    $row['total_value'] ? $row['total_value'] : 0, 
                    $row['asset_count'], 
                    number_format($avg_value, 2)
                ]);
            }
            break;
    }
    
    fclose($output);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Inventory Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h4>Inventory Management</h4>
            </div>
            <ul class="sidebar-menu">
                <li><a href="super_admin_dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                <li><a href="user_management.php"><i class="bi bi-people"></i> User Management</a></li>
                <li><a href="inventory.php"><i class="bi bi-box-seam"></i> Inventory</a></li>
                <li><a href="#"><i class="bi bi-cart3"></i> Orders</a></li>
                <li class="active"><a href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
                <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
                <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="content-title">Reports & Analytics</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-download me-1"></i> Export Report
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="reports.php?type=<?php echo $report_type; ?>&start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>&category=<?php echo $category; ?>&status=<?php echo $status; ?>&export=csv">Export as CSV</a></li>
                                    <li><a class="dropdown-item" href="#" id="printReport"><i class="fas fa-print me-1"></i> Print Report</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Report Type Selection Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card report-card <?php echo $report_type == 'asset_summary' ? 'active' : ''; ?>" onclick="window.location.href='reports.php?type=asset_summary'">
                                <div class="card-body text-center">
                                    <div class="report-icon text-primary">
                                        <i class="fas fa-chart-pie"></i>
                                    </div>
                                    <h5 class="card-title">Asset Summary</h5>
                                    <p class="card-text small text-muted">Overview of all assets by category and status</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card report-card <?php echo $report_type == 'asset_acquisition' ? 'active' : ''; ?>" onclick="window.location.href='reports.php?type=asset_acquisition'">
                                <div class="card-body text-center">
                                    <div class="report-icon text-success">
                                        <i class="fas fa-chart-line"></i>
                                    </div>
                                    <h5 class="card-title">Asset Acquisition</h5>
                                    <p class="card-text small text-muted">Track asset acquisitions over time</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card report-card <?php echo $report_type == 'asset_status' ? 'active' : ''; ?>" onclick="window.location.href='reports.php?type=asset_status'">
                                <div class="card-body text-center">
                                    <div class="report-icon text-info">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <h5 class="card-title">Asset Status</h5>
                                    <p class="card-text small text-muted">Detailed list of assets with filters</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card report-card <?php echo $report_type == 'value_report' ? 'active' : ''; ?>" onclick="window.location.href='reports.php?type=value_report'">
                                <div class="card-body text-center">
                                    <div class="report-icon text-warning">
                                        <i class="fas fa-peso-sign"></i>
                                    </div>
                                    <h5 class="card-title">Value Report</h5>
                                    <p class="card-text small text-muted">Asset valuation by category</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Section -->
                    <?php if ($report_type != 'asset_summary'): ?>
                    <div class="filter-section mb-4">
                        <form method="get" class="row g-3 align-items-end">
                            <input type="hidden" name="type" value="<?php echo $report_type; ?>">
                            
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            
                            <div class="col-md-2">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['category_name']; ?>" <?php echo $category == $cat['category_name'] ? 'selected' : ''; ?>>
                                            <?php echo $cat['category_name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <?php if ($report_type != 'maintenance_history'): ?>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Available" <?php echo $status == 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="In Use" <?php echo $status == 'In Use' ? 'selected' : ''; ?>>In Use</option>
                                    <option value="Maintenance" <?php echo $status == 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Retired" <?php echo $status == 'Retired' ? 'selected' : ''; ?>>Retired</option>
                                </select>
                            </div>
                            <?php endif; ?>
                            
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                    <!-- Report Content -->
                    <div class="card" id="reportContent">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <?php
                                switch ($report_type) {
                                    case 'asset_summary':
                                        echo 'Asset Summary Report';
                                        break;
                                    case 'asset_acquisition':
                                        echo 'Asset Acquisition Report';
                                        break;
                                    case 'asset_status':
                                        echo 'Asset Status Report';
                                        break;
                                    case 'maintenance_history':
                                        echo 'Maintenance History Report';
                                        break;
                                    case 'value_report':
                                        echo 'Asset Value Report';
                                        break;
                                }
                                ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if ($report_type == 'asset_summary'): ?>
                                <!-- Asset Summary Report -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="text-muted">Total Assets</h6>
                                                <h2 class="mb-0"><?php echo $report_data['total']; ?></h2>
                                            </div>
                                        </div>
                                    </div>
                                    <?php foreach ($report_data['status'] as $status): ?>
                                        <div class="col-md-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center">
                                                    <h6 class="text-muted"><?php echo $status['status']; ?></h6>
                                                    <h2 class="mb-0"><?php echo $status['count']; ?></h2>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5>Assets by Status</h5>
                                        <div class="chart-container">
                                            <canvas id="statusChart"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5>Assets by Category</h5>
                                        <div class="chart-container">
                                            <canvas id="categoryChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <h5>Category Breakdown</h5>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Category</th>
                                                    <th>Asset Count</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data['category'] as $category): ?>
                                                    <tr>
                                                        <td><?php echo $category['category']; ?></td>
                                                        <td><?php echo $category['count']; ?></td>
                                                        <td>
                                                            <?php 
                                                                $percentage = ($category['count'] / $report_data['total']) * 100;
                                                                echo number_format($percentage, 1) . '%';
                                                            ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                            <?php elseif ($report_type == 'asset_acquisition'): ?>
                                <!-- Asset Acquisition Report -->
                                <div class="chart-container mb-4">
                                    <canvas id="acquisitionChart"></canvas>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Month</th>
                                                <th>Assets Acquired</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $total_acquired = 0;
                                            foreach ($report_data as $row): 
                                                $total_acquired += $row['count'];
                                                $month_name = date('F Y', strtotime($row['month'] . '-01'));
                                            ?>
                                                <tr>
                                                    <td><?php echo $month_name; ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-primary">
                                                <td><strong>Total</strong></td>
                                                <td><strong><?php echo $total_acquired; ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                            <?php elseif ($report_type == 'asset_status'): ?>
                                <!-- Asset Status Report -->
                                <div class="table-responsive">
                                    <table id="assetStatusTable" class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Asset Name</th>
                                                <th>Category</th>
                                                <th>Status</th>
                                                <th>Date Acquired</th>
                                                <th>Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($report_data as $asset): ?>
                                                <tr>
                                                    <td><?php echo $asset['id']; ?></td>
                                                    <td><?php echo $asset['asset_name']; ?></td>
                                                    <td><?php echo $asset['category']; ?></td>
                                                    <td>
                                                        <?php 
                                                            $status_class = '';
                                                            switch($asset['status']) {
                                                                case 'Available':
                                                                    $status_class = 'status-available';
                                                                    break;
                                                                case 'In Use':
                                                                    $status_class = 'status-in-use';
                                                                    break;
                                                                case 'Maintenance':
                                                                    $status_class = 'status-maintenance';
                                                                    break;
                                                                case 'Retired':
                                                                    $status_class = 'status-retired';
                                                                    break;
                                                            }
                                                            echo '<span class="status-badge '.$status_class.'">'.$asset['status'].'</span>';
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($asset['date_acquired'])); ?></td>
                                                    <td><?php echo $asset['description']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                            <?php elseif ($report_type == 'maintenance_history'): ?>
                                <!-- Maintenance History Report -->
                                <?php if (empty($report_data)): ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No maintenance records found for the selected filters. You can add maintenance records from the asset detail page.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table id="maintenanceTable" class="table table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Asset</th>
                                                    <th>Category</th>
                                                    <th>Maintenance Date</th>
                                                    <th>Description</th>
                                                    <th>Cost</th>
                                                    <th>Performed By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($report_data as $record): ?>
                                                    <tr>
                                                        <td><?php echo $record['asset_name']; ?></td>
                                                        <td><?php echo $record['category']; ?></td>
                                                        <td><?php echo date('M d, Y', strtotime($record['maintenance_date'])); ?></td>
                                                        <td><?php echo $record['description']; ?></td>
                                                        <td>$<?php echo number_format($record['cost'], 2); ?></td>
                                                        <td><?php echo $record['performed_by']; ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr class="table-primary">
                                                    <td colspan="4"><strong>Total Maintenance Cost</strong></td>
                                                    <td colspan="2"><strong>$<?php 
                                                        $total_cost = array_sum(array_column($report_data, 'cost'));
                                                        echo number_format($total_cost, 2); 
                                                    ?></strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                <?php endif; ?>
                                
                            <?php elseif ($report_type == 'value_report'): ?>
                                <!-- Asset Value Report -->
                                <div class="chart-container mb-4">
                                    <canvas id="valueChart"></canvas>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Total Value</th>
                                                <th>Asset Count</th>
                                                <th>Average Value</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $grand_total = 0;
                                            $total_assets = 0;
                                            
                                            foreach ($report_data as $row): 
                                                $grand_total += $row['total_value'] ? $row['total_value'] : 0;
                                                $total_assets += $row['asset_count'];
                                                $avg_value = $row['asset_count'] > 0 ? $row['total_value'] / $row['asset_count'] : 0;
                                            ?>
                                                <tr>
                                                    <td><?php echo $row['category']; ?></td>
                                                    <td>₱<?php echo number_format($row['total_value'] ? $row['total_value'] : 0, 2); ?></td>
                                                    <td><?php echo $row['asset_count']; ?></td>
                                                    <td>₱<?php echo number_format($avg_value, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="table-primary">
                                                <td><strong>Total</strong></td>
                                                <td><strong>₱<?php echo number_format($grand_total, 2); ?></strong></td>
                                                <td><strong><?php echo $total_assets; ?></strong></td>
                                                <td><strong>₱<?php 
                                                    $overall_avg = $total_assets > 0 ? $grand_total / $total_assets : 0;
                                                    echo number_format($overall_avg, 2); 
                                                ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
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
                    options: { responsive: true }
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
                            y: { beginAtZero: true }
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
                            y: { beginAtZero: true }
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
                    options: { responsive: true }
                });
            }
        <?php endif; ?>
    });
    </script>

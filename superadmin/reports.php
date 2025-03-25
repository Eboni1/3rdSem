<?php
session_start();
include "../connect.php"; // Include database connection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

include "includes/reports_engine.php";
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
        <?php include "includes/sidebar.php"; ?>

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

<?php include "includes/reports_script.php";

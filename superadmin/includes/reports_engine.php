<?php
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
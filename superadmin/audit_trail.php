<?php
require_once "../connect.php"; // Database connection
function log_activity($conn, $action, $module) {
    if (!isset($_SESSION['user_id'])) {
        return false; // Can't log if no user is logged in
    }
    
    $user_id = $_SESSION['user_id'];
    $username = $_SESSION['username'];
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $stmt = $conn->prepare("INSERT INTO audit_trail (user_id, username, action, module, ip_address, user_agent) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $username, $action, $module, $ip_address, $user_agent);
    $result = $stmt->execute();
    $stmt->close();
    
    return $result;
}
?>

<?php
include "../connect.php";
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php");
    exit;
}

// Pagination
$records_per_page = 20;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// Filtering
$filter_user = isset($_GET['user']) ? $_GET['user'] : '';
$filter_module = isset($_GET['module']) ? $_GET['module'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';

// Build the query
$query = "SELECT * FROM audit_trail WHERE 1=1";
$count_query = "SELECT COUNT(*) as total FROM audit_trail WHERE 1=1";
$filter_params = [];
$filter_types = "";

if (!empty($filter_user)) {
    $query .= " AND (user_id = ? OR username LIKE ?)";
    $count_query .= " AND (user_id = ? OR username LIKE ?)";
    $filter_params[] = $filter_user;
    $filter_params[] = "%$filter_user%";
    $filter_types .= "is";
}

if (!empty($filter_module)) {
    $query .= " AND module = ?";
    $count_query .= " AND module = ?";
    $filter_params[] = $filter_module;
    $filter_types .= "s";
}

if (!empty($filter_date)) {
    $query .= " AND DATE(created_at) = ?";
    $count_query .= " AND DATE(created_at) = ?";
    $filter_params[] = $filter_date;
    $filter_types .= "s";
}

// Get total records for pagination (without LIMIT)
$count_stmt = $conn->prepare($count_query);
if (!empty($filter_types)) {
    $count_stmt->bind_param($filter_types, ...$filter_params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_records = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_records / $records_per_page);
$count_stmt->close();

// Add order and limit to the main query
$query .= " ORDER BY created_at DESC LIMIT ?, ?";

// Get audit trail records
$stmt = $conn->prepare($query);
if (!empty($filter_types)) {
    // Combine filter parameters with pagination parameters
    $all_params = array_merge($filter_params, [$offset, $records_per_page]);
    $all_types = $filter_types . "ii";
    $stmt->bind_param($all_types, ...$all_params);
} else {
    // Only pagination parameters
    $stmt->bind_param("ii", $offset, $records_per_page);
}
$stmt->execute();
$result = $stmt->get_result();
$audit_records = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique modules for filter dropdown
$modules_query = "SELECT DISTINCT module FROM audit_trail ORDER BY module";
$modules_result = $conn->query($modules_query);
$modules = [];
while ($row = $modules_result->fetch_assoc()) {
    $modules[] = $row['module'];
}

// Get users for filter dropdown
$users_query = "SELECT DISTINCT username FROM audit_trail ORDER BY username";
$users_result = $conn->query($users_query);
$users = [];
while ($row = $users_result->fetch_assoc()) {
    $users[] = $row['username'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Trail - Super Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
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
            <li><a href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
            <li><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
            <li class="active"><a href="audit_trail.php"><i class="bi bi-journal-text"></i> Audit Trail</a></li>
            <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        

        
    </div>

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
    </script>
</body>
</html>

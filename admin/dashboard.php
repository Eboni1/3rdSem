<?php
// admin/dashboard.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../connect.php'; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header('Location: ../login.php');
    exit;
}

// Include database connection
require_once '../connect.php';

// Fetch some statistics for the dashboard
try {
    $db = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get total users count
    $stmt = $db->query("SELECT COUNT(*) as total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
    
    // Get new users in last 7 days
    $stmt = $db->query("SELECT COUNT(*) as new_users FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $newUsers = $stmt->fetch(PDO::FETCH_ASSOC)['new_users'];
    
    // Get total number of RIS reports
    $stmt = $db->query("SELECT COUNT(*) as total_ris FROM reports WHERE type = 'RIS'");
    $totalRIS = $stmt->fetch(PDO::FETCH_ASSOC)['total_ris'];
    
    // Get total number of memorandum reports
    $stmt = $db->query("SELECT COUNT(*) as total_memo FROM reports WHERE type = 'Memorandum'");
    $totalMemo = $stmt->fetch(PDO::FETCH_ASSOC)['total_memo'];
    
    // Get recent report activities
    $stmt = $db->query("SELECT * FROM activity_logs WHERE action_type = 'report' ORDER BY created_at DESC LIMIT 5");
    $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle database errors
    $error = "Database error: " . $e->getMessage();
    // Set default values in case of error
    $totalUsers = 0;
    $newUsers = 0;
    $totalRIS = 0;
    $totalMemo = 0;
    $recentActivities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Report Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <div class="text-center mb-4">
                    <h4 class="text-white">Report Management</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users"></i> User Management
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ris_reports.php">
                            <i class="fas fa-file-alt"></i> RIS Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="memo_reports.php">
                            <i class="fas fa-clipboard"></i> Memorandum Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="generate_report.php">
                            <i class="fas fa-plus-circle"></i> Generate New Report
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Top Navigation Bar -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Report Management Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-dashboard card-primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Users</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalUsers); ?></div>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-circle bg-primary text-white">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-dashboard card-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        New Users (7 days)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($newUsers); ?></div>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-circle bg-success text-white">
                                        <i class="fas fa-user-plus"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-dashboard card-info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        RIS Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalRIS); ?></div>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-circle bg-info text-white">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card card-dashboard card-warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Memorandum Reports</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($totalMemo); ?></div>
                                </div>
                                <div class="col-auto">
                                    <div class="icon-circle bg-warning text-white">
                                        <i class="fas fa-clipboard"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Recent Report Activities -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Recent Report Activities</h6>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recentActivities)): ?>
                                <p class="text-center text-muted">No recent report activities found.</p>
                            <?php else: ?>
                                <?php foreach ($recentActivities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong><?php echo htmlspecialchars($activity['user_name'] ?? 'System'); ?></strong>
                                                <?php echo htmlspecialchars($activity['action']); ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php 
                                                    $date = new DateTime($activity['created_at']);
                                                    echo $date->format('M j, g:i a'); 
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <a href="users.php?action=add" class="btn btn-primary btn-block w-100">
                                        <i class="fas fa-user-plus me-2"></i> Add New User
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="generate_report.php?type=ris" class="btn btn-success btn-block w-100">
                                        <i class="fas fa-file-alt me-2"></i> Create RIS Report
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="generate_report.php?type=memo" class="btn btn-info btn-block w-100">
                                        <i class="fas fa-clipboard me-2"></i> Create Memorandum
                                    </a>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <a href="report_archive.php" class="btn btn-secondary btn-block w-100">
                                        <i class="fas fa-archive me-2"></i> Report Archive
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Info -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <strong>Current Date:</strong> <?php echo date('F j, Y'); ?>
                            </div>
                            <div class="mb-2">
                                <strong>Reports Generated Today:</strong> 
                                <?php 
                                    try {
                                        $stmt = $db->query("SELECT COUNT(*) as today_reports FROM reports WHERE DATE(created_at) = CURDATE()");
                                        echo $stmt->fetch(PDO::FETCH_ASSOC)['today_reports'];
                                    } catch (PDOException $e) {
                                        echo "0";
                                    }
                                ?>
                            </div>
                            <div>
                                <strong>Last Login:</strong> 
                                <?php 
                                    echo isset($_SESSION['last_login']) ? date('M j, Y g:i a', strtotime($_SESSION['last_login'])) : 'N/A'; 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

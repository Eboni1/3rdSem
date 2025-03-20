<?php
session_start();
include "../connect.php"; // Include database connection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

// Get total users count
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = mysqli_query($conn, $users_query);
$users_data = mysqli_fetch_assoc($users_result);
$total_users = $users_data['total_users'];

// Get inventory statistics
$total_items_query = "SELECT COUNT(*) as total FROM assets";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items_data = mysqli_fetch_assoc($total_items_result);
$total_items = $total_items_data['total'];

// Get available items
$available_query = "SELECT COUNT(*) as available FROM assets WHERE status = 'Available'";
$available_result = mysqli_query($conn, $available_query);
$available_data = mysqli_fetch_assoc($available_result);
$available_items = $available_data['available'];

// Get items in use
$in_use_query = "SELECT COUNT(*) as in_use FROM assets WHERE status = 'In Use'";
$in_use_result = mysqli_query($conn, $in_use_query);
$in_use_data = mysqli_fetch_assoc($in_use_result);
$in_use_items = $in_use_data['in_use'];

// Get maintenance items
$maintenance_query = "SELECT COUNT(*) as maintenance FROM assets WHERE status = 'Maintenance'";
$maintenance_result = mysqli_query($conn, $maintenance_query);
$maintenance_data = mysqli_fetch_assoc($maintenance_result);
$maintenance_items = $maintenance_data['maintenance'];

// Get recent users
$recent_users_query = "SELECT id, username, role, status FROM users ORDER BY id DESC LIMIT 3";
$recent_users_result = mysqli_query($conn, $recent_users_query);
$recent_users = mysqli_fetch_all($recent_users_result, MYSQLI_ASSOC);

// Get recent assets
$recent_assets_query = "SELECT id, asset_name, category, status, date_acquired FROM assets ORDER BY id DESC LIMIT 5";
$recent_assets_result = mysqli_query($conn, $recent_assets_query);
$recent_assets = mysqli_fetch_all($recent_assets_result, MYSQLI_ASSOC);

// Get category statistics
$categories_query = "SELECT category, COUNT(*) as count FROM assets GROUP BY category ORDER BY count DESC LIMIT 5";
$categories_result = mysqli_query($conn, $categories_query);
$categories_stats = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Super Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .asset-status-available {
            background-color: #d1e7dd;
            color: #0f5132;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .asset-status-in-use {
            background-color: #cff4fc;
            color: #055160;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .asset-status-maintenance {
            background-color: #fff3cd;
            color: #664d03;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .asset-status-retired {
            background-color: #f8d7da;
            color: #842029;
            padding: 3px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .category-stat {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding: 8px 12px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        
        .category-stat:hover {
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4>Inventory Management</h4>
        </div>
        <ul class="sidebar-menu">
            <li class="active"><a href="#"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="user_management.php"><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="inventory.php"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <li><a href="#"><i class="bi bi-cart3"></i> Orders</a></li>
            <li><a href="reports.php"><i class="bi bi-graph-up"></i> Reports</a></li>
            <!-- <li><a href="audit_trail.php"><i class="bi bi-journal-text"></i> Audit Trail</a></li> -->
            <li><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar mb-4">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h5 class="mb-0">Super Admin Dashboard</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container-fluid px-0">
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="card-value"><?php echo $total_users; ?></div>
                        <div class="card-label">TOTAL USERS</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="card-value"><?php echo $total_items; ?></div>
                        <div class="card-label">INVENTORY ITEMS</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div class="card-value"><?php echo $available_items; ?></div>
                        <div class="card-label">AVAILABLE ITEMS</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-tools"></i>
                        </div>
                        <div class="card-value"><?php echo $maintenance_items; ?></div>
                        <div class="card-label">MAINTENANCE ITEMS</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Recent Inventory Items</h5>
                            <a href="inventory.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Asset Name</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date Acquired</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recent_assets)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No assets found</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recent_assets as $asset): ?>
                                                <tr>
                                                    <td><?php echo $asset['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($asset['asset_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($asset['category']); ?></td>
                                                    <td>
                                                        <?php 
                                                            $status_class = '';
                                                            switch($asset['status']) {
                                                                case 'Available':
                                                                    $status_class = 'asset-status-available';
                                                                    break;
                                                                case 'In Use':
                                                                    $status_class = 'asset-status-in-use';
                                                                    break;
                                                                case 'Maintenance':
                                                                    $status_class = 'asset-status-maintenance';
                                                                    break;
                                                                case 'Retired':
                                                                    $status_class = 'asset-status-retired';
                                                                    break;
                                                                default:
                                                                    $status_class = '';
                                                            }
                                                            echo '<span class="'.$status_class.'">'.$asset['status'].'</span>';
                                                        ?>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($asset['date_acquired'])); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pie-chart me-2"></i>Inventory by Category</h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($categories_stats)): ?>
                                <p class="text-center">No categories found</p>
                            <?php else: ?>
                                <?php foreach ($categories_stats as $category): ?>
                                    <div class="category-stat">
                                        <span><?php echo htmlspecialchars($category['category']); ?></span>
                                        <span class="badge bg-primary"><?php echo $category['count']; ?> items</span>
                                    </div>
                                <?php endforeach; ?>
                                <div class="text-center mt-3">
                                    <a href="inventory.php" class="btn btn-sm btn-outline-primary">Manage Categories</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Inventory Status</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between">
                                    <span>Available</span>
                                    <span><?php echo $available_items; ?> items</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: <?php echo ($total_items > 0) ? ($available_items / $total_items * 100) : 0; ?>%" 
                                         aria-valuenow="<?php echo $available_items; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_items; ?>"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between">
                                    <span>In Use</span>
                                    <span><?php echo $in_use_items; ?> items</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-info" role="progressbar" 
                                         style="width: <?php echo ($total_items > 0) ? ($in_use_items / $total_items * 100) : 0; ?>%" 
                                         aria-valuenow="<?php echo $in_use_items; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_items; ?>"></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label d-flex justify-content-between">
                                    <span>Maintenance</span>
                                    <span><?php echo $maintenance_items; ?> items</span>
                                </label>
                                <div class="progress">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?php echo ($total_items > 0) ? ($maintenance_items / $total_items * 100) : 0; ?>%" 
                                         aria-valuenow="<?php echo $maintenance_items; ?>" aria-valuemin="0" aria-valuemax="<?php echo $total_items; ?>"></div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <a href="inventory.php" class="btn btn-sm btn-primary">Manage Inventory</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-6 mb-4">
                    <div class="user-form">
                        <h4 class="mb-4"><i class="bi bi-person-plus me-2"></i>Register a New User</h4>
                        <form action="register_user.php" method="post">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield"></i></span>
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="" selected disabled>Select a role</option>
                                        <option value="admin">Admin</option>
                                        <option value="user">User</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Register User
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="user-form">
                        <h4 class="mb-4"><i class="bi bi-people me-2"></i>Recent Users</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recent_users)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center">No users found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recent_users as $user): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td>
                                                    <span class="badge <?php echo ($user['role'] == 'admin' || $user['role'] == 'super_admin') ? 'bg-primary' : 'bg-info'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo ($user['status'] == 'active') ? 'bg-success' : 'bg-warning'; ?>">
                                                        <?php echo ucfirst(htmlspecialchars($user['status'] ?? 'active')); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this user?')"><i class="bi bi-trash"></i></a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <a href="user_management.php" class="btn btn-outline-primary">View All Users</a>
                    </div>
                </div>
            </div>
        </div>
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

<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: index.html"); // Redirect if not Super Admin
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Super Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 60px;
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
        }
        
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background-color: var(--secondary-color);
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 20px;
            background-color: rgba(0,0,0,0.1);
        }
        
        .sidebar-menu {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            padding: 10px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-menu li a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
        }
        
        .sidebar-menu li a i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .sidebar-menu li:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .sidebar-menu li.active {
            background-color: var(--primary-color);
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }
        
        .topbar {
            height: var(--topbar-height);
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: sticky;
            top: 0;
            z-index: 900;
        }
        
        .toggle-sidebar {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        
        .card-icon {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .card-value {
            font-size: 1.8rem;
            font-weight: bold;
        }
        
        .card-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .user-form {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 25px;
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
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
            <li><a href="#"><i class="bi bi-people"></i> User Management</a></li>
            <li><a href="#"><i class="bi bi-box-seam"></i> Inventory</a></li>
            <li><a href="#"><i class="bi bi-cart3"></i> Orders</a></li>
            <li><a href="#"><i class="bi bi-graph-up"></i> Reports</a></li>
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
            <li><a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
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
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
                        <div class="card-value">24</div>
                        <div class="card-label">TOTAL USERS</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="card-value">1,254</div>
                        <div class="card-label">INVENTORY ITEMS</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-cart-check"></i>
                        </div>
                        <div class="card-value">156</div>
                        <div class="card-label">ORDERS THIS MONTH</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="card-value">12</div>
                        <div class="card-label">LOW STOCK ALERTS</div>
                    </div>
                </div>
            </div>

            <div class="row">
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
                                    <tr>
                                        <td>john_doe</td>
                                        <td><span class="badge bg-primary">Admin</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>jane_smith</td>
                                        <td><span class="badge bg-info">User</span></td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>robert_johnson</td>
                                        <td><span class="badge bg-info">User</span></td>
                                        <td><span class="badge bg-warning">Inactive</span></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <a href="#" class="btn btn-outline-primary">View All Users</a>
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

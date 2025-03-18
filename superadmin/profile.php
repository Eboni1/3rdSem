<?php
session_start();
include "../connect.php"; // Include database connection

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

// Get user data
$user_id = $_SESSION["user_id"];
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$user_result = mysqli_stmt_get_result($stmt);
$user_data = mysqli_fetch_assoc($user_result);

// Handle profile update
$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];
    
    // Validate inputs
    if (empty($username) || empty($email)) {
        $error_message = "Username and email are required fields.";
    } else {
        // Check if username already exists (excluding current user)
        $check_query = "SELECT id FROM users WHERE username = ? AND id != ?";
        $check_stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($check_stmt, "si", $username, $user_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($check_result) > 0) {
            $error_message = "Username already exists. Please choose a different one.";
        } else {
            // If password change is requested
            if (!empty($current_password)) {
                // Verify current password
                if (password_verify($current_password, $user_data["password"])) {
                    // Check if new passwords match
                    if ($new_password === $confirm_password) {
                        if (strlen($new_password) < 6) {
                            $error_message = "New password must be at least 6 characters long.";
                        } else {
                            // Update profile with new password
                            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $update_query = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                            $update_stmt = mysqli_prepare($conn, $update_query);
                            mysqli_stmt_bind_param($update_stmt, "sssi", $username, $email, $hashed_password, $user_id);
                            
                            if (mysqli_stmt_execute($update_stmt)) {
                                $success_message = "Profile updated successfully with new password.";
                                $_SESSION["username"] = $username; // Update session
                                
                                // Refresh user data
                                $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
                                $user_data = mysqli_fetch_assoc($user_result);
                            } else {
                                $error_message = "Error updating profile: " . mysqli_error($conn);
                            }
                        }
                    } else {
                        $error_message = "New passwords do not match.";
                    }
                } else {
                    $error_message = "Current password is incorrect.";
                }
            } else {
                // Update profile without changing password
                $update_query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($update_stmt, "ssi", $username, $email, $user_id);
                
                if (mysqli_stmt_execute($update_stmt)) {
                    $success_message = "Profile updated successfully.";
                    $_SESSION["username"] = $username; // Update session
                    
                    // Refresh user data
                    $user_result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
                    $user_data = mysqli_fetch_assoc($user_result);
                } else {
                    $error_message = "Error updating profile: " . mysqli_error($conn);
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Super Admin</title>
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
            <li><a href="#"><i class="bi bi-gear"></i> Settings</a></li>
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
                <h5 class="mb-0">My Profile</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item active" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="container-fluid px-4">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="profile-avatar mb-3">
                                <i class="bi bi-person-circle" style="font-size: 5rem;"></i>
                            </div>
                            <h5><?php echo htmlspecialchars($user_data["username"]); ?></h5>
                            <p class="text-muted">Super Administrator</p>
                            <div class="d-flex justify-content-center">
                                <span class="badge bg-primary me-2">Super Admin</span>
                                <span class="badge bg-success">Active</span>
                            </div>
                            <hr>
                            <div class="text-start">
                                <p><strong><i class="bi bi-envelope me-2"></i>Email:</strong> <?php echo htmlspecialchars($user_data["email"] ?? "Not set"); ?></p>
                                <p><strong><i class="bi bi-calendar me-2"></i>Joined:</strong> <?php echo isset($user_data["created_at"]) ? date("F d, Y", strtotime($user_data["created_at"])) : "Not available"; ?></p>
                                <p><strong><i class="bi bi-shield-lock me-2"></i>Last Login:</strong> <?php echo isset($user_data["last_login"]) ? date("F d, Y H:i", strtotime($user_data["last_login"])) : "Not available"; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Edit Profile</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form action="profile.php" method="post">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data["username"]); ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data["email"] ?? ""); ?>" required>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                <h6 class="mb-3">Change Password</h6>
                                <p class="text-muted small mb-3">Leave these fields empty if you don't want to change your password.</p>
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="current_password" name="current_password">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                    <div class="form-text">Password must be at least 6 characters long.</div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="bi bi-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
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

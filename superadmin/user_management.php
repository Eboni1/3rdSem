<?php
session_start();
include "../connect.php"; // Include database connection
include "audit_trail.php"; // Include audit trail function

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

include "includes/user_management_engine.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Super Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Sidebar -->
    <?php include "includes/sidebar.php"; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar mb-4">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h5 class="mb-0">User Management</h5>
            </div>
            <div class="d-flex align-items-center">
                <span class="me-3"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="container-fluid px-0">
            <div class="row mb-4">
                <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="card-value"><?php echo $total_users; ?></div>
                        <div class="card-label">TOTAL USERS</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-person-check"></i>
                        </div>
                        <div class="card-value"><?php echo $active_users; ?></div>
                        <div class="card-label">ACTIVE USERS</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-person-dash"></i>
                        </div>
                        <div class="card-value"><?php echo $inactive_users; ?></div>
                        <div class="card-label">INACTIVE USERS</div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6 mb-3 mb-md-0">
                    <div class="dashboard-card text-center">
                        <div class="card-icon">
                            <i class="bi bi-building"></i>
                        </div>
                        <div class="card-value"><?php echo isset($total_offices) ? $total_offices : 0; ?></div>
                        <div class="card-label">TOTAL OFFICES</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-4">
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

                            <div class="mb-3">
                                <label for="office_id" class="form-label">Office</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-building"></i></span>
                                    <select class="form-select" id="office_id" name="office_id" required>
                                        <option value="" selected disabled>Select an office</option>
                                        <?php foreach ($offices as $office): ?>
                                            <option value="<?php echo $office['id']; ?>"><?php echo htmlspecialchars($office['office_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-plus me-2"></i>Register User
                            </button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="user-form">
                        <h4 class="mb-4"><i class="bi bi-people me-2"></i>All Users</h4>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Office</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <?php if ($user['role'] == 'super_admin'): ?>
                                                    <span class="badge bg-danger">Super Admin</span>
                                                <?php elseif ($user['role'] == 'admin'): ?>
                                                    <span class="badge bg-primary">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">User</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $office_name = "Unassigned"; // Default value
                                                foreach ($offices as $office) {
                                                    if ($office['id'] == $user['office_id']) {
                                                        $office_name = htmlspecialchars($office['office_name']);
                                                        break;
                                                    }
                                                }
                                                echo $office_name;
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($user['status'] == 'active'): ?>
                                                    <span class="badge bg-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <?php if ($user['role'] != 'super_admin'): ?>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <input type="hidden" name="new_status" value="<?php echo $user['status'] == 'active' ? 'inactive' : 'active'; ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-sm <?php echo $user['status'] == 'active' ? 'btn-outline-warning' : 'btn-outline-success'; ?>">
                                                            <i class="bi <?php echo $user['status'] == 'active' ? 'bi-person-dash' : 'bi-person-check'; ?>"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <?php if (count($users) == 0): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No users found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/user_management_script.php"; ?>
</body>

</html>
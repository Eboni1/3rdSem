<?php
session_start();
include "../connect.php"; // Include database connection
include "audit_trail.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

include "includes/settings_engine.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings - Super Admin</title>
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
                <h5 class="mb-0">Audit Trail</h5>
            </div>
            <div class="d-flex align-items-center">
                <!-- <span class="me-3"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
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
                </div> -->
            </div>
        </div>

        <!-- Audit Trail Content -->
        <div class="container-fluid px-0">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>System Activity Log</h5>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <form method="get" class="row g-3 mb-4">
                        <div class="col-md-3">
                            <label for="user" class="form-label">User</label>
                            <select class="form-select" id="user" name="user">
                                <option value="">All Users</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo htmlspecialchars($user); ?>" <?php echo ($filter_user == $user) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($user); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="module" class="form-label">Module</label>
                            <select class="form-select" id="module" name="module">
                                <option value="">All Modules</option>
                                <?php foreach ($modules as $module): ?>
                                    <option value="<?php echo htmlspecialchars($module); ?>" <?php echo ($filter_module == $module) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($module); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo $filter_date; ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-filter me-2"></i>Filter
                            </button>
                            <a href="audit_trail.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-2"></i>Clear
                            </a>
                        </div>
                    </form>

                    <!-- Audit Trail Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>User</th>
                                    <th>Module</th>
                                    <th>Action</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($audit_records)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No audit records found</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($audit_records as $record): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y h:i:s A', strtotime($record['created_at'])); ?></td>
                                            <td><?php echo htmlspecialchars($record['username']); ?></td>
                                            <td><?php echo htmlspecialchars($record['module']); ?></td>
                                            <td><?php echo htmlspecialchars($record['action']); ?></td>
                                            <td><?php echo htmlspecialchars($record['ip_address']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Audit trail pagination">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?>&user=<?php echo urlencode($filter_user); ?>&module=<?php echo urlencode($filter_module); ?>&date=<?php echo urlencode($filter_date); ?>">Previous</a>
                                </li>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&user=<?php echo urlencode($filter_user); ?>&module=<?php echo urlencode($filter_module); ?>&date=<?php echo urlencode($filter_date); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?>&user=<?php echo urlencode($filter_user); ?>&module=<?php echo urlencode($filter_module); ?>&date=<?php echo urlencode($filter_date); ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <!-- Top Bar -->
        <div class="topbar mb-4">
            <button class="toggle-sidebar" id="toggleSidebar">
                <i class="bi bi-list"></i>
            </button>
            <div>
                <h5 class="mb-0">System Settings</h5>
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
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Settings Content -->
        <div class="container-fluid px-0">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-gear-fill me-2"></i>Settings Menu</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush" id="settings-tabs" role="tablist">
                                <a class="list-group-item list-group-item-action active" id="general-tab" data-bs-toggle="list" href="#general" role="tab" aria-controls="general">
                                    <i class="bi bi-sliders me-2"></i> General Settings
                                </a>
                                <a class="list-group-item list-group-item-action" id="email-tab" data-bs-toggle="list" href="#email" role="tab" aria-controls="email">
                                    <i class="bi bi-envelope me-2"></i> Email Settings
                                </a>
                                <a class="list-group-item list-group-item-action" id="appearance-tab" data-bs-toggle="list" href="#appearance" role="tab" aria-controls="appearance">
                                    <i class="bi bi-palette me-2"></i> Appearance
                                </a>
                                <a class="list-group-item list-group-item-action" id="advanced-tab" data-bs-toggle="list" href="#advanced" role="tab" aria-controls="advanced">
                                    <i class="bi bi-code-square me-2"></i> Advanced
                                </a>
                                <a class="list-group-item list-group-item-action" id="backup-tab" data-bs-toggle="list" href="#backup" role="tab" aria-controls="backup">
                                    <i class="bi bi-cloud-download me-2"></i> Backup & Restore
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- General Settings -->
                                <div class="tab-pane fade show active" id="general" role="tabpanel" aria-labelledby="general-tab">
                                    <h4 class="mb-4">General Settings</h4>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="company_name" class="form-label">Company Name</label>
                                            <input type="text" class="form-control" id="company_name" name="company_name"
                                                value="<?php echo isset($settings['company_name']) ? htmlspecialchars($settings['company_name']['value']) : ''; ?>">
                                            <div class="form-text"><?php echo isset($settings['company_name']) ? htmlspecialchars($settings['company_name']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="items_per_page" class="form-label">Items Per Page</label>
                                            <input type="number" class="form-control" id="items_per_page" name="items_per_page"
                                                value="<?php echo isset($settings['items_per_page']) ? htmlspecialchars($settings['items_per_page']['value']) : '10'; ?>">
                                            <div class="form-text"><?php echo isset($settings['items_per_page']) ? htmlspecialchars($settings['items_per_page']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="default_currency" class="form-label">Default Currency</label>
                                            <select class="form-select" id="default_currency" name="default_currency">
                                                <option value="PHP" <?php echo (isset($settings['default_currency']) && $settings['default_currency']['value'] == 'PHP') ? 'selected' : ''; ?>>PHP (₱)</option>
                                                <option value="USD" <?php echo (isset($settings['default_currency']) && $settings['default_currency']['value'] == 'USD') ? 'selected' : ''; ?>>USD ($)</option>
                                                <option value="EUR" <?php echo (isset($settings['default_currency']) && $settings['default_currency']['value'] == 'EUR') ? 'selected' : ''; ?>>EUR (€)</option>
                                                <option value="GBP" <?php echo (isset($settings['default_currency']) && $settings['default_currency']['value'] == 'GBP') ? 'selected' : ''; ?>>GBP (£)</option>
                                            </select>
                                            <div class="form-text"><?php echo isset($settings['default_currency']) ? htmlspecialchars($settings['default_currency']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="date_format" class="form-label">Date Format</label>
                                            <select class="form-select" id="date_format" name="date_format">
                                                <option value="Y-m-d" <?php echo (isset($settings['date_format']) && $settings['date_format']['value'] == 'Y-m-d') ? 'selected' : ''; ?>>YYYY-MM-DD (2023-12-31)</option>
                                                <option value="m/d/Y" <?php echo (isset($settings['date_format']) && $settings['date_format']['value'] == 'm/d/Y') ? 'selected' : ''; ?>>MM/DD/YYYY (12/31/2023)</option>
                                                <option value="d/m/Y" <?php echo (isset($settings['date_format']) && $settings['date_format']['value'] == 'd/m/Y') ? 'selected' : ''; ?>>DD/MM/YYYY (31/12/2023)</option>
                                                <option value="d-M-Y" <?php echo (isset($settings['date_format']) && $settings['date_format']['value'] == 'd-M-Y') ? 'selected' : ''; ?>>DD-MMM-YYYY (31-Dec-2023)</option>
                                            </select>
                                            <div class="form-text"><?php echo isset($settings['date_format']) ? htmlspecialchars($settings['date_format']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="enable_user_registration" class="form-label">Enable User Registration</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_user_registration" name="enable_user_registration" value="true"
                                                    <?php echo (isset($settings['enable_user_registration']) && $settings['enable_user_registration']['value'] == 'true') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="enable_user_registration">Allow users to register accounts</label>
                                            </div>
                                            <div class="form-text"><?php echo isset($settings['enable_user_registration']) ? htmlspecialchars($settings['enable_user_registration']['description']) : ''; ?></div>
                                        </div>

                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Email Settings -->
                                <div class="tab-pane fade" id="email" role="tabpanel" aria-labelledby="email-tab">
                                    <h4 class="mb-4">Email Settings</h4>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="system_email" class="form-label">System Email</label>
                                            <input type="email" class="form-control" id="system_email" name="system_email"
                                                value="<?php echo isset($settings['system_email']) ? htmlspecialchars($settings['system_email']['value']) : ''; ?>">
                                            <div class="form-text"><?php echo isset($settings['system_email']) ? htmlspecialchars($settings['system_email']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="enable_email_notifications" class="form-label">Email Notifications</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="enable_email_notifications" name="enable_email_notifications" value="true"
                                                    <?php echo (isset($settings['enable_email_notifications']) && $settings['enable_email_notifications']['value'] == 'true') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="enable_email_notifications">Enable email notifications</label>
                                            </div>
                                            <div class="form-text"><?php echo isset($settings['enable_email_notifications']) ? htmlspecialchars($settings['enable_email_notifications']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="maintenance_reminder_days" class="form-label">Maintenance Reminder Days</label>
                                            <input type="number" class="form-control" id="maintenance_reminder_days" name="maintenance_reminder_days"
                                                value="<?php echo isset($settings['maintenance_reminder_days']) ? htmlspecialchars($settings['maintenance_reminder_days']['value']) : '30'; ?>">
                                            <div class="form-text"><?php echo isset($settings['maintenance_reminder_days']) ? htmlspecialchars($settings['maintenance_reminder_days']['description']) : ''; ?></div>
                                        </div>

                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Appearance Settings -->
                                <div class="tab-pane fade" id="appearance" role="tabpanel" aria-labelledby="appearance-tab">
                                    <h4 class="mb-4">Appearance Settings</h4>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="system_theme" class="form-label">System Theme</label>
                                            <select class="form-select" id="system_theme" name="system_theme">
                                                <option value="default" <?php echo (isset($settings['system_theme']) && $settings['system_theme']['value'] == 'default') ? 'selected' : ''; ?>>Default</option>
                                                <option value="dark" <?php echo (isset($settings['system_theme']) && $settings['system_theme']['value'] == 'dark') ? 'selected' : ''; ?>>Dark</option>
                                                <option value="light" <?php echo (isset($settings['system_theme']) && $settings['system_theme']['value'] == 'light') ? 'selected' : ''; ?>>Light</option>
                                                <option value="blue" <?php echo (isset($settings['system_theme']) && $settings['system_theme']['value'] == 'blue') ? 'selected' : ''; ?>>Blue</option>
                                            </select>
                                            <div class="form-text"><?php echo isset($settings['system_theme']) ? htmlspecialchars($settings['system_theme']['description']) : ''; ?></div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Theme Preview</label>
                                            <div class="card">
                                                <div class="card-body bg-light">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div class="p-2 bg-primary text-white">Primary</div>
                                                        <div class="p-2 bg-secondary text-white">Secondary</div>
                                                        <div class="p-2 bg-success text-white">Success</div>
                                                        <div class="p-2 bg-danger text-white">Danger</div>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <div class="p-2 bg-warning text-dark">Warning</div>
                                                        <div class="p-2 bg-info text-dark">Info</div>
                                                        <div class="p-2 bg-light text-dark">Light</div>
                                                        <div class="p-2 bg-dark text-white">Dark</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Advanced Settings -->
                                <div class="tab-pane fade" id="advanced" role="tabpanel" aria-labelledby="advanced-tab">
                                    <h4 class="mb-4">Advanced Settings</h4>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i> Changing these settings may affect system functionality. Proceed with caution.
                                    </div>

                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="debug_mode" class="form-label">Debug Mode</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="debug_mode" name="debug_mode" value="true"
                                                    <?php echo (isset($settings['debug_mode']) && $settings['debug_mode']['value'] == 'true') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="debug_mode">Enable debug mode</label>
                                            </div>
                                            <div class="form-text">Shows detailed error messages and logs. Use only in development.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                                            <input type="number" class="form-control" id="session_timeout" name="session_timeout"
                                                value="<?php echo isset($settings['session_timeout']) ? htmlspecialchars($settings['session_timeout']['value']) : '30'; ?>">
                                            <div class="form-text">Time in minutes before an inactive session expires</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="maintenance_mode" class="form-label">Maintenance Mode</label>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="maintenance_mode" name="maintenance_mode" value="true"
                                                    <?php echo (isset($settings['maintenance_mode']) && $settings['maintenance_mode']['value'] == 'true') ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="maintenance_mode">Enable maintenance mode</label>
                                            </div>
                                            <div class="form-text">When enabled, only administrators can access the system</div>
                                        </div>

                                        <button type="submit" name="update_settings" class="btn btn-primary">
                                            <i class="bi bi-save me-2"></i>Save Settings
                                        </button>
                                    </form>
                                </div>

                                <!-- Backup & Restore -->
                                <div class="tab-pane fade" id="backup" role="tabpanel" aria-labelledby="backup-tab">
                                    <h4 class="mb-4">Backup & Restore</h4>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Database Backup</h5>
                                        </div>
                                        <div class="card-body">
                                            <p>Create a backup of your database. This will download an SQL file containing all your data.</p>
                                            <a href="settings.php?action=backup" class="btn btn-primary">
                                                <i class="bi bi-download me-2"></i>Download Backup
                                            </a>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Restore Database</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-danger">
                                                <i class="bi bi-exclamation-triangle-fill me-2"></i> Warning: Restoring a database will overwrite all existing data. This action cannot be undone.
                                            </div>
                                            <form method="post" action="restore.php" enctype="multipart/form-data">
                                                <div class="mb-3">
                                                    <label for="restore_file" class="form-label">Select Backup File</label>
                                                    <input class="form-control" type="file" id="restore_file" name="restore_file" accept=".sql">
                                                </div>
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to restore the database? All existing data will be overwritten.')">
                                                    <i class="bi bi-upload me-2"></i>Restore Database
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Custom Settings -->
                                <div class="tab-pane fade" id="custom" role="tabpanel" aria-labelledby="custom-tab">
                                    <h4 class="mb-4">Custom Settings</h4>

                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h5 class="mb-0">Add New Setting</h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="post" action="">
                                                <div class="mb-3">
                                                    <label for="new_setting_name" class="form-label">Setting Name</label>
                                                    <input type="text" class="form-control" id="new_setting_name" name="new_setting_name" required>
                                                    <div class="form-text">Use lowercase letters and underscores (e.g
                                                        <div class="form-text">Use lowercase letters and underscores (e.g., system_timeout, logo_path)</div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="new_setting_value" class="form-label">Setting Value</label>
                                                        <input type="text" class="form-control" id="new_setting_value" name="new_setting_value" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label for="new_setting_description" class="form-label">Description</label>
                                                        <textarea class="form-control" id="new_setting_description" name="new_setting_description" rows="2"></textarea>
                                                        <div class="form-text">Brief explanation of what this setting controls</div>
                                                    </div>

                                                    <button type="submit" name="add_setting" class="btn btn-success">
                                                        <i class="bi bi-plus-circle me-2"></i>Add Setting
                                                    </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Custom Settings List</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Setting Name</th>
                                                            <th>Value</th>
                                                            <th>Description</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $custom_settings = array_filter($settings, function ($key) {
                                                            $default_settings = [
                                                                'company_name',
                                                                'system_email',
                                                                'items_per_page',
                                                                'enable_email_notifications',
                                                                'maintenance_reminder_days',
                                                                'default_currency',
                                                                'date_format',
                                                                'enable_user_registration',
                                                                'system_theme',
                                                                'debug_mode',
                                                                'session_timeout',
                                                                'maintenance_mode'
                                                            ];
                                                            return !in_array($key, $default_settings);
                                                        }, ARRAY_FILTER_USE_KEY);

                                                        if (count($custom_settings) > 0):
                                                            foreach ($custom_settings as $name => $setting):
                                                        ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($name); ?></td>
                                                                    <td><?php echo htmlspecialchars($setting['value']); ?></td>
                                                                    <td><?php echo htmlspecialchars($setting['description']); ?></td>
                                                                    <td>
                                                                        <a href="edit_setting.php?name=<?php echo urlencode($name); ?>" class="btn btn-sm btn-outline-primary">
                                                                            <i class="bi bi-pencil"></i>
                                                                        </a>
                                                                        <a href="settings.php?delete=<?php echo urlencode($name); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this setting?')">
                                                                            <i class="bi bi-trash"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php
                                                            endforeach;
                                                        else:
                                                            ?>
                                                            <tr>
                                                                <td colspan="4" class="text-center">No custom settings found. Add one using the form above.</td>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/settings_script.php";?>
</body>

</html>
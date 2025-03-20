<?php
session_start();
include "../connect.php"; // Include database connection
include "audit_trail.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

// Initialize variables for settings
$settings = [];
$success_message = '';
$error_message = '';

// Check if settings table exists, if not create it
$check_table = $conn->query("SHOW TABLES LIKE 'system_settings'");
if ($check_table->num_rows == 0) {
    // Create settings table
    $create_table = "CREATE TABLE system_settings (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        setting_name VARCHAR(100) NOT NULL UNIQUE,
        setting_value TEXT,
        setting_description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($create_table) === TRUE) {
        // Insert default settings
        $default_settings = [
            ['company_name', 'Inventory Management System', 'Company or organization name'],
            ['system_email', 'admin@example.com', 'System email for notifications'],
            ['items_per_page', '10', 'Number of items to display per page in tables'],
            ['enable_email_notifications', 'false', 'Enable or disable email notifications'],
            ['maintenance_reminder_days', '30', 'Days before maintenance due to send reminder'],
            ['default_currency', 'PHP', 'Default currency symbol for the system'],
            ['date_format', 'Y-m-d', 'PHP date format for displaying dates'],
            ['enable_user_registration', 'false', 'Allow users to register accounts'],
            ['system_theme', 'default', 'UI theme for the system']
        ];

        $insert_stmt = $conn->prepare("INSERT INTO system_settings (setting_name, setting_value, setting_description) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $name, $value, $description);

        foreach ($default_settings as $setting) {
            $name = $setting[0];
            $value = $setting[1];
            $description = $setting[2];
            $insert_stmt->execute();
        }

        $insert_stmt->close();
    } else {
        $error_message = "Error creating settings table: " . $conn->error;
    }
}

// Process form submission to update settings
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $changes = [];

    foreach ($_POST as $key => $value) {
        if ($key != 'update_settings') {
            // Get the old value to log the change
            $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_name = ?");
            $stmt->bind_param("s", $key);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $old_value = $row['setting_value'];
                if ($old_value != $value) {
                    $changes[] = "$key: from '$old_value' to '$value'";
                }
            }
            $stmt->close();

            // Update the setting
            $stmt = $conn->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_name = ?");
            $stmt->bind_param("ss", $value, $key);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Log the activity if there were changes
    if (!empty($changes)) {
        $action = "Updated settings: " . implode(", ", $changes);
        log_activity($conn, $action, "System Settings");
    }

    $success_message = "Settings updated successfully!";
}

// Fetch all settings
$settings_query = "SELECT * FROM system_settings ORDER BY id";
$settings_result = $conn->query($settings_query);

if ($settings_result->num_rows > 0) {
    while ($row = $settings_result->fetch_assoc()) {
        $settings[$row['setting_name']] = [
            'value' => $row['setting_value'],
            'description' => $row['setting_description']
        ];
    }
}

// Add new setting
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_setting'])) {
    $new_setting_name = $_POST['new_setting_name'];
    $new_setting_value = $_POST['new_setting_value'];
    $new_setting_description = $_POST['new_setting_description'];

    // Check if setting already exists
    $check_stmt = $conn->prepare("SELECT id FROM system_settings WHERE setting_name = ?");
    $check_stmt->bind_param("s", $new_setting_name);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows == 0) {
        $insert_stmt = $conn->prepare("INSERT INTO system_settings (setting_name, setting_value, setting_description) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("sss", $new_setting_name, $new_setting_value, $new_setting_description);

        if ($insert_stmt->execute()) {
            // Log the activity
            $action = "Added new setting: $new_setting_name with value: $new_setting_value";
            log_activity($conn, $action, "System Settings");

            $success_message = "New setting added successfully!";
            // Refresh settings
            header("Location: settings.php");
            exit;
        } else {
            $error_message = "Error adding new setting: " . $conn->error;
        }
        $insert_stmt->close();
    } else {
        $error_message = "Setting with this name already exists!";
    }
    $check_stmt->close();
}

// Delete setting with audit trail
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $setting_to_delete = $_GET['delete'];
    
    // Get the setting value before deleting
    $stmt = $conn->prepare("SELECT setting_value FROM system_settings WHERE setting_name = ?");
    $stmt->bind_param("s", $setting_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    $setting_value = "";
    if ($row = $result->fetch_assoc()) {
        $setting_value = $row['setting_value'];
    }
    $stmt->close();
    
    $delete_stmt = $conn->prepare("DELETE FROM system_settings WHERE setting_name = ?");
    $delete_stmt->bind_param("s", $setting_to_delete);
    
    if ($delete_stmt->execute()) {
        // Log the activity
        $action = "Deleted setting: $setting_to_delete with value: $setting_value";
        log_activity($conn, $action, "System Settings");
        
        $success_message = "Setting deleted successfully!";
        // Refresh settings
        header("Location: settings.php");
        exit;
    }else {
        $error_message = "Error deleting setting: " . $conn->error;
    }
    $delete_stmt->close();
}

// Backup database
if (isset($_GET['action']) && $_GET['action'] == 'backup') {
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $backup_file = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
    $output = '';

    foreach ($tables as $table) {
        // Get table structure
        $result = $conn->query("SHOW CREATE TABLE $table");
        $row = $result->fetch_row();
        $output .= "\n\n" . $row[1] . ";\n\n";

        // Get table data
        $result = $conn->query("SELECT * FROM $table");
        $num_fields = $result->field_count;

        while ($row = $result->fetch_row()) {
            $output .= "INSERT INTO $table VALUES(";
            for ($i = 0; $i < $num_fields; $i++) {
                $row[$i] = addslashes($row[$i]);
                $row[$i] = str_replace("\n", "\\n", $row[$i]);
                if (isset($row[$i])) {
                    $output .= "'" . $row[$i] . "'";
                } else {
                    $output .= "''";
                }
                if ($i < ($num_fields - 1)) {
                    $output .= ",";
                }
            }
            $output .= ");\n";
        }
        $output .= "\n\n";
    }

    // Download the backup file
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . $backup_file);
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    echo $output;
    exit;
}
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
            <li class="active"><a href="settings.php"><i class="bi bi-gear"></i> Settings</a></li>
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
                                <a class="list-group-item list-group-item-action" id="custom-tab" data-bs-toggle="list" href="#custom" role="tab" aria-controls="custom">
                                    <i class="bi bi-plus-circle me-2"></i> Custom Settings
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

        // Theme preview functionality
        document.getElementById('system_theme').addEventListener('change', function() {
            // This would be expanded in a real implementation to show theme previews
            console.log('Theme changed to: ' + this.value);
        });
    </script>
</body>

</html>
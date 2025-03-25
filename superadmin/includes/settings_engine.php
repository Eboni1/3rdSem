<?php
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

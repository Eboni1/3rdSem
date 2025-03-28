<?php
// Process user activation/deactivation if requested
if (isset($_POST['toggle_status']) && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Log the activity
    $action = "Changed user ID $user_id status to $new_status";
    log_activity($conn, $action, "User Management");
    
    // Redirect to refresh the page
    header("Location: user_management.php");
    exit;
}
// Fetch all users
$stmt = $conn->prepare("SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Count total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_users = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Count active users
$stmt = $conn->prepare("SELECT COUNT(*) as active FROM users WHERE status = 'active'");
$stmt->execute();
$active_users = $stmt->get_result()->fetch_assoc()['active'];
$stmt->close();

// Count inactive users
$stmt = $conn->prepare("SELECT COUNT(*) as inactive FROM users WHERE status = 'inactive'");
$stmt->execute();
$inactive_users = $stmt->get_result()->fetch_assoc()['inactive'];
$stmt->close();

// Fetch total number of offices
$total_offices_query = "SELECT COUNT(*) as total_offices FROM offices";
$total_offices_result = mysqli_query($conn, $total_offices_query);
$total_offices_data = mysqli_fetch_assoc($total_offices_result);
$total_offices = $total_offices_data['total_offices'];

// Fetch all offices
$offices_query = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$offices_result = mysqli_query($conn, $offices_query);
$offices = mysqli_fetch_all($offices_result, MYSQLI_ASSOC);

?>
<?php
// Ensure database connection is established
if (!isset($conn)) {
    include "../connect.php";
}

// Get total assets count
$total_assets_query = "SELECT COUNT(*) as total_assets FROM assets";
$total_assets_result = mysqli_query($conn, $total_assets_query);
$total_assets_data = mysqli_fetch_assoc($total_assets_result);
$total_assets = $total_assets_data['total_assets'];

// Get available assets count
$available_assets_query = "SELECT COUNT(*) as available_assets FROM assets WHERE status = 'Available'";
$available_assets_result = mysqli_query($conn, $available_assets_query);
$available_assets_data = mysqli_fetch_assoc($available_assets_result);
$available_assets = $available_assets_data['available_assets'];


// Get total users count
$users_query = "SELECT COUNT(*) as total_users FROM users";
$users_result = mysqli_query($conn, $users_query);
$total_users = mysqli_fetch_assoc($users_result)['total_users'];

// Get total offices count
$offices_query = "SELECT COUNT(*) as total_offices FROM offices";
$offices_result = mysqli_query($conn, $offices_query);
$total_offices = mysqli_fetch_assoc($offices_result)['total_offices'];

// Get inventory statistics
$total_items_query = "SELECT COUNT(*) as total FROM assets";
$total_items_result = mysqli_query($conn, $total_items_query);
$total_items = mysqli_fetch_assoc($total_items_result)['total'];

// Get available items
$available_query = "SELECT COUNT(*) as available FROM assets WHERE status = 'Available'";
$available_result = mysqli_query($conn, $available_query);
$available_items = mysqli_fetch_assoc($available_result)['available'];

// Get items in use
$in_use_query = "SELECT COUNT(*) as in_use FROM assets WHERE status = 'In Use'";
$in_use_result = mysqli_query($conn, $in_use_query);
$in_use_items = mysqli_fetch_assoc($in_use_result)['in_use'];

// Get maintenance items
$maintenance_query = "SELECT COUNT(*) as maintenance FROM assets WHERE status = 'Maintenance'";
$maintenance_result = mysqli_query($conn, $maintenance_query);
$maintenance_items = mysqli_fetch_assoc($maintenance_result)['maintenance'];

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

// Get office list with asset count and admin info
$offices_query = "
    SELECT 
        offices.id,
        offices.office_name,
        users.fullname AS admin_name,  -- Get admin name from users table
        COUNT(assets.id) AS total_assets
    FROM offices
    LEFT JOIN users ON users.office_id = offices.id AND users.role = 'admin' -- Get admin based on office_id
    LEFT JOIN assets ON assets.office_id = offices.id
    GROUP BY offices.id, users.fullname
    ORDER BY offices.office_name ASC
";

$offices_result = mysqli_query($conn, $offices_query);
$offices = mysqli_fetch_all($offices_result, MYSQLI_ASSOC);
?>

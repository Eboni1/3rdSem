<?php
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
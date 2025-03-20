<?php
session_start();
require_once "connect.php"; // Database connection
include "superadmin/audit_trail.php"; // Include audit trail function


// Ensure only Super Admins can register new users
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    die("Access denied.");
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = trim($_POST["role"]);

    // Validate inputs
    if (empty($username) || empty($password) || empty($role)) {
        die("All fields are required.");
    }

    // Ensure role is either 'admin' or 'user'
    if (!in_array($role, ['admin', 'user'])) {
        die("Invalid role.");
    }

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashed_password, $role);

    if ($stmt->execute()) {
        echo "User registered successfully. <a href='super_admin_dashboard.php'>Go back</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>

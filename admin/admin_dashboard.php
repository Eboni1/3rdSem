<?php
session_start();
require_once "../connect.php"; // Include database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

// Get admin's assigned office
$office_id = $_SESSION["office_id"];

// Fetch office details
$stmt = $conn->prepare("SELECT office_name FROM offices WHERE id = ?");
$stmt->bind_param("i", $office_id);
$stmt->execute();
$stmt->bind_result($office_name);
$stmt->fetch();
$stmt->close();

// Fetch users belonging to the same office as the admin
$sql = "SELECT id, username, role, status FROM users WHERE office_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= htmlspecialchars($office_name) ?></title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <h1>Admin Dashboard - <?= htmlspecialchars($office_name) ?></h1>
    <p>Welcome, <?= htmlspecialchars($_SESSION["username"]) ?>!</p>

    <h2>Users in <?= htmlspecialchars($office_name) ?></h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row["id"]) ?></td>
                <td><?= htmlspecialchars($row["username"]) ?></td>
                <td><?= htmlspecialchars($row["role"]) ?></td>
                <td><?= htmlspecialchars($row["status"]) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <a href="../logout.php">Logout</a>
</body>
</html>

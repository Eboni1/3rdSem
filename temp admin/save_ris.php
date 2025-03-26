<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Unauthorized access."]);
    exit;
}

require_once '../connect.php';

$title = $_POST['title'] ?? '';
$requesting_office = $_POST['requesting_office'] ?? '';
$purpose = $_POST['purpose'] ?? '';
$created_by = $_SESSION['user_id'];
$items = $_POST['items'] ?? [];

if (!empty($title) && !empty($requesting_office) && !empty($purpose) && !empty($items)) {
    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Generate unique RIS number
        $ris_number = "RIS-" . time();

        // Insert RIS report
        $stmt = $db->prepare("INSERT INTO ris_reports (ris_number, title, requesting_office, purpose, created_by, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
        $stmt->execute([$ris_number, $title, $requesting_office, $purpose, $created_by]);

        $ris_id = $db->lastInsertId(); // Get inserted RIS ID

        // Insert requested items
        $stmt = $db->prepare("INSERT INTO ris_items (ris_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt->execute([$ris_id, $item['name'], $item['quantity'], $item['unit']]);
        }

        // Log activity
        $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_name, action, action_type, created_at) VALUES (?, ?, ?, 'RIS Report', NOW())");
        $stmt->execute([$created_by, $_SESSION['username'], "created RIS #$ris_number"]);

        echo json_encode(["success" => true, "message" => "RIS Report created successfully."]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "All fields and at least one item are required."]);
}

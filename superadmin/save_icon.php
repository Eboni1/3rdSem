<?php
include "../connect.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $office_id = $_POST["office_id"];
    $icon = $_POST["icon"];

    // Update the icon for the selected office
    $query = "UPDATE offices SET icon = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $icon, $office_id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>

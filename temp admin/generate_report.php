<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include database connection
require_once '../connect.php';

// Determine report type
$report_type = $_GET['type'] ?? 'ris';
$valid_types = ['ris', 'memo'];
if (!in_array($report_type, $valid_types)) {
    die("Invalid report type.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $created_by = $_SESSION['user_id'];
    $items = $_POST['items'] ?? [];

    if (!empty($title) && !empty($description) && !empty($items)) {
        try {
            $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Generate a unique report number
            $report_number = strtoupper($report_type) . "-" . time();

            if ($report_type === 'ris') {
                $stmt = $db->prepare("INSERT INTO ris_reports (ris_number, title, description, created_by, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
            } else {
                $stmt = $db->prepare("INSERT INTO memorandum_reports (memo_number, title, description, created_by, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
            }
            $stmt->execute([$report_number, $title, $description, $created_by]);

            $report_id = $db->lastInsertId(); // Get the inserted report ID

            // Insert requested items
            $stmt = $db->prepare("INSERT INTO " . ($report_type === 'ris' ? "ris_items" : "memo_items") . " (ris_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$report_id, $item['name'], $item['quantity'], $item['unit']]);
            }

            // Redirect with success message
            header("Location: " . ($report_type === 'ris' ? "ris_reports.php" : "memo_reports.php") . "?success=Report created successfully.");
            exit;
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    } else {
        $error = "All fields and at least one item are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create <?= strtoupper($report_type); ?> Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Create New <?= strtoupper($report_type); ?> Report</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" class="form-control" name="title" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
        </div>

        <h5>Requested Items</h5>
        <table class="table table-bordered" id="itemsTable">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><input type="text" name="items[0][name]" class="form-control" required></td>
                    <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                    <td><input type="text" name="items[0][unit]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger remove-row">X</button></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-secondary" id="addRow">Add Item</button>

        <br><br>
        <button type="submit" class="btn btn-primary">Submit Report</button>
        <a href="<?= $report_type === 'ris' ? 'ris_reports.php' : 'memo_reports.php'; ?>" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let rowIndex = 1;

        // Add new row
        $("#addRow").click(function() {
            let newRow = `
                <tr>
                    <td><input type="text" name="items[${rowIndex}][name]" class="form-control" required></td>
                    <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control" required></td>
                    <td><input type="text" name="items[${rowIndex}][unit]" class="form-control" required></td>
                    <td><button type="button" class="btn btn-danger remove-row">X</button></td>
                </tr>`;
            $("#itemsTable tbody").append(newRow);
            rowIndex++;
        });

        // Remove row
        $(document).on("click", ".remove-row", function() {
            $(this).closest("tr").remove();
        });
    });
</script>

</body>
</html>

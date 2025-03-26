<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Include database connection
require_once '../connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $requesting_office = $_POST['requesting_office'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    $created_by = $_SESSION['user_id'];
    
    // Collect items dynamically from form
    $items = $_POST['items'] ?? [];
    
    if (!empty($requesting_office) && !empty($purpose) && !empty($items)) {
        try {
            $db = new PDO("mysql:host=" . $host . ";dbname=" . $dbname, $db_user, $db_pass);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Generate unique RIS number
            $ris_number = "RIS-" . time();

            // Insert RIS report
            $stmt = $db->prepare("INSERT INTO ris_reports (ris_number, requesting_office, purpose, created_by, created_at, status) VALUES (?, ?, ?, ?, NOW(), 'pending')");
            $stmt->execute([$ris_number, $requesting_office, $purpose, $created_by]);

            $ris_id = $db->lastInsertId(); // Get inserted RIS ID
            
            // Insert requested items
            $stmt = $db->prepare("INSERT INTO ris_items (ris_id, item_name, quantity, unit) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) {
                $stmt->execute([$ris_id, $item['name'], $item['quantity'], $item['unit']]);
            }

            // Log activity
            $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_name, action, action_type, created_at) VALUES (?, ?, ?, 'RIS Report', NOW())");
            $stmt->execute([$created_by, $_SESSION['username'], "created RIS #$ris_number"]);

            // Redirect with success message
            header("Location: ris_reports.php?success=Requisition and Issue Slip created successfully.");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create RIS - LGU Asset Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Create New Requisition and Issue Slip (RIS)</h2>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Requesting Office</label>
            <input type="text" class="form-control" name="requesting_office" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Purpose</label>
            <textarea class="form-control" name="purpose" rows="3" required></textarea>
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
        <button type="submit" class="btn btn-primary">Submit RIS</button>
        <a href="ris_reports.php" class="btn btn-secondary">Cancel</a>
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

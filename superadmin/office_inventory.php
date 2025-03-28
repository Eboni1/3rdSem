<?php
session_start();
include "../connect.php";

if (!isset($_GET['office_id'])) {
    die("No office selected.");
}

$office_id = $_GET['office_id'];

// Fetch office name
$officeQuery = "SELECT office_name FROM offices WHERE id = ?";
$stmt = $conn->prepare($officeQuery);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$officeResult = $stmt->get_result();
$office = $officeResult->fetch_assoc();

if (!$office) {
    die("Office not found.");
}

// Fetch assets for the selected office
$query = "SELECT * FROM assets WHERE office_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $office['office_name']; ?> Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="wrapper">
        <?php include "includes/sidebar.php"; ?> <!-- Sidebar -->

        <div class="main-content p-4">
            <h2 class="mb-4"><?php echo $office['office_name']; ?> Inventory</h2>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Asset Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Date Acquired</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($asset = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo $asset['asset_name']; ?></td>
                                <td><?php echo $asset['category']; ?></td>
                                <td><?php echo $asset['quantity']; ?></td>
                                <td>₱<?php echo number_format($asset['asset_value'], 2); ?></td>
                                <td><?php echo $asset['status']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($asset['date_acquired'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <a href="inventory.php" class="btn btn-secondary mt-3">Back to Offices</a>
        </div>
    </div>
</body>
</html>

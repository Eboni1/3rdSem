<?php
session_start();
include "../connect.php"; // Include database connection
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "super_admin") {
    header("Location: ../index.php"); // Redirect if not Super Admin
    exit;
}

// Handle asset operations (add, edit, delete)
$successMsg = $errorMsg = "";

include "includes/inventory_engine.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - Superadmin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <?php include "includes/sidebar.php"; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h2 class="content-title">Inventory Management</h2>
                        </div>
                        <div class="col-md-6 text-end">
                            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-tags"></i> Manage Categories
                            </button>
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addAssetModal">
                                <i class="fas fa-plus"></i> Add New Asset
                            </button>
                        </div>
                    </div>

                    <?php if (!empty($successMsg)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $successMsg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMsg)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $errorMsg; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Asset Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Total Assets</h6>
                                            <h2 class="mb-0">
                                                <?php
                                                $query = "SELECT COUNT(*) as total FROM assets";
                                                $result = mysqli_query($conn, $query);
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['total'];
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon-box bg-light-primary">
                                            <i class="fas fa-boxes text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Available</h6>
                                            <h2 class="mb-0">
                                                <?php
                                                $query = "SELECT COUNT(*) as available FROM assets WHERE status = 'Available'";
                                                $result = mysqli_query($conn, $query);
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['available'];
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon-box bg-light-success">
                                            <i class="fas fa-check-circle text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">In Use</h6>
                                            <h2 class="mb-0">
                                                <?php
                                                $query = "SELECT COUNT(*) as in_use FROM assets WHERE status = 'In Use'";
                                                $result = mysqli_query($conn, $query);
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['in_use'];
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon-box bg-light-info">
                                            <i class="fas fa-user-check text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card dashboard-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title text-muted">Maintenance</h6>
                                            <h2 class="mb-0">
                                                <?php
                                                $query = "SELECT COUNT(*) as maintenance FROM assets WHERE status = 'Maintenance'";
                                                $result = mysqli_query($conn, $query);
                                                $row = mysqli_fetch_assoc($result);
                                                echo $row['maintenance'];
                                                ?>
                                            </h2>
                                        </div>
                                        <div class="icon-box bg-light-warning">
                                            <i class="fas fa-tools text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assets Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Asset Inventory</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="assetsTable" class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Asset Name</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Unit</th>
                                            <th>Value</th>
                                            <th>Office</th>
                                            <th>Status</th>
                                            <th>Date Acquired</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($assets as $asset): ?>
                                            <tr>
                                                <td><?php echo $asset['asset_name']; ?></td>
                                                <td><?php echo $asset['category']; ?></td>
                                                <td><?php echo isset($asset['quantity']) ? $asset['quantity'] : ''; ?></td>
                                                <td><?php echo isset($asset['unit']) ? $asset['unit'] : ''; ?></td>
                                                <td><?php echo isset($asset['asset_value']) ? '₱' . number_format($asset['asset_value'], 2) : ''; ?></td>
                                                <td><?php echo isset($asset['office_name']) ? $asset['office_name'] : 'N/A'; ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($asset['status']) {
                                                        case 'Available':
                                                            $status_class = 'asset-status-available';
                                                            break;
                                                        case 'In Use':
                                                            $status_class = 'asset-status-in-use';
                                                            break;
                                                        case 'Maintenance':
                                                            $status_class = 'asset-status-maintenance';
                                                            break;
                                                        case 'Retired':
                                                            $status_class = 'asset-status-retired';
                                                            break;
                                                        default:
                                                            $status_class = '';
                                                    }
                                                    echo '<span class="' . $status_class . '">' . $asset['status'] . '</span>';
                                                    ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($asset['date_acquired'])); ?></td>
                                                <td><?php echo $asset['description']; ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-info edit-btn"
                                                        data-id="<?php echo $asset['id']; ?>"
                                                        data-name="<?php echo $asset['asset_name']; ?>"
                                                        data-category="<?php echo $asset['category']; ?>"
                                                        data-status="<?php echo $asset['status']; ?>"
                                                        data-description="<?php echo $asset['description']; ?>"
                                                        data-acquired="<?php echo $asset['date_acquired']; ?>"
                                                        data-value="<?php echo isset($asset['asset_value']) ? $asset['asset_value'] : ''; ?>"
                                                        data-quantity="<?php echo isset($asset['quantity']) ? $asset['quantity'] : ''; ?>"
                                                        data-unit="<?php echo isset($asset['unit']) ? $asset['unit'] : ''; ?>"
                                                        data-location="<?php echo isset($asset['location']) ? $asset['location'] : ''; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#editAssetModal">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="inventory.php?delete_id=<?php echo $asset['id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this asset?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Asset Modal -->
    <?php include "inventory_modals/add_asset_modal.php";?>


    <!-- Edit Asset Modal -->
    <?php include "inventory_modals/edit_asset_modal.php";?>

    <!-- Category Management Modal -->
    <?php include "inventory_modals/category_management_modal.php";?>

    <?php include "includes/inventory_script.php";?>
</body>
</html>
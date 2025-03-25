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
                                            <th>Location</th>
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
                                                <td><?php echo isset($asset['location']) ? $asset['location'] : ''; ?></td>
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
    <div class="modal fade" id="addAssetModal" tabindex="-1" aria-labelledby="addAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAssetModalLabel">Add New Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="inventory.php" method="POST">
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="asset_name" class="form-label">Asset Name</label>
                                <input type="text" class="form-control" id="asset_name" name="asset_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="asset_category" class="form-label">Category</label>
                                <select class="form-select" id="asset_category" name="asset_category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_name']; ?>"><?php echo $category['category_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
                            </div>
                            <div class="col-md-3">
                                <label for="unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="unit" name="unit" placeholder="pcs, sets, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="asset_value" class="form-label">Asset Value (₱)</label>
                                <input type="number" class="form-control" id="asset_value" name="asset_value" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="asset_status" class="form-label">Status</label>
                                <select class="form-select" id="asset_status" name="asset_status" required>
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_acquired" class="form-label">Date Acquired</label>
                                <input type="date" class="form-control" id="date_acquired" name="date_acquired" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" placeholder="Building, Room, etc.">
                        </div>
                        <div class="mb-3">
                            <label for="asset_description" class="form-label">Description</label>
                            <textarea class="form-control" id="asset_description" name="asset_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_asset" class="btn btn-primary">Add Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Edit Asset Modal -->
    <div class="modal fade" id="editAssetModal" tabindex="-1" aria-labelledby="editAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAssetModalLabel">Edit Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="inventory.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" id="edit_asset_id" name="asset_id">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_asset_name" class="form-label">Asset Name</label>
                                <input type="text" class="form-control" id="edit_asset_name" name="asset_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_asset_category" class="form-label">Category</label>
                                <select class="form-select" id="edit_asset_category" name="asset_category" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['category_name']; ?>"><?php echo $category['category_name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label for="edit_quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="edit_quantity" name="quantity" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label for="edit_unit" class="form-label">Unit</label>
                                <input type="text" class="form-control" id="edit_unit" name="unit" placeholder="pcs, sets, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_asset_value" class="form-label">Asset Value (₱)</label>
                                <input type="number" class="form-control" id="edit_asset_value" name="asset_value" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_asset_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_asset_status" name="asset_status" required>
                                    <option value="Available">Available</option>
                                    <option value="In Use">In Use</option>
                                    <option value="Maintenance">Maintenance</option>
                                    <option value="Retired">Retired</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="edit_date_acquired" class="form-label">Date Acquired</label>
                                <input type="date" class="form-control" id="edit_date_acquired" name="date_acquired" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="edit_location" name="location" placeholder="Building, Room, etc.">
                        </div>
                        <div class="mb-3">
                            <label for="edit_asset_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_asset_description" name="asset_description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_asset" class="btn btn-primary">Update Asset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Category Management Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryModalLabel">Category Management</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="categoryTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-tab-pane" type="button" role="tab" aria-controls="list-tab-pane" aria-selected="true">
                                <i class="fas fa-list me-1"></i> Categories List
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-tab-pane" type="button" role="tab" aria-controls="add-tab-pane" aria-selected="false">
                                <i class="fas fa-plus me-1"></i> Add New Category
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="categoryTabsContent">
                        <div class="tab-pane fade show active" id="list-tab-pane" role="tabpanel" aria-labelledby="list-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Assets Count</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category):
                                            // Count assets in this category
                                            $count_query = "SELECT COUNT(*) as count FROM assets WHERE category = '" . $category['category_name'] . "'";
                                            $count_result = mysqli_query($conn, $count_query);
                                            $count_data = mysqli_fetch_assoc($count_result);
                                        ?>
                                            <tr>
                                                <td><?php echo $category['id']; ?></td>
                                                <td><?php echo $category['category_name']; ?></td>
                                                <td><?php echo $count_data['count']; ?></td>
                                                <td>
                                                    <?php if ($count_data['count'] == 0): ?>
                                                        <a href="inventory.php?delete_category=<?php echo $category['id']; ?>"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure you want to delete this category?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled title="Cannot delete category in use">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="add-tab-pane" role="tabpanel" aria-labelledby="add-tab" tabindex="0">
                            <form action="inventory.php" method="POST">
                                <div class="mb-3">
                                    <label for="category_name" class="form-label">Category Name</label>
                                    <input type="text" class="form-control" id="category_name" name="category_name" required>
                                </div>
                                <div class="text-end">
                                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/inventory_script.php";?>
</body>
</html>
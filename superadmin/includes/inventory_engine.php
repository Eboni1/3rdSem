<?php
// Add new category
if (isset($_POST['add_category'])) {
    $category_name = mysqli_real_escape_string($conn, $_POST['category_name']);

    // Check if category already exists
    $check_query = "SELECT * FROM asset_categories WHERE category_name = '$category_name'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $errorMsg = "Category already exists!";
    } else {
        $query = "INSERT INTO asset_categories (category_name) VALUES ('$category_name')";

        if (mysqli_query($conn, $query)) {
            $successMsg = "Category added successfully!";
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}

// Delete category
if (isset($_GET['delete_category'])) {
    $category_id = mysqli_real_escape_string($conn, $_GET['delete_category']);

    // Check if category is in use
    $check_query = "SELECT COUNT(*) as count FROM assets WHERE category = (SELECT category_name FROM asset_categories WHERE id = '$category_id')";
    $check_result = mysqli_query($conn, $check_query);
    $check_data = mysqli_fetch_assoc($check_result);

    if ($check_data['count'] > 0) {
        $errorMsg = "Cannot delete category that is in use by assets!";
    } else {
        $query = "DELETE FROM asset_categories WHERE id = '$category_id'";

        if (mysqli_query($conn, $query)) {
            $successMsg = "Category deleted successfully!";
        } else {
            $errorMsg = "Error: " . mysqli_error($conn);
        }
    }
}

// Add new asset
if (isset($_POST['add_asset'])) {
    $asset_name = mysqli_real_escape_string($conn, $_POST['asset_name']);
    $asset_category = mysqli_real_escape_string($conn, $_POST['asset_category']);
    $asset_status = mysqli_real_escape_string($conn, $_POST['asset_status']);
    $asset_description = mysqli_real_escape_string($conn, $_POST['asset_description']);
    $date_acquired = mysqli_real_escape_string($conn, $_POST['date_acquired']);
    $asset_value = mysqli_real_escape_string($conn, $_POST['asset_value']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    $query = "INSERT INTO assets (asset_name, category, status, description, date_acquired, asset_value, quantity, unit, location) 
              VALUES ('$asset_name', '$asset_category', '$asset_status', '$asset_description', '$date_acquired', '$asset_value', '$quantity', '$unit', '$location')";

    if (mysqli_query($conn, $query)) {
        $successMsg = "Asset added successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Update asset
if (isset($_POST['update_asset'])) {
    $asset_id = mysqli_real_escape_string($conn, $_POST['asset_id']);
    $asset_name = mysqli_real_escape_string($conn, $_POST['asset_name']);
    $asset_category = mysqli_real_escape_string($conn, $_POST['asset_category']);
    $asset_status = mysqli_real_escape_string($conn, $_POST['asset_status']);
    $asset_description = mysqli_real_escape_string($conn, $_POST['asset_description']);
    $date_acquired = mysqli_real_escape_string($conn, $_POST['date_acquired']);
    $asset_value = mysqli_real_escape_string($conn, $_POST['asset_value']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $unit = mysqli_real_escape_string($conn, $_POST['unit']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);

    $query = "UPDATE assets SET 
              asset_name = '$asset_name', 
              category = '$asset_category', 
              status = '$asset_status', 
              description = '$asset_description', 
              date_acquired = '$date_acquired',
              asset_value = '$asset_value',
              quantity = '$quantity',
              unit = '$unit',
              location = '$location'
              WHERE id = '$asset_id'";

    if (mysqli_query($conn, $query)) {
        $successMsg = "Asset updated successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Delete asset
if (isset($_GET['delete_id'])) {
    $asset_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

    $query = "DELETE FROM assets WHERE id = '$asset_id'";

    if (mysqli_query($conn, $query)) {
        $successMsg = "Asset deleted successfully!";
    } else {
        $errorMsg = "Error: " . mysqli_error($conn);
    }
}

// Fetch all assets
$query = "SELECT * FROM assets ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$assets = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Fetch asset categories for dropdown
$query = "SELECT * FROM asset_categories ORDER BY category_name";
$categories_result = mysqli_query($conn, $query);

// If the asset_categories table doesn't exist yet, create it and fetch from assets table
if (!$categories_result) {
    // Create the asset_categories table
    $create_table_query = "CREATE TABLE IF NOT EXISTS `asset_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `category_name` varchar(100) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `category_name` (`category_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    mysqli_query($conn, $create_table_query);

    // Import existing categories from assets table
    $import_query = "INSERT IGNORE INTO asset_categories (category_name) 
                    SELECT DISTINCT category FROM assets WHERE category != ''";
    mysqli_query($conn, $import_query);

    // Fetch categories again
    $query = "SELECT * FROM asset_categories ORDER BY category_name";
    $categories_result = mysqli_query($conn, $query);
}

$categories = mysqli_fetch_all($categories_result, MYSQLI_ASSOC);

$query = "SELECT assets.*, offices.office_name 
          FROM assets 
          LEFT JOIN offices ON assets.office_id = offices.id";
$result = mysqli_query($conn, $query);
$assets = mysqli_fetch_all($result, MYSQLI_ASSOC);

?>
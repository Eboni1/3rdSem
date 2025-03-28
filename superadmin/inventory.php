<?php
session_start();
include "../connect.php";

// Fetch offices from the database
$query = "SELECT * FROM offices";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Select Office</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="wrapper">
        <?php include "includes/sidebar.php"; ?> <!-- Sidebar -->

        <div class="main-content p-4">
            <h2 class="mb-4">Select an Office</h2>
            <div class="row">
                <!-- "All Inventory" Card -->
                <div class="col-md-4">
                    <div class="card shadow-lg text-center p-3 mb-4">
                        <i class="fas fa-warehouse fa-3x text-secondary mb-3"></i>
                        <h4 class="card-title">All Inventory</h4>
                        <a href="inventory_temp.php" class="btn btn-dark">View All</a>
                    </div>
                </div>

                <!-- Office-Specific Cards -->
                <?php while ($office = mysqli_fetch_assoc($result)): ?>
                    <?php
                    // Fetch one admin user for the current office
                    $office_id = $office['office_name'];
                    $admin_query = "SELECT fullname FROM users WHERE office_id = ? AND role = 'admin' LIMIT 1";
                    $stmt = mysqli_prepare($conn, $admin_query);
                    mysqli_stmt_bind_param($stmt, "i", $office_id); // Bind as integer
                    mysqli_stmt_execute($stmt);
                    $admin_result = mysqli_stmt_get_result($stmt);
                    $admin = mysqli_fetch_assoc($admin_result);

                    ?>

                    <div class="col-md-4">
                        <div class="card shadow-lg text-center p-3 mb-4">
                            <i class="fas fa-building fa-3x text-primary mb-3"></i>
                            <h4 class="card-title"><?php echo $office['office_name']; ?></h4>

                            <!-- Display Admin User -->
                            <p class="text-muted"><strong>Admin:</strong>
                                <?php echo $admin ? $admin['fullname'] : 'No Admin Assigned'; ?>
                            </p>

                            <!-- View Inventory Button -->
                            <a href="office_inventory.php?office_id=<?php echo $office['id']; ?>" class="btn btn-primary">
                                View Inventory
                            </a>

                            <!-- View More Users Button (Trigger Modal) -->
                            <button class="btn btn-secondary mt-2 view-users-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#usersModal"
                                data-office-id="<?php echo $office['id']; ?>"
                                data-office-name="<?php echo htmlspecialchars($office['office_name']); ?>">
                                View More Details
                            </button>

                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="usersModal" tabindex="-1" aria-labelledby="usersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="usersModalLabel">Office Users</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="usersList">
                        <p class="text-center">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery & Bootstrap Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            function loadUsers(officeId, officeName) {
                $('#usersModalLabel').text('Users in ' + officeName); // Set modal title with office name
                $('#usersList').html('<p class="text-center">Loading...</p>');

                $.ajax({
                    url: 'office_users.php',
                    type: 'GET',
                    data: {
                        office_id: officeId
                    },
                    success: function(response) {
                        $('#usersList').html(response);
                    },
                    error: function() {
                        $('#usersList').html('<p class="text-danger">Failed to load users.</p>');
                    }
                });
            }

            // Load users when "View More Details" button is clicked
            $('.view-users-btn').click(function() {
                var officeId = $(this).data('office-id');
                var officeName = $(this).data('office-name'); // Get office name
                loadUsers(officeId, officeName);
            });
        });
    </script>


</body>

</html>

<?php
include "../connect.php";

if (!isset($_GET['office_id'])) {
    echo "<p class='text-danger'>Office ID is required.</p>";
    exit;
}

$office_id = intval($_GET['office_id']); // Sanitize input

// Fetch users from this office
$users_query = "SELECT fullname, email, role FROM users WHERE office_id = $office_id";
$users_result = mysqli_query($conn, $users_query);

if (mysqli_num_rows($users_result) > 0): ?>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center text-muted">No users found in this office.</p>
<?php endif; ?>
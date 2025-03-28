<?php
session_start();
include "../connect.php";

if (!isset($_GET['office_id'])) {
    echo "<p class='text-danger'>Office ID is required.</p>";
    exit;
}

$office_id = intval($_GET['office_id']); // Sanitize input
$limit = 5; // Users per page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Get total users count for pagination
$count_query = "SELECT COUNT(*) AS total FROM users WHERE office_id = $office_id";
$count_result = mysqli_query($conn, $count_query);
$total_users = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_users / $limit);

// Fetch paginated users
$users_query = "SELECT fullname, email, role FROM users WHERE office_id = $office_id LIMIT $limit OFFSET $offset";
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
                    <td>
                        <span class="badge 
        <?php
                switch ($user['role']) {
                    case 'admin':
                        echo 'bg-danger';
                        break;
                    case 'manager':
                        echo 'bg-primary';
                        break;
                    case 'staff':
                        echo 'bg-success';
                        break;
                    default:
                        echo 'bg-secondary';
                }
        ?>">
                            <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                        </span>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <nav>
        <ul class="pagination justify-content-center">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link user-pagination-btn" href="#" data-page="<?php echo $page - 1; ?>" data-office-id="<?php echo $office_id; ?>">Previous</a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link user-pagination-btn" href="#" data-page="<?php echo $i; ?>" data-office-id="<?php echo $office_id; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link user-pagination-btn" href="#" data-page="<?php echo $page + 1; ?>" data-office-id="<?php echo $office_id; ?>">Next</a>
            </li>
        </ul>
    </nav>
<?php else: ?>
    <p class="text-center text-muted">No users found in this office.</p>
<?php endif; ?>
<?php
// admin/ris_reports.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to login page if not logged in or not an admin
    header('Location: ../login.php');
    exit;
}

// Include database connection
require_once '../connect.php';

// Initialize variables
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Handle actions (approve, reject)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $report_id = (int)$_GET['id'];

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE ris_reports SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $report_id]);

            // Log the activity
            $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_name, action, action_type, created_at) VALUES (?, ?, ?, 'RIS', NOW())");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], "approved RIS report #$report_id"]);

            $success = "RIS Report #$report_id has been approved successfully.";
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE ris_reports SET status = 'rejected', rejected_by = ?, rejected_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $report_id]);

            // Log the activity
            $stmt = $db->prepare("INSERT INTO activity_logs (user_id, user_name, action, action_type, created_at) VALUES (?, ?, ?, 'RIS', NOW())");
            $stmt->execute([$_SESSION['user_id'], $_SESSION['username'], "rejected RIS report #$report_id"]);

            $success = "RIS Report #$report_id has been rejected.";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}


// Fetch RIS reports with filtering
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch RIS reports
    $query = "SELECT r.id, r.ris_number, r.title, r.status, r.created_at, 
                 u.username AS created_by_name,
                 COALESCE(GROUP_CONCAT(CONCAT(i.item_name, ' (', i.quantity, ' ', i.unit, ')') SEPARATOR ', '), '') AS items_list
          FROM ris_reports r
          LEFT JOIN users u ON r.created_by = u.id
          LEFT JOIN ris_items i ON r.id = i.ris_id
          GROUP BY r.id
          ORDER BY r.created_at DESC";


    $stmt = $db->prepare($query);
    $stmt->execute();
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ Count total records
    $countStmt = $db->prepare("SELECT COUNT(*) AS total FROM ris_reports");
    $countStmt->execute();
    $countResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $total_records = $countResult ? (int) $countResult['total'] : 0;
    $total_pages = ceil($total_records / $per_page);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $reports = [];
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RIS Reports - Report Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">

</head>

<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">Report Management</h4>
                    </div>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> User Management
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="ris_reports.php">
                                <i class="fas fa-file-alt"></i> RIS Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="memo_reports.php">
                                <i class="fas fa-clipboard"></i> Memorandum Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="generate_report.php">
                                <i class="fas fa-plus-circle"></i> Generate New Report
                            </a>
                        </li>
                        <li class="nav-item mt-5">
                            <a class="nav-link" href="../logout.php">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Top Navigation Bar -->
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">RIS Reports Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <!-- New RIS Report Button -->
                        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#risModal">
                            <i class="fas fa-plus"></i> New RIS Report
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $error; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filter Section -->
                <div class="card shadow mb-4 filter-card">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Filter RIS Reports</h6>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="ris_reports.php" class="row g-3">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Report #, Title, Description">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="" <?php echo $status === '' ? 'selected' : ''; ?>>All Statuses</option>
                                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Date From</label>
                                <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo htmlspecialchars($date_from); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Date To</label>
                                <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo htmlspecialchars($date_to); ?>">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="ris_reports.php" class="btn btn-secondary">
                                    <i class="fas fa-sync-alt"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- RIS Reports Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">RIS Reports List</h6>
                        <span class="badge bg-primary"><?php echo number_format($total_records ?? 0); ?> Total Reports</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="risReportsTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Report #</th>
                                        <th>Title</th>
                                        <th>Items</th> <!-- NEW COLUMN -->
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($reports)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No RIS reports found.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($reports as $report): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($report['ris_number']); ?></td>
                                                <td><?php echo htmlspecialchars($report['title']); ?></td>
                                                <td><?php echo htmlspecialchars($report['items_list'] ?: 'No items'); ?></td> <!-- DISPLAY ITEMS -->
                                                <td>
                                                    <?php if ($report['status'] === 'pending'): ?>
                                                        <span class="badge bg-warning status-badge">Pending</span>
                                                    <?php elseif ($report['status'] === 'approved'): ?>
                                                        <span class="badge bg-success status-badge">Approved</span>
                                                    <?php elseif ($report['status'] === 'rejected'): ?>
                                                        <span class="badge bg-danger status-badge">Rejected</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($report['created_by_name']); ?></td>
                                                <td><?php echo date('M d, Y g:i A', strtotime($report['created_at'])); ?></td>
                                                <td class="action-buttons">
                                                    <a href="view_report.php?id=<?php echo $report['id']; ?>" class="btn btn-info btn-sm" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <?php if ($report['status'] === 'pending'): ?>
                                                        <a href="ris_reports.php?action=approve&id=<?php echo $report['id']; ?>" class="btn btn-success btn-sm" title="Approve" onclick="return confirm('Are you sure you want to approve this report?');">
                                                            <i class="fas fa-check"></i>
                                                        </a>
                                                        <a href="ris_reports.php?action=reject&id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" title="Reject" onclick="return confirm('Are you sure you want to reject this report?');">
                                                            <i class="fas fa-times"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="edit_report.php?id=<?php echo $report['id']; ?>" class="btn btn-primary btn-sm" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="ris_reports.php?action=delete&id=<?php echo $report['id']; ?>" class="btn btn-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this report? This action cannot be undone.');">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a href="print_report.php?id=<?php echo $report['id']; ?>" class="btn btn-secondary btn-sm" title="Print" target="_blank">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>


                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center mt-4">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&date_from=<?php echo urlencode($date_from); ?>&date_to=<?php echo urlencode($date_to); ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

                <!-- RIS Report Modal -->
                <div class="modal fade" id="risModal" tabindex="-1" aria-labelledby="risModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="risModalLabel">Create New RIS Report</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="risAlert" class="alert d-none"></div>

                                <form id="risForm">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" name="title" required>
                                    </div>

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
                                </form>
                            </div>
                        </div>
                    </div>
                </div>



        </div>
    </div>
    </main>
    </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- jQuery for AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>



    <script>
        // Initialize DataTables with pagination disabled (we're using our own server-side pagination)
        $(document).ready(function() {
            $('#risReportsTable').DataTable({
                "paging": false,
                "searching": false,
                "ordering": true,
                "info": false,
                "responsive": true
            });
        });

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

            // AJAX Form Submission
            $("#risForm").submit(function(event) {
                event.preventDefault();
                let formData = $(this).serialize();

                $.ajax({
                    url: "save_ris.php",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        let data = JSON.parse(response);

                        if (data.success) {
                            $("#risAlert").removeClass("d-none alert-danger").addClass("alert-success").text(data.message);
                            $("#risForm")[0].reset();
                            $("#itemsTable tbody").html(`<tr>
                            <td><input type="text" name="items[0][name]" class="form-control" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control" required></td>
                            <td><input type="text" name="items[0][unit]" class="form-control" required></td>
                            <td><button type="button" class="btn btn-danger remove-row">X</button></td>
                        </tr>`);
                            rowIndex = 1;
                        } else {
                            $("#risAlert").removeClass("d-none alert-success").addClass("alert-danger").text(data.message);
                        }
                    }
                });
            });
        });
    </script>

</body>

</html>
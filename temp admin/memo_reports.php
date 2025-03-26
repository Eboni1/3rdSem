<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

// Include database connection
require_once '../connect.php';

// Initialize filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Handle actions (approve, reject, delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $memo_id = (int)$_GET['id'];

    try {
        $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if ($action === 'approve') {
            $stmt = $db->prepare("UPDATE memorandum_reports SET status = 'approved', approved_by = ?, approved_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $memo_id]);
        } elseif ($action === 'reject') {
            $stmt = $db->prepare("UPDATE memorandum_reports SET status = 'rejected', rejected_by = ?, rejected_at = NOW() WHERE id = ?");
            $stmt->execute([$_SESSION['user_id'], $memo_id]);
        } elseif ($action === 'delete' && $_SESSION['role'] === 'admin') {
            $stmt = $db->prepare("DELETE FROM memorandum_reports WHERE id = ?");
            $stmt->execute([$memo_id]);
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Fetch Memorandum Reports
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT m.*, u1.username AS created_by_name FROM memorandum_reports m
              LEFT JOIN users u1 ON m.created_by = u1.id WHERE 1";

    $params = [];

    if (!empty($search)) {
        $query .= " AND (m.memo_number LIKE ? OR m.title LIKE ? OR m.description LIKE ?)";
        array_push($params, "%$search%", "%$search%", "%$search%");
    }

    if (!empty($status)) {
        $query .= " AND m.status = ?";
        $params[] = $status;
    }

    if (!empty($date_from)) {
        $query .= " AND DATE(m.created_at) >= ?";
        $params[] = $date_from;
    }

    if (!empty($date_to)) {
        $query .= " AND DATE(m.created_at) <= ?";
        $params[] = $date_to;
    }

    // Count total records for pagination
    $countStmt = $db->prepare(str_replace("m.*, u1.username AS created_by_name", "COUNT(*) AS total", $query));
    $countStmt->execute($params);
    $total_records = $countStmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
    $total_pages = ceil($total_records / $per_page);

    // Add sorting and pagination
    $query .= " ORDER BY m.created_at DESC LIMIT $offset, $per_page";
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $memos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $memos = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Memorandum Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="style.css">

</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
            <div class="position-sticky pt-3">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ris_reports.php"><i class="fas fa-file-alt"></i> RIS Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="memo_reports.php"><i class="fas fa-clipboard"></i> Memorandum Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="generate_report.php"><i class="fas fa-plus-circle"></i> New Report</a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <h2 class="mt-3">Memorandum Reports</h2>

            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Memo #</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($memos as $memo): ?>
                        <tr>
                            <td><?= htmlspecialchars($memo['memo_number']); ?></td>
                            <td><?= htmlspecialchars($memo['title']); ?></td>
                            <td><?= ucfirst($memo['status']); ?></td>
                            <td><?= htmlspecialchars($memo['created_by_name']); ?></td>
                            <td><?= date('M d, Y g:i A', strtotime($memo['created_at'])); ?></td>
                            <td>
                                <a href="view_memo.php?id=<?= $memo['id']; ?>" class="btn btn-info btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="?action=approve&id=<?= $memo['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check"></i></a>
                                <a href="?action=reject&id=<?= $memo['id']; ?>" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></a>
                                <a href="?action=delete&id=<?= $memo['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

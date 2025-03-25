<?php
session_start();
require_once "connect.php"; // Include database connection

// Fetch Office List Dynamically
$offices = [];
$sql = "SELECT id, office_name FROM offices";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $offices[] = $row; // Store offices in an array
    }
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $office_id = trim($_POST["office"]);

    if (empty($username) || empty($password) || empty($office_id)) {
        echo "Please fill in all fields.";
        exit;
    }

    // Prepare SQL statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["office_id"] = $office_id; // Store selected office in session

            // Redirect based on role
            if ($user["role"] === "super_admin") {
                header("Location: superadmin/super_admin_dashboard.php");
            } elseif ($user["role"] === "admin") {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        } else {
            echo "Invalid username or password.";
        }
    } else {
        echo "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <title>Login - Pilar Inventory Management System</title>
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">


    <!-- Card Container for Login Form -->
    <div class="card shadow-lg p-4 rounded" style="max-width: 400px; width: 100%;">
        <div class="card-body text-center">
            <!-- Login Title Inside the Card -->
            <h3 class="fw-bold mb-3">LOGIN</h3>

            <!-- Logo Section -->
            <img src="img/logo.jpg" alt="Website Logo" class="img-fluid mb-3" style="max-width: 100px;">

            <form action="" method="post" autocomplete="off">
                <!-- Username Field -->
                <div class="mb-3 text-start">
                    <label for="username" class="fw-bold">Username</label>
                    <input type="text" name="username" id="username" class="form-control" autocomplete="off" placeholder="Enter your username">
                </div>

                <!-- Password Field -->
                <div class="mb-3 text-start">
                    <label for="password" class="fw-bold">Password</label>
                    <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" placeholder="Enter your password">
                </div>

                <!-- Office Dropdown -->
                <div class="mb-3 text-start">
                    <label for="office" class="fw-bold">Select Office</label>
                    <select name="office" id="office" class="form-select">
                        <option value="" selected disabled>Select Office</option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['office_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn btn-primary w-100">Login</button>

                <!-- Forgot Password Link -->
                <div class="mt-3">
                    <a href="forgot_password.php" class="text-decoration-none text-primary">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>


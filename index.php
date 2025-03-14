<?php
session_start();
require_once "connect.php"; // Include database connection

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        echo "Please fill in both fields.";
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
            $_SESSION["role"] = $user["role"]; // Store user role

            // Redirect based on role
            if ($user["role"] === "super_admin") {
                header("Location: super_admin_dashboard.php");
            } elseif ($user["role"] === "admin") {
                header("Location: admin_dashboard.php");
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
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="stylesheet" href="style.css">
    <title>Login</title>
</head>
<body>
    
    <div class="container mt-5 mb-3">
            <form action="" method="post">
                <div class="row mb-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6 text-center">  <!-- Changed from col-sm-4 to col-sm-6 -->
                        <label for="">Username</label>
                        <input type="text" name="username">
                    </div>
                    <div class="col-sm-3"></div> 
                </div>

                <div class="row mb-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6 text-center">  <!-- Changed from col-sm-4 to col-sm-6 -->
                        <label for="">Password</label>
                        <input type="password" name="password">
                    </div>
                    <div class="col-sm-3"></div>
                </div>

                <div class="row mt-3">
                    <div class="col-sm-3"></div>
                    <div class="col-sm-6">  <!-- Changed from col-sm-4 to col-sm-6 -->
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                    <div class="col-sm-3"></div>
                </div>
            </form>
        </div>
</body>
</html>
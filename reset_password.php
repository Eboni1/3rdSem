<?php
require_once "connect.php"; // Database connection

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify if the token exists and is not expired
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $new_password = trim($_POST["password"]);
            $confirm_password = trim($_POST["confirm_password"]);

            if (empty($new_password) || empty($confirm_password)) {
                $error = "Please enter your new password.";
            } elseif ($new_password !== $confirm_password) {
                $error = "Passwords do not match.";
            } else {
                // Hash the new password for security
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update password and remove reset token
                $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                $stmt->bind_param("ss", $hashed_password, $token);
                $stmt->execute();

                // Redirect to login page
                header("Location: index.php?reset=success");
                exit;
            }
        }
    } else {
        $error = "Invalid or expired reset token.";
    }
} else {
    $error = "No reset token provided.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Reset Password</title>
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-4 rounded" style="max-width: 400px; width: 100%;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-3">Reset Password</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="mb-3 text-start">
                    <label for="password" class="fw-bold">New Password</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Enter new password">
                </div>

                <div class="mb-3 text-start">
                    <label for="confirm_password" class="fw-bold">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required placeholder="Confirm new password">
                </div>

                <button type="submit" class="btn btn-primary w-100">Reset Password</button>
            </form>
        </div>
    </div>

</body>
</html>

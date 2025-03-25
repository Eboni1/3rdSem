<?php
require_once "connect.php"; // Include database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);

    if (empty($username)) {
        $error = "Please enter your username.";
    } else {
        // Fetch registered email from database
        $stmt = $conn->prepare("SELECT email FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($email);
            $stmt->fetch();

            // Generate a unique reset token
            $token = bin2hex(random_bytes(50));
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE username = ?");
            $stmt->bind_param("ss", $token, $username);
            $stmt->execute();

            // Send email with reset link (Modify this for your mail server)
            $reset_link = "http://yourwebsite.com/reset_password.php?token=" . $token;
            mail($email, "Password Reset", "Click here to reset your password: " . $reset_link);

            $success = "A password reset link has been sent to your registered email.";
        } else {
            $error = "No account found with this username.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <title>Forgot Password</title>
</head>
<body class="bg-light d-flex flex-column justify-content-center align-items-center vh-100">

    <div class="card shadow-lg p-4 rounded" style="max-width: 400px; width: 100%;">
        <div class="card-body text-center">
            <h3 class="fw-bold mb-3">Forgot Password</h3>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php else: ?>
                <form action="" method="post">
                    <div class="mb-3 text-start">
                        <label for="username" class="fw-bold">Enter Your Username</label>
                        <input type="text" name="username" id="username" class="form-control" required placeholder="Enter your username">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Submit</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>

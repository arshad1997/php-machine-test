<?php
include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique, secure token
        $token = bin2hex(random_bytes(32));
        $hashed_token = password_hash($token, PASSWORD_BCRYPT);
        $expires_at = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Delete any existing reset request
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Insert new reset token
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hashed_token, $expires_at);
        $stmt->execute();
        $stmt->close();

        // Send reset email
        $reset_link = "http://yourdomain.com/views/reset_password.php?token=" . urlencode($token) . "&email=" . urlencode($email);
        $subject = "Password Reset Request";
        $message = "
            <html>
            <head><title>Password Reset</title></head>
            <body>
                <p>Click the link below to reset your password:</p>
                <p><a href='{$reset_link}'>Reset Password</a></p>
                <p>If you did not request this, you can ignore this email.</p>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@yourdomain.com" . "\r\n";

        mail($email, $subject, $message, $headers);

        // Generic message to prevent email enumeration
        echo "<div class='alert alert-success text-center'>If an account with this email exists, you will receive a reset link.</div>";
    } else {
        // Show the same message for both existing & non-existing emails
        echo "<div class='alert alert-success text-center'>If an account with this email exists, you will receive a reset link.</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Forgot Password</h2>
    <form method="POST" class="w-50 mx-auto">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-warning w-100">Send Reset Link</button>
    </form>
    <p class="text-center mt-2"><a href="login.php">Back to Login</a></p>
</div>

<?php include '../includes/footer.php'; ?>

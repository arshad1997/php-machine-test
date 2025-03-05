<?php
include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert token into database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $token, $expires);
        $stmt->execute();

        // Send reset email
        $reset_link = "http://yourdomain.com/views/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the link below to reset your password:\n\n" . $reset_link;
        mail($email, $subject, $message);

        echo "<div class='alert alert-success text-center'>Check your email for a reset link.</div>";
    } else {
        echo "<div class='alert alert-danger text-center'>No account found with this email.</div>";
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

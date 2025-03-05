<?php
include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["token"])) {
    $token = $_GET["token"];

    // Validate token
    $stmt = $conn->prepare("SELECT email, expires_at FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($email, $expires_at);
    $stmt->fetch();

    if ($stmt->num_rows === 0 || strtotime($expires_at) < time()) {
        die("<div class='alert alert-danger text-center'>Invalid or expired token.</div>");
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $new_password = password_hash($_POST["password"], PASSWORD_BCRYPT);

    // Get user email from token
    $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($email);
    $stmt->fetch();

    if ($stmt->num_rows > 0) {
        // Update password in users table
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email);
        $stmt->execute();

        // Delete token after successful password reset
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();

        echo "<div class='alert alert-success text-center'>Password reset successful! <a href='login.php'>Login</a></div>";
    } else {
        echo "<div class='alert alert-danger text-center'>Invalid token.</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Reset Password</h2>
    <form method="POST" class="w-50 mx-auto">
        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET["token"]) ?>">
        <div class="mb-3">
            <label class="form-label">New Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $name, $hashed_password, $role);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION["user_id"] = $id;
            $_SESSION["user_name"] = $name;
            $_SESSION["user_role"] = $role;

            if ($role === "admin") {
                header("Location: dashboard.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            echo "<div class='alert alert-danger text-center'>Invalid password!</div>";
        }
    } else {
        echo "<div class='alert alert-danger text-center'>User not found!</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Login</h2>
    <form method="POST" class="w-50 mx-auto">
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Login</button>
    </form>
    <p class="text-center mt-2">Don't have an account? <a href="register.php">Register here</a></p>
    <p class="text-center mt-2">Forot Password? <a href="forgot_password.php">Click here</a></p>
</div>

<?php include '../includes/footer.php'; ?>

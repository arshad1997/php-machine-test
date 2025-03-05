<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Validate inputs
    if (empty($email) || empty($password)) {
        $errors[] = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Secure session handling
                session_regenerate_id(true);
                $_SESSION["user_id"] = $id;
                $_SESSION["user_name"] = $name;
                $_SESSION["user_role"] = $role;

                // Redirect based on user role
                $redirect_page = ($role === "admin") ? "dashboard.php" : "home.php";
                $stmt->close();
                $conn->close();
                
                header("Location: $redirect_page");
                exit();
            } else {
                $errors[] = "Invalid password!";
            }
        } else {
            $errors[] = "User not found!";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="container mt-4">
    <h2 class="text-center">Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger text-center">
            <?= implode("<br>", $errors); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="w-50 mx-auto shadow p-4 rounded bg-light">
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

    <p class="text-center mt-3">
        Don't have an account? <a href="register.php">Register here</a>
    </p>
    <p class="text-center mt-2">
        Forgot Password? <a href="forgot_password.php">Click here</a>
    </p>
</div>

<?php include '../includes/footer.php'; ?>

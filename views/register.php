<?php
include '../includes/db.php';
include '../includes/header.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Validate inputs
    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters!";
    }

    // Check if email exists
    if (empty($errors)) {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $errors[] = "Email already exists!";
        } else {
            // Secure password hashing
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $role = "user";

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $stmt->close();
                $check_email->close();
                $conn->close();
                
                // Redirect to login.php after successful registration
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Error registering user!";
            }
            $stmt->close();
        }
        $check_email->close();
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Register</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger text-center">
            <?= implode("<br>", $errors); ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="w-50 mx-auto shadow p-4 rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
            <small class="text-muted">At least 6 characters</small>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>

    <p class="text-center mt-2">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; ?>

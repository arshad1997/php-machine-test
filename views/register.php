<?php
include '../includes/db.php';
include '../includes/header.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    
    // Check if email already exists
    $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $check_email->store_result();
    
    if ($check_email->num_rows > 0) {
        echo "<div class='alert alert-danger text-center'>Email already exists!</div>";
    } else {
        $role = "user"; // Default role
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success text-center'>Registration successful! <a href='login.php'>Login here</a></div>";
        } else {
            echo "<div class='alert alert-danger text-center'>Error registering user!</div>";
        }
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Register</h2>
    <form method="POST" class="w-50 mx-auto">
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
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
    </form>
    <p class="text-center mt-2">Already have an account? <a href="login.php">Login here</a></p>
</div>

<?php include '../includes/footer.php'; ?>

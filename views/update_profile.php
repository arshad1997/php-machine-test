<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION["user_id"];
$message = "";

// Fetch current user data
$stmt = $conn->prepare("SELECT name, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $photo);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST["name"]);
    
    // Handle image upload
    if (!empty($_FILES["photo"]["name"])) {
        $target_dir = "../uploads/";
        $file_name = basename($_FILES["photo"]["name"]);
        $target_file = $target_dir . $file_name;
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is valid
        $allowed_types = ["jpg", "jpeg", "png"];
        if (in_array($file_type, $allowed_types)) {
            if (move_uploaded_file($_FILES["photo"]["tmp_name"], $target_file)) {
                $photo = $file_name;
            } else {
                $message = "<div class='alert alert-danger'>Failed to upload image.</div>";
            }
        } else {
            $message = "<div class='alert alert-danger'>Invalid file format. Only JPG, JPEG, and PNG allowed.</div>";
        }
    }

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET name = ?, photo = ? WHERE id = ?");
    $stmt->bind_param("ssi", $new_name, $photo, $user_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Something went wrong.</div>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Update Profile</h2>
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data" class="w-50 mx-auto">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Profile Photo</label><br>
            <img src="../uploads/<?= htmlspecialchars($photo) ?>" width="100" class="mb-2">
            <input type="file" name="photo" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Profile</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

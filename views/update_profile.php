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

// Function to fetch user details
function getUserDetails($conn, $user_id) {
    $stmt = $conn->prepare("SELECT name, photo FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($name, $photo);
    $stmt->fetch();
    $stmt->close();
    return ["name" => $name, "photo" => $photo];
}

// Function to handle image upload
function uploadImage($file) {
    $allowed_types = ["image/jpeg", "image/png", "image/jpg"];
    $upload_dir = "../uploads/";

    if ($file["error"] !== UPLOAD_ERR_OK) {
        return ["error" => "File upload error."];
    }

    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $file["tmp_name"]);
    finfo_close($file_info);

    if (!in_array($mime_type, $allowed_types)) {
        return ["error" => "Invalid file format. Only JPG, JPEG, and PNG allowed."];
    }

    $unique_name = uniqid("profile_", true) . "." . pathinfo($file["name"], PATHINFO_EXTENSION);
    $target_path = $upload_dir . $unique_name;

    if (move_uploaded_file($file["tmp_name"], $target_path)) {
        return ["success" => $unique_name];
    } else {
        return ["error" => "Failed to upload image."];
    }
}

// Fetch current user data
$user = getUserDetails($conn, $user_id);
$name = $user["name"];
$photo = $user["photo"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_name = trim($_POST["name"]);
    
    // Validate name input
    if (empty($new_name) || strlen($new_name) < 2) {
        $message = "<div class='alert alert-danger'>Invalid name. It must be at least 2 characters long.</div>";
    } else {
        $new_photo = $photo;

        // Handle image upload if a file is selected
        if (!empty($_FILES["photo"]["name"])) {
            $upload_result = uploadImage($_FILES["photo"]);
            if (isset($upload_result["error"])) {
                $message = "<div class='alert alert-danger'>" . $upload_result["error"] . "</div>";
            } else {
                $new_photo = $upload_result["success"];
            }
        }

        // Proceed with updating the database if there are no upload errors
        if (!isset($upload_result["error"])) {
            $stmt = $conn->prepare("UPDATE users SET name = ?, photo = ? WHERE id = ?");
            $stmt->bind_param("ssi", $new_name, $new_photo, $user_id);

            if ($stmt->execute()) {
                $message = "<div class='alert alert-success'>Profile updated successfully!</div>";
                $name = $new_name;
                $photo = $new_photo;
            } else {
                $message = "<div class='alert alert-danger'>Something went wrong. Please try again.</div>";
            }
        }
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

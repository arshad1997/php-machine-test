<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$user_id = $_SESSION["user_id"];

// Fetch user details
$stmt = $conn->prepare("SELECT name, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($name, $photo);
$stmt->fetch();
$stmt->close();

// Use a default profile photo if none exists
$photo = $photo ? "../uploads/" . htmlspecialchars($photo) : "../public/images/default-image.jfif";

// Fetch assigned subcategories
$stmt = $conn->prepare("
    SELECT s.subcategory_name, c.category_name 
    FROM profile_subcategories ps
    JOIN subcategories s ON ps.subcategory_id = s.id
    JOIN categories c ON s.category_id = c.id
    WHERE ps.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$assigned_subcategories = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mt-4">
    <h2 class="text-center">User Profile</h2>
    <div class="text-center">
        <img src="<?= $photo ?>" width="100" height="100" class="rounded-circle border" alt="User Photo">
        <h3><?= htmlspecialchars($name) ?></h3>
        <a href="../views/update_profile.php" class="btn btn-primary mt-3">Update Profile</a>
    </div>

    <h4 class="mt-4">Assigned Categories & Subcategories</h4>
    <?php if (!empty($assigned_subcategories)) : ?>
        <ul class="list-group">
            <?php foreach ($assigned_subcategories as $row) : ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($row['subcategory_name']) ?> (<?= htmlspecialchars($row['category_name']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p class="text-muted">No categories assigned.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

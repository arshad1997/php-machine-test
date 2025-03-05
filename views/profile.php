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

// Fetch assigned subcategories
$assigned_subcategories = $conn->query("SELECT s.subcategory_name, c.category_name 
                                        FROM profile_subcategories ps
                                        JOIN subcategories s ON ps.subcategory_id = s.id
                                        JOIN categories c ON s.category_id = c.id
                                        WHERE ps.user_id = $user_id");
?>

<div class="container mt-4">
    <h2 class="text-center">User Profile</h2>
    <div class="text-center">
        <img src="../uploads/<?= htmlspecialchars($photo) ?>" width="100" class="rounded-circle">
        <h3><?= htmlspecialchars($name) ?></h3>
        <a href="../views/update_profile.php" class="btn btn-primary mt-3">Update Profile</a>
    </div>

    <h4 class="mt-4">Assigned Categories & Subcategories</h4>
    <ul class="list-group">
        <?php while ($row = $assigned_subcategories->fetch_assoc()) : ?>
            <li class="list-group-item">
                <?= htmlspecialchars($row['subcategory_name']) ?> (<?= htmlspecialchars($row['category_name']) ?>)
            </li>
        <?php endwhile; ?>
    </ul>
</div>

<?php include '../includes/footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

// Fetch logged-in user details
$stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$stmt->bind_result($name, $role);
$stmt->fetch();
$stmt->close();

// Fetch profiles with categories and subcategories
$query = "SELECT u.photo, u.name, COALESCE(c.category_name, 'N/A') AS category_name, 
                 COALESCE(s.subcategory_name, 'N/A') AS subcategory_name 
          FROM users u
          LEFT JOIN profile_subcategories ps ON u.id = ps.user_id
          LEFT JOIN subcategories s ON ps.subcategory_id = s.id
          LEFT JOIN categories c ON s.category_id = c.id
          ORDER BY u.name";

$profiles = $conn->query($query);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Welcome, <?= htmlspecialchars($name) ?>!</h2>
        <a href="assign_categories.php" class="btn btn-primary">Assign Categories</a>
    </div>

    <?php if ($role === 'admin') : ?>
        <h3 class="mt-4">Profiles List</h3>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $profiles->fetch_assoc()) : ?>
                    <tr>
                        <td>
                            <?php if (!empty($row['photo'])) : ?>
                                <img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" class="rounded-circle" width="50" height="50">
                            <?php else : ?>
                                <img src="../public/images/default-image.jfif" class="rounded-circle" width="50" height="50">
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['category_name']) ?></td>
                        <td><?= htmlspecialchars($row['subcategory_name']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

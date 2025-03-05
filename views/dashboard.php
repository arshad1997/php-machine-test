<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

// Fetch user details
$stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$stmt->bind_result($name, $role);
$stmt->fetch();
$stmt->close();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
        <h2>Welcome, <?= htmlspecialchars($name) ?>!</h2>
        <a href="assign_categories.php" class="btn btn-primary">Assign Categories</a>
    </div>

    <?php if ($role === 'admin') : ?>
        <h3 class="mt-4">Profiles List</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Subcategory</th>
                </tr>
            </thead>
            <tbody>
                <?php
$result = $conn->query("SELECT p.photo, p.name, s.subcategory_name, c.category_name
FROM profile_subcategories ps
JOIN users p ON ps.user_id = p.id
JOIN subcategories s ON ps.subcategory_id = s.id
JOIN categories c ON s.category_id = c.id");

                while ($row = $result->fetch_assoc()) :
                ?>
                    <tr>
                        <td><img src="../uploads/<?= htmlspecialchars($row['photo']) ?>" width="50" height="50"></td>
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

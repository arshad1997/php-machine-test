<?php
session_start();
if (!isset($_SESSION["user_id"])) {
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
        <a href="logout.php" class="btn btn-danger">Logout</a>
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
                $result = $conn->query("SELECT p.photo, p.name, c.category_name, s.subcategory_name
                                        FROM profiles p
                                        LEFT JOIN categories c ON p.category_id = c.id
                                        LEFT JOIN subcategories s ON p.subcategory_id = s.id");
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

<?php
session_start();
// echo "User ID: " . ($_SESSION["user_id"] ?? 'Not Set') . "<br>";
// echo "Role: " . ($_SESSION["user_role"] ?? 'Not Set') . "<br>";
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$message = "";

// Handle Category Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_category'])) {
    $category_name = trim($_POST["category_name"]);
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $category_name);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Category added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding category.</div>";
    }
}

// Handle Subcategory Addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subcategory'])) {
    $subcategory_name = trim($_POST["subcategory_name"]);
    $category_id = $_POST["category_id"];
    $stmt = $conn->prepare("INSERT INTO subcategories (subcategory_name, category_id) VALUES (?, ?)");
    $stmt->bind_param("si", $subcategory_name, $category_id);
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>Subcategory added successfully!</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error adding subcategory.</div>";
    }
}

// Fetch Categories
$categories = $conn->query("SELECT * FROM categories");

// Fetch Subcategories
$subcategories = $conn->query("SELECT s.*, c.category_name FROM subcategories s JOIN categories c ON s.category_id = c.id");
?>

<div class="container mt-4">
    <h2 class="text-center">Admin Panel - Manage Categories & Subcategories</h2>
    <?= $message ?>

    <!-- Add Category -->
    <div class="card mt-3">
        <div class="card-header bg-primary text-white">Add Category</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" name="category_name" class="form-control" required>
                </div>
                <button type="submit" name="add_category" class="btn btn-success">Add Category</button>
            </form>
        </div>
    </div>

    <!-- Add Subcategory -->
    <div class="card mt-3">
        <div class="card-header bg-info text-white">Add Subcategory</div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Subcategory Name</label>
                    <input type="text" name="subcategory_name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Select Category</label>
                    <select name="category_id" class="form-control" required>
                        <?php while ($cat = $categories->fetch_assoc()) : ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" name="add_subcategory" class="btn btn-success">Add Subcategory</button>
            </form>
        </div>
    </div>

    <!-- Category List -->
    <h3 class="mt-4">Categories List</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Category Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT * FROM categories");
            while ($row = $result->fetch_assoc()) :
            ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Subcategory List -->
    <h3 class="mt-4">Subcategories List</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Subcategory Name</th>
                <th>Category</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $subcategories->fetch_assoc()) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['subcategory_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

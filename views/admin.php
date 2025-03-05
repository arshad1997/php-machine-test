<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$message = "";

// Function to execute prepared statements
function executeQuery($query, $params, $types = "")
{
    global $conn;
    $stmt = $conn->prepare($query);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

// Handle Category Addition
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['add_category']) && !empty(trim($_POST["category_name"]))) {
        $category_name = trim($_POST["category_name"]);
        $message = executeQuery("INSERT INTO categories (category_name) VALUES (?)", [$category_name], "s")
            ? "<div class='alert alert-success'>Category added successfully!</div>"
            : "<div class='alert alert-danger'>Error adding category.</div>";
    }

    // Handle Subcategory Addition
    if (isset($_POST['add_subcategory']) && !empty(trim($_POST["subcategory_name"])) && !empty($_POST["category_id"])) {
        $subcategory_name = trim($_POST["subcategory_name"]);
        $category_id = intval($_POST["category_id"]);
        $message = executeQuery("INSERT INTO subcategories (subcategory_name, category_id) VALUES (?, ?)", 
            [$subcategory_name, $category_id], "si")
            ? "<div class='alert alert-success'>Subcategory added successfully!</div>"
            : "<div class='alert alert-danger'>Error adding subcategory.</div>";
    }
}

// Fetch Categories and Subcategories
$categories = $conn->query("SELECT * FROM categories")->fetch_all(MYSQLI_ASSOC);
$subcategories = $conn->query("SELECT s.*, c.category_name FROM subcategories s JOIN categories c ON s.category_id = c.id")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container mt-4">
    <h2 class="text-center">Admin Panel - Manage Categories & Subcategories</h2>
    <?= $message ?>

    <div class="row">
        <div class="col-md-6">
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
        </div>

        <div class="col-md-6">
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
                                <?php foreach ($categories as $cat) : ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_subcategory" class="btn btn-success">Add Subcategory</button>
                    </form>
                </div>
            </div>
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
            <?php foreach ($categories as $row) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                </tr>
            <?php endforeach; ?>
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
            <?php foreach ($subcategories as $row) : ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['subcategory_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>

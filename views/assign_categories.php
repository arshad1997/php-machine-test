<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$message = "";

// Fetch all users
$users = $conn->query("SELECT id, name FROM users");

// Fetch all categories and their subcategories
$categories = $conn->query("SELECT * FROM categories");

// Handle form submission (Assign subcategories)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_subcategories'])) {
    $user_id = $_POST["user_id"];
    $selected_subcategories = $_POST["subcategories"] ?? [];

    // Remove existing assignments
    $conn->query("DELETE FROM profile_subcategories WHERE user_id = $user_id");

    // Insert new assignments
    foreach ($selected_subcategories as $subcategory_id) {
        $stmt = $conn->prepare("INSERT INTO profile_subcategories (user_id, subcategory_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $subcategory_id);
        $stmt->execute();
    }

    $message = "<div class='alert alert-success'>Subcategories assigned successfully!</div>";
}
?>

<div class="container mt-4">
    <h2 class="text-center">Assign Categories & Subcategories to Profiles</h2>
    <?= $message ?>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Select User</label>
            <select name="user_id" class="form-control" required>
                <option value="">Select a user</option>
                <?php while ($user = $users->fetch_assoc()) : ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Subcategories</label>
            <div class="border p-3">
                <?php
                $subcategories = $conn->query("SELECT s.id, s.subcategory_name, c.category_name 
                                               FROM subcategories s 
                                               JOIN categories c ON s.category_id = c.id");

                while ($row = $subcategories->fetch_assoc()) :
                ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="subcategories[]" value="<?= $row['id'] ?>">
                        <label class="form-check-label">
                            <?= htmlspecialchars($row['subcategory_name']) ?> (<?= htmlspecialchars($row['category_name']) ?>)
                        </label>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>

        <button type="submit" name="assign_subcategories" class="btn btn-primary">Assign Subcategories</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

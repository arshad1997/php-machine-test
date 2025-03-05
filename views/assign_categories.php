<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== 'admin') {
    header("Location: login.php");
    exit();
}

include '../includes/db.php';
include '../includes/header.php';

$message = "";

// Fetch users
$users = [];
$userQuery = $conn->query("SELECT id, name FROM users");
while ($user = $userQuery->fetch_assoc()) {
    $users[] = $user;
}

// Fetch categories and their subcategories
$categories = [];
$categoryQuery = $conn->query("SELECT c.id AS category_id, c.category_name, s.id AS subcategory_id, s.subcategory_name 
                               FROM categories c 
                               LEFT JOIN subcategories s ON c.id = s.category_id 
                               ORDER BY c.category_name, s.subcategory_name");
while ($row = $categoryQuery->fetch_assoc()) {
    $categories[$row['category_id']]['name'] = $row['category_name'];
    if ($row['subcategory_id']) {
        $categories[$row['category_id']]['subcategories'][] = [
            'id' => $row['subcategory_id'],
            'name' => $row['subcategory_name']
        ];
    }
}

// Handle subcategory assignment
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assign_subcategories'])) {
    $user_id = $_POST["user_id"];
    $selected_subcategories = $_POST["subcategories"] ?? [];

    // Remove existing assignments
    $stmt = $conn->prepare("DELETE FROM profile_subcategories WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Insert new assignments
    if (!empty($selected_subcategories)) {
        $stmt = $conn->prepare("INSERT INTO profile_subcategories (user_id, subcategory_id) VALUES (?, ?)");
        foreach ($selected_subcategories as $subcategory_id) {
            $stmt->bind_param("ii", $user_id, $subcategory_id);
            $stmt->execute();
        }
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
                <?php foreach ($users as $user) : ?>
                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Subcategories</label>
            <div class="border p-3">
                <?php foreach ($categories as $category_id => $category) : ?>
                    <strong><?= htmlspecialchars($category['name']) ?></strong>
                    <?php if (!empty($category['subcategories'])) : ?>
                        <div class="ms-3">
                            <?php foreach ($category['subcategories'] as $subcategory) : ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="subcategories[]" value="<?= $subcategory['id'] ?>">
                                    <label class="form-check-label">
                                        <?= htmlspecialchars($subcategory['name']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p class="text-muted ms-3">No subcategories available</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" name="assign_subcategories" class="btn btn-primary">Assign Subcategories</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?>

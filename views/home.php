<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

// Fetch latest profiles (Limit to 6 for display)
$profiles = $conn->query("SELECT name, photo FROM users ORDER BY id DESC LIMIT 6");
?>

<div class="container mt-4">
    <!-- Image Slider -->
    <div id="carouselExample" class="carousel slide mb-4" data-bs-ride="carousel">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="../public/images/slide1.jfif" class="d-block w-100" alt="Slide 1">
            </div>
            <div class="carousel-item">
                <img src="../public/images/slide2.jfif" class="d-block w-100" alt="Slide 2">
            </div>
            <div class="carousel-item">
                <img src="../public/images/slide3.jfif" class="d-block w-100" alt="Slide 3">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- Profiles Section -->
    <h2 class="text-center">Meet Our Members</h2>
    <div class="row">
        <?php while ($profile = $profiles->fetch_assoc()) : ?>
            <div class="col-md-4 text-center mb-3">
                <img src="uploads/<?= htmlspecialchars($profile['photo']) ?>" class="rounded-circle" width="100" height="100">
                <h5><?= htmlspecialchars($profile['name']) ?></h5>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

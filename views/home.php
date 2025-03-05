<?php
session_start();
include '../includes/db.php';
include '../includes/header.php';

$stmt = $conn->prepare("SELECT name, photo FROM users");
$stmt->execute();
$profiles = $stmt->get_result();
?>

<div class="container mt-4">
    <!-- Image Slider -->
    <div id="carouselExample" class="carousel slide mb-4 shadow rounded" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php 
            $slides = ['slide1.jfif', 'slide2.jfif', 'slide3.jfif'];
            foreach ($slides as $index => $slide) :
            ?>
                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                    <img src="../public/images/<?= $slide ?>" class="d-block w-100" alt="Slide <?= $index + 1 ?>" loading="lazy">
                </div>
            <?php endforeach; ?>
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
        <?php if ($profiles->num_rows > 0) : ?>
            <?php while ($profile = $profiles->fetch_assoc()) : ?>
                <div class="col-md-4 text-center mb-3">
                    <?php 
                    $photo = !empty($profile['photo']) && file_exists("uploads/" . $profile['photo']) 
                        ? "uploads/" . htmlspecialchars($profile['photo']) 
                        : "../public/images/default-image.jfif"; 
                    ?>
                    <img src="<?= $photo ?>" class="rounded-circle shadow-sm" width="100" height="100" loading="lazy">
                    <h5 class="mt-2"><?= htmlspecialchars($profile['name']) ?></h5>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <p class="text-center text-muted">No members found.</p>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

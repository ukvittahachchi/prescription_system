<?php
session_start();
include '../includes/auth_functions.php';
redirectIfNotLoggedIn();
if (!isPharmacy()) die("Access denied");

include '../config/db.php';

$prescription_id = $_GET['prescription_id'] ?? 0;

$stmt = $pdo->prepare("SELECT image_path FROM prescription_images WHERE prescription_id = ?");
$stmt->execute([$prescription_id]);
$images = $stmt->fetchAll();

if (empty($images)) {
    echo "<p>No images found for this prescription.</p>";
} else {
    echo '<div class="row">';
    foreach ($images as $image) {
        echo '<div class="col-md-6 mb-3">';
        echo '<img src="' . $image['image_path'] . '" class="img-fluid" alt="Prescription Image">';
        echo '</div>';
    }
    echo '</div>';
}
?>
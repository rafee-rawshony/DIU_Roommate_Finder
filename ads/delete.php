<?php
// =============================================
// ads/delete.php
// Delete an ad (only owner or admin can delete)
// =============================================

require_once '../config/db.php';

// Must be logged in to delete
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$ad_id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

if ($ad_id <= 0) {
    redirect('/index.php');
}

// Fetch the ad to verify ownership
$result = mysqli_query($conn, "SELECT * FROM ads WHERE id = $ad_id LIMIT 1");

if (mysqli_num_rows($result) == 0) {
    redirect('/index.php'); // Ad not found
}

$ad = mysqli_fetch_assoc($result);

// Check if user owns the ad or is admin
if ($ad['user_id'] != $user_id && !$is_admin) {
    redirect('/index.php'); // Not authorized
}

// Get images to delete the files from disk
$img_result = mysqli_query($conn, "SELECT image_path FROM ad_images WHERE ad_id = $ad_id");
while ($img = mysqli_fetch_assoc($img_result)) {
    $file_path = '../uploads/' . $img['image_path'];
    // Delete file if it exists
    if (file_exists($file_path)) {
        unlink($file_path);
    }
}

// Delete the ad from database
// (ad_images are deleted automatically due to CASCADE in foreign key)
mysqli_query($conn, "DELETE FROM ads WHERE id = $ad_id");

// Redirect to dashboard after deletion
redirect('/dashboard/index.php');
?>

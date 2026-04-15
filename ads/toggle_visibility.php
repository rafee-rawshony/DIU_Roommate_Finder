<?php
// =============================================
// ads/toggle_visibility.php
// Hide or show an ad (owner or admin only)
// =============================================

require_once '../config/db.php';

if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$set = isset($_GET['set']) ? (int)$_GET['set'] : -1;
$user_id = $_SESSION['user_id'];
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

if ($ad_id <= 0 || ($set !== 0 && $set !== 1)) {
    redirect('/dashboard/index.php');
}

$ad_result = mysqli_query($conn, "SELECT id, user_id FROM ads WHERE id = $ad_id LIMIT 1");
if (mysqli_num_rows($ad_result) === 0) {
    redirect('/dashboard/index.php');
}

$ad = mysqli_fetch_assoc($ad_result);
if ((int)$ad['user_id'] !== (int)$user_id && !$is_admin) {
    redirect('/dashboard/index.php');
}

mysqli_query($conn, "UPDATE ads SET is_hidden = $set WHERE id = $ad_id LIMIT 1");
redirect('/dashboard/index.php');
?>

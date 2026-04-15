<?php
// =============================================
// auth/logout.php
// Logs out the user by destroying session
// =============================================

require_once '../config/db.php';

// Destroy all session data (logs user out)
session_destroy();

// Redirect to homepage
redirect('/index.php');
?>

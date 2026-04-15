<?php
// =============================================
// config/db.php
// Database connection + Session Start
// Include this file at the top of every page
// =============================================

// Start session so we can track logged-in users
session_start();

// Let mysqli return false on connection failures instead of throwing.
mysqli_report(MYSQLI_REPORT_OFF);

// Load key=value pairs from root .env file.
function loadEnv($envPath) {
    if (!file_exists($envPath)) {
        return;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        if ($value !== '' && (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        )) {
            $value = substr($value, 1, -1);
        }

        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}

function envValue($key, $default = '') {
    $value = getenv($key);
    return ($value !== false) ? $value : $default;
}

$rootDir = dirname(__DIR__);
loadEnv($rootDir . DIRECTORY_SEPARATOR . '.env');

// Primary database connection details (production/hosting)
$host = envValue('DB_HOST', '');
$user = envValue('DB_USER', '');
$pass = envValue('DB_PASS', '');
$db   = envValue('DB_NAME', '');

// Connect to the MySQL database
$conn = false;
try {
    $conn = @mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    $conn = false;
}

// Local development fallback when InfinityFree DB is unreachable.
if (!$conn) {
    $host = envValue('LOCAL_DB_HOST', '127.0.0.1');
    $user = envValue('LOCAL_DB_USER', 'root');
    $pass = envValue('LOCAL_DB_PASS', '');
    $db   = envValue('LOCAL_DB_NAME', '');
    try {
        $conn = @mysqli_connect($host, $user, $pass, $db);
    } catch (mysqli_sql_exception $e) {
        $conn = false;
    }
}

// If connection fails, stop everything and show error
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set character encoding to UTF-8 (supports Bengali text)
mysqli_set_charset($conn, "utf8");

// =============================================
// Helper Functions
// =============================================

// Check if user is logged in
// Usage: if (!isLoggedIn()) { redirect to login }
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to a page
// Usage: redirect('login.php');
function redirect($url) {
    header("Location: $url");
    exit();
}

// Clean user input to prevent SQL injection & XSS
// Usage: $clean = clean($conn, $_POST['name']);
function clean($conn, $data) {
    $data = trim($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}
?>

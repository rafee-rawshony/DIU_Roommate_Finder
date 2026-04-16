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
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }

    return $default;
}

$rootDir = dirname(__DIR__);
loadEnv($rootDir . DIRECTORY_SEPARATOR . '.env');

$serverName = $_SERVER['SERVER_NAME'] ?? '';
$serverAddr = $_SERVER['SERVER_ADDR'] ?? '';
$isLocalRuntime = in_array($serverName, ['localhost', '127.0.0.1', '::1'], true)
    || in_array($serverAddr, ['127.0.0.1', '::1'], true)
    || PHP_SAPI === 'cli';

// Primary database connection details (production/hosting)
$host = envValue('DB_HOST', '');
$user = envValue('DB_USER', '');
$pass = envValue('DB_PASS', '');
$db   = envValue('DB_NAME', '');

if ($host === '' || $user === '' || $db === '') {
    die('Database config missing. Upload .env with DB_HOST, DB_USER, DB_PASS, DB_NAME.');
}

// Connect to the MySQL database
$conn = false;
$connectError = '';
try {
    $conn = @mysqli_connect($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    $conn = false;
}

if (!$conn) {
    $connectError = mysqli_connect_error();
}

// Local development fallback when InfinityFree DB is unreachable.
if (!$conn && $isLocalRuntime) {
    $host = envValue('LOCAL_DB_HOST', '127.0.0.1');
    $user = envValue('LOCAL_DB_USER', 'root');
    $pass = envValue('LOCAL_DB_PASS', '');
    $db   = envValue('LOCAL_DB_NAME', '');
    try {
        $conn = @mysqli_connect($host, $user, $pass, $db);
    } catch (mysqli_sql_exception $e) {
        $conn = false;
    }

    if (!$conn) {
        $fallbackError = mysqli_connect_error();
        if ($connectError !== '') {
            $connectError .= ' | Local fallback failed: ' . $fallbackError;
        } else {
            $connectError = $fallbackError;
        }
    }
}

// If connection fails, stop everything and show error
if (!$conn) {
    die("Database connection failed: " . ($connectError !== '' ? $connectError : mysqli_connect_error()));
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

// Resize image to uniform dimensions (600x400px)
// Usage: resizeImage($tmp_path, $dest_path, 600, 400);
function resizeImage($source, $destination, $max_width = 600, $max_height = 400) {
    $info = getimagesize($source);
    
    if (!$info) return false;
    
    $mime = $info['mime'];
    
    // Create image from file
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        case 'image/webp':
            $image = imagecreatefromwebp($source);
            break;
        default:
            return false;
    }
    
    if (!$image) return false;
    
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Calculate new dimensions maintaining aspect ratio
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = (int)($width * $ratio);
    $new_height = (int)($height * $ratio);
    
    // Create new image with target dimensions (center the resized image)
    $new_image = imagecreatetruecolor($max_width, $max_height);
    $bg_color = imagecolorallocate($new_image, 255, 255, 255);
    imagefill($new_image, 0, 0, $bg_color);
    
    // Calculate offset to center the image
    $offset_x = (int)(($max_width - $new_width) / 2);
    $offset_y = (int)(($max_height - $new_height) / 2);
    
    // Copy resized image to new canvas
    imagecopyresampled($new_image, $image, $offset_x, $offset_y, 0, 0, 
                       $new_width, $new_height, $width, $height);
    
    // Save resized image
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($new_image, $destination, 90);
            break;
        case 'image/png':
            imagepng($new_image, $destination, 9);
            break;
        case 'image/gif':
            imagegif($new_image, $destination);
            break;
        case 'image/webp':
            imagewebp($new_image, $destination, 90);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($new_image);
    
    return true;
}

// Common locations in Dhaka
function getCommonLocations() {
    return [
        'Dhanmondi',
        'Mirpur',
        'Bashundhara',
        'Gulshan',
        'Banani',
        'Uttara',
        'Asad Gate',
        'Shahbag',
        'Farmgate',
        'Motijheel',
        'Kawran Bazar',
        'Jatrabari',
        'Adabor',
        'Badda',
        'Mohammadpur'
    ];
}
?>

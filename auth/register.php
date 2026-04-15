<?php
// =============================================
// auth/register.php
// Student Registration Page
// Accepts @diu.edu.bd and @s.diu.edu.bd email addresses
// =============================================

require_once '../config/db.php';

// If user is already logged in, send to homepage
if (isLoggedIn()) {
    redirect('/index.php');
}

$error   = '';  // Error message to show
$success = '';  // Success message to show

// ---- Process Registration Form ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get form data and clean it
    $name     = clean($conn, $_POST['name']);
    $email    = clean($conn, strtolower($_POST['email']));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    // Validate: all fields required
    if (empty($name) || empty($email) || empty($password)) {
        $error = "All fields are required.";
    }
    // Validate: must be DIU email
    elseif (!str_ends_with($email, '@diu.edu.bd') && !str_ends_with($email, '@s.diu.edu.bd')) {
        $error = "Only @diu.edu.bd or @s.diu.edu.bd email addresses are allowed.";
    }
    // Validate: password must be at least 6 characters
    elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    }
    // Validate: passwords must match
    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }
    else {
        // Check if email is already registered
        $check = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");

        if (mysqli_num_rows($check) > 0) {
            $error = "This email is already registered. Please login.";
        } else {
            // Hash the password (never store plain text!)
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into database
            $sql = "INSERT INTO users (name, email, password)
                    VALUES ('$name', '$email', '$hashed')";

            if (mysqli_query($conn, $sql)) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - DIU Roommate Finder</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/public/32%20x%2032%20px.png">
    <link rel="apple-touch-icon" href="/public/128%20x%20128%20px.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@500;600;700&family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#634C9F',
                        primaryDark: '#523F8A',
                        primaryLight: '#F5F3FF',
                        accent: '#1A1A2E',
                        baseBg: '#F9FAFB',
                        cardBg: '#FFFFFF',
                        borderSoft: '#F3F4F6'
                    },
                    fontFamily: {
                        heading: ['Poppins', 'Inter', 'Hind Siliguri', 'sans-serif'],
                        body: ['Open Sans', 'Hind Siliguri', 'sans-serif']
                    },
                    boxShadow: {
                        soft: '0 10px 30px rgba(26, 26, 46, 0.08)'
                    }
                }
            }
        };
    </script>
</head>
<body class="bg-baseBg text-accent font-body min-h-screen antialiased">
    <div class="min-h-screen grid place-items-center p-4 sm:p-6">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <div class="h-12 w-12 mx-auto rounded-2xl bg-primaryLight flex items-center justify-center mb-3 overflow-hidden">
                    <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-9 w-9 object-contain">
                </div>
                <h1 class="font-heading text-3xl text-accent">Create Account</h1>
                <p class="text-sm text-accent/70 mt-1">Join the DIU roommate network</p>
            </div>

            <div class="bg-cardBg border border-borderSoft rounded-2xl shadow-soft p-6 sm:p-7">
                <?php if ($error): ?>
                    <div class="bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] rounded-xl px-4 py-3 mb-5 text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="bg-[#F0FDF4] border border-[#BBF7D0] text-[#15803D] rounded-xl px-4 py-3 mb-5 text-sm">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">Full Name</label>
                        <input type="text" name="name"
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                               placeholder="Your full name"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">University Email <span class="text-accent/50 font-medium">(@diu.edu.bd or @s.diu.edu.bd)</span></label>
                        <input type="email" name="email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="yourname@diu.edu.bd"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">Password</label>
                        <input type="password" name="password"
                               placeholder="Minimum 6 characters"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password"
                               placeholder="Re-enter your password"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <button type="submit"
                            class="w-full bg-primary text-white py-2.5 rounded-xl hover:bg-primaryDark font-semibold text-sm transition-colors">
                        Register
                    </button>

                    <a href="/index.php"
                       class="block w-full text-center bg-primaryLight text-primary py-2.5 rounded-xl hover:bg-[#EAE6FF] font-semibold text-sm transition-colors">
                        Back to Home
                    </a>
                </form>

                <p class="text-center text-sm text-accent/70 mt-5">
                    Already have an account?
                    <a href="/auth/login.php" class="text-primary font-semibold hover:text-primaryDark transition-colors">Login here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>

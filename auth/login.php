<?php
// =============================================
// auth/login.php
// Student Login Page
// =============================================

require_once '../config/db.php';

// If already logged in, redirect to home
if (isLoggedIn()) {
    redirect('/index.php');
}

$error = '';

// ---- Process Login Form ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email    = clean($conn, strtolower($_POST['email']));
    $password = $_POST['password'];

    // Check if fields are empty
    if (empty($email) || empty($password)) {
        $error = "Please enter your email and password.";
    } else {
        // Find user in database by email
        $sql    = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) == 1) {
            $user = mysqli_fetch_assoc($result);

            // Verify the password (using password_verify for hashed passwords)
            if (password_verify($password, $user['password'])) {

                // Password is correct! Save user info in session
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email']= $user['email'];
                $_SESSION['is_admin']  = $user['is_admin'];

                // Redirect to homepage after login
                redirect('/index.php');

            } else {
                $error = "Incorrect password. Please try again.";
            }
        } else {
            $error = "No account found with this email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - DIU Roommate Finder</title>
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
                <h1 class="font-heading text-3xl text-accent">Welcome Back</h1>
                <p class="text-sm text-accent/70 mt-1">Sign in to manage your roommate ads</p>
            </div>

            <div class="bg-cardBg border border-borderSoft rounded-2xl shadow-soft p-6 sm:p-7">
                <?php if ($error): ?>
                    <div class="bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] rounded-xl px-4 py-3 mb-5 text-sm">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">University Email</label>
                        <input type="email" name="email"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               placeholder="yourname@diu.edu.bd"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-accent/80 mb-1">Password</label>
                        <input type="password" name="password"
                               placeholder="Your password"
                               required
                               class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>

                    <button type="submit"
                            class="w-full bg-primary text-white py-2.5 rounded-xl hover:bg-primaryDark font-semibold text-sm transition-colors">
                        Login
                    </button>

                    <a href="/index.php"
                       class="block w-full text-center bg-primaryLight text-primary py-2.5 rounded-xl hover:bg-[#EAE6FF] font-semibold text-sm transition-colors">
                        Back to Home
                    </a>
                </form>

                <p class="text-center text-sm text-accent/70 mt-5">
                    Don't have an account?
                    <a href="/auth/register.php" class="text-primary font-semibold hover:text-primaryDark transition-colors">Register here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>

<?php
// =============================================
// includes/header.php
// Common HTML header + Navigation bar
// This is included at the top of every page
// =============================================

// Check if user is logged in (to show correct nav links)
$is_logged_in = isLoggedIn();
$user_name    = $is_logged_in ? $_SESSION['user_name'] : '';
$is_admin     = ($is_logged_in && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIU Roommate Finder</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/public/32%20x%2032%20px.png">
    <link rel="apple-touch-icon" href="/public/128%20x%20128%20px.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@500;600;700&family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN (no installation needed) -->
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
                        borderSoft: '#F3F4F6',
                        currency: '#111827',
                        statusDiscount: '#EF4444',
                        statusLow: '#F97316',
                        statusInStock: '#22C55E',
                        statusPending: '#FBBF24'
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
<body class="bg-baseBg text-accent font-body min-h-screen flex flex-col antialiased">

    <!-- ===== TOP NAVIGATION BAR ===== -->
    <nav class="sticky top-0 z-30 border-b border-borderSoft bg-white/95 backdrop-blur">
        <div class="bg-primary text-white text-center text-xs sm:text-sm font-semibold py-1.5">
            Welcome to DIU Roommate Finder Website
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <div class="flex items-center justify-between gap-3">
                <a href="/index.php" class="inline-flex items-center gap-2.5 min-w-fit">
                    <span class="h-10 w-10 rounded-xl bg-primaryLight flex items-center justify-center shadow-sm overflow-hidden">
                        <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-8 w-8 object-contain">
                    </span>
                    <span class="text-xl sm:text-2xl font-heading font-semibold tracking-tight text-accent">DIU Roommate<span class="text-primary">Hub</span></span>
                </a>

                <button type="button"
                        class="md:hidden h-10 w-10 rounded-lg border border-borderSoft bg-white text-primary flex items-center justify-center"
                        onclick="toggleMobileNav()"
                        aria-label="Toggle navigation menu"
                        aria-controls="mobile-nav-panel"
                        aria-expanded="false">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 5.75A.75.75 0 0 1 3.75 5h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 5.75Zm0 4.25a.75.75 0 0 1 .75-.75h12.5a.75.75 0 0 1 0 1.5H3.75A.75.75 0 0 1 3 10Zm.75 3.5a.75.75 0 0 0 0 1.5h12.5a.75.75 0 0 0 0-1.5H3.75Z" clip-rule="evenodd" />
                    </svg>
                </button>

                <div class="hidden md:flex flex-1 items-center gap-3 lg:gap-5">
                    <div class="flex-1 min-w-[260px]">
                        <form method="GET" action="/explore.php" class="relative">
                            <input type="text" name="location" placeholder="Search by location or keyword..."
                                   class="w-full border border-borderSoft rounded-xl pl-4 pr-11 py-2.5 text-sm bg-baseBg focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-primary/70 pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.474 9.765l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        </form>
                    </div>

                    <div class="flex items-center gap-2 ml-auto">
                        <?php if ($is_logged_in): ?>
                            <a href="/ads/post.php" class="bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primaryDark transition-colors text-sm whitespace-nowrap">+ Post Ad</a>
                            <details class="relative">
                                <summary class="list-none cursor-pointer h-10 w-10 rounded-full bg-primaryLight border border-[#DDD6FE] text-primary flex items-center justify-center hover:bg-[#EAE6FF] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM3 16a6 6 0 1 1 12 0v.5a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5V16Z" />
                                    </svg>
                                </summary>
                                <div class="absolute right-0 mt-2 w-44 rounded-xl border border-borderSoft bg-white shadow-soft p-2 z-40">
                                    <p class="px-3 py-2 text-xs text-accent/60 border-b border-borderSoft mb-1 truncate">Hi, <?php echo htmlspecialchars($user_name); ?></p>
                                    <a href="/dashboard/index.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-accent hover:bg-primaryLight hover:text-primary transition-colors">My Ads</a>
                                    <?php if ($is_admin): ?>
                                        <a href="/admin/index.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-accent hover:bg-primaryLight hover:text-primary transition-colors">Admin</a>
                                    <?php endif; ?>
                                    <a href="/auth/logout.php" class="block px-3 py-2 rounded-lg text-sm font-semibold text-accent hover:bg-primaryLight hover:text-primary transition-colors">Logout</a>
                                </div>
                            </details>
                        <?php else: ?>
                            <a href="/auth/login.php" class="px-3 py-2 rounded-lg text-accent hover:bg-primaryLight transition-colors text-sm font-semibold whitespace-nowrap">Sign In</a>
                            <a href="/ads/post.php" class="bg-primary text-white px-4 py-2 rounded-lg font-semibold hover:bg-primaryDark transition-colors text-sm whitespace-nowrap">+ Post Ad</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="mobile-nav-panel" class="md:hidden hidden mt-3 border-t border-borderSoft pt-3">
                <form method="GET" action="/explore.php" class="relative mb-3">
                    <input type="text" name="location" placeholder="Search by location or keyword..."
                           class="w-full border border-borderSoft rounded-xl pl-4 pr-11 py-2.5 text-sm bg-baseBg focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-primary/70 pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.474 9.765l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3Zm-4 5.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </form>

                <div class="grid grid-cols-2 gap-2 mb-3 text-sm font-semibold">
                    <a href="/index.php" class="px-3 py-2 rounded-lg bg-primaryLight text-primary text-center">Home</a>
                    <a href="/explore.php" class="px-3 py-2 rounded-lg bg-primaryLight text-primary text-center">Explore</a>
                </div>

                <div class="flex flex-col gap-2">
                    <a href="/ads/post.php" class="bg-primary text-white px-4 py-2.5 rounded-lg font-semibold text-sm text-center hover:bg-primaryDark transition-colors">+ Post Ad</a>
                    <?php if ($is_logged_in): ?>
                        <a href="/dashboard/index.php" class="px-4 py-2.5 rounded-lg border border-borderSoft text-accent font-semibold text-sm text-center hover:bg-primaryLight transition-colors">My Ads</a>
                        <?php if ($is_admin): ?>
                            <a href="/admin/index.php" class="px-4 py-2.5 rounded-lg border border-borderSoft text-accent font-semibold text-sm text-center hover:bg-primaryLight transition-colors">Admin</a>
                        <?php endif; ?>
                        <a href="/auth/logout.php" class="px-4 py-2.5 rounded-lg border border-borderSoft text-accent font-semibold text-sm text-center hover:bg-primaryLight transition-colors">Logout</a>
                        <p class="text-xs text-accent/50 text-center pt-1">Hi, <?php echo htmlspecialchars($user_name); ?></p>
                    <?php else: ?>
                        <a href="/auth/login.php" class="px-4 py-2.5 rounded-lg border border-borderSoft text-accent font-semibold text-sm text-center hover:bg-primaryLight transition-colors">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="hidden md:block border-t border-borderSoft bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2.5 flex items-center gap-2 sm:gap-4 overflow-x-auto text-sm font-semibold text-accent/70">
                <a href="/index.php" class="px-2 py-1 rounded-md hover:bg-primaryLight hover:text-primary transition-colors whitespace-nowrap">Home</a>
                <a href="/explore.php" class="px-2 py-1 rounded-md hover:bg-primaryLight hover:text-primary transition-colors whitespace-nowrap">Explore</a>
                <?php if ($is_admin): ?>
                    <a href="/admin/index.php" class="px-2 py-1 rounded-md hover:bg-primaryLight hover:text-primary transition-colors whitespace-nowrap">Admin</a>
                <?php endif; ?>
                <?php if ($is_logged_in): ?>
                    <span class="ml-auto text-xs text-accent/50 whitespace-nowrap">Hi, <?php echo htmlspecialchars($user_name); ?></span>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <script>
        function toggleMobileNav() {
            var panel = document.getElementById('mobile-nav-panel');
            if (!panel) {
                return;
            }
            panel.classList.toggle('hidden');
        }
    </script>
    <!-- ===== END NAVIGATION BAR ===== -->

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 py-8">

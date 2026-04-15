<?php
// =============================================
// dashboard/index.php
// User Dashboard - Shows user's posted ads
// =============================================

require_once '../config/db.php';

// Must be logged in to view dashboard
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';

$user_id = $_SESSION['user_id'];

// Fetch all ads posted by this user (including expired ones)
$sql = "SELECT ads.*, 
            (SELECT image_path FROM ad_images WHERE ad_id = ads.id LIMIT 1) AS thumb
        FROM ads
        WHERE user_id = $user_id
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);
$total  = mysqli_num_rows($result);
?>

<!-- Page header -->
<section class="mb-8">
    <div class="rounded-3xl border border-borderSoft bg-gradient-to-r from-primary to-primaryDark text-white p-6 sm:p-8 shadow-soft">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div>
                <h1 class="font-heading text-3xl">My Dashboard</h1>
                <p class="text-white/90 mt-2 text-sm sm:text-base">
                    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>. You have posted <?php echo $total; ?> ad<?php echo $total == 1 ? '' : 's'; ?>.
                </p>
            </div>
            <a href="/ads/post.php" class="inline-flex items-center bg-white text-primary px-5 py-2.5 rounded-xl font-semibold hover:bg-primaryLight transition-colors">+ Post New Ad</a>
        </div>
    </div>
</section>

<?php if ($total == 0): ?>
    <div class="bg-cardBg border border-borderSoft rounded-2xl shadow-soft p-10 text-center">
        <p class="text-4xl mb-3">📋</p>
        <h2 class="font-heading text-xl text-accent mb-2">You have not posted any ad yet</h2>
        <p class="text-sm text-accent/60 mb-6">Create your first listing to start getting roommate requests.</p>
        <a href="/ads/post.php" class="inline-flex items-center bg-primary text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-primaryDark transition-colors">Post Your First Ad</a>
    </div>
<?php else: ?>
    <div class="space-y-4">
        <?php while ($ad = mysqli_fetch_assoc($result)): ?>
            <?php
            $is_active = (strtotime($ad['expires_at']) > time());
            $status_label = $is_active ? 'Active' : 'Expired';
            $status_class = $is_active
                ? 'bg-[#F0FDF4] text-statusInStock border border-[#BBF7D0]'
                : 'bg-[#FEF2F2] text-statusDiscount border border-[#FECACA]';

            $is_hidden = isset($ad['is_hidden']) && (int)$ad['is_hidden'] === 1;
            $visibility_label = $is_hidden ? 'Hidden' : 'Visible';
            $visibility_class = $is_hidden
                ? 'bg-[#FFFBEB] text-[#92400E] border border-[#FDE68A]'
                : 'bg-primaryLight text-primary border border-[#DDD6FE]';
            ?>

            <article class="bg-cardBg border border-borderSoft rounded-2xl p-4 sm:p-5 shadow-sm hover:shadow-soft transition-shadow">
                <div class="flex items-start gap-4 flex-wrap sm:flex-nowrap">
                    <?php if ($ad['thumb']): ?>
                        <img src="/uploads/<?php echo htmlspecialchars($ad['thumb']); ?>"
                             alt="Thumbnail"
                             class="w-full sm:w-28 h-24 object-cover rounded-xl flex-shrink-0">
                    <?php else: ?>
                        <div class="w-full sm:w-28 h-24 bg-primaryLight rounded-xl flex items-center justify-center flex-shrink-0">
                            <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-9 w-9 object-contain">
                        </div>
                    <?php endif; ?>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3 flex-wrap">
                            <div>
                                <h3 class="font-heading text-lg text-accent mb-1 truncate"><?php echo htmlspecialchars($ad['title']); ?></h3>
                                <p class="text-sm text-accent/70">📍 <?php echo htmlspecialchars($ad['location']); ?> • <span class="font-semibold text-[#111827]">৳<?php echo number_format($ad['rent']); ?>/mo</span></p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs px-3 py-1 rounded-full font-semibold <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                                <span class="text-xs px-3 py-1 rounded-full font-semibold <?php echo $visibility_class; ?>"><?php echo $visibility_label; ?></span>
                            </div>
                        </div>

                        <p class="text-xs text-accent/55 mt-2">Posted: <?php echo date('d M Y', strtotime($ad['created_at'])); ?> • Expires: <?php echo date('d M Y', strtotime($ad['expires_at'])); ?></p>

                        <div class="mt-4 flex gap-2 flex-wrap">
                            <a href="/ads/details.php?id=<?php echo $ad['id']; ?>" class="inline-flex items-center bg-primaryLight text-primary px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#EAE6FF] transition-colors">View</a>
                            <?php if ($is_hidden): ?>
                                <a href="/ads/toggle_visibility.php?id=<?php echo $ad['id']; ?>&set=0"
                                   class="inline-flex items-center bg-[#DCFCE7] text-[#166534] px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#BBF7D0] transition-colors">
                                    Show
                                </a>
                            <?php else: ?>
                                <a href="/ads/toggle_visibility.php?id=<?php echo $ad['id']; ?>&set=1"
                                   class="inline-flex items-center bg-[#FFFBEB] text-[#92400E] px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FEF3C7] transition-colors">
                                    Hide
                                </a>
                            <?php endif; ?>
                            <a href="/ads/delete.php?id=<?php echo $ad['id']; ?>"
                               onclick="return confirm('Delete this ad?')"
                               class="inline-flex items-center bg-[#FEF2F2] text-statusDiscount px-4 py-2 rounded-lg text-sm font-semibold hover:bg-[#FEE2E2] transition-colors">Delete</a>
                        </div>
                    </div>
                </div>
            </article>
        <?php endwhile; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

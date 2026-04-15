<?php
// =============================================
// index.php
// Homepage - Shows all active ads with filters
// =============================================

require_once 'config/db.php';   // DB connection + session
require_once 'includes/header.php'; // Navigation bar

// Homepage shows the latest active ads as cards.
$sql = "SELECT ads.*, users.name AS owner_name
        FROM ads
        JOIN users ON ads.user_id = users.id
    WHERE ads.expires_at > NOW() AND ads.is_hidden = 0";

// Show newest ads first
$sql .= " ORDER BY ads.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<?php $total = mysqli_num_rows($result); ?>

<section class="mb-6 flex items-center justify-between gap-3 flex-wrap">
    <h1 class="font-heading text-4xl text-accent">Latest Rental Ads</h1>
    <p class="text-sm text-accent/60"><?php echo $total; ?> listing<?php echo $total == 1 ? '' : 's'; ?> available</p>
</section>

<section>
    <?php if ($total == 0): ?>
        <div class="rounded-2xl border border-borderSoft bg-cardBg p-10 text-center shadow-soft">
            <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-14 w-14 mx-auto mb-3 object-contain">
            <h3 class="font-heading text-xl text-accent mb-2">No ads available right now</h3>
            <p class="text-sm text-accent/60 mb-5">Please check back soon for new roommate listings.</p>
            <a href="/ads/post.php" class="inline-flex items-center justify-center bg-primary text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-primaryDark transition-colors">Post an Ad</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <?php while ($ad = mysqli_fetch_assoc($result)): ?>
                <?php
                $img_sql = "SELECT image_path FROM ad_images WHERE ad_id = " . $ad['id'] . " LIMIT 1";
                $img_result = mysqli_query($conn, $img_sql);
                $img_row = mysqli_fetch_assoc($img_result);
                $desc = $ad['description'];

                if ($ad['gender_tag'] == 'male') {
                    $gender_badge = 'Only Male';
                    $gender_class = 'bg-primaryLight text-primary';
                } elseif ($ad['gender_tag'] == 'female') {
                    $gender_badge = 'Only Female';
                    $gender_class = 'bg-primaryLight text-primaryDark';
                } else {
                    $gender_badge = 'Any Gender';
                    $gender_class = 'bg-borderSoft text-accent';
                }

                if ($ad['room_type'] == 'full') {
                    $room_badge = 'Full Room';
                    $room_class = 'bg-primaryLight text-primary';
                } else {
                    $room_badge = 'Shared';
                    $room_class = 'bg-[#FFF7ED] text-statusLow';
                }
                ?>

                <article class="group bg-cardBg rounded-2xl border border-borderSoft overflow-hidden shadow-sm hover:shadow-soft transition-all duration-300">
                    <div class="relative">
                        <?php if ($img_row): ?>
                            <img src="/uploads/<?php echo htmlspecialchars($img_row['image_path']); ?>"
                                 alt="Room Photo"
                                 class="w-full h-56 object-cover">
                        <?php else: ?>
                            <div class="w-full h-56 bg-primaryLight flex items-center justify-center">
                                <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-14 w-14 object-contain">
                            </div>
                        <?php endif; ?>
                        <span class="absolute left-3 top-3 text-[11px] font-semibold px-2.5 py-1 rounded-full bg-[#DCFCE7] text-[#166534]">Rental</span>
                    </div>

                    <div class="p-5">
                        <div class="flex gap-2 mb-3 flex-wrap">
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold <?php echo $gender_class; ?>"><?php echo $gender_badge; ?></span>
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold <?php echo $room_class; ?>"><?php echo $room_badge; ?></span>
                        </div>

                        <h3 class="font-heading text-2xl leading-tight text-accent mb-2 min-h-[62px]">
                            <?php echo htmlspecialchars($ad['title']); ?>
                        </h3>

                        <p class="text-sm text-accent/70 mb-4 min-h-[40px]">
                            <?php echo htmlspecialchars(strlen($desc) > 88 ? substr($desc, 0, 88) . '...' : $desc); ?>
                        </p>

                        <div class="flex items-center justify-between mb-3">
                            <p class="text-sm text-accent/70">📍 <?php echo htmlspecialchars($ad['location']); ?></p>
                            <p class="font-heading text-3xl leading-none font-semibold text-currency">৳<?php echo number_format($ad['rent']); ?></p>
                        </div>
                        <p class="text-[11px] text-accent/50 mb-4">per month</p>

                        <div class="flex items-center justify-between text-xs text-accent/55 mb-4">
                            <span>By <?php echo htmlspecialchars($ad['owner_name']); ?></span>
                            <span><?php echo date('d M Y', strtotime($ad['created_at'])); ?></span>
                        </div>

                        <a href="/ads/details.php?id=<?php echo $ad['id']; ?>"
                           class="inline-flex w-full items-center justify-center bg-primary text-white py-2.5 rounded-xl font-semibold hover:bg-primaryDark transition-colors">
                            View Details
                        </a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once 'includes/footer.php'; ?>

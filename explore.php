<?php
// =============================================
// explore.php
// Explore page - Shows active ads with filters
// =============================================

require_once 'config/db.php';
require_once 'includes/header.php';

$filter_gender    = isset($_GET['gender']) ? clean($conn, $_GET['gender']) : '';
$filter_room_type = isset($_GET['room_type']) ? clean($conn, $_GET['room_type']) : '';
$filter_location  = isset($_GET['location']) ? clean($conn, $_GET['location']) : '';
$filter_min_rent  = isset($_GET['min_rent']) ? (int)$_GET['min_rent'] : 0;
$filter_max_rent  = isset($_GET['max_rent']) ? (int)$_GET['max_rent'] : 0;

$sql = "SELECT ads.*, users.name AS owner_name
        FROM ads
        JOIN users ON ads.user_id = users.id
    WHERE ads.expires_at > NOW() AND ads.is_hidden = 0";

if ($filter_gender != '') {
    $sql .= " AND ads.gender_tag = '$filter_gender'";
}
if ($filter_room_type != '') {
    $sql .= " AND ads.room_type = '$filter_room_type'";
}
if ($filter_location != '') {
    $sql .= " AND ads.location LIKE '%$filter_location%'";
}
if ($filter_min_rent > 0) {
    $sql .= " AND ads.rent >= $filter_min_rent";
}
if ($filter_max_rent > 0) {
    $sql .= " AND ads.rent <= $filter_max_rent";
}

$sql .= " ORDER BY ads.created_at DESC";

$result = mysqli_query($conn, $sql);
$total = mysqli_num_rows($result);
?>

<section class="mb-5">
    <p class="text-sm text-accent/60">Marketplace Home / <span class="font-semibold text-primary">Explore</span></p>
</section>

<section class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
    <aside class="lg:col-span-3">
        <div class="bg-cardBg border border-borderSoft rounded-2xl p-5 shadow-sm lg:sticky lg:top-32">
            <h2 class="font-heading text-xl text-accent mb-5">Filter by Price</h2>

            <form method="GET" action="explore.php" class="space-y-4">
                <div class="grid grid-cols-[1fr_auto_1fr] items-end gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-accent/50 mb-1">Min</label>
                        <input type="number" name="min_rent"
                               value="<?php echo $filter_min_rent > 0 ? $filter_min_rent : ''; ?>"
                               placeholder="0"
                               class="w-full border border-borderSoft rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>
                    <span class="text-accent/40 pb-2">-</span>
                    <div>
                        <label class="block text-xs font-semibold text-accent/50 mb-1">Max</label>
                        <input type="number" name="max_rent"
                               value="<?php echo $filter_max_rent > 0 ? $filter_max_rent : ''; ?>"
                               placeholder="20000"
                               class="w-full border border-borderSoft rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-accent/80 mb-1">Location</label>
                    <input type="text" name="location"
                           value="<?php echo htmlspecialchars($filter_location); ?>"
                           placeholder="Dhanmondi, Mirpur"
                           class="w-full border border-borderSoft rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-accent/80 mb-1">Gender</label>
                    <select name="gender" class="w-full border border-borderSoft rounded-xl px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                        <option value="">All</option>
                        <option value="male" <?php echo ($filter_gender == 'male') ? 'selected' : ''; ?>>Only Male</option>
                        <option value="female" <?php echo ($filter_gender == 'female') ? 'selected' : ''; ?>>Only Female</option>
                        <option value="any" <?php echo ($filter_gender == 'any') ? 'selected' : ''; ?>>Any Gender</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-accent/80 mb-1">Room Type</label>
                    <select name="room_type" class="w-full border border-borderSoft rounded-xl px-3 py-2.5 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                        <option value="">All</option>
                        <option value="full" <?php echo ($filter_room_type == 'full') ? 'selected' : ''; ?>>Full Room</option>
                        <option value="shared" <?php echo ($filter_room_type == 'shared') ? 'selected' : ''; ?>>Shared</option>
                    </select>
                </div>

                <button type="submit" class="w-full bg-primary text-white py-2.5 rounded-xl font-semibold hover:bg-primaryDark transition-colors">Apply Filter</button>
                <a href="explore.php" class="block w-full text-center bg-primaryLight text-primary py-2.5 rounded-xl font-semibold hover:bg-[#EAE6FF] transition-colors">Reset</a>
            </form>
        </div>
    </aside>

    <div class="lg:col-span-9 space-y-5">
        <div class="bg-cardBg border border-borderSoft rounded-2xl px-4 py-3 sm:px-5 sm:py-4 flex items-center justify-between flex-wrap gap-3 shadow-sm">
            <p class="text-accent/75 text-sm sm:text-base">Showing <?php echo $total; ?> result<?php echo $total == 1 ? '' : 's'; ?></p>
            <div class="flex items-center gap-2 text-sm">
                <label class="text-accent/60 font-semibold">Sort by:</label>
                <select class="border border-borderSoft rounded-lg px-3 py-1.5 bg-white text-accent/90 focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option>Newest</option>
                    <option>Rent: Low to High</option>
                    <option>Rent: High to Low</option>
                </select>
            </div>
        </div>

        <?php if ($total == 0): ?>
            <div class="rounded-2xl border border-borderSoft bg-cardBg p-10 text-center shadow-soft">
                <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-14 w-14 mx-auto mb-3 object-contain">
                <h3 class="font-heading text-xl text-accent mb-2">No ads match your filters</h3>
                <p class="text-sm text-accent/60 mb-5">Try updating location, rent range, or room preference.</p>
                <a href="explore.php" class="inline-flex items-center justify-center bg-primary text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-primaryDark transition-colors">View all ads</a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

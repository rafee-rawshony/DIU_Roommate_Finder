<?php
// =============================================
// ads/details.php
// View Full Ad Details
// Full details + contact info only for logged-in users
// =============================================

require_once '../config/db.php';

// Get the ad ID from URL (e.g. details.php?id=5)
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If no ID provided, redirect to homepage
if ($ad_id <= 0) {
    redirect('/index.php');
}

require_once '../includes/header.php';

// Fetch the ad from database (including owner name)
$sql = "SELECT ads.*, users.name AS owner_name, users.email AS owner_email
        FROM ads
        JOIN users ON ads.user_id = users.id
        WHERE ads.id = $ad_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

// If ad not found, show error
if (mysqli_num_rows($result) == 0) {
    echo '<div class="bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] rounded-xl p-6 text-center">
        <p class="text-xl font-semibold">Ad not found</p>
        <a href="/index.php" class="text-primary underline mt-2 inline-block">Go back to homepage</a>
    </div>';
    require_once '../includes/footer.php';
    exit();
}

$ad = mysqli_fetch_assoc($result);

// Check if the ad is expired
$is_expired = (strtotime($ad['expires_at']) < time());

$owner_or_admin = isLoggedIn() && (
    ((int)$_SESSION['user_id'] === (int)$ad['user_id']) ||
    (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1)
);

// Hidden ads are visible only to the owner or admin.
if (isset($ad['is_hidden']) && (int)$ad['is_hidden'] === 1 && !$owner_or_admin) {
    echo '<div class="bg-[#FFFBEB] border border-[#FDE68A] text-[#92400E] rounded-xl p-6 text-center">
        <p class="text-xl font-semibold">This ad is currently hidden by the owner</p>
        <a href="/index.php" class="text-primary underline mt-2 inline-block">Go back to homepage</a>
    </div>';
    require_once '../includes/footer.php';
    exit();
}

// Fetch all images for this ad
$img_sql    = "SELECT image_path FROM ad_images WHERE ad_id = $ad_id";
$img_result = mysqli_query($conn, $img_sql);
$images     = mysqli_fetch_all($img_result, MYSQLI_ASSOC);

// Check if logged in
$logged_in = isLoggedIn();

$main_image = !empty($images) ? $images[0]['image_path'] : '';

$telegram_value = trim((string)($ad['telegram'] ?? ''));
$telegram_link = '';
if ($telegram_value !== '') {
    if (preg_match('/^https?:\/\//i', $telegram_value)) {
        $telegram_link = $telegram_value;
    } else {
        $telegram_link = 'https://t.me/' . ltrim($telegram_value, '@');
    }
}

$facebook_value = trim((string)($ad['facebook'] ?? ''));
$facebook_link = '';
if ($facebook_value !== '') {
    if (preg_match('/^https?:\/\//i', $facebook_value)) {
        $facebook_link = $facebook_value;
    } else {
        $facebook_link = 'https://facebook.com/' . ltrim($facebook_value, '/@');
    }
}
?>

<a href="/index.php" class="inline-flex items-center gap-2 text-primary hover:text-primaryDark transition-colors text-sm font-semibold mb-5">← Back to all ads</a>

<?php if ($is_expired): ?>
    <div class="bg-[#FFFBEB] border border-[#FDE68A] text-[#92400E] rounded-xl px-4 py-3 mb-5 text-sm">
        This ad has expired and may no longer be available.
    </div>
<?php endif; ?>

<div class="bg-cardBg rounded-2xl border border-borderSoft shadow-soft overflow-hidden">
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-0">
        <div class="lg:col-span-3 border-b lg:border-b-0 lg:border-r border-borderSoft">
            <?php if (!empty($images)): ?>
                <div class="p-4 sm:p-5">
                    <img id="mainPreviewImage"
                         src="/uploads/<?php echo htmlspecialchars($main_image); ?>"
                         alt="Room Photo"
                         class="w-full h-[280px] sm:h-[360px] object-cover rounded-xl">

                    <?php if (count($images) > 1): ?>
                        <div class="mt-3 grid grid-cols-4 sm:grid-cols-5 gap-2">
                            <?php foreach ($images as $img): ?>
                                <button type="button"
                                        class="rounded-lg overflow-hidden border border-borderSoft hover:border-primary transition-colors"
                                        onclick="document.getElementById('mainPreviewImage').src=this.querySelector('img').src;">
                                    <img src="/uploads/<?php echo htmlspecialchars($img['image_path']); ?>"
                                         alt="Room Thumbnail"
                                         class="w-full h-16 object-cover">
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="w-full h-[280px] sm:h-[360px] bg-primaryLight flex items-center justify-center">
                    <img src="/public/128%20x%20128%20px.png" alt="DIU Roommate Finder Logo" class="h-20 w-20 object-contain">
                </div>
            <?php endif; ?>
        </div>

        <div class="lg:col-span-2 p-5 sm:p-6">
            <div class="flex gap-2 mb-3 flex-wrap">
                <?php if ($ad['gender_tag'] == 'male'): ?>
                    <span class="px-3 py-1 rounded-full bg-primaryLight text-primary text-xs font-semibold">Only Male</span>
                <?php elseif ($ad['gender_tag'] == 'female'): ?>
                    <span class="px-3 py-1 rounded-full bg-primaryLight text-primaryDark text-xs font-semibold">Only Female</span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full bg-borderSoft text-accent text-xs font-semibold">Any Gender</span>
                <?php endif; ?>

                <?php if ($ad['room_type'] == 'full'): ?>
                    <span class="px-3 py-1 rounded-full bg-primaryLight text-primary text-xs font-semibold">Full Room</span>
                <?php else: ?>
                    <span class="px-3 py-1 rounded-full bg-[#FFF7ED] text-statusLow text-xs font-semibold">Shared</span>
                <?php endif; ?>
            </div>

            <h1 class="font-heading text-2xl text-accent mb-2"><?php echo htmlspecialchars($ad['title']); ?></h1>
            <?php if ($logged_in && !empty($ad['location'])): ?>
                <p class="text-sm text-accent/70 mb-4">📍 <?php echo htmlspecialchars($ad['location']); ?></p>
            <?php endif; ?>

            <div class="rounded-xl bg-primaryLight px-4 py-3 mb-5">
                <p class="text-xs text-accent/60">Monthly Rent</p>
                <p class="font-heading text-2xl text-[#111827]">৳<?php echo number_format($ad['rent']); ?></p>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-5 text-sm">
                <div class="rounded-lg border border-borderSoft p-3">
                    <p class="text-accent/55 text-xs mb-1">Posted On</p>
                    <p class="text-accent font-semibold"><?php echo date('d M Y', strtotime($ad['created_at'])); ?></p>
                </div>
                <div class="rounded-lg border border-borderSoft p-3">
                    <p class="text-accent/55 text-xs mb-1">Expires</p>
                    <p class="font-semibold <?php echo $is_expired ? 'text-statusDiscount' : 'text-accent'; ?>"><?php echo date('d M Y', strtotime($ad['expires_at'])); ?></p>
                </div>
            </div>

            <h2 class="font-heading text-base text-accent mb-2">Description</h2>
            <p class="text-sm text-accent/75 leading-relaxed whitespace-pre-line mb-6"><?php echo htmlspecialchars($ad['description']); ?></p>

            <h2 class="font-heading text-base text-accent mb-3">Contact Information</h2>

            <?php if ($logged_in): ?>
                <div class="rounded-xl border border-borderSoft bg-white p-4 space-y-2 text-sm">
                    <p><span class="font-semibold text-accent">Posted by:</span> <span class="text-accent/80"><?php echo htmlspecialchars($ad['owner_name']); ?></span></p>
                    <p><span class="font-semibold text-accent">Email:</span> <a href="mailto:<?php echo htmlspecialchars($ad['owner_email']); ?>" class="text-primary hover:text-primaryDark transition-colors"><?php echo htmlspecialchars($ad['owner_email']); ?></a></p>
                    <p><span class="font-semibold text-accent">Phone:</span> <a href="tel:<?php echo htmlspecialchars($ad['contact_phone']); ?>" class="text-primary hover:text-primaryDark transition-colors"><?php echo htmlspecialchars($ad['contact_phone']); ?></a></p>

                    <?php if (!empty($ad['whatsapp'])): ?>
                        <a href="https://wa.me/88<?php echo htmlspecialchars($ad['whatsapp']); ?>"
                           target="_blank"
                           class="inline-flex mt-2 items-center justify-center w-full bg-primary text-white px-4 py-2.5 rounded-xl hover:bg-primaryDark font-semibold transition-colors">
                            Chat on WhatsApp
                        </a>
                    <?php endif; ?>

                    <?php if ($telegram_link !== ''): ?>
                        <a href="<?php echo htmlspecialchars($telegram_link); ?>"
                           target="_blank"
                           class="inline-flex mt-2 items-center justify-center w-full bg-primaryLight text-primary px-4 py-2.5 rounded-xl hover:bg-[#EAE6FF] font-semibold transition-colors">
                            Open Telegram
                        </a>
                    <?php endif; ?>

                    <?php if ($facebook_link !== ''): ?>
                        <a href="<?php echo htmlspecialchars($facebook_link); ?>"
                           target="_blank"
                           class="inline-flex mt-2 items-center justify-center w-full bg-primaryLight text-primary px-4 py-2.5 rounded-xl hover:bg-[#EAE6FF] font-semibold transition-colors">
                            Open Facebook Profile
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="bg-primaryLight border border-borderSoft rounded-xl p-5 text-center">
                    <p class="text-3xl mb-2">🔒</p>
                    <p class="font-semibold text-accent mb-1">Login to view contact details</p>
                    <p class="text-sm text-accent/65 mb-4">Phone, WhatsApp, Telegram, and Facebook are visible for logged-in users only.</p>
                    <div class="flex gap-2 justify-center flex-wrap">
                        <a href="/auth/login.php" class="bg-primary text-white px-5 py-2 rounded-lg hover:bg-primaryDark font-semibold text-sm transition-colors">Login</a>
                        <a href="/auth/register.php" class="bg-white border border-borderSoft text-primary px-5 py-2 rounded-lg hover:bg-primaryLight font-semibold text-sm transition-colors">Register</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

        <!-- Delete Button (only for ad owner or admin) -->
        <?php
        $can_delete = $logged_in && (
            $_SESSION['user_id'] == $ad['user_id'] ||
            (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1)
        );
        ?>
        <?php if ($can_delete): ?>
            <div class="mx-5 sm:mx-6 my-6 pt-5 border-t border-borderSoft">
                <a href="/ads/delete.php?id=<?php echo $ad['id']; ?>"
                   onclick="return confirm('Are you sure you want to delete this ad?')"
                   class="inline-flex items-center bg-[#FEF2F2] text-statusDiscount px-5 py-2 rounded-lg hover:bg-[#FEE2E2] text-sm font-semibold transition-colors">
                    Delete This Ad
                </a>
            </div>
        <?php endif; ?>

</div>

<?php require_once '../includes/footer.php'; ?>

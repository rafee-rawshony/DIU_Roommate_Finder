<?php
// =============================================
// ads/edit.php
// Edit Roommate Ad
// Only the owner or admin can edit
// =============================================

require_once '../config/db.php';

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';

$error   = '';
$success = '';

// Get ad ID from URL
$ad_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($ad_id <= 0) {
    redirect('/index.php');
}

// Fetch the ad from database
$sql = "SELECT * FROM ads WHERE id = $ad_id LIMIT 1";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    redirect('/index.php');
}

$ad = mysqli_fetch_assoc($result);

// Check if user is owner or admin
$is_owner = ((int)$_SESSION['user_id'] === (int)$ad['user_id']);
$is_admin = (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1);

if (!$is_owner && !$is_admin) {
    redirect('/index.php');
}

// Fetch all images for this ad
$img_sql    = "SELECT id, image_path FROM ad_images WHERE ad_id = $ad_id ORDER BY id";
$img_result = mysqli_query($conn, $img_sql);
$images     = mysqli_fetch_all($img_result, MYSQLI_ASSOC);

// ---- Process Edit Ad Form ----
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get and clean form data
    $title       = clean($conn, $_POST['title']);
    $description = clean($conn, $_POST['description']);
    $rent        = (float) $_POST['rent'];
    $location    = clean($conn, $_POST['location']);
    $gender_tag  = clean($conn, $_POST['gender_tag']);
    $room_type   = clean($conn, $_POST['room_type']);
    $phone       = clean($conn, $_POST['contact_phone']);
    $whatsapp    = clean($conn, $_POST['whatsapp']);
    $telegram    = clean($conn, $_POST['telegram']);
    $facebook    = clean($conn, $_POST['facebook']);
    $expiry_days = (int) $_POST['expiry_days'];

    // Validate required fields
    if (empty($title) || empty($description) || empty($phone)) {
        $error = "Please fill in all required fields.";
    }
    elseif ($rent <= 0) {
        $error = "Please enter a valid rent amount.";
    }
    elseif (!in_array($expiry_days, [7, 15, 30, 45])) {
        $error = "Please select a valid expiry duration.";
    }
    else {
        // Calculate new expiry date
        $expires_at = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));

        // Update the ad in the database
        $sql = "UPDATE ads
                SET title = '$title',
                    description = '$description',
                    rent = $rent,
                    location = '$location',
                    gender_tag = '$gender_tag',
                    room_type = '$room_type',
                    contact_phone = '$phone',
                    whatsapp = '$whatsapp',
                    telegram = '$telegram',
                    facebook = '$facebook',
                    expiry_days = $expiry_days,
                    expires_at = '$expires_at'
                WHERE id = $ad_id";

        if (mysqli_query($conn, $sql)) {

            // ---- Handle Image Uploads ----
            if (!empty($_FILES['images']['name'][0])) {

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size      = 2 * 1024 * 1024; // 2MB per image
                $max_images    = 5; // Maximum 5 images per ad

                // Count current images
                $current_count = count($images);

                // Count new uploads
                $new_count = 0;
                foreach ($_FILES['images']['name'] as $name) {
                    if (!empty($name)) $new_count++;
                }

                // Total image count (existing + new)
                $total_count = $current_count + $new_count;

                // Validate total image count
                if ($total_count > $max_images) {
                    $error .= " Total images cannot exceed $max_images. You currently have $current_count images.";
                } else {
                    // Loop through each uploaded file
                    foreach ($_FILES['images']['tmp_name'] as $key => $tmp) {

                        $file_name = $_FILES['images']['name'][$key];
                        $file_type = $_FILES['images']['type'][$key];
                        $file_size = $_FILES['images']['size'][$key];

                        // Skip empty files
                        if (empty($file_name)) continue;

                        // Check file type and size
                        if (!in_array($file_type, $allowed_types)) {
                            $error .= " File '$file_name' is not a valid image.";
                            continue;
                        }
                        if ($file_size > $max_size) {
                            $error .= " File '$file_name' is too large (max 2MB).";
                            continue;
                        }

                        // Create a unique file name to avoid conflicts
                        $ext        = pathinfo($file_name, PATHINFO_EXTENSION);
                        $new_name   = uniqid('img_') . '.' . $ext;
                        $upload_dir = '../uploads/';
                        $dest       = $upload_dir . $new_name;

                        // Move the file to uploads folder
                        if (move_uploaded_file($tmp, $dest)) {
                            // Resize image to uniform dimensions (600x400px)
                            resizeImage($dest, $dest, 600, 400);
                            
                            // Save image path in database
                            $img_sql = "INSERT INTO ad_images (ad_id, image_path)
                                        VALUES ($ad_id, '$new_name')";
                            mysqli_query($conn, $img_sql);
                        }
                    }
                }
            }

            // Handle image deletions
            if (isset($_POST['delete_images'])) {
                $remaining_images = count($images) - count($_POST['delete_images']);
                
                // Check if at least 1 image will remain
                if ($remaining_images >= 1) {
                    foreach ($_POST['delete_images'] as $img_id) {
                        $img_id = (int)$img_id;
                        // Get image path before deleting
                        $del_sql = "SELECT image_path FROM ad_images WHERE id = $img_id AND ad_id = $ad_id";
                        $del_result = mysqli_query($conn, $del_sql);
                        if ($del_row = mysqli_fetch_assoc($del_result)) {
                            // Delete file from uploads folder
                            $file_path = '../uploads/' . $del_row['image_path'];
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                        }
                        // Delete from database
                        $sql = "DELETE FROM ad_images WHERE id = $img_id AND ad_id = $ad_id";
                        mysqli_query($conn, $sql);
                    }
                } else {
                    $error .= " You must keep at least 1 image.";
                }
            }

            if (!$error) {
                $success = "Your ad has been updated successfully!";
                // Refresh images list
                $img_result = mysqli_query($conn, "SELECT id, image_path FROM ad_images WHERE ad_id = $ad_id ORDER BY id");
                $images = mysqli_fetch_all($img_result, MYSQLI_ASSOC);
                // Re-fetch ad
                $result = mysqli_query($conn, "SELECT * FROM ads WHERE id = $ad_id LIMIT 1");
                $ad = mysqli_fetch_assoc($result);
            }

        } else {
            $error = "Failed to update ad. Please try again.";
        }
    }
}
?>

<!-- Page heading -->
<section class="mb-6">
    <a href="/dashboard/index.php" class="inline-flex items-center gap-2 text-primary hover:text-primaryDark transition-colors text-sm font-semibold mb-5">← Back to My Ads</a>
    <h1 class="font-heading text-3xl text-accent">Edit Ad</h1>
    <p class="text-sm text-accent/65 mt-1">Update your ad details and images.</p>
</section>

<?php if ($error): ?>
    <div class="bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] rounded-xl px-4 py-3 mb-5 text-sm">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-[#F0FDF4] border border-[#BBF7D0] text-[#15803D] rounded-xl px-4 py-3 mb-5 text-sm">
        <?php echo $success; ?>
    </div>
<?php endif; ?>

<div class="bg-cardBg border border-borderSoft rounded-2xl shadow-soft p-5 sm:p-7">
    <form method="POST" action="" enctype="multipart/form-data" class="space-y-6">

        <!-- Ad Title -->
        <div>
            <label class="block text-sm font-semibold text-accent/80 mb-1">
                Ad Title <span class="text-red-500">*</span>
            </label>
            <input type="text" name="title"
                   placeholder="e.g. Need roommate in Dhanmondi near DIU"
                   value="<?php echo htmlspecialchars($ad['title']); ?>"
                   required
                   class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-semibold text-accent/80 mb-1">
                Description <span class="text-red-500">*</span>
            </label>
            <textarea name="description" rows="5"
                      placeholder="Describe the room, facilities, nearby places, preferences..."
                      required
                      class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all"><?php echo htmlspecialchars($ad['description']); ?></textarea>
        </div>

        <!-- Two columns: Rent and Location -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Monthly Rent (৳) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="rent" min="0"
                       placeholder="e.g. 4000"
                       value="<?php echo htmlspecialchars($ad['rent']); ?>"
                       required
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Location <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <select name="location"
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="">-- Select a location --</option>
                    <?php foreach (getCommonLocations() as $loc): ?>
                        <option value="<?php echo htmlspecialchars($loc); ?>" 
                                <?php echo ($ad['location']==$loc) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($loc); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Two columns: Gender Tag and Room Type -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Suitable For <span class="text-red-500">*</span>
                </label>
                <select name="gender_tag" required
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="male"   <?php echo ($ad['gender_tag']=='male')   ? 'selected' : ''; ?>>👨 Only Male</option>
                    <option value="female" <?php echo ($ad['gender_tag']=='female') ? 'selected' : ''; ?>>👩 Only Female</option>
                    <option value="any"    <?php echo ($ad['gender_tag']=='any')    ? 'selected' : ''; ?>>👥 Any Gender</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Room Type <span class="text-red-500">*</span>
                </label>
                <select name="room_type" required
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="full"   <?php echo ($ad['room_type']=='full')   ? 'selected' : ''; ?>>🚪 Full Room</option>
                    <option value="shared" <?php echo ($ad['room_type']=='shared') ? 'selected' : ''; ?>>🤝 Shared Roommate</option>
                </select>
            </div>
        </div>

        <!-- Contact fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Contact Phone <span class="text-red-500">*</span>
                </label>
                <input type="text" name="contact_phone"
                       placeholder="e.g. 01700000000"
                       value="<?php echo htmlspecialchars($ad['contact_phone']); ?>"
                       required
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    WhatsApp Number <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="whatsapp"
                       placeholder="e.g. 01700000000"
                       value="<?php echo htmlspecialchars($ad['whatsapp']); ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Telegram <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="telegram"
                       placeholder="@username or link"
                       value="<?php echo htmlspecialchars($ad['telegram']); ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Facebook <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="facebook"
                       placeholder="Profile name or link"
                       value="<?php echo htmlspecialchars($ad['facebook']); ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Duration <span class="text-red-500">*</span>
                </label>
                <select name="expiry_days" required
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="7"  <?php echo ($ad['expiry_days']==7)  ? 'selected' : ''; ?>>7 Days</option>
                    <option value="15" <?php echo ($ad['expiry_days']==15) ? 'selected' : ''; ?>>15 Days</option>
                    <option value="30" <?php echo ($ad['expiry_days']==30) ? 'selected' : ''; ?>>30 Days</option>
                    <option value="45" <?php echo ($ad['expiry_days']==45) ? 'selected' : ''; ?>>45 Days</option>
                </select>
            </div>
        </div>

        <!-- Current Images -->
        <?php if (!empty($images)): ?>
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-2">
                    Current Images (<?php echo count($images); ?>/5)
                    <span class="text-accent/50 font-normal">Check to delete</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                    <?php foreach ($images as $img): ?>
                        <label class="relative group cursor-pointer">
                            <input type="checkbox" name="delete_images[]" value="<?php echo $img['id']; ?>" class="sr-only peer">
                            <img src="/uploads/<?php echo htmlspecialchars($img['image_path']); ?>"
                                 alt="Ad Image"
                                 class="w-full h-24 object-cover rounded-lg border border-borderSoft peer-checked:opacity-50 transition-opacity">
                            <div class="absolute inset-0 bg-red-500/0 peer-checked:bg-red-500/30 rounded-lg transition-colors flex items-center justify-center">
                                <span class="text-white font-semibold opacity-0 peer-checked:opacity-100 transition-opacity">Delete</span>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Upload New Images -->
        <div>
            <label class="block text-sm font-semibold text-accent/80 mb-2">
                Add More Images <span class="text-accent/50 font-normal">(1-5 total, max 2MB each)</span>
            </label>
            <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/gif,image/webp"
                   class="w-full border border-dashed border-borderSoft rounded-xl px-4 py-6 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all cursor-pointer">
            <p class="text-xs text-accent/50 mt-2">Supported: JPG, PNG, GIF, WebP. Max 5 images total, 2MB each.</p>
        </div>

        <!-- Submit Buttons -->
        <div class="flex gap-3 justify-end">
            <a href="/dashboard/index.php"
               class="px-6 py-2.5 rounded-xl border border-borderSoft text-accent font-semibold hover:bg-primaryLight transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 rounded-xl bg-primary text-white font-semibold hover:bg-primaryDark transition-colors">
                Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>

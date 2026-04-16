<?php
// =============================================
// ads/post.php
// Post a New Roommate Ad
// Only logged-in users can post ads
// =============================================

require_once '../config/db.php';

// Redirect to login if user is not logged in
if (!isLoggedIn()) {
    redirect('/auth/login.php');
}

require_once '../includes/header.php';

$error   = '';
$success = '';

// ---- Process Post Ad Form ----
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
    $user_id     = $_SESSION['user_id'];

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
        // Calculate expiry date (today + selected days)
        $expires_at = date('Y-m-d H:i:s', strtotime("+$expiry_days days"));

        // Insert the ad into the database
        $sql = "INSERT INTO ads
                    (user_id, title, description, rent, location, gender_tag, room_type,
                     contact_phone, whatsapp, telegram, facebook, expiry_days, expires_at)
                VALUES
                    ($user_id, '$title', '$description', $rent, '$location', '$gender_tag',
                     '$room_type', '$phone', '$whatsapp', '$telegram', '$facebook', $expiry_days, '$expires_at')";

        if (mysqli_query($conn, $sql)) {

            // Get the ID of the new ad
            $ad_id = mysqli_insert_id($conn);

            // ---- Handle Image Uploads ----
            // Check if any images were uploaded
            if (!empty($_FILES['images']['name'][0])) {

                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $max_size      = 2 * 1024 * 1024; // 2MB per image
                $max_images    = 5; // Maximum 5 images per ad

                // Count non-empty image uploads
                $image_count = 0;
                foreach ($_FILES['images']['name'] as $name) {
                    if (!empty($name)) $image_count++;
                }

                // Validate image count
                if ($image_count < 1) {
                    $error .= " Please upload at least 1 image.";
                } elseif ($image_count > $max_images) {
                    $error .= " You can upload a maximum of $max_images images.";
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

            $success = "Your ad has been posted successfully!";

        } else {
            $error = "Failed to post ad. Please try again.";
        }
    }
}
?>

<!-- Page heading -->
<section class="mb-6">
    <h1 class="font-heading text-3xl text-accent">Post a New Ad</h1>
    <p class="text-sm text-accent/65 mt-1">Share clear details to attract the right roommate faster.</p>
</section>

<?php if ($error): ?>
    <div class="bg-[#FEF2F2] border border-[#FECACA] text-[#B91C1C] rounded-xl px-4 py-3 mb-5 text-sm">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="bg-[#F0FDF4] border border-[#BBF7D0] text-[#15803D] rounded-xl px-4 py-3 mb-5 text-sm">
        <?php echo $success; ?>
        <a href="/index.php" class="underline font-semibold ml-2">Go to Homepage</a>
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
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
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
                      class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <!-- Two columns: Rent and Location -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <!-- Rent -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Monthly Rent (৳) <span class="text-red-500">*</span>
                </label>
                <input type="number" name="rent" min="0"
                       placeholder="e.g. 4000"
                       value="<?php echo isset($_POST['rent']) ? htmlspecialchars($_POST['rent']) : ''; ?>"
                       required
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-2">
                    Location <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <div class="space-y-3">
                    <!-- Option 1: Select from dropdown -->
                    <div>
                        <label class="text-xs font-semibold text-accent/70 mb-1 block">Select from Common Locations:</label>
                        <select name="location" id="location_select"
                                onchange="updateLocationInput(this.value)"
                                class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                            <option value="">-- Choose location --</option>
                            <?php foreach (getCommonLocations() as $loc): ?>
                                <option value="<?php echo htmlspecialchars($loc); ?>" 
                                        <?php echo (isset($_POST['location']) && $_POST['location']==$loc) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($loc); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Option 2: Manual entry or Google Maps -->
                    <div>
                        <label class="text-xs font-semibold text-accent/70 mb-1 block">Or Type Location Manually / Use Google Maps:</label>
                        <div class="flex gap-2">
                            <input type="text" id="location_input" name="location"
                                   placeholder="Type location or click Google Maps button"
                                   value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>"
                                   class="flex-1 border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                            <button type="button" onclick="openGoogleMaps()" 
                                    class="px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primaryDark transition-colors text-sm whitespace-nowrap">
                                📍 Maps
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Two columns: Gender Tag and Room Type -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            <!-- Gender Tag -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Suitable For <span class="text-red-500">*</span>
                </label>
                <select name="gender_tag" required
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="">-- Select --</option>
                    <option value="male"   <?php echo (isset($_POST['gender_tag']) && $_POST['gender_tag']=='male')   ? 'selected' : ''; ?>>👨 Only Male</option>
                    <option value="female" <?php echo (isset($_POST['gender_tag']) && $_POST['gender_tag']=='female') ? 'selected' : ''; ?>>👩 Only Female</option>
                    <option value="any"    <?php echo (isset($_POST['gender_tag']) && $_POST['gender_tag']=='any')    ? 'selected' : ''; ?>>👥 Any Gender</option>
                </select>
            </div>

            <!-- Room Type -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Room Type <span class="text-red-500">*</span>
                </label>
                <select name="room_type" required
                        class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                    <option value="">-- Select --</option>
                    <option value="full"   <?php echo (isset($_POST['room_type']) && $_POST['room_type']=='full')   ? 'selected' : ''; ?>>🚪 Full Room</option>
                    <option value="shared" <?php echo (isset($_POST['room_type']) && $_POST['room_type']=='shared') ? 'selected' : ''; ?>>🤝 Shared Roommate</option>
                </select>
            </div>
        </div>

        <!-- Contact fields -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

            <!-- Phone -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Contact Phone <span class="text-red-500">*</span>
                </label>
                <input type="text" name="contact_phone"
                       placeholder="e.g. 01700000000"
                       value="<?php echo isset($_POST['contact_phone']) ? htmlspecialchars($_POST['contact_phone']) : ''; ?>"
                       required
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <!-- WhatsApp (Optional) -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    WhatsApp Number <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="whatsapp"
                       placeholder="e.g. 01700000000"
                       value="<?php echo isset($_POST['whatsapp']) ? htmlspecialchars($_POST['whatsapp']) : ''; ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <!-- Telegram (Optional) -->
            <div>
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Telegram <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="telegram"
                       placeholder="e.g. username or t.me/username"
                       value="<?php echo isset($_POST['telegram']) ? htmlspecialchars($_POST['telegram']) : ''; ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>

            <!-- Facebook (Optional) -->
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-sm font-semibold text-accent/80 mb-1">
                    Facebook Profile <span class="text-accent/50 font-normal">(optional)</span>
                </label>
                <input type="text" name="facebook"
                       placeholder="e.g. facebook.com/yourprofile or yourprofile"
                       value="<?php echo isset($_POST['facebook']) ? htmlspecialchars($_POST['facebook']) : ''; ?>"
                       class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
            </div>
        </div>

        <!-- Ad Expiry -->
        <div>
            <label class="block text-sm font-semibold text-accent/80 mb-1">
                Ad Duration <span class="text-red-500">*</span>
            </label>
            <select name="expiry_days" required
                    class="w-full sm:w-64 border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primaryLight focus:border-primary transition-all">
                <option value="7"  <?php echo (isset($_POST['expiry_days']) && $_POST['expiry_days']=='7')  ? 'selected' : ''; ?>>7 Days</option>
                <option value="15" <?php echo (isset($_POST['expiry_days']) && $_POST['expiry_days']=='15') ? 'selected' : ''; ?>>15 Days</option>
                <option value="30" <?php echo (!isset($_POST['expiry_days']) || $_POST['expiry_days']=='30') ? 'selected' : ''; ?>>30 Days (Recommended)</option>
                <option value="45" <?php echo (isset($_POST['expiry_days']) && $_POST['expiry_days']=='45') ? 'selected' : ''; ?>>45 Days (Maximum)</option>
            </select>
        </div>

        <!-- Image Upload -->
        <div>
            <label class="block text-sm font-semibold text-accent/80 mb-1">
                Room Photos <span class="text-accent/50 font-normal">(optional, max 5 images, 2MB each)</span>
            </label>
            <input type="file" name="images[]" multiple accept="image/*"
                   class="w-full border border-borderSoft rounded-xl px-4 py-2.5 text-sm bg-white">
            <p class="text-xs text-accent/50 mt-1">Accepted: JPG, PNG, GIF, WebP</p>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-3 flex-wrap pt-1">
            <button type="submit"
                    class="bg-primary text-white px-8 py-2.5 rounded-xl hover:bg-primaryDark font-semibold text-sm transition-colors">
                Post Ad
            </button>
            <a href="/index.php"
               class="bg-primaryLight text-primary px-6 py-2.5 rounded-xl hover:bg-[#EAE6FF] font-semibold text-sm transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBXnrPhMiEDrxHqVgFQ5r_jXLQQNVL6TIo"></script>
<script>
    let googleMap;
    let marker;

    function updateLocationInput(value) {
        document.getElementById('location_input').value = value;
    }

    function openGoogleMaps() {
        // Create modal for map
        const modal = document.createElement('div');
        modal.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:1000;';
        
        const container = document.createElement('div');
        container.style.cssText = 'background:white;border-radius:12px;width:90%;max-width:600px;height:500px;display:flex;flex-direction:column;box-shadow:0 10px 30px rgba(26, 26, 46, 0.08);';
        
        const header = document.createElement('div');
        header.style.cssText = 'padding:16px;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;';
        header.innerHTML = '<h2 style="margin:0;font-size:18px;font-weight:600;color:#1a1a2e;">Select Location on Map</h2><button onclick="this.closest(\'[data-modal]\').remove()" style="background:none;border:none;font-size:24px;cursor:pointer;color:#999;">×</button>';
        
        const mapContainer = document.createElement('div');
        mapContainer.style.cssText = 'flex:1;min-height:0;';
        
        const footer = document.createElement('div');
        footer.style.cssText = 'padding:16px;border-top:1px solid #f3f4f6;display:flex;gap:8px;justify-content:flex-end;';
        footer.innerHTML = '<button onclick="this.closest(\'[data-modal]\').remove()" style="padding:10px 24px;border:1px solid #f3f4f6;border-radius:9px;cursor:pointer;background:white;color:#1a1a2e;font-weight:600;">Cancel</button><button onclick="selectMapLocation()" style="padding:10px 24px;border-radius:9px;cursor:pointer;background:#634c9f;color:white;border:none;font-weight:600;">Select Location</button>';
        
        container.appendChild(header);
        container.appendChild(mapContainer);
        container.appendChild(footer);
        modal.setAttribute('data-modal', 'true');
        modal.appendChild(container);
        document.body.appendChild(modal);
        
        // Initialize map (Dhaka center)
        googleMap = new google.maps.Map(mapContainer, {
            zoom: 13,
            center: { lat: 23.8103, lng: 90.4125 }
        });
        
        // Add marker on click
        googleMap.addListener('click', (e) => {
            if (marker) marker.setMap(null);
            marker = new google.maps.Marker({
                position: e.latLng,
                map: googleMap
            });
            
            // Get address from coordinates
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: e.latLng }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const address = results[0].formatted_address;
                    marker.setTitle(address);
                }
            });
        });
    }

    function selectMapLocation() {
        if (marker) {
            const geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: marker.getPosition() }, (results, status) => {
                if (status === 'OK' && results[0]) {
                    const address = results[0].formatted_address;
                    document.getElementById('location_input').value = address;
                }
                document.querySelector('[data-modal]')?.remove();
            });
        } else {
            alert('Please click on the map to select a location');
        }
    }
</script>

<?php require_once '../includes/footer.php'; ?>

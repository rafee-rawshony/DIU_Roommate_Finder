<?php
// =============================================
// admin/index.php
// Admin Panel - View and manage all ads
// Only accessible by admin users
// =============================================

require_once '../config/db.php';

// Check if user is logged in AND is admin
if (!isLoggedIn() || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    // Not an admin - redirect to homepage
    redirect('/index.php');
}

require_once '../includes/header.php';

// Count stats for overview
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM users"))['c'];
$total_ads   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM ads"))['c'];
$active_ads  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM ads WHERE expires_at > NOW()"))['c'];
$expired_ads = $total_ads - $active_ads;

// Fetch all ads (newest first) with owner info
$sql = "SELECT ads.*, users.name AS owner_name, users.email AS owner_email
        FROM ads
        JOIN users ON ads.user_id = users.id
        ORDER BY ads.created_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!-- Admin Page Heading -->
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-800">⚙️ Admin Panel</h1>
    <p class="text-gray-500 text-sm mt-1">Manage all ads and users</p>
</div>

<!-- Stats Overview Cards -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">

    <div class="bg-white rounded-xl shadow p-5 text-center">
        <p class="text-3xl font-bold text-green-600"><?php echo $total_users; ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Users</p>
    </div>

    <div class="bg-white rounded-xl shadow p-5 text-center">
        <p class="text-3xl font-bold text-blue-600"><?php echo $total_ads; ?></p>
        <p class="text-sm text-gray-500 mt-1">Total Ads</p>
    </div>

    <div class="bg-white rounded-xl shadow p-5 text-center">
        <p class="text-3xl font-bold text-green-600"><?php echo $active_ads; ?></p>
        <p class="text-sm text-gray-500 mt-1">Active Ads</p>
    </div>

    <div class="bg-white rounded-xl shadow p-5 text-center">
        <p class="text-3xl font-bold text-red-500"><?php echo $expired_ads; ?></p>
        <p class="text-sm text-gray-500 mt-1">Expired Ads</p>
    </div>

</div>

<!-- All Ads Table -->
<div class="bg-white rounded-xl shadow overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">All Ads</h2>
    </div>

    <?php if (mysqli_num_rows($result) == 0): ?>
        <div class="p-10 text-center text-gray-400">No ads posted yet.</div>
    <?php else: ?>

        <!-- Scrollable table for mobile -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">ID</th>
                        <th class="px-4 py-3 text-left">Title</th>
                        <th class="px-4 py-3 text-left">Posted By</th>
                        <th class="px-4 py-3 text-left">Location</th>
                        <th class="px-4 py-3 text-left">Rent</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Expires</th>
                        <th class="px-4 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">

                    <?php while ($ad = mysqli_fetch_assoc($result)): ?>
                        <?php $is_active = (strtotime($ad['expires_at']) > time()); ?>
                        <tr class="hover:bg-gray-50">

                            <td class="px-4 py-3 text-gray-500">#<?php echo $ad['id']; ?></td>

                            <td class="px-4 py-3">
                                <a href="/ads/details.php?id=<?php echo $ad['id']; ?>"
                                   class="text-green-600 hover:underline font-medium">
                                    <?php echo htmlspecialchars(substr($ad['title'], 0, 30)); ?>
                                    <?php echo strlen($ad['title']) > 30 ? '...' : ''; ?>
                                </a>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                <?php echo htmlspecialchars($ad['owner_name']); ?>
                                <br>
                                <span class="text-xs text-gray-400">
                                    <?php echo htmlspecialchars($ad['owner_email']); ?>
                                </span>
                            </td>

                            <td class="px-4 py-3 text-gray-600">
                                <?php echo htmlspecialchars($ad['location']); ?>
                            </td>

                            <td class="px-4 py-3 font-semibold text-green-700">
                                ৳<?php echo number_format($ad['rent']); ?>
                            </td>

                            <td class="px-4 py-3">
                                <?php if ($is_active): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">Active</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-100 text-red-600 rounded-full text-xs font-medium">Expired</span>
                                <?php endif; ?>
                            </td>

                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?php echo date('d M Y', strtotime($ad['expires_at'])); ?>
                            </td>

                            <td class="px-4 py-3">
                                <div class="flex gap-2 flex-wrap">
                                    <a href="/ads/edit.php?id=<?php echo $ad['id']; ?>"
                                       class="text-xs bg-blue-100 text-blue-600 px-3 py-1 rounded-lg hover:bg-blue-200 font-medium">
                                        Edit
                                    </a>
                                    <a href="/ads/delete.php?id=<?php echo $ad['id']; ?>"
                                       onclick="return confirm('Delete this ad?')"
                                       class="text-xs bg-red-100 text-red-600 px-3 py-1 rounded-lg hover:bg-red-200 font-medium">
                                        Delete
                                    </a>
                                </div>
                            </td>

                        </tr>
                    <?php endwhile; ?>

                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

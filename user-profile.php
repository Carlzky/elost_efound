<?php
session_start();
include 'config/db.php';

/* =========================
   GET CURRENT LOGGED USER
========================= */

// change this depending on your login session
$current_user_id = $_SESSION['user_id'] ?? 1;

/* =========================
   GET PROFILE USER ID
========================= */

$profile_user_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : $current_user_id;

/* =========================
   FETCH PROFILE USER
========================= */

$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE id = ?
");

$stmt->bind_param("i", $profile_user_id);
$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows > 0){
    $profile_user = $result->fetch_assoc();
}else{

    // dummy fallback user
    $profile_user = [
        'id' => 0,
        'username' => 'Sample User',
        'email' => 'sample@email.com',
        'full_name' => 'Sample User'
    ];
}

/* =========================
   PROFILE AVATAR
========================= */

// your database currently has NO profile_picture column
// so use default avatar for now

$avatar = 'images/default-avatar.png';
$my_avatar = 'images/default-avatar.png';

/* =========================
   ITEMS CLAIMED
========================= */

$stmt_claimed = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM claims
    WHERE claimant_user_id = ?
    AND claim_status = 'Approved'
");

$stmt_claimed->bind_param("i", $profile_user_id);
$stmt_claimed->execute();

$items_claimed =
    $stmt_claimed
    ->get_result()
    ->fetch_assoc()['total'] ?? 0;

/* =========================
   ITEMS RETURNED
========================= */

$stmt_returned = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM found_items
    WHERE user_id = ?
    AND status = 'Claimed'
");

$stmt_returned->bind_param("i", $profile_user_id);
$stmt_returned->execute();

$items_returned =
    $stmt_returned
    ->get_result()
    ->fetch_assoc()['total'] ?? 0;

/* =========================
   REPORTS MADE
========================= */

$stmt_reports = $conn->prepare("
    SELECT
        (
            (SELECT COUNT(*) FROM lost_items WHERE user_id = ?)
            +
            (SELECT COUNT(*) FROM found_items WHERE user_id = ?)
        ) AS total
");

$stmt_reports->bind_param(
    "ii",
    $profile_user_id,
    $profile_user_id
);

$stmt_reports->execute();

$reports_made =
    $stmt_reports
    ->get_result()
    ->fetch_assoc()['total'] ?? 0;

/* =========================
   RECENT POSTS
========================= */

$recent_posts_query = "

    SELECT
        lost_id AS item_id,
        item_name,
        location_lost AS location,
        item_image,
        created_at,
        'Lost' AS item_type
    FROM lost_items
    WHERE user_id = ?

    UNION

    SELECT
        found_id AS item_id,
        item_name,
        location_found AS location,
        item_image,
        created_at,
        'Found' AS item_type
    FROM found_items
    WHERE user_id = ?

    ORDER BY created_at DESC
    LIMIT 5

";

$recent_posts = $conn->prepare($recent_posts_query);

$recent_posts->bind_param(
    "ii",
    $profile_user_id,
    $profile_user_id
);

$recent_posts->execute();

$recent_posts = $recent_posts->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Profile - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/user-profile_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

</head>
<body>

<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR DRAWER -->
<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo-icon">🔍</div>
        <div class="logo-text">
            E-LOST <span class="txt-highlight">MOH</span><br>
            E-FOUND <span class="txt-highlight">KOH</span>
        </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg></span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>
                <span class="nav-text">Claims</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="notif.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg></span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="messages.php">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()">
                <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- TOP BAR -->
<div class="top-bar">
    <button class="hamburger-btn" onclick="openSidebar()">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <div class="top-logo-icon">🔍</div>

    <div class="top-bar-right">
        <a href="notif.php" class="notif-bell-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </a>
        <a href="profile.php">
            <img class="avatar-sm" src="<?php echo htmlspecialchars($my_avatar); ?>" alt="My Profile">
        </a>
    </div>
</div>

<!-- PAGE CONTENT -->
<div class="page-content">
    <div class="page-label">Community</div>
    <h1 class="page-title">User Profile</h1>

    <!-- PROFILE HERO -->
    <div class="profile-hero">
        <div class="profile-hero-left">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture" class="profile-avatar">
            <div>
                <div class="profile-name"><?php echo htmlspecialchars($profile_user['username']); ?></div>
                <div class="profile-email"><?php echo htmlspecialchars($profile_user['email']); ?></div>
            </div>
        </div>
        <?php if($profile_user_id !== $current_user_id): ?>
        <a href="messages.php?to=<?php echo $profile_user_id; ?>" class="message-btn">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Message
        </a>
        <?php endif; ?>
    </div>

    <!-- STATS ROW -->
    <div class="stats-row">
        <div class="stat-cell">
            <span class="num"><?php echo $items_claimed; ?></span>
            <span class="lbl">Items Claimed</span>
        </div>
        <div class="stat-cell">
            <span class="num"><?php echo $items_returned; ?></span>
            <span class="lbl">Items Returned</span>
        </div>
        <div class="stat-cell">
            <span class="num"><?php echo $reports_made; ?></span>
            <span class="lbl">Reports Made</span>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid">

        <!-- RECENT POSTS -->
        <div class="panel-card">
            <h2>Recent Posts</h2>
            <?php if($recent_posts->num_rows > 0): while($row = $recent_posts->fetch_assoc()):
                $img = !empty($row['item_image']) ? $row['item_image'] : 'uploads/default.png';
            ?>
            <div class="recent-item">
                <img src="<?php echo htmlspecialchars($img); ?>" alt="">
                <div class="recent-item-info">
                    <h4>
                        <?php echo htmlspecialchars($row['item_name']); ?>
                        <span class="badge <?php echo $row['item_type'] === 'Lost' ? 'badge-lost' : 'badge-found'; ?>">
                            <?php echo $row['item_type']; ?>
                        </span>
                    </h4>
                    <p><?php echo htmlspecialchars($row['location']); ?> · <?php echo date('M j, Y', strtotime($row['created_at'])); ?></p>
                </div>
            </div>
            <?php endwhile; else: ?>
            <p style="color:gray;font-size:13px;">No posts yet.</p>
            <?php endif; ?>
        </div>

        <!-- ABOUT -->
        <div class="panel-card">
            <h2>About</h2>
            <div class="info-row">
                <div class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></div>
                <div>
                    <div class="info-label">Username</div>
                    <div class="info-value"><?php echo htmlspecialchars($profile_user['username']); ?></div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
                <div>
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($profile_user['email']); ?></div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <div>
                    <div class="info-label">Total Activity</div>
                    <div class="info-value"><?php echo ($items_claimed + $items_returned + $reports_made); ?> interactions</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></div>
                <div>
                    <div class="info-label">Community Standing</div>
                    <div class="info-value green">
                        <?php
                            $total = $items_claimed + $items_returned + $reports_made;
                            if($total >= 20) echo 'Active Contributor';
                            elseif($total >= 10) echo 'Regular Member';
                            elseif($total >= 3) echo 'New Member';
                            else echo 'Just Joined';
                        ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- LOGOUT MODAL -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="logout-btn" onclick="window.location.href='logout.php'">Confirm</button>
        </div>
    </div>
</div>

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('open');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('open');
}

function openLogoutModal() {
    closeSidebar();
    document.getElementById('logoutOverlay').style.display = 'flex';
}

function closeLogoutModal() {
    document.getElementById('logoutOverlay').style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeSidebar(); closeLogoutModal(); }
});
</script>

</body>
</html>
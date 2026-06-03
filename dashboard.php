<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include "config/db.php";

if(!isset($_SESSION['username'])){
    header("Location: registration.php");
    exit();
}

// Ensure user_id exists in session, otherwise fetch it
if (!isset($_SESSION['user_id'])) {
    $username_check = $_SESSION['username'];
    $stmt_id = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_id->bind_param("s", $username_check);
    $stmt_id->execute();
    $result_id = $stmt_id->get_result();
    if ($row_id = $result_id->fetch_assoc()) {
        $_SESSION['user_id'] = $row_id['id'];
    } else {
        header("Location: logout.php");
        exit();
    }
}

$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Fetch the user's profile image for the top bar avatar
$stmt_profile = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$profile_res = $stmt_profile->get_result();
$profile_data = $profile_res->fetch_assoc();

// Set the avatar path with your custom default image fallback
$avatar = !empty($profile_data['profile_image']) ? $profile_data['profile_image'] : 'assets/img/defaultProfile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - E-LOST KOH, E-FOUND MOH</title>

<link rel="stylesheet" href="assets/css/dashboard_style.css?v=2">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

</head>
<body>

<div class="sidebar" id="sidebar">

    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <div class="logo-section" onclick="openSidebarIfCollapsed()">
        <div class="logo-icon">🔍</div>
            <div class="logo-text">
                E-LOST <span class="txt-highlight">MOH</span><br>
                E-FOUND <span class="txt-highlight">KOH</span>
            </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item active">
            <a href="dashboard.php" data-tooltip="Dashboard">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php" data-tooltip="Report Item">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php" data-tooltip="Browse Items">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php" data-tooltip="My Claims">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </span>
                <span class="nav-text">Claims</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="notif.php" data-tooltip="Notifications">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="messages.php" data-tooltip="Messages">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()" data-tooltip="Logout">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="main-content">
    <div class="top-bar">
        <div class="welcome">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user); ?>!</p>
        </div>
        <div class="user-profile">
            <a href="notif.php" class="notif-bell-btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </a>

            <a href="profile.php" class="avatar-link">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture" class="avatar">
            </a>

        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Lost Items</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM lost_items WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Total Found Items</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM found_items WHERE user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Claims Pending</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM claims WHERE claim_status='Pending' AND claimant_user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <div class="panel-card">
            <h2>Recent Activity</h2>
            <?php
            $sql = "SELECT * FROM report_history WHERE user_id = ? ORDER BY action_date DESC LIMIT 5";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
            ?>
                <div class="activity-item">
                    <div>
                        <h4><?php echo htmlspecialchars($row['action_done']); ?></h4>
                        <span><?php echo htmlspecialchars($row['action_date']); ?></span>
                    </div>
                </div>
            <?php
                endwhile;
            else:
            ?>
                <p style="color:gray;font-size:14px;">No recent activity found.</p>
            <?php endif; ?>
        </div>

        <div class="panel-card">
            <h2>Recently Posted Items</h2>
            <?php
            $sql = "
            SELECT item_name, item_image, location_lost AS location, created_at, 'Lost' AS item_type
            FROM lost_items
            WHERE user_id = ?
            UNION ALL
            SELECT item_name, item_image, location_found AS location, created_at, 'Found' AS item_type
            FROM found_items
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 3
            ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0):
                while($row = $result->fetch_assoc()):
                    $image = !empty($row['item_image']) ? $row['item_image'] : 'uploads/default.png';
            ?>
            <div class="recent-item">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="">
                <div class="recent-item-info">
                    <h4><?php echo htmlspecialchars($row['item_name']); ?></h4>
                    <p>
                        <?php echo htmlspecialchars($row['item_type']); ?> ·
                        <?php echo htmlspecialchars($row['location']); ?>
                    </p>
                </div>
            </div>
            <?php 
                endwhile;
            else: 
            ?>
            <p style="color:gray;font-size:14px;">No recent items found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="logout-btn" onclick="confirmLogout()">Confirm</button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
}

document.querySelector('.logo-section').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');

    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
});

function openLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "none";
}

function confirmLogout(){
    window.location.href = "actions/logout.php";
}

function openSidebarIfCollapsed() {
    const sidebar = document.getElementById('sidebar');

    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
}
</script>

</body>
</html>
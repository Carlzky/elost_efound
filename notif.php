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

$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Notifications - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/notif_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
   
</head>
<body>

<!-- ======================== SIDEBAR ======================== -->
<div class="sidebar">

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
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </span>
                <span class="nav-text">My Claims</span>
            </a>
        </li>
        <li class="nav-item active">
            <a href="notif.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="messages.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<!-- ======================== MAIN CONTENT ======================== -->
<div class="main-content">

    <div class="top-bar">
        <div class="search-wrapper">
            <svg class="search-icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" placeholder="Search notifications...">
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
        <img src="images/default-avatar.png" alt="Profile Picture" class="avatar">
    </a>

</div>
    </div>

    <h1 class="page-title">Notifications</h1>
    <p class="page-subtitle">Stay updated on your items and claims.</p>

    <div class="page-wrapper">
        <div class="notification-card">

            <div class="notification-header">
                <div class="notification-heading">Recent Notifications</div>
                <button class="mark-read-btn" onclick="markAllAsRead()">Mark all as read</button>
            </div>

            <div class="notification-list" id="notificationList">

                <?php
                $sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if($result->num_rows > 0):
                    while($row = $result->fetch_assoc()):
                        $isRead = ($row['is_read'] == 'Yes') ? 'read' : '';
                        $time = date('M d, Y • g:i A', strtotime($row['created_at']));
                ?>
                <div class="notification-item <?php echo $isRead; ?>" onclick="openNotification(this, <?php echo $row['notification_id']; ?>)">
                    <div class="notification-left">
                        <div class="notification-bell">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#7A7A7A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <h3><?php echo htmlspecialchars($row['notification_text']); ?></h3>
                            <p><?php echo $time; ?></p>
                        </div>
                    </div>
                    <?php if(!$row['is_read']): ?>
                    <div class="notification-status"></div>
                    <?php endif; ?>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                    <!-- Fallback static notifications if no DB data -->
                    <div class="notification-item" onclick="openNotification(this, 0)">
                        <div class="notification-left">
                            <div class="notification-bell">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#7A7A7A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                            </div>
                            <div class="notification-content">
                                <h3>No notifications yet.</h3>
                                <p>You're all caught up!</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- ======================== LOGOUT MODAL ======================== -->
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
    function openNotification(element, notifId) {
        element.classList.add('read');
        if (notifId > 0) {
            fetch('mark_notif_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'notif_id=' + notifId
            });
        }
    }

    function markAllAsRead() {
        document.querySelectorAll('.notification-item').forEach(n => n.classList.add('read'));
        fetch('mark_notif_read.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'mark_all=1'
        });
    }

    function openLogoutModal() {
        document.getElementById('logoutOverlay').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutOverlay').style.display = 'none';
    }

    function confirmLogout() {
        window.location.href = 'logout.php';
    }
</script>

</body>
</html>
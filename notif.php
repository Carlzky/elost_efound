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

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #1F5D4A;
            --primary-dark: #143F32;
            --gold: #F1B846;
            --primary-green: #1F5D4A;
            --light-green: #BBC34A;
            --dark-gray: #68735C;
            --bg-gray: #F4F4F4;
            --pure-white: #FFFFFF;
            --text-dark: #1A1A1A;
            --border-light: #E4E4E4;
            --danger: #E74C3C;
            --sidebar-width: 240px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gray);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* ========================
           SIDEBAR
        ========================= */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-green);
            color: white;
            padding: 24px;
            position: fixed;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border: 2px solid var(--gold);
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 26px;
            box-shadow:
                0 12px 30px rgba(0,0,0,0.35),
                inset 0 3px 6px rgba(255,255,255,0.18);
            transition:
                transform 0.7s cubic-bezier(0.2,0.8,0.2,1),
                box-shadow 0.7s cubic-bezier(0.2,0.8,0.2,1);
        }

        .logo-icon:hover {
            transform: scale(1.08) translateY(-5px) rotate(4deg);
            box-shadow:
                0 18px 40px rgba(0,0,0,0.45),
                inset 0 3px 6px rgba(255,255,255,0.25);
        }

        .logo-text {
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            line-height: 1.3;
            font-weight: 600;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            height: 100%;
        }

        .nav-item a {
            text-decoration: none;
            color: rgba(255,255,255,0.82);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 13px 16px;
            border-radius: 10px;
            transition: 0.25s ease;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item a:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }

        .nav-item.active a {
            background: rgba(255,255,255,0.12);
            color: white;
            font-weight: 500;
        }

        .nav-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            opacity: 0.8;
        }

        /* ========================
           MAIN CONTENT
        ========================= */
        .main-content {
            margin-left: var(--sidebar-width);
            width: 100%;
            padding: 42px;
        }

        /* ========================
           TOP BAR
        ========================= */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .search-wrapper {
            position: relative;
            width: 300px;
            display: flex;
            align-items: center;
        }

        .search-wrapper input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: 1px solid #E0E0E0;
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            background: var(--pure-white);
            outline: none;
        }

        .search-icon-svg {
            position: absolute;
            left: 14px;
            color: #888;
            pointer-events: none;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .notif-bell-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            color: #555;
            transition: transform 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .notif-bell-btn:hover { transform: scale(1.08); }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #DDD;
        }

        /* ========================
           PAGE CONTENT
        ========================= */
        .page-title {
            font-family: 'Poppins', sans-serif;
            font-size: 30px;
            margin-bottom: 4px;
        }

        .page-subtitle {
            color: #7A7A7A;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .page-wrapper {
            width: 100%;
            max-width: 760px;
        }

        /* ========================
           NOTIFICATION CARD
        ========================= */
        .notification-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 10px 28px rgba(0,0,0,0.04);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .notification-heading {
            font-family: 'Poppins', sans-serif;
            font-size: 17px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .mark-read-btn {
            border: none;
            background: var(--primary-green);
            color: white;
            padding: 10px 16px;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.25s ease;
        }

        .mark-read-btn:hover { background: var(--primary-dark); }

        .notification-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        /* ========================
           NOTIFICATION ITEM
        ========================= */
        .notification-item {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 18px;
            border: 1px solid var(--border-light);
            border-radius: 14px;
            background: #FCFCFC;
            transition: all 0.25s ease;
            cursor: pointer;
            animation: fadeIn 0.4s ease;
        }

        .notification-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        .notification-item.read {
            opacity: 0.65;
            background: #F8F8F8;
        }

        .notification-item.read .notification-status { display: none; }

        .notification-left {
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .notification-bell {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #F1F5F3;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-bell svg {
            width: 18px;
            height: 18px;
            fill: #7A7A7A;
        }

        .notification-content h3 {
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .notification-content p {
            font-size: 11px;
            letter-spacing: 0.3px;
            color: #8A8A8A;
        }

        .notification-status {
            width: 10px;
            height: 10px;
            background: #6BCB4D;
            border-radius: 50%;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #888;
            font-size: 14px;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ========================
           LOGOUT MODAL
        ========================= */
        .logout-overlay {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(6px);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .logout-modal {
            background: white;
            padding: 32px;
            border-radius: 20px;
            text-align: center;
            width: 320px;
            border: 1px solid #EAEAEA;
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
            transform: scale(0.85);
            opacity: 0;
            animation: popIn 0.25s forwards;
        }

        @keyframes popIn {
            to { transform: scale(1); opacity: 1; }
        }

        .logout-modal h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 20px;
            margin-bottom: 10px;
            color: var(--primary-green);
        }

        .logout-modal p {
            font-size: 14px;
            color: #7A7A7A;
            margin-bottom: 24px;
        }

        .logout-buttons { display: flex; gap: 12px; }

        .cancel-btn {
            flex: 1; padding: 12px;
            border: 1px solid #E0E0E0;
            border-radius: 10px;
            background: #F4F4F4;
            font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 500;
            cursor: pointer; transition: 0.2s;
        }

        .cancel-btn:hover { background: #E8E8E8; }

        .logout-btn {
            flex: 1; padding: 12px;
            border: none; border-radius: 10px;
            background: var(--primary-green);
            color: white;
            font-family: 'Inter', sans-serif;
            font-size: 14px; font-weight: 600;
            cursor: pointer; transition: 0.2s;
        }

        .logout-btn:hover { background: var(--primary-dark); }

        /* ========================
           RESPONSIVE
        ========================= */
        @media (max-width: 768px) {
            .sidebar { width: 78px; padding: 20px 12px; }
            .logo-text, .nav-text { display: none; }
            .nav-item a { justify-content: center; padding: 14px; }
            .main-content { margin-left: 78px; padding: 22px; }
            .top-bar { flex-direction: column; gap: 14px; align-items: stretch; }
            .search-wrapper { width: 100%; }
            .page-title { font-size: 24px; }
            .notification-header { flex-direction: column; align-items: flex-start; gap: 14px; }
        }
    </style>
</head>
<body>

<!-- ======================== SIDEBAR ======================== -->
<div class="sidebar">
    <div class="logo-section">
        <div class="logo-icon">🔍</div>
        <div class="logo-text">E-LOST MOH<br>E-FOUND KOH</div>
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
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            </a>
            <div class="avatar"></div>
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
                        $isRead = $row['is_read'] ? 'read' : '';
                        $time = date('M d, Y • g:i A', strtotime($row['created_at']));
                ?>
                <div class="notification-item <?php echo $isRead; ?>" onclick="openNotification(this, <?php echo $row['notif_id']; ?>)">
                    <div class="notification-left">
                        <div class="notification-bell">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#7A7A7A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <h3><?php echo htmlspecialchars($row['message']); ?></h3>
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
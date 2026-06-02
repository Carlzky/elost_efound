<?php
session_start();

include "config/db.php";

// ── Handle AJAX mark-as-read requests (before any HTML output) ──
if (isset($_POST['ajax_action']) && isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    $uid = $_SESSION['user_id'];

    if ($_POST['ajax_action'] === 'mark_all') {
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 'Yes' WHERE user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        echo json_encode(['success' => true]);

    } elseif ($_POST['ajax_action'] === 'mark_one' && isset($_POST['notif_id'])) {
        $nid = intval($_POST['notif_id']);
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 'Yes' WHERE notification_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $nid, $uid);
        $stmt->execute();
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit();
}

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
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
        header("Location: actions/logout.php");
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

$avatar = !empty($profile_data['profile_image']) ? $profile_data['profile_image'] : 'assets/img/defaultProfile.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Notifications - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/notif_style.css?v=2">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>
        /* ── Notification Detail Modal ── */
        .notif-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: overlayFadeIn 0.2s ease;
        }
        .notif-modal-overlay.active {
            display: flex;
        }
        @keyframes overlayFadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        .notif-modal {
            background: #fff;
            border-radius: 16px;
            padding: 36px 32px 28px;
            max-width: 460px;
            width: 90%;
            box-shadow: 0 24px 60px rgba(0,0,0,0.18);
            position: relative;
            animation: modalSlideUp 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes modalSlideUp {
            from { transform: translateY(30px) scale(0.96); opacity: 0; }
            to   { transform: translateY(0)    scale(1);    opacity: 1; }
        }

        .notif-modal-close {
            position: absolute;
            top: 14px;
            right: 16px;
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 22px;
            line-height: 1;
            padding: 4px 8px;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
        }
        .notif-modal-close:hover {
            background: #f0f0f0;
            color: #333;
        }

        .notif-modal-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #f0f4ff;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 18px;
        }
        .notif-modal-icon svg {
            width: 26px;
            height: 26px;
            stroke: #4f6ef7;
        }

        .notif-modal-title {
            font-family: 'Poppins', sans-serif;
            font-size: 17px;
            font-weight: 600;
            color: #1a1a2e;
            margin: 0 0 10px;
            line-height: 1.4;
        }

        .notif-modal-time {
            font-size: 13px;
            color: #999;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .notif-modal-time svg {
            width: 14px;
            height: 14px;
            stroke: #bbb;
        }

        .notif-modal-divider {
            border: none;
            border-top: 1px solid #f0f0f0;
            margin: 16px 0;
        }

        .notif-modal-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .notif-modal-badge.unread {
            background: #fff3cd;
            color: #856404;
        }
        .notif-modal-badge.read {
            background: #d1f7e0;
            color: #166534;
        }

        .notif-modal-footer {
            margin-top: 24px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .notif-modal-dismiss {
            padding: 10px 22px;
            border-radius: 8px;
            border: 1.5px solid #e0e0e0;
            background: #fff;
            color: #555;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.15s;
        }
        .notif-modal-dismiss:hover { background: #f5f5f5; }

        .notif-modal-mark-read {
            padding: 10px 22px;
            border-radius: 8px;
            border: none;
            background: #4f6ef7;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
        }
        .notif-modal-mark-read:hover { background: #3a57d4; transform: translateY(-1px); }
        .notif-modal-mark-read:disabled {
            background: #d1f7e0;
            color: #166534;
            cursor: default;
            transform: none;
        }
    </style>
</head>
<body>

<!-- ======================== SIDEBAR ======================== -->
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
                <span class="nav-text">Claims</span>
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
            <input type="text" id="searchInput" placeholder="Search notifications..." oninput="filterNotifications()">
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
                        // is_read persisted from DB — already read stays read on refresh
                        $isRead    = ($row['is_read'] == 'Yes') ? 'read' : '';
                        $time      = date('M d, Y • g:i A', strtotime($row['created_at']));
                        $timeIso   = htmlspecialchars($row['created_at']);
                        $notifText = htmlspecialchars($row['notification_text']);
                        $notifId   = intval($row['notification_id']);
                ?>
                <div class="notification-item <?php echo $isRead; ?>"
                     data-id="<?php echo $notifId; ?>"
                     data-text="<?php echo $notifText; ?>"
                     data-time="<?php echo htmlspecialchars($time); ?>"
                     data-read="<?php echo ($row['is_read'] == 'Yes') ? '1' : '0'; ?>"
                     onclick="openNotifModal(this)">
                    <div class="notification-left">
                        <div class="notification-bell">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#7A7A7A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                        </div>
                        <div class="notification-content">
                            <h3><?php echo $notifText; ?></h3>
                            <p><?php echo $time; ?></p>
                        </div>
                    </div>
                    <?php if($row['is_read'] != 'Yes'): ?>
                    <div class="notification-status" id="dot-<?php echo $notifId; ?>"></div>
                    <?php endif; ?>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                    <div class="notification-item read">
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

<!-- ======================== NOTIFICATION DETAIL MODAL ======================== -->
<div class="notif-modal-overlay" id="notifModalOverlay" onclick="handleOverlayClick(event)">
    <div class="notif-modal" id="notifModal">
        <button class="notif-modal-close" onclick="closeNotifModal()" title="Close">&times;</button>

        <div class="notif-modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </div>

        <p class="notif-modal-title" id="modalText">—</p>

        <div class="notif-modal-time">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span id="modalTime">—</span>
        </div>

        <span class="notif-modal-badge" id="modalBadge">—</span>

        <hr class="notif-modal-divider">

        <div class="notif-modal-footer">
            <button class="notif-modal-dismiss" onclick="closeNotifModal()">Close</button>
            <button class="notif-modal-mark-read" id="modalMarkReadBtn" onclick="markCurrentAsRead()">
                ✓ Mark as Read
            </button>
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

function openSidebarIfCollapsed() {
    const sidebar = document.getElementById('sidebar');

    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
}

    let currentNotifId   = null;
    let currentNotifItem = null;

    /* ── Open notification detail modal ── */
    function openNotifModal(element) {
        const notifId = parseInt(element.dataset.id);
        const text    = element.dataset.text;
        const time    = element.dataset.time;
        const isRead  = element.dataset.read === '1';

        currentNotifId   = notifId;
        currentNotifItem = element;

        document.getElementById('modalText').textContent = text;
        document.getElementById('modalTime').textContent = time;

        const badge = document.getElementById('modalBadge');
        const btn   = document.getElementById('modalMarkReadBtn');

        if (isRead) {
            badge.textContent = '✓ Read';
            badge.className   = 'notif-modal-badge read';
            btn.textContent   = '✓ Already Read';
            btn.disabled      = true;
        } else {
            badge.textContent = '● Unread';
            badge.className   = 'notif-modal-badge unread';
            btn.textContent   = '✓ Mark as Read';
            btn.disabled      = false;
        }

        document.getElementById('notifModalOverlay').classList.add('active');
    }

    /* ── Close modal ── */
    function closeNotifModal() {
        document.getElementById('notifModalOverlay').classList.remove('active');
    }

    /* ── Close when clicking outside modal box ── */
    function handleOverlayClick(e) {
        if (e.target === document.getElementById('notifModalOverlay')) {
            closeNotifModal();
        }
    }

    /* ── ESC key closes modal ── */
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeNotifModal();
    });

    /* ── Mark current (open) notification as read ── */
    function markCurrentAsRead() {
        if (!currentNotifId || !currentNotifItem) return;

        // Update DOM immediately
        currentNotifItem.classList.add('read');
        currentNotifItem.dataset.read = '1';
        const dot = document.getElementById('dot-' + currentNotifId);
        if (dot) dot.remove();

        // Update modal badge + button
        const badge = document.getElementById('modalBadge');
        badge.textContent = '✓ Read';
        badge.className   = 'notif-modal-badge read';
        const btn = document.getElementById('modalMarkReadBtn');
        btn.textContent = '✓ Already Read';
        btn.disabled    = true;

        // Persist to DB (posts back to this same file)
        fetch('notif.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'ajax_action=mark_one&notif_id=' + currentNotifId
        });
    }

    /* ── Mark ALL as read ── */
    function markAllAsRead() {
        document.querySelectorAll('.notification-item').forEach(n => {
            n.classList.add('read');
            n.dataset.read = '1';
        });
        document.querySelectorAll('.notification-status').forEach(d => d.remove());

        fetch('notif.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body:    'ajax_action=mark_all'
        });
    }

    /* ── Search filter ── */
    function filterNotifications() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('.notification-item').forEach(item => {
            const text = (item.dataset.text || '').toLowerCase();
            item.style.display = text.includes(query) ? '' : 'none';
        });
    }

    /* ── Logout modal ── */
    function openLogoutModal()  { document.getElementById('logoutOverlay').style.display = 'flex'; }
    function closeLogoutModal() { document.getElementById('logoutOverlay').style.display = 'none'; }
    function confirmLogout()    { window.location.href = 'logout.php'; }
</script>

</body>
</html>
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
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #1F5D4A;
        --primary-dark: #143F32;
        --gold: #F1B846;
        --primary-green: #1F5D4A;
        --light-green: #BBC34A;
        --bg-gray: #F4F4F4;
        --pure-white: #FFFFFF;
        --text-dark: #1A1A1A;
        --sidebar-width: 240px;
        --approved: #4CAF50;
        --rejected: #E74C3C;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-gray);
        color: var(--text-dark);
        min-height: 100vh;
    }

    /* ========================
       SIDEBAR DRAWER
    ========================= */
    .sidebar-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(4px);
        z-index: 800;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.open {
        opacity: 1;
        pointer-events: all;
    }

    .sidebar {
        width: var(--sidebar-width);
        background: var(--primary-green);
        color: white;
        padding: 24px;
        position: fixed;
        top: 0; left: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 900;
        transform: translateX(-100%);
        transition: transform 0.32s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar.open { transform: translateX(0); }

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
        box-shadow: 0 12px 30px rgba(0,0,0,0.35), inset 0 3px 6px rgba(255,255,255,0.18);
        transition: transform 0.7s cubic-bezier(0.2,0.8,0.2,1), box-shadow 0.7s cubic-bezier(0.2,0.8,0.2,1);
    }

    .logo-icon:hover {
        transform: scale(1.08) translateY(-5px) rotate(4deg);
        box-shadow: 0 18px 40px rgba(0,0,0,0.45), inset 0 3px 6px rgba(255,255,255,0.25);
    }

    .logo-text {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 1.3;
        font-weight: 700;
        color: #FFFFFF;
    }

    .logo-text .txt-highlight { color: #BBC34A; }

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

    .nav-item a:hover { background: rgba(255,255,255,0.05); color: white; }

    .nav-item.active a {
        background: rgba(255,255,255,0.12);
        color: white;
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
       TOP BAR
    ========================= */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 18px 28px;
        background: var(--pure-white);
        border-bottom: 1px solid #EAEAEA;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .hamburger-btn {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        padding: 4px;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .hamburger-btn:hover { background: #F0F0F0; }

    .top-logo-icon {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: 2px solid var(--gold);
        border-radius: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 18px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.25), inset 0 2px 4px rgba(255,255,255,0.18);
        transition: transform 0.5s cubic-bezier(0.2,0.8,0.2,1);
    }

    .top-logo-icon:hover { transform: scale(1.08) rotate(4deg); }

    .top-bar-right {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .notif-bell-btn {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #555;
        transition: transform 0.2s;
        display: flex;
        align-items: center;
        text-decoration: none;
    }

    .notif-bell-btn:hover { transform: scale(1.08); }

    .avatar-sm {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        object-fit: cover;
        background: #DDD;
        border: 2px solid #EAEAEA;
    }

    /* ========================
       PAGE CONTENT
    ========================= */
    .page-content {
        padding: 36px 28px;
        max-width: 720px;
        margin: 0 auto;
    }

    .page-label {
        font-size: 12px;
        font-weight: 600;
        color: #9A9A9A;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 6px;
    }

    .page-title {
        font-family: 'Poppins', sans-serif;
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 24px;
    }

    /* ========================
       PROFILE HERO
    ========================= */
    .profile-hero {
        background: var(--primary-green);
        border-radius: 16px;
        padding: 28px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
    }

    .profile-hero::before {
        content: '';
        position: absolute;
        width: 240px; height: 240px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.07);
        top: -80px; right: -50px;
        pointer-events: none;
    }

    .profile-hero::after {
        content: '';
        position: absolute;
        width: 140px; height: 140px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.05);
        bottom: -50px; right: 90px;
        pointer-events: none;
    }

    .profile-hero-left {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid rgba(255,255,255,0.35);
        box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        background: rgba(255,255,255,0.2);
        flex-shrink: 0;
    }

    .profile-name {
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        font-weight: 700;
        color: #fff;
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 13px;
        color: rgba(255,255,255,0.72);
    }

    .message-btn {
        background: var(--pure-white);
        color: var(--primary-green);
        border: none;
        padding: 10px 20px;
        border-radius: 9px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: 0.2s ease;
        position: relative;
        z-index: 1;
        flex-shrink: 0;
    }

    .message-btn:hover {
        background: #F0F0F0;
        transform: translateY(-1px);
    }

    /* ========================
       STATS ROW
    ========================= */
    .stats-row {
        background: var(--pure-white);
        border-radius: 14px;
        border: 1px solid #EAEAEA;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        margin-bottom: 20px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }

    .stat-cell {
        padding: 22px 16px;
        text-align: center;
        position: relative;
    }

    .stat-cell:not(:last-child)::after {
        content: '';
        position: absolute;
        right: 0; top: 20%;
        height: 60%; width: 1px;
        background: #EAEAEA;
    }

    .stat-cell .num {
        font-family: 'Poppins', sans-serif;
        font-size: 30px;
        font-weight: 700;
        color: var(--primary-green);
        display: block;
        margin-bottom: 4px;
    }

    .stat-cell .lbl {
        font-size: 12px;
        color: #7A7A7A;
        font-weight: 500;
    }

    /* ========================
       CONTENT GRID
    ========================= */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .panel-card {
        background: var(--pure-white);
        border-radius: 14px;
        padding: 22px;
        border: 1px solid #EAEAEA;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }

    .panel-card h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--text-dark);
    }

    .recent-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F0F0F0;
    }

    .recent-item:last-child { border-bottom: none; padding-bottom: 0; }
    .recent-item:first-child { padding-top: 0; }

    .recent-item img {
        width: 46px; height: 46px;
        border-radius: 9px;
        object-fit: cover;
        background: #EEE;
        border: 1px solid #EAEAEA;
        flex-shrink: 0;
    }

    .recent-item-info h4 {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 3px;
        color: var(--text-dark);
    }

    .recent-item-info p { font-size: 11px; color: #9A9A9A; }

    .badge {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 600;
        margin-left: 5px;
    }

    .badge-lost { background: #FEE8E8; color: var(--rejected); }
    .badge-found { background: #E8F5EA; color: var(--approved); }

    /* About panel rows */
    .info-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F0F0F0;
    }

    .info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .info-row:first-child { padding-top: 0; }

    .info-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        background: #EEF5F2;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary-green);
        flex-shrink: 0;
    }

    .info-icon svg { width: 15px; height: 15px; }

    .info-label {
        font-size: 10px;
        color: #9A9A9A;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        margin-bottom: 2px;
    }

    .info-value {
        font-size: 13px;
        font-weight: 500;
        color: var(--text-dark);
    }

    .info-value.green { color: var(--approved); font-weight: 600; }

    /* ========================
       LOGOUT MODAL
    ========================= */
    .logout-overlay {
        position: fixed;
        inset: 0;
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

    @keyframes popIn { to { transform: scale(1); opacity: 1; } }

    .logout-modal h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        margin-bottom: 10px;
        color: var(--primary-green);
    }

    .logout-modal p { font-size: 14px; color: #7A7A7A; margin-bottom: 24px; }
    .logout-buttons { display: flex; gap: 12px; }

    .cancel-btn {
        flex: 1; padding: 12px;
        border: 1px solid #E0E0E0; border-radius: 10px;
        background: #F4F4F4; font-family: 'Inter', sans-serif;
        font-size: 14px; font-weight: 500; cursor: pointer; transition: 0.2s;
    }

    .cancel-btn:hover { background: #E8E8E8; }

    .logout-btn {
        flex: 1; padding: 12px; border: none; border-radius: 10px;
        background: var(--primary-green); color: white;
        font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: 0.2s;
    }

    .logout-btn:hover { background: var(--primary-dark); }

    @media (max-width: 600px) {
        .content-grid { grid-template-columns: 1fr; }
        .profile-hero { flex-direction: column; align-items: flex-start; gap: 16px; }
        .page-content { padding: 24px 16px; }
    }
</style>
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
                <span class="nav-text">My Claims</span>
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
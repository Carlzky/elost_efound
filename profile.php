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

$user_id = $_SESSION['user_id'];

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);

// Fetch stats
$claimed_sql = "SELECT COUNT(*) AS total FROM claims WHERE claimant_user_id = ? AND claim_status = 'Approved'";
$stmt2 = $conn->prepare($claimed_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$claimed = $stmt2->get_result()->fetch_assoc()['total'];

$returned_sql = "SELECT COUNT(*) AS total FROM found_items WHERE user_id = ?";
$stmt3 = $conn->prepare($returned_sql);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$returned = $stmt3->get_result()->fetch_assoc()['total'];

$reports_sql = "SELECT COUNT(*) AS total FROM lost_items WHERE user_id = ?";
$stmt4 = $conn->prepare($reports_sql);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$reports = $stmt4->get_result()->fetch_assoc()['total'];

$avatar = !empty($profile['profile_image']) ? $profile['profile_image'] : 'uploads/default-avatar.png';
$username = htmlspecialchars($profile['username'] ?? '');
$email = htmlspecialchars($profile['email'] ?? '');
$full_name = htmlspecialchars($profile['full_name'] ?? '');
$cvsu_email = htmlspecialchars($profile['cvsu_email'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>My Profile - E-LOST KOH, E-FOUND MOH</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #1F5D4A;
        --primary-dark: #143F32;
        --gold: #F1B846;
        --primary-green: #1F5D4A;
        --bg-gray: #F4F4F4;
        --pure-white: #FFFFFF;
        --text-dark: #1A1A1A;
        --sidebar-width: 240px;
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

    .sidebar.open {
        transform: translateX(0);
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
        justify-content: center;
        padding: 4px;
        border-radius: 6px;
        transition: background 0.2s;
    }

    .hamburger-btn:hover { background: #F0F0F0; }

    .top-bar-center {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Center logo — same box as sidebar */
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

    .top-logo-icon:hover {
        transform: scale(1.08) rotate(4deg);
    }

    .top-logo-text {
        font-family: 'Poppins', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: var(--primary-green);
        line-height: 1.2;
    }

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
        max-width: 680px;
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
       PROFILE BANNER
    ========================= */
    .profile-banner {
        background: var(--primary-green);
        border-radius: 16px;
        padding: 28px 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        position: relative;
        margin-bottom: 20px;
    }

    .profile-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        background: rgba(255,255,255,0.25);
        border: 3px solid rgba(255,255,255,0.4);
        flex-shrink: 0;
    }

    .profile-info h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 18px;
        font-weight: 700;
        color: white;
        margin-bottom: 4px;
    }

    .profile-info p {
        font-size: 13px;
        color: rgba(255,255,255,0.75);
    }

    .edit-profile-btn {
        position: absolute;
        right: 20px;
        bottom: 20px;
        background: white;
        color: var(--primary-green);
        border: none;
        border-radius: 8px;
        padding: 8px 16px;
        font-family: 'Inter', sans-serif;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .edit-profile-btn:hover { background: #F0F0F0; }

    /* ========================
       STATS ROW
    ========================= */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        background: var(--pure-white);
        border-radius: 14px;
        border: 1px solid #EAEAEA;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }

    .stat-cell {
        text-align: center;
        padding: 20px 12px;
        border-right: 1px solid #EAEAEA;
    }

    .stat-cell:last-child { border-right: none; }

    .stat-cell .num {
        font-family: 'Poppins', sans-serif;
        font-size: 28px;
        font-weight: 700;
        color: var(--primary-green);
        margin-bottom: 4px;
    }

    .stat-cell .label {
        font-size: 12px;
        color: #9A9A9A;
        font-weight: 500;
    }

    /* ========================
       INFO CARD
    ========================= */
    .info-card {
        background: var(--pure-white);
        border-radius: 14px;
        border: 1px solid #EAEAEA;
        padding: 22px 24px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.04);
    }

    .info-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 14px 0;
        border-bottom: 1px solid #F4F4F4;
    }

    .info-row:last-child { border-bottom: none; }

    .info-label {
        font-size: 11px;
        font-weight: 600;
        color: #9A9A9A;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-dark);
    }

    /* ========================
       EDIT PROFILE MODAL
    ========================= */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(6px);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: flex-start;
        padding: 60px 20px;
        overflow-y: auto;
    }

    .modal-overlay.open { display: flex; }

    .modal-card {
        background: white;
        border-radius: 20px;
        width: 100%;
        max-width: 560px;
        padding: 32px;
        border: 1px solid #EAEAEA;
        box-shadow: 0 24px 60px rgba(0,0,0,0.14);
        animation: slideUp 0.25s cubic-bezier(0.34,1.56,0.64,1) forwards;
        transform: translateY(30px);
        opacity: 0;
    }

    @keyframes slideUp {
        to { transform: translateY(0); opacity: 1; }
    }

    .modal-back {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        color: var(--primary-green);
        font-weight: 600;
        cursor: pointer;
        margin-bottom: 24px;
        background: none;
        border: none;
        padding: 0;
    }

    .modal-back:hover { opacity: 0.75; }

    .modal-title {
        font-family: 'Poppins', sans-serif;
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 24px;
    }

    /* Photo section */
    .photo-section {
        margin-bottom: 28px;
    }

    .photo-section h3 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 14px;
    }

    .photo-row {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .photo-preview {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        background: #DDD;
        border: 2px solid #EAEAEA;
        flex-shrink: 0;
    }

    .photo-actions {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .photo-hint {
        font-size: 11px;
        color: #9A9A9A;
        margin-bottom: 4px;
    }

    .photo-btns {
        display: flex;
        gap: 10px;
    }

    .btn-change-photo {
        background: var(--primary-green);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-change-photo:hover { background: var(--primary-dark); }

    .btn-remove-photo {
        background: transparent;
        color: #E74C3C;
        border: 1px solid #E74C3C;
        border-radius: 8px;
        padding: 8px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-remove-photo:hover { background: #FFF5F5; }

    /* Form fields */
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 18px;
        margin-bottom: 28px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full { grid-column: 1 / -1; }

    .form-group label {
        font-size: 12px;
        font-weight: 600;
        color: #555;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .form-group input {
        padding: 10px 14px;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        color: var(--text-dark);
        background: #FAFAFA;
        outline: none;
        transition: border 0.2s;
    }

    .form-group input:focus {
        border-color: var(--primary-green);
        background: white;
    }

    /* Modal footer buttons */
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-top: 1px solid #F0F0F0;
        padding-top: 22px;
    }

    .btn-cancel {
        padding: 11px 22px;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        background: #F4F4F4;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-cancel:hover { background: #E8E8E8; }

    .btn-save {
        padding: 11px 24px;
        border: none;
        border-radius: 10px;
        background: var(--primary-green);
        color: white;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .btn-save:hover { background: var(--primary-dark); }

    /* Hidden file input */
    #photoInput { display: none; }

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

    .logout-confirm-btn {
        flex: 1; padding: 12px; border: none; border-radius: 10px;
        background: var(--primary-green); color: white;
        font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: 0.2s;
    }

    .logout-confirm-btn:hover { background: var(--primary-dark); }
</style>
</head>
<body>

<!-- ======================== SIDEBAR OVERLAY ======================== -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- ======================== SIDEBAR DRAWER ======================== -->
<div class="sidebar" id="sidebar">
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
        <li class="nav-item">
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

<!-- ======================== TOP BAR ======================== -->
<div class="top-bar">
    <!-- Hamburger -->
    <button class="hamburger-btn" onclick="openSidebar()">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <!-- Center Logo (same style as sidebar) -->
    <div class="top-bar-center">
        <div class="top-logo-icon">🔍</div>
    </div>

    <!-- Right: bell + avatar -->
    <div class="top-bar-right">
        <a href="notif.php" class="notif-bell-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </a>
        <img class="avatar-sm" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar">
    </div>
</div>

<!-- ======================== PAGE CONTENT ======================== -->
<div class="page-content">
    <div class="page-label">Account</div>
    <h1 class="page-title">My Profile</h1>

    <!-- Profile Banner -->
    <div class="profile-banner">
        <img class="profile-avatar" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar">
        <div class="profile-info">
            <h2><?php echo $username; ?></h2>
            <p><?php echo $email; ?></p>
        </div>
        <button class="edit-profile-btn" onclick="openEditModal()">Edit profile</button>
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-cell">
            <div class="num"><?php echo $claimed; ?></div>
            <div class="label">Items Claimed</div>
        </div>
        <div class="stat-cell">
            <div class="num"><?php echo $returned; ?></div>
            <div class="label">Items Returned</div>
        </div>
        <div class="stat-cell">
            <div class="num"><?php echo $reports; ?></div>
            <div class="label">Reports Made</div>
        </div>
    </div>

    <!-- Info Card -->
    <div class="info-card">
        <div class="info-row">
            <div class="info-label">Full Name</div>
            <div class="info-value"><?php echo $full_name ?: '—'; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Username</div>
            <div class="info-value"><?php echo $username ?: '—'; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">CvSU Email</div>
            <div class="info-value"><?php echo $cvsu_email ?: $email; ?></div>
        </div>
    </div>
</div>

<!-- ======================== EDIT PROFILE MODAL ======================== -->
<div class="modal-overlay" id="editModal">
    <div class="modal-card">
        <button class="modal-back" onclick="closeEditModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Profile
        </button>

        <h2 class="modal-title">Edit Profile</h2>

        <form method="POST" action="update_profile.php" enctype="multipart/form-data">

            <!-- Profile Photo -->
            <div class="photo-section">
                <h3>Profile Photo</h3>
                <div class="photo-row">
                    <img class="photo-preview" id="photoPreview" src="<?php echo htmlspecialchars($avatar); ?>" alt="preview">
                    <div class="photo-actions">
                        <div class="photo-hint">JPG, PNG, GIF. Max size 2MB.</div>
                        <div class="photo-btns">
                            <button type="button" class="btn-change-photo" onclick="document.getElementById('photoInput').click()">Change Photo</button>
                            <button type="button" class="btn-remove-photo" onclick="removePhoto()">Remove</button>
                        </div>
                    </div>
                </div>
                <input type="file" id="photoInput" name="profile_image" accept="image/*" onchange="previewPhoto(event)">
            </div>

            <!-- Form Fields -->
            <div class="form-grid">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>">
                </div>
                <div class="form-group">
                    <label>CvSU Email Address</label>
                    <input type="email" name="cvsu_email" placeholder="Username@cvsu.gmail.com" value="<?php echo $cvsu_email ?: $email; ?>">
                </div>
                <div class="form-group full">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Full Name" value="<?php echo $full_name; ?>">
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>

        </form>
    </div>
</div>

<!-- ======================== LOGOUT MODAL ======================== -->
<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="logout-confirm-btn" onclick="window.location.href='logout.php'">Confirm</button>
        </div>
    </div>
</div>

<script>
    /* ---- Sidebar drawer ---- */
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }

    /* Close on ESC */
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeSidebar(); closeEditModal(); closeLogoutModal(); }
    });

    /* ---- Edit profile modal ---- */
    function openEditModal() {
        document.getElementById('editModal').classList.add('open');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('open');
    }

    /* Close modal on overlay click */
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    /* ---- Photo preview ---- */
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
            reader.readAsDataURL(file);
        }
    }

    function removePhoto() {
        document.getElementById('photoPreview').src = 'uploads/default-avatar.png';
        document.getElementById('photoInput').value = '';
    }

    /* ---- Logout modal ---- */
    function openLogoutModal() {
        closeSidebar();
        document.getElementById('logoutOverlay').style.display = 'flex';
    }

    function closeLogoutModal() {
        document.getElementById('logoutOverlay').style.display = 'none';
    }
</script>

</body>
</html>
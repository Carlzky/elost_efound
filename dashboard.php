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
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - E-LOST KOH, E-FOUND MOH</title>

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
        --border: #E5E5E5;
        --sidebar-width: 240px;
        --pending: #E9A93D;
        --approved: #4CAF50;
        --rejected: #E74C3C;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-gray);
        display: flex;
        min-height: 100vh;
        color: var(--text-dark);
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
            0 12px 30px rgba(0, 0, 0, 0.35),
            inset 0 3px 6px rgba(255, 255, 255, 0.18);
        transition:
            transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1),
            box-shadow 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .logo-icon:hover {
        transform: scale(1.08) translateY(-5px) rotate(4deg);
        box-shadow:
            0 18px 40px rgba(0, 0, 0, 0.45),
            inset 0 3px 6px rgba(255, 255, 255, 0.25);
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
        color: rgba(255, 255, 255, 0.82);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 10px;
        transition: 0.25s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .nav-item a:hover,
    .nav-item.active a {
        background: rgba(255, 255, 255, 0.12);
        color: white;
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

    .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #888;
        font-size: 14px;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .notif-bell {
        font-size: 18px;
        cursor: pointer;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #DDD;
    }

    /* ========================
       PAGE TITLE
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

    /* ========================
       STAT CARDS
    ========================= */

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: var(--pure-white);
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #EAEAEA;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .stat-card h3 {
        font-size: 13px;
        font-weight: 500;
        color: #7A7A7A;
        margin-bottom: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-card .stat-number {
        font-family: 'Poppins', sans-serif;
        font-size: 34px;
        font-weight: 700;
        color: var(--primary-green);
    }

    /* ========================
       CONTENT GRID
    ========================= */

    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }

    /* ========================
       SHARED PANEL CARD
    ========================= */

    .panel-card {
        background: var(--pure-white);
        border-radius: 16px;
        padding: 28px;
        border: 1px solid #EAEAEA;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
    }

    .panel-card h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 22px;
        color: var(--text-dark);
    }

    /* ========================
       ACTIVITY LIST
    ========================= */

    .activity-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0;
        border-bottom: 1px solid #F0F0F0;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-item h4 {
        font-size: 14px;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .activity-item span {
        color: #9A9A9A;
        font-size: 12px;
    }

    /* ========================
       RECENT ITEMS LIST
    ========================= */

    .recent-item {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }

    .recent-item:last-child {
        margin-bottom: 0;
    }

    .recent-item img {
        width: 56px;
        height: 56px;
        border-radius: 10px;
        object-fit: cover;
        background: #EEE;
        border: 1px solid #EAEAEA;
    }

    .recent-item-info h4 {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .recent-item-info p {
        font-size: 12px;
        color: #9A9A9A;
    }

    /* ========================
       LOGOUT MODAL
    ========================= */

    .logout-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
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
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        transform: scale(0.85);
        opacity: 0;
        animation: popIn 0.25s forwards;
    }

    @keyframes popIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
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

    .logout-buttons {
        display: flex;
        gap: 12px;
    }

    .cancel-btn {
        flex: 1;
        padding: 12px;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        background: #F4F4F4;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
    }

    .cancel-btn:hover {
        background: #E8E8E8;
    }

    .logout-btn {
        flex: 1;
        padding: 12px;
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

    .logout-btn:hover {
        background: var(--primary-dark);
    }

    /* ========================
       RESPONSIVE
    ========================= */

    @media (max-width: 1024px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 78px;
            padding: 20px 12px;
        }

        .logo-text,
        .nav-text {
            display: none;
        }

        .nav-item a {
            justify-content: center;
            padding: 14px;
        }

        .main-content {
            margin-left: 78px;
            padding: 22px;
        }

        .top-bar {
            flex-direction: column;
            gap: 14px;
            align-items: stretch;
        }

        .search-wrapper {
            width: 100%;
        }

        .page-title {
            font-size: 24px;
        }
    }

</style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo-section">
        <div class="logo-icon">🔍</div>
        <div class="logo-text">E-LOST KOH<br>E-FOUND MOH</div>
    </div>

    <ul class="nav-menu">

        <li class="nav-item active">
            <a href="dashboard.php">
                🏠
                <span class="nav-text">Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="report-item.php">
                📦
                <span class="nav-text">Report Item</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="browse-items.php">
                🔎
                <span class="nav-text">Browse Items</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="claim.php">
                📄
                <span class="nav-text">My Claims</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="notif.php">
                🔔
                <span class="nav-text">Notifications</span>
            </a>
        </li>

        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()">
                🚪
                <span class="nav-text">Logout</span>
            </a>
        </li>

    </ul>

</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- TOP BAR -->
    <div class="top-bar">

        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" placeholder="Search items...">
        </div>

        <div class="user-profile">
            <span class="notif-bell">🔔</span>
            <div class="avatar"></div>
        </div>

    </div>

    <!-- PAGE TITLE -->
    <h1 class="page-title">Dashboard</h1>
    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($user); ?>!</p>

    <!-- STAT CARDS -->
    <div class="stats-grid">

        <div class="stat-card">
            <h3>Total Lost Items</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM lost_items";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Total Found Items</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM found_items";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>

        <div class="stat-card">
            <h3>Claims Pending</h3>
            <div class="stat-number">
                <?php
                $sql = "SELECT COUNT(*) AS total FROM claims WHERE claim_status='Pending'";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();
                echo $row['total'];
                ?>
            </div>
        </div>

    </div>

    <!-- CONTENT GRID -->
    <div class="content-grid">

        <!-- RECENT ACTIVITY -->
        <div class="panel-card">

            <h2>Recent Activity</h2>

            <?php
            $sql = "SELECT * FROM report_history ORDER BY action_date DESC LIMIT 5";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()):
            ?>

            <div class="activity-item">
                <div>
                    <h4><?php echo htmlspecialchars($row['action_done']); ?></h4>
                    <span><?php echo htmlspecialchars($row['action_date']); ?></span>
                </div>
            </div>

            <?php endwhile; ?>

        </div>

 <!-- RECENTLY POSTED ITEMS -->
<div class="panel-card">

    <h2>Recently Posted Items</h2>

    <?php
    $sql = "SELECT * FROM lost_items
            ORDER BY created_at DESC
            LIMIT 3";

    $result = $conn->query($sql);
    ?>

    <?php if($result->num_rows > 0): ?>

        <?php while($row = $result->fetch_assoc()): ?>

            <div class="recent-item">

                <?php
                $image = !empty($row['item_image'])
                ? $row['item_image']
                : 'uploads/default.png';
                ?>

                <img src="<?php echo htmlspecialchars($image); ?>" alt="">

                <div class="recent-item-info">

                    <h4>
                        <?php echo htmlspecialchars($row['item_name']); ?>
                    </h4>

                    <p>
                        Lost ·
                        <?php echo htmlspecialchars($row['location_lost']); ?>
                    </p>

                </div>

            </div>

        <?php endwhile; ?>

    <?php else: ?>

        <p style="color:gray;font-size:14px;">
            No recent items found.
        </p>

    <?php endif; ?>

</div>

<!-- LOGOUT MODAL -->
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
function openLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "none";
}

function confirmLogout(){
    window.location.href = "logout.php";
}
</script>

</body>
</html>
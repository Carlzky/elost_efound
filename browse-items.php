<?php
session_start();
include "config/db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items - E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1F5D4A;
            --primary-dark: #143F32;
            --gold: #F1B846;
            --primary-green: #1F5D4A;
            --light-green: #BBC34A;
            --dark-gray: #68735C;
            --bg-gray: #F2F2F2;
            --pure-white: #FFFFFF;
            --text-dark: #1A1A1A;
            --sidebar-width: 240px;
            --status-lost: #D9534F;
            --status-found: #1F5D4A;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation Component */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-green);
            color: var(--pure-white);
            padding: 24px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
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
            background: linear-gradient(135deg, #1F5D4A, #143F32);
            border: 2px solid #F1B846;
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
            font-weight: 700;
            color: #FFFFFF;
        }

        .logo-text .txt-highlight {
            color: #BBC34A;
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

        .nav-item a:hover {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .nav-item.active a {
            background: rgba(255, 255, 255, 0.12);
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

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            height:100%;
        }



        /* Workspace Panels */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
        }

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
            background-color: var(--pure-white);
        }

        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #888888;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar-link{
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar{
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            cursor: pointer;
            border: 2px solid #E5E5E5;
            transition: all 0.25s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .avatar:hover{
            transform: scale(1.06);
            border-color: var(--primary-green);
            box-shadow: 0 6px 14px rgba(0,0,0,0.12);
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            margin-bottom: 24px;
        }

        .filter-tabs {
            display: flex;
            gap: 24px;
            border-bottom: 1px solid #E0E0E0;
            margin-bottom: 24px;
            padding-bottom: 8px;
        }

        .tab {
            font-size: 15px;
            font-weight: 500;
            color: var(--dark-gray);
            cursor: pointer;
            padding-bottom: 8px;
            position: relative;
        }

        .tab.active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -9px;
            left: 0;
            width: 100%;
            height: 3px;
            background-color: var(--primary-green);
        }

        .filter-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 30px;
            align-items: center;
        }

        .select-input {
            padding: 10px 14px;
            border: 1px solid #E0E0E0;
            border-radius: 6px;
            background-color: var(--pure-white);
            font-family: 'Inter', sans-serif;
            min-width: 130px;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .btn-filter {
            background-color: var(--primary-green);
            color: var(--pure-white);
            border: none;
            padding: 10px 18px;
            border-radius: 6px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-weight: 500;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* GRID CARDS LAYOUT CONTAINER */
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .item-card {
            background-color: var(--pure-white);
            border-radius: 12px;
            padding: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
            display: flex;
            flex-direction: column;
            border: 1px solid #EAEAEA;
        }

        .card-image-box {
            height: 200px;
            background-color: #F7F7F7;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-gray);
            margin-bottom: 12px;
            font-size: 14px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .card-meta {
            font-size: 13px;
            color: #7A7A7A;
            margin-bottom: 4px;
        }

        .status-lost {
            color: var(--status-lost);
            font-weight: 500;
        }

        .status-found {
            color: var(--status-found);
            font-weight: 500;
        }

        .card-date {
            font-size: 12px;
            color: #A0A0A0;
            margin-bottom: 16px;
        }

        .btn-view-details {
            margin-top: auto;
            display: block;
            text-align: center;
            background-color: var(--primary-green);
            color: var(--pure-white);
            text-decoration: none;
            padding: 8px 0;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: background-color 0.2s;
        }

        .btn-view-details:hover {
            background-color: #164335;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .logo-text, .nav-text { display: none; }
            .main-content { margin-left: 70px; padding: 20px; }
            .top-bar { flex-direction: column-reverse; gap: 16px; align-items: stretch; }
            .search-wrapper { width: 100%; }
        }

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
    </style>
</head>
<body>

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
        <li class="nav-item active">
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

    <div class="main-content">
        <div class="top-bar">
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search items...">
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

        <h1>Browse Items</h1>

        <div class="filter-tabs">
            <div class="tab active">Lost Items</div>
            <div class="tab">Found Items</div>
        </div>

        <div class="filter-bar">

    <select class="select-input" id="categoryFilter">
        <option value="">All Categories</option>
        <option value="bags">Bag / Backpack</option>
        <option value="electronics">Electronics / Gadgets</option>
        <option value="documents">Documents / ID Cards</option>
        <option value="wallets">Wallets / Purses</option>
    </select>

    <select class="select-input" id="locationFilter">
        <option value="">All Locations</option>
        <option value="canteen">Canteen</option>
        <option value="library">Library</option>
        <option value="gym">Gym</option>
        <option value="admin bldg">Admin Bldg</option>
    </select>

    <select class="select-input" id="sortFilter">
        <option value="newest">Newest</option>
        <option value="oldest">Oldest</option>
    </select>

    <button class="btn-filter" onclick="filterItems()">🎛️ Filter</button>

</div>
        <div id="no-results-message" style="display: none; text-align: center; color: var(--dark-gray); padding: 40px; font-weight: 500; font-size: 16px;"></div>

        <div class="items-grid">
            
            <?php

$sql = "

SELECT 
    lost_id AS item_id,
    item_name,
    category,
    location_lost AS location,
    date_lost AS item_date,
    item_image,
    'lost' AS item_type
FROM lost_items

UNION ALL

SELECT 
    found_id AS item_id,
    item_name,
    category,
    location_found AS location,
    date_found AS item_date,
    item_image,
    'found' AS item_type
FROM found_items

ORDER BY item_date DESC

";

$result = $conn->query($sql);

if($result && $result->num_rows > 0):

    while($row = $result->fetch_assoc()):

        $image = !empty($row['item_image'])
            ? $row['item_image']
            : 'uploads/default.png';

?>

<div class="item-card"
    data-category="<?php echo strtolower($row['category']); ?>"
    data-location="<?php echo strtolower($row['location']); ?>"
    data-type="<?php echo strtolower($row['item_type']); ?>"
    data-date="<?php echo $row['item_date']; ?>"
>

    <div class="card-image-box">

        <img 
            src="<?php echo htmlspecialchars($image); ?>" 
            alt="Item Image"
            style="width:100%; height:100%; object-fit:cover;"
        >

    </div>

    <div class="card-title">
        <?php echo htmlspecialchars($row['item_name']); ?>
    </div>

    <div class="card-meta">

        <?php if($row['item_type'] == 'lost'): ?>

            <span class="status-lost">Lost</span>

        <?php else: ?>

            <span class="status-found">Found</span>

        <?php endif; ?>

        • <?php echo htmlspecialchars($row['location']); ?>

    </div>

    <div class="card-date">
        <?php echo date("F d, Y", strtotime($row['item_date'])); ?>
    </div>

    <a href="item-details.php?id=<?php echo $row['item_id']; ?>" class="btn-view-details">
        View Details
    </a>

</div>

<?php

    endwhile;

else:

?>

<p style="color:gray;">No items found.</p>

<?php endif; ?>

        </div>
    </div>



</div>
    <script>

const tabs = document.querySelectorAll('.tab');

const searchInput = document.querySelector('.search-wrapper input');

const noResultsMessage = document.getElementById('no-results-message');

function filterItems() {

    const cards = document.querySelectorAll('.item-card');

    const searchText = searchInput.value.toLowerCase().trim();

    const category =
        document.getElementById('categoryFilter')
        .value
        .toLowerCase();

    const location =
        document.getElementById('locationFilter')
        .value
        .toLowerCase();

    const sort =
        document.getElementById('sortFilter')
        .value;

    const activeTab =
        document.querySelector('.tab.active')
        .textContent
        .toLowerCase();

    let visibleCards = [];

    cards.forEach(card => {

        const title =
            card.querySelector('.card-title')
            .textContent
            .toLowerCase();

        const meta =
            card.querySelector('.card-meta')
            .textContent
            .toLowerCase();

        const cardCategory =
            card.dataset.category;

        const cardLocation =
            card.dataset.location;

        const cardType =
            card.dataset.type;

        let matches = true;

        if(searchText &&
           !title.includes(searchText) &&
           !meta.includes(searchText)) {

            matches = false;
        }

        if(category &&
           cardCategory !== category) {

            matches = false;
        }

        if(location &&
           !cardLocation.includes(location)) {

            matches = false;
        }

        if(activeTab.includes('lost') &&
           cardType !== 'lost') {

            matches = false;
        }

        if(activeTab.includes('found') &&
           cardType !== 'found') {

            matches = false;
        }

        if(matches) {

            card.style.display = '';

            visibleCards.push(card);

        } else {

            card.style.display = 'none';
        }
    });

    const grid = document.querySelector('.items-grid');

    visibleCards.sort((a, b) => {

        const dateA = new Date(a.dataset.date);

        const dateB = new Date(b.dataset.date);

        return sort === 'newest'
            ? dateB - dateA
            : dateA - dateB;
    });

    visibleCards.forEach(card => {
        grid.appendChild(card);
    });


    if(visibleCards.length === 0) {

        noResultsMessage.style.display = 'block';

        noResultsMessage.textContent =
            "No matching items found.";

    } else {

        noResultsMessage.style.display = 'none';
    }
}

tabs.forEach(tab => {

    tab.addEventListener('click', () => {

        tabs.forEach(t => t.classList.remove('active'));

        tab.classList.add('active');

        filterItems();
    });
});

searchInput.addEventListener('input', filterItems);

filterItems();

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
</body>
</html>
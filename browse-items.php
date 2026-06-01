<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include "config/db.php";

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
    <title>Browse Items - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/browse-items_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
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
                <span class="nav-text">Claims</span>
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
                    <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture" class="avatar">
                </a>
            </div>
        </div>

        <h1>Browse Items</h1>

        <div class="filter-tabs">
    <div class="tab active" data-tab="lost">
        Lost Items
    </div>
    <div class="tab" data-tab="found">
        Found Items
    </div>
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

    <button class="btn-filter" onclick="filterItems()">Filter</button>

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

    <a href="item-details.php?id=<?php echo $row['item_id']; ?>&type=<?php echo $row['item_type']; ?>" class="btn-view-details">
                View Details
    </a>

</div>

<?php

    endwhile;

else:

?>

<div id="database-empty-message"
     style="
        justify-content:center;
        align-items:center;
        min-height:150px;
        padding-left:100px;
        text-align:right;
        color:var(--dark-gray);
        font-weight:500;
        font-size:16px;
     ">
    No items found.
</div>

<?php endif; ?>

        </div>
    </div>



</div>
    <script>

const tabs = document.querySelectorAll('.tab');

const urlParams = new URLSearchParams(window.location.search);

const currentTab = urlParams.get('tab');

if(currentTab){

    tabs.forEach(tab => {

        tab.classList.remove('active');

        if(tab.dataset.tab === currentTab){

            tab.classList.add('active');
        }
    });
}

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


if(cards.length > 0 && visibleCards.length === 0) {

    noResultsMessage.style.display = 'block';
    noResultsMessage.textContent = "No matching items found.";

} else {

    noResultsMessage.style.display = 'none';
}

}

tabs.forEach(tab => {

    tab.addEventListener('click', () => {

        tabs.forEach(t => t.classList.remove('active'));

        tab.classList.add('active');

        const selectedTab = tab.dataset.tab;

        window.history.replaceState(
            null,
            null,
            '?tab=' + selectedTab
        );

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
    window.location.href = 'actions/logout.php';
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
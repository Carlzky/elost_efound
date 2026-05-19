<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Items - E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">
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
            font-family:'Poppins', sans-serif;
            font-size:15px;
            line-height:1.3;
            font-weight:600;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            height:100%;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
        }

        .nav-item.active a, .nav-item a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--pure-white);
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

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #DDD;
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
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-section">
            <div class="logo-icon">🔍</div>
            <div class="logo-text">E-LOST MOH<br>E-FOUND KOH</div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href=" dashboard.php">🏠<span class="nav-text">Dashboard</span></a></li>
            <li class="nav-item"><a href="report-item.php">📦<span class="nav-text">Report Item</span></a></li>
            <li class="nav-item active"><a href="browse-items.php">🔎<span class="nav-text">Browse Items</span></a></li>
            <li class="nav-item"><a href="claim.php">📄<span class="nav-text">My Claims</span></a></li>
            <li class="nav-item"><a href="notif.php">🔔<span class="nav-text">Notifications</span></a></li>
            <li class="nav-item" style="margin-top: auto;"><a href="logout.php">🚪<span class="nav-text">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-wrapper">
                <span class="search-icon">🔍</span>
                <input type="text" placeholder="Search items...">
            </div>
            <div class="user-profile">
                <span>🔔</span>
                <div class="avatar"></div>
            </div>
        </div>

        <h1>Browse Items</h1>

        <div class="filter-tabs">
            <div class="tab active">Lost Items</div>
            <div class="tab">Found Items</div>
        </div>

        <div class="filter-bar">
            <select class="select-input"><option>Category</option></select>
            <select class="select-input"><option>Location</option></select>
            <select class="select-input"><option>Newest</option></select>
            <button class="btn-filter">🎛️ Filter</button>
        </div>

        <div id="no-results-message" style="display: none; text-align: center; color: var(--dark-gray); padding: 40px; font-weight: 500; font-size: 16px;"></div>

        <div class="items-grid">
            
            <div class="item-card">
                <div class="card-image-box">🎒 Backpack Image</div>
                <div class="card-title">Black Backpack</div>
                <div class="card-meta"><span class="status-lost">Lost</span> • Canteen</div>
                <div class="card-date">May 20, 2026</div>
                <a href="item-details.php?id=1" class="btn-view-details">View Details</a>
            </div>

            <div class="item-card">
                <div class="card-image-box">⌚ Watch Image</div>
                <div class="card-title">Silver Watch</div>
                <div class="card-meta"><span class="status-found">Found</span> • Library</div>
                <div class="card-date">May 19, 2026</div>
                <a href="item-details.php?id=2" class="btn-view-details">View Details</a>
            </div>

            <div class="item-card">
                <div class="card-image-box">🎧 Earbuds Image</div>
                <div class="card-title">White Earbuds</div>
                <div class="card-meta"><span class="status-lost">Lost</span> • Gym</div>
                <div class="card-date">May 18, 2026</div>
                <a href="item-details.php?id=3" class="btn-view-details">View Details</a>
            </div>

            <div class="item-card">
                <div class="card-image-box">🪪 ID Image</div>
                <div class="card-title">ID Card</div>
                <div class="card-meta"><span class="status-found">Found</span> • Admin Bldg</div>
                <div class="card-date">May 18, 2026</div>
                <a href="item-details.php?id=4" class="btn-view-details">View Details</a>
            </div>

        </div>
    </div>



</div>
    <script>
        
        const tabs = document.querySelectorAll('.tab');
        const cards = document.querySelectorAll('.item-card');
        const searchInput = document.querySelector('.search-wrapper input');
        const noResultsMessage = document.getElementById('no-results-message');

        function filterItems() {
            const searchText = searchInput.value.toLowerCase().trim();
            const originalSearchText = searchInput.value.trim();
            const activeTab = document.querySelector('.tab.active').textContent.trim().toLowerCase();
            
            let visibleCardsCount = 0;

            cards.forEach(card => {
                const cardTitle = card.querySelector('.card-title').textContent.toLowerCase();
                const cardMeta = card.querySelector('.card-meta').textContent.toLowerCase();

                // If user is typing something, search EVERYTHING
                if (searchText !== "") {
                    if (cardTitle.includes(searchText) || cardMeta.includes(searchText)) {
                        card.style.display = ''; // Clears display to fall back on native CSS grid rule
                        visibleCardsCount++;
                    } else {
                        card.style.display = 'none';
                    }
                } 
                // If search bar is empty, fall back to normal tab filtering
                else {
                    const matchesTab = activeTab.includes('lost') ? cardMeta.includes('lost') : cardMeta.includes('found');
                    if (matchesTab) {
                        card.style.display = ''; // Clears display to fall back on native CSS grid rule
                        visibleCardsCount++;
                    } else {
                        card.style.display = 'none';
                    }
                }
            });

            // Handle empty search feedback response
            if (visibleCardsCount === 0 && searchText !== "") {
                noResultsMessage.textContent = `No "${originalSearchText}" Found`;
                noResultsMessage.style.display = 'block';
            } else {
                noResultsMessage.style.display = 'none';
            }
        }

        // Listener 1: Tab Switching Event
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                searchInput.value = ""; 
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                filterItems(); 
            });
        });

        // Listener 2: Typing Event
        searchInput.addEventListener('input', () => {
            filterItems(); 
        });

        // Initialize layout on page load
        filterItems();
    </script>
</body>
</html>
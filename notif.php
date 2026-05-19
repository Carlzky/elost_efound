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

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter', sans-serif;
            background:var(--bg-gray);
            color:var(--text-dark);
            display:flex;
            min-height:100vh;
        }

        /* =========================
           SIDEBAR
        ========================== */

        .sidebar{
            width:var(--sidebar-width);
            background:var(--primary-green);
            color:white;
            padding:24px;
            display:flex;
            flex-direction:column;
            position:fixed;
            height:100vh;
        }

        .logo-section{
            display:flex;
            align-items:center;
            gap:14px;
            margin-bottom:40px;
        }

        .logo-icon{
            width:58px;
            height:58px;

            background:linear-gradient(
                135deg,
                var(--primary),
                var(--primary-dark)
            );

            border:2px solid var(--gold);
            border-radius:16px;

            display:flex;
            justify-content:center;
            align-items:center;

            font-size:26px;

            box-shadow:
                0 12px 30px rgba(0,0,0,0.35),
                inset 0 3px 6px rgba(255,255,255,0.18);

            transition:
                transform 0.7s cubic-bezier(0.2,0.8,0.2,1),
                box-shadow 0.7s cubic-bezier(0.2,0.8,0.2,1);
        }

        .logo-icon:hover{
            transform:scale(1.08) translateY(-5px) rotate(4deg);

            box-shadow:
                0 18px 40px rgba(0,0,0,0.45),
                inset 0 3px 6px rgba(255,255,255,0.25);
        }

        .logo-text{
            font-family:'Poppins', sans-serif;
            font-size:15px;
            line-height:1.3;
            font-weight:600;
        }

        .nav-menu{
            list-style:none;
            display:flex;
            flex-direction:column;
            gap:8px;
            height:100%;
        }

        .nav-item a{
            text-decoration:none;
            color:rgba(255,255,255,0.82);

            display:flex;
            align-items:center;
            gap:12px;

            padding:13px 16px;
            border-radius:10px;

            transition:all 0.25s ease;
            font-size:14px;
            font-weight:500;
        }

        .nav-item a:hover,
        .nav-item.active a{
            background:rgba(255,255,255,0.12);
            color:white;
        }

        /* =========================
           MAIN CONTENT
        ========================== */

        .main-content{
            margin-left:var(--sidebar-width);
            width:100%;
            padding:42px;
            display:flex;
            justify-content:center;
            align-items:flex-start;
        }

        .page-wrapper{
            width:100%;
            max-width:760px;
        }

        .page-title{
            font-family:'Poppins', sans-serif;
            font-size:30px;
            margin-bottom:26px;
            color:var(--text-dark);
        }

        /* =========================
           CARD
        ========================== */

        .notification-card{
            background:white;
            border-radius:20px;
            padding:28px;
            border:1px solid #EAEAEA;

            box-shadow:
                0 10px 28px rgba(0,0,0,0.04);
        }

        .notification-header{
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:24px;
        }

        .notification-heading{
            font-size:18px;
            font-weight:700;
            color:var(--text-dark);
        }

        .mark-read-btn{
            border:none;
            background:var(--primary-green);
            color:white;
            padding:10px 16px;
            border-radius:10px;
            font-size:12px;
            font-weight:600;
            cursor:pointer;
            transition:0.25s ease;
        }

        .mark-read-btn:hover{
            background:var(--primary-dark);
        }

        .notification-list{
            display:flex;
            flex-direction:column;
            gap:18px;
        }

        /* =========================
           NOTIFICATION ITEM
        ========================== */

        .notification-item{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;

            padding:18px;
            border:1px solid var(--border-light);
            border-radius:14px;

            background:#FCFCFC;

            transition:all 0.25s ease;
            cursor:pointer;

            animation:fadeIn 0.4s ease;
        }

        .notification-item:hover{
            transform:translateY(-2px);

            box-shadow:
                0 8px 20px rgba(0,0,0,0.05);
        }

        .notification-item.read{
            opacity:0.72;
            background:#F8F8F8;
        }

        .notification-item.read .notification-status{
            display:none;
        }

        .notification-left{
            display:flex;
            gap:14px;
            align-items:flex-start;
        }

        .notification-bell{
            width:40px;
            height:40px;

            border-radius:12px;
            background:#F1F5F3;

            display:flex;
            align-items:center;
            justify-content:center;

            flex-shrink:0;
        }

        .notification-bell svg{
            width:18px;
            height:18px;
            fill:#7A7A7A;
        }

        .notification-content h3{
            font-size:14px;
            line-height:1.5;
            margin-bottom:6px;
            font-weight:600;
            color:var(--text-dark);
        }

        .notification-content p{
            font-size:11px;
            letter-spacing:0.3px;
            color:#8A8A8A;
        }

        .notification-status{
            width:10px;
            height:10px;
            background:#6BCB4D;
            border-radius:50%;
            margin-top:6px;
            flex-shrink:0;
        }

        /* =========================
           EMPTY STATE
        ========================== */

        .empty-state{
            text-align:center;
            padding:50px 20px;
            color:#888;
            font-size:14px;
        }

        /* =========================
           ANIMATION
        ========================== */

        @keyframes fadeIn{
            from{
                opacity:0;
                transform:translateY(10px);
            }
            to{
                opacity:1;
                transform:translateY(0);
            }
        }

        /* =========================
           RESPONSIVE
        ========================== */

        @media (max-width:768px){

            .sidebar{
                width:78px;
                padding:20px 12px;
            }

            .logo-text,
            .nav-text{
                display:none;
            }

            .nav-item a{
                justify-content:center;
                padding:14px;
            }

            .main-content{
                margin-left:78px;
                padding:22px;
            }

            .notification-item{
                padding:16px;
            }

            .page-title{
                font-size:24px;
            }

            .notification-header{
                flex-direction:column;
                align-items:flex-start;
                gap:14px;
            }
        }

    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">

        <div class="logo-section">

            <div class="logo-icon">
                🔍
            </div>

            <div class="logo-text">
                E-LOST MOH<br>
                E-FOUND KOH
            </div>

        </div>

        <ul class="nav-menu">

            <li class="nav-item">
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

            <li class="nav-item active">
                <a href="notif.php">
                    🔔
                    <span class="nav-text">Notifications</span>
                </a>
            </li>

            <li class="nav-item" style="margin-top:auto;">
                <a href="logout.php">
                    🚪
                    <span class="nav-text">Logout</span>
                </a>
            </li>

        </ul>

    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <div class="page-wrapper">

            <h1 class="page-title">
                Notifications
            </h1>

            <div class="notification-card">

                <div class="notification-header">

                    <div class="notification-heading">
                        Recent Notifications
                    </div>

                    <button class="mark-read-btn" onclick="markAllAsRead()">
                        Mark all as read
                    </button>

                </div>

                <div class="notification-list" id="notificationList">

                    <!-- NOTIFICATION -->
                    <div class="notification-item"
                        onclick="openNotification(this)">

                        <div class="notification-left">

                            <div class="notification-bell">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 24a2.4 2.4 0 0 0 2.4-2.4H9.6A2.4 2.4 0 0 0 12 24zm6-7.2v-5.4c0-3.312-1.788-6.084-4.8-6.804V3.6a1.2 1.2 0 1 0-2.4 0v1.008C7.788 5.328 6 8.1 6 11.4v5.4L3.6 19.2v1.2h16.8v-1.2L18 16.8z"/>
                                </svg>
                            </div>

                            <div class="notification-content">
                                <h3>
                                    Your item "Black Backpack" has been claimed.
                                </h3>

                                <p>
                                    May 20, 2025 • 7:10 PM
                                </p>
                            </div>

                        </div>

                        <div class="notification-status"></div>

                    </div>

                    <!-- NOTIFICATION -->
                    <div class="notification-item"
                        onclick="openNotification(this)">

                        <div class="notification-left">

                            <div class="notification-bell">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 24a2.4 2.4 0 0 0 2.4-2.4H9.6A2.4 2.4 0 0 0 12 24zm6-7.2v-5.4c0-3.312-1.788-6.084-4.8-6.804V3.6a1.2 1.2 0 1 0-2.4 0v1.008C7.788 5.328 6 8.1 6 11.4v5.4L3.6 19.2v1.2h16.8v-1.2L18 16.8z"/>
                                </svg>
                            </div>

                            <div class="notification-content">
                                <h3>
                                    New item matches your lost report.
                                </h3>

                                <p>
                                    May 20, 2025 • 10:15 AM
                                </p>
                            </div>

                        </div>

                        <div class="notification-status"></div>

                    </div>

                    <!-- READ NOTIFICATION -->
                    <div class="notification-item read">

                        <div class="notification-left">

                            <div class="notification-bell">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 24a2.4 2.4 0 0 0 2.4-2.4H9.6A2.4 2.4 0 0 0 12 24zm6-7.2v-5.4c0-3.312-1.788-6.084-4.8-6.804V3.6a1.2 1.2 0 1 0-2.4 0v1.008C7.788 5.328 6 8.1 6 11.4v5.4L3.6 19.2v1.2h16.8v-1.2L18 16.8z"/>
                                </svg>
                            </div>

                            <div class="notification-content">
                                <h3>
                                    Your claim for "Silver Watch" is approved.
                                </h3>

                                <p>
                                    May 19, 2025 • 4:45 PM
                                </p>
                            </div>

                        </div>

                    </div>

                    <!-- READ NOTIFICATION -->
                    <div class="notification-item read">

                        <div class="notification-left">

                            <div class="notification-bell">
                                <svg viewBox="0 0 24 24">
                                    <path d="M12 24a2.4 2.4 0 0 0 2.4-2.4H9.6A2.4 2.4 0 0 0 12 24zm6-7.2v-5.4c0-3.312-1.788-6.084-4.8-6.804V3.6a1.2 1.2 0 1 0-2.4 0v1.008C7.788 5.328 6 8.1 6 11.4v5.4L3.6 19.2v1.2h16.8v-1.2L18 16.8z"/>
                                </svg>
                            </div>

                            <div class="notification-content">
                                <h3>
                                    New found item has been posted.
                                </h3>

                                <p>
                                    May 18, 2025 • 8:00 AM
                                </p>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    <script>

        function openNotification(element){

            element.classList.add('read');

            console.log("Notification opened");

        }

        function markAllAsRead(){

            const notifications =
                document.querySelectorAll('.notification-item');

            notifications.forEach(notification => {

                notification.classList.add('read');

            });

        }

    </script>

</body>
</html>
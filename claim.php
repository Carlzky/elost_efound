<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Claim System - E-LOST KOH, E-FOUND MOH</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

    <style>

        :root{
            --primary:#1F5D4A;
            --primary-dark:#143F32;
            --gold:#F1B846;

            --bg-gray:#F4F4F4;
            --white:#FFFFFF;
            --text-dark:#1A1A1A;
            --border:#E5E5E5;

            --pending:#E9A93D;
            --approved:#4CAF50;
            --rejected:#E74C3C;

            --sidebar-width:240px;
        }

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:'Inter', sans-serif;
            background:var(--bg-gray);
            display:flex;
            min-height:100vh;
            color:var(--text-dark);
        }

        /* SIDEBAR */

        .sidebar{
            width:var(--sidebar-width);
            background:var(--primary);
            color:white;
            padding:24px;
            position:fixed;
            height:100vh;
            display:flex;
            flex-direction:column;
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
            color:rgba(255,255,255,0.85);

            display:flex;
            align-items:center;
            gap:12px;

            padding:13px 16px;
            border-radius:10px;

            transition:0.25s ease;

            font-size:14px;
            font-weight:500;
        }

        .nav-item a:hover,
        .nav-item.active a{
            background:rgba(255,255,255,0.12);
            color:white;
        }

        .nav-icon{
            width:20px;
            height:20px;
            display:flex;
            align-items:center;
            justify-content:center;
        }

        .nav-icon svg{
            width:20px;
            height:20px;
        }

        /* MAIN CONTENT */

        .main-content{
            margin-left:var(--sidebar-width);
            width:100%;
            padding:42px;
        }

        .page-title{
            font-family:'Poppins', sans-serif;
            font-size:30px;
            margin-bottom:26px;
        }

        /* CLAIM CARD */

        .claim-card{
            background:white;
            border-radius:20px;
            padding:28px;

            border:1px solid #EAEAEA;

            box-shadow:
                0 10px 28px rgba(0,0,0,0.04);
        }

        .claim-heading{
            font-size:18px;
            font-weight:700;
            margin-bottom:24px;
        }

        /* TABS */

        .tabs{
            display:flex;
            gap:26px;
            margin-bottom:24px;
            border-bottom:1px solid #ECECEC;
            padding-bottom:14px;
        }

        .tab{
            font-size:13px;
            font-weight:600;
            color:#8A8A8A;
            cursor:pointer;
            position:relative;
            padding-bottom:10px;
        }

        .tab.active{
            color:var(--primary);
        }

        .tab.active::after{
            content:'';
            position:absolute;
            left:0;
            bottom:-15px;

            width:100%;
            height:3px;

            background:var(--primary);
            border-radius:10px;
        }

        /* TABLE */

        .table-wrapper{
            overflow-x:auto;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        thead{
            background:#FAFAFA;
        }

        th{
            text-align:left;
            padding:14px 16px;
            font-size:12px;
            color:#777;
            font-weight:600;
        }

        td{
            padding:18px 16px;
            font-size:13px;
            border-top:1px solid #F0F0F0;
        }

        tr:hover{
            background:#FCFCFC;
        }

        /* STATUS */

        .status{
            padding:6px 12px;
            border-radius:30px;
            font-size:11px;
            font-weight:600;
            display:inline-block;
        }

        .pending{
            background:#FFF4DF;
            color:var(--pending);
        }

        .approved{
            background:#E8F7EC;
            color:var(--approved);
        }

        .rejected{
            background:#FDECEC;
            color:var(--rejected);
        }

        /* CLICKABLE ITEM */

        .item-link{
            color:var(--primary);
            cursor:pointer;
            font-weight:600;
        }

        .item-link:hover{
            text-decoration:underline;
        }

        /* MODAL */

        .modal{
            display:none;
            position:fixed;
            z-index:999;
            left:0;
            top:0;
            width:100%;
            height:100%;
            background:rgba(0,0,0,0.45);

            justify-content:center;
            align-items:center;
        }

        .modal-content{
            background:white;
            width:760px;
            max-width:95%;
            border-radius:20px;
            padding:28px;
            position:relative;
        }

        .close-btn{
            position:absolute;
            top:16px;
            right:20px;
            font-size:28px;
            cursor:pointer;
            color:#777;
        }

        .modal-body{
            display:flex;
            gap:28px;
        }

        .modal-image img{
            width:240px;
            height:280px;
            object-fit:cover;
            border-radius:14px;
            background:#EEE;
        }

        .modal-details{
            flex:1;
        }

        .modal-details h2{
            font-size:28px;
            margin-bottom:10px;
        }

        .detail-row{
            margin-top:14px;
            font-size:14px;
            line-height:1.5;
        }

        @media(max-width:768px){

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

            .page-title{
                font-size:24px;
            }

            .claim-card{
                padding:20px;
            }

            .modal-body{
                flex-direction:column;
            }

            .modal-image img{
                width:100%;
                height:auto;
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

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                    </span>

                    <span class="nav-text">Dashboard</span>

                </a>
            </li>

            <li class="nav-item">
                <a href="report-item.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </span>

                    <span class="nav-text">Report Item</span>

                </a>
            </li>

            <li class="nav-item">
                <a href="browse-items.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>

                    <span class="nav-text">Browse Items</span>

                </a>
            </li>

            <li class="nav-item active">
                <a href="claims.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </span>

                    <span class="nav-text">My Claims</span>

                </a>
            </li>

            <li class="nav-item">
                <a href="notif.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                    </span>

                    <span class="nav-text">Notifications</span>

                </a>
            </li>

            <li class="nav-item">
                <a href="messages.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </span>

                    <span class="nav-text">Messages</span>

                </a>
            </li>

            <li class="nav-item" style="margin-top:auto;">
                <a href="logout.php">

                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </span>

                    <span class="nav-text">Logout</span>

                </a>
            </li>

        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <h1 class="page-title">
            Claim System Page
        </h1>

        <div class="claim-card">

            <div class="claim-heading">
                Claims
            </div>

            <!-- TABS -->
            <div class="tabs">

                <div class="tab active"
                    onclick="filterClaims('all', this)">
                    All
                </div>

                <div class="tab"
                    onclick="filterClaims('pending', this)">
                    Pending
                </div>

                <div class="tab"
                    onclick="filterClaims('approved', this)">
                    Approved
                </div>

                <div class="tab"
                    onclick="filterClaims('rejected', this)">
                    Rejected
                </div>

            </div>

            <!-- TABLE -->
            <div class="table-wrapper">

                <table>

                    <thead>

                        <tr>
                            <th>Item</th>
                            <th>Claimant</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody id="claimsTable">

                        <!-- ROW -->
                        <tr data-status="pending">

                            <td>
                                <span class="item-link"
                                    onclick="openModal(
                                        'Black Backpack',
                                        'pending',
                                        'Bag',
                                        'Bleachers',
                                        'May 20, 2025 - 10:00 AM',
                                        'Black backpack with minimal design. Left near the basketball court.',
                                        'Juan Dela Cruz',
                                        'https://via.placeholder.com/250'
                                    )">
                                    Black Backpack
                                </span>
                            </td>

                            <td>Juan Dela Cruz</td>

                            <td>May 20, 2025</td>

                            <td>
                                <span class="status pending">
                                    Pending
                                </span>
                            </td>

                        </tr>

                        <!-- ROW -->
                        <tr data-status="approved">

                            <td>
                                <span class="item-link"
                                    onclick="openModal(
                                        'Silver Watch',
                                        'approved',
                                        'Watch',
                                        'Library',
                                        'May 19, 2025 - 9:30 AM',
                                        'Silver watch found near the library entrance.',
                                        'Maria Santos',
                                        'https://via.placeholder.com/250'
                                    )">
                                    Silver Watch
                                </span>
                            </td>

                            <td>Maria Santos</td>

                            <td>May 19, 2025</td>

                            <td>
                                <span class="status approved">
                                    Approved
                                </span>
                            </td>

                        </tr>

                        <!-- ROW -->
                        <tr data-status="rejected">

                            <td>
                                <span class="item-link"
                                    onclick="openModal(
                                        'ID Card',
                                        'rejected',
                                        'Identification',
                                        'Gym',
                                        'May 18, 2025 - 1:15 PM',
                                        'Student ID card found near the gym area.',
                                        'Joshua Garcia',
                                        'https://via.placeholder.com/250'
                                    )">
                                    ID Card
                                </span>
                            </td>

                            <td>Joshua Garcia</td>

                            <td>May 18, 2025</td>

                            <td>
                                <span class="status rejected">
                                    Rejected
                                </span>
                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- MODAL -->
    <div class="modal" id="itemModal">

        <div class="modal-content">

            <span class="close-btn"
                onclick="closeModal()">
                &times;
            </span>

            <div class="modal-body">

                <div class="modal-image">

                    <img id="modalImage"
                        src=""
                        alt="Item">

                </div>

                <div class="modal-details">

                    <h2 id="modalTitle">
                        Item Name
                    </h2>

                    <span class="status"
                        id="modalStatus">
                        Pending
                    </span>

                    <div class="detail-row">
                        <strong>Category:</strong>
                        <span id="modalCategory"></span>
                    </div>

                    <div class="detail-row">
                        <strong>Location:</strong>
                        <span id="modalLocation"></span>
                    </div>

                    <div class="detail-row">
                        <strong>Date:</strong>
                        <span id="modalDate"></span>
                    </div>

                    <div class="detail-row">
                        <strong>Description:</strong>
                        <span id="modalDescription"></span>
                    </div>

                    <div class="detail-row">
                        <strong>Posted By:</strong>
                        <span id="modalUser"></span>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script>

        // FILTER CLAIMS
        function filterClaims(status, clickedTab){

            const tabs = document.querySelectorAll('.tab');

            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            clickedTab.classList.add('active');

            const rows = document.querySelectorAll('#claimsTable tr');

            rows.forEach(row => {

                if(status === 'all'){

                    row.style.display = '';

                } else {

                    if(row.dataset.status === status){

                        row.style.display = '';

                    } else {

                        row.style.display = 'none';

                    }

                }

            });

        }

        // OPEN MODAL
        function openModal(
            title,
            status,
            category,
            location,
            date,
            description,
            user,
            image
        ){

            document.getElementById('modalTitle').innerText =
                title;

            document.getElementById('modalCategory').innerText =
                category;

            document.getElementById('modalLocation').innerText =
                location;

            document.getElementById('modalDate').innerText =
                date;

            document.getElementById('modalDescription').innerText =
                description;

            document.getElementById('modalUser').innerText =
                user;

            document.getElementById('modalImage').src =
                image;

            const statusElement =
                document.getElementById('modalStatus');

            statusElement.innerText =
                status.charAt(0).toUpperCase() + status.slice(1);

            statusElement.className =
                'status ' + status;

            document.getElementById('itemModal').style.display =
                'flex';
        }

        // CLOSE MODAL
        function closeModal(){

            document.getElementById('itemModal').style.display =
                'none';
        }

        // CLOSE WHEN CLICK OUTSIDE
        window.onclick = function(event){

            const modal =
                document.getElementById('itemModal');

            if(event.target === modal){

                closeModal();

            }

        }

    </script>

</body>
</html>
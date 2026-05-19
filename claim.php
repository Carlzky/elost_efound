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

        /* =========================
           SIDEBAR
        ========================== */

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

        /* =========================
           MAIN CONTENT
        ========================== */

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

        /* =========================
           CLAIM CARD
        ========================== */

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

        /* =========================
           TABS
        ========================== */

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
            transition:0.25s ease;
        }

        .tab:hover{
            color:var(--primary);
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

        /* =========================
           TABLE
        ========================== */

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

        /* =========================
           STATUS
        ========================== */

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

        /* =========================
           ACTIONS
        ========================== */

        .actions{
            display:flex;
            gap:12px;
            align-items:center;
        }

        .action-btn{
            border:none;
            background:none;
            cursor:pointer;
            font-size:16px;
            transition:0.2s ease;
        }

        .action-btn:hover{
            transform:scale(1.2);
        }

        /* =========================
           RESPONSIVE
        ========================== */

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
                E-LOST KOH<br>
                E-FOUND MOH
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

            <li class="nav-item active">
                <a href="claims.php">
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
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody id="claimsTable">

                        <!-- ROW -->
                        <tr data-status="pending">

                            <td>Black Backpack</td>

                            <td>Juan Dela Cruz</td>

                            <td>May 20, 2025</td>

                            <td>
                                <span class="status pending">
                                    Pending
                                </span>
                            </td>

                            <td>

                                <div class="actions">

                                    <button class="action-btn"
                                        onclick="approveClaim(this)">
                                        ✔️
                                    </button>

                                    <button class="action-btn"
                                        onclick="rejectClaim(this)">
                                        ❌
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <!-- ROW -->
                        <tr data-status="pending">

                            <td>Silver Watch</td>

                            <td>Maria Santos</td>

                            <td>May 19, 2025</td>

                            <td>
                                <span class="status pending">
                                    Pending
                                </span>
                            </td>

                            <td>

                                <div class="actions">

                                    <button class="action-btn"
                                        onclick="approveClaim(this)">
                                        ✔️
                                    </button>

                                    <button class="action-btn"
                                        onclick="rejectClaim(this)">
                                        ❌
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <!-- ROW -->
                        <tr data-status="rejected">

                            <td>ID Card</td>

                            <td>Joshua Garcia</td>

                            <td>May 18, 2025</td>

                            <td>
                                <span class="status rejected">
                                    Rejected
                                </span>
                            </td>

                            <td>

                                <div class="actions">

                                    <button class="action-btn"
                                        onclick="approveClaim(this)">
                                        ✔️
                                    </button>

                                    <button class="action-btn"
                                        onclick="rejectClaim(this)">
                                        ❌
                                    </button>

                                </div>

                            </td>

                        </tr>

                        <!-- ROW -->
                        <tr data-status="approved">

                            <td>Wallet</td>

                            <td>Carlo Reyes</td>

                            <td>May 17, 2025</td>

                            <td>
                                <span class="status approved">
                                    Approved
                                </span>
                            </td>

                            <td>

                                <div class="actions">

                                    <button class="action-btn"
                                        onclick="approveClaim(this)">
                                        ✔️
                                    </button>

                                    <button class="action-btn"
                                        onclick="rejectClaim(this)">
                                        ❌
                                    </button>

                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- JAVASCRIPT -->
    <script>

        // FILTER CLAIMS
        function filterClaims(status, clickedTab){

            // REMOVE ACTIVE CLASS
            const tabs = document.querySelectorAll('.tab');

            tabs.forEach(tab => {
                tab.classList.remove('active');
            });

            // ADD ACTIVE CLASS
            clickedTab.classList.add('active');

            // FILTER ROWS
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

        // APPROVE CLAIM
        function approveClaim(button){

            const row = button.closest('tr');

            row.dataset.status = 'approved';

            row.querySelector('.status').className =
                'status approved';

            row.querySelector('.status').innerText =
                'Approved';

        }

        // REJECT CLAIM
        function rejectClaim(button){

            const row = button.closest('tr');

            row.dataset.status = 'rejected';

            row.querySelector('.status').className =
                'status rejected';

            row.querySelector('.status').innerText =
                'Rejected';

        }

    </script>

</body>
</html>
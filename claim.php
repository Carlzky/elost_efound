<?php
session_start();
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    die("Login required.");
}

$user_id = $_SESSION['user_id'];

/*FETCH CLAIMS HERE*/

$sql = "
SELECT 
    c.claim_id,
    c.claim_status,
    c.claimed_at,
    c.claimant_user_id,

    f.found_id,
    f.item_name,
    f.category,
    f.location_found,
    f.date_found,
    f.description,
    f.item_image,

    u.username AS claimant_name

FROM claims c
JOIN found_items f ON c.found_item_id = f.found_id
JOIN users u ON c.claimant_user_id = u.id
WHERE f.user_id = ?
ORDER BY c.claimed_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

/*FETCH FOUND REPORTS*/

$found_sql = "
SELECT
    fr.report_id,
    fr.report_status,
    fr.created_at,

    l.lost_id,
    l.item_name,
    l.category,
    l.location_lost,
    l.date_lost,
    l.description,
    l.item_image,

    u.username AS finder_name

FROM found_reports fr

JOIN lost_items l
ON fr.lost_item_id = l.lost_id

JOIN users u
ON fr.finder_user_id = u.id

WHERE l.user_id = ?

ORDER BY fr.created_at DESC
";

$found_stmt = $conn->prepare($found_sql);
$found_stmt->bind_param("i", $user_id);
$found_stmt->execute();

$found_result = $found_stmt->get_result();


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Claim System - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/claim_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

   
</head>

<body>

    <!-- SIDEBAR -->
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
                <a href="claim.php">

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
                <a href="#" onclick="openLogoutModal()">
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

    <div class="main-content">

        <h1 class="page-title">
            Claim System Page
        </h1>

        <div class="claim-card">

            <div class="claim-heading">
                Claims
            </div>

            <div class="claim-type-tabs">

    <div class="claim-type active"
        onclick="filterClaimType('lost', this)">
        Lost Item Claims
    </div>

    <div class="claim-type"
        onclick="filterClaimType('found', this)">
        Found Item Claims
    </div>

</div>

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
<?php while($row = $result->fetch_assoc()): ?>

<tr 
    data-status="<?= strtolower($row['claim_status']) ?>"
    data-claimtype="lost"
>

    <td>
        <span class="item-link"
            onclick="openModal(...)">
            <?= htmlspecialchars($row['item_name']) ?>
        </span>
    </td>

    <td><?= htmlspecialchars($row['claimant_name']) ?></td>

    <td><?= date("M d, Y", strtotime($row['claimed_at'])) ?></td>

    <td>
        <span class="status <?= strtolower($row['claim_status']) ?>">
            <?= $row['claim_status'] ?>
        </span>
    </td>

</tr>

<?php endwhile; ?>

<?php while($found = $found_result->fetch_assoc()): ?>

<tr
    data-status="<?= strtolower($found['report_status']) ?>"
    data-claimtype="found"
>

    <td>
        <span class="item-link">
            <?= htmlspecialchars($found['item_name']) ?>
        </span>
    </td>

    <td>
        <?= htmlspecialchars($found['finder_name']) ?>
    </td>

    <td>
        <?= date("M d, Y", strtotime($found['created_at'])) ?>
    </td>

    <td>
        <span class="status <?= strtolower($found['report_status']) ?>">
            <?= $found['report_status'] ?>
        </span>
    </td>

</tr>

<?php endwhile; ?>
</tbody>

                </table>

            </div>

        </div>

    </div>

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

    <script>

        let currentClaimType = 'lost';
        let currentStatus = 'all';

        function filterClaims(status, clickedTab){

    currentStatus = status;

    const tabs = document.querySelectorAll('.tab');

    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    clickedTab.classList.add('active');

    applyFilters();
}

function filterClaimType(type, clickedTab){

    currentClaimType = type;

    const tabs =
        document.querySelectorAll('.claim-type');

    tabs.forEach(tab => {
        tab.classList.remove('active');
    });

    clickedTab.classList.add('active');

    applyFilters();
}

function applyFilters(){

    const rows =
        document.querySelectorAll('#claimsTable tr');

    rows.forEach(row => {

        const rowStatus =
            row.dataset.status;

        const rowType =
            row.dataset.claimtype;

        let show = true;

        if(currentClaimType !== rowType){

            show = false;
        }

        if(currentStatus !== 'all' &&
           currentStatus !== rowStatus){

            show = false;
        }

        row.style.display =
            show ? '' : 'none';

    });

}

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

        function closeModal(){

            document.getElementById('itemModal').style.display =
                'none';
        }

        window.onclick = function(event){

            const modal =
                document.getElementById('itemModal');

            if(event.target === modal){

                closeModal();

            }

        }

        function openLogoutModal() {
            document.getElementById("logoutOverlay").style.display = "flex";
        }

        function closeLogoutModal() {
            document.getElementById("logoutOverlay").style.display = "none";
        }

        function confirmLogout() {
            window.location.href = "logout.php";
        }

        applyFilters();
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
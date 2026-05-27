<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
include 'config/db.php';



// Active filter tab
$filter = $_GET['status'] ?? 'all';

// Build query based on filter
$allowed = ['all','Pending','Approved','Rejected'];
if(!in_array($filter, $allowed)) $filter = 'all';

if($filter === 'all'){
    $sql = "
        SELECT c.claim_id, li.item_name, u.username AS claimant,
               c.claim_date, c.claim_status
        FROM claims c
        JOIN lost_items li ON c.item_id = li.item_id
        JOIN users u ON c.claimant_user_id = u.user_id
        ORDER BY c.claim_date DESC
    ";
    $result = $conn->query($sql);
} else {
    $sql = "
        SELECT c.claim_id, li.item_name, u.username AS claimant,
               c.claim_date, c.claim_status
        FROM claims c
        JOIN lost_items li ON c.item_id = li.item_id
        JOIN users u ON c.claimant_user_id = u.user_id
        WHERE c.claim_status = ?
        ORDER BY c.claim_date DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $filter);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Count per status
$counts = ['all'=>0,'Pending'=>0,'Approved'=>0,'Rejected'=>0];
$cq = $conn->query("SELECT claim_status, COUNT(*) AS n FROM claims GROUP BY claim_status");
while($row = $cq->fetch_assoc()){
    $counts[$row['claim_status']] = $row['n'];
    $counts['all'] += $row['n'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Claim Requests - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
    --pg:#1F5D4A; --pgd:#143F32; --gold:#F1B846; --lg:#BBC34A;
    --bg:#F4F4F4; --white:#FFFFFF; --dark:#1A1A1A; --muted:#7A7A7A;
    --border:#E5E5E5; --sw:220px;
    --pending:#E9A93D; --approved:#4CAF50; --rejected:#E74C3C;
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--dark);display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:var(--sw);background:var(--pg);position:fixed;height:100vh;display:flex;flex-direction:column;padding:24px 18px;z-index:200;}
.logo-wrap{display:flex;align-items:center;gap:12px;margin-bottom:36px;}
.logo-box{width:52px;height:52px;background:linear-gradient(135deg,var(--pg),var(--pgd));border:2px solid var(--gold);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 8px 22px rgba(0,0,0,.3);transition:transform .6s cubic-bezier(.2,.8,.2,1);flex-shrink:0;}
.logo-box:hover{transform:scale(1.08) rotate(4deg);}
.logo-txt{font-family:'Poppins',sans-serif;font-size:13px;font-weight:700;color:#fff;line-height:1.3;}
.logo-txt span{color:var(--lg);}
.nav{list-style:none;display:flex;flex-direction:column;gap:4px;flex:1;}
.nav li a{display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;text-decoration:none;color:rgba(255,255,255,.78);font-size:13.5px;font-weight:500;transition:.2s ease;}
.nav li a:hover{background:rgba(255,255,255,.07);color:#fff;}
.nav li.active a{background:rgba(255,255,255,.14);color:#fff;}
.nav li.mt{margin-top:auto;}
.nav-ico{width:18px;height:18px;flex-shrink:0;opacity:.85;}

/* MAIN */
.main{margin-left:var(--sw);flex:1;display:flex;flex-direction:column;}

/* TOP BAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;gap:16px;position:sticky;top:0;z-index:100;}
.search-wrap{position:relative;flex:1;max-width:360px;}
.search-wrap input{width:100%;padding:9px 14px 9px 38px;border:1px solid #E0E0E0;border-radius:9px;font-family:'Inter',sans-serif;font-size:13.5px;background:#FAFAFA;outline:none;transition:border .2s;}
.search-wrap input:focus{border-color:var(--pg);background:#fff;}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#AAA;pointer-events:none;}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:18px;}
.bell-btn{background:none;border:none;cursor:pointer;color:#555;display:flex;align-items:center;transition:transform .2s;text-decoration:none;}
.bell-btn:hover{transform:scale(1.1);}
.admin-pill{display:flex;align-items:center;gap:10px;padding:6px 14px 6px 6px;background:#F4F4F4;border:1px solid var(--border);border-radius:999px;font-size:13.5px;font-weight:500;color:var(--dark);}
.admin-av{width:30px;height:30px;border-radius:50%;background:var(--pg);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;}

/* CONTENT */
.content{padding:32px;}
.page-title{font-family:'Poppins',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px;}
.page-sub{color:var(--muted);font-size:13px;margin-bottom:24px;}

/* FILTER TABS */
.filter-tabs{display:flex;gap:6px;margin-bottom:22px;flex-wrap:wrap;}
.tab{padding:7px 18px;border-radius:8px;border:1px solid var(--border);background:var(--white);font-size:13px;font-weight:500;color:var(--muted);text-decoration:none;transition:.2s;display:inline-flex;align-items:center;gap:7px;}
.tab:hover{border-color:#CCC;color:var(--dark);}
.tab.active{background:var(--pg);border-color:var(--pg);color:#fff;}
.tab-count{font-size:11px;padding:2px 7px;border-radius:20px;font-weight:700;}
.tab.active .tab-count{background:rgba(255,255,255,.2);color:#fff;}
.tab:not(.active) .tab-count{background:#F0F0F0;color:var(--muted);}

/* TABLE PANEL */
.panel{background:var(--white);border-radius:14px;border:1px solid var(--border);box-shadow:0 3px 12px rgba(0,0,0,.04);overflow:hidden;}
table{width:100%;border-collapse:collapse;font-size:13px;}
thead th{padding:11px 20px;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);background:#FAFAFA;border-bottom:1px solid #F0F0F0;}
tbody td{padding:14px 20px;border-bottom:1px solid #F8F8F8;color:var(--dark);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:#FAFCFB;}
.td-id{color:var(--muted);font-size:12px;}

/* STATUS BADGES */
.badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.3px;}
.badge-Pending{background:#FEF6E6;color:var(--pending);}
.badge-Approved{background:#E8F5EA;color:var(--approved);}
.badge-Rejected{background:#FEE8E8;color:var(--rejected);}

/* ACTION BUTTON */
.btn-view{display:inline-flex;align-items:center;gap:5px;padding:6px 14px;border-radius:7px;background:var(--pg);color:#fff;font-size:12px;font-weight:600;text-decoration:none;border:none;cursor:pointer;transition:.2s;}
.btn-view:hover{background:var(--pgd);}

/* EMPTY STATE */
.empty{text-align:center;padding:52px 20px;color:var(--muted);font-size:14px;}

/* LOGOUT MODAL */
.lo-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(6px);display:none;justify-content:center;align-items:center;z-index:9999;}
.lo-modal{background:#fff;padding:32px;border-radius:20px;text-align:center;width:300px;border:1px solid var(--border);box-shadow:0 20px 50px rgba(0,0,0,.15);animation:popIn .25s forwards;transform:scale(.85);opacity:0;}
@keyframes popIn{to{transform:scale(1);opacity:1;}}
.lo-modal h2{font-family:'Poppins',sans-serif;font-size:20px;margin-bottom:8px;color:var(--pg);}
.lo-modal p{font-size:14px;color:var(--muted);margin-bottom:22px;}
.lo-btns{display:flex;gap:12px;}
.lo-cancel{flex:1;padding:11px;border:1px solid #E0E0E0;border-radius:10px;background:#F4F4F4;font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;}
.lo-cancel:hover{background:#E8E8E8;}
.lo-confirm{flex:1;padding:11px;border:none;border-radius:10px;background:var(--pg);color:#fff;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;}
.lo-confirm:hover{background:var(--pgd);}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="logo-wrap">
        <div class="logo-box">🔍</div>
        <div class="logo-txt">E-LOST <span>MOH</span><br>E-FOUND <span>KOH</span></div>
    </div>
    <ul class="nav">
        <li><a href="admin-dashboard.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard</a></li>
        <li><a href="admin-items.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>Manage Items</a></li>
        <li class="active"><a href="admin-claims.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Claim Requests</a></li>
        <li><a href="admin-users.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users</a></li>
        <li><a href="admin-reports.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Reports</a></li>
        <li><a href="admin-announcements.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>Announcements</a></li>
        <li><a href="admin-settings.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Settings</a></li>
        <li class="mt"><a href="#" onclick="document.getElementById('loOverlay').style.display='flex'"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Logout</a></li>
    </ul>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="search-wrap">
            <svg class="search-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Search items, users, claims…" id="searchInput" oninput="filterTable()">
        </div>
        <div class="topbar-right">
            <a href="admin-notif.php" class="bell-btn">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </a>
            <div class="admin-pill">
                <div class="admin-av"><?php echo strtoupper(substr($admin_name,0,1)); ?></div>
                <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>
    </div>

    <div class="content">
        <h1 class="page-title">Claim Requests</h1>
        <p class="page-sub">Review and manage user claim requests</p>

        <!-- FILTER TABS -->
        <div class="filter-tabs">
            <a href="admin-claims.php" class="tab <?php echo $filter==='all'?'active':''; ?>">
                All <span class="tab-count"><?php echo $counts['all']; ?></span>
            </a>
            <a href="admin-claims.php?status=Pending" class="tab <?php echo $filter==='Pending'?'active':''; ?>">
                Pending <span class="tab-count"><?php echo $counts['Pending']; ?></span>
            </a>
            <a href="admin-claims.php?status=Approved" class="tab <?php echo $filter==='Approved'?'active':''; ?>">
                Approved <span class="tab-count"><?php echo $counts['Approved']; ?></span>
            </a>
            <a href="admin-claims.php?status=Rejected" class="tab <?php echo $filter==='Rejected'?'active':''; ?>">
                Rejected <span class="tab-count"><?php echo $counts['Rejected']; ?></span>
            </a>
        </div>

        <!-- TABLE -->
        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Claim ID</th>
                        <th>Item Name</th>
                        <th>Claimant</th>
                        <th>Date Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="claimsTable">
                <?php if($result && $result->num_rows > 0):
                    while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td class="td-id">CC-<?php echo str_pad($row['claim_id'],4,'0',STR_PAD_LEFT); ?></td>
                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['claimant']); ?></td>
                    <td><?php echo date('M j, Y', strtotime($row['claim_date'])); ?></td>
                    <td><span class="badge badge-<?php echo $row['claim_status']; ?>"><?php echo $row['claim_status']; ?></span></td>
                    <td><a href="admin-claim-detail.php?id=<?php echo $row['claim_id']; ?>" class="btn-view">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View
                    </a></td>
                </tr>
                <?php endwhile; else: ?>
                <tr><td colspan="6" class="empty">No claim requests found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- LOGOUT MODAL -->
<div class="lo-overlay" id="loOverlay">
    <div class="lo-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="lo-btns">
            <button class="lo-cancel" onclick="document.getElementById('loOverlay').style.display='none'">Cancel</button>
            <button class="lo-confirm" onclick="window.location.href='admin-logout.php'">Confirm</button>
        </div>
    </div>
</div>

<script>
function filterTable(){
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#claimsTable tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
document.getElementById('loOverlay').addEventListener('click', function(e){
    if(e.target===this) this.style.display='none';
});
</script>
</body>
</html>
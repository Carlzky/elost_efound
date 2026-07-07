<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../config/db.php';

// ── AUTH GUARD ───────────────────────────────────────────────────

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

// ── SUMMARY STATS ────────────────────────────────────────────────
$stats = [];
$queries = [
    'total_lost'     => "SELECT COUNT(*) AS n FROM lost_items",
    'total_found'    => "SELECT COUNT(*) AS n FROM found_items",
    'total_claims'   => "SELECT COUNT(*) AS n FROM claims",
    'total_users'    => "SELECT COUNT(*) AS n FROM users",
];
foreach($queries as $key => $sql){
    $r = $conn->query($sql);
    $stats[$key] = $r ? $r->fetch_assoc()['n'] : 0;
}

// ── CLAIMS BY STATUS ─────────────────────────────────────────────
$claims_by_status = ['Pending'=>0,'Approved'=>0,'Rejected'=>0];
$cq = $conn->query("SELECT claim_status, COUNT(*) AS n FROM claims GROUP BY claim_status");
if($cq){ while($row = $cq->fetch_assoc()){ $claims_by_status[$row['claim_status']] = (int)$row['n']; } }
$claims_max = max(1, max($claims_by_status));

// ── ITEMS BY CATEGORY (lost items) ──────────────────────────────
$categories = [];
$catq = $conn->query("SELECT category, COUNT(*) AS n FROM lost_items GROUP BY category ORDER BY n DESC LIMIT 6");
if($catq){ while($row = $catq->fetch_assoc()){ $categories[$row['category']] = (int)$row['n']; } }
$cat_max = !empty($categories) ? max($categories) : 1;

// ── MONTHLY CLAIMS TREND (last 6 months) ────────────────────────
$months = [];
for($i = 5; $i >= 0; $i--){
    $label = date('M Y', strtotime("-$i months"));
    $key   = date('Y-m', strtotime("-$i months"));
    $months[$key] = ['label' => $label, 'count' => 0];
}
$trendq = $conn->query("
    SELECT DATE_FORMAT(claim_date, '%Y-%m') AS ym, COUNT(*) AS n
    FROM claims
    WHERE claim_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY ym
");
if($trendq){
    while($row = $trendq->fetch_assoc()){
        if(isset($months[$row['ym']])) $months[$row['ym']]['count'] = (int)$row['n'];
    }
}
$trend_max = 1;
foreach($months as $m){ $trend_max = max($trend_max, $m['count']); }

// ── RESOLUTION RATE ──────────────────────────────────────────────
$resolved_pct = $stats['total_claims'] > 0
    ? round((($claims_by_status['Approved']) / $stats['total_claims']) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Analytics - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
    --pg:#1F5D4A; --pgd:#143F32; --gold:#F1B846; --lg:#BBC34A;
    --bg:#F4F4F4; --white:#FFFFFF; --dark:#1A1A1A; --muted:#7A7A7A;
    --border:#E5E5E5; --sw:220px;
    --c-lost:#E07B39; --c-found:#3A9E6F; --c-pending:#E9A93D;
    --c-approved:#4CAF50; --c-rejected:#E74C3C; --c-users:#5B8DEF;
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
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:18px;}
.bell-btn{background:none;border:none;cursor:pointer;color:#555;display:flex;align-items:center;transition:transform .2s;text-decoration:none;}
.bell-btn:hover{transform:scale(1.1);}
.admin-pill{display:flex;align-items:center;gap:10px;padding:6px 14px 6px 6px;background:#F4F4F4;border:1px solid var(--border);border-radius:999px;font-size:13.5px;font-weight:500;color:var(--dark);}
.admin-av{width:30px;height:30px;border-radius:50%;background:var(--pg);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;}

/* CONTENT */
.content{padding:32px;}
.page-title{font-family:'Poppins',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px;}
.page-sub{color:var(--muted);font-size:13px;margin-bottom:28px;}

/* SUMMARY ROW */
.summary-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.summary-card{background:var(--white);border-radius:14px;border:1px solid var(--border);padding:18px 20px;box-shadow:0 3px 12px rgba(0,0,0,.04);}
.summary-label{font-size:11.5px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;}
.summary-num{font-family:'Poppins',sans-serif;font-size:30px;font-weight:700;}

/* PANEL GRID */
.charts-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:20px;margin-bottom:20px;}
.panel{background:var(--white);border-radius:14px;border:1px solid var(--border);box-shadow:0 3px 12px rgba(0,0,0,.04);padding:22px;}
.panel h2{font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;margin-bottom:20px;}

/* BAR CHART (vertical, monthly trend) */
.bar-chart{display:flex;align-items:flex-end;gap:14px;height:180px;padding:0 4px;}
.bar-col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;height:100%;gap:8px;}
.bar-fill{width:100%;max-width:34px;background:linear-gradient(180deg,var(--pg),var(--pgd));border-radius:6px 6px 0 0;transition:height .4s ease;position:relative;}
.bar-val{font-size:11px;font-weight:700;color:var(--pg);margin-bottom:4px;}
.bar-label{font-size:11px;color:var(--muted);margin-top:6px;}

/* HORIZONTAL BARS (category breakdown) */
.hbar-row{margin-bottom:14px;}
.hbar-top{display:flex;justify-content:space-between;font-size:12.5px;margin-bottom:6px;}
.hbar-name{font-weight:500;}
.hbar-count{color:var(--muted);font-weight:600;}
.hbar-track{background:#F0F0F0;border-radius:20px;height:9px;overflow:hidden;}
.hbar-fill{height:100%;background:var(--pg);border-radius:20px;transition:width .4s ease;}

/* STATUS DONUT-ISH BREAKDOWN (simple stacked bar) */
.status-legend{display:flex;flex-direction:column;gap:12px;margin-top:6px;}
.legend-row{display:flex;align-items:center;gap:10px;font-size:13px;}
.legend-dot{width:11px;height:11px;border-radius:3px;flex-shrink:0;}
.legend-name{flex:1;}
.legend-count{font-weight:700;}
.stacked-bar{display:flex;height:14px;border-radius:20px;overflow:hidden;margin-bottom:16px;background:#F0F0F0;}
.stacked-seg{height:100%;transition:width .4s ease;}

/* RESOLUTION GAUGE */
.gauge-wrap{display:flex;align-items:center;gap:18px;}
.gauge-track{flex:1;background:#F0F0F0;border-radius:20px;height:16px;overflow:hidden;}
.gauge-fill{height:100%;background:linear-gradient(90deg,var(--c-approved),#3A9E6F);border-radius:20px;transition:width .4s ease;}
.gauge-pct{font-family:'Poppins',sans-serif;font-size:22px;font-weight:700;color:var(--c-approved);min-width:60px;text-align:right;}

/* RESPONSIVE */
@media(max-width:1200px){
    .summary-grid{grid-template-columns:repeat(2,1fr);}
    .charts-grid{grid-template-columns:1fr;}
}
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
        <li><a href="admin-dash.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard</a></li>
        <li><a href="admin-items.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>Manage Items</a></li>
        <li><a href="admin-claims.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Claim Requests</a></li>
        <li><a href="admin-users.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users</a></li>
        <li><a href="admin-reports.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Reports</a></li>
        <li class="active"><a href="admin-analytics.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Analytics</a></li>
        <li><a href="admin-announcements.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>Announcements</a></li>
        <li><a href="admin-settings.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Settings</a></li>
        <li class="mt"><a href="admin-login.php" onclick="document.getElementById('loOverlay').style.display='flex'"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Logout</a></li>
    </ul>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="topbar-right" style="margin-left:0;">
            <a href="admin-notif.php" class="bell-btn">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </a>
        </div>
        <div class="topbar-right">
            <div class="admin-pill">
                <div class="admin-av"><?php echo strtoupper(substr($admin_name,0,1)); ?></div>
                <?php echo htmlspecialchars($admin_name); ?>
            </div>
        </div>
    </div>

    <div class="content">
        <h1 class="page-title">Analytics</h1>
        <p class="page-sub">Insights into lost &amp; found activity across the system</p>

        <!-- SUMMARY -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-label">Total Lost Items</div>
                <div class="summary-num" style="color:var(--c-lost);"><?php echo $stats['total_lost']; ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Found Items</div>
                <div class="summary-num" style="color:var(--c-found);"><?php echo $stats['total_found']; ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Claims</div>
                <div class="summary-num" style="color:var(--pg);"><?php echo $stats['total_claims']; ?></div>
            </div>
            <div class="summary-card">
                <div class="summary-label">Total Users</div>
                <div class="summary-num" style="color:var(--c-users);"><?php echo $stats['total_users']; ?></div>
            </div>
        </div>

        <div class="charts-grid">
            <!-- MONTHLY TREND -->
            <div class="panel">
                <h2>Claims Trend — Last 6 Months</h2>
                <div class="bar-chart">
                    <?php foreach($months as $m):
                        $h = $m['count'] > 0 ? max(6, round(($m['count'] / $trend_max) * 150)) : 4;
                    ?>
                    <div class="bar-col">
                        <div class="bar-val"><?php echo $m['count']; ?></div>
                        <div class="bar-fill" style="height:<?php echo $h; ?>px;"></div>
                        <div class="bar-label"><?php echo $m['label']; ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- CLAIMS BY STATUS -->
            <div class="panel">
                <h2>Claims by Status</h2>
                <div class="stacked-bar">
                    <?php foreach(['Pending'=>'#E9A93D','Approved'=>'#4CAF50','Rejected'=>'#E74C3C'] as $st => $color):
                        $pct = $stats['total_claims'] > 0 ? round(($claims_by_status[$st] / $stats['total_claims']) * 100) : 0;
                    ?>
                    <div class="stacked-seg" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>;"></div>
                    <?php endforeach; ?>
                </div>
                <div class="status-legend">
                    <div class="legend-row"><div class="legend-dot" style="background:#E9A93D;"></div><div class="legend-name">Pending</div><div class="legend-count"><?php echo $claims_by_status['Pending']; ?></div></div>
                    <div class="legend-row"><div class="legend-dot" style="background:#4CAF50;"></div><div class="legend-name">Approved</div><div class="legend-count"><?php echo $claims_by_status['Approved']; ?></div></div>
                    <div class="legend-row"><div class="legend-dot" style="background:#E74C3C;"></div><div class="legend-name">Rejected</div><div class="legend-count"><?php echo $claims_by_status['Rejected']; ?></div></div>
                </div>
            </div>
        </div>

        <div class="charts-grid">
            <!-- ITEMS BY CATEGORY -->
            <div class="panel">
                <h2>Lost Items by Category</h2>
                <?php if(!empty($categories)): ?>
                    <?php foreach($categories as $cat => $n):
                        $pct = round(($n / $cat_max) * 100);
                    ?>
                    <div class="hbar-row">
                        <div class="hbar-top">
                            <span class="hbar-name"><?php echo htmlspecialchars($cat); ?></span>
                            <span class="hbar-count"><?php echo $n; ?></span>
                        </div>
                        <div class="hbar-track"><div class="hbar-fill" style="width:<?php echo $pct; ?>%;"></div></div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color:var(--muted);font-size:13px;">No category data yet.</p>
                <?php endif; ?>
            </div>

            <!-- RESOLUTION RATE -->
            <div class="panel">
                <h2>Claim Resolution Rate</h2>
                <p style="font-size:12.5px;color:var(--muted);margin-bottom:16px;">Percentage of total claims that were approved</p>
                <div class="gauge-wrap">
                    <div class="gauge-track"><div class="gauge-fill" style="width:<?php echo $resolved_pct; ?>%;"></div></div>
                    <div class="gauge-pct"><?php echo $resolved_pct; ?>%</div>
                </div>
            </div>
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
document.getElementById('loOverlay').addEventListener('click', function(e){
    if(e.target===this) this.style.display='none';
});
document.addEventListener('keydown', e => {
    if(e.key === 'Escape') document.getElementById('loOverlay').style.display = 'none';
});
</script>
</body>
</html>
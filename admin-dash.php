<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include "config/db.php";



// ── Stats ──────────────────────────────────────────────────────────
$stats = [];

$queries = [
    'total_lost'     => "SELECT COUNT(*) AS n FROM lost_items",
    'total_found'    => "SELECT COUNT(*) AS n FROM found_items",
    'pending_claims' => "SELECT COUNT(*) AS n FROM claims WHERE claim_status='Pending'",
    'approved_claims'=> "SELECT COUNT(*) AS n FROM claims WHERE claim_status='Approved'",
    'rejected_claims'=> "SELECT COUNT(*) AS n FROM claims WHERE claim_status='Rejected'",
    'total_users'    => "SELECT COUNT(*) AS n FROM users",
    'total_reports'  => "SELECT COUNT(*) AS n FROM reports",
    'resolved_items' => "SELECT COUNT(*) AS n FROM claims WHERE claim_status='Resolved'",
];

foreach($queries as $key => $sql){
    $r = $conn->query($sql);
    $stats[$key] = $r ? $r->fetch_assoc()['n'] : 0;
}

// ── Recent Claims ──────────────────────────────────────────────────
$recent_claims_sql = "
    SELECT c.claim_id, li.item_name, u.username AS claimant, c.claim_date, c.claim_status
    FROM claims c
    JOIN lost_items li ON c.item_id = li.item_id
    JOIN users u ON c.claimant_user_id = u.user_id
    ORDER BY c.claim_date DESC LIMIT 5
";
$recent_claims = $conn->query($recent_claims_sql);

// ── Recent Reports ─────────────────────────────────────────────────
$recent_reports_sql = "
    SELECT r.report_id, r.report_type, u.username, r.report_date, r.report_status
    FROM reports r
    JOIN users u ON r.reported_by = u.user_id
    ORDER BY r.report_date DESC LIMIT 5
";
$recent_reports = $conn->query($recent_reports_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - E-LOST KOH, E-FOUND MOH</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
/* ─── TOKENS ─────────────────────────────────────────────── */
:root {
    --pg:   #1F5D4A;
    --pgd:  #143F32;
    --gold: #F1B846;
    --lg:   #BBC34A;
    --bg:   #F4F4F4;
    --white:#FFFFFF;
    --dark: #1A1A1A;
    --muted:#7A7A7A;
    --border:#E5E5E5;
    --sw:   220px;

    --c-lost:     #E07B39;
    --c-found:    #3A9E6F;
    --c-pending:  #E9A93D;
    --c-approved: #4CAF50;
    --c-rejected: #E74C3C;
    --c-users:    #5B8DEF;
    --c-reports:  #9B59B6;
    --c-resolved: #17A589;
}

*{ margin:0; padding:0; box-sizing:border-box; }

body{
    font-family:'Inter',sans-serif;
    background:var(--bg);
    color:var(--dark);
    display:flex;
    min-height:100vh;
}

/* ─── SIDEBAR ────────────────────────────────────────────── */
.sidebar{
    width:var(--sw);
    background:var(--pg);
    position:fixed;
    height:100vh;
    display:flex;
    flex-direction:column;
    padding:24px 18px;
    z-index:200;
}

.logo-wrap{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:36px;
}

.logo-box{
    width:52px; height:52px;
    background:linear-gradient(135deg,var(--pg),var(--pgd));
    border:2px solid var(--gold);
    border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:22px;
    box-shadow:0 8px 22px rgba(0,0,0,0.3),inset 0 2px 5px rgba(255,255,255,0.15);
    transition:transform .6s cubic-bezier(.2,.8,.2,1);
    flex-shrink:0;
}
.logo-box:hover{ transform:scale(1.08) rotate(4deg); }

.logo-txt{
    font-family:'Poppins',sans-serif;
    font-size:13px; font-weight:700;
    color:#fff; line-height:1.3;
}
.logo-txt span{ color:var(--lg); }

.nav{
    list-style:none;
    display:flex; flex-direction:column;
    gap:4px; flex:1;
}

.nav li a{
    display:flex; align-items:center; gap:10px;
    padding:11px 14px;
    border-radius:10px;
    text-decoration:none;
    color:rgba(255,255,255,.78);
    font-size:13.5px; font-weight:500;
    transition:.2s ease;
}
.nav li a:hover{ background:rgba(255,255,255,.07); color:#fff; }
.nav li.active a{ background:rgba(255,255,255,.14); color:#fff; }
.nav li.mt{ margin-top:auto; }

.nav-ico{ width:18px; height:18px; flex-shrink:0; opacity:.85; }

/* ─── MAIN ───────────────────────────────────────────────── */
.main{
    margin-left:var(--sw);
    flex:1;
    display:flex;
    flex-direction:column;
    min-height:100vh;
}

/* ─── TOP BAR ────────────────────────────────────────────── */
.topbar{
    background:var(--white);
    border-bottom:1px solid var(--border);
    padding:14px 32px;
    display:flex;
    align-items:center;
    gap:16px;
    position:sticky; top:0; z-index:100;
}

.search-wrap{
    position:relative;
    flex:1; max-width:360px;
}
.search-wrap input{
    width:100%;
    padding:9px 14px 9px 38px;
    border:1px solid #E0E0E0;
    border-radius:9px;
    font-family:'Inter',sans-serif;
    font-size:13.5px;
    background:#FAFAFA;
    outline:none;
    transition:border .2s;
}
.search-wrap input:focus{ border-color:var(--pg); background:#fff; }
.search-ico{
    position:absolute; left:12px; top:50%;
    transform:translateY(-50%);
    color:#AAA; pointer-events:none;
}

.topbar-right{
    margin-left:auto;
    display:flex; align-items:center; gap:18px;
}

.bell-btn{
    background:none; border:none; cursor:pointer;
    color:#555; display:flex; align-items:center;
    transition:transform .2s; text-decoration:none;
}
.bell-btn:hover{ transform:scale(1.1); }

.admin-pill{
    display:flex; align-items:center; gap:10px;
    padding:6px 14px 6px 6px;
    background:#F4F4F4;
    border:1px solid var(--border);
    border-radius:999px;
    cursor:pointer;
    transition:.2s;
    text-decoration:none;
    color:var(--dark);
    font-size:13.5px; font-weight:500;
    position:relative;
}
.admin-pill:hover{ background:#EBEBEB; }

.admin-av{
    width:30px; height:30px;
    border-radius:50%;
    background:var(--pg);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:13px; font-weight:700;
}

.chevron{ color:#AAA; }

/* dropdown */
.pill-dropdown{
    display:none;
    position:absolute; top:calc(100% + 8px); right:0;
    background:#fff;
    border:1px solid var(--border);
    border-radius:12px;
    box-shadow:0 8px 28px rgba(0,0,0,0.1);
    min-width:150px;
    overflow:hidden;
    z-index:500;
}
.admin-pill:focus-within .pill-dropdown,
.admin-pill:hover .pill-dropdown{ display:block; }
.pill-dropdown a{
    display:flex; align-items:center; gap:8px;
    padding:11px 16px;
    font-size:13px; color:var(--dark);
    text-decoration:none;
    transition:background .15s;
}
.pill-dropdown a:hover{ background:#F8F8F8; }
.pill-dropdown a svg{ color:var(--muted); }

/* ─── CONTENT ────────────────────────────────────────────── */
.content{
    padding:32px;
    flex:1;
}

.page-title{
    font-family:'Poppins',sans-serif;
    font-size:26px; font-weight:700;
    margin-bottom:2px;
}
.page-sub{ color:var(--muted); font-size:13px; margin-bottom:28px; }

/* ─── STATS GRID ─────────────────────────────────────────── */
.stats-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:28px;
}

.stat-card{
    background:var(--white);
    border-radius:14px;
    border:1px solid var(--border);
    padding:20px 20px 16px;
    box-shadow:0 3px 12px rgba(0,0,0,0.04);
    display:flex; flex-direction:column;
    gap:14px;
    transition:transform .2s, box-shadow .2s;
}
.stat-card:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 22px rgba(0,0,0,0.08);
}

.stat-top{
    display:flex; justify-content:space-between; align-items:center;
}

.stat-icon{
    width:44px; height:44px;
    border-radius:11px;
    display:flex; align-items:center; justify-content:center;
    font-size:20px;
}

.stat-label{
    font-size:11.5px; color:var(--muted);
    font-weight:600; text-align:right;
    text-transform:uppercase; letter-spacing:.4px;
}

.stat-number{
    font-family:'Poppins',sans-serif;
    font-size:34px; font-weight:700;
}

.stat-link{
    font-size:12px; font-weight:600;
    text-decoration:none;
    display:inline-flex; align-items:center; gap:4px;
    opacity:.8; transition:opacity .15s;
}
.stat-link:hover{ opacity:1; }

/* icon bg tints */
.ic-lost    { background:#FEF0E6; color:var(--c-lost); }
.ic-found   { background:#E6F6EF; color:var(--c-found); }
.ic-pending { background:#FEF6E6; color:var(--c-pending); }
.ic-approved{ background:#E8F5EA; color:var(--c-approved); }
.ic-rejected{ background:#FEE8E8; color:var(--c-rejected); }
.ic-users   { background:#EAF0FE; color:var(--c-users); }
.ic-reports { background:#F3EAF9; color:var(--c-reports); }
.ic-resolved{ background:#E6FAF7; color:var(--c-resolved); }

/* number + link colors */
.cl-lost    { color:var(--c-lost); }    .lk-lost    { color:var(--c-lost); }
.cl-found   { color:var(--c-found); }   .lk-found   { color:var(--c-found); }
.cl-pending { color:var(--c-pending); } .lk-pending { color:var(--c-pending); }
.cl-approved{ color:var(--c-approved);}  .lk-approved{ color:var(--c-approved);}
.cl-rejected{ color:var(--c-rejected);}  .lk-rejected{ color:var(--c-rejected);}
.cl-users   { color:var(--c-users); }   .lk-users   { color:var(--c-users); }
.cl-reports { color:var(--c-reports); } .lk-reports { color:var(--c-reports); }
.cl-resolved{ color:var(--c-resolved);} .lk-resolved{ color:var(--c-resolved);}

/* ─── BOTTOM GRID ────────────────────────────────────────── */
.bottom-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.panel{
    background:var(--white);
    border-radius:14px;
    border:1px solid var(--border);
    box-shadow:0 3px 12px rgba(0,0,0,0.04);
    overflow:hidden;
}

.panel-head{
    padding:18px 22px 14px;
    display:flex; justify-content:space-between; align-items:center;
    border-bottom:1px solid #F0F0F0;
}

.panel-head h2{
    font-family:'Poppins',sans-serif;
    font-size:15px; font-weight:700;
}

.view-all{
    font-size:12px; font-weight:600;
    color:var(--pg); text-decoration:none;
    padding:4px 10px;
    border-radius:6px;
    transition:background .15s;
}
.view-all:hover{ background:#EEF5F2; }

/* ─── TABLE ──────────────────────────────────────────────── */
table{
    width:100%; border-collapse:collapse;
    font-size:13px;
}

thead th{
    padding:10px 22px;
    text-align:left;
    font-size:11px; font-weight:600;
    text-transform:uppercase; letter-spacing:.5px;
    color:var(--muted);
    background:#FAFAFA;
    border-bottom:1px solid #F0F0F0;
}

tbody td{
    padding:13px 22px;
    border-bottom:1px solid #F8F8F8;
    color:var(--dark);
}

tbody tr:last-child td{ border-bottom:none; }
tbody tr:hover td{ background:#FAFCFB; }

.td-id{ color:var(--muted); font-size:12px; }

/* status badges */
.badge{
    display:inline-block;
    padding:3px 9px;
    border-radius:20px;
    font-size:11px; font-weight:700;
    letter-spacing:.3px;
}
.badge-pending  { background:#FEF6E6; color:var(--c-pending); }
.badge-approved { background:#E8F5EA; color:var(--c-approved); }
.badge-rejected { background:#FEE8E8; color:var(--c-rejected); }
.badge-resolved { background:#E6FAF7; color:var(--c-resolved); }
.badge-new      { background:#FEE8E8; color:var(--c-rejected); }
.badge-review   { background:#FEF6E6; color:var(--c-pending); }
.badge-closed   { background:#F0F0F0; color:#888; }

/* ─── LOGOUT MODAL ───────────────────────────────────────── */
.lo-overlay{
    position:fixed; inset:0;
    background:rgba(0,0,0,.4);
    backdrop-filter:blur(6px);
    display:none; justify-content:center; align-items:center;
    z-index:9999;
}

.lo-modal{
    background:#fff;
    padding:32px; border-radius:20px;
    text-align:center; width:300px;
    border:1px solid var(--border);
    box-shadow:0 20px 50px rgba(0,0,0,.15);
    animation:popIn .25s forwards;
    transform:scale(.85); opacity:0;
}

@keyframes popIn{ to{ transform:scale(1); opacity:1; } }

.lo-modal h2{
    font-family:'Poppins',sans-serif;
    font-size:20px; margin-bottom:8px;
    color:var(--pg);
}
.lo-modal p{ font-size:14px; color:var(--muted); margin-bottom:22px; }

.lo-btns{ display:flex; gap:12px; }
.lo-cancel{
    flex:1; padding:11px;
    border:1px solid #E0E0E0; border-radius:10px;
    background:#F4F4F4; font-family:'Inter',sans-serif;
    font-size:14px; font-weight:500; cursor:pointer;
    transition:.2s;
}
.lo-cancel:hover{ background:#E8E8E8; }
.lo-confirm{
    flex:1; padding:11px; border:none; border-radius:10px;
    background:var(--pg); color:#fff;
    font-family:'Inter',sans-serif; font-size:14px;
    font-weight:600; cursor:pointer; transition:.2s;
}
.lo-confirm:hover{ background:var(--pgd); }

/* ─── RESPONSIVE ─────────────────────────────────────────── */
@media(max-width:1200px){
    .stats-grid{ grid-template-columns:repeat(2,1fr); }
}
@media(max-width:900px){
    .bottom-grid{ grid-template-columns:1fr; }
}
@media(max-width:768px){
    :root{ --sw:0px; }
    .sidebar{ display:none; }
    .main{ margin-left:0; }
    .content{ padding:20px; }
    .stats-grid{ grid-template-columns:1fr 1fr; }
}
</style>
</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar">
    <div class="logo-wrap">
        <div class="logo-box">🔍</div>
        <div class="logo-txt">
            E-LOST <span>MOH</span><br>
            E-FOUND <span>KOH</span>
        </div>
    </div>

    <ul class="nav">
        <li class="active">
            <a href="admin-dashboard.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                Dashboard
            </a>
        </li>
        <li>
            <a href="admin-items.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>
                Manage Items
            </a>
        </li>
        <li>
            <a href="admin-claims.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                Claim Requests
            </a>
        </li>
        <li>
            <a href="admin-users.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Users
            </a>
        </li>
        <li>
            <a href="admin-reports.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Reports
            </a>
        </li>
        <li>
            <a href="admin-announcements.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
                Announcements
            </a>
        </li>
        <li>
            <a href="admin-settings.php">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                Settings
            </a>
        </li>
        <li class="mt">
            <a href="#" onclick="document.getElementById('loOverlay').style.display='flex'">
                <svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
        </li>
    </ul>
</aside>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

    <!-- TOP BAR -->
    <div class="topbar">
        <div class="search-wrap">
            <svg class="search-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Search items, users, claims…">
        </div>

        <div class="topbar-right">
            <a href="admin-notif.php" class="bell-btn">
                <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </a>

            <div class="admin-pill" tabindex="0">
                <div class="admin-av"><?php echo strtoupper(substr($admin_name,0,1)); ?></div>
                <?php echo htmlspecialchars($admin_name); ?>
                <svg class="chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                <div class="pill-dropdown">
                    <a href="admin-profile.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Profile
                    </a>
                    <a href="#" onclick="document.getElementById('loOverlay').style.display='flex'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- CONTENT -->
    <div class="content">
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Overview of system activities</p>

        <!-- ── STAT CARDS ── -->
        <div class="stats-grid">

            <!-- Total Lost -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-lost">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                    </div>
                    <span class="stat-label">Total Lost Items</span>
                </div>
                <div class="stat-number cl-lost"><?php echo $stats['total_lost']; ?></div>
                <a href="admin-items.php?type=lost" class="stat-link lk-lost">View All →</a>
            </div>

            <!-- Total Found -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-found">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/><path d="M12 12v4"/><path d="M10 14h4"/></svg>
                    </div>
                    <span class="stat-label">Total Found Items</span>
                </div>
                <div class="stat-number cl-found"><?php echo $stats['total_found']; ?></div>
                <a href="admin-items.php?type=found" class="stat-link lk-found">View All →</a>
            </div>

            <!-- Pending Claims -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-pending">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <span class="stat-label">Pending Claims</span>
                </div>
                <div class="stat-number cl-pending"><?php echo $stats['pending_claims']; ?></div>
                <a href="admin-claims.php?status=pending" class="stat-link lk-pending">View All →</a>
            </div>

            <!-- Approved Claims -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-approved">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <span class="stat-label">Approved Claims</span>
                </div>
                <div class="stat-number cl-approved"><?php echo $stats['approved_claims']; ?></div>
                <a href="admin-claims.php?status=approved" class="stat-link lk-approved">View All →</a>
            </div>

            <!-- Rejected Claims -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-rejected">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <span class="stat-label">Rejected Claims</span>
                </div>
                <div class="stat-number cl-rejected"><?php echo $stats['rejected_claims']; ?></div>
                <a href="admin-claims.php?status=rejected" class="stat-link lk-rejected">View All →</a>
            </div>

            <!-- Total Users -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-users">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <span class="stat-label">Total Users</span>
                </div>
                <div class="stat-number cl-users"><?php echo $stats['total_users']; ?></div>
                <a href="admin-users.php" class="stat-link lk-users">View All →</a>
            </div>

            <!-- Reports -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-reports">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </div>
                    <span class="stat-label">Reports</span>
                </div>
                <div class="stat-number cl-reports"><?php echo $stats['total_reports']; ?></div>
                <a href="admin-reports.php" class="stat-link lk-reports">View All →</a>
            </div>

            <!-- Resolved Items -->
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon ic-resolved">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <span class="stat-label">Resolved Items</span>
                </div>
                <div class="stat-number cl-resolved"><?php echo $stats['resolved_items']; ?></div>
                <a href="admin-claims.php?status=resolved" class="stat-link lk-resolved">View All →</a>
            </div>

        </div><!-- /stats-grid -->

        <!-- ── BOTTOM TABLES ── -->
        <div class="bottom-grid">

            <!-- Recent Claims -->
            <div class="panel">
                <div class="panel-head">
                    <h2>Recent Claims Requests</h2>
                    <a href="admin-claims.php" class="view-all">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Item Name</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($recent_claims && $recent_claims->num_rows > 0):
                        while($row = $recent_claims->fetch_assoc()):
                            $sc = strtolower($row['claim_status']);
                    ?>
                        <tr>
                            <td class="td-id">CC-<?php echo str_pad($row['claim_id'],4,'0',STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['claim_date'])); ?></td>
                            <td><span class="badge badge-<?php echo $sc; ?>"><?php echo $row['claim_status']; ?></span></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center;color:#AAA;padding:22px;">No claims yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Reports -->
            <div class="panel">
                <div class="panel-head">
                    <h2>Recent Reports</h2>
                    <a href="admin-reports.php" class="view-all">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Report</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if($recent_reports && $recent_reports->num_rows > 0):
                        while($row = $recent_reports->fetch_assoc()):
                            $rs = strtolower($row['report_status']);
                    ?>
                        <tr>
                            <td class="td-id">RR-<?php echo str_pad($row['report_id'],4,'0',STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($row['report_type']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['report_date'])); ?></td>
                            <td><span class="badge badge-<?php echo $rs; ?>"><?php echo $row['report_status']; ?></span></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" style="text-align:center;color:#AAA;padding:22px;">No reports yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div><!-- /bottom-grid -->
    </div><!-- /content -->
</div><!-- /main -->

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
// Close logout modal on overlay click
document.getElementById('loOverlay').addEventListener('click', function(e){
    if(e.target === this) this.style.display = 'none';
});
document.addEventListener('keydown', e => {
    if(e.key === 'Escape') document.getElementById('loOverlay').style.display = 'none';
});
</script>

</body>
</html>
<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
include 'config/db.php';

$admin_name = "Admin"; // dummy admin name for preview

$claim_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// ── Handle Approve / Reject POST ────────────────────────────────
$action_done = null;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $new_status = $_POST['action'] === 'approve' ? 'Approved' : 'Rejected';

    // only update if real claim id exists
    if($claim_id > 0){
        $upd = $conn->prepare("UPDATE claims SET claim_status=? WHERE claim_id=?");
        $upd->bind_param("si", $new_status, $claim_id);
        $upd->execute();
    }

    $action_done = $new_status;
}

// ── DEFAULT DUMMY DATA ──────────────────────────────────────────
$claim = [
    'claim_id'         => 1,
    'claim_date'       => date('Y-m-d H:i:s'),
    'claim_status'     => 'Pending',
    'claim_message'    => 'Hi admin, this item belongs to me. I can provide additional proof if needed.',

    'proof_image'      => 'uploads/sample-proof.jpg',

    'item_id'          => 101,
    'item_name'        => 'Black Wallet',
    'category'         => 'Personal Belongings',
    'location_lost'    => 'School Canteen',
    'date_lost'        => date('Y-m-d H:i:s', strtotime('-2 days')),
    'description'      => 'A black leather wallet containing school ID and some cash.',
    'item_image'       => 'uploads/sample-item.jpg',
    'posted_by'        => 'Juan Dela Cruz',

    'claimant_id'      => 2,
    'claimant_name'    => 'Maria Santos',
    'claimant_email'   => 'maria@example.com',
    'claimant_avatar'  => 'images/default-avatar.png'
];

// ── FETCH REAL DATA IF EXISTS ───────────────────────────────────
if($claim_id > 0){

    $sql = "
        SELECT
            c.claim_id, c.claim_date, c.claim_status, c.claim_message,
            c.proof_image,
            li.item_id, li.item_name, li.category, li.location_lost,
            li.date_lost, li.description, li.item_image, li.posted_by,
            u.user_id AS claimant_id, u.username AS claimant_name,
            u.email AS claimant_email, u.profile_picture AS claimant_avatar
        FROM claims c
        JOIN lost_items li ON c.item_id = li.item_id
        JOIN users u ON c.claimant_user_id = u.user_id
        WHERE c.claim_id = ?
    ";

    $stmt = $conn->prepare($sql);

    if($stmt){
        $stmt->bind_param("i", $claim_id);
        $stmt->execute();

        $real_claim = $stmt->get_result()->fetch_assoc();

        // overwrite dummy data only if real record exists
        if($real_claim){
            $claim = $real_claim;
        }
    }
}

$item_img  = !empty($claim['item_image'])
    ? $claim['item_image']
    : 'uploads/default.png';

$proof_img = !empty($claim['proof_image'])
    ? $claim['proof_image']
    : null;

$avatar = !empty($claim['claimant_avatar'])
    ? $claim['claimant_avatar']
    : 'images/default-avatar.png';

$status = $action_done ?? $claim['claim_status'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Claim Detail - Admin</title>
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

/* SIDEBAR — same as claims list */
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
.search-wrap input{width:100%;padding:9px 14px 9px 38px;border:1px solid #E0E0E0;border-radius:9px;font-family:'Inter',sans-serif;font-size:13.5px;background:#FAFAFA;outline:none;}
.search-ico{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#AAA;pointer-events:none;}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:18px;}
.bell-btn{background:none;border:none;cursor:pointer;color:#555;display:flex;align-items:center;text-decoration:none;}
.admin-pill{display:flex;align-items:center;gap:10px;padding:6px 14px 6px 6px;background:#F4F4F4;border:1px solid var(--border);border-radius:999px;font-size:13.5px;font-weight:500;}
.admin-av{width:30px;height:30px;border-radius:50%;background:var(--pg);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;}

/* CONTENT */
.content{padding:32px;}

/* BACK BUTTON */
.back-btn{display:inline-flex;align-items:center;gap:7px;font-size:13px;font-weight:600;color:var(--pg);text-decoration:none;margin-bottom:22px;padding:8px 16px;border-radius:8px;border:1px solid var(--border);background:var(--white);transition:.2s;}
.back-btn:hover{background:#F0F0F0;}

/* TWO-COLUMN GRID */
.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;}

/* PANELS */
.panel{background:var(--white);border-radius:14px;border:1px solid var(--border);box-shadow:0 3px 12px rgba(0,0,0,.04);overflow:hidden;}
.panel-head{padding:16px 22px;border-bottom:1px solid #F0F0F0;}
.panel-head h2{font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;}
.panel-body{padding:20px 22px;display:flex;flex-direction:column;gap:14px;}

/* ITEM PREVIEW */
.item-preview{display:flex;gap:16px;align-items:flex-start;}
.item-thumb{width:90px;height:90px;border-radius:10px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;background:#EEE;}
.item-meta{display:flex;flex-direction:column;gap:7px;flex:1;}

/* META ROWS */
.meta-row{display:flex;align-items:flex-start;gap:8px;font-size:13px;}
.meta-row svg{flex-shrink:0;color:var(--muted);margin-top:1px;}
.meta-label{color:var(--muted);min-width:76px;font-size:12px;font-weight:500;}
.meta-val{color:var(--dark);font-weight:500;}

/* CLAIMANT */
.claimant-row{display:flex;align-items:center;gap:14px;padding-bottom:14px;border-bottom:1px solid #F0F0F0;margin-bottom:4px;}
.claimant-av{width:52px;height:52px;border-radius:50%;object-fit:cover;border:2px solid var(--border);}
.claimant-name{font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;}
.claimant-email{font-size:12px;color:var(--muted);}

/* PROOF IMAGES */
.proof-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
.proof-img{width:100%;aspect-ratio:1;border-radius:8px;object-fit:cover;border:1px solid var(--border);cursor:pointer;transition:opacity .2s;}
.proof-img:hover{opacity:.85;}
.proof-placeholder{width:100%;aspect-ratio:1;border-radius:8px;background:#F0F0F0;display:flex;align-items:center;justify-content:center;color:#CCC;}

/* MESSAGE BOX */
.message-box{background:#F8F8F8;border-radius:9px;padding:14px;font-size:13px;color:#555;line-height:1.6;border:1px solid #EFEFEF;}

/* STATUS BADGE */
.badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;}
.badge-Pending{background:#FEF6E6;color:var(--pending);}
.badge-Approved{background:#E8F5EA;color:var(--approved);}
.badge-Rejected{background:#FEE8E8;color:var(--rejected);}

/* ACTION BAR */
.action-bar{display:flex;gap:12px;align-items:center;flex-wrap:wrap;}
.btn-approve{padding:11px 24px;border:none;border-radius:9px;background:var(--pg);color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:7px;}
.btn-approve:hover{background:var(--pgd);}
.btn-reject{padding:11px 24px;border:none;border-radius:9px;background:var(--rejected);color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;display:inline-flex;align-items:center;gap:7px;}
.btn-reject:hover{background:#C0392B;}
.btn-message{padding:11px 24px;border:1px solid var(--border);border-radius:9px;background:var(--white);color:var(--dark);font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:7px;}
.btn-message:hover{background:#F4F4F4;}
.btn-disabled{opacity:.5;pointer-events:none;}

/* ══ RESULT MODAL ══ */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(8px);display:flex;justify-content:center;align-items:center;z-index:9999;}
.modal-overlay.hidden{display:none;}
.modal-card{background:#fff;border-radius:22px;padding:42px 36px;text-align:center;width:340px;box-shadow:0 24px 60px rgba(0,0,0,.18);border:1px solid var(--border);animation:popIn .3s cubic-bezier(.34,1.56,.64,1) forwards;transform:scale(.8);opacity:0;}
@keyframes popIn{to{transform:scale(1);opacity:1;}}
.result-icon{width:72px;height:72px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;}
.result-icon.approved{background:#E8F5EA;}
.result-icon.rejected{background:#FEE8E8;}
.result-icon svg{width:36px;height:36px;}
.modal-card h2{font-family:'Poppins',sans-serif;font-size:22px;font-weight:700;margin-bottom:8px;}
.modal-card p{font-size:14px;color:var(--muted);margin-bottom:24px;}
.btn-back-claims{padding:12px 28px;border:none;border-radius:10px;background:var(--pg);color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;cursor:pointer;transition:.2s;}
.btn-back-claims:hover{background:var(--pgd);}

/* CONFIRM MODAL */
.confirm-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(6px);display:flex;justify-content:center;align-items:center;z-index:8000;}
.confirm-overlay.hidden{display:none;}
.confirm-card{background:#fff;border-radius:18px;padding:32px;text-align:center;width:300px;box-shadow:0 20px 50px rgba(0,0,0,.15);animation:popIn .25s forwards;transform:scale(.85);opacity:0;}
.confirm-card h3{font-family:'Poppins',sans-serif;font-size:18px;margin-bottom:8px;}
.confirm-card p{font-size:13px;color:var(--muted);margin-bottom:22px;}
.confirm-btns{display:flex;gap:10px;}
.confirm-cancel{flex:1;padding:10px;border:1px solid #E0E0E0;border-radius:9px;background:#F4F4F4;font-size:13px;font-weight:500;cursor:pointer;}
.confirm-ok{flex:1;padding:10px;border:none;border-radius:9px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;}
.confirm-ok.green{background:var(--pg);}
.confirm-ok.red{background:var(--rejected);}

/* LOGOUT MODAL */
.lo-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(6px);display:none;justify-content:center;align-items:center;z-index:9999;}
.lo-modal{background:#fff;padding:32px;border-radius:20px;text-align:center;width:300px;border:1px solid var(--border);box-shadow:0 20px 50px rgba(0,0,0,.15);animation:popIn .25s forwards;transform:scale(.85);opacity:0;}
.lo-modal h2{font-family:'Poppins',sans-serif;font-size:20px;margin-bottom:8px;color:var(--pg);}
.lo-modal p{font-size:14px;color:var(--muted);margin-bottom:22px;}
.lo-btns{display:flex;gap:12px;}
.lo-cancel{flex:1;padding:11px;border:1px solid #E0E0E0;border-radius:10px;background:#F4F4F4;font-size:14px;font-weight:500;cursor:pointer;}
.lo-confirm{flex:1;padding:11px;border:none;border-radius:10px;background:var(--pg);color:#fff;font-size:14px;font-weight:600;cursor:pointer;}

@media(max-width:900px){.detail-grid{grid-template-columns:1fr;}}
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
        <li><a href="admin-dashb.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard</a></li>
        <li><a href="admin-items.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>Manage Items</a></li>
        <li class="active"><a href="admin-claims.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Claim Requests</a></li>
        <li><a href="admin-users.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users</a></li>
        <li><a href="admin-reports.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Reports</a></li>
        <li><a href="admin-announcements.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>Announcements</a></li>
        <li><a href="admin-settings.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>Settings</a></li>
        <li class="mt"><a href="admin-login.php" onclick="document.getElementById('loOverlay').style.display='flex'"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>Logout</a></li>
    </ul>
</aside>

<!-- MAIN -->
<div class="main">
    <div class="topbar">
        <div class="search-wrap">
            <svg class="search-ico" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" placeholder="Search items, users, claims…">
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

        <a href="admin-claims.php" class="back-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Claims
        </a>

        <!-- TWO-COLUMN DETAIL -->
        <div class="detail-grid">

            <!-- LEFT: Item Information -->
            <div class="panel">
                <div class="panel-head">
                    <h2>Item Information</h2>
                </div>
                <div class="panel-body">
                    <div class="item-preview">
                        <img class="item-thumb" src="<?php echo htmlspecialchars($item_img); ?>" alt="Item">
                        <div class="item-meta">
                            <div style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;margin-bottom:4px;">
                                <?php echo htmlspecialchars($claim['item_name']); ?>
                            </div>
                            <span class="badge badge-<?php echo $status; ?>"><?php echo $status; ?></span>
                        </div>
                    </div>

                    <div class="meta-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/></svg>
                        <span class="meta-label">Category</span>
                        <span class="meta-val"><?php echo htmlspecialchars($claim['category'] ?? '—'); ?></span>
                    </div>
                    <div class="meta-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span class="meta-label">Location</span>
                        <span class="meta-val"><?php echo htmlspecialchars($claim['location_lost'] ?? '—'); ?></span>
                    </div>
                    <div class="meta-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="meta-label">Date Lost</span>
                        <span class="meta-val"><?php echo $claim['date_lost'] ? date('M j, Y · g:i A', strtotime($claim['date_lost'])) : '—'; ?></span>
                    </div>
                    <div class="meta-row" style="align-items:flex-start;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-top:2px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span class="meta-label">Description</span>
                        <span class="meta-val" style="line-height:1.5;"><?php echo htmlspecialchars($claim['description'] ?? '—'); ?></span>
                    </div>
                    <div class="meta-row">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span class="meta-label">Posted By</span>
                        <span class="meta-val"><?php echo htmlspecialchars($claim['posted_by'] ?? '—'); ?></span>
                    </div>

                    <!-- Message from claimant -->
                    <div>
                        <div style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Message</div>
                        <div class="message-box">
                            <?php echo !empty($claim['claim_message']) ? htmlspecialchars($claim['claim_message']) : '<em style="color:#BCBCBC;">No message provided.</em>'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Claimant Info + Proof -->
            <div style="display:flex;flex-direction:column;gap:20px;">

                <!-- Claimant -->
                <div class="panel">
                    <div class="panel-head"><h2>Claimant Information</h2></div>
                    <div class="panel-body">
                        <div class="claimant-row">
                            <img class="claimant-av" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar">
                            <div>
                                <div class="claimant-name"><?php echo htmlspecialchars($claim['claimant_name']); ?></div>
                                <div class="claimant-email"><?php echo htmlspecialchars($claim['claimant_email']); ?></div>
                            </div>
                        </div>
                        <div class="meta-row">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span class="meta-label">Claim Date</span>
                            <span class="meta-val"><?php echo date('M j, Y', strtotime($claim['claim_date'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Proof Submitted -->
                <div class="panel">
                    <div class="panel-head"><h2>Proof Submitted</h2></div>
                    <div class="panel-body">
                        <?php if($proof_img): ?>
                        <div class="proof-grid">
                            <img class="proof-img" src="<?php echo htmlspecialchars($proof_img); ?>" alt="Proof" onclick="window.open(this.src,'_blank')">
                            <div class="proof-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                            <div class="proof-placeholder"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
                        </div>
                        <?php else: ?>
                        <p style="font-size:13px;color:var(--muted);text-align:center;padding:16px 0;">No proof image submitted.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

        <!-- ACTION BAR -->
        <div class="action-bar">
            <?php if($status === 'Pending'): ?>
            <button class="btn-approve" onclick="openConfirm('approve')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Approve Claim
            </button>
            <button class="btn-reject" onclick="openConfirm('reject')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject Claim
            </button>
            <?php else: ?>
            <button class="btn-approve btn-disabled">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Approve Claim
            </button>
            <button class="btn-reject btn-disabled">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject Claim
            </button>
            <?php endif; ?>
            <a href="messages.php?to=<?php echo $claim['claimant_id']; ?>" class="btn-message">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                Message User
            </a>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

<!-- ══ CONFIRM MODAL ══ -->
<div class="confirm-overlay hidden" id="confirmOverlay">
    <div class="confirm-card">
        <h3 id="confirmTitle">Approve this claim?</h3>
        <p id="confirmText">The user will be authorized to claim the item.</p>
        <div class="confirm-btns">
            <button class="confirm-cancel" onclick="closeConfirm()">Cancel</button>
            <form method="POST" style="flex:1;">
                <input type="hidden" name="action" id="confirmAction" value="approve">
                <button type="submit" class="confirm-ok green" id="confirmOkBtn" style="width:100%;">Confirm</button>
            </form>
        </div>
    </div>
</div>

<!-- ══ RESULT MODAL (shown after action) ══ -->
<?php if($action_done): ?>
<div class="modal-overlay" id="resultModal">
    <div class="modal-card">
        <?php if($action_done === 'Approved'): ?>
        <div class="result-icon approved">
            <svg viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <h2 style="color:var(--approved);">Claim Approved!</h2>
        <p>The user was authorized to claim the item.</p>
        <?php else: ?>
        <div class="result-icon rejected">
            <svg viewBox="0 0 24 24" fill="none" stroke="#E74C3C" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <h2 style="color:var(--rejected);">Claim Rejected!</h2>
        <p>The user was not authorized to claim the item.</p>
        <?php endif; ?>
        <button class="btn-back-claims" onclick="window.location.href='admin-claims.php'">Back to Claims</button>
    </div>
</div>
<?php endif; ?>

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
function openConfirm(type){
    const overlay = document.getElementById('confirmOverlay');
    const title   = document.getElementById('confirmTitle');
    const text    = document.getElementById('confirmText');
    const okBtn   = document.getElementById('confirmOkBtn');
    const action  = document.getElementById('confirmAction');

    if(type === 'approve'){
        title.textContent = 'Approve this claim?';
        text.textContent  = 'The user will be authorized to claim the item.';
        okBtn.className   = 'confirm-ok green';
        okBtn.textContent = 'Approve';
        action.value      = 'approve';
    } else {
        title.textContent = 'Reject this claim?';
        text.textContent  = 'The user will not be authorized to claim the item.';
        okBtn.className   = 'confirm-ok red';
        okBtn.textContent = 'Reject';
        action.value      = 'reject';
    }
    overlay.classList.remove('hidden');
}

function closeConfirm(){
    document.getElementById('confirmOverlay').classList.add('hidden');
}

document.getElementById('confirmOverlay').addEventListener('click', function(e){
    if(e.target === this) closeConfirm();
});

document.getElementById('loOverlay').addEventListener('click', function(e){
    if(e.target === this) this.style.display = 'none';
});

document.addEventListener('keydown', e => {
    if(e.key === 'Escape'){
        closeConfirm();
        document.getElementById('loOverlay').style.display = 'none';
    }
});
</script>
</body>
</html>
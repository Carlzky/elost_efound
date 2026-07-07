<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include '../config/db.php';

// ── AUTH GUARD ───────────────────────────────────────────────────


// ── HANDLE CREATE / DELETE ───────────────────────────────────────
$feedback = null;

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    if(isset($_POST['form_action']) && $_POST['form_action'] === 'create'){
        $title    = trim($_POST['title'] ?? '');
        $message  = trim($_POST['message'] ?? '');
        $audience = $_POST['audience'] ?? 'All Users';

        if($title !== '' && $message !== ''){
            $stmt = $conn->prepare("INSERT INTO announcements (title, message, audience, posted_by, date_posted) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("sssi", $title, $message, $audience, $admin_id);
            $stmt->execute();
            $feedback = "Announcement posted.";
        } else {
            $feedback = "Title and message are required.";
        }
    }

    if(isset($_POST['form_action']) && $_POST['form_action'] === 'delete' && isset($_POST['announcement_id'])){
        $aid = intval($_POST['announcement_id']);
        $stmt = $conn->prepare("DELETE FROM announcements WHERE announcement_id=?");
        $stmt->bind_param("i", $aid);
        $stmt->execute();
        $feedback = "Announcement deleted.";
    }

    header("Location: admin-announcements.php?msg=" . urlencode($feedback));
    exit();
}

$feedback = $_GET['msg'] ?? null;

// ── FETCH ANNOUNCEMENTS ──────────────────────────────────────────
$sql = "
    SELECT a.announcement_id, a.title, a.message, a.audience, a.date_posted, ad.admin_name AS posted_by_name
    FROM announcements a
    LEFT JOIN admins ad ON a.posted_by = ad.admin_id
    ORDER BY a.date_posted DESC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Announcements - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<style>
:root{
    --pg:#1F5D4A; --pgd:#143F32; --gold:#F1B846; --lg:#BBC34A;
    --bg:#F4F4F4; --white:#FFFFFF; --dark:#1A1A1A; --muted:#7A7A7A;
    --border:#E5E5E5; --sw:220px; --rejected:#E74C3C;
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
.page-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:24px;}
.page-title{font-family:'Poppins',sans-serif;font-size:26px;font-weight:700;margin-bottom:4px;}
.page-sub{color:var(--muted);font-size:13px;}

.btn-new{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border:none;border-radius:9px;background:var(--pg);color:#fff;font-family:'Poppins',sans-serif;font-size:13.5px;font-weight:700;cursor:pointer;transition:.2s;white-space:nowrap;}
.btn-new:hover{background:var(--pgd);}

.feedback{background:#E8F5EA;color:#1E7E34;border:1px solid #c3e6cb;padding:11px 16px;border-radius:9px;font-size:13.5px;font-weight:500;margin-bottom:18px;}

/* ANNOUNCEMENT CARDS */
.ann-list{display:flex;flex-direction:column;gap:14px;}
.ann-card{background:var(--white);border-radius:14px;border:1px solid var(--border);box-shadow:0 3px 12px rgba(0,0,0,.04);padding:20px 22px;}
.ann-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px;}
.ann-title{font-family:'Poppins',sans-serif;font-size:16px;font-weight:700;}
.ann-meta{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:4px;}
.ann-audience{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;background:#EAF0FE;color:#5B8DEF;letter-spacing:.3px;}
.ann-date{font-size:12px;color:var(--muted);}
.ann-author{font-size:12px;color:var(--muted);}
.ann-message{font-size:13.5px;color:#444;line-height:1.6;margin-top:8px;white-space:pre-wrap;}
.ann-actions{display:flex;gap:6px;flex-shrink:0;}
.btn-sm{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;border:1px solid var(--border);background:var(--white);color:var(--dark);cursor:pointer;transition:.2s;}
.btn-sm:hover{background:#F4F4F4;}
.btn-sm.danger{color:var(--rejected);border-color:#F5C6C6;}
.btn-sm.danger:hover{background:#FEE8E8;}

.empty{text-align:center;padding:60px 20px;color:var(--muted);font-size:14px;background:var(--white);border-radius:14px;border:1px solid var(--border);}

/* NEW ANNOUNCEMENT MODAL */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(8px);display:none;justify-content:center;align-items:center;z-index:9000;}
.modal-overlay.show{display:flex;}
.modal-card{background:#fff;border-radius:18px;padding:30px 32px;width:460px;max-width:92vw;box-shadow:0 24px 60px rgba(0,0,0,.18);animation:popIn .3s cubic-bezier(.34,1.56,.64,1) forwards;transform:scale(.9);opacity:0;}
@keyframes popIn{to{transform:scale(1);opacity:1;}}
.modal-card h2{font-family:'Poppins',sans-serif;font-size:19px;font-weight:700;margin-bottom:18px;color:var(--pg);}
.field{margin-bottom:16px;}
.field label{display:block;font-size:12.5px;font-weight:600;color:var(--dark);margin-bottom:6px;}
.field input, .field select, .field textarea{
    width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;
    font-family:'Inter',sans-serif;font-size:13.5px;background:#FAFAFA;outline:none;transition:border .2s,background .2s;
}
.field input:focus, .field select:focus, .field textarea:focus{border-color:var(--pg);background:#fff;}
.field textarea{resize:vertical;min-height:100px;}
.modal-btns{display:flex;gap:10px;margin-top:22px;}
.btn-cancel{flex:1;padding:11px;border:1px solid #E0E0E0;border-radius:9px;background:#F4F4F4;font-family:'Inter',sans-serif;font-size:14px;font-weight:500;cursor:pointer;}
.btn-cancel:hover{background:#E8E8E8;}
.btn-submit{flex:1;padding:11px;border:none;border-radius:9px;background:var(--pg);color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:700;cursor:pointer;}
.btn-submit:hover{background:var(--pgd);}

/* LOGOUT MODAL */
.lo-overlay{position:fixed;inset:0;background:rgba(0,0,0,.4);backdrop-filter:blur(6px);display:none;justify-content:center;align-items:center;z-index:9999;}
.lo-modal{background:#fff;padding:32px;border-radius:20px;text-align:center;width:300px;border:1px solid var(--border);box-shadow:0 20px 50px rgba(0,0,0,.15);animation:popIn .25s forwards;transform:scale(.85);opacity:0;}
.lo-modal h2{font-family:'Poppins',sans-serif;font-size:20px;margin-bottom:8px;color:var(--pg);}
.lo-modal p{font-size:14px;color:var(--muted);margin-bottom:22px;}
.lo-btns{display:flex;gap:12px;}
.lo-cancel{flex:1;padding:11px;border:1px solid #E0E0E0;border-radius:10px;background:#F4F4F4;font-size:14px;font-weight:500;cursor:pointer;}
.lo-cancel:hover{background:#E8E8E8;}
.lo-confirm{flex:1;padding:11px;border:none;border-radius:10px;background:var(--pg);color:#fff;font-size:14px;font-weight:600;cursor:pointer;}
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
        <li><a href="admin-dash.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>Dashboard</a></li>
        <li><a href="admin-items.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"/><path d="M16 3H8l-2 4h12l-2-4z"/></svg>Manage Items</a></li>
        <li><a href="admin-claims.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>Claim Requests</a></li>
        <li><a href="admin-users.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>Users</a></li>
        <li><a href="admin-reports.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>Reports</a></li>
        <li><a href="admin-analytics.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>Analytics</a></li>
        <li class="active"><a href="admin-announcements.php"><svg class="nav-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>Announcements</a></li>
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
        <div class="page-head">
            <div>
                <h1 class="page-title">Announcements</h1>
                <p class="page-sub">Post updates and notices for all users</p>
            </div>
            <button type="button" class="btn-new" onclick="openNewModal()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Announcement
            </button>
        </div>

        <?php if($feedback): ?>
        <div class="feedback"><?php echo htmlspecialchars($feedback); ?></div>
        <?php endif; ?>

        <!-- ANNOUNCEMENTS LIST -->
        <?php if($result && $result->num_rows > 0): ?>
        <div class="ann-list">
            <?php while($row = $result->fetch_assoc()): ?>
            <div class="ann-card">
                <div class="ann-top">
                    <div>
                        <div class="ann-title"><?php echo htmlspecialchars($row['title']); ?></div>
                        <div class="ann-meta">
                            <span class="ann-audience"><?php echo htmlspecialchars($row['audience']); ?></span>
                            <span class="ann-date"><?php echo date('M j, Y g:i A', strtotime($row['date_posted'])); ?></span>
                            <span class="ann-author">by <?php echo htmlspecialchars($row['posted_by_name'] ?? 'Admin'); ?></span>
                        </div>
                    </div>
                    <div class="ann-actions">
                        <form method="POST" onsubmit="return confirm('Delete this announcement?');">
                            <input type="hidden" name="form_action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?php echo $row['announcement_id']; ?>">
                            <button type="submit" class="btn-sm danger">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="ann-message"><?php echo htmlspecialchars($row['message']); ?></div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty">No announcements yet. Click "New Announcement" to post one.</div>
        <?php endif; ?>
    </div>
</div>

<!-- NEW ANNOUNCEMENT MODAL -->
<div class="modal-overlay" id="newModal">
    <div class="modal-card">
        <h2>Create New Announcement</h2>
        <form method="POST">
            <input type="hidden" name="form_action" value="create">

            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title" placeholder="e.g. System Maintenance Notice" required>
            </div>

            <div class="field">
                <label for="audience">Audience</label>
                <select id="audience" name="audience">
                    <option value="All Users">All Users</option>
                    <option value="Students">Students</option>
                    <option value="Staff">Staff</option>
                </select>
            </div>

            <div class="field">
                <label for="message">Message</label>
                <textarea id="message" name="message" placeholder="Write the announcement details…" required></textarea>
            </div>

            <div class="modal-btns">
                <button type="button" class="btn-cancel" onclick="closeNewModal()">Cancel</button>
                <button type="submit" class="btn-submit">Post Announcement</button>
            </div>
        </form>
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
function openNewModal(){ document.getElementById('newModal').classList.add('show'); }
function closeNewModal(){ document.getElementById('newModal').classList.remove('show'); }
document.getElementById('newModal').addEventListener('click', function(e){
    if(e.target === this) closeNewModal();
});

document.getElementById('loOverlay').addEventListener('click', function(e){
    if(e.target===this) this.style.display='none';
});
document.addEventListener('keydown', e => {
    if(e.key === 'Escape'){
        closeNewModal();
        document.getElementById('loOverlay').style.display = 'none';
    }
});
</script>
</body>
</html>
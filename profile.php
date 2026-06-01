<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include "config/db.php";

if(!isset($_SESSION['username'])){
    header("Location: registration.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $username_check = $_SESSION['username'];
    $stmt_id = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt_id->bind_param("s", $username_check);
    $stmt_id->execute();
    $result_id = $stmt_id->get_result();
    if ($row_id = $result_id->fetch_assoc()) {
        $_SESSION['user_id'] = $row_id['id'];
    } else {
        header("Location: logout.php");
        exit();
    }
}

$user_id = $_SESSION['user_id'];

// Fetch user profile
$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
if (!$stmt) { die("SQL Error: " . $conn->error); }
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile_result = $stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Fetch stats
$claimed_sql = "SELECT COUNT(*) AS total FROM claims WHERE claimant_user_id = ? AND claim_status = 'Approved'";
$stmt2 = $conn->prepare($claimed_sql);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$claimed = $stmt2->get_result()->fetch_assoc()['total'];

$returned_sql = "SELECT COUNT(*) AS total FROM found_items WHERE user_id = ?";
$stmt3 = $conn->prepare($returned_sql);
$stmt3->bind_param("i", $user_id);
$stmt3->execute();
$returned = $stmt3->get_result()->fetch_assoc()['total'];

$reports_sql = "SELECT COUNT(*) AS total FROM lost_items WHERE user_id = ?";
$stmt4 = $conn->prepare($reports_sql);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$reports = $stmt4->get_result()->fetch_assoc()['total'];

// Set variables with fallback values
$avatar = !empty($profile['profile_image']) ? $profile['profile_image'] : 'assets/img/defaultProfile.png';
$username = htmlspecialchars($profile['username'] ?? '');
$email = htmlspecialchars($profile['email'] ?? '');
$full_name = htmlspecialchars($profile['full_name'] ?? '');
$cvsu_email = htmlspecialchars($profile['cvsu_email'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>My Profile - E-LOST KOH, E-FOUND MOH</title>

<link rel="stylesheet" href="assets/css/profile_style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="toast-container" id="toastContainer"></div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="logo-section">
        <div class="logo-icon">🔍</div>
        <div class="logo-text">E-LOST MOH<br>E-FOUND KOH</div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </span>
                <span class="nav-text">Claims</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="notif.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="messages.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
       
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
</div>

<div class="top-bar">
    <button class="hamburger-btn" onclick="openSidebar()">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <div class="top-bar-center">
        <div class="top-logo-icon">🔍</div>
    </div>
    <div class="top-bar-right">
        <a href="notif.php" class="notif-bell-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </a>
        <img class="avatar-sm" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar">
    </div>
</div>

<div class="page-content">
    <div class="page-label">Account</div>
    <h1 class="page-title">My Profile</h1>

    <div class="profile-banner">
        <img class="profile-avatar" src="<?php echo htmlspecialchars($avatar); ?>" alt="avatar">
        <div class="profile-info">
            <h2><?php echo $username; ?></h2>
            <p><?php echo $email; ?></p>
        </div>
        <button class="edit-profile-btn" onclick="openEditModal()">Edit profile</button>
    </div>

    <div class="stats-row">
        <div class="stat-cell">
            <div class="num"><?php echo $claimed; ?></div>
            <div class="label">Items Claimed</div>
        </div>
        <div class="stat-cell">
            <div class="num"><?php echo $returned; ?></div>
            <div class="label">Items Returned</div>
        </div>
        <div class="stat-cell">
            <div class="num"><?php echo $reports; ?></div>
            <div class="label">Reports Made</div>
        </div>
    </div>

    <div class="info-card">
        <div class="info-row">
            <div class="info-label">Full Name</div>
            <div class="info-value"><?php echo $full_name ?: '—'; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">Username</div>
            <div class="info-value"><?php echo $username ?: '—'; ?></div>
        </div>
        <div class="info-row">
            <div class="info-label">CvSU Email</div>
            <div class="info-value"><?php echo $cvsu_email ?: $email; ?></div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="editModal">
    <div class="modal-card">
        <button class="modal-back" onclick="closeEditModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
            Back to Profile
        </button>

        <h2 class="modal-title">Edit Profile</h2>

        <form method="POST" action="actions/update_profile.php" enctype="multipart/form-data">
            <div class="photo-section">
                <h3>Profile Photo</h3>
                <div class="photo-row">
                    <img class="photo-preview" id="photoPreview" src="<?php echo htmlspecialchars($avatar); ?>" alt="preview">
                    <div class="photo-actions">
                        <div class="photo-hint">JPG, PNG, GIF. Max size 2MB.</div>
                        <div class="photo-btns">
                            <button type="button" class="btn-change-photo" onclick="document.getElementById('photoInput').click()">Change Photo</button>
                            <button type="button" class="btn-remove-photo" onclick="removePhoto()">Remove</button>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" id="removeImageFlag" name="remove_image_flag" value="0">
                
                <input type="file" id="photoInput" name="profile_image" accept="image/*" style="position: absolute; left: -9999px; width: 1px; height: 1px; opacity: 0;" onchange="previewPhoto(event)">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>">
                </div>
                <div class="form-group">
                    <label>CvSU Email Address</label>
                        <input type="email" name="cvsu_email" placeholder="Username@cvsu.gmail.com" 
                            value="<?php echo $cvsu_email ?: $email; ?>"
                        <?php echo !empty($profile['cvsu_email']) ? 'readonly style="background-color: #E0E0E0; cursor: not-allowed; color: #7A7A7A;" title="Your CvSU Email is permanent and cannot be changed."' : ''; ?>>
                </div>
                <div class="form-group full">
                    <label>Full Name</label>
                    <input type="text" name="full_name" placeholder="Full Name" value="<?php echo $full_name; ?>">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="logout-confirm-btn" onclick="window.location.href='logout.php'">Confirm</button>
        </div>
    </div>
</div>

<script>
    /* ---- Toast Notification Engine ---- */
    function showToast(type, title, description) {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        const iconSymbol = type === 'success' ? '✓' : '✕';
        
        toast.innerHTML = `
            <div class="toast-icon" aria-hidden="true">${iconSymbol}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-desc">${description}</div>
            </div>
            <button class="toast-close" aria-label="Dismiss Notification" onclick="dismissToast(this.parentElement)">✕</button>
        `;
        
        container.appendChild(toast);
        setTimeout(() => dismissToast(toast), 5000);
    }

    function dismissToast(toastElement) {
        if (!toastElement.classList.contains('fade-out')) {
            toastElement.classList.add('fade-out');
            setTimeout(() => toastElement.remove(), 300);
        }
    }

    document.addEventListener("DOMContentLoaded", () => {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        
        if (status) {
            switch(status) {
                case 'success':
                    showToast('success', 'Changes Saved', 'Your profile records have been updated successfully.');
                    break;
                case 'error_size':
                    showToast('error', 'Upload Rejected', 'The profile photo exceeds the 2MB file size constraint.');
                    break;
                case 'error_type':
                    showToast('error', 'Invalid File Type', 'Only valid imagery metrics (JPG, PNG, GIF) are allowed.');
                    break;
                case 'error_upload':
                    showToast('error', 'System Failsafe', 'Could not save the image data to our local directory.');
                    break;
                case 'error':
                    showToast('error', 'Database Error', 'An unexpected transaction error occurred. Please try again.');
                    break;
            }
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });

    /* ---- Sidebar drawer ---- */
    function openSidebar() {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebarOverlay').classList.add('open');
    }

    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebarOverlay').classList.remove('open');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeSidebar(); closeEditModal(); closeLogoutModal(); }
    });

    /* ---- Edit profile modal ---- */
    function openEditModal() { document.getElementById('editModal').classList.add('open'); }
    function closeEditModal() { document.getElementById('editModal').classList.remove('open'); }
    document.getElementById('editModal').addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });

    /* ---- Photo preview & removal flags ---- */
    function previewPhoto(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
            reader.readAsDataURL(file);
            document.getElementById('removeImageFlag').value = '0'; // Reset remove flag if they add a new photo
        }
    }

    function removePhoto() {
        document.getElementById('photoPreview').src = 'assets/img/defaultProfile.png';
        document.getElementById('photoInput').value = '';
        document.getElementById('removeImageFlag').value = '1'; // Signal backend to delete current photo
    }

    /* ---- Logout modal ---- */
    function openLogoutModal() { closeSidebar(); document.getElementById('logoutOverlay').style.display = 'flex'; }
    function closeLogoutModal() { document.getElementById('logoutOverlay').style.display = 'none'; }
</script>

</body>
</html>
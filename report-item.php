<?php
session_start();
include "config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: registration.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ── EDIT MODE ──────────────────────────────────────────────
$edit_mode = false;
$item      = null;
$edit_id   = null;
$edit_type = 'lost';

if (isset($_GET['edit']) && $_GET['edit'] == '1' && isset($_GET['id']) && isset($_GET['type'])) {
    $edit_id   = intval($_GET['id']);
    $edit_type = strtolower($_GET['type']);

    if ($edit_type === 'lost') {
        $sql = "SELECT lost_id AS item_id, item_name, category, location_lost AS location,
                       date_lost AS item_date, description, item_image, user_id
                FROM lost_items WHERE lost_id = ? AND user_id = ?";
    } elseif ($edit_type === 'found') {
        $sql = "SELECT found_id AS item_id, item_name, category, location_found AS location,
                       date_found AS item_date, description, item_image, user_id
                FROM found_items WHERE found_id = ? AND user_id = ?";
    } else {
        header("Location: browse-items.php");
        exit();
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();

    // If item not found or doesn't belong to user, redirect
    if (!$item) {
        header("Location: browse-items.php");
        exit();
    }

    $edit_mode = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? 'Edit Post' : 'Report Item'; ?> - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/report-item_style.css?v=4">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
    
</head>
<body>

    <div class="sidebar" id="sidebar">

        <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <div class="logo-section" onclick="openSidebarIfCollapsed()">
            <div class="logo-icon">🔍</div>
            <div class="logo-text">
                E-LOST <span class="txt-highlight">MOH</span><br>
                E-FOUND <span class="txt-highlight">KOH</span>
            </div>
        </div>

        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" data-tooltip="Dashboard">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></span>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item active">
                <a href="report-item.php" data-tooltip="Report Item">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg></span>
                    <span class="nav-text">Report Item</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="browse-items.php" data-tooltip="Browse Items">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></span>
                    <span class="nav-text">Browse Items</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="claim.php" data-tooltip="My Claims">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg></span>
                    <span class="nav-text">Claims</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="notif.php" data-tooltip="Notifications">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span>
                    <span class="nav-text">Notifications</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="messages.php" data-tooltip="Messages">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg></span>
                    <span class="nav-text">Messages</span>
                </a>
            </li>
            <li class="nav-item" style="margin-top: auto;">
                <a href="#" onclick="openLogoutModal()" data-tooltip="Logout">
                    <span class="nav-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span>
                    <span class="nav-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>


    <div class="main-content">
        <div class="content-header">
            <h1><?php echo $edit_mode ? 'Edit Post' : 'Report Item'; ?></h1>
        </div>

        <div class="form-card">
            <form action="<?php echo $edit_mode ? 'actions/update_item.php' : 'actions/save_report.php'; ?>" method="POST" enctype="multipart/form-data">

                <!-- Hidden fields -->
                <input type="hidden" name="type" id="item-type" value="<?php echo $edit_mode ? $edit_type : 'lost'; ?>">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="item_id"   value="<?php echo $item['item_id']; ?>">
                    <input type="hidden" name="item_type" value="<?php echo $edit_type; ?>">
                <?php endif; ?>

                <!-- Toggle buttons — disabled in edit mode so type can't be changed -->
                <div class="toggle-container">
                    <button type="button"
                            class="toggle-btn <?php echo (!$edit_mode || $edit_type === 'lost') ? 'active' : ''; ?>"
                            id="btn-lost"
                            <?php echo $edit_mode ? 'disabled style="pointer-events:none;opacity:0.55;"' : ''; ?>>
                        Lost Item
                    </button>
                    <button type="button"
                            class="toggle-btn <?php echo ($edit_mode && $edit_type === 'found') ? 'active' : ''; ?>"
                            id="btn-found"
                            <?php echo $edit_mode ? 'disabled style="pointer-events:none;opacity:0.55;"' : ''; ?>>
                        Found Item
                    </button>
                </div>

                <div class="form-grid">
                    <div class="fields-left">

                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" name="item_name"
                                   value="<?php echo $edit_mode ? htmlspecialchars($item['item_name']) : ''; ?>"
                                   placeholder="e.g. Black Backpack" required>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location"
                                   value="<?php echo $edit_mode ? htmlspecialchars($item['location']) : ''; ?>"
                                   placeholder="e.g. Canteen" required>
                        </div>

                        <div class="form-group">
                            <label id="date-label">
                                <?php echo $edit_mode ? ($edit_type === 'lost' ? 'Date Lost' : 'Date Found') : 'Date Lost'; ?>
                            </label>
                            <input type="date" name="date"
                                   value="<?php echo $edit_mode ? $item['item_date'] : ''; ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Description</label>
                            <textarea name="description" placeholder="Provide more details..." required><?php echo $edit_mode ? htmlspecialchars($item['description']) : ''; ?></textarea>
                        </div>

                    </div>

                    <div class="fields-right">

                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="" disabled <?php echo $edit_mode ? '' : 'selected'; ?>>Select category</option>
                                <option value="bags"        <?php echo ($edit_mode && $item['category'] === 'bags')        ? 'selected' : ''; ?>>Bag / Backpack</option>
                                <option value="electronics" <?php echo ($edit_mode && $item['category'] === 'electronics') ? 'selected' : ''; ?>>Electronics / Gadgets</option>
                                <option value="documents"   <?php echo ($edit_mode && $item['category'] === 'documents')   ? 'selected' : ''; ?>>Documents / ID Cards</option>
                                <option value="wallets"     <?php echo ($edit_mode && $item['category'] === 'wallets')     ? 'selected' : ''; ?>>Wallets / Purses</option>
                            </select>
                        </div>

                        <div class="form-group upload-container">
                            <label>Upload Image</label>
                            <input type="file"
                                   id="file-input"
                                   name="item_image"
                                   accept=".jpg,.jpeg,.png,.gif,.webp"
                                   style="display: none;">
                            
                            <div class="upload-dropzone" id="dropzone">

                                <?php if ($edit_mode && !empty($item['item_image'])): ?>
                                    <img id="image-preview"
                                         src="<?php echo htmlspecialchars($item['item_image']); ?>"
                                         style="max-width:100%; max-height:160px; border-radius:6px; object-fit:cover; margin-bottom:10px;">
                                <?php else: ?>
                                    <div class="upload-icon" id="upload-icon">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                        </svg>
                                    </div>
                                <?php endif; ?>

                                <p class="upload-text" id="upload-status">
                                    <?php if ($edit_mode): ?>
                                        Click to replace image<br>
                                        <span>Leave empty to keep current</span>
                                    <?php else: ?>
                                        Click to upload<br>
                                        <span>JPG, PNG, GIF, WEBP (Max 5 MB)</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <?php echo $edit_mode ? 'Save Changes' : 'Submit Report'; ?>
                </button>

            </form>
        </div>
    </div>

    <script>
        const btnLost    = document.getElementById('btn-lost');
        const btnFound   = document.getElementById('btn-found');
        const dateLabel  = document.getElementById('date-label');
        const dropzone   = document.getElementById('dropzone');
        const fileInput  = document.getElementById('file-input');
        const uploadStatus = document.getElementById('upload-status');
        const editMode   = <?php echo $edit_mode ? 'true' : 'false'; ?>;

        // Only allow toggling in non-edit mode
        if (!editMode) {
            btnLost.addEventListener('click', () => {
                btnLost.classList.add('active');
                btnFound.classList.remove('active');
                dateLabel.textContent = 'Date Lost';
                document.getElementById('item-type').value = "lost";
            });

            btnFound.addEventListener('click', () => {
                btnFound.classList.add('active');
                btnLost.classList.remove('active');
                dateLabel.textContent = 'Date Found';
                document.getElementById('item-type').value = "found";
            });
        }

        dropzone.addEventListener('click', () => { fileInput.click(); });

        const allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ];

        const maxSize = 5 * 1024 * 1024; // 5 MB

        fileInput.addEventListener('change', validateFile);

        function validateFile() {
            const file = fileInput.files[0];
            if (!file) return;

            if (!allowedTypes.includes(file.type)) {
                alert("Only JPG, PNG, GIF, and WEBP files are allowed.");
                fileInput.value = "";
                uploadStatus.innerHTML = editMode
                    ? "Click to replace image<br><span>Leave empty to keep current</span>"
                    : "Click to upload<br><span>JPG, PNG, GIF, WEBP (Max 5 MB)</span>";
                return;
            }

            if (file.size > maxSize) {
                alert("File size must not exceed 5 MB.");
                fileInput.value = "";
                uploadStatus.innerHTML = editMode
                    ? "Click to replace image<br><span>Leave empty to keep current</span>"
                    : "Click to upload<br><span>JPG, PNG, GIF, WEBP (Max 5 MB)</span>";
                return;
            }

            // Show preview
            const reader = new FileReader();
            reader.onload = function(e) {
                let preview = document.getElementById('image-preview');
                if (!preview) {
                    preview = document.createElement('img');
                    preview.id = 'image-preview';
                    preview.style.cssText = 'max-width:100%; max-height:160px; border-radius:6px; object-fit:cover; margin-bottom:10px;';
                    dropzone.insertBefore(preview, uploadStatus);

                    // Hide the upload icon once an image is chosen
                    const icon = document.getElementById('upload-icon');
                    if (icon) icon.style.display = 'none';
                }
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);

            uploadStatus.innerHTML = `<strong>Selected:</strong><br>
                <span style="font-size:12px;color:var(--primary-green);">${file.name}</span>`;
        }

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = "var(--primary-green)";
            dropzone.style.backgroundColor = "#F9FAF9";
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.style.borderColor = "#CCCCCC";
            dropzone.style.backgroundColor = "#FAFAFA";
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.style.borderColor = "#CCCCCC";
            dropzone.style.backgroundColor = "#FAFAFA";
            if (e.dataTransfer.files.length > 0) {
                const file = e.dataTransfer.files[0];

                if (!allowedTypes.includes(file.type)) {
                    alert("Invalid file type.");
                    return;
                }
                if (file.size > maxSize) {
                    alert("File size must not exceed 5 MB.");
                    return;
                }

                fileInput.files = e.dataTransfer.files;
                validateFile();
            }
        });

        document.querySelector('.logo-section').addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }

        function openSidebarIfCollapsed() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
            }
        }

        function openLogoutModal()  { document.getElementById("logoutOverlay").style.display = "flex"; }
        function closeLogoutModal() { document.getElementById("logoutOverlay").style.display = "none"; }
        function confirmLogout()    { window.location.href = "actions/logout.php"; }
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
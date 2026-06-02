<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: registration.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item - E-LOST KOH, E-FOUND MOH</title>

    <link rel="stylesheet" href="assets/css/report-item_style.css?v=2">
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
            <h1>Report Item</h1>
        </div>

        <div class="form-card">
            <form action="actions/save_report.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="type" id="item-type" value="lost">
            
                <div class="toggle-container">
                    <button type="button" class="toggle-btn active" id="btn-lost">Lost Item</button>
                    <button type="button" class="toggle-btn" id="btn-found">Found Item</button>
                </div>

                <div class="form-grid">
                    <div class="fields-left">
                        <div class="form-group">
                            <label>Item Name</label>
                            <input type="text" name="item_name" placeholder="e.g. Black Backpack" required>
                        </div>

                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" name="location" placeholder="e.g. Canteen" required>
                        </div>

                        <div class="form-group">
                            <label id="date-label">Date Lost</label>
                            <input type="date" name="date" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Description</label>
                            <textarea name="description" placeholder="Provide more details..." required></textarea>
                        </div>
                    </div>

                    <div class="fields-right">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" required>
                                <option value="" disabled selected>Select category</option>
                                <option value="bags">Bag / Backpack</option>
                                <option value="electronics">Electronics / Gadgets</option>
                                <option value="documents">Documents / ID Cards</option>
                                <option value="wallets">Wallets / Purses</option>
                            </select>
                        </div>

                        <div class="form-group upload-container">
                            <label>
                                Upload Image
                            </label>
                            <input type="file"
                            id="file-input"
                            name="item_image"
                            accept=".jpg,.jpeg,.png,.gif,.webp"
                            style="display: none;">
                            
                            <div class="upload-dropzone" id="dropzone">
                                <div class="upload-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                    </svg>
                                </div>
                                <p class="upload-text" id="upload-status">
                                    Click to upload<br>
                                    <span>JPG, PNG, GIF, WEBP (Max 5 MB)</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Submit Report</button>
            </form>
        </div>
    </div>

    <script>
        const btnLost = document.getElementById('btn-lost');
        const btnFound = document.getElementById('btn-found');
        const dateLabel = document.getElementById('date-label');
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const uploadStatus = document.getElementById('upload-status');

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

        dropzone.addEventListener('click', () => { fileInput.click(); });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadStatus.innerHTML = `<strong>Selected:</strong><br><span style="font-size:12px; color:var(--primary-green);">${fileInput.files[0].name}</span>`;
            }
        });

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
                alert("File size must not exceed 10 MB.");
                return;
            }

            fileInput.files = e.dataTransfer.files;
                uploadStatus.innerHTML = `<strong>Dropped:</strong><br><span style="font-size:12px; color:var(--primary-green);">${e.dataTransfer.files[0].name}</span>`;
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

        function openLogoutModal() { document.getElementById("logoutOverlay").style.display = "flex"; }
        function closeLogoutModal() { document.getElementById("logoutOverlay").style.display = "none"; }
        function confirmLogout() { window.location.href = "actions/logout.php"; }

        const allowedTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/quicktime',
    'video/x-msvideo'
];

const maxSize = 10 * 1024 * 1024; // 10 MB

fileInput.addEventListener('change', validateFile);

function validateFile() {
    const file = fileInput.files[0];

    if (!file) return;

    if (!allowedTypes.includes(file.type)) {
        alert("Only JPG, PNG, GIF, WEBP, MP4, MOV, and AVI files are allowed.");
        fileInput.value = "";
        uploadStatus.innerHTML = "Click to upload<br><span>or drag and drop</span>";
        return;
    }

    if (file.size > maxSize) {
        alert("File size must not exceed 10 MB.");
        fileInput.value = "";
        uploadStatus.innerHTML = "Click to upload<br><span>or drag and drop</span>";
        return;
    }

    uploadStatus.innerHTML =
        `<strong>Selected:</strong><br>
        <span style="font-size:12px;color:var(--primary-green);">
            ${file.name}
        </span>`;
}
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
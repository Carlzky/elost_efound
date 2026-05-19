<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Item - E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1F5D4A;
            --primary-dark: #143F32;
            --gold: #F1B846;
            --primary-green: #1F5D4A;
            --light-green: #BBC34A;
            --dark-gray: #68735C;
            --bg-gray: #F2F2F2;
            --pure-white: #FFFFFF;
            --text-dark: #1A1A1A;
            --border-light: #E0E0E0;
            --sidebar-width: 240px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-gray);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Navigation Component */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-green);
            color: var(--pure-white);
            padding: 24px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 10;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #1F5D4A, #143F32);
            border: 2px solid #F1B846;
            border-radius: 16px;

            display: flex;
            justify-content: center;
            align-items: center;

            font-size: 26px;

            box-shadow:
                0 12px 30px rgba(0, 0, 0, 0.35),
                inset 0 3px 6px rgba(255, 255, 255, 0.18);

            transition:
                transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1),
                box-shadow 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
        }

        .logo-icon:hover {
            transform: scale(1.08) translateY(-5px) rotate(4deg);

            box-shadow:
                0 18px 40px rgba(0, 0, 0, 0.45),
                inset 0 3px 6px rgba(255, 255, 255, 0.25);
        }
        

        .logo-text {
            font-family:'Poppins', sans-serif;
            font-size:15px;
            line-height:1.3;
            font-weight:600;
        }

        .nav-menu {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            height:100%;
        }

        .nav-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            padding: 12px 16px;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-item.active a, .nav-item a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--pure-white);
        }

        /* FIXED: Main Workspace Section styled to center content both ways */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center; /* Centers vertically */
            align-items: center;     /* Centers horizontally */
            min-height: 100vh;
        }

        /* Fixed heading wrapper to stay top-left aligned relative to the centered card */
        .content-header {
            width: 100%;
            max-width: 680px;
            margin-bottom: 24px;
            text-align: left;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 28px;
            color: var(--text-dark);
        }

        /* Report Form Card Layout Frame */
        .form-card {
            background-color: var(--pure-white);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
            width: 100%;
            max-width: 680px;
            border: 1px solid #EAEAEA;
        }

        /* Pill Mode Toggle Selector Layout Component */
        .toggle-container {
            display: flex;
            gap: 10px;
            background-color: #F2F2F2;
            padding: 6px;
            border-radius: 30px;
            width: max-content;
            margin-bottom: 28px;
        }

        .toggle-btn {
            border: none;
            padding: 10px 24px;
            border-radius: 20px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            background: transparent;
            color: var(--dark-gray);
            transition: all 0.2s ease;
        }

        .toggle-btn.active {
            background-color: var(--primary-green);
            color: var(--pure-white);
        }

        /* Two-Column Side-by-Side Matrix Grid */
        .form-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 24px;
            margin-bottom: 16px;
        }

        .fields-left, .fields-right {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

        label {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
        }

        input[type="text"], input[type="date"], select, textarea {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border-light);
            border-radius: 8px;
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            color: var(--text-dark);
            background-color: #FAFAFA;
            outline: none;
            transition: border-color 0.2s;
        }

        /* LOCKED SIZING: User cannot drag or stretch description box */
        textarea {
            resize: none; 
            height: 110px;
        }

        input::placeholder, textarea::placeholder {
            color: #B3B3B3;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--primary-green);
            background-color: var(--pure-white);
        }

        /* Safe drag & drop zone layout structure */
        .upload-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            margin-bottom: 16px;
        }

        .upload-dropzone {
            border: 2px dashed #CCCCCC;
            background-color: #FAFAFA;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-grow: 1;
            padding: 20px;
            text-align: center;
            color: var(--dark-gray);
            cursor: pointer;
            min-height: 230px; /* Synchronizes layout height with the left fields precisely */
            transition: all 0.2s ease;
        }

        .upload-dropzone:hover {
            border-color: var(--primary-green);
            background-color: #F9FAF9;
        }

        .upload-icon {
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .upload-icon svg {
            width: 40px;
            height: 40px;
            fill: #888888;
        }

        .upload-text {
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
        }

        .upload-text span {
            color: #888888;
            font-weight: 400;
            font-size: 12px;
        }

        /* Large Bottom Primary Action Button */
        .btn-submit {
            width: 100%;
            background-color: var(--primary-green);
            color: var(--pure-white);
            border: none;
            padding: 14px;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 8px;
            transition: background-color 0.2s;
        }

        .btn-submit:hover {
            background-color: #164335;
        }

        @media (max-width: 768px) {
            .sidebar { width: 70px; }
            .logo-text, .nav-text { display: none; }
            .main-content { margin-left: 70px; padding: 20px; justify-content: flex-start; }
            .form-grid { grid-template-columns: 1fr; gap: 0; }
            .upload-dropzone { min-height: 160px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="logo-section">
            <div class="logo-icon">🔍</div>
            <div class="logo-text">E-LOST MOH<br>E-FOUND KOH</div>
        </div>
        <ul class="nav-menu">
            <li class="nav-item"><a href="dashboard.php">🏠<span class="nav-text">Dashboard</span></a></li>
            <li class="nav-item active"><a href="report-item.php">📦<span class="nav-text">Report Item</span></a></li>
            <li class="nav-item"><a href="browse-items.php">🔎<span class="nav-text">Browse Items</span></a></li>
            <li class="nav-item"><a href="claim.php">📄<span class="nav-text">My Claims</span></a></li>
            <li class="nav-item"><a href="notif.php">🔔<span class="nav-text">Notifications</span></a></li>
            <li class="nav-item" style="margin-top: auto;"><a href="logout.php">🚪<span class="nav-text">Logout</span></a></li>
        </ul>
    </div>

    <div class="main-content">
        
        <div class="content-header">
            <h1>Report Item</h1>
        </div>

        <div class="form-card">
            <form action="save_report.php" method="POST" enctype="multipart/form-data">
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
                                <option value="" disabled open selected>Select category</option>
                                <option value="bags">Bag / Backpack</option>
                                <option value="electronics">Electronics / Gadgets</option>
                                <option value="documents">Documents / ID Cards</option>
                                <option value="wallets">Wallets / Purses</option>
                            </select>
                        </div>

                        <div class="form-group upload-container">
                            <label>Upload Image</label>
                            <input type="file" id="file-input" name="item_image" accept="image/*" style="display: none;">
                            
                            <div class="upload-dropzone" id="dropzone">
                                <div class="upload-icon">
                                    <svg viewBox="0 0 24 24">
                                        <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                    </svg>
                                </div>
                                <p class="upload-text" id="upload-status">Click to upload<br><span>or drag and drop</span></p>
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

        // Toggle Switch functionality
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

        // Click-to-upload hook handler
        dropzone.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                uploadStatus.innerHTML = `<strong>Selected:</strong><br><span style="font-size:12px; color:var(--primary-green);">${fileInput.files[0].name}</span>`;
            }
        });

        // Drag and drop event listeners
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
                fileInput.files = e.dataTransfer.files;
                uploadStatus.innerHTML = `<strong>Dropped:</strong><br><span style="font-size:12px; color:var(--primary-green);">${e.dataTransfer.files[0].name}</span>`;
            }
        });
    </script>
</body>
</html>
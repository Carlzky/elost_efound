<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details - E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #1F5D4A;
            --light-green: #BBC34A;
            --dark-gray: #68735C;
            --bg-gray: #F2F2F2;
            --pure-white: #FFFFFF;
            --text-dark: #1A1A1A;
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
            padding: 40px;
            display: flex;
            justify-content: center;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            background-color: var(--pure-white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            padding: 30px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 24px;
        }

        .back-link:hover {
            color: var(--primary-green);
        }

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

        /* Left Side: Image Container */
        .image-container {
            background-color: #EBEBEB;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            min-height: 400px;
            color: var(--dark-gray);
            border: 1px dashed #CCCCCC;
        }

        /* Right Side: Content Container */
        .details-container {
            display: flex;
            flex-direction: column;
        }

        h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 32px;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .status-badge {
            align-self: flex-start;
            background-color: var(--light-green);
            color: var(--primary-green);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-label {
            font-size: 14px;
            color: var(--dark-gray);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 16px;
            font-weight: 500;
        }

        .description-text {
            font-size: 15px;
            line-height: 1.6;
            color: #4A4A4A;
        }

        /* Action Buttons */
        .action-buttons {
            margin-top: auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            padding-top: 30px;
        }

        .btn {
            font-family: 'Inter', sans-serif;
            font-size: 16px;
            font-weight: 500;
            padding: 14px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-green);
            color: var(--pure-white);
        }

        .btn-primary:hover {
            background-color: #164335;
        }

        .btn-secondary {
            background-color: var(--pure-white);
            color: var(--text-dark);
            border: 1px solid #CCCCCC;
        }

        .btn-secondary:hover {
            background-color: #F9F9F9;
        }
    </style>
</head>
<body>

<div class="container">
    <a href="browse-items.php" class="back-link">&lt; Back</a>

    <div class="grid-layout">
        <div class="image-container">
            <svg width="48" height="48" fill="currentColor" viewBox="0 0 16 16" style="margin-bottom: 8px; opacity: 0.5;">
                <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z"/>
                <path d="M2.003 16a2 2 0 0 1-2-2V3.5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2V14a2 2 0 0 1-2 2h-12zm12-1a1 1 0 0 0 1-1V5.05l-3.477 3.476a.5.5 0 0 1-.706 0L9.27 6.474l-4.534 4.536A1 1 0 0 0 4.1 12h8a1 1 0 0 0 1-1v-1a1 1 0 0 0-1-1H9.83l-2.141 2.142a.5.5 0 0 1-.707 0L4.456 9.614 1.5 12.569V14a1 1 0 0 0 1 1h12z"/>
            </svg>
            <p>[ Image Preview Space ]</p>
        </div>

        <div class="details-container">
            <h1>Black Backpack</h1>
            <div class="status-badge">Lost</div>

            <div class="info-group">
                <div class="info-label">Category</div>
                <div class="info-value">Bag</div>
            </div>

            <div class="info-group">
                <div class="info-label">Location</div>
                <div class="info-value">Canteen</div>
            </div>

            <div class="info-group">
                <div class="info-label">Date Lost</div>
                <div class="info-value">May 20, 2026 • 10:30 AM</div>
            </div>

            <div class="info-group">
                <div class="info-label">Description</div>
                <div class="info-value description-text">
                    Black backpack with minimal design. Left near the counter lines.
                </div>
            </div>

            <div class="info-group">
                <div class="info-label">Posted by</div>
                <div class="info-value" style="color: var(--primary-green); font-weight: 600;">Yuunnaa</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary">Claim Item</button>
                <button class="btn btn-secondary">Message Owner</button>
            </div>
        </div>
    </div>
</div>

</body>
</html>
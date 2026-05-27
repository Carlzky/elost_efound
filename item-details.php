<?php
session_start();
include "config/db.php";

if(!isset($_GET['id']) || !isset($_GET['type'])){
    die("Invalid item.");
}

$id = intval($_GET['id']);
$type = $_GET['type'];

if($type == 'lost') {

    $sql = "
    SELECT 
        lost_id AS item_id,
        item_name,
        category,
        location_lost AS location,
        date_lost AS item_date,
        description,
        item_image,
        user_id,
        'Lost' AS item_type
    FROM lost_items
    WHERE lost_id = ?
    ";

} elseif($type == 'found') {

    $sql = "
    SELECT 
        found_id AS item_id,
        item_name,
        category,
        location_found AS location,
        date_found AS item_date,
        description,
        item_image,
        user_id,
        'Found' AS item_type
    FROM found_items
    WHERE found_id = ?
    ";

} else {

    die("Invalid item type.");
}

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows == 0){
    die("Item not found.");
}

$item = $result->fetch_assoc();



$user_sql = "SELECT username FROM users WHERE id = ?";

$user_stmt = $conn->prepare($user_sql);

$user_stmt->bind_param("i", $item['user_id']);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

$user_data = $user_result->fetch_assoc();

$posted_by = $user_data ? $user_data['username'] : 'Unknown User';

$image = !empty($item['item_image'])
    ? $item['item_image']
    : 'uploads/default.png';
?>

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

            --lost-bg: #FEE2E2;
            --lost-text: #B91C1C;

            --found-bg: #DCFCE7;
            --found-text: #166534;
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

        .grid-layout {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }

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
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .status-lost {
            background-color: var(--lost-bg);
            color: var(--lost-text);
        }

        .status-found {
            background-color: var(--found-bg);
            color: var(--found-text);
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

    <a href="browse-items.php?tab=<?php echo strtolower($item['item_type']); ?>" class="back-link">
    &lt; Back
    </a>

    <div class="grid-layout">

        <div class="image-container">
            <img 
                src="<?php echo htmlspecialchars($image); ?>"
                style="width:100%; height:100%; object-fit:cover;"
            >
        </div>

        <div class="details-container">

            <h1><?php echo htmlspecialchars($item['item_name']); ?></h1>

            <?php if($item['item_type'] == "Lost"): ?>
                <div class="status-badge status-lost">Lost</div>
            <?php else: ?>
                <div class="status-badge status-found">Found</div>
            <?php endif; ?>

            <div class="info-group">
                <div class="info-label">Category</div>
                <div class="info-value"><?php echo htmlspecialchars($item['category']); ?></div>
            </div>

            <div class="info-group">
                <div class="info-label">Location</div>
                <div class="info-value"><?php echo htmlspecialchars($item['location']); ?></div>
            </div>

            <div class="info-group">
                <div class="info-label">
                    <?php echo $item['item_type'] == "Lost" ? "Date Lost" : "Date Found"; ?>
                </div>
                <div class="info-value">
                    <?php echo date("F d, Y", strtotime($item['item_date'])); ?>
                </div>
            </div>

            <div class="info-group">
                <div class="info-label">Description</div>
                <div class="info-value description-text">
                    <?php echo htmlspecialchars($item['description']); ?>
                </div>
            </div>

            <div class="info-group">
                <div class="info-label">Posted by</div>
                <div class="info-value" style="color: var(--primary-green); font-weight: 600;">
                    <?php echo htmlspecialchars($posted_by); ?>
                </div>
            </div>

            <div class="action-buttons">

                <?php if($item['item_type'] == "Found"): ?>

                <a href="found_thisitem.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary">
                Claim This Item
                </a>

                <?php else: ?>

                <a href="found_lostitem.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary">
                Found This Item
                </a>

                <?php endif; ?>

                
                <a href="messages.php?user_id=<?php echo $item['user_id']; ?>" class="btn btn-secondary">
                Message Owner
                </a>

            </div>

        </div>

    </div>

</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

// 1. Secure the page
if(!isset($_SESSION['username'])){
    header("Location: registration.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// 2. Fetch Profile Image
$stmt_profile = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$profile_res = $stmt_profile->get_result();
$profile_data = $profile_res->fetch_assoc();
$avatar = !empty($profile_data['profile_image']) ? $profile_data['profile_image'] : 'assets/img/defaultProfile.png';

// 3. Original Item Fetching Logic
if(!isset($_GET['id']) || !isset($_GET['type'])){
    die("Invalid item.");
}
$id = intval($_GET['id']);
$type = strtolower($_GET['type']); // Convert to lowercase for consistent comparison

// ---- FIXED: Dynamically define the query string based on item type ----
if ($type === 'lost') {
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
} elseif ($type === 'found') {
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
    die("Invalid item type specified.");
}

// Prepare and execute the dynamic query string
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
    <title>Item Details</title>

    <link rel="stylesheet" href="assets/css/item_details_style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500&family=Poppins:wght@600&display=swap" rel="stylesheet">
    
</head>
<body>

<div class="header">
    <div class="header-left">
        <div class="logo-section">
            <div class="logo-icon">🔍</div>
            <div class="logo-text">
                E-LOST <span class="txt-highlight">MOH</span><br>
                E-FOUND <span class="txt-highlight">KOH</span>
            </div>
        </div>
    </div>

    <div class="header-right">
        <a href="notif.php" class="notif-bell-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </a>

        <a href="profile.php" class="avatar-link">
            <img 
                src="<?php echo htmlspecialchars($avatar); ?>"
                alt="Profile Picture"
                class="avatar"
            >
        </a>
    </div>
</div>

<div class="container">
    <a href="browse-items.php?tab=<?php echo strtolower($item['item_type']); ?>" class="back-link">
    &lt; Back
    </a>

    <div class="grid-layout">
        <div class="image-container">
            <img src="<?php echo htmlspecialchars($image); ?>"
                 style="width:100%; height:100%; object-fit:cover;">
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

    <a href="user-profile.php?id=<?php echo $item['user_id']; ?>"
       class="info-value"
       style="color: var(--primary-green); font-weight: 600; text-decoration: none;">
        <?php echo htmlspecialchars($posted_by); ?>
    </a>
</div>

            <div class="action-buttons">
                <?php if($user_id != $item['user_id']): ?>
                    
                    <?php if($item['item_type'] == "Found"): ?>
                    <a href="found_thisitem.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary">
                        Claim This Item
                    </a>
                    <?php else: ?>
                    <a href="found_lostitem.php?id=<?php echo $item['item_id']; ?>" class="btn btn-primary">
                        Found This Item
                    </a>
                    <?php endif; ?>

                    <a href="messages.php?user_id=<?php echo $item['user_id']; ?>&item_id=<?php echo $item['item_id']; ?>" class="btn btn-secondary">
                        Message Owner
                    </a>

                <?php else: ?>
                    
                    <div style="width: 100%; padding: 14px; background: #e8f5e9; color: #2E7D32; border: 1px solid #c8e6c9; border-radius: 8px; text-align: center; font-weight: 600; font-size: 15px;">
                        📌 This is your post
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<?php
session_start();
include "config/db.php";

if(!isset($_GET['id'])){
    die("Item ID missing.");
}

$id = intval($_GET['id']);

$sql = "
SELECT 
    found_id AS item_id,
    item_name,
    category,
    location_found AS location,
    date_found AS item_date,
    description,
    item_image,
    user_id
FROM found_items
WHERE found_id = ?
";

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

$image = !empty($item['item_image']) ? $item['item_image'] : 'uploads/default.png';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Item Details</title>

<link rel="stylesheet" href="assets/css/found_thisitem_style.css">
<link href="[fonts.googleapis.com](https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap)" rel="stylesheet">


</head>

<body>

<!-- NAVBAR (uniform with found_lostitem) -->
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
        <a href="notif.php" class="notif-bell-btn" aria-label="Notifications">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
        </a>
        <a href="profile.php" class="avatar-link">
            <img src="images/default-avatar.png" alt="Profile Picture" class="avatar">
        </a>
    </div>
</div>

<div class="wrapper">
    <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'browse-items.php'; ?>" class="back-link">
        &lt; Back to Previous Page
    </a>

    <div class="grid">
        <!-- LEFT: Item details -->
        <div class="card">
            <div class="img">
                <img src="<?php echo htmlspecialchars($image); ?>" alt="Item Image">
            </div>

            <h2 class="item-title"><?php echo htmlspecialchars($item['item_name']); ?></h2>

            <!-- Keep status specific to this page -->
            <div class="status-badge status-found">Found</div>

            <div class="label">Category</div>
            <div class="value"><?php echo htmlspecialchars($item['category']); ?></div>

            <div class="label">Location</div>
            <div class="value"><?php echo htmlspecialchars($item['location']); ?></div>

            <div class="label">Date Found</div>
            <div class="value"><?php echo date("F d, Y", strtotime($item['item_date'])); ?></div>

            <div class="label">Description</div>
            <div class="value"><?php echo htmlspecialchars($item['description']); ?></div>

            <div class="label">Posted by</div>
            <div class="value" style="color:var(--primary); font-weight:600;">
                <?php echo htmlspecialchars($posted_by); ?>
            </div>
        </div>

        <!-- RIGHT: Contact form -->
        <div class="card">
            <h3 class="form-title">Contact Owner</h3>

            <form method="POST" action="send_claim.php" enctype="multipart/form-data">
                <input type="hidden" name="receiver_id" value="<?php echo $item['user_id']; ?>">
                <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

                <label for="name">Your Name</label>
                <input id="name" type="text" name="name" required>

                <label for="contact">Your Contact</label>
                <input id="contact" type="text" name="contact" required>

                <label for="message">Message</label>
                <textarea id="message" name="message" rows="5" required></textarea>

                <label for="proof_image">Upload Proof</label>
                <input id="proof_image" type="file" name="proof_image" accept="image/*">

                <button type="submit" class="btn">Send Claim</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>

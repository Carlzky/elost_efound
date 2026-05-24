<?php
session_start();
include "config/db.php";

if(!isset($_GET['id'])){
    die("Item ID missing.");
}

$id = intval($_GET['id']);

$sql = "
SELECT 
    lost_id AS item_id,
    item_name,
    category,
    location_lost AS location,
    date_lost AS item_date,
    description,
    item_image,
    user_id
FROM lost_items
WHERE lost_id = ?
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
<title>Item Details</title>

<style>

:root {
    --lost-bg: #FEE2E2;
    --lost-text: #B91C1C;

    --found-bg: #DCFCE7;
    --found-text: #166534;
}

body{
    margin:0;
    font-family:Arial;
    background:#f4f6f5;
}

/* TOP BAR */
.header{
    background:#1F5D4A;
    color:white;
    padding:18px 30px;
    font-weight:600;
}

/* CENTER WRAPPER */
.wrapper{
    max-width:1100px;
    margin:30px auto;
}

/* BACK LINK FIXED */
.back-link{
    display: inline-flex;
    align-items: center;
    color: var(--text-dark);
    text-decoration: none;
    font-weight: 500;
    margin-bottom: 24px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns: 1.3fr 0.8fr;
    gap:20px;
}

/* CARD */
.card{
    background:white;
    border-radius:12px;
    padding:20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

/* IMAGE */
.img{
    width:100%;
    height:260px;
    border-radius:10px;
    overflow:hidden;
    background:#ddd;
}

.img img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* BADGE */
.badge{
    display:inline-block;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    margin:10px 0;
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

/* TEXT */
.label{font-size:13px;color:#777;}
.value{font-size:15px;margin-bottom:8px;}

/* INPUTS */
input, textarea{
    width:100%;
    padding:10px;
    margin-top:5px;
    margin-bottom:12px;
    border:1px solid #ddd;
    border-radius:6px;
}

/* UPLOAD */
.upload{
    border:2px dashed #ccc;
    padding:15px;
    text-align:center;
    border-radius:10px;
    font-size:13px;
    color:#777;
    margin-bottom:12px;
}

/* BUTTON */
.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:8px;
    background:#1F5D4A;
    color:white;
    font-weight:600;
    cursor:pointer;
}
</style>
</head>

<body>

<div class="header">
    E-LOST MOH / E-FOUND KOH
</div>

<div class="wrapper">

    <a href="browse-items.php" class="back-link">
        &lt; Back to Items
    </a>

    <div class="grid">

        <!-- LEFT -->
        <div class="card">

            <div class="img">
                <img src="<?php echo htmlspecialchars($image); ?>">
            </div>

            <h2><?php echo htmlspecialchars($item['item_name']); ?></h2>

            <div class="status-badge status-lost">Lost</div>

            <div class="label">Category</div>
            <div class="value"><?php echo htmlspecialchars($item['category']); ?></div>

            <div class="label">Location</div>
            <div class="value"><?php echo htmlspecialchars($item['location']); ?></div>

            <div class="label">
                Date Found
            </div>
            <div class="value">
                <?php echo date("F d, Y", strtotime($item['item_date'])); ?>
            </div>

            <div class="label">Description</div>
            <div class="value"><?php echo htmlspecialchars($item['description']); ?></div>

            <div class="label">Posted by</div>
            <div class="value" style="color:#1F5D4A;font-weight:600;">
                <?php echo htmlspecialchars($posted_by); ?>
            </div>

        </div>

        <!-- RIGHT -->
        <div class="card">

            <h3>I Found This Item</h3>

            <form method="POST" action="send_found_report.php" enctype="multipart/form-data">

            <input type="hidden" name="receiver_id" value="<?php echo $item['user_id']; ?>">
            <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">

            <label>Your Name</label>
            <input type="text" name="name" required>

            <label>Your Contact</label>
            <input type="text" name="contact" required>

            <label>Message</label>
            <textarea name="message" rows="5" required></textarea>

            <label>Upload Proof</label>
            <input type="file" name="proof_image" accept="image/*">

            <button class="btn">Notify Owner</button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
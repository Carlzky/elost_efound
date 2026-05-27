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

$image = !empty($item['item_image']) 
    ? $item['item_image'] 
    : 'uploads/default.png';

$back_url = isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])
    ? $_SERVER['HTTP_REFERER']
    : 'browse-items.php';
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Item Details</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">

<style>

:root{
    --primary: #1F5D4A;
    --primary-dark: #143F32;
    --gold: #F1B846;
    --light-green: #BBC34A;
    --bg: #F4F6F5;
    --white: #FFFFFF;
    --text: #1A1A1A;
    --border: #E5E5E5;

    --lost-bg: #FEE2E2;
    --lost-text: #B91C1C;

    --found-bg: #DCFCE7;
    --found-text: #166534;
}

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{
    font-family:'Inter', sans-serif;
    background:var(--bg);
    color:var(--text);
}

/* =========================
   HEADER
========================= */
.header{
    background:var(--primary);
    padding:16px 32px;

    display:flex;
    justify-content:space-between;
    align-items:center;

    position:sticky;
    top:0;
    z-index:100;
}

/* LEFT */
.header-left{
    display:flex;
    align-items:center;
}

/* LOGO */
.logo-section{
    display:flex;
    align-items:center;
    gap:14px;
}

.logo-icon{
    width:56px;
    height:56px;

    background:linear-gradient(
        135deg,
        var(--primary),
        var(--primary-dark)
    );

    border:2px solid var(--gold);
    border-radius:16px;

    display:flex;
    justify-content:center;
    align-items:center;

    font-size:24px;

    box-shadow:
        0 10px 25px rgba(0,0,0,0.25),
        inset 0 2px 4px rgba(255,255,255,0.15);

    transition:0.3s ease;
}

.logo-icon:hover{
    transform:scale(1.05) rotate(4deg);
}

.logo-text{
    font-family:'Poppins', sans-serif;
    font-size:15px;
    line-height:1.3;
    font-weight:700;
    color:white;
}

.txt-highlight{
    color:var(--light-green);
}

/* RIGHT */
.header-right{
    display:flex;
    align-items:center;
    gap:20px;
}

/* NOTIFICATION */
.notif-bell-btn{
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    transition:0.2s;
}

.notif-bell-btn:hover{
    transform:scale(1.1);
}

/* PROFILE */
.avatar-link{
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
}

.avatar{
    width:42px;
    height:42px;
    border-radius:50%;
    object-fit:cover;
    border:2px solid rgba(255,255,255,0.6);
    transition:0.2s ease;
    background:white;
}

.avatar:hover{
    transform:scale(1.06);
    border-color:white;
}

/* =========================
   WRAPPER
========================= */
.wrapper{
    max-width:1100px;
    margin:30px auto;
    padding:0 20px;
}

/* BACK BUTTON */
.back-link{
    display:inline-flex;
    align-items:center;
    gap:6px;

    color:var(--primary);
    text-decoration:none;

    font-weight:600;
    margin-bottom:24px;

    transition:0.2s;
}

.back-link:hover{
    opacity:0.8;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:1.3fr 0.8fr;
    gap:20px;
}

/* CARD */
.card{
    background:var(--white);
    border-radius:16px;
    padding:24px;

    border:1px solid #ECECEC;

    box-shadow:
        0 4px 14px rgba(0,0,0,0.05);
}

/* IMAGE */
.img{
    width:100%;
    height:280px;

    overflow:hidden;
    border-radius:14px;

    background:#DDD;

    margin-bottom:18px;
}

.img img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* TITLE */
.item-title{
    font-family:'Poppins', sans-serif;
    font-size:28px;
    margin-bottom:10px;
}

/* BADGE */
.status-badge{
    display:inline-block;

    padding:5px 14px;

    border-radius:20px;

    font-size:14px;
    font-weight:600;

    margin-bottom:24px;
}

.status-lost{
    background:var(--lost-bg);
    color:var(--lost-text);
}

.status-found{
    background:var(--found-bg);
    color:var(--found-text);
}

/* LABELS */
.label{
    font-size:13px;
    color:#777;
    margin-top:14px;
}

.value{
    margin-top:4px;
    font-size:15px;
    line-height:1.6;
}

/* FORM */
.form-title{
    font-family:'Poppins', sans-serif;
    font-size:20px;
    margin-bottom:20px;
}

label{
    display:block;
    font-size:14px;
    font-weight:600;
    margin-bottom:6px;
    color:#444;
}

input,
textarea{
    width:100%;
    padding:12px;

    border:1px solid #DDD;
    border-radius:8px;

    font-size:14px;
    font-family:'Inter', sans-serif;

    margin-bottom:16px;

    outline:none;
}

input:focus,
textarea:focus{
    border-color:var(--primary);
}

/* FILE */
input[type="file"]{
    background:#FAFAFA;
}

/* BUTTON */
.btn{
    width:100%;

    padding:13px;

    border:none;
    border-radius:10px;

    background:var(--primary);
    color:white;

    font-size:15px;
    font-weight:600;

    cursor:pointer;

    transition:0.2s;
}

.btn:hover{
    background:var(--primary-dark);
}

/* RESPONSIVE */
@media(max-width:900px){

    .grid{
        grid-template-columns:1fr;
    }

    .img{
        height:230px;
    }

    .header{
        padding:14px 20px;
    }

    .logo-text{
        font-size:13px;
    }

}

</style>
</head>

<body>

<!-- HEADER -->
<div class="header">

    <!-- LEFT -->
    <div class="header-left">

        <div class="logo-section">

            <div class="logo-icon">
                🔍
            </div>

            <div class="logo-text">
                E-LOST <span class="txt-highlight">MOH</span><br>
                E-FOUND <span class="txt-highlight">KOH</span>
            </div>

        </div>

    </div>

    <!-- RIGHT -->
    <div class="header-right">

        <!-- NOTIFICATION -->
        <a href="notif.php" class="notif-bell-btn">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">

                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>

                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>

            </svg>
        </a>

        <!-- PROFILE -->
        <a href="profile.php" class="avatar-link">

            <img 
                src="images/default-avatar.png"
                alt="Profile Picture"
                class="avatar"
            >

        </a>

    </div>

</div>

<div class="wrapper">

    <!-- BACK BUTTON -->
    <a href="<?php echo htmlspecialchars($back_url); ?>" class="back-link">
        &lt; Back to Previous Page
    </a>

    <div class="grid">

        <!-- LEFT CARD -->
        <div class="card">

            <div class="img">
                <img 
                    src="<?php echo htmlspecialchars($image); ?>" 
                    alt="Item Image"
                >
            </div>

            <h2 class="item-title">
                <?php echo htmlspecialchars($item['item_name']); ?>
            </h2>

            <div class="status-badge status-lost">
                Lost
            </div>

            <div class="label">Category</div>

            <div class="value">
                <?php echo htmlspecialchars($item['category']); ?>
            </div>

            <div class="label">Location</div>

            <div class="value">
                <?php echo htmlspecialchars($item['location']); ?>
            </div>

            <div class="label">Date Lost</div>

            <div class="value">
                <?php echo date("F d, Y", strtotime($item['item_date'])); ?>
            </div>

            <div class="label">Description</div>

            <div class="value">
                <?php echo htmlspecialchars($item['description']); ?>
            </div>

            <div class="label">Posted by</div>

            <div class="value" style="color:var(--primary); font-weight:600;">
                <?php echo htmlspecialchars($posted_by); ?>
            </div>

        </div>

        <!-- RIGHT CARD -->
        <div class="card">

            <h3 class="form-title">
                I Found This Item
            </h3>

            <form 
                method="POST"
                action="send_found_report.php"
                enctype="multipart/form-data"
            >

                <input 
                    type="hidden"
                    name="receiver_id"
                    value="<?php echo $item['user_id']; ?>"
                >

                <input 
                    type="hidden"
                    name="item_id"
                    value="<?php echo $item['item_id']; ?>"
                >

                <label>Your Name</label>

                <input 
                    type="text"
                    name="name"
                    required
                >

                <label>Your Contact</label>

                <input 
                    type="text"
                    name="contact"
                    required
                >

                <label>Message</label>

                <textarea 
                    name="message"
                    rows="5"
                    required
                ></textarea>

                <label>Upload Proof</label>

                <input 
                    type="file"
                    name="proof_image"
                    accept="image/*"
                >

                <button type="submit" class="btn">
                    Notify Owner
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>
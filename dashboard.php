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

$user = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>E-Lost Koh E-Found Moh Dashboard</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    background:#f5f5f5;
    display:flex;
}

/* SIDEBAR */

.sidebar{
    width:240px;
    height:100vh;
    background:#0d5c46;
    color:white;
    padding:20px;
    position:fixed;
    left:0;
    top:0;
}

.logo{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:40px;
}

.logo img{
    width:45px;
    height:45px;
    border-radius:10px;
}

.logo h2{
    font-size:16px;
    line-height:20px;
}

.menu{
    list-style:none;
}

.menu li{
    margin:18px 0;
}

.menu a{
    text-decoration:none;
    color:white;
    display:flex;
    align-items:center;
    gap:10px;
    font-size:15px;
    transition:0.3s;
}

.menu a:hover{
    padding-left:5px;
    color:#c6ffb3;
}

/* MAIN CONTENT */

.main{
    margin-left:240px;
    width:calc(100% - 240px);
    padding:25px;
}

/* TOPBAR */

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:25px;
}

.search-box{
    width:320px;
    background:white;
    padding:10px 15px;
    border-radius:10px;
    display:flex;
    align-items:center;
    box-shadow:0 2px 8px rgba(0,0,0,0.08);
}

.search-box input{
    border:none;
    outline:none;
    width:100%;
    margin-left:10px;
}

.profile{
    display:flex;
    align-items:center;
    gap:15px;
}

.profile img{
    width:40px;
    height:40px;
    border-radius:50%;
}

/* WELCOME */

.welcome{
    margin-bottom:20px;
}

.welcome h1{
    font-size:28px;
    color:#222;
}

.welcome p{
    color:gray;
}

/* CARDS */

.cards{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:white;
    border-radius:15px;
    padding:20px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.card h3{
    font-size:14px;
    color:gray;
    margin-bottom:10px;
}

.card .number{
    font-size:30px;
    font-weight:600;
    color:#0d5c46;
}

/* CONTENT SECTION */

.content{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
}

/* RECENT ACTIVITY */

.activity{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.activity h2{
    margin-bottom:20px;
}

.activity-item{
    display:flex;
    justify-content:space-between;
    padding:15px 0;
    border-bottom:1px solid #eee;
}

.activity-item:last-child{
    border-bottom:none;
}

.activity-item span{
    color:gray;
    font-size:14px;
}

/* RECENT ITEMS */

.recent-items{
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0 3px 10px rgba(0,0,0,0.08);
}

.recent-items h2{
    margin-bottom:20px;
}

.item{
    display:flex;
    align-items:center;
    gap:15px;
    margin-bottom:20px;
}

.item img{
    width:60px;
    height:60px;
    border-radius:10px;
    object-fit:cover;
}

.item-info h4{
    font-size:15px;
}

.item-info p{
    color:gray;
    font-size:13px;
}
/* BLUR BACKGROUND */
.logout-overlay{
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    backdrop-filter: blur(6px);
    display:none;
    justify-content:center;
    align-items:center;
    z-index:9999;
}

/* MODAL BOX */
.logout-modal{
    background:white;
    padding:30px;
    border-radius:15px;
    text-align:center;
    width:300px;
    transform:scale(0.8);
    opacity:0;
    animation: popIn 0.25s forwards;
}

/* ANIMATION */
@keyframes popIn{
    to{
        transform:scale(1);
        opacity:1;
    }
}

.logout-modal h2{
    margin-bottom:10px;
    color:#0d5c46;
}

.logout-modal p{
    margin-bottom:20px;
    color:gray;
}

/* BUTTONS */
.logout-buttons{
    display:flex;
    justify-content:space-between;
    gap:10px;
}

.cancel-btn{
    flex:1;
    padding:10px;
    border:none;
    border-radius:8px;
    background:#ddd;
    cursor:pointer;
}

.logout-btn{
    flex:1;
    padding:10px;
    border:none;
    border-radius:8px;
    background:#0d5c46;
    color:white;
    cursor:pointer;
}

.logout-btn:hover{
    background:#0a4636;
}

/* RESPONSIVE */

@media(max-width:900px){

    .content{
        grid-template-columns:1fr;
    }

    .sidebar{
        width:200px;
    }

    .main{
        margin-left:200px;
        width:calc(100% - 200px);
    }
}

@media(max-width:700px){

    .sidebar{
        display:none;
    }

    .main{
        margin-left:0;
        width:100%;
    }

    .topbar{
        flex-direction:column;
        gap:15px;
        align-items:flex-start;
    }

    .search-box{
        width:100%;
    }
}

</style>
</head>
<body>

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo">
        <img src="logo.png" alt="">
        <h2>E-LOST KOH<br>E-FOUND MOH</h2>
    </div>

    <ul class="menu">
        <li><a href="#">Dashboard</a></li>
        <li><a href="report-item.php">Report Lost Item</a></li>
        <li><a href="#">Report Found Item</a></li>
        <li><a href="browse-items.php">Browse Items</a></li>
        <li><a href="#">My Claims</a></li>
        <li><a href="#">Notifications</a></li>
        <li><a href="#" onclick="openLogoutModal()">Logout</a></li>
    </ul>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <div class="search-box">
            🔍
            <input type="text" placeholder="Search items...">
        </div>

        <div class="profile">
            <span>🔔</span>
            <img src="">
        </div>

    </div>

    <!-- WELCOME -->

    <div class="welcome">
    <h1>Dashboard</h1>
    <p>Welcome back, <?php echo $user; ?>!</p>
    </div>

    <!-- CARDS -->

    <div class="cards">

        <div class="card">
            <h3>Total Lost Items</h3>
            <div class="number">
            <?php
            $sql = "SELECT COUNT(*) AS total FROM lost_items";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            echo $row['total'];
            ?>
            </div>
        </div>

        <div class="card">
            <h3>Total Found Items</h3>
           <div class="number">
            <?php
            $sql = "SELECT COUNT(*) AS total FROM found_items";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            echo $row['total'];
            ?>
</div>
        </div>

        <div class="card">
            <h3>Claims Pending</h3>
            <div class="number">
            <?php
            $sql = "SELECT COUNT(*) AS total FROM claims WHERE claim_status='Pending'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            echo $row['total'];
            ?>
            </div>
        </div>

    </div>

    <!-- CONTENT -->

    <div class="content">

        <!-- RECENT ACTIVITY -->

        <div class="activity">

            <h2>Recent Activity</h2>

            <?php
            $sql = "SELECT * FROM report_history ORDER BY action_date DESC LIMIT 3";
            $result = $conn->query($sql);

            while($row = $result->fetch_assoc()){
            ?>

            <div class="activity-item">
            <div>
            <h4><?php echo $row['action_done']; ?></h4>
            <span><?php echo $row['action_date']; ?></span>
            </div>
            </div>

            <?php } ?>

            </div>

        <!-- RECENT ITEMS -->

        <div class="recent-items">

            <h2>Recently Posted Items</h2>

                <?php
                  $sql = "SELECT * FROM lost_items ORDER BY created_at DESC LIMIT 3";
                  $result = $conn->query($sql);

                  while($row = $result->fetch_assoc()){
                  ?>

                  <div class="item">
                  <img src="<?php echo $row['item_image']; ?>">
                    <div class="item-info">
                        <h4><?php echo $row['item_name']; ?></h4>
                        <p>Lost - <?php echo $row['location_lost']; ?></p>
                    </div>
             </div>

                 <?php } ?>

            </div>

        </div>

    </div>
<!-- LOGOUT MODAL -->
<div class="logout-overlay" id="logoutOverlay">

    <div class="logout-modal">

        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>

        <div class="logout-buttons">

            <button class="cancel-btn" onclick="closeLogoutModal()">
                Cancel
            </button>

            <button class="logout-btn" onclick="confirmLogout()">
                Confirm
            </button>

        </div>

    </div>

</div>
<script>
function openLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "none";
}

function confirmLogout(){
    window.location.href = "logout.php";
}
</script>
</body>
</html>
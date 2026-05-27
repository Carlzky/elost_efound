<?php
session_start();
// FIXED PATH: Go up one level to reach the config folder
include "../config/db.php";

if (!isset($_SESSION['user_id'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Required</title>
<style>
body{ margin:0; padding:0; font-family:Arial, sans-serif; background:#f5f5f5; overflow:hidden; }
.overlay{ position:fixed; top:0; left:0; width:100%; height:100%; backdrop-filter:blur(8px); background:rgba(0,0,0,0.35); display:flex; justify-content:center; align-items:center; animation:fadeIn 0.3s ease; }
.modal{ width:350px; background:white; border-radius:20px; padding:30px; text-align:center; box-shadow:0 10px 25px rgba(0,0,0,0.2); animation:popUp 0.3s ease; }
.modal h2{ margin:0 0 10px; color:#222; }
.modal p{ color:#666; font-size:15px; margin-bottom:25px; }
.login-btn{ display:inline-block; padding:12px 24px; background:#2E7D32; color:white; text-decoration:none; border-radius:10px; font-weight:600; transition:0.2s; }
.login-btn:hover{ background:#256428; transform:scale(1.03); }
@keyframes fadeIn{ from{ opacity:0; } to{ opacity:1; } }
@keyframes popUp{ from{ transform:scale(0.8); opacity:0; } to{ transform:scale(1); opacity:1; } }
</style>
</head>
<body>
<div class="overlay">
    <div class="modal">
        <h2>Login Required</h2>
        <p>You must be logged in first before submitting a report.</p>
        <a href="../registration.php" class="login-btn">Go to Login</a>
    </div>
</div>
</body>
</html>
<?php
exit();
}

$user_id = $_SESSION['user_id'];

$type = $_POST['type'] ?? '';
$item_name = $_POST['item_name'] ?? '';
$category = $_POST['category'] ?? '';
$location = $_POST['location'] ?? '';
$date = $_POST['date'] ?? '';
$description = $_POST['description'] ?? '';

$image_path = NULL;

if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == 0) {
    // FIXED PATH: Target directory from the perspective of the actions/ folder
    $upload_dir = "../uploads/";

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $image_name = time() . "_" . basename($_FILES["item_image"]["name"]);
    $target_file = $upload_dir . $image_name;

    if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)) {
        // Save path relative to root so files like browse-items.php can read it easily
        $image_path = "uploads/" . $image_name;
    }
}

if ($type == "lost") {
    $sql = "INSERT INTO lost_items (user_id, item_name, category, location_lost, date_lost, description, item_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $item_name, $category, $location, $date, $description, $image_path);
} else if ($type == "found") {
    $sql = "INSERT INTO found_items (user_id, item_name, category, location_found, date_found, description, item_image) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssss", $user_id, $item_name, $category, $location, $date, $description, $image_path);
} else {
    die("Invalid report type.");
}

if ($stmt->execute()) {
    $report_id = $conn->insert_id;

    if ($type == "lost") {
        $report_type = "Lost";
        $action_done = "Reported a lost item: " . $item_name;
    } else {
        $report_type = "Found";
        $action_done = "Reported a found item: " . $item_name;
    }

    $history_sql = "INSERT INTO report_history (user_id, report_type, report_id, action_done) VALUES (?, ?, ?, ?)";
    $history_stmt = $conn->prepare($history_sql);
    $history_stmt->bind_param("isis", $user_id, $report_type, $report_id, $action_done);
    $history_stmt->execute();
    $history_stmt->close();

    // FIXED PATH: Go up one level to redirect to browse-items.php at the root
    header("Location: ../browse-items.php?success=1");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
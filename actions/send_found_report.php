<?php
session_start();
date_default_timezone_set('Asia/Manila');

// FIXED PATH: Go up one level to reach the config folder safely
include "../config/db.php";

if(!isset($_SESSION['user_id'])){
    die("Login required.");
}

$finder_id = $_SESSION['user_id'];

$receiver_id = intval($_POST['receiver_id']);
$item_id = intval($_POST['item_id']);
$message = trim($_POST['message']);
$finder_name = trim($_POST['name']);
$finder_contact = trim($_POST['contact']);

if($message == ""){
    die("Message required.");
}

$proof_image = null;

if (!empty($_FILES['proof_image']['name'])) {
    $fileName = time() . "_" . basename($_FILES["proof_image"]["name"]);
    
    // FIXED PATH: Point outward from actions/ directory to perform stream writes
    $targetDir = "../uploads/";
    
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $targetPath = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["proof_image"]["tmp_name"], $targetPath)) {
        // Save without leading '../' so root interfaces (like load_messages.php) read the image correctly
        $proof_image = "uploads/" . $fileName;
    }
}

// 1. Log Report Entry to Database Table
$sql = "
INSERT INTO found_reports (lost_item_id, finder_user_id, finder_name, finder_contact, message, proof_image)
VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iissss", $item_id, $finder_id, $finder_name, $finder_contact, $message, $proof_image);
$stmt->execute();
$report_id = $conn->insert_id;
$stmt->close();

// 2. Insert Core Notification Component Message Frame
$message_text = "Found report submitted for item ID: $item_id";

$msg_sql = "
INSERT INTO messages (sender_id, receiver_id, item_id, report_id, message_text, message_type)
VALUES (?, ?, ?, ?, ?, 'found_report')
";

$msg_stmt = $conn->prepare($msg_sql);
$msg_stmt->bind_param("iiiis", $finder_id, $receiver_id, $item_id, $report_id, $message_text);
$msg_stmt->execute();
$msg_stmt->close();

// 3. Inject Structural Alert Target Entry
$notif_text = "Someone reported they found your lost item.";

$notif = $conn->prepare("
INSERT INTO notifications (user_id, notification_text, notification_type)
VALUES (?, ?, 'found_report')
");
$notif->bind_param("is", $receiver_id, $notif_text);
$notif->execute();
$notif->close();
$conn->close();

// FIXED PATH: Go up one level to return out of actions/ and load messages.php at root
header("Location: ../messages.php?receiver_id=" . $receiver_id . "&item_id=" . $item_id);
exit();
?>
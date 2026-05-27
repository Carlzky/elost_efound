<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "config/db.php";

if(!isset($_SESSION['user_id'])){
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_POST['receiver_id'];
$message = trim($_POST['message']);
$item_id = $_POST['item_id'] ?? null;
$item_name = $_POST['item_name'] ?? null;

if($message == ""){
    exit();
}

$sql = "
INSERT INTO messages 
(sender_id, receiver_id, message_text, item_id)
VALUES (?, ?, ?, ?)
";
$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iisi",
    $sender_id,
    $receiver_id,
    $message,
    $item_id,
);

$stmt->execute();

echo "success";
?>
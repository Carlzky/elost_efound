<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "config/db.php";

if(!isset($_SESSION['user_id'])){
    die("Login required.");
}

$claimant_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$item_id = intval($_POST['item_id']);
$message = trim($_POST['message']);

$claimant_name = $_POST['name'];
$claimant_contact = $_POST['contact'];

if($message == ""){
    die("Message required.");
}

/* =========================
   1. HANDLE IMAGE UPLOAD
========================= */
$proof_image = null;

if (!empty($_FILES['proof_image']['name'])) {

    $fileName = time() . "_" . basename($_FILES["proof_image"]["name"]);
    $targetPath = "uploads/" . $fileName;

    move_uploaded_file($_FILES["proof_image"]["tmp_name"], $targetPath);

    $proof_image = $targetPath;
}

/* =========================
   2. INSERT INTO CLAIMS
========================= */
$sql = "
INSERT INTO claims
(found_item_id, claimant_user_id, claimant_name, claimant_contact, message, proof_image)
VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iissss",
    $item_id,
    $claimant_id,
    $claimant_name,
    $claimant_contact,
    $message,
    $proof_image
);

$stmt->execute();

/* IMPORTANT: GET CLAIM ID */
$claim_id = $conn->insert_id;

/* =========================
   3. INSERT SYSTEM MESSAGE
   (AUTO CREATE CONVERSATION)
========================= */

$message_text = "📦 New Claim Request Submitted";

$msg_sql = "
INSERT INTO messages 
(sender_id, receiver_id, item_id, message_text, claim_id, message_type)
VALUES (?, ?, ?, ?, ?, 'claim')
";

$msg_stmt = $conn->prepare($msg_sql);

$msg_stmt->bind_param(
    "iiisi",
    $claimant_id,
    $receiver_id,
    $item_id,
    $message_text,
    $claim_id
);

$msg_stmt->execute();

/* =========================
   4. REDIRECT TO CHAT
========================= */
header("Location: messages.php?receiver_id=".$receiver_id."&item_id=".$item_id);
exit();
?>
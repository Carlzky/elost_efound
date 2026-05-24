<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    die("Login required.");
}

if (!isset($_POST['claim_id'])) {
    die("Invalid claim.");
}

$claim_id = intval($_POST['claim_id']);

/* Update claim */
$stmt = $conn->prepare("
    UPDATE claims
    SET claim_status = 'Approved'
    WHERE claim_id = ?
");
$stmt->bind_param("i", $claim_id);
$stmt->execute();

/* Get full claim info */
$claim_stmt = $conn->prepare("
    SELECT * FROM claims WHERE claim_id = ?
");
$claim_stmt->bind_param("i", $claim_id);
$claim_stmt->execute();
$claim = $claim_stmt->get_result()->fetch_assoc();

/* Get owner of found item */
$item_stmt = $conn->prepare("
    SELECT user_id
    FROM found_items
    WHERE found_id = ?
");
$item_stmt->bind_param("i", $claim['found_item_id']);
$item_stmt->execute();
$item = $item_stmt->get_result()->fetch_assoc();

$receiver_id = $item['user_id'];

if ($receiver_id != $_SESSION['user_id']) {
    die("Unauthorized action.");
}


/*CHECK IF MESSAGE ALREADY EXISTS (PREVENT DUPLICATES) */
$check = $conn->prepare("
    SELECT message_id 
    FROM messages 
    WHERE claim_id = ? 
    AND message_text LIKE '%APPROVED%'
");

$check->bind_param("i", $claim_id);
$check->execute();
$res = $check->get_result();

$msg = "Your claim was APPROVED.";

if ($res->num_rows == 0) {

    $msg_stmt = $conn->prepare("
        INSERT INTO messages 
        (sender_id, receiver_id, item_id, message_text, claim_id, message_type)
        VALUES (?, ?, ?, ?, ?, 'system')
    ");

$msg_stmt->bind_param(
    "iiisi",
    $claim['claimant_user_id'],
    $receiver_id,
    $claim['found_item_id'],
    $msg,
    $claim_id
);

$msg_stmt->execute();

}

/*Redirect */
header("Location: messages.php?receiver_id=".$receiver_id."&item_id=".$claim['found_item_id']);
exit();
?>
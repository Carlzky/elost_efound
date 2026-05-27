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

$stmt = $conn->prepare("
    UPDATE claims
    SET claim_status = 'Approved'
    WHERE claim_id = ?
");
$stmt->bind_param("i", $claim_id);
$stmt->execute();

$claim_stmt = $conn->prepare("
    SELECT * FROM claims WHERE claim_id = ?
");
$claim_stmt->bind_param("i", $claim_id);
$claim_stmt->execute();
$claim = $claim_stmt->get_result()->fetch_assoc();

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

$notif_text = "Your claim request was approved.";

$notif = $conn->prepare("
INSERT INTO notifications
(user_id, notification_text, notification_type)
VALUES (?, ?, 'claim')
");

$notif->bind_param(
    "is",
    $claim['claimant_user_id'],
    $notif_text
);

$notif->execute();

header("Location: messages.php?receiver_id=".$receiver_id."&item_id=".$claim['found_item_id']);
exit();
?>
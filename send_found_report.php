<?php
session_start();
date_default_timezone_set('Asia/Manila');

include "config/db.php";


if(!isset($_SESSION['user_id'])){
    die("Login required.");
}

$finder_id = $_SESSION['user_id'];

$receiver_id = intval($_POST['receiver_id']);
$item_id = intval($_POST['item_id']);

$message = trim($_POST['message']);

$finder_name = $_POST['name'];
$finder_contact = $_POST['contact'];

if($message == ""){
    die("Message required.");
}


$proof_image = null;

if (!empty($_FILES['proof_image']['name'])) {

    $fileName = time() . "_" . basename($_FILES["proof_image"]["name"]);

    $targetPath = "uploads/" . $fileName;

    move_uploaded_file(
        $_FILES["proof_image"]["tmp_name"],
        $targetPath
    );

    $proof_image = $targetPath;
}


$sql = "
INSERT INTO found_reports
(
    lost_item_id,
    finder_user_id,
    finder_name,
    finder_contact,
    message,
    proof_image
)
VALUES (?, ?, ?, ?, ?, ?)
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "iissss",
    $item_id,
    $finder_id,
    $finder_name,
    $finder_contact,
    $message,
    $proof_image
);

$stmt->execute();

$report_id = $conn->insert_id;


$message_type = 'found_report';
$claim_id = null;
$message_text = "Found report submitted for item ID: $item_id";

$msg_sql = "
INSERT INTO messages
(
    sender_id,
    receiver_id,
    item_id,
    report_id,
    message_text,
    message_type
)
VALUES (?, ?, ?, ?, ?, 'found_report')
";

$msg_stmt = $conn->prepare($msg_sql);

$msg_stmt->bind_param(
    "iiiis",
    $finder_id,
    $receiver_id,
    $item_id,
    $report_id,
    $message_text
);

$msg_stmt->execute();

$notif_text = "Someone reported they found your lost item.";

$notif = $conn->prepare("
INSERT INTO notifications
(user_id, notification_text, notification_type)
VALUES (?, ?, 'found_report')
");

$notif->bind_param(
    "is",
    $receiver_id,
    $notif_text
);

$notif->execute();

header(
    "Location: messages.php?receiver_id="
    .$receiver_id.
    "&item_id="
    .$item_id
);

exit();
?>
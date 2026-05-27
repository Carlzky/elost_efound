<?php
session_start();

date_default_timezone_set('Asia/Manila');

include "config/db.php";

if (!isset($_SESSION['user_id'])) {
    die("Login required.");
}

if (!isset($_POST['report_id'])) {
    die("Invalid report.");
}

$report_id = intval($_POST['report_id']);

$report_stmt = $conn->prepare("
    SELECT * FROM found_reports
    WHERE report_id = ?
");

$report_stmt->bind_param("i", $report_id);

$report_stmt->execute();

$report = $report_stmt->get_result()->fetch_assoc();

if (!$report) {
    die("Report not found.");
}

$item_stmt = $conn->prepare("
    SELECT user_id
    FROM lost_items
    WHERE lost_id = ?
");

$item_stmt->bind_param(
    "i",
    $report['lost_item_id']
);

$item_stmt->execute();

$item = $item_stmt->get_result()->fetch_assoc();

if (!$item) {
    die("Lost item not found.");
}

$receiver_id = $item['user_id'];

if ($receiver_id != $_SESSION['user_id']) {
    die("Unauthorized action.");
}

$update_stmt = $conn->prepare("
    UPDATE found_reports
    SET report_status = 'Rejected'
    WHERE report_id = ?
");

$update_stmt->bind_param("i", $report_id);

$update_stmt->execute();

$check = $conn->prepare("
    SELECT message_id
    FROM messages
    WHERE report_id = ?
    AND message_text LIKE '%REJECTED%'
");

$check->bind_param("i", $report_id);

$check->execute();

$res = $check->get_result();

$msg = "Your found item report was REJECTED.";

if ($res->num_rows == 0) {

    $msg_stmt = $conn->prepare("
        INSERT INTO messages
        (
            sender_id,
            receiver_id,
            item_id,
            report_id,
            message_text,
            message_type
        )
        VALUES (?, ?, ?, ?, ?, 'system')
    ");

    $msg_stmt->bind_param(
        "iiiis",
        $receiver_id,
        $report['finder_user_id'],
        $report['lost_item_id'],
        $report_id,
        $msg
    );

    $msg_stmt->execute();
}

header(
    "Location: messages.php?receiver_id="
    .$report['finder_user_id'].
    "&item_id="
    .$report['lost_item_id']
);

exit();
?>
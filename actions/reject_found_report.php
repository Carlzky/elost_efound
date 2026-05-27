<?php
session_start();

date_default_timezone_set('Asia/Manila');

include "../config/db.php";

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

$notif_text = "Your found item report was rejected.";

$notif = $conn->prepare("
INSERT INTO notifications
(user_id, notification_text, notification_type)
VALUES (?, ?, 'found_report')
");

$notif->bind_param(
    "is",
    $report['finder_user_id'],
    $notif_text
);

$notif->execute();

header(
    "Location: messages.php?receiver_id="
    .$report['finder_user_id'].
    "&item_id="
    .$report['lost_item_id']
);

exit();
?>
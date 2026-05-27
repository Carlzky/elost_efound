<?php
session_start();
date_default_timezone_set('Asia/Manila');

// FIXED PATH: Go up one level to reach the config folder safely
include "../config/db.php"; 

if (!isset($_SESSION['user_id'])) exit;

$current_user_id = $_SESSION['user_id'];
if (!isset($_GET['receiver_id'])) exit;

$receiver_id = intval($_GET['receiver_id']);
$item_id = isset($_GET['item_id']) ? intval($_GET['item_id']) : 0;

$sql = "
SELECT * FROM messages
WHERE 
(
    (sender_id = ? AND receiver_id = ?)
    OR
    (sender_id = ? AND receiver_id = ?)
)
AND item_id = ?
ORDER BY sent_at ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id, $item_id);
$stmt->execute();
$result = $stmt->get_result();

$lastDate = null;

while ($row = $result->fetch_assoc()) {
    $messageDate = date("Y-m-d", strtotime($row['sent_at']));
    $messageTime = date("h:i A", strtotime($row['sent_at']));

    $today = date("Y-m-d");
    $yesterday = date("Y-m-d", strtotime("-1 day"));

    if ($messageDate == $today) {
        $displayDate = "Today";
    } elseif ($messageDate == $yesterday) {
        $displayDate = "Yesterday";
    } else {
        $displayDate = date("F d, Y", strtotime($messageDate));
    }

    if ($lastDate != $messageDate) {
        echo '<div class="date-divider"><span>'.$displayDate.'</span></div>';
        $lastDate = $messageDate;
    }

    $class = ($row['sender_id'] == $current_user_id) ? "outgoing" : "incoming";

    /* CHECK IF MESSAGE IS A CLAIM MESSAGE */
    if ($row['message_type'] == 'claim') {
        $claim_stmt = $conn->prepare("SELECT * FROM claims WHERE claim_id = ?");
        $claim_stmt->bind_param("i", $row['claim_id']);
        $claim_stmt->execute();
        $claim = $claim_stmt->get_result()->fetch_assoc();

        if (!$claim) continue;

        $status = strtolower($claim['claim_status']);
        $isFinal = in_array($status, ['approved', 'rejected']);

        $statusClass = match($status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending'
        };

        echo '
        <div class="claim-card-wrapper '.$class.'">
            <div class="claim-card">
                <div class="claim-header">Claim Request</div>
                <div class="claim-info"><strong>Name:</strong> '.htmlspecialchars($claim['claimant_name']).'</div>
                <div class="claim-info"><strong>Contact:</strong> '.htmlspecialchars($claim['claimant_contact']).'</div>
                <div class="claim-info"><strong>Message:</strong><br>'.htmlspecialchars($claim['message']).'</div>';

        if (!empty($claim['proof_image'])) {
            echo '<img src="'.htmlspecialchars($claim['proof_image']).'" class="claim-proof">';
        }

        echo '<div class="claim-status '.$statusClass.'">'.htmlspecialchars($claim['claim_status']).'</div>';

        /* OWNER ACTION BUTTONS*/
        $item_stmt = $conn->prepare("SELECT user_id FROM found_items WHERE found_id = ?");
        $item_stmt->bind_param("i", $claim['found_item_id']);
        $item_stmt->execute();
        $item_res = $item_stmt->get_result();
        $item_owner = $item_res->fetch_assoc();

        if (($item_owner['user_id'] ?? null) == $current_user_id) {
            if ($isFinal) {
                echo '<div class="claim-actions"><small style="color:gray;">Decision already made</small></div>';
            } else {
                echo '
                <div class="claim-actions">
                    <!-- FIXED PATHS: Links directly because they now live in the same actions/ folder! -->
                    <form method="POST" action="actions/approve_claim.php" onsubmit="return confirm(\'Are you sure you want to APPROVE this claim? This cannot be undone.\');">
                        <input type="hidden" name="claim_id" value="'.$claim['claim_id'].'">
                        <button class="approve-btn">Approve</button>
                    </form>
                    <form method="POST" action="actions/reject_claim.php" onsubmit="return confirm(\'Are you sure you want to REJECT this claim? This cannot be undone.\');">
                        <input type="hidden" name="claim_id" value="'.$claim['claim_id'].'">
                        <button class="reject-btn">Reject</button>
                    </form>
                </div>';
            }
        }
        echo '</div></div>';

    } elseif ($row['message_type'] == 'found_report') {
        $report_stmt = $conn->prepare("SELECT * FROM found_reports WHERE report_id = ?");
        $report_stmt->bind_param("i", $row['report_id']);
        $report_stmt->execute();
        $report = $report_stmt->get_result()->fetch_assoc();

        if (!$report) continue;

        $status = strtolower($report['report_status']);
        $isFinal = in_array($status, ['approved', 'rejected']);

        $statusClass = match($status) {
            'approved' => 'approved',
            'rejected' => 'rejected',
            default => 'pending'
        };

        echo '
        <div class="claim-card-wrapper '.$class.'">
            <div class="claim-card">
                <div class="claim-header">Found Item Report</div>
                <div class="claim-info"><strong>Name:</strong> '.htmlspecialchars($report['finder_name']).'</div>
                <div class="claim-info"><strong>Contact:</strong> '.htmlspecialchars($report['finder_contact']).'</div>
                <div class="claim-info"><strong>Message:</strong><br>'.htmlspecialchars($report['message']).'</div>';

        if (!empty($report['proof_image'])) {
            echo '<img src="'.htmlspecialchars($report['proof_image']).'" class="claim-proof">';
        }

        echo '<div class="claim-status '.$statusClass.'">'.htmlspecialchars($report['report_status']).'</div>';

        $item_stmt = $conn->prepare("SELECT user_id FROM lost_items WHERE lost_id = ?");
        $item_stmt->bind_param("i", $report['lost_item_id']);
        $item_stmt->execute();
        $item_owner = $item_stmt->get_result()->fetch_assoc();

        if (($item_owner['user_id'] ?? null) == $current_user_id) {
            if ($isFinal) {
                echo '<div class="claim-actions"><small style="color:gray;">Decision already made</small></div>';
            } else {
                echo '
                <div class="claim-actions">
                    <!-- FIXED PATHS: Links directly because they now live in the same actions/ folder! -->
                    <form method="POST" action="actions/approve_found_report.php" onsubmit="return confirm(\'Approve this report?\');">
                        <input type="hidden" name="report_id" value="'.$report['report_id'].'">
                        <button class="approve-btn">Approve</button>
                    </form>
                    <form method="POST" action="actions/reject_found_report.php" onsubmit="return confirm(\'Reject this report?\');">
                        <input type="hidden" name="report_id" value="'.$report['report_id'].'">
                        <button class="reject-btn">Reject</button>
                    </form>
                </div>';
            }
        }
        echo '</div></div>';
    } else {
        /* NORMAL MESSAGE */
        echo '
        <div class="bubble-wrapper '.$class.'">
            <div class="message-bubble">'.htmlspecialchars($row['message_text']).'</div>
            <span class="bubble-timestamp">'.$messageTime.'</span>
        </div>';
    }
}
?>
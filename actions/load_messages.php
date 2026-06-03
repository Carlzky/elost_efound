<?php
session_start();
date_default_timezone_set('Asia/Manila');
include "../config/db.php";

if (!isset($_SESSION['user_id'])) exit;
$current_user_id = $_SESSION['user_id'];
if (!isset($_GET['receiver_id'])) exit;
$receiver_id = intval($_GET['receiver_id']);

$sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY sent_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iiii", $current_user_id, $receiver_id, $receiver_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$lastDate = null;

while ($row = $result->fetch_assoc()) {
    $messageDate = date("Y-m-d", strtotime($row['sent_at']));
    $messageTime = date("h:i A", strtotime($row['sent_at']));
    $today       = date("Y-m-d");
    $yesterday   = date("Y-m-d", strtotime("-1 day"));

    if ($messageDate == $today)         $displayDate = "Today";
    elseif ($messageDate == $yesterday) $displayDate = "Yesterday";
    else                                $displayDate = date("F d, Y", strtotime($messageDate));

    if ($lastDate != $messageDate) {
        echo '<div class="date-divider"><span>' . $displayDate . '</span></div>';
        $lastDate = $messageDate;
    }

    $class = ($row['sender_id'] == $current_user_id) ? "outgoing" : "incoming";

    // ── CLAIM CARD ──────────────────────────────────────────────────────────
    if (isset($row['message_type']) && $row['message_type'] == 'claim') {

        $claim_stmt = $conn->prepare("SELECT * FROM claims WHERE claim_id = ?");
        $claim_stmt->bind_param("i", $row['claim_id']);
        $claim_stmt->execute();
        $claim = $claim_stmt->get_result()->fetch_assoc();
        if (!$claim) continue;

        $status      = strtolower($claim['claim_status']);
        $isFinal     = in_array($status, ['approved', 'rejected']);
        $statusClass = match($status) { 'approved' => 'approved', 'rejected' => 'rejected', default => 'pending' };
        $claim_id    = intval($claim['claim_id']);

        $d = $conn->prepare("SELECT fi.*, u.username as posted_by FROM found_items fi JOIN users u ON fi.user_id = u.id WHERE fi.found_id = ?");
        $d->bind_param("i", $claim['found_item_id']);
        $d->execute();
        $item_detail = $d->get_result()->fetch_assoc();

        $modal_data = json_encode([
            "item_id"     => $claim['found_item_id'],
            "item_name"   => $item_detail['item_name'] ?? '',
            "category"    => $item_detail['category'] ?? '',
            "location"    => $item_detail['location_found'] ?? '',
            "item_date"   => isset($item_detail['date_found']) ? date("F d, Y", strtotime($item_detail['date_found'])) : '',
            "description" => $item_detail['description'] ?? '',
            "item_image"  => $item_detail['item_image'] ?? '',
            "posted_by"   => $item_detail['posted_by'] ?? '',
            "owner_id"    => $item_detail['user_id'] ?? '',
            "type"        => "found",
            "is_owner"    => ($current_user_id == ($item_detail['user_id'] ?? 0))
        ], JSON_HEX_APOS | JSON_HEX_QUOT);

        echo '<div class="claim-card-wrapper ' . $class . '">';
        echo '<div class="claim-card">';
        echo '<div class="claim-header">Claim Request</div>';
        echo '<div class="claim-info"><strong>Name:</strong> '       . htmlspecialchars($claim['claimant_name'])    . '</div>';
        echo '<div class="claim-info"><strong>Contact:</strong> '    . htmlspecialchars($claim['claimant_contact']) . '</div>';
        echo '<div class="claim-info"><strong>Message:</strong><br>' . htmlspecialchars($claim['message'])          . '</div>';

        if (!empty($claim['proof_image'])) {
            echo '<img src="' . htmlspecialchars($claim['proof_image']) . '" class="claim-proof">';
        }

        echo '<div class="report-footer">';
        echo '<button class="view-details-btn" onclick="openItemDetailsModal(' . htmlspecialchars($modal_data, ENT_QUOTES) . ')">View Details</button>';
        echo '<div class="claim-status ' . $statusClass . '">' . htmlspecialchars($claim['claim_status']) . '</div>';
        echo '</div>';

        $o = $conn->prepare("SELECT user_id FROM found_items WHERE found_id = ?");
        $o->bind_param("i", $claim['found_item_id']);
        $o->execute();
        $item_owner = $o->get_result()->fetch_assoc();

        if (($item_owner['user_id'] ?? null) == $current_user_id) {
            if ($isFinal) {
                echo '<div class="claim-actions"><small style="color:gray;">Decision already made</small></div>';
            } else {
                echo '<div class="claim-actions">';
                echo '<button class="approve-btn" onclick="openApproveModal(' . $claim_id . ', \'actions/approve_claim.php\')">Approve</button>';
                echo '<button class="reject-btn"  onclick="openRejectModal('  . $claim_id . ', \'actions/reject_claim.php\')">Reject</button>';
                echo '</div>';
            }
        }

        echo '</div></div>';

    // ── FOUND REPORT CARD ────────────────────────────────────────────────────
    } elseif (isset($row['message_type']) && $row['message_type'] == 'found_report') {

        $report_stmt = $conn->prepare("SELECT * FROM found_reports WHERE report_id = ?");
        $report_stmt->bind_param("i", $row['report_id']);
        $report_stmt->execute();
        $report = $report_stmt->get_result()->fetch_assoc();
        if (!$report) continue;

        $status      = strtolower($report['report_status']);
        $isFinal     = in_array($status, ['approved', 'rejected']);
        $statusClass = match($status) { 'approved' => 'approved', 'rejected' => 'rejected', default => 'pending' };
        $report_id   = intval($report['report_id']);

        $d2 = $conn->prepare("SELECT li.*, u.username as posted_by FROM lost_items li JOIN users u ON li.user_id = u.id WHERE li.lost_id = ?");
        $d2->bind_param("i", $report['lost_item_id']);
        $d2->execute();
        $lost_detail = $d2->get_result()->fetch_assoc();

        $modal_data_lost = json_encode([
            "item_id"     => $report['lost_item_id'],
            "item_name"   => $lost_detail['item_name'] ?? '',
            "category"    => $lost_detail['category'] ?? '',
            "location"    => $lost_detail['location_lost'] ?? '',
            "item_date"   => isset($lost_detail['date_lost']) ? date("F d, Y", strtotime($lost_detail['date_lost'])) : '',
            "description" => $lost_detail['description'] ?? '',
            "item_image"  => $lost_detail['item_image'] ?? '',
            "posted_by"   => $lost_detail['posted_by'] ?? '',
            "owner_id"    => $lost_detail['user_id'] ?? '',
            "type"        => "lost",
            "is_owner"    => ($current_user_id == ($lost_detail['user_id'] ?? 0))
        ], JSON_HEX_APOS | JSON_HEX_QUOT);

        echo '<div class="claim-card-wrapper ' . $class . '">';
        echo '<div class="claim-card">';
        echo '<div class="claim-header">Found Item Report</div>';
        echo '<div class="claim-info"><strong>Name:</strong> '       . htmlspecialchars($report['finder_name'])    . '</div>';
        echo '<div class="claim-info"><strong>Contact:</strong> '    . htmlspecialchars($report['finder_contact']) . '</div>';
        echo '<div class="claim-info"><strong>Message:</strong><br>' . htmlspecialchars($report['message'])        . '</div>';

        if (!empty($report['proof_image'])) {
            echo '<img src="' . htmlspecialchars($report['proof_image']) . '" class="claim-proof">';
        }

        echo '<div class="report-footer">';
        echo '<button class="view-details-btn" onclick="openItemDetailsModal(' . htmlspecialchars($modal_data_lost, ENT_QUOTES) . ')">View Details</button>';
        echo '<div class="claim-status ' . $statusClass . '">' . htmlspecialchars($report['report_status']) . '</div>';
        echo '</div>';

        $o2 = $conn->prepare("SELECT user_id FROM lost_items WHERE lost_id = ?");
        $o2->bind_param("i", $report['lost_item_id']);
        $o2->execute();
        $item_owner2 = $o2->get_result()->fetch_assoc();

        if (($item_owner2['user_id'] ?? null) == $current_user_id) {
            if ($isFinal) {
                echo '<div class="claim-actions"><small style="color:gray;">Decision already made</small></div>';
            } else {
                echo '<div class="claim-actions">';
                echo '<button class="approve-btn" onclick="openApproveModal(' . $report_id . ', \'actions/approve_found_report.php\')">Approve</button>';
                echo '<button class="reject-btn"  onclick="openRejectModal('  . $report_id . ', \'actions/reject_found_report.php\')">Reject</button>';
                echo '</div>';
            }
        }

        echo '</div></div>';

    // ── PLAIN TEXT MESSAGE ───────────────────────────────────────────────────
    } else {
        echo '<div class="bubble-wrapper ' . $class . '">';
        echo '<div class="message-bubble">' . htmlspecialchars($row['message_text']) . '</div>';
        echo '<span class="bubble-timestamp">' . $messageTime . '</span>';
        echo '</div>';
    }
}
?>
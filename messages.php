<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

include "config/db.php";

if(!isset($_SESSION['username'])){
    header("Location: registration.php");
    exit();
}

$user = $_SESSION['username'];
$current_user_id = $_SESSION['user_id'];

$selected_user_id = null;

if (isset($_GET['user_id'])) {
    $selected_user_id = intval($_GET['user_id']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - E-LOST KOH, E-FOUND MOH</title>

<link rel="stylesheet" href="assets/css/messages_style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">


<style>
    :root {
        --primary: #1F5D4A;
        --primary-dark: #143F32;
        --gold: #F1B846;
        --primary-green: #1F5D4A;
        --light-green: #BBC34A;
        --dark-gray: #68735C;
        --bg-gray: #F4F4F4;
        --pure-white: #FFFFFF;
        --text-dark: #1A1A1A;
        --border: #E5E5E5;
        --sidebar-width: 240px;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: var(--bg-gray);
        display: flex;
        min-height: 100vh;
        color: var(--text-dark);
        overflow: hidden;
    }

    /* ========================
       SIDEBAR WITH ANIMATION
    ========================= */
    .sidebar {
        width: var(--sidebar-width);
        background: var(--primary-green);
        color: white;
        padding: 24px;
        position: fixed;
        height: 100vh;
        display: flex;
        flex-direction: column;
        z-index: 10;
    }

    .logo-section {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 40px;
    }

    .logo-icon {
        width: 58px;
        height: 58px;
        background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        border: 2px solid var(--gold);
        border-radius: 16px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 26px;
        box-shadow:
            0 12px 30px rgba(0, 0, 0, 0.35),
            inset 0 3px 6px rgba(255, 255, 255, 0.18);
        transition:
            transform 0.7s cubic-bezier(0.2, 0.8, 0.2, 1),
            box-shadow 0.7s cubic-bezier(0.2, 0.8, 0.2, 1);
    }

    .logo-section:hover .logo-icon {
        transform: scale(1.08) translateY(-5px) rotate(4deg);
        box-shadow:
            0 18px 40px rgba(0, 0, 0, 0.45),
            inset 0 3px 6px rgba(255, 255, 255, 0.25);
    }

    .logo-text {
        font-family: 'Poppins', sans-serif;
        font-size: 15px;
        line-height: 1.3;
        font-weight: 700;
        color: #FFFFFF;
    }

    .logo-text .txt-highlight {
        color: #BBC34A;
    }

    .nav-menu {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 8px;
        height: 100%;
    }

    .nav-item a {
        text-decoration: none;
        color: rgba(255, 255, 255, 0.82);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 13px 16px;
        border-radius: 10px;
        transition: 0.25s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .nav-item a:hover {
        background: rgba(255, 255, 255, 0.05);
        color: white;
    }

    .nav-item.active a {
        background: rgba(255, 255, 255, 0.12);
        color: white;
        font-weight: 500;
    }

    .nav-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        opacity: 0.8;
    }

    /* ========================
       MAIN APP WORKSPACE
    ========================= */
    .main-content {
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        height: 100vh;
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
    }

    /* ========================
       TOP HEADER BAR
    ========================= */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 40px;
        border-bottom: 1px solid var(--border);
        background: var(--pure-white);
        height: 80px;
    }

    .page-title {
        font-family: 'Poppins', sans-serif;
        font-size: 26px;
        font-weight: 700;
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 24px;
    }

    .notif-bell-btn {
        background: transparent;
        border: none;
        cursor: pointer;
        color: #555;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .avatar-link{
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar{
        width: 42px;
        height: 42px;
        border-radius: 50%;
        object-fit: cover;
        cursor: pointer;
        border: 2px solid #E5E5E5;
        transition: all 0.25s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .avatar:hover{
        transform: scale(1.06);
        border-color: var(--primary-green);
        box-shadow: 0 6px 14px rgba(0,0,0,0.12);
    }

    /* ========================
       SPLIT-PANE CHAT PLATFORM
    ========================= */
    .chat-container {
        display: flex;
        flex: 1;
        height: calc(100vh - 80px);
        overflow: hidden;
    }

    /* INBOX CHAT LIST SIDEBAR */
    .chat-list-panel {
        width: 320px;
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        background: #FFFFFF;
    }

    .search-chat-wrapper {
        padding: 20px;
        position: relative;
    }

    .search-chat-wrapper input {
        width: 100%;
        padding: 12px 16px 12px 42px;
        border: 1px solid #DCE1E5;
        border-radius: 20px;
        font-size: 14px;
        outline: none;
        background: #F8F9FA;
    }

    .search-icon-svg {
        position: absolute;
        left: 34px;
        top: 50%;
        transform: translateY(-50%);
        color: #8A94A6;
        pointer-events: none;
    }

    .conversations-scroll-box {
        flex: 1;
        overflow-y: auto;
    }

    .conversation-card {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px 20px;
        cursor: pointer;
        border-bottom: 1px solid #F8F9FA;
        transition: background 0.2s;
    }

    .conversation-card:hover {
        background: #F4F7F6;
    }

    .conversation-card.active {
        background: #EBF2F0;
    }

    .user-thumb {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: #E0E0E0;
        flex-shrink: 0;
    }

    .card-meta-details {
        flex: 1;
        min-width: 0;
    }

    .meta-row-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .meta-row-top h4 {
        font-size: 14px;
        font-weight: 600;
        color: #1A1A1A;
    }

    .meta-row-top .timestamp {
        font-size: 11px;
        color: #8A94A6;
    }

    .preview-msg-text {
        font-size: 13px;
        color: #68735C;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* CENTRAL CHAT VIEW WINDOW (Hidden by Default) */
    .active-chat-window {
        display: none; /* Changed from flex to hide layout state initially */
        flex: 1;
        flex-direction: column;
        background: #F9FBFA;
    }

    .chat-header-identity {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 30px;
        background: #FFFFFF;
        border-bottom: 1px solid var(--border);
    }

    .header-user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .header-user-info h3 {
        font-size: 15px;
        font-weight: 600;
    }

    .header-user-info p {
        font-size: 12px;
        color: #8A94A6;
    }

    .info-action-btn {
        color: #1F5D4A;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    /* MESSAGES AREA */
    .messages-stream-container {
        flex: 1;
        padding: 30px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* FALLBACK INTRO CHAT VIEW (Visible by Default) */
    .empty-chat-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        flex: 1;
        color: #8A94A6;
        background: #F9FBFA;
    }

    .date-divider {
        text-align: center;
        margin: 10px 0;
        font-size: 12px;
        color: #8A94A6;
        position: relative;
    }

    .date-divider span {
        background: #F9FBFA;
        padding: 0 12px;
        position: relative;
        z-index: 1;
    }

    .date-divider::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 1px;
        background: #E5E5E5;
        left: 0;
        top: 50%;
        z-index: 0;
    }

    .bubble-wrapper {
        display: flex;
        flex-direction: column;
        max-width: 65%;
    }

    .bubble-wrapper.incoming {
        align-self: flex-start;
    }

    .bubble-wrapper.outgoing {
        align-self: flex-end;
        align-items: flex-end;
    }

    .message-bubble {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.4;
        word-break: break-word;
    }

    .incoming .message-bubble {
        background: #EEDFD3;
        color: #1A1A1A;
        border-top-left-radius: 2px;
    }

    .outgoing .message-bubble {
        background: #1F5D4A;
        color: #FFFFFF;
        border-top-right-radius: 2px;
    }

    .bubble-timestamp {
        font-size: 10px;
        color: #8A94A6;
        margin-top: 4px;
        padding: 0 4px;
    }

    /* COMPOSER INPUT BAR */
    .message-composer-footer {
        padding: 20px 30px;
        background: #FFFFFF;
        border-top: 1px solid var(--border);
    }

    .composer-form-container {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #F4F6F5;
        padding: 6px 10px 6px 18px;
        border-radius: 24px;
    }

    .clip-attachment-btn {
        background: transparent;
        border: none;
        color: #68735C;
        cursor: pointer;
        display: flex;
        align-items: center;
    }

    .composer-form-container input {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        padding: 10px 0;
    }

    .send-payload-btn {
        background: var(--primary-green);
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .send-payload-btn:hover {
        background: var(--primary-dark);
    }

    /* ========================
       LOGOUT MODAL
    ========================= */
    .logout-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(6px);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .logout-modal {
        background: white;
        padding: 32px;
        border-radius: 20px;
        text-align: center;
        width: 320px;
        border: 1px solid #EAEAEA;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        transform: scale(0.85);
        opacity: 0;
        animation: popIn 0.25s forwards;
    }

    @keyframes popIn {
        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .logout-modal h2 {
        font-family: 'Poppins', sans-serif;
        font-size: 20px;
        margin-bottom: 10px;
        color: var(--primary-green);
    }

    .logout-modal p {
        font-size: 14px;
        color: #7A7A7A;
        margin-bottom: 24px;
    }

    .logout-buttons {
        display: flex;
        gap: 12px;
    }

    .cancel-btn {
        flex: 1;
        padding: 12px;
        border: 1px solid #E0E0E0;
        border-radius: 10px;
        background: #F4F4F4;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: 0.2s;
    }

    .cancel-btn:hover { 
        background: #E8E8E8; 
    }

    .logout-btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 10px;
        background: var(--primary-green);
        color: white;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: 0.2s;
    }

    .logout-btn:hover { 
        background: var(--primary-dark); 
    }
</style>
</head>
<body>

<div class="sidebar">

    <div class="logo-section">
        <div class="logo-icon">🔍</div>
        <div class="logo-text">
            E-LOST <span class="txt-highlight">MOH</span><br>
            E-FOUND <span class="txt-highlight">KOH</span>
        </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </span>
                <span class="nav-text">My Claims</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="notif.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item active">
            <a href="messages.php">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>

</div>

<div class="main-content">

    <div class="top-bar">
        <h1 class="page-title">Messages</h1>
        <div class="user-profile">

            <a href="notif.php" class="notif-bell-btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </a>

            <a href="profile.php" class="avatar-link">
                <img src="images/default-avatar.png" alt="Profile Picture" class="avatar">
            </a>

        </div>
    </div>

    <div class="chat-container">

        <div class="chat-list-panel">
            <div class="search-chat-wrapper">
                <svg class="search-icon-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="chatSearchInput" placeholder="Search Messages">
            </div>

            <div class="conversations-scroll-box" id="conversationsBox">
                <?php
$sql = "
SELECT DISTINCT 
users.id,
users.username,
messages.item_id
FROM messages
JOIN users 
ON users.id = IF(messages.sender_id = ?, messages.receiver_id, messages.sender_id)
WHERE messages.sender_id = ? OR messages.receiver_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();

$result = $stmt->get_result();

while($chat = $result->fetch_assoc()):

$item_name = "General Chat";

if ($chat['item_id']) {

    $item_stmt = $conn->prepare("
        SELECT item_name FROM lost_items
        WHERE lost_id = ?

        UNION

        SELECT item_name FROM found_items
        WHERE found_id = ?
    ");

    $item_stmt->bind_param(
        "ii",
        $chat['item_id'],
        $chat['item_id']
    );

    $item_stmt->execute();

    $item_result = $item_stmt->get_result();

    $item_row = $item_result->fetch_assoc();

    if ($item_row) {
        $item_name = $item_row['item_name'];
    }
}
?>

<div class="conversation-card"
     data-userid="<?php echo $chat['id']; ?>"
     data-username="<?php echo htmlspecialchars($chat['username']); ?>"
     data-item="<?php echo $chat['item_id']; ?>">

    <div class="user-thumb"></div>

    <div class="card-meta-details">

        <div class="meta-row-top">
            <h4>
                <?php echo htmlspecialchars($chat['username']); ?>
            </h4>
        </div>

        <p class="preview-msg-text">
            <?php echo htmlspecialchars($item_name); ?>
        </p>

    </div>

</div>

<?php endwhile; ?>
            </div>
        </div>

        <div class="active-chat-window" id="activeChatWindow">
            <div class="chat-header-identity">
                <div class="header-user-info">
                    <div class="user-thumb" style="width:40px; height:40px;"></div>
                    <div>
                        <h3 id="chatHeaderName">Select Conversation</h3>
                        <p id="chatHeaderItem">Messages</p>
                    </div>
                </div>
                <div class="info-action-btn">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
            </div>

            <div class="messages-stream-container" id="chatStreamBox">

            </div>

            <div class="message-composer-footer">
                <div class="composer-form-container">
                    <button class="clip-attachment-btn" id="attachBtn">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    </button>
                    <input type="text" id="messageInput" placeholder="Type a message....">
                    <button class="send-payload-btn" id="sendBtn">Send</button>
                </div>
            </div>
        </div>

        <div class="empty-chat-state" id="emptyChatState">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <p style="margin-top: 12px; font-weight: 500;">Select a conversation to start messaging</p>
        </div>

    </div>
</div>

<div class="logout-overlay" id="logoutOverlay">
    <div class="logout-modal">
        <h2>Logout</h2>
        <p>Are you sure you want to logout?</p>
        <div class="logout-buttons">
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
            <button class="logout-btn" onclick="confirmLogout()">Confirm</button>
        </div>
    </div>
</div>

<script>
// Track global element pointers
const activeChatWindow = document.getElementById('activeChatWindow');
const emptyChatState = document.getElementById('emptyChatState');
const chatCards = document.querySelectorAll('.conversation-card');

// Helper functions to show/hide the workspace panels smoothly
function openChatWorkspace() {
    activeChatWindow.style.display = 'flex';
    emptyChatState.style.display = 'none';
}

function closeChatWorkspace() {
    activeChatWindow.style.display = 'none';
    emptyChatState.style.display = 'flex';
    // Clear active highlight states from all list blocks
    chatCards.forEach(c => c.classList.remove('active'));
}

// 1. LIVE SEARCH FILTER SYSTEM (With Auto-Close protection)
document.getElementById('chatSearchInput').addEventListener('input', function(e) {
    const filterText = e.target.value.toLowerCase().trim();
    let currentOpenCardStillVisible = false;
    
  document.querySelectorAll('.conversation-card').forEach(card => {
        const username = card.getAttribute('data-username').toLowerCase();
        const msgPreview = card.querySelector('.preview-msg-text').textContent.toLowerCase();
        
        if (username.includes(filterText) || msgPreview.includes(filterText)) {
            card.style.display = 'flex';
            // Verify if the active card is still matching search results
            if(card.classList.contains('active')) {
                currentOpenCardStillVisible = true;
            }
        } else {
            card.style.display = 'none';
        }
    });

    // If active chat card gets hidden during search filtration, close the conversation panel immediately
    if (!currentOpenCardStillVisible && activeChatWindow.style.display === 'flex') {
        closeChatWorkspace();
    }
});
document.querySelectorAll('.conversation-card').forEach(card => {

    card.addEventListener('click', function() {

        chatCards.forEach(c => c.classList.remove('active'));

        this.classList.add('active');

        const clickedUser =
            this.getAttribute('data-username');

        const receiverId =
            this.getAttribute('data-userid');

        document.getElementById('chatHeaderName')
            .textContent = clickedUser;

        openChatWorkspace();

        const itemId = this.getAttribute('data-item');

        fetch("load_messages.php?receiver_id=" + receiverId + "&item_id=" + itemId)

        .then(response => response.text())

        .then(data => {

            document.getElementById('chatStreamBox')
                .innerHTML = data;

            streamBox.scrollTop =
                streamBox.scrollHeight;
        });

    });

});

// SYSTEM MODAL OVERLAYS CONTROLLER
function openLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "none";
}

function confirmLogout(){
    window.location.href = "logout.php";
}

// --- MESSAGE SENDING LOGIC ---
const sendBtn = document.getElementById('sendBtn');
const messageInput = document.getElementById('messageInput');
const streamBox = document.getElementById('chatStreamBox');

function sendMessage() {

    const text = messageInput.value.trim();

    if(text === "") return;

    const activeCard =
        document.querySelector('.conversation-card.active');

    if(!activeCard) return;

    const receiverId = activeCard.dataset.userid;

    const itemId = activeCard.dataset.item;

    fetch("send_message.php", {

        method: "POST",

        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },

        body:
            "receiver_id=" + receiverId +
            "&item_id=" + itemId +
            "&message=" + encodeURIComponent(text)

    })

    .then(response => response.text())

    .then(data => {

        console.log(data);

        if(data.trim() === "success") {

            const bubble = document.createElement('div');

            bubble.className = 'bubble-wrapper outgoing';

            bubble.innerHTML = `
                <div class="message-bubble">${text}</div>
                <span class="bubble-timestamp">Just now</span>
            `;

            streamBox.appendChild(bubble);

            messageInput.value = "";

            streamBox.scrollTop =
                streamBox.scrollHeight;

        }

    });

}

// Click and Enter key listeners
sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});

// --- ATTACHMENT LOGIC ---
const attachBtn = document.getElementById('attachBtn');
// Create a hidden file input element
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.style.display = 'none';

document.body.appendChild(fileInput);

attachBtn.addEventListener('click', () => {
    fileInput.click(); // Open system file picker
});

fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        alert("File selected: " + e.target.files[0].name);
        // Add your logic here to upload the file to your server via AJAX
    }
});

function loadMessagesAuto() {

    const activeCard =
        document.querySelector('.conversation-card.active');

    if (!activeCard) return;

    const receiverId = activeCard.dataset.userid;

    const itemId = activeCard.dataset.item;

    fetch(
        "load_messages.php?receiver_id="
        + receiverId +
        "&item_id="
        + itemId
    )

    .then(response => response.text())

    .then(data => {

        const chatBox =
            document.getElementById('chatStreamBox');

        if (chatBox.innerHTML !== data) {

            const wasNearBottom =
                chatBox.scrollHeight - chatBox.scrollTop
                <= chatBox.clientHeight + 200;

            chatBox.innerHTML = data;

            if (wasNearBottom) {
                chatBox.scrollTop =
                    chatBox.scrollHeight;
            }
        }

    });

}

setInterval(loadMessagesAuto, 2000);
</script>

</body>
</html>
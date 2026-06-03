<?php
session_start();
include "config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: registration.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

$stmt_profile = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
$stmt_profile->bind_param("i", $current_user_id);
$stmt_profile->execute();
$profile_res = $stmt_profile->get_result();
$profile_data = $profile_res->fetch_assoc();
$avatar = !empty($profile_data['profile_image']) ? $profile_data['profile_image'] : 'assets/img/defaultProfile.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messages - E-LOST KOH, E-FOUND MOH</title>
<link rel="stylesheet" href="assets/css/messages_style.css?v=3">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
</head>

<body>

<div class="sidebar" id="sidebar">

    <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()" title="Toggle Sidebar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
    </button>

    <div class="logo-section" onclick="openSidebarIfCollapsed()">
        <div class="logo-icon">🔍</div>
            <div class="logo-text">
                E-LOST <span class="txt-highlight">MOH</span><br>
                E-FOUND <span class="txt-highlight">KOH</span>
            </div>
    </div>

    <ul class="nav-menu">
        <li class="nav-item">
            <a href="dashboard.php" data-tooltip="Dashboard">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </span>
                <span class="nav-text">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="report-item.php" data-tooltip="Report Item">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="12" y1="18" x2="12" y2="12"></line><line x1="9" y1="15" x2="15" y2="15"></line></svg>
                </span>
                <span class="nav-text">Report Item</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="browse-items.php" data-tooltip="Browse Items">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </span>
                <span class="nav-text">Browse Items</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="claim.php" data-tooltip="My Claims">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
                </span>
                <span class="nav-text">Claims</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="notif.php" data-tooltip="Notifications">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                </span>
                <span class="nav-text">Notifications</span>
            </a>
        </li>
        <li class="nav-item active">
            <a href="messages.php" data-tooltip="Messages">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <span class="nav-text">Messages</span>
            </a>
        </li>
        <li class="nav-item" style="margin-top: auto;">
            <a href="#" onclick="openLogoutModal()" data-tooltip="Logout">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </span>
                <span class="nav-text">Logout</span>
            </a>
        </li>
    </ul>
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

<div class="main-content">
    <div class="top-bar">
        <h1 class="page-title">Messages</h1>
        <div class="user-profile">
            <a href="notif.php" class="notif-bell-btn">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </a>
            <a href="profile.php" class="avatar-link">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile Picture" class="avatar">
            </a>
        </div>
    </div>

    <div class="chat-container">
        <div class="chat-list-panel">
            <div class="search-chat-wrapper">
                <input type="text" id="chatSearchInput" placeholder="Search Messages">
            </div>

            <div class="conversations-scroll-box" id="conversationsBox">
<?php
// 1. Get existing conversations GROUPED ONLY BY USER to prevent duplicate chat heads
$sql = "
SELECT users.id, users.username, users.profile_image
FROM users
JOIN messages ON users.id = IF(messages.sender_id = ?, messages.receiver_id, messages.sender_id)
WHERE messages.sender_id = ? OR messages.receiver_id = ?
GROUP BY users.id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$existing_chats = [];
while($row = $result->fetch_assoc()) {
    $existing_chats[$row['id']] = $row;
}
$stmt->close();

// 2. Catch incoming click intent (Checking for either user_id or receiver_id params)
$url_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : (isset($_GET['receiver_id']) ? intval($_GET['receiver_id']) : null);

if ($url_user_id && $url_user_id !== $current_user_id) {
    if (!isset($existing_chats[$url_user_id])) {
        $user_stmt = $conn->prepare("SELECT id, username, profile_image FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $url_user_id);
        $user_stmt->execute();
        $user_info = $user_stmt->get_result()->fetch_assoc();
        $user_stmt->close();
        
        if ($user_info) {
            $existing_chats = array($url_user_id => $user_info) + $existing_chats;
        }
    }
}

// 3. Render the unified conversation sidebar list
foreach ($existing_chats as $chat):
    $chat_avatar = !empty($chat['profile_image']) ? $chat['profile_image'] : 'assets/img/defaultProfile.png';
    $isActive = ($url_user_id == $chat['id']) ? ' active' : '';
?>
<div class="conversation-card<?php echo $isActive; ?>"
     data-userid="<?php echo $chat['id']; ?>"
     data-username="<?php echo htmlspecialchars($chat['username']); ?>"
     data-avatar="<?php echo htmlspecialchars($chat_avatar); ?>">
    <div class="user-thumb" style="background-image: url('<?php echo htmlspecialchars($chat_avatar); ?>'); background-size: cover; background-position: center;"></div>
    <div class="card-meta-details">
        <div class="meta-row-top">
            <h4><?php echo htmlspecialchars($chat['username']); ?></h4>
        </div>
        <p class="preview-msg-text">View Conversation</p>
    </div>
</div>
<?php endforeach; ?>
            </div>
        </div>

        <div class="active-chat-window" id="activeChatWindow">
            <div class="chat-header-identity">
                <div class="header-user-info">
                    <div class="user-thumb" id="chatHeaderAvatar" style="width:40px; height:40px; background-size: cover; background-position: center;"></div>
                    <div>
                        <h3 id="chatHeaderName">Select Conversation</h3>
                        <p id="chatHeaderItem">Messages</p>
                    </div>
                </div>
            </div>

            <div class="messages-stream-container" id="chatStreamBox"></div>

            <div class="message-composer-footer">
                <div class="composer-form-container">
                    <input type="text" id="messageInput" placeholder="Type a message....">
                    <button class="send-payload-btn" id="sendBtn">Send</button>
                </div>
            </div>
        </div>

        <div class="empty-chat-state" id="emptyChatState">
            <p style="margin-top: 12px; font-weight: 500;">Select a conversation to start messaging</p>
        </div>
    </div>
</div>

<script>
const activeChatWindow = document.getElementById('activeChatWindow');
const emptyChatState = document.getElementById('emptyChatState');
const chatCards = document.querySelectorAll('.conversation-card');
const sendBtn = document.getElementById('sendBtn');
const messageInput = document.getElementById('messageInput');
const streamBox = document.getElementById('chatStreamBox');
const chatHeaderAvatar = document.getElementById('chatHeaderAvatar');

function openChatWorkspace() {
    activeChatWindow.style.display = 'flex';
    emptyChatState.style.display = 'none';
}

document.querySelectorAll('.conversation-card').forEach(card => {
    card.addEventListener('click', function() {
        chatCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        const clickedUser = this.getAttribute('data-username');
        const receiverId = this.getAttribute('data-userid');
        const userAvatar = this.getAttribute('data-avatar');

        document.getElementById('chatHeaderName').textContent = clickedUser;
        document.getElementById('chatHeaderItem').textContent = 'Direct Message';
        chatHeaderAvatar.style.backgroundImage = "url('" + userAvatar + "')";
        openChatWorkspace();

        fetch("actions/load_messages.php?receiver_id=" + receiverId)
        .then(response => response.text())
        .then(data => {

    if (!data) return;

    const shouldStickBottom =
        streamBox.scrollHeight - streamBox.scrollTop <= streamBox.clientHeight + 150;

    // only redraw if chat changed
    if (streamBox.innerHTML !== data) {

        streamBox.innerHTML = data;

        if (shouldStickBottom) {
            streamBox.scrollTop = streamBox.scrollHeight;
        }
    }
});
    });
});

function sendMessage() {
    const text = messageInput.value.trim();
    if(text === "") return;

    const activeCard = document.querySelector('.conversation-card.active');
    if(!activeCard) return;
    const receiverId = activeCard.dataset.userid;

    fetch("actions/send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "receiver_id=" + receiverId + "&message=" + encodeURIComponent(text)
    })
    .then(response => response.text())
    .then(data => {
        if(data.trim() === "success") {
            const bubble = document.createElement('div');
            bubble.className = 'bubble-wrapper outgoing';
            bubble.innerHTML = `
                <div class="message-bubble">${text}</div>
                <span class="bubble-timestamp">Just now</span>
            `;
            streamBox.appendChild(bubble);
            messageInput.value = "";
            streamBox.scrollTop = streamBox.scrollHeight;
        }
    });
}

sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

let isLoading = false;

function loadMessagesAuto() {
    if (isLoading) return;

    const activeCard = document.querySelector('.conversation-card.active');
    if (!activeCard) return;

    isLoading = true;

    const receiverId = activeCard.dataset.userid;

    fetch("actions/load_messages.php?receiver_id=" + receiverId)
    .then(res => res.text())
    .then(data => {

    if (!data) return;

    const shouldStickBottom =
        streamBox.scrollHeight - streamBox.scrollTop <= streamBox.clientHeight + 150;

    if (streamBox.innerHTML !== data) {

        streamBox.innerHTML = data;

        if (shouldStickBottom) {
            streamBox.scrollTop = streamBox.scrollHeight;
        }
    }
})
    .finally(() => isLoading = false);
}

// --- FIXED AUTO-CLICKER ---
// Checks for whichever parameter triggered the redirect
window.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const targetUid = urlParams.get('user_id') || urlParams.get('receiver_id');

    if (targetUid) {
        const matchingCard = document.querySelector(`.conversation-card[data-userid="${targetUid}"]`);
        if (matchingCard) {
            matchingCard.click();
        }
    }
});

setInterval(loadMessagesAuto, 2000);

function showSystemMessage(title, message) {
    const overlay = document.getElementById("systemOverlay");
    const titleEl = document.getElementById("systemTitle");
    const msgEl = document.getElementById("systemMessage");

    if (!overlay || !titleEl || !msgEl) return;

    titleEl.innerText = title;
    msgEl.innerText = message;
    overlay.style.display = "flex";
}

function closeSystemModal() {
    document.getElementById("systemOverlay").style.display = "none";
}

    function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('collapsed');
}

document.querySelector('.logo-section').addEventListener('click', function () {
    const sidebar = document.getElementById('sidebar');

    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
});

function openSidebarIfCollapsed() {
    const sidebar = document.getElementById('sidebar');

    if (sidebar.classList.contains('collapsed')) {
        sidebar.classList.remove('collapsed');
    }
}

function openLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "flex";
}

function closeLogoutModal(){
    document.getElementById("logoutOverlay").style.display = "none";
}

function confirmLogout(){
    window.location.href = "actions/logout.php";
}

</script>


</body>
</html>
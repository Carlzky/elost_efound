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
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
            SELECT item_name FROM lost_items WHERE lost_id = ?
            UNION
            SELECT item_name FROM found_items WHERE found_id = ?
        ");
        $item_stmt->bind_param("ii", $chat['item_id'], $chat['item_id']);
        $item_stmt->execute();
        $item_result = $item_stmt->get_result();
        if ($item_row = $item_result->fetch_assoc()) {
            $item_name = $item_row['item_name'];
        }
        $item_stmt->close();
    }
?>
<div class="conversation-card"
     data-userid="<?php echo $chat['id']; ?>"
     data-username="<?php echo htmlspecialchars($chat['username']); ?>"
     data-item="<?php echo $chat['item_id']; ?>">
    <div class="user-thumb"></div>
    <div class="card-meta-details">
        <div class="meta-row-top">
            <h4><?php echo htmlspecialchars($chat['username']); ?></h4>
        </div>
        <p class="preview-msg-text"><?php echo htmlspecialchars($item_name); ?></p>
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

            <div class="messages-stream-container" id="chatStreamBox"></div>

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
const activeChatWindow = document.getElementById('activeChatWindow');
const emptyChatState = document.getElementById('emptyChatState');
const chatCards = document.querySelectorAll('.conversation-card');
const sendBtn = document.getElementById('sendBtn');
const messageInput = document.getElementById('messageInput');
const streamBox = document.getElementById('chatStreamBox');

function openChatWorkspace() {
    activeChatWindow.style.display = 'flex';
    emptyChatState.style.display = 'none';
}

function closeChatWorkspace() {
    activeChatWindow.style.display = 'none';
    emptyChatState.style.display = 'flex';
    chatCards.forEach(c => c.classList.remove('active'));
}

document.getElementById('chatSearchInput').addEventListener('input', function(e) {
    const filterText = e.target.value.toLowerCase().trim();
    let currentOpenCardStillVisible = false;
    
    document.querySelectorAll('.conversation-card').forEach(card => {
        const username = card.getAttribute('data-username').toLowerCase();
        const msgPreview = card.querySelector('.preview-msg-text').textContent.toLowerCase();
        
        if (username.includes(filterText) || msgPreview.includes(filterText)) {
            card.style.display = 'flex';
            if(card.classList.contains('active')) {
                currentOpenCardStillVisible = true;
            }
        } else {
            card.style.display = 'none';
        }
    });

    if (!currentOpenCardStillVisible && activeChatWindow.style.display === 'flex') {
        closeChatWorkspace();
    }
});

document.querySelectorAll('.conversation-card').forEach(card => {
    card.addEventListener('click', function() {
        chatCards.forEach(c => c.classList.remove('active'));
        this.classList.add('active');

        const clickedUser = this.getAttribute('data-username');
        const receiverId = this.getAttribute('data-userid');
        const itemId = this.getAttribute('data-item');

        document.getElementById('chatHeaderName').textContent = clickedUser;
        openChatWorkspace();

        fetch("actions/load_messages.php?receiver_id=" + receiverId + "&item_id=" + itemId)
        .then(response => response.text())
        .then(data => {
            streamBox.innerHTML = data;
            streamBox.scrollTop = streamBox.scrollHeight;
        });
    });
});

function openLogoutModal(){ document.getElementById("logoutOverlay").style.display = "flex"; }
function closeLogoutModal(){ document.getElementById("logoutOverlay").style.display = "none"; }
function confirmLogout(){ window.location.href = "actions/logout.php"; }

function sendMessage() {
    const text = messageInput.value.trim();
    if(text === "") return;

    const activeCard = document.querySelector('.conversation-card.active');
    if(!activeCard) return;

    const receiverId = activeCard.dataset.userid;
    const itemId = activeCard.dataset.item;

    fetch("actions/send_message.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "receiver_id=" + receiverId + "&item_id=" + itemId + "&message=" + encodeURIComponent(text)
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

const attachBtn = document.getElementById('attachBtn');
const fileInput = document.createElement('input');
fileInput.type = 'file';
fileInput.style.display = 'none';
document.body.appendChild(fileInput);

attachBtn.addEventListener('click', () => { fileInput.click(); });
fileInput.addEventListener('change', (e) => {
    if (e.target.files.length > 0) {
        alert("File selected: " + e.target.files[0].name);
    }
});

function loadMessagesAuto() {
    const activeCard = document.querySelector('.conversation-card.active');
    if (!activeCard) return;

    const receiverId = activeCard.dataset.userid;
    const itemId = activeCard.dataset.item;

    fetch("actions/load_messages.php?receiver_id=" + receiverId + "&item_id=" + itemId)
    .then(response => response.text())
    .then(data => {
        if (streamBox.innerHTML !== data) {
            const wasNearBottom = streamBox.scrollHeight - streamBox.scrollTop <= streamBox.clientHeight + 200;
            streamBox.innerHTML = data;
            if (wasNearBottom) {
                streamBox.scrollTop = streamBox.scrollHeight;
            }
        }
    });
}

setInterval(loadMessagesAuto, 2000);
</script>
</body>
</html>
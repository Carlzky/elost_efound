<?php
session_start();

include 'config/db.php'; // Aligned to your clean directory architecture

if (isset($_COOKIE['remember_me'])) {
    if (strpos($_COOKIE['remember_me'], ':') !== false) {
        list($selector, $validator) = explode(':', $_COOKIE['remember_me']);

        $stmt = $conn->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->bind_param("s", $selector);
        $stmt->execute();
        $stmt->close();
    }

    setcookie("remember_me", "", time() - 3600, "/");
}

// ---- NEW: DROP THE ESTABLISHED VISITOR TRACKER COOKIE ----
// This tells registration.php to display "Welcome Back" on the next visit
setcookie(
    'has_visited',
    'true',
    time() + (86400 * 365), // Persistent lifespan configuration tracker: 1 year
    "/",
    "",
    false,
    true // HttpOnly flag keeps this state marker safe from XSS script hijacking
);

// Clear runtime variables out of memory
$_SESSION = [];

session_unset();
session_destroy();

// Transitions through your premium linear splash screen back to the sign-in prompt
header("Location: loading.html?redirect=login");
exit();
?>
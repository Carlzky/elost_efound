<?php
session_start();

include 'config/db.php';

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

$_SESSION = [];

session_unset();
session_destroy();

header("Location: loading.html?redirect=login");
exit();
?>
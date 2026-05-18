<?php
session_start();
include 'db.php';

$message = "";
$step = 1; // Step 1: Request Code, Step 2: Validate & Reset

if (isset($_SESSION['reset_email'])) {
    $step = 2;
}

if (isset($_SESSION['redirect_message'])) {
    $message = $_SESSION['redirect_message'];
    unset($_SESSION['redirect_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ---- STEP 1: GENERATE TOKEN ----
    if (isset($_POST['action']) && $_POST['action'] == 'request_token') {
        $email = trim($_POST['email']);
        
        if (!str_ends_with(strtolower($email), '@cvsu.edu.ph')) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Only institutional university emails are allowed!</div>";
            header("Location: forgot_password.php");
            exit();
        }

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $token  = strval(rand(100000, 999999)); 
            $expiry = date('Y-m-d H:i:s', time() + 1800); // 30-minute validation timeframe
            
            $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->bind_param("s", $email);
            $del->execute();
            $del->close();

            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $token, $expiry);
            $stmt->execute();
            $stmt->close();

            // Local simulation helper notice for offline development environments
            $_SESSION['redirect_message'] = "<div class='msg success'>Reset code dispatched! Local debugging code token is: <b>$token</b></div>";
            $_SESSION['reset_email'] = $email;
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Email address is not registered!</div>";
        }
        $check->close();
        header("Location: forgot_password.php");
        exit();
    }

    // ---- STEP 2: OVERWRITE PASSWORD ----
    if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
        $email            = $_SESSION['reset_email'] ?? '';
        $token            = trim($_POST['token']);
        $new_password     = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($new_password) < 6) {
            $_SESSION['redirect_message'] = "<div class='msg error'>New password must be at least 6 characters long!</div>";
            header("Location: forgot_password.php");
            exit();
        }
        
        if ($new_password !== $confirm_password) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Passwords do not match!</div>";
            header("Location: forgot_password.php");
            exit();
        }

        $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expiry > NOW()");
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email);
            
            if ($update->execute()) {
                $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->bind_param("s", $email);
                $del->execute();
                $del->close();

                unset($_SESSION['reset_email']);
                $_SESSION['redirect_message'] = "<div class='msg success'>Password reset successfully! Proceed to sign in.</div>";
                header("Location: registration.php");
                exit();
            }
            $update->close();
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Invalid, wrong, or expired reset token!</div>";
        }
        $stmt->close();
        header("Location: forgot_password.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Recovery - E-LOST KOH, E-FOUND MOH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="registrationstyle.css">
</head>
<body>

<div class="page-wrapper">
    <div class="container">
        
        <div class="brand-side">
            <div class="logo-box">🔍</div>
            <h1>E-LOST <span>KOH</span><br>E-FOUND <span>MOH</span></h1>
        </div>

        <div class="form-card">
            <div class="form-padding-wrapper">
                <div id="message-container"><?php echo $message; ?></div>

                <?php if ($step == 1): ?>
                    <div class="form-section">
                        <h2>Recover Account</h2>
                        <form action="forgot_password.php" method="POST">
                            <input type="hidden" name="action" value="request_token">
                            <div class="input-group">
                                <input type="email" name="email" placeholder="Enter your @cvsu.edu.ph email" required>
                            </div>
                            <button type="submit" class="btn-primary">Send Reset Token</button>
                        </form>
                        <div class="footer-text">
                            Remembered details? <a href="registration.php" style="color:var(--primary); font-weight:700; text-decoration:none;">Back to Log In</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-section">
                        <h2>Reset Password</h2>
                        <form action="forgot_password.php" method="POST">
                            <input type="hidden" name="action" value="reset_password">
                            <div class="input-group">
                                <input type="text" name="token" placeholder="6-Digit Reset Token" required>
                            </div>
                            <div class="input-group">
                                <input type="password" name="new_password" id="new-password" placeholder="New Password (Min 6 chars)" minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('new-password', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <div class="input-group">
                                <input type="password" name="confirm_password" id="confirm-password" placeholder="Confirm New Password" minlength="6" required>
                                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirm-password', this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </button>
                            </div>
                            <button type="submit" class="btn-primary">Update Password</button>
                        </form>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<script>
function togglePasswordVisibility(fieldId, toggleButton) {
    const passwordField = document.getElementById(fieldId);
    const svgIcon = toggleButton.querySelector('svg');
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        svgIcon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>`;
    } else {
        passwordField.type = 'password';
        svgIcon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>`;
    }
}

window.addEventListener('DOMContentLoaded', () => {
    const msgContainer = document.getElementById('message-container');
    if (msgContainer && msgContainer.innerHTML.trim() !== "") {
        setTimeout(() => { msgContainer.classList.add('reveal-smooth'); }, 50);
        setTimeout(() => { msgContainer.classList.remove('reveal-smooth'); setTimeout(() => { msgContainer.innerHTML = ''; }, 500); }, 5000); 
    }
});
</script>
</body>
</html>
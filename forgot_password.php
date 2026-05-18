<?php
session_start();
// Path updated to match the new config folder location
include 'config/db.php'; 

$message = "";
$step = 1; 
$fetched_question = "";

if (isset($_SESSION['recovery_email']) && isset($_SESSION['recovery_question'])) {
    $step = 2;
    $fetched_question = $_SESSION['recovery_question'];
}

if (isset($_SESSION['redirect_message'])) {
    $message = $_SESSION['redirect_message'];
    unset($_SESSION['redirect_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ---- STEP 1: VERIFY INSTALMENT ACCOUNT ----
    if (isset($_POST['action']) && $_POST['action'] == 'verify_account') {
        $email = trim($_POST['email']);
        
        if (!str_ends_with(strtolower($email), '@cvsu.edu.ph')) {
            $_SESSION['redirect_message'] = "<div class='msg error'>Only institutional university emails are allowed!</div>";
            header("Location: forgot_password.php");
            exit();
        }

        $stmt = $conn->prepare("SELECT security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($security_question);
            $stmt->fetch();
            
            $_SESSION['recovery_email'] = $email;
            $_SESSION['recovery_question'] = $security_question;
            $_SESSION['redirect_message'] = "<div class='msg success'>Account found! Please answer your chosen challenge question below.</div>";
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Email address is not registered in the system!</div>";
        }
        $stmt->close();
        header("Location: forgot_password.php");
        exit();
    }

    // ---- STEP 2: CHALLENGE VERIFICATION & PASSWORD RESET ----
    if (isset($_POST['action']) && $_POST['action'] == 'submit_recovery') {
        $email            = $_SESSION['recovery_email'] ?? '';
        $security_answer  = trim($_POST['security_answer']);
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

        $stmt = $conn->prepare("SELECT security_answer FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($hashed_answer);
            $stmt->fetch();
            
            if (password_verify(strtolower($security_answer), $hashed_answer)) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                
                $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $update->bind_param("ss", $hashed_password, $email);
                
                if ($update->execute()) {
                    unset($_SESSION['recovery_email']);
                    unset($_SESSION['recovery_question']);
                    $_SESSION['redirect_message'] = "<div class='msg success'>Password updated successfully! Proceed to log in.</div>";
                    header("Location: registration.php");
                    exit();
                }
                $update->close();
            } else {
                $_SESSION['redirect_message'] = "<div class='msg error'>Incorrect answer to security question challenge!</div>";
            }
        } else {
            $_SESSION['redirect_message'] = "<div class='msg error'>Session sync error. Restart process.</div>";
            unset($_SESSION['recovery_email']);
            unset($_SESSION['recovery_question']);
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
    <link rel="stylesheet" href="assets/css/registrationstyle.css">
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
                            <input type="hidden" name="action" value="verify_account">
                            <div class="input-group">
                                <input type="email" name="email" placeholder="Enter your @cvsu.edu.ph email" required>
                            </div>
                            <button type="submit" class="btn-primary">Find Account</button>
                        </form>
                        <div class="footer-text">
                            Remembered details? <a href="registration.php" style="color:var(--primary); font-weight:700; text-decoration:none;">Back to Log In</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="form-section">
                        <h2>Identity Verification</h2>
                        <form action="forgot_password.php" method="POST">
                            <input type="hidden" name="action" value="submit_recovery">
                            
                            <div class="challenge-box" style="margin-bottom:20px; font-weight:600; color:var(--primary); text-align:center; font-size:15.5px;">
                                📋 <?php echo htmlspecialchars($fetched_question); ?>
                            </div>

                            <div class="input-group">
                                <input type="text" name="security_answer" placeholder="Type your security answer" required>
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
        setTimeout(() => { hideMessageSmoothly(); }, 4000); 
    }
});
</script>
</body>
</html>